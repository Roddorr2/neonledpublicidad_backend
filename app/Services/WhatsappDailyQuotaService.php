<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class WhatsappDailyQuotaService
{
    /**
     * Estados finales que cuentan como envíos confirmados por webhook
     */
    private const FINAL_STATES = ['sent', 'delivered', 'failed', 'undelivered', 'rejected', 'expired'];

    /**
     * Obtiene el total de envíos confirmados por webhook HOY
     * 
     * @param Carbon|null $date Fecha a contar (default: hoy)
     * @return int Total de envíos confirmados
     */
    public function getEnviosDia(?Carbon $date = null): int
    {
        $date = $date ?? now();

        return DB::table('whatsapp_webhook_events')
            ->whereDate('created_at', $date->toDateString())
            ->whereIn('status', self::FINAL_STATES)
            ->count();
    }

    /**
     * Obtiene el límite diario de envíos
     * 
     * @return int Límite configurado
     */
    public function getLimiteDiario(): int
    {
        return (int) config('whatsapp.daily_limit', 50);
    }

    /**
     * Obtiene el porcentaje de cuota consumida del día
     * 
     * @return float Porcentaje de 0 a 100
     */
    public function getPercentajeConsumido(): float
    {
        $envios = $this->getEnviosDia();
        $limite = $this->getLimiteDiario();

        return $limite > 0 ? min(100, ($envios / $limite) * 100) : 0;
    }

    /**
     * Obtiene envíos restantes del día
     * 
     * @return int Envíos disponibles
     */
    public function getEnviosRestantes(): int
    {
        $envios = $this->getEnviosDia();
        $limite = $this->getLimiteDiario();

        return max(0, $limite - $envios);
    }

    /**
     * Verifica si se alcanzó el límite diario
     * 
     * @return bool True si se alcanzó o superó el límite
     */
    public function isLimitReached(): bool
    {
        return $this->getEnviosDia() >= $this->getLimiteDiario();
    }

    /**
     * Obtiene estadísticas completas del día
     * 
     * @return array Array con todas las métricas
     */
    public function getDailyStats(): array
    {
        $enviosDia = $this->getEnviosDia();
        $limiteDiario = $this->getLimiteDiario();
        $porcentaje = $this->getPercentajeConsumido();
        $restantes = $this->getEnviosRestantes();

        return [
            'envios_hoy' => $enviosDia,
            'limite_diario' => $limiteDiario,
            'porcentaje' => round($porcentaje, 2),
            'restantes' => $restantes,
            'limite_alcanzado' => $this->isLimitReached(),
        ];
    }

    /**
     * Obtiene el conteo de envíos por estado (para análisis)
     * 
     * @return array Array con conteo por status
     */
    public function getEnviosPorEstado(): array
    {
        return DB::table('whatsapp_webhook_events')
            ->whereDate('created_at', now()->toDateString())
            ->whereIn('status', self::FINAL_STATES)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Verifica si hay quota disponible para enviar
     * 
     * @param int $cantidad Cantidad de envíos a verificar
     * @return bool True si hay suficiente cuota
     */
    public function hasQuota(int $cantidad = 1): bool
    {
        return ($this->getEnviosDia() + $cantidad) <= $this->getLimiteDiario();
    }

}
