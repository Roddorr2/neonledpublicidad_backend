<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\WhatsappChunk;
use App\Services\WhatsappHealthService;

class OrchestrateWhatsappCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:orchestrate {--campaign-id=} {--max-chunks=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process one scheduled whatsapp chunk (one-per-run)';

    public function handle()
    {
        $lock = Cache::lock('whatsapp-orchestrator-lock', 60);

        if (! $lock->get()) {
            $this->info('Another orchestrator is running. Exiting.');
            return 0;
        }

        $maxChunks = max(1, (int) $this->option('max-chunks'));
        $campaignFilter = $this->option('campaign-id') ? (int) $this->option('campaign-id') : null;
        $processed = 0;
        $staleSeconds = (int) config('whatsapp.stale_processing_seconds', 120);

        try {
            // Detect and recover stale chunks (processing/sent > X seconds old)
            $this->recoverStaleChunks($staleSeconds);

            while ($processed < $maxChunks) {
                $chunk = WhatsappChunk::query()
                    ->where('status', 'pending')
                    ->where('scheduled_at', '<=', now())
                    ->when($campaignFilter, fn ($q) => $q->where('campaign_id', $campaignFilter))
                    // Do not send the next chunk of a campaign while another chunk is awaiting webhook consolidation.
                    ->whereNotExists(function ($sub) {
                        $sub->selectRaw('1')
                            ->from('whatsapp_chunks as blocked')
                            ->whereColumn('blocked.campaign_id', 'whatsapp_chunks.campaign_id')
                            ->whereIn('blocked.status', ['processing', 'sent']);
                    })
                    ->orderBy('scheduled_at')
                    ->orderBy('id')
                    ->first();

                if (! $chunk) {
                    if ($processed === 0) {
                        $this->info('No pending chunks ready to send.');
                    }
                    break;
                }

                $this->info('Processing chunk id=' . $chunk->id);

                $chunk->status = 'processing';
                $chunk->attempts = $chunk->attempts + 1;
                $chunk->save();

                $recipients = data_get($chunk->meta, 'recipients', []);
                $campaign = \App\Models\Campania::find($chunk->campaign_id);

                $payload = [
                    'campaign_id' => $chunk->campaign_id,
                    'campania_id' => $chunk->campaign_id,
                    'chunk_id' => $chunk->id,
                    'chunk_number' => $chunk->chunk_index,
                    'recipients' => $recipients,
                    'message' => $campaign?->parrafo,
                    'parrafo' => $campaign?->parrafo,
                    'image_url' => $campaign?->imagen_url,
                ];

                $url = whatsapp_url('/api/whatsapp/send-campaign-batch');
                $apiKey = whatsapp_api_key();

                try {
                    $response = Http::withHeaders(['X-API-Key' => $apiKey])->post($url, $payload);
                } catch (\Exception $e) {
                    // Connection error (cURL, timeout, etc) - mark as transport_failed for recovery
                    Log::error('whatsapp.orchestrator.error', ['chunk_id' => $chunk->id, 'error' => $e->getMessage()]);
                    $chunk->status = 'transport_failed';
                    $chunk->save();
                    Log::info('whatsapp.chunk.transport_failed_on_connection', ['chunk_id' => $chunk->id, 'error' => $e->getMessage()]);
                    // Don't return 1 - continue processing other chunks
                    continue;
                }

                if ($response->successful()) {
                    $chunk->status = 'sent';
                    $chunk->sent_at = now();
                    $body = $response->json();
                    $chunk->save();

                    // Confirm/consume reservation slots for this chunk where applicable.
                    if (! empty($chunk->reservation_id)) {
                        try {
                            DB::transaction(function () use ($chunk) {
                                $res = DB::table('whatsapp_campaign_reservations')
                                    ->where('id', $chunk->reservation_id)
                                    ->lockForUpdate()
                                    ->first();

                                if ($res) {
                                    $current = (int) ($res->reserved_slots ?? 0);
                                    $consume = (int) ($chunk->recipients_count ?? 0);
                                    $new = max(0, $current - $consume);
                                    DB::table('whatsapp_campaign_reservations')
                                        ->where('id', $chunk->reservation_id)
                                        ->update(['reserved_slots' => $new, 'updated_at' => now()]);
                                }
                            });
                        } catch (\Exception $e) {
                            Log::warning('whatsapp.orchestrator.reservation_consume_failed', ['chunk_id' => $chunk->id, 'reservation_id' => $chunk->reservation_id, 'error' => $e->getMessage()]);
                        }
                    }

                    Log::info('whatsapp.chunk.sent', ['chunk_id' => $chunk->id, 'campaign_id' => $chunk->campaign_id, 'response' => $body]);
                    $this->info('Chunk sent successfully.');
                    $processed++;
                    continue;
                }

                Log::warning('whatsapp.chunk.response_not_ok', ['chunk_id' => $chunk->id, 'status' => $response->status(), 'body' => $response->body()]);
                
                // Differentiate between transport errors (5xx - recoverable) and final errors (4xx - permanent)
                if ($response->status() >= 500) {
                    // Transport/service error: mark as recoverable for retry when service comes back
                    $chunk->status = 'transport_failed';
                    Log::info('whatsapp.chunk.transport_failed', ['chunk_id' => $chunk->id, 'status' => $response->status()]);
                } else {
                    // Client/permanent error: mark as failed
                    $chunk->status = 'failed';
                    Log::error('whatsapp.chunk.failed', ['chunk_id' => $chunk->id, 'status' => $response->status()]);
                }
                
                $chunk->save();
                $this->error('Provider returned non-OK status: ' . $response->status());
                return 1;
            }

            return 0;
        } finally {
            $lock->release();
        }
    }

