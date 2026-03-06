<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\WatModal;
use App\Models\Campania;

class ProcessWhatsappStatus implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

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
            // Resolve the WatModal row: prefer id_modal_wat, otherwise try id_modalservicio
            $wat = null;
            if (!empty($event->id_modal_wat)) {
                $wat = WatModal::where('id_modal_wat', $event->id_modal_wat)->lockForUpdate()->first();
            }
            if (!$wat && !empty($raw['id_modalservicio'])) {
                $wat = WatModal::where('id_modalservicio', $raw['id_modalservicio'])->lockForUpdate()->first();
            }

            // Update WatModal and campania counters if applicable
            if ($wat) {
                $messageId = $raw['message_id'] ?? $event->provider_message_id ?? null;

                // Idempotency: if same message_id already recorded, skip
                if ($messageId && $wat->message_id && $wat->message_id === $messageId) {
                    Log::info('whatsapp.process_event.idempotent', ['wat_id' => $wat->id_modal_wat, 'message_id' => $messageId]);
                    return;
                }

                $status = $raw['status'] ?? $event->status;

                if ($status === 'sent' || $status === 'delivered') {
                    $wat->estado = 1;
                    $wat->message_id = $messageId ?: $wat->message_id;
                    $wat->fecha = $raw['sentAt'] ?? now();
                } else {
                    $wat->estado = 0;
                    $wat->error = $raw['error'] ?? ($event->status ?? 'failed');
                }

                // attempts
                $wat->attempts = ($wat->attempts ?? 0) + 1;
                $wat->save();

                // Update campaign counters if campania_id provided
                $campaniaId = $raw['campania_id'] ?? $event->campania_id ?? null;
                if ($campaniaId) {
                    $camp = Campania::find($campaniaId);
                    if ($camp) {
                        if ($status === 'sent' || $status === 'delivered') {
                            $camp->increment('envios_exitosos', 1);
                        } else {
                            $camp->increment('envios_fallidos', 1);
                        }
                        $camp->decrement('envios_pendientes', 1);
                        $camp->save();
                    }
                }
            }

            // mark webhook event processed (optional)
            DB::table('whatsapp_webhook_events')->where('id', $event->id)->update(['updated_at' => now()]);
        });
    }
}
