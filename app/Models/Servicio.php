<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        return $this->hasMany(Reclamacion::class, 'id_servicio');
    }

    public function campanias(){
        return $this->hasMany(Campania::class, 'id_servicio', 'id_servicio');
    }
}
