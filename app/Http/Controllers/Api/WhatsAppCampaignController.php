<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campania;
use App\Models\modalservicios;
use App\Jobs\SendWhatsappCampaignJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;

class WhatsAppCampaignController extends Controller
{
    /**
     * POST /api/whatsapp/campaign/activate
     * Activa una campaña de WhatsApp masiva
     */
    public function activate(Request $request)
    {
        // Validación del payload
        $validator = Validator::make($request->all(), [
            'service' => 'required|string|in:p1,p2,p3,p4',
            'paragraph' => 'required|string|min:10|max:1000',
            'image' => 'required|string', // Base64 o URL
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Mapear service a id_producto
            $serviceMap = ['p1' => 1, 'p2' => 2, 'p3' => 3, 'p4' => 4];
            $idProducto = $serviceMap[$request->service];

            // Subir imagen a Cloudinary
            $imagenUrl = $this->processAndUploadImage($request->image);
            if (!$imagenUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar la imagen'
                ], 400);
            }

            DB::beginTransaction();

            // 1. Consultar destinatarios desde modal_wats con sus modalservicios
            $watModals = DB::table('modal_wats')
                ->join('modalservicios', 'modal_wats.id_modalservicio', '=', 'modalservicios.id_modalservicio')
                ->where('modalservicios.id_producto', $idProducto)
                ->where('modalservicios.estado', 1)
                ->select(
                    'modal_wats.id_modal_wat',
                    'modal_wats.id_modalservicio',
                    'modal_wats.number_message',
                    'modalservicios.nombre',
                    'modalservicios.telefono',
                    'modalservicios.id_producto'
                )
                ->get();

            if ($watModals->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay destinatarios para este producto'
                ], 404);
            }

            $totalDestinatarios = $watModals->count();

            // 2. Crear registro de campaña
            $campania = Campania::create([
                'id_servicio' => $idProducto,
                'parrafo' => $request->paragraph,
                'imagen_url' => $imagenUrl,
                'estado' => 'pendiente',
                'total_destinatarios' => $totalDestinatarios,
                'envios_pendientes' => $totalDestinatarios,
                'fecha_inicio' => now()
            ]);

            // 3. Dividir destinatarios en chunks de 50 y despachar jobs
            $chunks = $watModals->chunk(50);
            $chunkNumber = 0;
            
            foreach ($chunks as $chunk) {
                $chunkNumber++;
                SendWhatsappCampaignJob::dispatch(
                    $campania->id_campania,
                    $chunkNumber,
                    $chunk->toArray(),
                    $request->paragraph,
                    $imagenUrl,
                    $idProducto
                );
            }

            // 4. Actualizar estado de campaña
            $campania->update(['estado' => 'en_proceso']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Campaña activada exitosamente',
                'data' => [
                    'id_campania' => $campania->id_campania,
                    'imagen_url' => $imagenUrl,
                    'total_destinatarios' => $totalDestinatarios,
                    'chunks' => $chunks->count(),
                    'estado' => $campania->estado
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al activar campaña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/whatsapp/campaign/{id}/status
     * Obtiene el estado en tiempo real de una campaña
     */
    public function status($id)
    {
        $campania = Campania::with('servicio:id_servicio,nombre')->find($id);

        if (!$campania) {
            return response()->json([
                'success' => false,
                'message' => 'Campaña no encontrada'
            ], 404);
        }

        // Calcular progreso
        $progreso = $campania->total_destinatarios > 0 
            ? round((($campania->envios_exitosos + $campania->envios_fallidos) / $campania->total_destinatarios) * 100, 2)
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'id_campania' => $campania->id_campania,
                'servicio' => $campania->servicio->nombre ?? 'N/A',
                'estado' => $campania->estado,
                'progreso' => $progreso . '%',
                'total_destinatarios' => $campania->total_destinatarios,
                'envios_exitosos' => $campania->envios_exitosos,
                'envios_fallidos' => $campania->envios_fallidos,
                'envios_pendientes' => $campania->envios_pendientes,
                'fecha_inicio' => $campania->fecha_inicio?->format('Y-m-d H:i:s'),
                'fecha_fin' => $campania->fecha_fin?->format('Y-m-d H:i:s'),
                'duracion' => $campania->fecha_inicio && $campania->fecha_fin 
                    ? $campania->fecha_inicio->diffForHumans($campania->fecha_fin, true)
                    : 'En proceso'
            ]
        ]);
    }

    /**
     * GET /api/whatsapp/campaigns
     * Lista las campañas recientes
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $estado = $request->query('estado');

        $query = Campania::with('servicio:id_servicio,nombre')
            ->orderBy('created_at', 'desc');

        if ($estado && in_array($estado, ['pendiente', 'en_proceso', 'completada', 'cancelada', 'error'])) {
            $query->where('estado', $estado);
        }

        $campanias = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $campanias->items(),
            'pagination' => [
                'total' => $campanias->total(),
                'per_page' => $campanias->perPage(),
                'current_page' => $campanias->currentPage(),
                'last_page' => $campanias->lastPage()
            ]
        ]);
    }

    /**
     * Procesa y sube imagen a Cloudinary
     * Acepta base64, URL o archivo
     * @param string $image Base64, URL o archivo
     * @return string|null URL de la imagen subida o null si falla
     */
    private function processAndUploadImage($image)
    {
        try {
            // Si es una URL válida, retornarla directamente
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                return $image;
            }

            // Inicializar instancia de Cloudinary con credenciales del .env
            // En entorno local usa HTTP para evitar problemas de certificado SSL
            $config = [
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key'    => env('CLOUDINARY_KEY'),
                    'api_secret' => env('CLOUDINARY_SECRET'),
                ],
                'url' => ['secure' => true],
            ];

            if (app()->environment('local')) {
                $config['api'] = [
                    'upload_prefix' => 'http://api.cloudinary.com',
                ];
                $config['url']['secure'] = false;
            }

            $cloudinary = new Cloudinary($config);

            // Si es base64, decodificar y subir
            if (strpos($image, 'data:image') === 0) {
                // Extraer solo la parte base64 usando regex (soporta cualquier formato)
                $image = preg_replace('/^data:image\/[a-zA-Z]+;base64,/', '', $image);
                // Limpiar espacios y saltos de línea
                $image = preg_replace('/\s+/', '', $image);
                $image = str_replace(' ', '+', $image);

                $imageData = base64_decode($image, true);
                if ($imageData === false || strlen($imageData) === 0) {
                    Log::error('Error al decodificar base64: datos inválidos o vacíos');
                    return null;
                }

                // Crear archivo temporal
                $tmpFile = tempnam(sys_get_temp_dir(), 'img');
                file_put_contents($tmpFile, $imageData);

                if (filesize($tmpFile) === 0) {
                    Log::error('Archivo temporal vacío después de escribir base64');
                    @unlink($tmpFile);
                    return null;
                }

                Log::info('Subiendo imagen a Cloudinary', [
                    'tmpFile' => $tmpFile,
                    'filesize' => filesize($tmpFile),
                ]);

                $result = $cloudinary->uploadApi()->upload($tmpFile, [
                    'folder' => 'campanias_whatsapp'
                ]);

                @unlink($tmpFile);

                // Verificar respuesta
                if (!$result || !isset($result['secure_url'])) {
                    Log::error('Cloudinary no retornó secure_url', [
                        'result_type' => gettype($result),
                        'result' => is_array($result) ? $result : (string) $result,
                    ]);
                    return null;
                }

                Log::info('Imagen subida exitosamente', ['url' => $result['secure_url']]);
                return $result['secure_url'];
            }

            // Si es base64 puro SIN prefijo data:image
            $imageData = base64_decode($image, true);
            if ($imageData !== false && strlen($imageData) > 0) {
                $tmpFile = tempnam(sys_get_temp_dir(), 'img');
                file_put_contents($tmpFile, $imageData);

                if (filesize($tmpFile) === 0) {
                    @unlink($tmpFile);
                    Log::error('Archivo temporal vacío (base64 sin prefijo)');
                    return null;
                }

                $result = $cloudinary->uploadApi()->upload($tmpFile, [
                    'folder' => 'campanias_whatsapp'
                ]);

                @unlink($tmpFile);

                if (!$result || !isset($result['secure_url'])) {
                    Log::error('Cloudinary no retornó secure_url (base64 sin prefijo)', [
                        'result_type' => gettype($result),
                    ]);
                    return null;
                }

                return $result['secure_url'];
            }

            Log::error('Formato de imagen no válido: no es URL, ni base64 con prefijo, ni base64 puro');
            return null;

        } catch (\Exception $e) {
            Log::error('Error al procesar imagen', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}
