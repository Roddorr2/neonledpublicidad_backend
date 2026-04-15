<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{

    use HasFactory;
    protected $table = 'clientes';
    public $timestamps = false;
    // protected $primaryKey = 'id_cliente';
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'telefono',
        'distrito',
        'imagen_perfil',
        'imagen_perfil_url',
        'id_user',
        'id_rol',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function propuestas(): HasMany
    {
        return $this->hasMany(Propuesta::class, 'id_cliente', 'id');
    }

    public function getNombreCompleto(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }

    public function getContacto(): string
    {
        return "{$this->email} | {$this->telefono}";
    }

    public function tienePropuestas(): bool
    {
        return $this->propuestas()->exists();
    }
}
