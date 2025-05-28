<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Relations\HasMany;
/**
 * @OA\Schema(
 *     schema="Servicio",
 *     type="object",
 *     title="Servicio",
 *     required={"id_servicio", "nombre", "descripcion"},
 *     @OA\Property(
 *         property="id_servicio",
 *         type="integer",
 *         description="ID único del servicio",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="nombre",
 *         type="string",
 *         description="Nombre del servicio"
 *     ),
 *     @OA\Property(
 *         property="descripcion",
 *         type="string",
 *         description="Descripción del servicio"
 *     )
 * )
 */

class Servicio extends Model
{
    protected $table = 'servicios';

    protected $primaryKey = 'id_servicio';

    protected $fillable = [
        'id_servicio',
        'nombre',
        'descripcion'
    ];

    public $timestamps = false;

    public function reclamacion(){
        return $this->belongsTo(Reclamacion::class, 'id_servicio');
    }
}
