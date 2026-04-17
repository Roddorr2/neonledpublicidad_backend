<?php

namespace App\Traits;

trait HasContactInfo
{
    public function getContacto(): string
    {
        return "{$this->email} | {$this->telefono}";
    }
}
