<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reclamacion extends Model
{
    protected $table = 'reclamaciones';

    protected $primaryKey = 'id_reclamacion';

    public $timestamps = false;

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
