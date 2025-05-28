<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="EmailModal",
 *     type="object",
 *     title="EmailModal",
 *     required={"estado", "id_modalservicio", "number_message", "fecha"},
 *     @OA\Property(
 *         property="id_modal_email",
 *         type="integer",
 *         format="int64",
 *         description="ID único del intento de envío de email"
 *     ),
 *     @OA\Property(
 *         property="estado",
 *         type="boolean",
 *         description="Estado del envío (true si fue exitoso, false si falló)"
 *     ),
 *     @OA\Property(
 *         property="error",
 *         type="string",
 *         nullable=true,
 *         description="Mensaje de error en caso de fallo en el envío"
 *     ),
 *     @OA\Property(
 *         property="id_modalservicio",
 *         type="integer",
 *         format="int64",
 *         description="ID del modalservicio relacionado"
 *     ),
 *     @OA\Property(
 *         property="number_message",
 *         type="string",
 *         description="Identificador o número del mensaje enviado"
 *     ),
 *     @OA\Property(
 *         property="fecha",
 *         type="string",
 *         format="date-time",
 *         description="Fecha y hora del intento de envío"
 *     )
 * )
 */

class EmailModal extends Model
{
    use HasFactory;

    protected $table = 'modal_emails';
    protected $primaryKey = 'id_modal_email';
    protected $fillable = [
        'estado',
        'error',
        'id_modalservicio',
        'number_message',
        'fecha',
    ];
    protected $casts = [
        'estado' => 'boolean',
    ];

    public $timestamps = false;

    public function modalServicio(){
        return $this->belongsTo(modalservicios::class,'id_modal_servicio');
    }
}
