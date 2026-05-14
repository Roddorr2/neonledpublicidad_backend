<?php

namespace App\Services;

use App\Models\Campania;
use App\Models\WhatsappCampaignReservation;
use App\Models\WhatsappChunk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PlannerService
{
    /**
     * Plan a campaign into whatsapp_chunks.
     *
     * @param  array $recipients Array of recipient objects/arrays with at least 'id_modalservicio' and 'telefono'
     * @return array Created WhatsappChunk models
     */
    public static function planCampaign(int $campaignId, array $recipients, ?int $chunkSize = null, ?int $spacingMinutes = null): array
    {
        $created = [];

        // Validate campaign exists; throw 404 if not found
        $campaign = Campania::findOrFail($campaignId);
        $start    = $campaign->fecha_inicio ? Carbon::parse($campaign->fecha_inicio) : Carbon::now();

        // Normalize recipients: keep unique by telefono
        $seen       = [];
        $normalized = [];
        foreach ($recipients as $r) {
            $telefono = is_object($r) ? ($r->telefono ?? null) : ($r['telefono'] ?? null);
            $id       = is_object($r) ? ($r->id_modalservicio ?? ($r->id ?? null)) : ($r['id_modalservicio'] ?? ($r['id'] ?? null));
            $nombre   = is_object($r) ? ($r->nombre ?? null) : ($r['nombre'] ?? null);
            if (empty($telefono)) {
                continue;
            }
            if (isset($seen[$telefono])) {
                continue;
            }
            $seen[$telefono] = true;
            $normalized[]    = [
                'id_modalservicio' => $id,
                'telefono'         => $telefono,
                'nombre'           => $nombre,
            ];
        }

        // determine chunk size and spacing from config when not provided
        $chunkSize = $chunkSize ?? (int)config('whatsapp.chunk_size', 20);
        // Prefer seconds if configured for fine-grained testing; fall back to minutes
        $spacingSeconds = (int)config('whatsapp.chunk_spacing_seconds', 0);
        if ($spacingSeconds <= 0) {
            $spacingMinutes = $spacingMinutes ?? (int)config('whatsapp.chunk_spacing_minutes', 2);
            $spacingSeconds = $spacingMinutes * 60;
        }

        // Reserve slots for the campaign on the target date (apply daily limit)
        $date            = $start->copy()->startOfDay();
        $totalRecipients = count($normalized);
        if ($totalRecipients === 0) {
            return [];
        }

        // Try to reserve all recipients; if fails, decrement until success (small loop, limits are small)
        $reserved    = false;
        $reservation = null;
        $attempt     = $totalRecipients;
        while ($attempt > 0) {
            $res = WhatsappCampaignReservation::reserveSlots($campaignId, $date, $attempt);
            if ($res !== false) {
                $reserved    = $attempt;
                $reservation = $res;
                break;
            }
            $attempt--;
        }

        if (! $reserved) {
            Log::warning('planner.reserve_slots.none_available', ['campaign_id' => $campaignId, 'date' => $date->toDateString(), 'total_requested' => $totalRecipients]);

            return [];
        }

        // Trim recipients to the number actually reserved
        $normalized = array_slice($normalized, 0, $reserved);

        // The controller is expected to initialize `envios_pendientes` when creating the campaign.
        // Do not increment here to avoid double-counting when Planner is called from the controller flow.

        // Batch into chunks
        $batches = array_chunk($normalized, $chunkSize);

        foreach ($batches as $index => $batch) {
            $scheduled = $start->copy()->addSeconds($index * $spacingSeconds);

            $chunk = WhatsappChunk::create([
                'reservation_id'   => $reservation?->id ?? null,
                'campaign_id'      => $campaignId,
                'chunk_index'      => $index + 1,
                'recipients_count' => count($batch),
                'attempts'         => 0,
                'max_attempts'     => 3,
                'status'           => 'pending',
                'meta'             => ['recipients' => $batch],
                'scheduled_at'     => $scheduled->toDateTimeString(),
            ]);

            $created[] = $chunk;
        }

        return $created;
    }

    /**
     * Estimate campaign duration and chunking without creating any DB rows.
     * Accepts array of recipients or integer count.
     *
     * @param array|int          $recipientsOrCount
     * @param Carbon|string|null $startDate
     */
    public static function estimateCampaign($recipientsOrCount, ?int $chunkSize = null, ?int $dailyLimit = null, ?int $spacingMinutes = null, $startDate = null): array
    {
        $chunkSize      = $chunkSize ?? (int)config('whatsapp.chunk_size', 20);
        $dailyLimit     = $dailyLimit ?? (int)config('whatsapp.daily_limit', 50);
        $spacingSeconds = (int)config('whatsapp.chunk_spacing_seconds', 0);
        if ($spacingSeconds <= 0) {
            $spacingMinutes = $spacingMinutes ?? (int)config('whatsapp.chunk_spacing_minutes', 2);
            $spacingSeconds = $spacingMinutes * 60;
        }

        // derive total recipients
        if (is_array($recipientsOrCount)) {
            // normalize unique by telefono if possible
            $seen  = [];
            $total = 0;
            foreach ($recipientsOrCount as $r) {
                $telefono = is_object($r) ? ($r->telefono ?? null) : ($r['telefono'] ?? null);
                if (empty($telefono)) {
                    continue;
                }
                if (isset($seen[$telefono])) {
                    continue;
                }
                $seen[$telefono] = true;
                $total++;
            }
        } else {
            $total = (int)$recipientsOrCount;
        }

        $totalChunks   = (int)ceil($total / max(1, $chunkSize));
        $estimatedDays = (int)ceil($total / max(1, $dailyLimit));

        // build per-day breakdown
        $remaining = $total;
        $perDay    = [];
        for ($d = 0; $d < $estimatedDays; $d++) {
            $dayCount = min($remaining, $dailyLimit);
            $perDay[] = [
                'day_index'  => $d + 1,
                'recipients' => $dayCount,
                'chunks'     => (int)ceil($dayCount / max(1, $chunkSize)),
            ];
            $remaining -= $dayCount;
            if ($remaining <= 0) {
                break;
            }
        }

        // schedule preview for first N chunks
        $start        = $startDate ? (is_string($startDate) ? Carbon::parse($startDate) : $startDate) : Carbon::now();
        $preview      = [];
        $previewCount = min(10, $totalChunks);
        for ($i = 0; $i < $previewCount; $i++) {
            $seconds   = $i * $spacingSeconds;
            $preview[] = $start->copy()->addSeconds($seconds)->toDateTimeString();
        }

        return [
            'total_recipients'   => $total,
            'chunk_size'         => $chunkSize,
            'total_chunks'       => $totalChunks,
            'daily_limit'        => $dailyLimit,
            'estimated_days'     => $estimatedDays,
            'per_day'            => $perDay,
            'schedule_preview'   => $preview,
            'estimated_end_date' => $start->copy()->addDays(max(0, $estimatedDays - 1))->toDateString(),
        ];
    }
}
