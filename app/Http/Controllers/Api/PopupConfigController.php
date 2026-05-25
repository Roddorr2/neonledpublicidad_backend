<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PopupConfig;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PopupConfigController extends Controller
{
    // ─── Helpers privados ────────────────────────────────────────────────────

    /**
     * Sube una imagen usando FileUploadService (igual que PlantillasWhatsappController).
     */
    private function subirImagen($archivo, int $idProducto, string $slot): array
    {
        $ext      = $archivo->getClientOriginalExtension() ?: 'jpg';
        $carpeta  = "popup_configs/producto-{$idProducto}/{$slot}";
        $filename = "popup_{$idProducto}_{$slot}_" . time() . ".{$ext}";

        $uploader = new FileUploadService();
        return $uploader->subir($archivo, $carpeta, null, null, [
            'filename' => $filename,
        ]);
    }

    /**
     * Reemplaza una imagen (borra la anterior en Cloudinary y sube la nueva).
     */
    private function reemplazarImagen($archivo, int $idProducto, string $slot, ?string $publicIdAnterior, ?string $urlAnterior): array
    {
        $ext      = $archivo->getClientOriginalExtension() ?: 'jpg';
        $carpeta  = "popup_configs/producto-{$idProducto}/{$slot}";
        $filename = "popup_{$idProducto}_{$slot}_" . time() . ".{$ext}";

        $uploader = new FileUploadService();
        return $uploader->subir($archivo, $carpeta, $publicIdAnterior, $urlAnterior, [
            'delete_previous_cloud' => true,
            'delete_previous_local' => true,
            'filename'              => $filename,
        ]);
    }

    /**
     * Borra una imagen de Cloudinary usando el método oficial del proyecto.
     */
    private function borrarImagen(?string $publicId): void
    {
        if (empty($publicId)) return;
        try {
            (new FileUploadService())->eliminarPublicId($publicId);
        } catch (\Throwable $e) {
            Log::warning("PopupConfigController: no se pudo eliminar imagen [{$publicId}]: " . $e->getMessage());
        }
    }

    // ─── Validaciones ─────────────────────────────────────────────────────────

    private function reglasValidacion(bool $esCreacion = true): array
    {
        $reglas = [
            'title_text'         => 'required|string|min:5|max:80',
            'title_color'        => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'button_text'        => 'required|string|min:2|max:25',
            'button_color'       => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'service_color'      => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'service_color_2'    => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'gradient_direction' => 'required|string|max:20',
            'trigger_time'       => 'required|integer|in:3,5,8,12,20,30,60',
            'left_image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'left_opacity'       => 'nullable|integer|min:0|max:100',
            'left_alt'           => 'nullable|string|max:255',
            // remove_left_image: si viene "1" se borra la imagen izquierda
            'remove_left_image'  => 'nullable|in:0,1',
            'right_image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'right_opacity'      => 'nullable|integer|min:0|max:100',
            'right_alt'          => 'nullable|string|max:255',
            'remove_right_image' => 'nullable|in:0,1',
            'mobile_image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'mobile_opacity'     => 'nullable|integer|min:0|max:100',
            'mobile_alt'         => 'nullable|string|max:255',
            'remove_mobile_image'=> 'nullable|in:0,1',
        ];

        if ($esCreacion) {
            $reglas['id_producto'] = 'required|integer|exists:productos,id_producto';
        }

        return $reglas;
    }

    // ─── ENDPOINTS PRIVADOS ───────────────────────────────────────────────────

    public function index()
    {
        $configs = PopupConfig::with('producto:id_producto,nombre')->orderBy('id_producto')->get();
        return response()->json(['success' => true, 'data' => $configs]);
    }

    public function show(int $id)
    {
        $config = PopupConfig::with('producto:id_producto,nombre')->find($id);
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Configuración no encontrada'], 404);
        }
        return response()->json(['success' => true, 'data' => $config]);
    }

    public function showByProducto(int $id_producto)
    {
        $config = PopupConfig::with('producto:id_producto,nombre')
            ->byProducto($id_producto)
            ->first();
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'No existe configuración para este producto'], 404);
        }
        return response()->json(['success' => true, 'data' => $config]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->reglasValidacion(true));
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if (PopupConfig::where('id_producto', $request->input('id_producto'))->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una configuración para este producto. Usa el endpoint de actualización.',
            ], 422);
        }

        try {
            $idProducto = (int) $request->input('id_producto');

            $data = [
                'id_producto'        => $idProducto,
                'title_text'         => $request->input('title_text'),
                'title_color'        => $request->input('title_color'),
                'button_text'        => $request->input('button_text'),
                'button_color'       => $request->input('button_color'),
                'service_color'      => $request->input('service_color'),
                'service_color_2'    => $request->input('service_color_2'),
                'gradient_direction' => $request->input('gradient_direction'),
                'trigger_time'       => (int) $request->input('trigger_time'),
                'left_opacity'       => (int) ($request->input('left_opacity') ?? 85),
                'left_alt'           => $request->input('left_alt'),
                'right_opacity'      => (int) ($request->input('right_opacity') ?? 100),
                'right_alt'          => $request->input('right_alt'),
                'mobile_opacity'     => (int) ($request->input('mobile_opacity') ?? 100),
                'mobile_alt'         => $request->input('mobile_alt'),
                'created_by'         => $request->user()?->id,
                'updated_by'         => $request->user()?->id,
            ];

            foreach (['left', 'right', 'mobile'] as $slot) {
                if ($request->hasFile("{$slot}_image")) {
                    $resultado = $this->subirImagen($request->file("{$slot}_image"), $idProducto, $slot);
                    if (empty($resultado['url'])) {
                        return response()->json(['success' => false, 'message' => "Fallo al subir la imagen {$slot}."], 500);
                    }
                    $data["{$slot}_image_url"]       = $resultado['url'];
                    $data["{$slot}_image_public_id"] = $resultado['public_id'] ?? null;
                }
            }

            $config = PopupConfig::create($data);
            $config->load('producto:id_producto,nombre');

            return response()->json([
                'success' => true,
                'message' => 'Pop-Up creado correctamente',
                'data'    => $config,
            ], 201);

        } catch (\Exception $e) {
            Log::error('PopupConfigController@store: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear el Pop-Up', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $config = PopupConfig::find($id);
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Configuración no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), $this->reglasValidacion(false));
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $idProducto = (int) $config->id_producto;

            $config->title_text         = $request->input('title_text');
            $config->title_color        = $request->input('title_color');
            $config->button_text        = $request->input('button_text');
            $config->button_color       = $request->input('button_color');
            $config->service_color      = $request->input('service_color');
            $config->service_color_2    = $request->input('service_color_2');
            $config->gradient_direction = $request->input('gradient_direction');
            $config->trigger_time       = (int) $request->input('trigger_time');
            $config->left_opacity       = (int) ($request->input('left_opacity') ?? $config->left_opacity);
            $config->left_alt           = $request->input('left_alt');
            $config->right_opacity      = (int) ($request->input('right_opacity') ?? $config->right_opacity);
            $config->right_alt          = $request->input('right_alt');
            $config->mobile_opacity     = (int) ($request->input('mobile_opacity') ?? $config->mobile_opacity);
            $config->mobile_alt         = $request->input('mobile_alt');
            $config->updated_by         = $request->user()?->id;

            foreach (['left', 'right', 'mobile'] as $slot) {
                $removeKey = "remove_{$slot}_image";

                if ($request->hasFile("{$slot}_image")) {
                    // Subir nueva imagen (reemplaza la anterior en Cloudinary)
                    $resultado = $this->reemplazarImagen(
                        $request->file("{$slot}_image"),
                        $idProducto,
                        $slot,
                        $config->{"{$slot}_image_public_id"},
                        $config->{"{$slot}_image_url"}
                    );
                    if (empty($resultado['url'])) {
                        return response()->json(['success' => false, 'message' => "Fallo al subir la imagen {$slot}."], 500);
                    }
                    $config->{"{$slot}_image_url"}       = $resultado['url'];
                    $config->{"{$slot}_image_public_id"} = $resultado['public_id'] ?? null;

                } elseif ($request->input($removeKey) == '1') {
                    // El frontend indica explícitamente que se eliminó la imagen
                    $this->borrarImagen($config->{"{$slot}_image_public_id"});
                    $config->{"{$slot}_image_url"}       = null;
                    $config->{"{$slot}_image_public_id"} = null;
                }
                // Si no viene archivo NI remove_*=1, la imagen existente se conserva sin cambios
            }

            $config->save();
            $config->load('producto:id_producto,nombre');

            return response()->json([
                'success' => true,
                'message' => 'Pop-Up actualizado correctamente',
                'data'    => $config,
            ]);

        } catch (\Exception $e) {
            Log::error('PopupConfigController@update: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar el Pop-Up', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id)
    {
        $config = PopupConfig::find($id);
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Configuración no encontrada'], 404);
        }

        try {
            foreach (['left', 'right', 'mobile'] as $slot) {
                $this->borrarImagen($config->{"{$slot}_image_public_id"});
            }
            $config->delete();
            return response()->json(['success' => true, 'message' => 'Pop-Up eliminado correctamente']);

        } catch (\Exception $e) {
            Log::error('PopupConfigController@destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el Pop-Up', 'error' => $e->getMessage()], 500);
        }
    }

    // ─── ENDPOINT PÚBLICO ─────────────────────────────────────────────────────

    public function showPublic(int $id_producto)
    {
        $config = PopupConfig::byProducto($id_producto)->first();
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'No existe configuración para este producto'], 404);
        }
        return response()->json(['success' => true, 'data' => $config]);
    }
}
