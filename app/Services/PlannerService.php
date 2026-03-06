<?php

namespace App\Services;

use App\Models\WhatsappChunk;
use App\Models\Campania;
use App\Models\WhatsappCampaignReservation;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PlannerService
{
	/**
	 * Plan a campaign into whatsapp_chunks.
	 *
	 * @param int $campaignId
	 * @param array $recipients Array of recipient objects/arrays with at least 'id_modalservicio' and 'telefono'
	 * @param int $chunkSize
	 * @param int $spacingMinutes
	 * @return array Created WhatsappChunk models
	 */
	public static function planCampaign(int $campaignId, array $recipients, int $chunkSize = null, int $spacingMinutes = null): array
	{
		$created = [];

		$campaign = Campania::find($campaignId);
		$start = $campaign && $campaign->fecha_inicio ? Carbon::parse($campaign->fecha_inicio) : Carbon::now();

		// Normalize recipients: keep unique by telefono
		$seen = [];
		$normalized = [];
		foreach ($recipients as $r) {
			$telefono = is_object($r) ? ($r->telefono ?? null) : ($r['telefono'] ?? null);
			$id = is_object($r) ? ($r->id_modalservicio ?? ($r->id ?? null)) : ($r['id_modalservicio'] ?? ($r['id'] ?? null));
			if (empty($telefono)) continue;
			if (isset($seen[$telefono])) continue;
			$seen[$telefono] = true;
			$normalized[] = ['id_modalservicio' => $id, 'telefono' => $telefono];
		}

		// determine chunk size and spacing from config when not provided
		$chunkSize = $chunkSize ?? (int) config('whatsapp.chunk_size', 20);
		$spacingMinutes = $spacingMinutes ?? (int) config('whatsapp.chunk_spacing_minutes', 2);

		// Reserve slots for the campaign on the target date (apply daily limit)
		$date = $start->copy()->startOfDay();
		$totalRecipients = count($normalized);
		if ($totalRecipients === 0) {
			return [];
		}

		// Try to reserve all recipients; if fails, decrement until success (small loop, limits are small)
		$reserved = false;
		$attempt = $totalRecipients;
		while ($attempt > 0) {
			$res = WhatsappCampaignReservation::reserveSlots($campaignId, $date, $attempt);
			if ($res !== false) {
				$reserved = $attempt;
				break;
			}
			$attempt--;
		}

		if (! $reserved) {
			Log::info('planner.reserve_slots.none_available', ['campaign_id' => $campaignId, 'date' => $date->toDateString()]);
			return [];
		}

		// Trim recipients to the number actually reserved
		$normalized = array_slice($normalized, 0, $reserved);

		// The controller is expected to initialize `envios_pendientes` when creating the campaign.
		// Do not increment here to avoid double-counting when Planner is called from the controller flow.

		// Batch into chunks
		$batches = array_chunk($normalized, $chunkSize);

		foreach ($batches as $index => $batch) {
			$scheduled = $start->copy()->addMinutes($index * $spacingMinutes);

			$chunk = WhatsappChunk::create([
				'campaign_id' => $campaignId,
				'chunk_index' => $index + 1,
				'recipients_count' => count($batch),
				'attempts' => 0,
				'max_attempts' => 3,
				'status' => 'pending',
				'meta' => ['recipients' => $batch],
				'scheduled_at' => $scheduled->toDateTimeString(),
			]);

			$created[] = $chunk;
		}

		return $created;
	}
}
