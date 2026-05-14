<?php

namespace App\Services;

use App\Models\CloudinaryUpload;
use Carbon\Carbon;
use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Servicio reutilizable para subir archivos (Cloudinary o storage local).
 * Todo en español y usando nombres de variables generales.
 */
class FileUploadService
{
    /**
     * Sube un archivo a Cloudinary (si está configurado) o al disco público.
     *
     * @param  UploadedFile|string $entrada          Archivo subido (UploadedFile) o ruta/base64 (no implementado aún)
     * @param  string              $carpeta          Carpeta/namespace en el proveedor (ej. "plantillas_whatsapp")
     * @param  string|null         $publicIdAnterior public_id previo en Cloudinary (para eliminar)
     * @param  string|null         $urlAnterior      URL previa en storage (para eliminar archivo local)
     * @return array               ['url' => string, 'public_id' => string|null]
     */
    public function subir($entrada, string $carpeta, ?string $publicIdAnterior = null, ?string $urlAnterior = null, array $opciones = []): array
    {
        try {
            // Aceptamos principalmente UploadedFile
            if (! is_object($entrada) || ! method_exists($entrada, 'getRealPath')) {
                Log::warning('FileUploadService: entrada no es UploadedFile, retornando vacio');

                return ['url' => '', 'public_id' => null];
            }

            // Si hay configuración de Cloudinary, usarla
            $useCloudinary = env('CLOUDINARY_URL') || (env('CLOUDINARY_CLOUD_NAME') && env('CLOUDINARY_KEY') && env('CLOUDINARY_SECRET'));

            if ($useCloudinary) {
                $resultado = null;
                try {
                    $uploadOptions = ['folder' => $carpeta];
                    // allow upload_preset via options or env (for unsigned uploads)
                    if (! empty($opciones['upload_preset'])) {
                        $uploadOptions['upload_preset'] = $opciones['upload_preset'];
                    } elseif (env('CLOUDINARY_UPLOAD_PRESET')) {
                        $uploadOptions['upload_preset'] = env('CLOUDINARY_UPLOAD_PRESET');
                    }

                    if (class_exists(\CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::class)) {
                        $resultado = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::uploadApi()->upload($entrada->getRealPath(), $uploadOptions);
                    } else {
                        // Intentar resolver singleton/app binding primero
                        try {
                            $cloudinary = app(Cloudinary::class);
                        } catch (\Throwable $e) {
                            // Fallback: crear instancia manual
                            $cloudinary = new Cloudinary([
                                'cloud' => [
                                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                                    'api_key'    => env('CLOUDINARY_KEY'),
                                    'api_secret' => env('CLOUDINARY_SECRET'),
                                ],
                                'url' => ['secure' => false],
                                'api' => ['upload_prefix' => 'http://api.cloudinary.com'],
                            ]);
                        }

                        $resultado = $cloudinary->uploadApi()->upload($entrada->getRealPath(), $uploadOptions);
                    }
                } catch (\Exception $e) {
                    Log::error('FileUploadService: excepción al subir a Cloudinary', ['error' => $e->getMessage()]);
                }

                if ($resultado && isset($resultado['secure_url'])) {
                    // eliminar anterior en Cloudinary si aplica
                    if ($publicIdAnterior && ($opciones['delete_previous_cloud'] ?? true)) {
                        try {
                            if (class_exists(\CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::class)) {
                                \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::destroy($publicIdAnterior);
                            } else {
                                $cloudinary->uploadApi()->destroy($publicIdAnterior);
                            }
                        } catch (\Exception $e) {
                            Log::warning('FileUploadService: no se pudo eliminar public_id previo: ' . $e->getMessage());
                        }
                    }

                    // Si existe una reserva en BD para este public_id, marcarla como usada
                    $publicId = $resultado['public_id'] ?? null;
                    if ($publicId) {
                        try {
                            $rec = CloudinaryUpload::where('public_id', $publicId)->first();
                            if ($rec && ! $rec->used) {
                                $rec->used       = true;
                                $rec->secure_url = $resultado['secure_url'];
                                $rec->save();
                            }
                        } catch (\Throwable $e) {
                            Log::warning('FileUploadService: no se pudo marcar reserva Cloudinary como usada: ' . $e->getMessage());
                        }
                    }

                    return ['url' => $resultado['secure_url'], 'public_id' => $publicId];
                }

                // Si Cloudinary falló o no devolvió URL, intentar fallback a almacenamiento local
                Log::warning('FileUploadService: Cloudinary falló, intentando fallback a almacenamiento local', ['resultado' => $resultado]);
                // Fallback: store locally under structured folders
                $localFolder = $this->buildLocalFolderPath($carpeta, $opciones);
                // If a desired filename is provided, store with that name
                $filename = $opciones['filename'] ?? null;
                if ($filename) {
                    $rutaFallback = Storage::disk('public')->putFileAs($localFolder, $entrada, $filename);
                } else {
                    $rutaFallback = Storage::disk('public')->putFile($localFolder, $entrada);
                }

                if ($rutaFallback) {
                    // Derivar un public_id local consistente (sin extensión) si se guardó con filename
                    $publicIdFallback = null;
                    if (! empty($filename)) {
                        $base             = pathinfo($filename, PATHINFO_FILENAME);
                        $publicIdFallback = trim($localFolder, '/') . '/' . $base;
                    } else {
                        // intentar derivar del nombre de archivo devuelto
                        $base = pathinfo($rutaFallback, PATHINFO_FILENAME);
                        if ($base) {
                            $publicIdFallback = trim($localFolder, '/') . '/' . $base;
                        }
                    }

                    return ['url' => asset('storage/' . $rutaFallback), 'public_id' => $publicIdFallback];
                }

                return ['url' => '', 'public_id' => null];
            }

            // Si no hay Cloudinary, almacenar localmente en disco público
            $localFolder = $this->buildLocalFolderPath($carpeta, $opciones);
            $filename    = $opciones['filename'] ?? null;
            if ($filename) {
                $ruta = Storage::disk('public')->putFileAs($localFolder, $entrada, $filename);
            } else {
                $ruta = Storage::disk('public')->putFile($localFolder, $entrada);
            }
            if ($ruta) {
                // eliminar anterior local si la URL previa apunta a /storage/
                if ($urlAnterior && str_contains($urlAnterior, '/storage/')) {
                    try {
                        $relativa = str_replace(asset('storage/'), '', $urlAnterior);
                        Storage::disk('public')->delete($relativa);
                    } catch (\Exception $e) {
                        Log::warning('FileUploadService: no se pudo eliminar archivo local previo: ' . $e->getMessage());
                    }
                }

                // Derivar public_id local consistente (sin extensión)
                $publicIdFallback = null;
                if (! empty($filename)) {
                    $base             = pathinfo($filename, PATHINFO_FILENAME);
                    $publicIdFallback = trim($localFolder, '/') . '/' . $base;
                } else {
                    $base = pathinfo($ruta, PATHINFO_FILENAME);
                    if ($base) {
                        $publicIdFallback = trim($localFolder, '/') . '/' . $base;
                    }
                }

                return ['url' => asset('storage/' . $ruta), 'public_id' => $publicIdFallback];
            }

            return ['url' => '', 'public_id' => null];
        } catch (\Exception $e) {
            Log::error('FileUploadService: error subiendo archivo', ['error' => $e->getMessage()]);

            return ['url' => '', 'public_id' => null];
        }
    }

