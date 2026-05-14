<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantillaEmail extends Model
{
    protected $table = 'plantillas_email';

    protected $primaryKey = 'id_plantilla_email';

    public $incrementing = true;

    protected $fillable = [
        'id_producto',
        'numero_plantilla',
        'nombre',
        'asunto',
        'encabezado',
        'imagen_url',
        'mensaje',
        'mensaje_boton',
        'url_boton',
        'footer',
        'red_facebook',
        'red_tiktok',
        'red_instagram',
        'red_linkedin',
        'created_by',
        'updated_by',
    ];
}
