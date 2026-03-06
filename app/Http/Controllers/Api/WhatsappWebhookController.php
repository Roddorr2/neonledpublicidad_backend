<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessWhatsappStatus;
use Illuminate\Support\Facades\Response;

class WhatsappWebhookController extends Controller
{
    public function status(Request $request)
    {
        $apiKey = $request->header('X-API-Key') ?: $request->header('x-api-key');
        if (!$apiKey || $apiKey !== config('services.whatsapp.apikey')) {
            Log::warning('whatsapp.webhook.unauthorized', ['ip' => $request->ip()]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->all();

        $validator = Validator::make($data, [
            'id_modal_wat' => 'sometimes|integer',
            'id_modalservicio' => 'sometimes|integer',
            'campania_id' => 'sometimes|integer',
            'campaign_id' => 'sometimes|integer',
            'status' => 'required|string',
            'message_id' => 'nullable|string',
            'error' => 'nullable|string',
            'sentAt' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            Log::warning('whatsapp.webhook.invalid_payload', ['errors' => $validator->errors()->all()]);
            return response()->json(['error' => 'Invalid payload'], 422);
        }

        // Persist raw event quickly
        // Normalize campaign id: accept both campania_id and campaign_id
        $campaniaId = $data['campania_id'] ?? $data['campaign_id'] ?? null;

        $eventId = DB::table('whatsapp_webhook_events')->insertGetId([
            'chunk_id' => $data['chunk_id'] ?? $data['chunk_number'] ?? null,
            'campania_id' => $campaniaId,
            'id_modal_wat' => $data['id_modal_wat'] ?? null,
            'provider_message_id' => $data['message_id'] ?? $data['provider_message_id'] ?? null,
            'status' => $data['status'] ?? null,
            'raw' => json_encode($data),
            'received_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Dispatch async job to process the event
        ProcessWhatsappStatus::dispatch($eventId)->onQueue('whatsapp-status');

        return response()->json(['accepted' => true, 'event_id' => $eventId], 202);
    }
}
