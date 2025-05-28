<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="Contactanos",
 *     type="object",
 *     title="Contacto",
 *     description="Esquema que representa la información de un contacto recibido desde el formulario de contacto.",
 *     required={"nombre", "apellido", "telefono", "distrito", "email", "tipo_reclamo", "mensaje"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nombre", type="string", maxLength=255, example="Juan"),
 *     @OA\Property(property="apellido", type="string", maxLength=255, example="Pérez"),
 *     @OA\Property(property="telefono", type="string", maxLength=20, example="999999999"),
 *     @OA\Property(property="distrito", type="string", maxLength=255, example="Lima"),
 *     @OA\Property(property="email", type="string", format="email", example="juan.perez@example.com"),
 *     @OA\Property(property="tipo_reclamo", type="string", maxLength=1050, example="Consulta general"),
 *     @OA\Property(property="mensaje", type="string", maxLength=1050, example="Mensaje de prueba"),
 *     @OA\Property(property="estado", type="integer", example=0),
 *     @OA\Property(property="fecha_hora", type="string", format="date-time", example="2025-05-22T15:00:00Z"),
 *     @OA\Property(property="fecha_hora_actualizacion", type="string", format="date-time", example="2025-05-23T12:00:00Z")
 * )
 */
class Contactanos extends Model
{
    use HasFactory;

    protected $table = 'contactanos';
    protected $primaryKey = 'id_contactanos';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'apellido',
        'telefono',
        'distrito',
        'email',
        'detalle_reclamacion',
        'mensaje',
        'estado',
        'fecha_hora',
        'fecha_hora_actualizacion',
    ];
    
}
