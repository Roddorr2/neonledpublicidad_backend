<?php

namespace App\Jobs;

use App\Models\Campania;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SendWhatsappCampaignJob implements ShouldQueue
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
        $this->campaniaId = $campaniaId;
        $this->chunkNumber = $chunkNumber;
        $this->recipients = $recipients;
        $this->message = $message;
        $this->imageUrl = $imageUrl;
        $this->idProducto = $idProducto;
        
        // Establecer la cola usando el método del trait Queueable
        $this->onQueue('whatsapp');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Preparar payload para WhatsApp Service
            $payload = [
                'campania_id' => $this->campaniaId,
                'chunk_number' => $this->chunkNumber,
                'recipients' => array_map(function($recipient) {
                    return [
                        'id_modalservicio' => $recipient->id_modalservicio,
                        'nombre' => $recipient->nombre,
                        'telefono' => '51' . $recipient->telefono,
                        'number_message' => $recipient->number_message
                    ];
                }, $this->recipients),
                'message' => $this->message,
                'image_url' => $this->imageUrl,
                'id_producto' => $this->idProducto
            ];

            // Enviar a WhatsApp Service
            $response = Http::timeout(60)
                ->post(config('services.whatsapp.url') . '/api/send-campaign-batch', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Actualizar estadísticas de campaña
                $campania = Campania::find($this->campaniaId);
                if ($campania) {
                    $campania->increment('envios_exitosos', $responseData['exitosos'] ?? count($this->recipients));
                    $campania->increment('envios_fallidos', $responseData['fallidos'] ?? 0);
                    $campania->decrement('envios_pendientes', count($this->recipients));

                    // Si no hay más pendientes, marcar como completada
                    if ($campania->envios_pendientes <= 0) {
                        $campania->update([
                            'estado' => 'completada',
                            'fecha_fin' => now()
                        ]);
                    }
                }

                Log::info('Chunk enviado exitosamente', [
                    'campania_id' => $this->campaniaId,
                    'chunk_number' => $this->chunkNumber,
                    'recipients_count' => count($this->recipients)
                ]);
            } else {
                throw new \Exception('Error en WhatsApp Service: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Error en SendWhatsappCampaignJob', [
                'campania_id' => $this->campaniaId,
                'chunk_number' => $this->chunkNumber,
                'error' => $e->getMessage()
            ]);

            // Actualizar campaña con error
            $campania = Campania::find($this->campaniaId);
            if ($campania) {
                $campania->increment('envios_fallidos', count($this->recipients));
                $campania->decrement('envios_pendientes', count($this->recipients));
                $campania->update(['estado' => 'error']);
            }

            throw $e;
        }
    }
}
