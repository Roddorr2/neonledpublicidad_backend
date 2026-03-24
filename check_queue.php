<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║           PANEL DE COLAS y CONTADOR DE ENVÍOS WhatsApp        ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// SECCIÓN 1: Estado General de Colas
echo "📊 ESTADO DE COLAS:\n";
echo "─────────────────────────────────────────────────\n";
$jobsPending = DB::table('jobs')->count();
$jobsFailed = DB::table('failed_jobs')->count();
printf("  Pendientes: %d | Fallidos: %d\n\n", $jobsPending, $jobsFailed);

// SECCIÓN 2: Campañas Activas y Contadores
echo "📈 CAMPAÑAS EN PROGRESO (Contadores de Envíos):\n";
echo "─────────────────────────────────────────────────\n";
$campaigns = DB::table('campanias_whatsapp')
    ->whereIn('estado', ['en_proceso', 'pausada_hasta_mañana', 'completada'])
    ->orderBy('fecha_inicio', 'desc')
    ->limit(10)
    ->get();

if ($campaigns->isNotEmpty()) {
    foreach ($campaigns as $camp) {
        $total = (int) $camp->total_destinatarios;
        $exitosos = (int) $camp->envios_exitosos;
        $fallidos = (int) $camp->envios_fallidos;
        $pendientes = (int) $camp->envios_pendientes;
        $consumidos = $exitosos + $fallidos;
        $porcentaje = $total > 0 ? round(($consumidos / $total) * 100, 1) : 0;
        
        echo sprintf(
            "  ID %d | Estado: %-15s | Progreso: %d/%d (%.1f%%)\n",
            $camp->id_campania,
            $camp->estado,
            $consumidos,
            $total,
            $porcentaje
        );
        echo sprintf(
            "    ├─ ✅ Exitosos: %d | ❌ Fallidos: %d | ⏳ Pendientes: %d\n",
            $exitosos,
            $fallidos,
            $pendientes
        );
        echo sprintf(
            "    ├─ Milestone: %d%% | Version: %d | Inicio: %s\n",
            (int) ($camp->progress_milestone ?? 0),
            (int) ($camp->progress_version ?? 0),
            $camp->fecha_inicio
        );
        echo "    └─\n";
    }
} else {
    echo "  (Sin campañas en progreso)\n";
}

// SECCIÓN 3: Jobs Pendientes por Queue
echo "\n📋 JOBS PENDIENTES POR QUEUE:\n";
echo "─────────────────────────────────────────────────\n";
$jobsByQueue = DB::table('jobs')
    ->selectRaw('queue, COUNT(*) as count')
    ->groupBy('queue')
    ->get();

if ($jobsByQueue->isNotEmpty()) {
    foreach ($jobsByQueue as $q) {
        echo sprintf("  Queue '%s': %d jobs\n", $q->queue ?: 'default', $q->count);
    }
} else {
    echo "  (Sin jobs pendientes)\n";
}

// SECCIÓN 4: Últimos Jobs (muestra detalles)
echo "\n🔍 ÚLTIMOS 5 JOBS PENDIENTES:\n";
echo "─────────────────────────────────────────────────\n";
$jobs = DB::table('jobs')->orderBy('id', 'desc')->take(5)->get();
foreach ($jobs as $j) {
    $payload = json_decode($j->payload, true);
    echo sprintf(
        "  ID: %d | Queue: %-15s | Job: %s\n",
        $j->id,
        $j->queue ?: 'default',
        $payload['displayName'] ?? '?'
    );
    echo sprintf(
        "    Attempts: %d | Created: %s\n",
        $j->attempts,
        date('Y-m-d H:i:s', $j->created_at)
    );
}

if ($jobs->isEmpty()) {
    echo "  (Sin jobs pendientes)\n";
}

// SECCIÓN 5: Jobs Fallidos
echo "\n❌ ÚLTIMOS 5 JOBS FALLIDOS:\n";
echo "─────────────────────────────────────────────────\n";
$failed = DB::table('failed_jobs')->orderBy('id', 'desc')->take(5)->get();
foreach ($failed as $f) {
    $payload = json_decode($f->payload, true);
    echo sprintf(
        "  ID: %d | Queue: %-15s | Job: %s\n",
        $f->id,
        $f->queue ?: 'default',
        $payload['displayName'] ?? '?'
    );
    echo sprintf(
        "    Failed at: %s\n",
        $f->failed_at
    );
    $exception = substr($f->exception, 0, 150);
    echo sprintf(
        "    Error: %s...\n",
        $exception
    );
}

if ($failed->isEmpty()) {
    echo "  (Sin jobs fallidos)\n";
}

// SECCIÓN 6: Webhook Events Pending
echo "\n📡 ÚLTIMOS WEBHOOK EVENTS PROCESADOS:\n";
echo "─────────────────────────────────────────────────\n";
$events = DB::table('whatsapp_webhook_events')
    ->orderBy('id', 'desc')
    ->take(5)
    ->get();

foreach ($events as $e) {
    echo sprintf(
        "  Event ID: %d | Chunk: %d | Recipient: %s | Status: %s | Created: %s\n",
        $e->id,
        $e->chunk_id,
        $e->recipient,
        $e->status,
        date('Y-m-d H:i:s', strtotime($e->created_at))
    );
}

if ($events->isEmpty()) {
    echo "  (Sin eventos)\n";
}

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║ Panel actualizado: " . date('Y-m-d H:i:s') . "                                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
