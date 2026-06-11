<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $token;

    public function __construct($user, $token)
    {
        $this->user  = $user;
        $this->token = $token;
    }

    public function build()
    {
        return $this->subject('Restablecimiento de Contraseña')
            ->view('mails.forgot-password')
            ->with([
                'nombre' => $this->user->name,
                'email'  => $this->user->email,
                'token'  => $this->token,
            ]);
    }
}
