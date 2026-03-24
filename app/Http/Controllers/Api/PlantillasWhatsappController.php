<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlantillaWhatsapp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploadService;

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
     * Endpoint for whatsapp-service consumption. Protects via X-API-Key header.
     */
    public function showByProductoNumero(Request $request, $id_producto, $numero_plantilla)
    {
        $apiKey = $request->header('x-api-key') ?? $request->header('X-API-Key');
        if (!$apiKey || $apiKey !== config('services.whatsapp.apikey')) {
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
     * Update plantilla (multipart/form-data). Auth required by routes.
     */
    public function actualizar(Request $request, $id)
    {
        $plantilla = PlantillaWhatsapp::find($id);
        if (! $plantilla) {
            return response()->json(['success' => false, 'message' => 'Plantilla no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'mensaje' => 'required|string|max:5000',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $plantilla->mensaje = $request->input('mensaje');

                if ($request->hasFile('imagen')) {
                    $archivo = $request->file('imagen');
                    $uploader = new FileUploadService();
                    // Build a descriptive filename for local fallback / Cloudinary public_id suggestion
                    $ext = $archivo->getClientOriginalExtension() ?: 'jpg';
                    $safeProducto = preg_replace('/[^A-Za-z0-9_-]/', '_', (string)$plantilla->id_producto);
                    $safeNumero = preg_replace('/[^A-Za-z0-9_-]/', '_', (string)$plantilla->numero_plantilla);
                    $timestamp = time();
                    $filename = "plantilla_{$safeProducto}_{$safeNumero}_{$timestamp}.{$ext}";
                    $carpeta = "plantillas/whatsapp/producto-{$safeProducto}/plantilla-{$safeNumero}";

                    // For plantillas, ensure we remove previous cloud or local image when updating
                    $resultado = $uploader->subir($archivo, $carpeta, $plantilla->imagen_public_id, $plantilla->imagen_url, [
                        'delete_previous_cloud' => true,
                        'delete_previous_local' => true,
                        'filename' => $filename,
                    ]);

                    if (empty($resultado['url'])) {
                        // Log details to help debugging (Cloudinary fallback, storage, exceptions, etc.)
                        Log::error('PlantillasWhatsappController: fallo al subir imagen', ['plantilla_id' => $plantilla->id, 'resultado' => $resultado]);
                        // If the client sent a file but upload failed, return error instead of saving with null image
                        return response()->json(['success' => false, 'message' => 'Fallo al subir la imagen. No se actualizó la plantilla.'], 500);
                    }

                    $plantilla->imagen_url = $resultado['url'];
                    $plantilla->imagen_public_id = $resultado['public_id'] ?? null;
                }

            $plantilla->updated_by = $request->user()?->id ?? $plantilla->updated_by;
            $plantilla->save();

            return response()->json(['success' => true, 'message' => 'Plantilla WhatsApp actualizada exitosamente', 'data' => $plantilla]);

        } catch (\Exception $e) {
            Log::error('Error actualizando plantilla', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al actualizar plantilla', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Crear plantillas de prueba para un producto (devuelve las 3 variantes).
     * Uso: POST /api/plantillas/whatsapp/create-test  { id_producto: 5 }
     * Protegido por auth:sanctum y rol (marketing, administrador).
     */
    

}
