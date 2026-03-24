<?php

namespace App\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\WatModal;
use App\Models\Campania;
use App\Models\WhatsappChunk;

class ProcessWhatsappStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const PROGRESS_MILESTONES = [15, 45, 60, 75, 90, 95, 100];
    private const FINAL_SUCCESS = ['sent', 'delivered'];
    private const FINAL_FAILURE = ['failed', 'undelivered', 'rejected', 'expired'];
    // States considered telemetry-only / non-descriptive for counters
    private const IGNORED_FOR_COUNTERS = ['queued', 'sending', 'read', 'accepted', 'processing'];

    public $eventId;

    public function __construct($eventId)
    {
        $this->eventId = $eventId;
    }

    public function handle()
    {
        $event = DB::table('whatsapp_webhook_events')->where('id', $this->eventId)->first();
        if (! $event) {
            Log::warning('whatsapp.process_event.missing', ['eventId' => $this->eventId]);
            return;
        }

        $raw = json_decode($event->raw ?: '{}', true);

        DB::transaction(function() use ($event, $raw) {
            // Primary processing: use chunk recipients and webhook data (id_modalservicio) to update campaign state.
            $messageId = $raw['message_id'] ?? $event->provider_message_id ?? null;
            $status = strtolower(trim($raw['status'] ?? $event->status ?? ''));

            $finalSuccess = self::FINAL_SUCCESS;
            $finalFailure = self::FINAL_FAILURE;
            $finalStates = array_merge($finalSuccess, $finalFailure);

            // If state is not final, nothing to count (telemetry only)
            if (!in_array($status, $finalStates)) {
                Log::info('whatsapp.process_event.nonfinal', ['eventId' => $event->id, 'status' => $status]);
                return;
            }

            $campaniaId = $raw['campania_id'] ?? $event->campania_id ?? null;
            $chunkId = $raw['chunk_id'] ?? $event->chunk_id ?? null;
            $recipientId = $raw['id_modalservicio'] ?? null;

            // Idempotency: check if an earlier final event exists for same recipient or same provider_message_id
            $alreadyCounted = false;
            if ($messageId) {
                $alreadyCounted = DB::table('whatsapp_webhook_events')
                    ->where('id', '<', $event->id)
                    ->where('provider_message_id', $messageId)
                    ->whereIn('status', $finalStates)
                    ->exists();
            }

            if (! $alreadyCounted && $recipientId !== null) {
                $prev = DB::table('whatsapp_webhook_events')
                    ->where('id', '<', $event->id)
                    ->where('chunk_id', $chunkId)
                    ->whereIn('status', $finalStates)
                    ->get();

                foreach ($prev as $p) {
                    $pr = json_decode($p->raw ?: '{}', true);
                    if (($pr['id_modalservicio'] ?? null) === $recipientId) {
                        $alreadyCounted = true;
                        break;
                    }
                }
            }

            if ($alreadyCounted) {
                Log::info('whatsapp.process_event.idempotent', ['eventId' => $event->id, 'recipient' => $recipientId, 'message_id' => $messageId]);
                return;
            }

            // Update campaign counters
            if ($campaniaId) {
                $camp = Campania::find($campaniaId);
                if ($camp) {
                    if (in_array($status, $finalSuccess)) {
                        $camp->increment('envios_exitosos', 1);
                    } else {
                        $camp->increment('envios_fallidos', 1);
                    }
                    $camp->decrement('envios_pendientes', 1);
                    $camp->refresh();

                    $milestone = $this->resolveProgressMilestone($camp->getProgressPercentage());
                    if ($milestone !== null && ((int) ($camp->progress_milestone ?? 0) < $milestone)) {
                        $camp->progress_milestone = $milestone;
                        $camp->progress_milestone_updated_at = now();
                        $camp->progress_version = ((int) ($camp->progress_version ?? 0)) + 1;
                    }
                    $camp->save();
                }
            }

            // Attempt to close the chunk if all recipients have final states
            if ($chunkId) {
                $chunk = WhatsappChunk::where('id', $chunkId)->lockForUpdate()->first();
                if ($chunk) {
                    $recipients = $chunk->meta['recipients'] ?? [];
                    $ids = array_column($recipients, 'id_modalservicio');
                    $totalRecipients = count($ids);

                    if ($totalRecipients > 0) {
                        $events = DB::table('whatsapp_webhook_events')
                            ->where('chunk_id', $chunk->id)
                            ->whereIn('status', $finalStates)
                            ->get();

                        $seenFinal = [];
                        $seenSuccess = [];
                        foreach ($events as $e) {
                            $pr = json_decode($e->raw ?: '{}', true);
                            $rid = $pr['id_modalservicio'] ?? null;
                            $st = strtolower(trim($pr['status'] ?? $e->status ?? ''));
                            if ($rid === null) continue;
                            if (in_array($st, $finalStates)) {
                                $seenFinal[$rid] = true;
                            }
                            if (in_array($st, self::FINAL_SUCCESS)) {
                                $seenSuccess[$rid] = true;
                            }
                        }

                        $finalCount = count($seenFinal);
                        $successCount = count($seenSuccess);

                        if ($finalCount >= $totalRecipients) {
                            $chunk->status = ($successCount === $totalRecipients) ? 'completed' : 'partial';
                            $chunk->completed_at = now();
                            $chunk->save();
                            // After closing a chunk, check whether the campaign can be marked completed.
                            if ($campaniaId) {
                                $campCheck = Campania::find($campaniaId);
                                if ($campCheck) {
                                    // Recompute: if no pending sends and all chunks are final -> complete campaign
                                    $pending = (int) ($campCheck->envios_pendientes ?? 0);
                                    $totalChunks = DB::table('whatsapp_chunks')->where('campaign_id', $campaniaId)->count();
                                    $finalChunks = DB::table('whatsapp_chunks')->where('campaign_id', $campaniaId)->whereIn('status', ['completed','partial','sent'])->count();

                                    if ($pending === 0 && $totalChunks > 0 && $finalChunks >= $totalChunks) {
                                        if ($campCheck->estado !== 'completada') {
                                            $campCheck->estado = 'completada';
                                            // set fecha_fin to latest chunk completion if available, otherwise now
                                            $lastCompletedAt = DB::table('whatsapp_chunks')
                                                ->where('campaign_id', $campaniaId)
                                                ->whereNotNull('completed_at')
                                                ->max('completed_at');
                                            $campCheck->fecha_fin = $lastCompletedAt ?: now();
                                            $campCheck->save();
                                            Log::info('whatsapp.campaign.completed', ['campania_id' => $campaniaId, 'fecha_fin' => $campCheck->fecha_fin]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // mark webhook event processed (optional)
            DB::table('whatsapp_webhook_events')->where('id', $event->id)->update(['updated_at' => now()]);
        });
    }

    private function resolveProgressMilestone(float $percentage): ?int
    {
        $reached = null;
        foreach (self::PROGRESS_MILESTONES as $milestone) {
            if ($percentage >= $milestone) {
                $reached = $milestone;
            }
        }

        return $reached;
    }
}
