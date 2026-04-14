<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappHealthService
{
    /**
     * Check if WhatsApp service is connected and ready for sending.
     * Uses cache to avoid repeated calls within 30 seconds.
     * Validates: API key, WhatsApp connection, webhook capability.
     * Returns: ['connected' => bool, 'error' => string|null, 'webhooksOperational' => bool]
     */
    public static function checkHealth()
    {
        // Cache health check for 30 seconds to avoid hammering the service
        $cacheKey = 'whatsapp.service.health';
        $cacheTtl = 30; // seconds

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $cacheTtl, function () {
            return self::performHealthCheck();
        });
    }

    /**
     * Perform actual health check with strict validation.
     * Uses /api/whatsapp/health (strict) instead of /api/whatsapp/status (loose).
     * This endpoint requires API key and validates webhook machinery.
     * Returns current state: {connected, webhooksOperational, apiKeyValid, error}
     * Private method called by checkHealth().
     */
    private static function performHealthCheck()
    {
        $url = whatsapp_url('/api/whatsapp/health');
        $apiKey = whatsapp_api_key();
        $maxRetries = 2;
        $retryDelayMs = 100;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::withHeaders(['X-API-Key' => $apiKey])
                    ->timeout(5)
                    ->post($url, []); // POST to /health endpoint

                if ($response->successful()) {
                    $data = $response->json();
                    $connected = data_get($data, 'connected', false);
                    $webhooksOperational = data_get($data, 'webhooksOperational', false);
                    $apiKeyValid = data_get($data, 'apiKeyValid', false);
                    
                    Log::debug('whatsapp.health_check.success', [
                        'connected' => $connected,
                        'webhooksOperational' => $webhooksOperational,
                        'apiKeyValid' => $apiKeyValid
                    ]);
                    
                    return [
                        'connected' => (bool) $connected,
                        'webhooksOperational' => (bool) $webhooksOperational,
                        'apiKeyValid' => (bool) $apiKeyValid,
                        'error' => null
                    ];
                }

                // Non-2xx response (service down or unhealthy)
                if ($response->status() >= 500) {
                    if ($attempt < $maxRetries) {
                        usleep($retryDelayMs * 1000);
                        continue;
                    }

                    return [
                        'connected' => false,
                        'webhooksOperational' => false,
                        'apiKeyValid' => true,
                        'error' => 'Service returned HTTP ' . $response->status()
                    ];
                }

                // 4xx errors (API key invalid, etc) - don't retry
                return [
                    'connected' => false,
                    'webhooksOperational' => false,
                    'apiKeyValid' => false,
                    'error' => 'API Key validation failed (HTTP ' . $response->status() . ')'
                ];
            } catch (\Exception $e) {
                if ($attempt < $maxRetries) {
                    usleep($retryDelayMs * 1000);
                    continue;
                }

                Log::warning('whatsapp.health_check.failed', [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries
                ]);
                return [
                    'connected' => false,
                    'webhooksOperational' => false,
                    'apiKeyValid' => true,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'connected' => false,
            'webhooksOperational' => false,
            'apiKeyValid' => true,
            'error' => 'Health check exhausted retries'
        ];
    }

    /**
     * Get missing recipients for a chunk by comparing expected vs webhook events.
     * Returns array of missing id_modalservicio.
     */
    public static function getMissingRecipients(int $chunkId)
    {
        try {
            $chunk = \App\Models\WhatsappChunk::find($chunkId);
            if (!$chunk) {
                return [];
            }

            $recipients = data_get($chunk->meta, 'recipients', []);
            $expectedIds = array_values(array_filter(array_map(function ($r) {
                return $r['id_modalservicio'] ?? null;
            }, $recipients)));

            if (empty($expectedIds)) {
                return [];
            }

            $finalStates = ['sent','delivered','failed','undelivered','rejected','expired'];

            // Fetch raw webhook events and extract id_modalservicio safely in PHP
            $events = \Illuminate\Support\Facades\DB::table('whatsapp_webhook_events')
                ->where('chunk_id', $chunkId)
                ->whereIn('status', $finalStates)
                ->get(['id', 'raw']);

            $seen = [];
            foreach ($events as $event) {
                try {
                    $rawData = json_decode($event->raw ?? '{}', true);
                    $id = $rawData['id_modalservicio'] ?? null;
                    if ($id !== null) {
                        $seen[(int) $id] = true;
                    }
                } catch (\Exception $je) {
                    // Skip malformed JSON in individual events
                    Log::warning('whatsapp.get_missing_recipients.json_parse_error', ['event_id' => $event->id, 'error' => $je->getMessage()]);
                }
            }

            $seenIds = array_keys($seen);
            return array_values(array_diff($expectedIds, $seenIds));
        } catch (\Exception $e) {
            Log::error('whatsapp.get_missing_recipients.failed', ['chunk_id' => $chunkId, 'error' => $e->getMessage()]);
            return [];
        }
    }
}
