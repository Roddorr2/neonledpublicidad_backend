<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campania;
use App\Models\modalservicios;
use App\Jobs\SendWhatsAppCampaignJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\FileUploadService;

class WhatsAppCampaignController extends Controller
{

    /**
     * POST /api/whatsapp/campaign/activate
     * Activa una campaña de WhatsApp masiva
     */
    public function activate(Request $request)
    {
        // Validación del payload ----
        $validator = Validator::make($request->all(), [
            'service' => 'required|string|in:p1,p2,p3,p4,p5,p6,p7,p8,p9,p10,p11,p12,p13,p14,p15',
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
            $serviceMap = [
                'p1' => 1,
                'p2' => 2,
                'p3' => 3,
                'p4' => 4,
                'p5' => 5,
                'p6' => 6,
                'p7' => 7,
                'p8' => 8,
                'p9' => 9,
                'p10' => 10,
                'p11' => 11,
                'p12' => 12,
                'p13' => 13,
                'p14' => 14,
                'p15' => 15
            ];
            $idProducto = $serviceMap[$request->service];

            // Verificar que el producto exista
            $productoExists = DB::table('productos')->where('id_producto', $idProducto)->exists();
            if (! $productoExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado para el servicio seleccionado',
                    'id_producto' => $idProducto
                ], 404);
            }

            // Subir imagen usando servicio reutilizable
            $subidor = new FileUploadService();
            $resultadoSubida = $subidor->subir($request->file('image'), 'campanias_whatsapp');
            $imagenUrl = $resultadoSubida['url'] ?? null;
            if (!$imagenUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar la imagen'
                ], 400);
            }

            DB::beginTransaction();

            // 1. Consultar destinatarios directamente desde modalservicios activos por producto
            $destinatarios = DB::table('modalservicios')
                ->where('id_producto', $idProducto)
                ->where('estado', 1)
                ->select('id_modalservicio', 'nombre', 'telefono', 'id_producto')
                ->orderByDesc('id_modalservicio')
                ->get()
                ->unique('telefono')
                ->values();

            if ($destinatarios->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay destinatarios para este producto'
                ], 404);
            }

            $totalDestinatarios = $destinatarios->count();

            // 2. Crear registro de campaña
            $campania = Campania::create([
                'id_servicio' => $idProducto,
                'user_id' => $request->user()->id, // ← Registra quién la creó
                'parrafo' => $request->paragraph,
                'imagen_url' => $imagenUrl,
                'estado' => 'pendiente',
                'total_destinatarios' => $totalDestinatarios,
                'envios_pendientes' => $totalDestinatarios,
                'fecha_inicio' => now()
            ]);

            // Log de auditoría
            \Log::info('Campaña WhatsApp creada', [
                'campania_id' => $campania->id_campania,
                'creado_por_user_id' => $request->user()->id,
                'creado_por_nombre' => $request->user()->name
            ]);

            // 3. Planificar los chunks y persistir en whatsapp_chunks (el orquestador los enviará)
            $recipients = $destinatarios->toArray();
            $chunksCreated = \App\Services\PlannerService::planCampaign($campania->id_campania, $recipients);

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
                    'chunks' => count($chunksCreated),
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
     * POST /api/whatsapp/campaign/estimate
     * Estima duración (días) y chunking para una campaña sin crearla.
     */
    public function estimate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service' => 'nullable|string',
            'id_servicio' => 'nullable|integer',
            'recipients' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $recipients = [];

            // If service or id_servicio provided, load recipients from modalservicios
            $idProducto = null;
            if ($request->filled('id_servicio')) {
                $idProducto = (int) $request->input('id_servicio');
            } elseif ($request->filled('service')) {
                $serviceMap = [
                    'p1' => 1,'p2' => 2,'p3' => 3,'p4' => 4,'p5' => 5,
                    'p6' => 6,'p7' => 7,'p8' => 8,'p9' => 9,'p10' => 10,
                    'p11' => 11,'p12' => 12,'p13' => 13,'p14' => 14,'p15' => 15
                ];
                $idProducto = $serviceMap[$request->service] ?? null;
            }

            if ($idProducto) {
                $destinatarios = DB::table('modalservicios')
                    ->where('id_producto', $idProducto)
                    ->where('estado', 1)
                    ->select('id_modalservicio', 'nombre', 'telefono')
                    ->orderByDesc('id_modalservicio')
                    ->get()
                    ->unique('telefono')
                    ->values();

                $recipients = $destinatarios->toArray();
            } elseif ($request->filled('recipients')) {
                $recipients = $request->input('recipients');
            } else {
                return response()->json(['success' => false, 'message' => 'Se requiere service/id_servicio o recipients'], 422);
            }

            $chunkSize = $request->input('chunk_size');
            $dailyLimit = $request->input('daily_limit');
            $spacing = $request->input('spacing_minutes');
            $start = $request->input('start_date');

            $est = \App\Services\PlannerService::estimateCampaign($recipients, $chunkSize, $dailyLimit, $spacing, $start);

            return response()->json(['success' => true, 'data' => $est]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
        // Delegar al servicio FileUploadService
        if (!$imageFile || !is_object($imageFile) || !method_exists($imageFile, 'getRealPath')) {
            Log::error('No se recibió archivo de imagen válido');
            return null;
        }

        $uploader = new FileUploadService();
        $res = $uploader->subir($imageFile, 'campanias_whatsapp');

        if (empty($res['url'])) {
            Log::error('FileUploadService no retornó URL válida', ['result' => $res]);
            return null;
        }

        Log::info('Imagen subida exitosamente', ['url' => $res['url']]);
        return $res['url'];
    }
}
