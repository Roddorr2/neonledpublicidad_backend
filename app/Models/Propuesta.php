<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Propuesta extends Model
{
    protected $fillable = [
        'id_cliente',
        'nombre',
        'descripcion'
    ];
    protected $appends = ['cantidad_imagenes', 'cantidad_videos','fecha_formateada'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id');
    }
    public function getCantidadImagenesAttribute(): int
    {
        $path = "cliente/{$this->id_cliente}/propuestas/{$this->id}/imagenes/";
        return count(Storage::disk('public')->files($path));
    }
    public function getCantidadVideosAttribute(): int
    {
        $path = "cliente/{$this->id_cliente}/propuestas/{$this->id}/videos/";
        return count(Storage::disk('public')->files($path));
    }
    public function getFechaFormateadaAttribute(): string|null
    {
        return $this->created_at ? $this->created_at->format('d/m/Y') : null;
    }

    public function estaCompleta(): bool
    {
        return !empty($this->nombre) && !empty($this->descripcion);
    }
}
