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

    public $watModal;
    public $data;
    public $productoName;

    public function __construct(WatModal $watModal, array $data, string $productoName)
    {
        $this->watModal = $watModal;
        $this->data = $data;
        $this->productoName = $productoName;
    }

    public function handle()
    {
        try {
            if (!$this->watModal) {
                Log::error('WatModal no encontrado');
                return;
            }

            // Formatear el número: prefijo 51, sin espacios
            $telefono = $this->data['telefono'];
            $telefono = preg_replace('/\s+/', '', $telefono); // quitar espacios
            if (strpos($telefono, '51') !== 0) {
                $telefono = '51' . $telefono;
            }
            $templateOption = optional($this->watModal->modalservicio)->id_producto;
            if (!$templateOption) {
                Log::error('modalservicio o id_producto no encontrado', [
                    'id_modal_wat' => $this->watModal->id_modal_wat,
                    'id_modalservicio' => $this->watModal->id_modalservicio,
                ]);
                return;
            }
            $response = Http::withHeaders([
                'x-api-key' => config('services.whatsapp.apikey')
            ])->post(config('services.whatsapp.url') . '/api/whatsapp/send-message-image', [
                'nombre' => $this->data['nombre'],
                'templateOption' => $templateOption,
                'messageType' => $this->watModal->number_message,
                'telefono' => $telefono,
            ]);

            if ($response->failed()) {
                throw new \Exception($response->body());
            }

            $this->watModal->update([
                'estado' => 1,
                'fecha' => now(),
            ]);

        } catch (\Exception $e) {
            $this->watModal->update([
                'estado' => 1,
                'error' => $e->getMessage(),
                'fecha' => now(),
            ]);

            Log::error('Error WhatsApp', [
                'id_modal_wat' => $this->watModal->id_modal_wat,
                'error' => $e->getMessage()
            ]);
        }
    }
}
