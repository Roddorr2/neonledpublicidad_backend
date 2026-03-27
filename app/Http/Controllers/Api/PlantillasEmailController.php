<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlantillaEmail;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PlantillasEmailController extends Controller
{
    public function index()
    {
        $plantillas = PlantillaEmail::orderBy('id_producto')->orderBy('numero_plantilla')->get();
        return response()->json(['success' => true, 'data' => $plantillas]);
    }

    public function show($id)
    {
        $plantilla = PlantillaEmail::find($id);
        if (! $plantilla) {
            return response()->json(['success' => false, 'message' => 'Plantilla no encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => $plantilla]);
    }

    /**
     * Endpoint consumido por servicios externos (header X-API-Key).
     */
    public function showByProductoNumero(Request $request, $id_producto, $numero_plantilla)
    {
        $apiKey = $request->header('x-api-key') ?? $request->header('X-API-Key');
        $expectedApiKey = env('EMAIL_SERVICE_API_KEY', env('WHATSAPP_SERVICE_API_KEY'));

        if (! $apiKey || ! $expectedApiKey || $apiKey !== $expectedApiKey) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $plantilla = PlantillaEmail::where('id_producto', $id_producto)
            ->where('numero_plantilla', $numero_plantilla)
            ->first();

        if (! $plantilla) {
            return response()->json(['success' => false, 'message' => 'Plantilla no encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => $plantilla]);
    }

    /**
     * Actualiza plantilla email (multipart/form-data opcional para imagen).
     */
    public function actualizar(Request $request, $id)
    {
        $plantilla = PlantillaEmail::find($id);
        if (! $plantilla) {
            return response()->json(['success' => false, 'message' => 'Plantilla no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'nullable|string|max:255',
            'asunto' => 'required|string|max:255',
            'encabezado' => 'required|string|max:255',
            'mensaje' => 'required|string|max:10000',
            'mensaje_boton' => 'nullable|string|max:255',
            'url_boton' => 'nullable|url|max:2048',
            'footer' => 'nullable|string|max:10000',
            'red_facebook' => 'nullable|url|max:2048',
            'red_tiktok' => 'nullable|url|max:2048',
            'red_instagram' => 'nullable|url|max:2048',
            'red_linkedin' => 'nullable|url|max:2048',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $plantilla->nombre = $request->input('nombre', $plantilla->nombre);
            $plantilla->asunto = $request->input('asunto');
            $plantilla->encabezado = $request->input('encabezado');
            $plantilla->mensaje = $request->input('mensaje');
            $plantilla->mensaje_boton = $request->input('mensaje_boton');
            $plantilla->url_boton = $request->input('url_boton');
            $plantilla->footer = $request->input('footer');
            $plantilla->red_facebook = $request->input('red_facebook');
            $plantilla->red_tiktok = $request->input('red_tiktok');
            $plantilla->red_instagram = $request->input('red_instagram');
            $plantilla->red_linkedin = $request->input('red_linkedin');

            if ($request->hasFile('imagen')) {
                $archivo = $request->file('imagen');
                $uploader = new FileUploadService();
                $ext = $archivo->getClientOriginalExtension() ?: 'jpg';
                $filename = "template_{$plantilla->id_producto}_{$plantilla->numero_plantilla}.{$ext}";

                $resultado = $uploader->subir($archivo, 'plantillas_email', null, $plantilla->imagen_url, [
                    'delete_previous_cloud' => false,
                    'delete_previous_local' => true,
                    'filename' => $filename,
                    'entity_id' => $plantilla->id_producto,
                ]);

                if (empty($resultado['url'])) {
                    Log::error('PlantillasEmailController: fallo al subir imagen', [
                        'plantilla_id' => $plantilla->id_plantilla_email,
                        'resultado' => $resultado,
                    ]);
                    return response()->json(['success' => false, 'message' => 'Fallo al subir la imagen. No se actualizó la plantilla.'], 500);
                }

                $plantilla->imagen_url = $resultado['url'];
            }

            $plantilla->updated_by = $request->user()?->id ?? $plantilla->updated_by;
            $plantilla->save();

            return response()->json([
                'success' => true,
                'message' => 'Plantilla email actualizada exitosamente',
                'data' => $plantilla,
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando plantilla email', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar plantilla email',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
