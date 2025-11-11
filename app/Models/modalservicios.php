<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class modalservicios extends Model
{
    protected $table = 'modalservicios';
    protected $primaryKey = 'id_modalservicio';

    protected $fillable = [
        'nombre',
        'telefono',
        'correo',
        'id_producto',
        'fecha',
        'estado'
    ];

    public $timestamps = false;

    // COMENTADO HASTA QUE LA TABLA PRODUCTOS SE USE
    // public function producto()
    // {
    //     return $this->belongsTo(Producto::class, 'id_producto');
    // }

    public function watModal()
    {
        return $this->hasMany(WatModal::class, 'id_modalservicio', 'id_modalservicio');
    }
    public function emailModal()
    {
        return $this->hasMany(EmailModal::class, 'id_modalservicio', 'id_modalservicio');
    }

}
