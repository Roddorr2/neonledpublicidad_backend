<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Reclamacion extends Model
{
    protected $table = 'reclamaciones';

    protected $primaryKey = 'id_reclamacion';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'apellido',
        'tipoDocumento',
        'dni',
        'email',
        'telefono',
        'departamento',
        'direccion',
        'distrito',
        'id_servicio',
        'tipoReclamo',
        'fechaIncidente',
        'montoReclamado',
        'descripcionServicio',
        'checkReclamoForm',
        'aceptaPoliticaPrivacidad',
        'fechaReclamo',
        'estadoReclamo',
    ];
    protected $casts = [
        'fechaReclamo' => 'datetime',
        'fechaIncidente' => 'datetime', 
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio');
    }
}
