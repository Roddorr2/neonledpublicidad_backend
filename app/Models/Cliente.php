<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

      public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function propuestas()
    {
        return $this->hasMany(Propuesta::class, 'id_cliente', 'id');
    }
}
