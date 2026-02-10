<?php

namespace Tests\Feature;

use Tests\TestCase;

class CloudinaryTest extends TestCase
{
    /**
     * Test 1: Verificar que las variables de entorno de Cloudinary están configuradas
     */
    public function test_cloudinary_env_variables_are_set(): void
    {
        $cloudUrl   = env('CLOUDINARY_URL');
        $cloudName  = env('CLOUDINARY_CLOUD_NAME');
        $cloudKey   = env('CLOUDINARY_KEY');
        $cloudSecret = env('CLOUDINARY_SECRET');

        echo "\n--- CLOUDINARY ENV VARS ---\n";
        echo "CLOUDINARY_URL:        " . ($cloudUrl ? substr($cloudUrl, 0, 30) . '...' : 'NULL/EMPTY') . "\n";
        echo "CLOUDINARY_CLOUD_NAME: " . ($cloudName ?: 'NULL/EMPTY') . "\n";
        echo "CLOUDINARY_KEY:        " . ($cloudKey ? substr($cloudKey, 0, 6) . '...' : 'NULL/EMPTY') . "\n";
        echo "CLOUDINARY_SECRET:     " . ($cloudSecret ? substr($cloudSecret, 0, 6) . '...' : 'NULL/EMPTY') . "\n";

        $this->assertNotEmpty($cloudUrl, 'CLOUDINARY_URL no está definida en .env');
        $this->assertNotEmpty($cloudName, 'CLOUDINARY_CLOUD_NAME no está definida en .env');
        $this->assertNotEmpty($cloudKey, 'CLOUDINARY_KEY no está definida en .env');
        $this->assertNotEmpty($cloudSecret, 'CLOUDINARY_SECRET no está definida en .env');
    }

    /**
     * Test 2: Verificar que config/cloudinary.php existe y tiene cloud_url
     */
    public function test_cloudinary_config_is_published(): void
    {
        $configPath = config_path('cloudinary.php');
        $exists = file_exists($configPath);

        echo "\n--- CLOUDINARY CONFIG ---\n";
        echo "config/cloudinary.php existe: " . ($exists ? 'SI' : 'NO') . "\n";

        if ($exists) {
            $cloudUrl = config('cloudinary.cloud_url');
            echo "cloud_url resuelto: " . ($cloudUrl ? substr($cloudUrl, 0, 30) . '...' : 'NULL/EMPTY') . "\n";
            $this->assertNotEmpty($cloudUrl, 'cloud_url no resuelve correctamente');
        } else {
            echo "ACCION REQUERIDA: Ejecuta 'php artisan vendor:publish --tag=cloudinary-laravel-config'\n";
            $this->fail('config/cloudinary.php NO existe. El paquete cloudinary-labs/cloudinary-laravel lo requiere.');
        }
    }

