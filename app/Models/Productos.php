<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Productos extends Model
{
    protected $table = 'productos';

    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'id_empleado',
        'nombre',
        'descripcion',
        'path_main',
        'path_background',
        'path1', 'tituloimg1', 'descripcionimg1',
        'path2', 'tituloimg2', 'descripcionimg2',
        'path3', 'tituloimg3', 'descripcionimg3',
        'caracteristicas_descrip',
        'ventajas_descrip',
        'consumoenergetico_descrip',
        'iluminacion_descrip',
        'durabilidad_descrip',
        'estado'
    ];

    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}
