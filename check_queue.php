<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ESTADO DE COLAS ===\n";
echo "Jobs pendientes: " . DB::table('jobs')->count() . "\n";
echo "Jobs fallidos:   " . DB::table('failed_jobs')->count() . "\n\n";

$jobs = DB::table('jobs')->take(5)->get();
foreach ($jobs as $j) {
    $payload = json_decode($j->payload, true);
    echo "ID: {$j->id} | Queue: {$j->queue} | Job: " . ($payload['displayName'] ?? '?') . "\n";
    echo "  Attempts: {$j->attempts} | Created: " . date('Y-m-d H:i:s', $j->created_at) . "\n";
}

if ($jobs->isEmpty()) {
    echo "No hay jobs pendientes en la cola.\n";
}

echo "\n--- FAILED JOBS ---\n";
$failed = DB::table('failed_jobs')->take(5)->orderBy('id', 'desc')->get();
foreach ($failed as $f) {
    $payload = json_decode($f->payload, true);
    echo "ID: {$f->id} | Queue: {$f->queue} | Job: " . ($payload['displayName'] ?? '?') . "\n";
    echo "  Failed at: {$f->failed_at}\n";
    echo "  Exception (primeros 200): " . substr($f->exception, 0, 200) . "\n\n";
}
if ($failed->isEmpty()) {
    echo "No hay jobs fallidos.\n";
}
