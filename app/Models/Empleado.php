<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cloudinary\Cloudinary;
/**
 * @OA\Schema(
 *     schema="Empleado",
 *     type="object",
 *     title="Empleado",
 *     required={"nombre", "apellido", "email", "dni", "telefono", "id_user", "id_rol"},
 *     @OA\Property(
 *         property="id_empleado",
 *         type="integer",
 *         description="ID único del empleado"
 *     ),
 *     @OA\Property(
 *         property="nombre",
 *         type="string",
 *         description="Nombres del empleado"
 *     ),
 *     @OA\Property(
 *         property="apellido",
 *         type="string",
 *         description="Apellidos del empleado"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Correo electrónico del empleado"
 *     ),
 *     @OA\Property(
 *         property="dni",
 *         type="string",
 *         description="Documento Nacional de Identidad"
 *     ),
 *     @OA\Property(
 *         property="telefono",
 *         type="string",
 *         description="Número telefónico"
 *     ),
 *     @OA\Property(
 *         property="imagen_perfil",
 *         type="string",
 *         nullable=true,
 *         description="Nombre del archivo de imagen de perfil"
 *     ),
 *     @OA\Property(
 *         property="imagen_perfil_url",
 *         type="string",
 *         nullable=true,
 *         description="URL completa de la imagen de perfil"
 *     ),
 *     @OA\Property(
 *         property="id_user",
 *         type="integer",
 *         description="ID del usuario asociado"
 *     ),
 *     @OA\Property(
 *         property="id_rol",
 *         type="integer",
 *         description="ID del rol asociado"
 *     )
 * )
 */
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

    public function blog()
    {
        return $this->hasMany(Blog::class, 'id_empleado', 'id_empleado');
    }
}
