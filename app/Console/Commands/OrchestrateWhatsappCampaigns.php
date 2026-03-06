<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\WhatsappChunk;

class OrchestrateWhatsappCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:orchestrate';

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

        try {
            $chunk = WhatsappChunk::where('status', 'pending')
                ->where('scheduled_at', '<=', now())
                ->orderBy('scheduled_at')
                ->first();

            if (! $chunk) {
                $this->info('No pending chunks ready to send.');
                return 0;
            }

            $this->info('Processing chunk id=' . $chunk->id);

            // mark processing and increment attempts
            $chunk->status = 'processing';
            $chunk->attempts = $chunk->attempts + 1;
            $chunk->save();

            $recipients = data_get($chunk->meta, 'recipients', []);

            // include campaign message/image
            $campaign = \App\Models\Campania::find($chunk->campaign_id);
            // Include both English/Spanish aliases for compatibility with different provider implementations
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

            // Try the canonical path, fallback to provider-specific route if needed
            $base = rtrim(config('services.whatsapp.url'), '/');
            $url = $base . '/api/whatsapp/send-batch';
            $apiKey = config('services.whatsapp.apikey');

            try {
                $response = Http::withHeaders(['X-API-Key' => $apiKey])->post($url, $payload);
            } catch (\Exception $e) {
                Log::error('whatsapp.orchestrator.error', ['chunk_id' => $chunk->id, 'error' => $e->getMessage()]);
                $chunk->status = 'failed';
                $chunk->save();
                $this->error('HTTP error: ' . $e->getMessage());
                return 1;
            }

            if ($response->successful()) {
                $chunk->status = 'sent';
                $chunk->sent_at = now();
                // if provider reports no failures, we can mark completed; otherwise leave for webhook
                $body = $response->json();
                if (isset($body['failed']) && $body['failed'] === 0) {
                    $chunk->completed_at = now();
                    $chunk->status = 'completed';
                }
                $chunk->save();

                Log::info('whatsapp.chunk.sent', ['chunk_id' => $chunk->id, 'campaign_id' => $chunk->campaign_id, 'response' => $body]);
                $this->info('Chunk sent successfully.');
                return 0;
            }

            // non-successful response
            Log::warning('whatsapp.chunk.response_not_ok', ['chunk_id' => $chunk->id, 'status' => $response->status(), 'body' => $response->body()]);
            $chunk->status = 'failed';
            $chunk->save();
            $this->error('Provider returned non-OK status: ' . $response->status());
            return 1;

        } finally {
            $lock->release();
        }
    }
}
