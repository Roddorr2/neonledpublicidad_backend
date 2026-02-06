<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campania;
use App\Models\modalservicios;
use App\Jobs\SendWhatsappCampaignJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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
            'id_producto' => 'required|exists:productos,id_producto',
            'imagen_url' => 'required|url|max:100',
            'parrafo' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 1. Consultar destinatarios desde modal_wats con sus modalservicios
            $watModals = DB::table('modal_wats')
                ->join('modalservicios', 'modal_wats.id_modalservicio', '=', 'modalservicios.id_modalservicio')
                ->where('modalservicios.id_producto', $request->id_producto)
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
                'id_servicio' => $request->id_producto, // Usar id_producto en id_servicio
                'parrafo' => $request->parrafo,
                'imagen_url' => $request->imagen_url,
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
                    $request->parrafo,
                    $request->imagen_url,
                    $request->id_producto
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
}
