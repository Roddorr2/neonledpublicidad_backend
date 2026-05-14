<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlantillaWhatsapp\ActualizarPlantillaWhatsappRequest;
use App\Models\PlantillaWhatsapp;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlantillasWhatsappController extends Controller
{
    public function index()
    {
        $plantillas = PlantillaWhatsapp::orderBy('id_producto')->orderBy('numero_plantilla')->get();

        return response()->json(['success' => true, 'data' => $plantillas]);
    }

    public function show($id)
    {
        $p = PlantillaWhatsapp::find($id);
        if (! $p) {
            return response()->json(['success' => false, 'message' => 'Plantilla no encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => $p]);
    }

    /**
     * Endpoint para el whatsapp-service. Protegido via X-API-Key header.
     */
    public function showByProductoNumero(Request $request, $id_producto, $numero_plantilla)
    {
        $apiKey = $request->header('x-api-key') ?? $request->header('X-API-Key');
        if (! $apiKey || $apiKey !== config('services.whatsapp.apikey')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $p = PlantillaWhatsapp::where('id_producto', $id_producto)
            ->where('numero_plantilla', $numero_plantilla)
            ->first();

        if (! $p) {
            return response()->json(['success' => false, 'message' => 'Plantilla no encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => $p]);
    }

    /**
     * Actualizar plantilla (multipart/form-data). Auth requerida por rutas.
     */
    public function actualizar(ActualizarPlantillaWhatsappRequest $request, $id)
    {
        $plantilla = PlantillaWhatsapp::find($id);
        if (! $plantilla) {
            return response()->json(['success' => false, 'message' => 'Plantilla no encontrada'], 404);
        }

        try {
            $plantilla->mensaje = $request->input('mensaje');

            if ($request->hasFile('imagen')) {
                $archivo      = $request->file('imagen');
                $uploader     = new FileUploadService;
                $ext          = $archivo->getClientOriginalExtension() ?: 'jpg';
                $safeProducto = preg_replace('/[^A-Za-z0-9_-]/', '_', (string)$plantilla->id_producto);
                $safeNumero   = preg_replace('/[^A-Za-z0-9_-]/', '_', (string)$plantilla->numero_plantilla);
                $timestamp    = time();
                $filename     = "plantilla_{$safeProducto}_{$safeNumero}_{$timestamp}.{$ext}";
                $carpeta      = "plantillas/whatsapp/producto-{$safeProducto}/plantilla-{$safeNumero}";

                $resultado = $uploader->subir($archivo, $carpeta, $plantilla->imagen_public_id, $plantilla->imagen_url, [
                    'delete_previous_cloud' => true,
                    'delete_previous_local' => true,
                    'filename'              => $filename,
                ]);

                if (empty($resultado['url'])) {
                    Log::error('PlantillasWhatsappController: fallo al subir imagen', [
                        'plantilla_id' => $plantilla->id,
                        'resultado'    => $resultado,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Fallo al subir la imagen. No se actualizó la plantilla.',
                    ], 500);
                }

                $plantilla->imagen_url       = $resultado['url'];
                $plantilla->imagen_public_id = $resultado['public_id'] ?? null;
            }

            $plantilla->updated_by = $request->user()?->id ?? $plantilla->updated_by;
            $plantilla->save();

            return response()->json([
                'success' => true,
                'message' => 'Plantilla WhatsApp actualizada exitosamente',
                'data'    => $plantilla,
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando plantilla', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar plantilla',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
