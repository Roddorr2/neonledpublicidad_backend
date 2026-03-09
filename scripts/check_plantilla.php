<?php
// Usage: php scripts/check_plantilla.php <id>
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PlantillaWhatsapp;

$id = $argv[1] ?? null;
if (! $id) {
    echo "Usage: php scripts/check_plantilla.php <id>\n";
    exit(1);
}

$p = PlantillaWhatsapp::find($id);
if (! $p) {
    echo "Plantilla $id not found\n";
    exit(1);
}

echo json_encode($p->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
