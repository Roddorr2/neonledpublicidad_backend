<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Cloudinary\Cloudinary;
use App\Traits\HasFullName;
use App\Traits\HasContactInfo;
class   Empleado extends Model
{
    use HasFactory, SoftDeletes, HasFullName, HasContactInfo;

    protected $table = 'empleados';
    protected $primaryKey = 'id_empleado';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'dni',
        'telefono',
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

    public function hasSpecialAccess(): bool
    {
        return cache()->remember("special-access-{$this->id_empleado}", 3600, function () {
            $allowedIds = config('special_access.employee_ids', []);
            return in_array($this->id_empleado, $allowedIds);
        });
    }

    // public function blog()
    // {
    //     return $this->hasMany(Blog::class, 'id_empleado', 'id_empleado');
    // }

    public function card(): HasMany
    {
        return $this->hasMany(Card::class, 'id_empleado', 'id_empleado');
    }

    public function producto(): HasMany
    {
        return $this->hasMany(Productos::class, 'id_empleado', 'id_empleado');
    }

    public function blogAuditoria(): HasMany
    {
        return $this->hasMany(BlogAuditoria::class, 'id_empleado', 'id_empleado');
    }

    public function puedeAcceder(): bool
    {
        return $this->hasSpecialAccess();
    }
}
