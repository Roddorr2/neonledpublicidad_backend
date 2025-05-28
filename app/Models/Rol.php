<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @OA\Schema(
 *     schema="Rol",
 *     type="object",
 *     title="Rol",
 *     required={"nombre"},
 *     @OA\Property(
 *         property="id_rol",
 *         type="integer",
 *         format="int64",
 *         description="ID único del rol"
 *     ),
 *     @OA\Property(
 *         property="nombre",
 *         type="string",
 *         description="Nombre del rol"
 *     )
 * )
 */
class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'id_rol', 'id_rol');
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'role_permission', 'id_rol', 'id_permiso');
    }
}
