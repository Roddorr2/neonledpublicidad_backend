<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WhatsappCampaignReservation extends Model
{
    protected $table = 'whatsapp_campaign_reservations';

    protected $fillable = [
        'campaign_id',
        'date',
        'reserved_slots',
    ];

    protected $casts = [
        'reserved_slots' => 'integer',
        'date'           => 'date',
    ];

    /**
     * Attempt to reserve $n slots for $campaignId on $date (Carbon).
     * Returns the reservation model on success, or false on insufficient capacity.
     * This operation is atomic.
     *
     * @param  int|null                          $campaignId
     * @return WhatsappCampaignReservation|false
     */
    public static function reserveSlots($campaignId, Carbon $date, int $n)
    {
        $maxPerDay  = config('whatsapp.daily_limit', 50);
        $dateString = $date->format('Y-m-d');

        return DB::transaction(function () use ($campaignId, $dateString, $n, $maxPerDay) {
            // Lock the row for update if exists
            $reservation = self::where('campaign_id', $campaignId)
                ->where('date', $dateString)
                ->lockForUpdate()
                ->first();

            if (! $reservation) {
                $reservation = self::create([
                    'campaign_id'    => $campaignId,
                    'date'           => $dateString,
                    'reserved_slots' => 0,
                ]);
            }

            if ($reservation->reserved_slots + $n > $maxPerDay) {
                return false;
            }

            $reservation->reserved_slots += $n;
            $reservation->save();

            return $reservation;
        });
    }
}
