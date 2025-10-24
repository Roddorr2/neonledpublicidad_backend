<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cloudinary\Cloudinary;
class   Empleado extends Model
{
    use HasFactory;

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


    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function hasSpecialAccess()
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

    public function card()
    {
        return $this->hasMany(Card::class, 'id_empleado', 'id_empleado');
    }

    public function producto()
    {
        return $this->hasMany(Producto::class, 'id_empleado', 'id_empleado');
    }
}
