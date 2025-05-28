<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="Permiso",
 *     type="object",
 *     title="Permiso",
 *     required={"nombre", "slug"},
 *     @OA\Property(
 *         property="id_permiso",
 *         type="integer",
 *         format="int64",
 *         description="ID único del permiso"
 *     ),
 *     @OA\Property(
 *         property="nombre",
 *         type="string",
 *         description="Nombre del permiso"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         description="Identificador único para el permiso"
 *     ),
 *     @OA\Property(
 *         property="descripcion",
 *         type="string",
 *         description="Descripción del permiso"
 *     )
 * )
 */
class Permiso extends Model
{
    protected $table = 'permisos';
    protected $primaryKey = 'id_permiso';
    public $timestamps = false;

    protected $fillable = ['nombre', 'slug', 'descripcion'];

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'role_permission', 'id_permiso', 'id_rol');
    }
}
