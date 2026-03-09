<?php
// Usage: php scripts/cloudinary_upload_test.php "C:\\Users\\axtev\\Pictures\\assets\\968634c60cbf34ee8a87e12f4057402c.jpg"

require __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Cloudinary;

if ($argc < 2) {
    echo "Usage: php scripts/cloudinary_upload_test.php <path-to-image>\n";
    exit(1);
}

$imagePath = $argv[1];
if (!file_exists($imagePath)) {
    echo "File not found: $imagePath\n";
    exit(1);
}

// read .env for cloudinary creds (simple parser)
$envPath = __DIR__ . '/../.env';
$env = [];
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v);
    }
}

$cloudName = $env['CLOUDINARY_CLOUD_NAME'] ?? getenv('CLOUDINARY_CLOUD_NAME');
$apiKey = $env['CLOUDINARY_KEY'] ?? getenv('CLOUDINARY_KEY');
$apiSecret = $env['CLOUDINARY_SECRET'] ?? getenv('CLOUDINARY_SECRET');

if (! $cloudName || ! $apiKey || ! $apiSecret) {
    echo "Cloudinary credentials not found in .env or env vars.\n";
    exit(1);
}

$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => $cloudName,
        'api_key' => $apiKey,
        'api_secret' => $apiSecret,
    ],
    'url' => ['secure' => false],
    'api' => ['upload_prefix' => 'http://api.cloudinary.com'],
]);

try {
    $result = $cloudinary->uploadApi()->upload($imagePath, ['folder' => 'plantillas_whatsapp_test']);
    echo "Upload result:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} catch (Exception $e) {
    echo "Upload failed: " . $e->getMessage() . "\n";
    exit(1);
}
