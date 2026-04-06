<?php

namespace App\Jobs;

use App\Mail\MailService;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEmailJob extends BaseJob
{
    public $correo;
    public $data;
    public $idProducto;
    public $tipoCorreo;

    public function __construct($correo, $data, $idProducto, $tipoCorreo)
    {
        parent::__construct();
        
        $this->correo = $correo;
        $this->data = $data;
        $this->idProducto = $idProducto;
        $this->tipoCorreo = $tipoCorreo;
    }

    public function handle(): void
    {
        try {
            Mail::to($this->correo)->send(
                new MailService($this->tipoCorreo, $this->data, $this->idProducto)
            );
            
            $this->logInfo('Email sent successfully', [
                'email' => $this->correo,
                'tipo' => $this->tipoCorreo,
            ]);
        } catch (Throwable $e) {
            $this->handleFailure($e);
            throw $e;
        }
    }
}