    /**
     * Construye una ruta de carpeta local estructurada bajo `uploads/`.
     * Formato: uploads/{carpeta}/{YYYY}/{MM}/{entity_id?}
     */
    private function buildLocalFolderPath(string $carpeta, array $opciones = []): string
    {
        // Special case: plantilla whatsapp should go to public/storage/plantillas/whatsapp
        if (in_array($carpeta, ['plantillas_whatsapp', 'plantillas/whatsapp'])) {
            return 'plantillas/whatsapp';
        }

        if (str_starts_with(trim($carpeta, '/'), 'plantillas/whatsapp/')) {
            return trim($carpeta, '/');
        }

        // Special case: plantilla email should go to public/storage/plantillas/email
        if (in_array($carpeta, ['plantillas_email', 'plantillas/email'])) {
            return 'plantillas/email';
        }

        // If the target is empleados/perfiles we want a flat, predictable path
        // like storage/app/public/empleados/perfiles/{id} (no 'uploads' nor YYYY/MM)
        if (str_starts_with(trim($carpeta, '/'), 'empleados/perfiles')) {
            // If the carpeta already contains the id (common usage), keep it as-is
            return trim($carpeta, '/');
        }

        // Default behavior: organized under uploads/YYYY/MM for other folders
        $base  = 'uploads';
        $year  = date('Y');
        $month = date('m');
        $parts = [$base, trim($carpeta, '/'), $year, $month];
        if (! empty($opciones['entity_id'])) {
            $parts[] = (string)$opciones['entity_id'];
        }
        // Filtrar segmentos vacíos y unir con '/'
        $segments = array_filter($parts, fn ($p) => $p !== null && $p !== '');

        return implode('/', $segments);
    }

