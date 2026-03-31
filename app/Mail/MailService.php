<?php

namespace App\Mail;

use App\Models\PlantillaEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class MailService extends Mailable
{
    use Queueable, SerializesModels;
    public int $number_message;
    public $data;
    public $id_producto;

    public function __construct($number_message, $data, $id_producto)
    {
        $this->number_message = $number_message;
        $this->data = $data;
        $this->id_producto = $id_producto;
    }

    public function build()
    {
        $mail_content = $this->resolveMailContent();
        $configPath = "email_content.services.{$this->id_producto}.messages.{$this->number_message}";

        if (empty($mail_content) || !is_array($mail_content) || !isset($mail_content['subject']) || !isset($mail_content['message'])) {
            Log::error("ERROR DE CONFIGURACIÓN DE CORREO: Contenido no encontrado o incompleto.", [
                'config_path_intentada' => $configPath,
                'data_recibida' => $this->data,
            ]);

            throw new \Exception("Fallo al construir MailService. Contenido no encontrado en: " . $configPath);
        }

        $image = null;
        if (!empty($mail_content['image'])) {
            $image = config('app.url') . $mail_content['image'];
        }

        return $this->subject($mail_content['subject'])
                    ->view('mails.modal')
                    ->with([
                        'data' => $this->data,
                        'send_message' => $mail_content['message'],
                        'title' => $mail_content['title'] ?? $mail_content['subject'],
                        'image' => $image,
                        'extra_message' => $mail_content['extra'] ?? null,
                    ]);
    }

    private function resolveMailContent(): ?array
    {
        $plantilla = PlantillaEmail::where('id_producto', $this->id_producto)
            ->where('numero_plantilla', $this->number_message)
            ->first();

        if ($plantilla) {
            return [
                'subject' => $plantilla->asunto,
                'title' => $plantilla->encabezado,
                'message' => $plantilla->mensaje,
                'image' => $plantilla->imagen_url,
                'extra' => null,
            ];
        }

        $configPath = "email_content.services.{$this->id_producto}.messages.{$this->number_message}";
        return Config::get($configPath);
    }
}
