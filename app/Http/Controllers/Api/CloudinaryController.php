<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CloudinaryController extends Controller
{
    /**
     * Return a signature and related params for direct client uploads to Cloudinary.
     * Query params supported: public_id, folder, notification_url
     */
    public function signature(Request $request)
    {
        $carpeta  = $request->query('folder') ?? $request->query('carpeta') ?? 'uploads';
        $publicId = $request->query('public_id') ?? null;
        $userId   = $request->user()?->id ?? null;

        $uploader = app(\App\Services\FileUploadService::class);
        $res      = $uploader->generarFirmaReserva($carpeta, $publicId, $userId, 120);

        return response()->json($res);
    }

    /**
     * Firma cualquier conjunto de parametros que envie el widget de subida (next-cloudinary
     * CldUploadWidget con uploadSignature). Usado para subidas firmadas client-direct.
     */
    public function firmarParametros(Request $request)
    {
        $paramsToSign = $request->input('paramsToSign', []);

        ksort($paramsToSign);
        $toSign = urldecode(http_build_query($paramsToSign));
        $secret = env('CLOUDINARY_SECRET') ?: config('services.cloudinary.secret');

        return response()->json([
            'signature' => sha1($toSign . ($secret ?? '')),
        ]);
    }

    /**
     * Webhook endpoint for Cloudinary upload notifications.
     * Validates signature and timestamp, then logs payload.
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Cloudinary webhook received', $payload);

        $secret    = env('CLOUDINARY_SECRET') ?: config('services.cloudinary.secret');
        $publicId  = $payload['public_id'] ?? null;
        $version   = $payload['version'] ?? null;
        $signature = $payload['signature'] ?? null;
        $timestamp = $payload['timestamp'] ?? null;

        if (! $signature || ! $publicId) {
            Log::warning('Cloudinary webhook missing signature or public_id', $payload);

            return response('Missing signature or public_id', 400);
        }

        // Build the string to sign similarly to Cloudinary: include present params in canonical order
        $toSignParts = [];
        if ($publicId) {
            $toSignParts[] = "public_id={$publicId}";
        }
        if ($version) {
            $toSignParts[] = "version={$version}";
        }
        if ($timestamp) {
            $toSignParts[] = "timestamp={$timestamp}";
        }
        $toSign = implode('&', $toSignParts);

        $calculated = sha1($toSign . ($secret ?? ''));

        if (! hash_equals($calculated, $signature)) {
            Log::warning('Cloudinary webhook signature mismatch', ['calculated' => $calculated, 'received' => $signature]);

            return response('Invalid signature', 403);
        }

        // timestamp tolerance (5 minutes)
        if ($timestamp) {
            $now = time();
            if (abs($now - (int)$timestamp) > 300) {
                Log::warning('Cloudinary webhook timestamp outside tolerance', ['timestamp' => $timestamp]);

                return response('Stale timestamp', 403);
            }
        }

        // At this point the webhook is validated. Persist or dispatch a job as needed.
        Log::info('Cloudinary webhook validated', ['public_id' => $publicId, 'secure_url' => $payload['secure_url'] ?? null]);

        return response()->json(['ok' => true]);
    }
}
