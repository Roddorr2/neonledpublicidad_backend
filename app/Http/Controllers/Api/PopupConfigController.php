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
    public function index()
    {
        $configs = PopupConfig::with(['subservicio.servicio'])->get();

        return response()->json(['success' => true, 'data' => $configs]);
    }

    public function show($id)
    {
        $config = PopupConfig::find($id);
        if (! $config) {
            return response()->json(['success' => false, 'message' => 'Configuración no encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => $config]);
    }

    public function showByProducto($id_producto)
    {
        $config = PopupConfig::byProducto($id_producto)->first();
        if (! $config) {
            return response()->json(['success' => false, 'message' => 'Configuración no encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => $config]);
    }

    public function showByProductoPublic($id_producto)
    {
        $config = PopupConfig::byProducto($id_producto)->first();
        if (! $config) {
            return response()->json(['success' => false, 'message' => 'Configuración no encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => $config]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_producto'        => 'required|integer|exists:productos,id_producto|unique:popup_configs,id_producto',
            'title_text'         => 'required|string|min:5|max:80',
            'button_text'        => 'required|string|min:2|max:25',
            'service_color'      => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'title_color'        => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'button_color'       => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'service_color_2'    => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'gradient_direction' => 'required|string|max:20',
            'trigger_time'       => 'required|in:3,5,8',
            'left_image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'left_opacity'       => 'nullable|integer|min:0|max:100',
            'right_image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'right_opacity'      => 'nullable|integer|min:0|max:100',
            'mobile_image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'mobile_opacity'     => 'nullable|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->only([
                'id_producto', 'title_text', 'title_color', 'button_text', 'button_color',
                'service_color', 'service_color_2', 'gradient_direction', 'trigger_time',
                'left_opacity', 'left_alt', 'right_opacity', 'right_alt', 'mobile_opacity', 'mobile_alt',
            ]);

            $carpeta = "popup_configs/producto-{$request->id_producto}";

            if ($request->hasFile('left_image')) {
                $resultado              = $this->uploadImage($request->file('left_image'), $carpeta, null, null);
                $data['left_image_url'] = $resultado['url'];
                $data['left_public_id'] = $resultado['public_id'];
            }

            if ($request->hasFile('right_image')) {
                $resultado               = $this->uploadImage($request->file('right_image'), $carpeta, null, null);
                $data['right_image_url'] = $resultado['url'];
                $data['right_public_id'] = $resultado['public_id'];
            }

            if ($request->hasFile('mobile_image')) {
                $resultado                = $this->uploadImage($request->file('mobile_image'), $carpeta, null, null);
                $data['mobile_image_url'] = $resultado['url'];
                $data['mobile_public_id'] = $resultado['public_id'];
            }

            $data['created_by'] = $request->user()?->id;

            $config = PopupConfig::create($data);

            return response()->json(['success' => true, 'message' => 'Configuración creada exitosamente', 'data' => $config], 201);
        } catch (\Exception $e) {
            Log::error('Error creando configuración popup', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al crear configuración', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $config = PopupConfig::find($id);
        if (! $config) {
            return response()->json(['success' => false, 'message' => 'Configuración no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title_text'         => 'required|string|min:5|max:80',
            'button_text'        => 'required|string|min:2|max:25',
            'service_color'      => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'title_color'        => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'button_color'       => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'service_color_2'    => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'gradient_direction' => 'required|string|max:20',
            'trigger_time'       => 'required|in:3,5,8',
            'left_image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'left_opacity'       => 'nullable|integer|min:0|max:100',
            'right_image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'right_opacity'      => 'nullable|integer|min:0|max:100',
            'mobile_image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'mobile_opacity'     => 'nullable|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->only([
                'title_text', 'title_color', 'button_text', 'button_color',
                'service_color', 'service_color_2', 'gradient_direction', 'trigger_time',
                'left_opacity', 'left_alt', 'right_opacity', 'right_alt', 'mobile_opacity', 'mobile_alt',
            ]);

            $carpeta = "popup_configs/producto-{$config->id_producto}";

            if ($request->hasFile('left_image')) {
                $resultado              = $this->uploadImage($request->file('left_image'), $carpeta, $config->left_public_id, $config->left_image_url);
                $data['left_image_url'] = $resultado['url'];
                $data['left_public_id'] = $resultado['public_id'];
            }

            if ($request->hasFile('right_image')) {
                $resultado               = $this->uploadImage($request->file('right_image'), $carpeta, $config->right_public_id, $config->right_image_url);
                $data['right_image_url'] = $resultado['url'];
                $data['right_public_id'] = $resultado['public_id'];
            }

            if ($request->hasFile('mobile_image')) {
                $resultado                = $this->uploadImage($request->file('mobile_image'), $carpeta, $config->mobile_public_id, $config->mobile_image_url);
                $data['mobile_image_url'] = $resultado['url'];
                $data['mobile_public_id'] = $resultado['public_id'];
            }

            $data['updated_by'] = $request->user()?->id;

            $config->update($data);

            return response()->json(['success' => true, 'message' => 'Configuración actualizada exitosamente', 'data' => $config]);
        } catch (\Exception $e) {
            Log::error('Error actualizando configuración popup', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al actualizar configuración', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $config = PopupConfig::find($id);
        if (! $config) {
            return response()->json(['success' => false, 'message' => 'Configuración no encontrada'], 404);
        }

        try {
            // Delete images
            if ($config->left_public_id) {
                $this->deleteCloudinaryImage($config->left_public_id);
            }
            if ($config->right_public_id) {
                $this->deleteCloudinaryImage($config->right_public_id);
            }
            if ($config->mobile_public_id) {
                $this->deleteCloudinaryImage($config->mobile_public_id);
            }

            $config->delete();

            return response()->json(['success' => true, 'message' => 'Configuración eliminada exitosamente']);
        } catch (\Exception $e) {
            Log::error('Error eliminando configuración popup', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al eliminar configuración', 'error' => $e->getMessage()], 500);
        }
    }

    private function uploadImage($file, $carpeta, $publicIdAnterior = null, $urlAnterior = null)
    {
        $uploader  = new FileUploadService;
        $resultado = $uploader->subir($file, $carpeta, $publicIdAnterior, $urlAnterior, [
            'delete_previous_cloud' => true,
            'delete_previous_local' => true,
        ]);

        if (empty($resultado['url'])) {
            throw new \Exception('Fallo al subir la imagen');
        }

        return $resultado;
    }

    private function deleteCloudinaryImage($publicId)
    {
        try {
            if (class_exists(\CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::class)) {
                \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::destroy($publicId);
            } else {
                $cloudinary = app(\Cloudinary\Cloudinary::class);
                $cloudinary->uploadApi()->destroy($publicId);
            }
        } catch (\Exception $e) {
            Log::warning('No se pudo eliminar imagen de Cloudinary: ' . $e->getMessage());
        }
    }
}
