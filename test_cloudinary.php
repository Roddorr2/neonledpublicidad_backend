<?php

/**
 * Test rápido de Cloudinary - ejecutar con: php test_cloudinary.php
 * Verifica credenciales y capacidad de upload
 */

require_once __DIR__ . '/vendor/autoload.php';

// Cargar .env manualmente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== TEST DE CLOUDINARY ===\n\n";

// 1. Verificar variables de entorno
$vars = [
    'CLOUDINARY_URL'        => $_ENV['CLOUDINARY_URL'] ?? null,
    'CLOUDINARY_CLOUD_NAME' => $_ENV['CLOUDINARY_CLOUD_NAME'] ?? null,
    'CLOUDINARY_KEY'        => $_ENV['CLOUDINARY_KEY'] ?? null,
    'CLOUDINARY_SECRET'     => $_ENV['CLOUDINARY_SECRET'] ?? null,
];

echo "1. VARIABLES DE ENTORNO:\n";
foreach ($vars as $key => $val) {
    if ($val) {
        echo "   [OK] {$key} = " . substr($val, 0, 20) . "...\n";
    } else {
        echo "   [FALLO] {$key} no definida\n";
    }
}
echo "\n";

// 2. Verificar extensiones PHP necesarias
echo "2. EXTENSIONES PHP:\n";
$extensions = ['curl', 'json', 'fileinfo', 'gd'];
foreach ($extensions as $ext) {
    echo '   ' . (extension_loaded($ext) ? '[OK]' : '[NO]') . " ext-{$ext}\n";
}
echo "\n";

// 3. Intentar crear instancia de Cloudinary
echo "3. INSTANCIAR CLOUDINARY (HTTP):\n";
try {
    $cloudinary = new Cloudinary\Cloudinary([
        'cloud' => [
            'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'],
            'api_key'    => $_ENV['CLOUDINARY_KEY'],
            'api_secret' => $_ENV['CLOUDINARY_SECRET'],
        ],
        'url' => ['secure' => false],
        'api' => [
            'upload_prefix' => 'http://api.cloudinary.com',
        ],
    ]);
    echo "   [OK] Instancia creada con HTTP\n";
} catch (Exception $e) {
    echo '   [FALLO] ' . $e->getMessage() . "\n";
    exit(1);
}
echo "\n";

// 4. Crear archivo temporal (PNG mínimo 1x1px)
echo "4. CREAR ARCHIVO TEMPORAL:\n";
$pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==');
$tmpFile  = tempnam(sys_get_temp_dir(), 'cloudinary_test_');
file_put_contents($tmpFile, $pngBytes);
$size = filesize($tmpFile);
echo "   [OK] {$tmpFile} ({$size} bytes)\n\n";

// 5. Subir a Cloudinary
echo "5. SUBIR IMAGEN A CLOUDINARY:\n";
try {
    $result = $cloudinary->uploadApi()->upload($tmpFile, [
        'folder'    => 'test_diagnostico',
        'public_id' => 'test_' . time(),
    ]);

    echo "   [OK] Upload exitoso!\n";
    echo '   Tipo de resultado: ' . gettype($result) . "\n";
    if (is_object($result)) {
        echo '   Clase: ' . get_class($result) . "\n";
    }

    // Verificar acceso como array
    if (is_array($result) || $result instanceof ArrayAccess) {
        echo '   secure_url: ' . ($result['secure_url'] ?? 'NO EXISTE') . "\n";
        echo '   public_id:  ' . ($result['public_id'] ?? 'NO EXISTE') . "\n";
        echo '   format:     ' . ($result['format'] ?? 'NO EXISTE') . "\n";
    } else {
        echo "   [WARN] resultado NO es array/ArrayAccess\n";
        echo '   Dump: ' . print_r($result, true) . "\n";
    }

    // Limpiar de Cloudinary
    try {
        $cloudinary->uploadApi()->destroy($result['public_id']);
        echo "   [OK] Imagen de test eliminada\n";
    } catch (Exception $e) {
        echo '   [WARN] No se pudo eliminar: ' . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo '   [FALLO] ' . $e->getMessage() . "\n";
    echo '   Clase: ' . get_class($e) . "\n";

    $msg = $e->getMessage();
    if (str_contains($msg, 'Invalid API Key')) {
        echo "\n   >>> DIAGNOSTICO: API Key inválida - verifica CLOUDINARY_KEY\n";
    } elseif (str_contains($msg, 'unknown cloud')) {
        echo "\n   >>> DIAGNOSTICO: Cloud name no existe - verifica CLOUDINARY_CLOUD_NAME\n";
    } elseif (str_contains($msg, 'Signature') || str_contains($msg, 'signature')) {
        echo "\n   >>> DIAGNOSTICO: API Secret incorrecto - verifica CLOUDINARY_SECRET\n";
    } elseif (str_contains($msg, 'cURL') || str_contains($msg, 'curl')) {
        echo "\n   >>> DIAGNOSTICO: Error de red/conexión - verifica acceso a internet\n";

    } else {
        echo "\n   >>> DIAGNOSTICO: Error no clasificado. Revisa el mensaje arriba.\n";
    }
} finally {
    @unlink($tmpFile);
}

echo "\n=== FIN DEL TEST ===\n";
