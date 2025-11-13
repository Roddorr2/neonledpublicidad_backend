<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

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
        $configPath = "email_content.services.{$this->id_producto}.messages.{$this->number_message}";
        $mail_content = Config::get($configPath);

        if (empty($mail_content) || !is_array($mail_content) || !isset($mail_content['subject']) || !isset($mail_content['message'])) {
            Log::error("ERROR DE CONFIGURACIÓN DE CORREO: Contenido no encontrado o incompleto.", [
                'config_path_intentada' => $configPath,
                'data_recibida' => $this->data,
            ]);

            throw new \Exception("Fallo al construir MailService. Contenido no encontrado en: " . $configPath);
        }

        return $this->subject($mail_content['subject'])
                    ->view('mails.modal')
                    ->with([
                        'data' => $this->data,
                        'send_message' => $mail_content['message'],
                        'title' => $mail_content['title'] ?? $mail_content['subject'], // Usar Subject como fallback
                        'image' => $mail_content['image'] ?? null,
                    ]);
    }
}
