<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantillaWhatsapp extends Model
{
    protected $table = 'plantillas_whatsapp';

    protected $primaryKey = 'id_plantilla_whatsapp';

    public $incrementing = true;

    protected $fillable = [
        'id_producto', 'numero_plantilla', 'nombre', 'mensaje', 'imagen_url', 'imagen_public_id', 'created_by', 'updated_by',
    ];
}
