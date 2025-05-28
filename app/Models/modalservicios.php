<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="ModalServicio",
 *     type="object",
 *     title="ModalServicio",
 *     required={"nombre", "telefono", "correo", "id_servicio", "fecha", "estado"},
 *     @OA\Property(
 *         property="id_modalservicio",
 *         type="integer",
 *         format="int64",
 *         description="ID único del modal de servicio"
 *     ),
 *     @OA\Property(
 *         property="nombre",
 *         type="string",
 *         description="Nombre del cliente"
 *     ),
 *     @OA\Property(
 *         property="telefono",
 *         type="string",
 *         description="Número de teléfono del cliente"
 *     ),
 *     @OA\Property(
 *         property="correo",
 *         type="string",
 *         format="email",
 *         description="Correo electrónico del cliente"
 *     ),
 *     @OA\Property(
 *         property="id_servicio",
 *         type="integer",
 *         description="ID del servicio solicitado"
 *     ),
 *     @OA\Property(
 *         property="fecha",
 *         type="string",
 *         format="date-time",
 *         description="Fecha y hora de la solicitud"
 *     ),
 *     @OA\Property(
 *         property="estado",
 *         type="string",
 *         description="Estado de la solicitud"
 *     ),
 *     @OA\Property(
 *         property="wat_modal",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/WatModal"),
 *         description="Mensajes de WhatsApp relacionados con esta solicitud"
 *     ),
 *     @OA\Property(
 *         property="email_modal",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/EmailModal"),
 *         description="Intentos de envío de email relacionados con esta solicitud"
 *     )
 * )
 */

class modalservicios extends Model
{
    protected $table = 'modalservicios';
    protected $primaryKey = 'id_modalservicio';

    protected $fillable = [
        'nombre',
        'telefono',
        'correo',
        'id_servicio',
        'fecha',
        'estado'
    ];

    public $timestamps = false;

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio');
    }

    public function watModal()
    {
        return $this->hasMany(WatModal::class, 'id_modalservicio', 'id_modalservicio');
    }
    public function emailModal()
    {
        return $this->hasMany(EmailModal::class, 'id_modalservicio', 'id_modalservicio');
    }

}
