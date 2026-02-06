<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campania extends Model
{
    protected $table = 'campanias_whatsapp';

    protected $primaryKey = 'id_campania';

    protected $fillable = [
        'id_servicio',
        'parrafo',
        'imagen_url',
        'estado',
        'total_destinatarios',
        'envios_exitosos',
        'envios_fallidos',
        'envios_pendientes',
        'fecha_inicio',
        'fecha_fin'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'total_destinatarios' => 'integer',
        'envios_exitosos' => 'integer',
        'envios_fallidos' => 'integer',
        'envios_pendientes' => 'integer',
    ];

    /**
     * Relación con Servicio
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }
}
