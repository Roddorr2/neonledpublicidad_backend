<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Productos;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class ProductosController extends Controller
{
    public function get()
    {
        try {
            $productosPaginados = Productos::with('empleado')->orderBy('id_producto', 'asc')->paginate(7);
            $productosMapeados = $productosPaginados->getCollection()->map(function ($producto) {
                $images = [];

                if ($producto->path_main) {
                    $images[] = [
                        'type' => 'main',
                        'url' => $producto->path_main,
                        // 'titulo' => null,
                        // 'descripcion' => null,
                        //
                    ];
                }

                if ($producto->path_background) {
                    $images[] = [
                        'type' => 'background',
                        'url' => $producto->path_background,
                        // 'titulo' => null,
                        // 'descripcion' => null,
                    ];
                }

                for ($i = 1; $i <= 3; $i++) {
                    $pathKey = 'path' . $i;
                    $titleKey = 'tituloimg' . $i;
                    $descKey = 'descripcionimg' . $i;

                    if ($producto->$pathKey) {
                        $images[] = [
                            'type' => 'secondary',
                            'url' => $producto->$pathKey,
                            'titulo' => $producto->$titleKey,
                            'descripcion' => $producto->$descKey,
                        ];
                    }
                }

                $producto->images = $images;

                // Eliminamos las columnas originales para limpiar la respuesta
                unset($producto->path1, $producto->tituloimg1, $producto->descripcionimg1);
                unset($producto->path2, $producto->tituloimg2, $producto->descripcionimg2);
                unset($producto->path3, $producto->tituloimg3, $producto->descripcionimg3);
                unset($producto->path_main, $producto->path_background);

                return $producto;
            });

            $productosPaginados->setCollection($productosMapeados);

            return response()->json([
                'status' => 200,
                'data' => $productosPaginados
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al obtener los productos',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function getById(int $id)
    {
        try{
            $producto = Productos::with(['empleado' => function ($query) {
                $query->select('id_empleado');
            }])->findOrFail($id);
            $images = [];

            if ($producto->path_main) {
                $images[] = [
                    'type' => 'main',
                    'url' => $producto->path_main,
                    // 'titulo' => null,
                    // 'descripcion' => null,
                ];
            }

            if ($producto->path_background) {
                $images[] = [
                    'type' => 'background',
                    'url' => $producto->path_background,
                    // 'titulo' => null,
                    // 'descripcion' => null,
                ];
            }

            for ($i = 1; $i <= 3; $i++) {
                $pathKey = 'path' . $i;
                $titleKey = 'tituloimg' . $i;
                $descKey = 'descripcionimg' . $i;

                if ($producto->$pathKey) {
                    $images[] = [
                        'type' => 'secondary',
                        'url' => $producto->$pathKey,
                        'titulo' => $producto->$titleKey,
                        'descripcion' => $producto->$descKey,
                    ];
                }

                unset($producto->$pathKey);
                unset($producto->$titleKey);
                unset($producto->$descKey);
            }

            unset($producto->path_main);
            unset($producto->path_background);

            $producto->images = $images;

            return response()->json([
                'status' => 200,
                'data' => $producto
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'message' => 'Producto no encontrado'
            ], 404);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 500,
                'message' => 'Error interno del servidor',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function getCompact()
    {
        try {
            $productosPaginados = Productos::select('id_producto', 'id_empleado','nombre', 'descripcion', 'path_main', 'estado')->orderBy('id_producto', 'asc')->paginate(7);
            $productosMapeados = $productosPaginados->getCollection()->map(function ($producto) {
                if ($producto->path_main && !Str::contains($producto->path_main, 'http')) {
                    $producto->path_main = Storage::url($producto->path_main);
                }
                return $producto;
            });

            $productosPaginados->setCollection($productosMapeados);

            return response()->json([
                'status' => 200,
                'data' => $productosPaginados
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al obtener los productos de forma compacta',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function create(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:250',
                'descripcion' => 'required|string|max:250',

                'id_empleado' => 'required|integer|exists:empleados,id_empleado',

                'file_main' => 'nullable|image|max:2048',
                'file_background' => 'nullable|image|max:2048',

                'tituloimg1' => 'nullable|string|max:255',
                'descripcionimg1' => 'nullable|string',
                'tituloimg2' => 'nullable|string|max:255',
                'descripcionimg2' => 'nullable|string',
                'tituloimg3' => 'nullable|string|max:255',
                'descripcionimg3' => 'nullable|string',

                'caracteristicas_descrip' => 'nullable|string',
                'ventajas_descrip' => 'nullable|string',
                'consumoenergetico_descrip' => 'nullable|string',
                'iluminacion_descrip' => 'nullable|string',
                'durabilidad_descrip' => 'nullable|string',
                'estado' => 'boolean',

                'file1' => 'nullable|image|max:2048',
                'file2' => 'nullable|image|max:2048',
                'file3' => 'nullable|image|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            DB::beginTransaction();

            // crear el producto sin las rutas de imagen para obtener el ID
            $productoData = $request->except(['file_main', 'file_background', 'file1', 'file2', 'file3']);

            // Inicializar las rutas en null antes de crear el registro
            $productoData['path_main'] = $productoData['path_background'] = null;
            $productoData['path1'] = $productoData['path2'] = $productoData['path3'] = null;

            $producto = Productos::create($productoData);
            $productoId = $producto->id_producto;

            if ($request->hasFile('file_main')) {
                $file = $request->file('file_main');
                $image = Image::read($file);
                $relativePath = "productos/{$productoId}/imagenes";
                $finalFilename = "{$relativePath}/main.webp"; // Nombre fijo y sin colisión

                Storage::disk('public')->put($finalFilename, (string) $image->toWebp());
                $producto->path_main = $finalFilename;
            }

            if ($request->hasFile('file_background')) {
                $file = $request->file('file_background');
                $image = Image::read($file);
                $relativePath = "productos/{$productoId}/imagenes";
                $finalFilename = "{$relativePath}/background.webp"; // Nombre fijo y sin colisión

                Storage::disk('public')->put($finalFilename, (string) $image->toWebp());
                $producto->path_background = $finalFilename;
            }

            // Iteramos sobre los tres posibles paths
            for ($pathIndex = 1; $pathIndex <= 3; $pathIndex++) {
                $fileKey = 'file' . $pathIndex; // file1, file2, file3
                $pathKey = 'path' . $pathIndex; // path1, path2, path3

                // Solo procesamos si se ha subido un archivo con la clave específica
                if ($request->hasFile($fileKey)) {
                    $file = $request->file($fileKey);
                    $image = Image::read($file);

                    $relativePath = "productos/{$productoId}/imagenes";
                    // Nombre fijo: imagen-1.webp, imagen-2.webp, imagen-3.webp
                    $finalFilename = "{$relativePath}/imagen-{$pathIndex}.webp";

                    Storage::disk('public')->put($finalFilename, (string) $image->toWebp());
                    $producto->$pathKey = Storage::url($finalFilename); // Guardar la URL pública
                }
            }

            $producto->save();

            DB::commit();

            return response()->json([
                'status' => 201,
                'message' => 'Producto creado exitosamente',
                'data' => $producto->load('empleado')
            ], 201);

        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                'status' => 500,
                'message' => 'Error al crear el producto',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:250',
                'descripcion' => 'required|string',

                'id_empleado' => 'nullable|integer|exists:empleados,id_empleado',

                'file_main' => 'nullable|image|max:2048',
                'file_background' => 'nullable|image|max:2048',

                'path1' => 'nullable|string|max:255',
                'tituloimg1' => 'nullable|string|max:255',
                'descripcionimg1' => 'nullable|string',

                'path2' => 'nullable|string|max:255',
                'tituloimg2' => 'nullable|string|max:255',
                'descripcionimg2' => 'nullable|string',

                'path3' => 'nullable|string|max:255',
                'tituloimg3' => 'nullable|string|max:255',
                'descripcionimg3' => 'nullable|string',

                'caracteristicas_descrip' => 'nullable|string',
                'ventajas_descrip' => 'nullable|string',
                'consumoenergetico_descrip' => 'nullable|string',
                'iluminacion_descrip' => 'nullable|string',
                'durabilidad_descrip' => 'nullable|string',
                'estado' => 'boolean',

                'file1' => 'nullable|file|max:2048|mimes:jpeg,jpg,png,gif,webp,avif',
                'file2' => 'nullable|file|max:2048|mimes:jpeg,jpg,png,gif,webp,avif',
                'file3' => 'nullable|file|max:2048|mimes:jpeg,jpg,png,gif,webp,avif',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $producto = Productos::findOrFail($id);

            DB::beginTransaction();

            $productoData = $request->except(['file_main', 'file_background', 'files', 'file_indices', 'file1', 'file2', 'file3']);
            $producto->update($productoData);

            $productId = $producto->id_producto;

            if ($request->hasFile('file_main')) {
                $pathKey = 'path_main';

                // Eliminar la imagen anterior
                if (!empty($producto->$pathKey)) {
                    $storagePath = str_replace(Storage::url('/'), '', $producto->$pathKey);
                    if (Storage::disk('public')->exists($storagePath)) {
                        Storage::disk('public')->delete($storagePath);
                    }
                }

                // Guardar nueva imagen
                $file = $request->file('file_main');
                $image = Image::read($file);
                $relativePath = "productos/{$productId}/imagenes";
                $finalFilename = "{$relativePath}/main.webp";

                Storage::disk('public')->put($finalFilename, (string) $image->toWebp());
                $producto->$pathKey = Storage::url($finalFilename); // Guardar la URL pública
            }

            if ($request->hasFile('file_background')) {
                $pathKey = 'path_background';

                // Eliminar la imagen anterior
                if (!empty($producto->$pathKey)) {
                    $storagePath = str_replace(Storage::url('/'), '', $producto->$pathKey);
                    if (Storage::disk('public')->exists($storagePath)) {
                        Storage::disk('public')->delete($storagePath);
                    }
                }

                // Guardar nueva imagen
                $file = $request->file('file_background');
                $image = Image::read($file);
                $relativePath = "productos/{$productId}/imagenes";
                $finalFilename = "{$relativePath}/background.webp";

                Storage::disk('public')->put($finalFilename, (string) $image->toWebp());
                $producto->$pathKey = Storage::url($finalFilename); // Guardar la URL pública
            }

            // Iteramos sobre los tres posibles paths
            for ($pathIndex = 1; $pathIndex <= 3; $pathIndex++) {
                $fileKey = 'file' . $pathIndex; // file1, file2, file3
                $pathKey = 'path' . $pathIndex; // path1, path2, path3

                // Solo procesamos si se ha subido un archivo con la clave específica
                if ($request->hasFile($fileKey)) {
                    $file = $request->file($fileKey);

                    // eliminar la imagen secundaria anterior (si existe)
                    if (!empty($producto->$pathKey)) {
                        $storagePath = str_replace(Storage::url('/'), '', $producto->$pathKey);
                        // Solo eliminamos si es un archivo almacenado localmente (no una URL externa)
                        if (Storage::disk('public')->exists($storagePath) && !Str::contains($producto->$pathKey, 'http')) {
                            Storage::disk('public')->delete($storagePath);
                        }
                    }

                    $image = Image::read($file);

                    $relativePath = "productos/{$id}/imagenes";
                    // Nombre fijo: imagen-1.webp, imagen-2.webp, imagen-3.webp
                    $finalFilename = "{$relativePath}/imagen-{$pathIndex}.webp";

                    Storage::disk('public')->put($finalFilename, (string) $image->toWebp());
                    $publicPath = Storage::url($finalFilename);

                    $producto->$pathKey = $publicPath;
                }
            }

            $producto->save();

            DB::commit();

            $images = [];

            for ($i = 1; $i <= 3; $i++) {
                $pathKey = 'path' . $i;
                $titleKey = 'tituloimg' . $i;
                $descKey = 'descripcionimg' . $i;

                if ($producto->$pathKey) {
                    $images[] = [
                        'type' => 'secondary',
                        'url' => $producto->$pathKey,
                        'titulo' => $producto->$titleKey,
                        'descripcion' => $producto->$descKey,
                    ];
                }
                unset($producto->$pathKey, $producto->$titleKey, $producto->$descKey);
            }

            if ($producto->path_main) {
                $images[] = [
                    'url' => $producto->path_main,
                    // 'titulo' => null,
                    // 'descripcion' => null,
                    'type' => 'main'
                ];
                unset($producto->path_main);
            }
            if ($producto->path_background) {
                $images[] = [
                    'url' => $producto->path_background,
                    // 'titulo' => null,
                    // 'descripcion' => null,
                    'type' => 'background'
                ];
                unset($producto->path_background);
            }


            $producto->images = $images;

            return response()->json([
                'status' => 200,
                'message' => 'Producto actualizado exitosamente',
                'data' => $producto->load('empleado')
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollback();
            return response()->json([
                'status' => 404,
                'message' => 'Producto no encontrado'
            ], 404);
        } catch (\Exception $ex) {
            DB::rollback();
            if (Str::contains($ex->getMessage(), 'foreign key constraint') || Str::contains($ex->getMessage(), '1452')) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Error de clave foránea. El id_empleado proporcionado no existe.',
                    'error' => $ex->getMessage()
                ], 400);
            }
            return response()->json([
                'status' => 500,
                'message' => 'Error al actualizar el producto',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $producto = Productos::findOrFail($id);

            DB::beginTransaction();

            $storagePath = "productos/{$producto->id_producto}";

            if (Storage::disk('public')->exists($storagePath)) {
                Storage::disk('public')->deleteDirectory($storagePath);
            }

            $producto->delete();

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Producto y sus archivos eliminados exitosamente'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'message' => 'Producto no encontrado'
            ], 404);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                'status' => 500,
                'message' => 'Error al eliminar el producto',
                'error' => $ex->getMessage()
            ], 500);
        }
    }
}
