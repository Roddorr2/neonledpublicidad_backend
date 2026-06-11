<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWhatsappStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WhatsappWebhookController extends Controller
{
    public function status(Request $request)
    {
        $apiKey = $request->header('X-API-Key') ?: $request->header('x-api-key');
        if (! $apiKey || $apiKey !== config('services.whatsapp.apikey')) {
            Log::warning('whatsapp.webhook.unauthorized', ['ip' => $request->ip()]);

            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = $request->all();
        $events  = $this->extractEvents($payload);

        if (empty($events)) {
            Log::warning('whatsapp.webhook.invalid_payload', ['errors' => ['Payload must include status or non-empty events[]']]);

            return response()->json(['error' => 'Invalid payload'], 422);
        }

        $acceptedEventIds = [];
        $rejected         = [];

        foreach ($events as $index => $eventData) {
            $normalized = $this->normalizeEvent($payload, $eventData);

            $validator = Validator::make($normalized, [
                // identifiers may be absent for modal-only events; accept nulls
                'id_modal_wat'        => 'nullable|integer',
                'id_modalservicio'    => 'nullable|integer',
                'campania_id'         => 'nullable|integer',
                'chunk_id'            => 'nullable|integer',
                'status'              => 'required|string',
                'message_id'          => 'nullable|string',
                'provider_message_id' => 'nullable|string',
                'error'               => 'nullable|string',
                'sentAt'              => 'nullable|date',
            ]);

            if ($validator->fails()) {
                $rejected[] = [
                    'index'  => $index,
                    'errors' => $validator->errors()->all(),
                ];

                continue;
            }

            // Ensure at least one identifier is present: modal or campaign or modalservicio
            if (empty($normalized['id_modal_wat']) && empty($normalized['campania_id']) && empty($normalized['id_modalservicio'])) {
                $rejected[] = [
                    'index'  => $index,
                    'errors' => ['Missing identifier: one of id_modal_wat, campania_id or id_modalservicio is required.'],
                ];

                continue;
            }

            $eventId = DB::table('whatsapp_webhook_events')->insertGetId([
                'chunk_id'            => $normalized['chunk_id'] ?? null,
                'campania_id'         => $normalized['campania_id'] ?? null,
                'id_modal_wat'        => $normalized['id_modal_wat'] ?? null,
                'provider_message_id' => $normalized['provider_message_id'] ?? ($normalized['message_id'] ?? null),
                'status'              => $normalized['status'] ?? null,
                'raw'                 => json_encode($normalized),
                'received_at'         => now(),
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            ProcessWhatsappStatus::dispatch($eventId)->onQueue('whatsapp-status');
            $acceptedEventIds[] = $eventId;
        }

        if (empty($acceptedEventIds)) {
            Log::warning('whatsapp.webhook.invalid_payload', ['errors' => $rejected]);

            return response()->json(['error' => 'Invalid payload', 'rejected' => $rejected], 422);
        }

        return response()->json([
            'accepted'        => true,
            'events_accepted' => count($acceptedEventIds),
            'event_ids'       => $acceptedEventIds,
            'events_rejected' => count($rejected),
            'rejected'        => $rejected,
        ], 202);
    }

    /**
     * Accepts either one event payload or a batch payload with events[].
     */
    private function extractEvents(array $payload): array
    {
        if (isset($payload['events']) && is_array($payload['events'])) {
            return array_values(array_filter($payload['events'], fn ($item) => is_array($item)));
        }

        return [$payload];
    }

    /**
     * Normalizes aliases while preserving canonical identifiers.
     */
    private function normalizeEvent(array $payload, array $event): array
    {
        $rootDefaults = $payload;
        unset($rootDefaults['events']);

        $normalized = array_merge($rootDefaults, $event);

        $normalized['campania_id'] = $normalized['campania_id'] ?? $normalized['campaign_id'] ?? null;
        $normalized['campaign_id'] = $normalized['campaign_id'] ?? $normalized['campania_id'];

        // Never overwrite explicit chunk_id with chunk_number.
        if (! isset($normalized['chunk_id']) || $normalized['chunk_id'] === null) {
            $normalized['chunk_id'] = $normalized['chunk_number'] ?? null;
        }

        $normalized['provider_message_id'] = $normalized['provider_message_id'] ?? $normalized['message_id'] ?? null;
        $normalized['message_id']          = $normalized['message_id'] ?? $normalized['provider_message_id'];

        if (isset($normalized['status']) && is_string($normalized['status'])) {
            $normalized['status'] = strtolower(trim($normalized['status']));
        }

        if (isset($normalized['error']) && ! is_string($normalized['error']) && $normalized['error'] !== null) {
            $normalized['error'] = json_encode($normalized['error']);
        }

        return $normalized;
    }
}
