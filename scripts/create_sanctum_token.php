<?php
// Creates a personal access token for user id 1 and prints it.
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::find(1);
if (! $user) {
    echo "User id 1 not found\n";
    exit(1);
}

$token = $user->createToken('test-token')->plainTextToken;
echo $token . PHP_EOL;
