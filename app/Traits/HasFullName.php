<?php

namespace App\Traits;

trait HasFullName
{
    public function getNombreCompleto(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }
}
