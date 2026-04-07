<?php

namespace App\Jobs;

use App\Models\WatModal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 60;

    public $watModal;
    public $data;
    public $productoName;

    public function __construct(WatModal $watModal, array $data, string $productoName)
    {
        $this->watModal = $watModal;
        $this->data = $data;
        $this->productoName = $productoName;
    }

    public function handle(): void
    {
        try {
            if ($this->watModal->estado === 1) {
                Log::info('WatModal ya procesado', ['id' => $this->watModal->id_modal_wat]);
                return;
            }

            // Obtener modalservicio para acceder a id_producto
            $modalservicio = \App\Models\modalservicios::find($this->watModal->id_modalservicio);
            if (!$modalservicio) {
                throw new \Exception('Modalservicio no encontrado');
            }

            // Obtener plantilla WhatsApp con imagen
            $plantilla = \App\Models\PlantillaWhatsapp::where('id_producto', $modalservicio->id_producto)
                ->where('numero_plantilla', $this->watModal->number_message)
                ->first();

            if (!$plantilla) {
                throw new \Exception('Plantilla WhatsApp no encontrada');
            }

            // Enviar y obtener message_id
            $messageId = $this->sendViaWhatsApp($modalservicio, $plantilla);

            // Actualizar como pendiente (esperando confirmación por webhook)
            // Guardamos message_id pero no marcamos como exitoso hasta recibir webhook final
            $this->updateWatModal(0, '', $messageId);
            Log::info('Mensaje WhatsApp enviado (pending confirmation)', [
                'id' => $this->watModal->id_modal_wat,
                'message_id' => $messageId,
            ]);

        } catch (\Exception $e) {
            Log::error('Error WhatsApp', ['error' => $e->getMessage()]);

            $attempts = ($this->watModal->attempts ?? 0) + 1;
            
            if ($attempts >= $this->tries) {
                $this->updateWatModal(0, 'Error: ' . substr($e->getMessage(), 0, 200), null, $attempts);
            } else {
                $this->watModal->update([
                    'error' => substr($e->getMessage(), 0, 200),
                    'attempts' => $attempts,
                    'fecha' => now(),
                ]);
                throw $e;
            }
        }
    }

    private function sendViaWhatsApp($modalservicio, $plantilla): string
    {
        $url = whatsapp_url('/api/whatsapp/send-message-image');
        $apiKey = whatsapp_api_key();

        // Enviar teléfono tal como viene en la base de datos
        $telefono = $this->data['telefono'] ?? '';

            $payload = [
                'telefono' => $telefono,
                // Keep templateOption for backward compatibility but include canonical fields
                'templateOption' => $this->watModal->number_message,
                'nombre' => $this->data['nombre'] ?? '',
                'fecha' => now()->format('Y-m-d'),
                'hora' => now()->format('H:i'),
                'productoName' => $this->productoName,
                // canonical contract fields expected by Node.js
                'mensaje' => $plantilla->mensaje ?? ($plantilla->contenido ?? ''),
                'image_url' => $plantilla->imagen_url ?? null,
                'id_modal_wat' => $this->watModal->id_modal_wat,
                'id_plantilla_whatsapp' => $plantilla->id_plantilla_whatsapp ?? $plantilla->id ?? null,
            ];

            // Log full payload for debugging/testing (will contain user phone/name)
            Log::info('WhatsApp payload enviado', [
                'url' => $url,
                'payload' => $payload,
            ]);

            $response = Http::withHeaders(['X-API-Key' => $apiKey])->post($url, $payload);

        if (!$response->successful()) {
            throw new \Exception(
                'WhatsApp error: ' . $response->status() . ' - ' . substr($response->body(), 0, 200)
            );
        }

        $responseData = $response->json();
        $messageId = $responseData['message_id'] ?? $responseData['id'] ?? null;

        return $messageId ?? 'unknown';
    }

    private function updateWatModal(int $estado, string $error = '', ?string $messageId = null, int $attempts = 0): void
    {
        $updateData = [
            'estado' => $estado,
            'error' => $error,
            'fecha' => now(),
        ];

        if ($messageId) {
            $updateData['message_id'] = $messageId;
        }

        if ($attempts > 0) {
            $updateData['attempts'] = $attempts;
        }

        $this->watModal->update($updateData);
    }
}