    /**
     * Genera una firma y registra una reserva para una subida directa (client-direct) opcional.
     * Retorna los parámetros que el frontend debe enviar: api_key, timestamp, signature, public_id, folder
     */
    public function generarFirmaReserva(string $carpeta, ?string $publicId = null, ?int $userId = null, int $ttlSegundos = 120): array
    {
        // Generar public_id si no se provee
        if (empty($publicId)) {
            $publicId = trim($carpeta, '/') . '/' . uniqid();
        }

        $timestamp = time();
        $expiresAt = Carbon::createFromTimestamp($timestamp + $ttlSegundos);

        $params = [
            'public_id' => $publicId,
            'folder'    => $carpeta,
            'timestamp' => $timestamp,
        ];

        ksort($params);
        $toSign    = urldecode(http_build_query($params));
        $secret    = env('CLOUDINARY_SECRET') ?: config('services.cloudinary.secret');
        $signature = sha1($toSign . ($secret ?? ''));

        // Guardar reserva en DB
        try {
            CloudinaryUpload::create([
                'public_id'  => $publicId,
                'user_id'    => $userId,
                'used'       => false,
                'expires_at' => $expiresAt,
                'metadata'   => json_encode(['folder' => $carpeta]),
            ]);
        } catch (\Throwable $e) {
            Log::warning('FileUploadService: no se pudo crear reserva Cloudinary: ' . $e->getMessage());
        }

        return [
            'cloud_name' => env('CLOUDINARY_CLOUD_NAME') ?: config('services.cloudinary.cloud_name'),
            'api_key'    => env('CLOUDINARY_KEY') ?: config('services.cloudinary.key'),
            'timestamp'  => $timestamp,
            'signature'  => $signature,
            'public_id'  => $publicId,
            'folder'     => $carpeta,
            'expires_at' => $expiresAt->toDateTimeString(),
        ];
    }

    /**
     * Elimina un public_id de Cloudinary si es posible. Devuelve true si la eliminación fue solicitada con éxito.
     */
    public function eliminarPublicId(?string $publicId): bool
    {
        if (empty($publicId)) {
            return false;
        }

        try {
            if (class_exists(\CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::class)) {
                \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::destroy($publicId);
            } else {
                $cloudinary = app(Cloudinary::class);
                $cloudinary->uploadApi()->destroy($publicId);
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('FileUploadService: fallo al eliminar public_id via Cloudinary: ' . $e->getMessage());

            return false;
        }
    }
}