    /**
     * Test 3: Verificar conexión real subiendo una imagen tiny de 1x1 px
     */
    public function test_cloudinary_upload_works(): void
    {
        // Verificar config existe primero
        if (!file_exists(config_path('cloudinary.php'))) {
            $this->markTestSkipped('config/cloudinary.php no existe — publica primero la config.');
        }

        // PNG mínimo de 1x1 px (sin necesitar ext-gd)
        // Generado con: base64_encode de un PNG 1x1 rojo
        $pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==');
        $tmpFile = tempnam(sys_get_temp_dir(), 'cloudinary_test_');
        file_put_contents($tmpFile, $pngBytes);

        $filesize = filesize($tmpFile);
        echo "\n--- CLOUDINARY UPLOAD TEST ---\n";
        echo "Archivo temporal: {$tmpFile} ({$filesize} bytes)\n";

        $this->assertGreaterThan(0, $filesize, 'El archivo temporal está vacío');

        try {
            $cloudinary = new \Cloudinary\Cloudinary([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key'    => env('CLOUDINARY_KEY'),
                    'api_secret' => env('CLOUDINARY_SECRET'),
                ],
                'url' => ['secure' => false],
                'api' => [
                    'upload_prefix' => 'http://api.cloudinary.com',
                ],
            ]);

            $result = $cloudinary->uploadApi()->upload($tmpFile, [
                'folder' => 'test_diagnostico',
                'public_id' => 'test_' . time(),
            ]);

            echo "Upload exitoso!\n";
            echo "secure_url: " . ($result['secure_url'] ?? 'NO DISPONIBLE') . "\n";
            echo "public_id:  " . ($result['public_id'] ?? 'NO DISPONIBLE') . "\n";
            echo "format:     " . ($result['format'] ?? 'NO DISPONIBLE') . "\n";

            $this->assertArrayHasKey('secure_url', $result, 'La respuesta no contiene secure_url');
            $this->assertNotEmpty($result['secure_url'], 'secure_url está vacío');

            // Limpiar: borrar la imagen de test
            try {
                $cloudinary->uploadApi()->destroy($result['public_id']);
                echo "Imagen de test eliminada de Cloudinary.\n";
            } catch (\Exception $e) {
                echo "Nota: No se pudo eliminar la imagen de test: {$e->getMessage()}\n";
            }

        } catch (\Exception $e) {
            echo "ERROR DE CLOUDINARY: {$e->getMessage()}\n";
            echo "Clase de excepción: " . get_class($e) . "\n";

            if (str_contains($e->getMessage(), 'Invalid API Key')) {
                echo ">>> DIAGNOSTICO: La API Key es inválida.\n";
            } elseif (str_contains($e->getMessage(), 'unknown cloud')) {
                echo ">>> DIAGNOSTICO: El CLOUD_NAME no existe.\n";
            } elseif (str_contains($e->getMessage(), 'Signature')) {
                echo ">>> DIAGNOSTICO: El API Secret es incorrecto.\n";
            } elseif (str_contains($e->getMessage(), 'cURL')) {
                echo ">>> DIAGNOSTICO: Error de red/conexión.\n";
            }

            $this->fail("Cloudinary upload falló: {$e->getMessage()}");
        } finally {
            @unlink($tmpFile);
        }
    }

    /**
     * Test 4: Simular el flujo exacto de processAndUploadImage con base64
     */
    public function test_base64_processing_flow(): void
    {
        if (!file_exists(config_path('cloudinary.php'))) {
            $this->markTestSkipped('config/cloudinary.php no existe.');
        }

        echo "\n--- BASE64 FLOW TEST ---\n";

        // PNG mínimo de 1x1 px (sin necesitar ext-gd)
        $pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==');
        $base64 = 'data:image/png;base64,' . base64_encode($pngBytes);
        echo "Base64 generado: " . strlen($base64) . " caracteres\n";
        echo "Prefijo: " . substr($base64, 0, 22) . "...\n";

        // Simular el mismo flujo de processAndUploadImage
        $image = $base64;

        // Paso 1: Verificar que no es URL
        $isUrl = filter_var($image, FILTER_VALIDATE_URL);
        echo "¿Es URL?: " . ($isUrl ? 'SI' : 'NO') . "\n";

        // Paso 2: Verificar que detecta data:image
        $hasPrefix = strpos($image, 'data:image') === 0;
        echo "¿Tiene prefijo data:image?: " . ($hasPrefix ? 'SI' : 'NO') . "\n";

        // Paso 3: Limpiar prefijo
        $cleaned = $image;
        $cleaned = str_replace('data:image/png;base64,', '', $cleaned);
        $cleaned = str_replace(' ', '+', $cleaned);
        echo "Base64 limpio: " . strlen($cleaned) . " caracteres\n";

        // Paso 4: Decodificar
        $decoded = base64_decode($cleaned);
        echo "Decodificado: " . strlen($decoded) . " bytes\n";
        $this->assertNotFalse($decoded, 'base64_decode retornó false');
        $this->assertGreaterThan(0, strlen($decoded), 'Datos decodificados vacíos');

        // Paso 5: Escribir a archivo temporal
        $tmpFile = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($tmpFile, $decoded);
        $tmpSize = filesize($tmpFile);
        echo "Archivo temporal: {$tmpFile} ({$tmpSize} bytes)\n";
        $this->assertGreaterThan(0, $tmpSize, 'Archivo temporal vacío');

        // Paso 6: Subir a Cloudinary
        try {
            $cloudinary = new \Cloudinary\Cloudinary([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key'    => env('CLOUDINARY_KEY'),
                    'api_secret' => env('CLOUDINARY_SECRET'),
                ],
                'url' => ['secure' => false],
                'api' => [
                    'upload_prefix' => 'http://api.cloudinary.com',
                ],
            ]);

            $result = $cloudinary->uploadApi()->upload($tmpFile, [
                'folder' => 'test_diagnostico',
            ]);

            echo "Tipo de \$result: " . gettype($result) . "\n";
            echo "¿Es array?: " . (is_array($result) ? 'SI' : 'NO') . "\n";
            echo "¿Es ApiResponse?: " . ($result instanceof \Cloudinary\Api\ApiResponse ? 'SI' : 'NO') . "\n";
            echo "Clase: " . (is_object($result) ? get_class($result) : 'no-object') . "\n";

            // Intentar acceder como array
            if (is_array($result) || $result instanceof \ArrayAccess) {
                echo "secure_url: " . ($result['secure_url'] ?? 'KEY NO EXISTE') . "\n";
                $this->assertNotEmpty($result['secure_url'] ?? null, 'secure_url vacío/inexistente');
            } else {
                echo "ADVERTENCIA: \$result NO es array ni ArrayAccess\n";
                echo "Contenido: " . print_r($result, true) . "\n";
                $this->fail('$result no es accesible como array');
            }

            // Limpiar
            try {
                $cloudinary->uploadApi()->destroy($result['public_id']);
            } catch (\Exception $e) { /* ignore */ }

            echo "Flujo base64 completo OK!\n";

        } catch (\Exception $e) {
            echo "ERROR en upload: {$e->getMessage()}\n";
            $this->fail("Upload falló: {$e->getMessage()}");
        } finally {
            @unlink($tmpFile);
        }
    }
}
