<?php

namespace App\Jobs;

use App\Models\Campania;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de reintentos antes de marcar como fallido
     */
    public $tries = 3;

    public $campaniaId;

    public $chunkNumber;

    public $recipients;

    public $message;

    public $imageUrl;

    public $idProducto;

    /**
     * Create a new job instance.
     */
    public function __construct($campaniaId, $chunkNumber, $recipients, $message, $imageUrl, $idProducto)
    {
        $this->campaniaId  = $campaniaId;
        $this->chunkNumber = $chunkNumber;
        $this->recipients  = $recipients;
        $this->message     = $message;
        $this->imageUrl    = $imageUrl;
        $this->idProducto  = $idProducto;

        // Establecer la cola usando el método del trait Queueable
        $this->onQueue('whatsapp');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Instead of sending directly, create whatsapp_chunks via PlannerService
        try {
            $recipientsArray = array_map(function ($recipient) {
                // normalize object or array
                $telefono = is_object($recipient) ? ($recipient->telefono ?? null) : ($recipient['telefono'] ?? null);
                $id       = is_object($recipient) ? ($recipient->id_modalservicio ?? ($recipient->id ?? null)) : ($recipient['id_modalservicio'] ?? ($recipient['id'] ?? null));

                return ['id_modalservicio' => $id, 'telefono' => $telefono, 'nombre' => is_object($recipient) ? ($recipient->nombre ?? null) : ($recipient['nombre'] ?? null)];
            }, $this->recipients);

            $chunks = \App\Services\PlannerService::planCampaign($this->campaniaId, $recipientsArray);

            Log::info('SendWhatsAppCampaignJob planned chunks', ['campania_id' => $this->campaniaId, 'chunks' => count($chunks)]);
        } catch (\Exception $e) {
            Log::error('Error planning campaign chunks', ['campania_id' => $this->campaniaId, 'error' => $e->getMessage()]);
            // update campaign to error state
            $campania = Campania::find($this->campaniaId);
            if ($campania) {
                $campania->update(['estado' => 'error']);
            }
            throw $e;
        }
    }
}
