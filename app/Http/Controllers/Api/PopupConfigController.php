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
        $hex     = ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'];
        $hexReq  = ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];
        $imgRule = 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120';

        $reglas = [
            // ── Texto (compartido) ───────────────────────────────────────────
            'title_text'  => 'required|string|min:5|max:80',
            'button_text' => 'required|string|min:2|max:25',

            // ── Desktop ─────────────────────────────────────────────────────
            'title_color'        => $hexReq,
            'button_color'       => $hexReq,
            'service_color'      => $hexReq,
            'service_color_2'    => $hex,
            'gradient_direction' => 'required|string|max:20',
            'trigger_time'       => 'required|integer|between:1,100',
            'left_image'         => $imgRule,
            'left_opacity'       => 'nullable|integer|min:0|max:100',
            'left_alt'           => 'nullable|string|max:80',
            'remove_left_image'  => 'nullable|in:0,1',
            'right_image'        => $imgRule,
            'right_opacity'      => 'nullable|integer|min:0|max:100',
            'right_alt'          => 'nullable|string|max:80',
            'remove_right_image' => 'nullable|in:0,1',

            // ── Mobile ──────────────────────────────────────────────────────
            'mobile_trigger_time'       => 'required|integer|between:1,100',
            'mobile_title_color'        => $hexReq,
            'mobile_button_color'       => $hexReq,
            'mobile_service_color'      => $hexReq,
            'mobile_service_color_2'    => $hex,
            'mobile_gradient_direction' => 'required|string|max:20',
            'mobile_image'              => $imgRule,
            'mobile_opacity'            => 'nullable|integer|min:0|max:100',
            'mobile_alt'                => 'nullable|string|max:80',
            'remove_mobile_image'       => 'nullable|in:0,1',
        ];

        if ($esCreacion) {
            $reglas['id_producto'] = 'required|integer|exists:productos,id_producto';
        }

        return $reglas;
    }

    // ─── Helper: extraer campos del request ──────────────────────────────────

    private function extractData(Request $request, bool $isNew, ?PopupConfig $config = null): array
    {
        return [
            // Texto (compartido)
            'title_text'  => $request->input('title_text'),
            'button_text' => $request->input('button_text'),

            // Desktop
            'title_color'        => $request->input('title_color'),
            'button_color'       => $request->input('button_color'),
            'service_color'      => $request->input('service_color'),
            'service_color_2'    => $request->input('service_color_2'),
            'gradient_direction' => $request->input('gradient_direction'),
            'trigger_time'       => (int) $request->input('trigger_time'),
            'left_opacity'       => (int) ($request->input('left_opacity') ?? ($config?->left_opacity ?? 85)),
            'left_alt'           => $request->input('left_alt'),
            'right_opacity'      => (int) ($request->input('right_opacity') ?? ($config?->right_opacity ?? 100)),
            'right_alt'          => $request->input('right_alt'),

            // Mobile
            'mobile_trigger_time'       => (int) $request->input('mobile_trigger_time'),
            'mobile_title_color'        => $request->input('mobile_title_color'),
            'mobile_button_color'       => $request->input('mobile_button_color'),
            'mobile_service_color'      => $request->input('mobile_service_color'),
            'mobile_service_color_2'    => $request->input('mobile_service_color_2'),
            'mobile_gradient_direction' => $request->input('mobile_gradient_direction'),
            'mobile_opacity'            => (int) ($request->input('mobile_opacity') ?? ($config?->mobile_opacity ?? 100)),
            'mobile_alt'                => $request->input('mobile_alt'),
        ];
    }

    // ─── Helper: procesar imágenes ───────────────────────────────────────────

    private function procesarImagenes(Request $request, array &$data, int $idProducto, bool $isNew, ?PopupConfig $config = null): ?array
    {
        foreach (['left', 'right', 'mobile'] as $slot) {
            if ($request->hasFile("{$slot}_image")) {
                if ($isNew) {
                    $resultado = $this->subirImagen($request->file("{$slot}_image"), $idProducto, $slot);
                } else {
                    $resultado = $this->reemplazarImagen(
                        $request->file("{$slot}_image"),
                        $idProducto,
                        $slot,
                        $config?->{"{$slot}_image_public_id"},
                        $config?->{"{$slot}_image_url"}
                    );
                }

                if (empty($resultado['url'])) {
                    return ['error' => "Fallo al subir la imagen {$slot}."];
                }

                $data["{$slot}_image_url"]       = $resultado['url'];
                $data["{$slot}_image_public_id"] = $resultado['public_id'] ?? null;

            } elseif (!$isNew && $request->input("remove_{$slot}_image") == '1') {
                $this->borrarImagen($config?->{"{$slot}_image_public_id"});
                $data["{$slot}_image_url"]       = null;
                $data["{$slot}_image_public_id"] = null;
            }
        }

        return null; // sin error
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

            $data = $this->extractData($request, true);
            $data['id_producto'] = $idProducto;
            $data['created_by']  = $request->user()?->id;
            $data['updated_by']  = $request->user()?->id;

            $error = $this->procesarImagenes($request, $data, $idProducto, true);
            if ($error) {
                return response()->json(['success' => false, 'message' => $error['error']], 500);
            }

            $config = PopupConfig::create($data);
            $config->load('producto:id_producto,nombre');

            return response()->json([
                'success' => true,
                'message' => 'Pop-Up creado correctamente',
                'data'    => $config,
            ], 201);

        } catch (\Exception $e) {
            Log::error('PopupConfigController@store: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Error al crear el Pop-Up', 'error' => 'Error interno del servidor'], 500);
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

            $data = $this->extractData($request, false, $config);
            $data['updated_by'] = $request->user()?->id;

            $error = $this->procesarImagenes($request, $data, $idProducto, false, $config);
            if ($error) {
                return response()->json(['success' => false, 'message' => $error['error']], 500);
            }

            $config->fill($data)->save();
            $config->load('producto:id_producto,nombre');

            return response()->json([
                'success' => true,
                'message' => 'Pop-Up actualizado correctamente',
                'data'    => $config,
            ]);

        } catch (\Exception $e) {
            Log::error('PopupConfigController@update: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Error al actualizar el Pop-Up', 'error' => 'Error interno del servidor'], 500);
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
            Log::error('PopupConfigController@destroy: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Error al eliminar el Pop-Up', 'error' => 'Error interno del servidor'], 500);
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