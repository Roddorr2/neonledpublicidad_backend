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
        'user_id', // ← NUEVO para auditoría
        'parrafo',
        'imagen_url',
        'estado',
        'total_destinatarios',
        'envios_exitosos',
        'envios_fallidos',
        'envios_pendientes',
        'progress_milestone',
        'progress_version',
        'progress_milestone_updated_at',
        'fecha_inicio',
        'fecha_fin'
    ];
    /**
     * Relación con el usuario creador (auditoría)
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'total_destinatarios' => 'integer',
        'envios_exitosos' => 'integer',
        'envios_fallidos' => 'integer',
        'envios_pendientes' => 'integer',
        'progress_milestone' => 'integer',
        'progress_version' => 'integer',
        'progress_milestone_updated_at' => 'datetime',
    ];

    /**
     * Relación con Servicio (legado)
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }

    /**
     * Relación con Producto (id_servicio almacena id_producto)
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Productos::class, 'id_servicio', 'id_producto');
    }

    /**
     * Obtiene el porcentaje de progreso de la campaña
     */
    public function getProgressPercentage(): float
    {
        if ($this->total_destinatarios === 0) {
            return 0;
        }
        
        return round((($this->envios_exitosos + $this->envios_fallidos) / $this->total_destinatarios) * 100, 2);
    }

    public static function getActiveCampaign(): ?self
    {
        return self::whereIn('estado', ['en_proceso', 'pausada_hasta_mañana'])
            ->orderBy('created_at', 'asc')
            ->first();
    }

    public function canBeStarted(): bool
    {
        if (!in_array($this->estado, ['borrador', 'pendiente', 'pausada_hasta_mañana'])) {
            return false;
        }

        $active = self::getActiveCampaign();
        return !$active || $active->id_campania === $this->id_campania;
    }
}
