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
            'image' => 'required|file|image', // Solo archivo imagen
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
            $imagenUrl = $this->processAndUploadImage($request->file('image'));
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
    private function processAndUploadImage($imageFile)
    {
        try {
            // Solo aceptar archivos subidos (UploadedFile)
            if (!$imageFile || !is_object($imageFile) || !method_exists($imageFile, 'getRealPath')) {
                Log::error('No se recibió archivo de imagen válido');
                return null;
            }

            $cloudinary = new Cloudinary([
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

            $result = $cloudinary->uploadApi()->upload($imageFile->getRealPath(), [
                'folder' => 'campanias_whatsapp'
            ]);

            if (!$result || !isset($result['secure_url'])) {
                Log::error('Cloudinary no retornó secure_url', [
                    'result_type' => gettype($result),
                    'result' => is_array($result) ? $result : (string) $result,
                ]);
                return null;
            }

            Log::info('Imagen subida exitosamente', ['url' => $result['secure_url']]);
            return $result['secure_url'];

        } catch (\Exception $e) {
            Log::error('Error al procesar imagen', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}
