<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PlantillasEmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = Config::get('email_content.services', []);

        if (empty($services) || !is_array($services)) {
            return;
        }

        $rows = [];
        $now = now();

        foreach ($services as $idProducto => $serviceData) {
            $messages = $serviceData['messages'] ?? [];
            if (!is_array($messages)) {
                continue;
            }

            foreach ($messages as $numeroPlantilla => $messageData) {
                if (!is_array($messageData)) {
                    continue;
                }

                $subject = (string) ($messageData['subject'] ?? '');
                $title = (string) ($messageData['title'] ?? $subject);
                $message = (string) ($messageData['message'] ?? '');
                $extra = (string) ($messageData['extra'] ?? '');
                $fullMessage = $this->buildMensaje($message, $extra);
                $imagePath = (string) ($messageData['image'] ?? '');
                $imageUrl = $this->buildAbsoluteImageUrl($imagePath);

                if ($subject === '' || $fullMessage === '') {
                    continue;
                }

                $rows[] = [
                    'id_producto' => (int) $idProducto,
                    'numero_plantilla' => (int) $numeroPlantilla,
                    'nombre' => $title !== '' ? $title : "Plantilla {$idProducto}-{$numeroPlantilla}",
                    'asunto' => $subject,
                    'encabezado' => $title,
                    'imagen_url' => $imageUrl,
                    'mensaje' => $fullMessage,
                    'mensaje_boton' => null,
                    'url_boton' => null,
                    'footer' => null,
                    'red_facebook' => null,
                    'red_tiktok' => null,
                    'red_instagram' => null,
                    'red_linkedin' => null,
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($rows)) {
            DB::table('plantillas_email')->upsert(
                $rows,
                ['id_producto', 'numero_plantilla'],
                [
                    'nombre',
                    'asunto',
                    'encabezado',
                    'imagen_url',
                    'mensaje',
                    'updated_at',
                ]
            );
        }
    }

    private function buildAbsoluteImageUrl(string $path): ?string
    {
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $baseUrl = rtrim((string) config('app.url'), '/');
        $normalizedPath = '/' . ltrim($path, '/');
        return $baseUrl . $normalizedPath;
    }

    private function buildMensaje(string $message, string $extra): string
    {
        $message = trim($message);
        $extra = trim($extra);

        if ($message !== '' && $extra !== '') {
            return $message . "\n\n" . $extra;
        }

        return $message !== '' ? $message : $extra;
    }
}
