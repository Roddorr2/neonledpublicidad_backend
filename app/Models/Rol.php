<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class, 'id_rol', 'id_rol');
    }

    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'role_permission', 'id_rol', 'id_permiso');
    }

    public function getNombre(): string
    {
        return $this->nombre ?? 'Sin rol';
    }

    public function tieneEmpleados(): bool
    {
        return $this->empleados()->exists();
    }
}
