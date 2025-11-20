<?php

namespace App\Jobs;

use App\Mail\MailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEmailJob implements ShouldQueue
{
    use Queueable;

    public $correo;
    public $data;
    public $idProducto;
    public $tipoCorreo;

    public function __construct($correo, $data, $idProducto, $tipoCorreo)
    {
        $this->correo = $correo;
        $this->data = $data;
        $this->idProducto = $idProducto;
        $this->tipoCorreo = $tipoCorreo;
    }

    public function handle()
    {
        Mail::to($this->correo)->send(
            new MailService($this->tipoCorreo, $this->data, $this->idProducto)
        );
    }
}
