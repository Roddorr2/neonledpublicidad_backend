<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campania;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\FileUploadService;
use App\Services\WhatsappDailyQuotaService;

class WhatsAppCampaignController extends Controller
{
    private const SERVICE_MAP = [
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
        'p15' => 15,
        'p16' => 16,
    ];

    /**
     * GET /api/whatsapp/campaign/preview/{service}
     * Devuelve una vista previa del total de destinatarios sin crear campaña.
     */
    public function previewCampaign(Request $request, $service)
    {
        $preview = $this->buildPreviewDataByService((string) $service);
        if (!$preview) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio inválido'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'service' => (string) $service,
                'id_producto' => $preview['id_producto'],
                'total_destinatarios' => $preview['total_destinatarios'],
                'estimated_days' => $preview['estimated_days'],
            ]
        ]);
    }

    /**
     * POST /api/whatsapp/campaign/create
     * Crea campaña en estado borrador.
     */
    public function createCampaign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service' => 'required|string|in:p1,p2,p3,p4,p5,p6,p7,p8,p9,p10,p11,p12,p13,p14,p15,p16',
            'paragraph' => 'required|string|min:10|max:1000',
            'image' => 'required|file|image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $idProducto = $this->resolveServiceToProducto((string) $request->service);
            if (!$idProducto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Servicio inválido'
                ], 422);
            }

            $productoExists = DB::table('productos')->where('id_producto', $idProducto)->exists();
            if (! $productoExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado para el servicio seleccionado',
                    'id_producto' => $idProducto
                ], 404);
            }

            $destinatarios = $this->getDestinatariosByProducto($idProducto);
            if ($destinatarios->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay destinatarios para este producto'
                ], 404);
            }

            $subidor = new FileUploadService();
            $resultadoSubida = $subidor->subir($request->file('image'), 'campanias_whatsapp');
            $imagenUrl = $resultadoSubida['url'] ?? null;
            if (!$imagenUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar la imagen'
                ], 400);
            }

            $totalDestinatarios = $destinatarios->count();

            $campania = Campania::create([
                'id_servicio' => $idProducto,
                'user_id' => $request->user()->id,
                'parrafo' => $request->paragraph,
                'imagen_url' => $imagenUrl,
                'estado' => 'borrador',
                'total_destinatarios' => $totalDestinatarios,
                'envios_pendientes' => $totalDestinatarios,
                'progress_milestone' => 0,
                'progress_version' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Campaña creada en borrador',
                'data' => [
                    'campania_id' => $campania->id_campania,
                    'total_destinatarios' => $totalDestinatarios,
                    'estado' => $campania->estado,
                    'imagen_url' => $imagenUrl,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear campaña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/whatsapp/campaign/{id}/start
     * Inicia una campaña en borrador aplicando FIFO de campañas.
     */
    public function startCampaign(Request $request, $id)
    {
        $campania = Campania::with('producto:id_producto,nombre')->find($id);

        if (!$campania) {
            return response()->json([
                'success' => false,
                'message' => 'Campaña no encontrada'
            ], 404);
        }

        if (!in_array($campania->estado, ['borrador', 'pendiente', 'pausada_hasta_mañana'])) {
            return response()->json([
                'success' => false,
                'message' => 'La campaña no puede iniciarse desde el estado actual',
                'error_type' => 'invalid_state'
            ], 400);
        }

        $activeCampaign = Campania::getActiveCampaign();
        if ($activeCampaign && $activeCampaign->id_campania !== $campania->id_campania) {
            return response()->json([
                'success' => false,
                'message' => 'Ya hay una campaña en proceso. Espera a que finalice.',
                'error_type' => 'campaign_active',
                'active_campaign' => [
                    'id' => $activeCampaign->id_campania,
                    'servicio' => $activeCampaign->producto->nombre ?? 'N/A',
                    'estado' => $activeCampaign->estado,
                    'progreso' => $activeCampaign->getProgressPercentage(),
                ]
            ], 409);
        }

        try {
            $destinatarios = $this->getDestinatariosByProducto((int) $campania->id_servicio);
            if ($destinatarios->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay destinatarios disponibles para esta campaña'
                ], 404);
            }

            $campania->envios_pendientes = $destinatarios->count();
            $campania->estado = 'en_proceso';
            $campania->fecha_inicio = $campania->fecha_inicio ?: now();
            $campania->save();

            $chunksCreated = \App\Services\PlannerService::planCampaign($campania->id_campania, $destinatarios->toArray());

            if (count($chunksCreated) === 0) {
                $campania->estado = 'error';
                $campania->save();

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudieron planificar envíos para esta campaña'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Campaña iniciada exitosamente',
                'data' => [
                    'campania_id' => $campania->id_campania,
                    'estado' => $campania->estado,
                    'total_destinatarios' => (int) $campania->total_destinatarios,
                    'chunks' => count($chunksCreated),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar campaña',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/whatsapp/campaign/activate
     * Activa una campaña de WhatsApp masiva
     */
    public function activate(Request $request)
    {
        // Compatibilidad legacy: activate = create (borrador) + start (FIFO)
        $createResponse = $this->createCampaign($request);
        $createPayload = $createResponse->getData(true);

        if (($createPayload['success'] ?? false) !== true) {
            return $createResponse;
        }

        $campaignId = $createPayload['data']['campania_id'] ?? null;
        if (!$campaignId) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo obtener el ID de campaña creada'
            ], 500);
        }

        $startResponse = $this->startCampaign($request, $campaignId);
        $startPayload = $startResponse->getData(true);

        if (($startPayload['success'] ?? false) !== true) {
            return $startResponse;
        }

        return response()->json([
            'success' => true,
            'message' => 'Campaña activada exitosamente',
            'data' => $startPayload['data'] ?? []
        ], 201);
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
        $progreso = $campania->getProgressPercentage();

        return response()->json([
            'success' => true,
            'data' => [
                'id_campania' => $campania->id_campania,
                'servicio' => $campania->servicio->nombre ?? 'N/A',
                'estado' => $campania->estado,
                'progreso' => [
                    'total' => (int) $campania->total_destinatarios,
                    'exitosos' => (int) $campania->envios_exitosos,
                    'fallidos' => (int) $campania->envios_fallidos,
                    'pendientes' => (int) $campania->envios_pendientes,
                    'porcentaje' => $progreso,
                ],
                'progress_milestone' => (int) ($campania->progress_milestone ?? 0),
                'progress_version' => (int) ($campania->progress_version ?? 0),
                'envios_hoy' => app(WhatsappDailyQuotaService::class)->getEnviosDia(),
                'limite_diario' => app(WhatsappDailyQuotaService::class)->getLimiteDiario(),
                'fecha_inicio' => $campania->fecha_inicio?->format('Y-m-d H:i:s'),
                'fecha_fin' => $campania->fecha_fin?->format('Y-m-d H:i:s'),
                'duracion' => $campania->fecha_inicio && $campania->fecha_fin 
                    ? $campania->fecha_inicio->diffForHumans($campania->fecha_fin, true)
                    : 'En proceso'
            ]
        ]);
    }

    /**
     * GET /api/whatsapp/campaign/{id}/progress-flag
     * Retorna un flag de cambio de progreso para evitar pedir status completo en cada polling.
     */
    public function progressFlag(Request $request, $id)
    {
        $campania = Campania::find($id);

        if (!$campania) {
            return response()->json([
                'success' => false,
                'message' => 'Campaña no encontrada'
            ], 404);
        }

        $sinceVersion = (int) $request->query('since_version', -1);
        $currentVersion = (int) ($campania->progress_version ?? 0);
        $changed = $sinceVersion < 0 || $currentVersion !== $sinceVersion;

        return response()->json([
            'success' => true,
            'data' => [
                'id_campania' => $campania->id_campania,
                'estado' => $campania->estado,
                'changed' => $changed,
                'progress_version' => $currentVersion,
                'progress_milestone' => (int) ($campania->progress_milestone ?? 0),
                'porcentaje' => $campania->getProgressPercentage(),
                'updated_at' => $campania->progress_milestone_updated_at?->format('Y-m-d H:i:s'),
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
            $service = null;
            if ($request->filled('service')) {
                $service = (string) $request->input('service');
                $idProducto = $this->resolveServiceToProducto($service);
            } elseif ($request->filled('id_servicio')) {
                $idProducto = (int) $request->input('id_servicio');
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

            if ($service) {
                $preview = $this->buildPreviewDataByService($service);
                if ($preview) {
                    $est['service'] = $service;
                    $est['id_producto'] = $preview['id_producto'];
                    $est['total_destinatarios'] = $preview['total_destinatarios'];
                    $est['estimated_days'] = $preview['estimated_days'];
                }
            }

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
        $limit = (int) $request->query('limit', 10);
        $perPage = (int) $request->query('per_page', $limit > 0 ? $limit : 15);
        $estado = $request->query('estado');

        $query = Campania::with('servicio:id_servicio,nombre')
            ->orderBy('created_at', 'desc');

        if ($estado && in_array($estado, ['borrador', 'pendiente', 'en_proceso', 'pausada_hasta_mañana', 'pausada_fuera_horario', 'completada', 'cancelada', 'error'])) {
            $query->where('estado', $estado);
        }

        $campanias = $query->paginate($perPage);

        $quotaService = app(WhatsappDailyQuotaService::class);
        $enviosDia = $quotaService->getEnviosDia();
        $limiteDiario = $quotaService->getLimiteDiario();

        $items = collect($campanias->items())->map(function ($campania) {
            return [
                'id_campania' => $campania->id_campania,
                'servicio' => $campania->servicio->nombre ?? 'N/A',
                'estado' => $campania->estado,
                'total_destinatarios' => (int) $campania->total_destinatarios,
                'envios_exitosos' => (int) $campania->envios_exitosos,
                'envios_fallidos' => (int) $campania->envios_fallidos,
                'envios_pendientes' => (int) $campania->envios_pendientes,
                'porcentaje' => $campania->getProgressPercentage(),
                'progress_milestone' => (int) ($campania->progress_milestone ?? 0),
                'progress_version' => (int) ($campania->progress_version ?? 0),
                'can_be_started' => $campania->canBeStarted(),
                'fecha_inicio' => $campania->fecha_inicio?->format('Y-m-d H:i:s'),
                'fecha_fin' => $campania->fecha_fin?->format('Y-m-d H:i:s'),
            ];
        })->values();

        $activeCampaign = $items->first(function ($campania) {
            return in_array($campania['estado'], ['en_proceso', 'pausada_hasta_mañana']);
        });

        return response()->json([
            'success' => true,
            'active_campaign' => $activeCampaign,
            'data' => [
                'campanias' => $items,
                // Compatibilidad con clientes que esperaban data.data
                'data' => $items,
            ],
            'envios_hoy' => $enviosDia,
            'limite_diario' => $limiteDiario,
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

    private function getDestinatariosByProducto(int $idProducto)
    {
        return DB::table('modalservicios')
            ->where('id_producto', $idProducto)
            ->where('estado', 1)
            ->select('id_modalservicio', 'nombre', 'telefono', 'id_producto')
            ->orderByDesc('id_modalservicio')
            ->get()
            ->unique('telefono')
            ->values();
    }

    private function resolveServiceToProducto(string $service): ?int
    {
        return self::SERVICE_MAP[$service] ?? null;
    }

    private function buildPreviewDataByService(string $service): ?array
    {
        $idProducto = $this->resolveServiceToProducto($service);
        if (!$idProducto) {
            return null;
        }

        $destinatarios = $this->getDestinatariosByProducto($idProducto);
        $total = $destinatarios->count();
        $dailyLimit = (int) config('whatsapp.daily_limit', 50);

        return [
            'id_producto' => $idProducto,
            'total_destinatarios' => $total,
            'estimated_days' => $total > 0 ? (int) ceil($total / max(1, $dailyLimit)) : 0,
        ];
    }
}
