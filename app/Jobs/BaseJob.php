<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de intentos antes de marcar como fallido
     */
    public $tries = 3;

    /**
     * Segundos de espera entre reintentos
     */
    public $backoff = 60;

    /**
     * Timeout en segundos para la ejecución del job
     */
    public $timeout = 120;

    /**
     * Nombre del job para logging
     */
    protected string $jobName;

    /**
     * Contador de intentos actuales para logging
     */
    protected int $currentAttempt = 0;

    public function __construct()
    {
        $this->jobName = class_basename(static::class);
    }

    /**
     * Ejecutar el job - debe implementarse en cada subclass
     */
    abstract public function handle(): void;

    /**
     * Log de información con contexto del job
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Log::info("[{$this->jobName}] {$message}", $context);
    }

    /**
     * Log de error con contexto del job
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error("[{$this->jobName}] {$message}", $context);
    }

    /**
     * Log de advertencia con contexto del job
     */
    protected function logWarning(string $message, array $context = []): void
    {
        Log::warning("[{$this->jobName}] {$message}", $context);
    }

    /**
     * Log de debug con contexto del job (para desarrollo)
     */
    protected function logDebug(string $message, array $context = []): void
    {
        Log::debug("[{$this->jobName}] {$message}", $context);
    }

    /**
     * Determina si se debe reintentar basado en el número de intentos
     * Útil para lógica condicional de reintentos
     */
    protected function shouldRetry(int $currentAttempt): bool
    {
        return $currentAttempt < $this->tries;
    }

    /**
     * Obtiene el delay en segundos para el próximo reintento
     * Puede ser sobrescrito en subclasses para lógica exponencial
     */
    protected function getRetryDelay(int $attempt): int
    {
        return $this->backoff;
    }

    /**
     * Manejo centralizado de fallos
     */
    protected function handleFailure(Throwable $e): void
    {
        $this->logError('Job execution failed', [
            'exception_class' => get_class($e),
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);
    }

    /**
     * Ejecutado cuando el job falla permanentemente (después de $tries intentos)
     */
    public function failed(Throwable $exception): void
    {
        $this->logError('Job permanently failed', [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
        ]);
    }

    /**
     * Retorna información del job para debugging
     */
    protected function getJobInfo(): array
    {
        return [
            'name' => $this->jobName,
            'tries' => $this->tries,
            'timeout' => $this->timeout,
            'backoff' => $this->backoff,
        ];
    }
}
