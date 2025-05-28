<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reclamacion extends Model
{
    protected $table = 'reclamaciones';
    protected $primaryKey = 'id_reclamacion';
    public $timestamps = false;
/**
 * @OA\Schema(
 *     schema="Reclamacion",
 *     type="object",
 *     title="Reclamacion",
 *     required={"nombre", "apellido", "email", "telefono", "departamento", "direccion", "distrito", "id_servicio", "fechaIncidente", "descripcionServicio", "checkReclamoForm", "aceptaPoliticaPrivacidad", "fechaReclamo"},
 *     @OA\Property(property="id_reclamacion", type="integer", readOnly=true),
 *     @OA\Property(property="nombre", type="string"),
 *     @OA\Property(property="apellido", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="telefono", type="string"),
 *     @OA\Property(property="departamento", type="string"),
 *     @OA\Property(property="direccion", type="string"),
 *     @OA\Property(property="distrito", type="string"),
 *     @OA\Property(property="id_servicio", type="integer", description="ID del servicio asociado"),
 *     @OA\Property(property="fechaIncidente", type="string", format="date"),
 *     @OA\Property(property="montoReclamado", type="number", format="float", nullable=true),
 *     @OA\Property(property="descripcionServicio", type="string"),
 *     @OA\Property(property="checkReclamoForm", type="boolean", description="Indica si es reclamo (true) o queja (false)"),
 *     @OA\Property(property="aceptaPoliticaPrivacidad", type="boolean"),
 *     @OA\Property(property="fechaReclamo", type="string", format="date"),
 *     @OA\Property(property="estadoReclamo", type="string", nullable=true)
 * )
 */
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'telefono',
        'departamento',
        'direccion',
        'distrito',
        'id_servicio',
        'fechaIncidente',
        'montoReclamado',
        'descripcionServicio',
        'checkReclamoForm',
        'aceptaPoliticaPrivacidad',
        'fechaReclamo',
        'estadoReclamo',
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio');
    }
}