    /**
     * Detect chunks in processing/sent state for > X seconds and recover missing recipients.
     * Also retries chunks stuck in transport_failed when service recovers.
     * Wraps in transaction for atomicity. Checks health before recovery.
     */
    private function recoverStaleChunks(int $staleSeconds)
    {
        try {
            // Check WhatsApp service health upfront
            $health = WhatsappHealthService::checkHealth();
            
            // If webhooks are down, try to reactivate the service
            if (!$health['webhooksOperational'] && $health['apiKeyValid']) {
                $this->info('🔌 Webhooks down - attempting to start connection...');
                $this->attemptStartConnection();
                
                // Check health again after reactivation attempt
                $health = WhatsappHealthService::checkHealth();
                
                Log::info('recovery.connection_reactivation_attempted', [
                    'webhooksOperational' => $health['webhooksOperational'] ?? false,
                    'connected' => $health['connected'] ?? false
                ]);
            }
            
            // If service still not operational, skip recovery
            if (!$health['connected']) {
                $reason = $health['error'] ?? 'Health check failed';
                
                Log::info('recovery.skipped.service_down', [
                    'reason' => $reason,
                    'webhooksOperational' => $health['webhooksOperational'] ?? false,
                    'apiKeyValid' => $health['apiKeyValid'] ?? false
                ]);
                return; // Reschedule recovery on next orchestrator cycle
            }
            
            // If service just came back up, retry chunks that failed due to transport errors
            $this->retryTransportFailedChunks();

            $staleTime = now()->subSeconds($staleSeconds);
            $maxRecoveryAttempts = config('whatsapp.max_recovery_attempts', 3);

            DB::transaction(function () use ($staleTime, $maxRecoveryAttempts) {
                // Find chunks stuck in processing/sent (within transaction)
                $staleChunks = WhatsappChunk::query()
                    ->whereIn('status', ['processing', 'sent'])
                    ->where(function ($q) use ($staleTime) {
                        $q->where('sent_at', '<', $staleTime)
                          ->orWhere('updated_at', '<', $staleTime);
                    })
                    ->limit(5)
                    ->lockForUpdate()
                    ->get();

                if ($staleChunks->isEmpty()) {
                    return;
                }

                foreach ($staleChunks as $chunk) {
                    $missing = WhatsappHealthService::getMissingRecipients($chunk->id);

                    // Only mark as completed if:
                    // 1. No missing recipients AND
                    // 2. We actually have webhook events (not just assumption based on empty missing list)
                    if (empty($missing)) {
                        // Double-check: verify we actually have webhooks
                        $recipientsMeta = data_get($chunk->meta, 'recipients', []);
                        $totalExpected = count($recipientsMeta);
                        
                        if ($totalExpected > 0) {
                            // Count actual final state webhooks
                            $finalStates = ['sent','delivered','failed','undelivered','rejected','expired'];
                            $finalWebhookCount = DB::table('whatsapp_webhook_events')
                                ->where('chunk_id', $chunk->id)
                                ->whereIn('status', $finalStates)
                                ->count();
                            
                            // Only mark completed if we have webhooks for all recipients
                            if ($finalWebhookCount >= $totalExpected) {
                                if ($chunk->status !== 'completed') {
                                    $chunk->status = 'completed';
                                    $chunk->completed_at = now();
                                    $chunk->save();
                                    Log::info('recovery.chunk.all_webhooks_received', ['chunk_id' => $chunk->id, 'webhook_count' => $finalWebhookCount, 'expected' => $totalExpected]);
                                }
                                continue;
                            } else {
                                // Not all webhooks arrived yet - requeue and wait
                                Log::info('recovery.chunk.incomplete_webhooks', ['chunk_id' => $chunk->id, 'received' => $finalWebhookCount, 'expected' => $totalExpected]);
                                // Continue to requeue logic below
                            }
                        } else {
                            // No recipients in meta - this shouldn't happen but mark as completed to avoid infinite loop
                            if ($chunk->status !== 'completed') {
                                $chunk->status = 'completed';
                                $chunk->completed_at = now();
                                $chunk->save();
                                Log::warning('recovery.chunk.no_recipients_in_meta', ['chunk_id' => $chunk->id]);
                            }
                            continue;
                        }
                    }

                    // Track recovery attempts in meta
                    $meta = $chunk->meta ?? [];
                    $recoveryAttempts = (int) data_get($meta, 'recovery_attempts', 0);

                    // Check if max attempts reached
                    if ($recoveryAttempts >= $maxRecoveryAttempts) {
                        $missingCount = count($missing);
                        $missingPercentage = ($missingCount / $chunk->recipients_count) * 100;
                        Log::warning('recovery.chunk.max_attempts_reached', [
                            'chunk_id' => $chunk->id,
                            'missing_count' => $missingCount,
                            'missing_percentage' => round($missingPercentage, 2),
                        ]);
                        // Mark as partial (incomplete but no more retries)
                        $chunk->status = 'partial';
                        $chunk->completed_at = now();
                        $chunk->save();
                        continue;
                    }

                    // Requeue: need to try again for missing recipients ONLY
                    // Filter recipients meta to contain ONLY missing recipients to prevent re-sending to already-sent users
                    $allRecipients = data_get($chunk->meta, 'recipients', []);
                    // getMissingRecipients returns array of IDs: [5, 6, 7]
                    $missingRecipientIds = array_map('intval', $missing);
                    $recipientsToRetry = array_filter($allRecipients, function ($recipient) use ($missingRecipientIds) {
                        return in_array((int)($recipient['id_modalservicio'] ?? null), $missingRecipientIds);
                    });
                    
                    $meta['recovery_attempts'] = $recoveryAttempts + 1;
                    $meta['recovery_reason'] = 'missing_webhooks';
                    $meta['last_recovery_at'] = now()->toIso8601String();
                    $meta['recipients'] = array_values($recipientsToRetry); // Update to only missing recipients
                    $chunk->meta = $meta;
                    $chunk->status = 'pending';
                    $chunk->scheduled_at = now()->addSeconds(10);
                    $chunk->save();

                    Log::info('recovery.chunk.requeued', [
                        'chunk_id' => $chunk->id,
                        'campaign_id' => $chunk->campaign_id,
                        'missing_count' => count($missing),
                        'retry_recipients_count' => count($recipientsToRetry),
                        'recovery_attempt' => $recoveryAttempts + 1,
                        'max_attempts' => $maxRecoveryAttempts,
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error('recovery.failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * Retry chunks that failed due to transport errors (5xx) when service comes back up.
     * Only retries chunks from the last 2 hours to avoid stale retries.
     * Also filters recipients to only those missing webhooks to prevent duplicates.
     */
    private function retryTransportFailedChunks()
    {
        try {
            DB::transaction(function () {
                // Find transport_failed chunks that still have attempts remaining (max 3)
                $transportFailed = WhatsappChunk::query()
                    ->where('status', 'transport_failed')
                    ->where('attempts', '<', 3)  // Retry until attempts exhausted
                    ->orderBy('updated_at', 'asc')  // Retry oldest first
                    ->limit(10)  // Avoid overloading
                    ->lockForUpdate()
                    ->get();

                if ($transportFailed->isEmpty()) {
                    Log::debug('recovery.transport_failed.none_found', [
                        'reason' => 'no_chunks_with_attempts_remaining'
                    ]);
                    return;
                }

                foreach ($transportFailed as $chunk) {
                    // Get missing recipients to filter what actually needs to be retried
                    $missing = WhatsappHealthService::getMissingRecipients($chunk->id);
                    
                    // If all recipients already have webhooks, mark as completed instead of retrying
                    if (empty($missing)) {
                        $chunk->status = 'completed';
                        $chunk->completed_at = now();
                        $chunk->save();
                        Log::info('recovery.transport_failed.already_sent', [
                            'chunk_id' => $chunk->id,
                            'campaign_id' => $chunk->campaign_id,
                            'reason' => 'all_recipients_already_delivered'
                        ]);
                        continue;
                    }
                    
                    // Filter recipients to only missing ones to prevent duplicate sends
                    $allRecipients = data_get($chunk->meta, 'recipients', []);
                    // getMissingRecipients returns array of IDs: [5, 6, 7]
                    $missingRecipientIds = array_map('intval', $missing);
                    $recipientsToRetry = array_filter($allRecipients, function ($recipient) use ($missingRecipientIds) {
                        return in_array((int)($recipient['id_modalservicio'] ?? null), $missingRecipientIds);
                    });
                    
                    // Reset to pending for immediate retry
                    $chunk->status = 'pending';
                    $chunk->scheduled_at = now();
                    $chunk->attempts = ($chunk->attempts ?? 0) + 1;  // Increment attempt counter
                    
                    // Track this was a recovery from transport failure
                    $meta = $chunk->meta ?? [];
                    $meta['transport_recovery_at'] = now()->toIso8601String();
                    $meta['recipients'] = array_values($recipientsToRetry); // Update to only missing recipients
                    $chunk->meta = $meta;
                    
                    $chunk->save();
                    
                    Log::info('recovery.transport_failed.requeued', [
                        'chunk_id' => $chunk->id,
                        'campaign_id' => $chunk->campaign_id,
                        'attempt' => $chunk->attempts,
                        'missing_count' => count($missing),
                        'retry_recipients_count' => count($recipientsToRetry),
                        'reason' => 'service_recovered'
                    ]);
                }

                // Mark as permanently failed if attempts exhausted
                $exhausted = WhatsappChunk::query()
                    ->where('status', 'transport_failed')
                    ->where('attempts', '>=', 3)
                    ->lockForUpdate()
                    ->get();

                foreach ($exhausted as $chunk) {
                    $chunk->status = 'failed';
                    $chunk->save();

                    Log::warning('recovery.transport_failed.abandoned', [
                        'chunk_id' => $chunk->id,
                        'campaign_id' => $chunk->campaign_id,
                        'attempts' => $chunk->attempts,
                        'reason' => 'max_retry_attempts_exhausted'
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error('recovery.transport_failed.error', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Attempt to start the WhatsApp service connection via /start-connection endpoint.
     * Called when webhooksOperational=false to reactivate the service.
     */
    private function attemptStartConnection()
    {
        try {
            $url = whatsapp_url('/api/whatsapp/start-connection');
            $apiKey = whatsapp_api_key();

            // Call /start-connection endpoint with API key
            $response = Http::withHeaders(['X-API-Key' => $apiKey])
                ->timeout(5)
                ->post($url, []);

            if ($response->successful()) {
                Log::info('whatsapp.start_connection.success', [
                    'message' => $response->json()['message'] ?? 'Connection started'
                ]);
                
                // Wait a bit for the service to establish connection and webhooks
                sleep(3);
            } else {
                Log::warning('whatsapp.start_connection.failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('whatsapp.start_connection.error', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
