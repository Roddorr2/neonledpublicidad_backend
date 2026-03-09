<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlantillaWhatsapp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Cloudinary;

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
        if (!$apiKey || $apiKey !== env('WHATSAPP_SERVICE_API_KEY')) {
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
                $file = $request->file('imagen');

                // If Cloudinary configured, upload there
                if (env('CLOUDINARY_URL') || (env('CLOUDINARY_CLOUD_NAME') && env('CLOUDINARY_KEY') && env('CLOUDINARY_SECRET'))) {
                    $cloudinary = new Cloudinary([
                        'cloud' => [
                            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                            'api_key'    => env('CLOUDINARY_KEY'),
                            'api_secret' => env('CLOUDINARY_SECRET'),
                        ],
                        'url' => ['secure' => false],
                        'api' => ['upload_prefix' => 'http://api.cloudinary.com'],
                    ]);

                    $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                        'folder' => 'plantillas_whatsapp'
                    ]);

                    if ($result && isset($result['secure_url'])) {
                        // delete previous in cloudinary if exists
                        if ($plantilla->imagen_public_id) {
                            try {
                                $cloudinary->uploadApi()->destroy($plantilla->imagen_public_id);
                            } catch (\Exception $e) {
                                Log::warning('No se pudo eliminar imagen previa en Cloudinary: '.$e->getMessage());
                            }
                        }

                        $plantilla->imagen_url = $result['secure_url'];
                        $plantilla->imagen_public_id = $result['public_id'] ?? null;
                    }

                } else {
                    // store locally in public disk
                    $path = $file->store('plantillas_whatsapp', 'public');
                    $url = asset('storage/' . $path);

                    // delete previous local file if it looks like a storage url
                    if ($plantilla->imagen_url && str_contains($plantilla->imagen_url, '/storage/')) {
                        try {
                            $relative = str_replace(asset('storage/'), '', $plantilla->imagen_url);
                            Storage::disk('public')->delete($relative);
                        } catch (\Exception $e) {
                            Log::warning('No se pudo eliminar imagen local previa: '.$e->getMessage());
                        }
                    }

                    $plantilla->imagen_url = $url;
                    $plantilla->imagen_public_id = null;
                }
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
