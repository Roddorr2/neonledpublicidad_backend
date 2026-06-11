<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Producto\StoreProductoRequest;
use App\Http\Requests\Producto\UpdateProductoRequest;
use App\Models\Productos;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ProductosController extends Controller
{
    public function get()
    {
        try {
            $productosPaginados = Productos::with('empleado')->orderBy('id_producto', 'asc')->paginate(20);
            $productosMapeados  = $productosPaginados->getCollection()->map(function ($producto) {
                $images = [];

                if ($producto->path_main) {
                    $images[] = ['type' => 'main', 'url' => $producto->path_main];
                }
                if ($producto->path_background) {
                    $images[] = ['type' => 'background', 'url' => $producto->path_background];
                }

                for ($i = 1; $i <= 3; $i++) {
                    $pathKey  = 'path' . $i;
                    $titleKey = 'tituloimg' . $i;
                    $descKey  = 'descripcionimg' . $i;

                    if ($producto->$pathKey) {
                        $images[] = [
                            'type'        => 'secondary',
                            'url'         => $producto->$pathKey,
                            'titulo'      => $producto->$titleKey,
                            'descripcion' => $producto->$descKey,
                        ];
                    }
                }

                $producto->images = $images;

                unset($producto->path1, $producto->tituloimg1, $producto->descripcionimg1);
                unset($producto->path2, $producto->tituloimg2, $producto->descripcionimg2);
                unset($producto->path3, $producto->tituloimg3, $producto->descripcionimg3);
                unset($producto->path_main, $producto->path_background);

                return $producto;
            });

            $productosPaginados->setCollection($productosMapeados);

            return response()->json(['status' => 200, 'data' => $productosPaginados], 200);
        } catch (\Exception $ex) {
            return response()->json(['status' => 500, 'message' => 'Error al obtener los productos', 'error' => $ex->getMessage()], 500);
        }
    }

    public function getById(int $id)
    {
        try {
            $producto = Productos::with(['empleado' => function ($query) {
                $query->select('id_empleado');
            }])->findOrFail($id);

            $images = [];

            if ($producto->path_main) {
                $images[] = ['type' => 'main', 'url' => $producto->path_main];
            }
            if ($producto->path_background) {
                $images[] = ['type' => 'background', 'url' => $producto->path_background];
            }

            for ($i = 1; $i <= 3; $i++) {
                $pathKey  = 'path' . $i;
                $titleKey = 'tituloimg' . $i;
                $descKey  = 'descripcionimg' . $i;

                if ($producto->$pathKey) {
                    $images[] = ['type' => 'secondary', 'url' => $producto->$pathKey, 'titulo' => $producto->$titleKey, 'descripcion' => $producto->$descKey];
                }

                unset($producto->$pathKey, $producto->$titleKey, $producto->$descKey);
            }

            unset($producto->path_main, $producto->path_background);
            $producto->images = $images;

            return response()->json(['status' => 200, 'data' => $producto], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 404, 'message' => 'Producto no encontrado'], 404);
        } catch (\Exception $ex) {
            return response()->json(['status' => 500, 'message' => 'Error interno del servidor', 'error' => $ex->getMessage()], 500);
        }
    }

    public function getCompact()
    {
        try {
            $productosPaginados = Productos::select('id_producto', 'id_empleado', 'nombre', 'descripcion', 'path_main', 'estado')->orderBy('id_producto', 'asc')->paginate(7);
            $productosMapeados  = $productosPaginados->getCollection()->map(function ($producto) {
                if ($producto->path_main && ! Str::contains($producto->path_main, 'http')) {
                    $producto->path_main = Storage::url($producto->path_main);
                }

                return $producto;
            });

            $productosPaginados->setCollection($productosMapeados);

            return response()->json(['status' => 200, 'data' => $productosPaginados], 200);
        } catch (\Exception $ex) {
            return response()->json(['status' => 500, 'message' => 'Error al obtener los productos de forma compacta', 'error' => $ex->getMessage()], 500);
        }
    }

    public function create(StoreProductoRequest $request)
    {
        try {
            DB::beginTransaction();

            $productoData              = $request->except(['file_main', 'file_background', 'file1', 'file2', 'file3']);
            $productoData['path_main'] = $productoData['path_background'] = null;
            $productoData['path1']     = $productoData['path2'] = $productoData['path3'] = null;

            $producto   = Productos::create($productoData);
            $productoId = $producto->id_producto;

            if ($request->hasFile('file_main')) {
                $image         = Image::read($request->file('file_main'));
                $relativePath  = "productos/{$productoId}/imagenes";
                $finalFilename = "{$relativePath}/main.webp";
                Storage::disk('public')->put($finalFilename, (string)$image->toWebp());
                $producto->path_main = $finalFilename;
            }

            if ($request->hasFile('file_background')) {
                $image         = Image::read($request->file('file_background'));
                $relativePath  = "productos/{$productoId}/imagenes";
                $finalFilename = "{$relativePath}/background.webp";
                Storage::disk('public')->put($finalFilename, (string)$image->toWebp());
                $producto->path_background = $finalFilename;
            }

            for ($pathIndex = 1; $pathIndex <= 3; $pathIndex++) {
                $fileKey = 'file' . $pathIndex;
                $pathKey = 'path' . $pathIndex;

                if ($request->hasFile($fileKey)) {
                    $image         = Image::read($request->file($fileKey));
                    $relativePath  = "productos/{$productoId}/imagenes";
                    $finalFilename = "{$relativePath}/imagen-{$pathIndex}.webp";
                    Storage::disk('public')->put($finalFilename, (string)$image->toWebp());
                    $producto->$pathKey = Storage::url($finalFilename);
                }
            }

            $producto->save();

            DB::commit();

            return response()->json([
                'status'  => 201,
                'message' => 'Producto creado exitosamente',
                'data'    => $producto->load('empleado'),
            ], 201);

        } catch (\Exception $ex) {
            DB::rollback();

            return response()->json(['status' => 500, 'message' => 'Error al crear el producto', 'error' => $ex->getMessage()], 500);
        }
    }

    public function update(UpdateProductoRequest $request, int $id)
    {
        try {
            $producto = Productos::findOrFail($id);

            DB::beginTransaction();

            $productoData = $request->except(['file_main', 'file_background', 'files', 'file_indices', 'file1', 'file2', 'file3']);
            $producto->update($productoData);

            $productId = $producto->id_producto;

            if ($request->hasFile('file_main')) {
                $pathKey = 'path_main';
                if (! empty($producto->$pathKey)) {
                    $storagePath = str_replace(Storage::url('/'), '', $producto->$pathKey);
                    if (Storage::disk('public')->exists($storagePath)) {
                        Storage::disk('public')->delete($storagePath);
                    }
                }
                $image         = Image::read($request->file('file_main'));
                $finalFilename = "productos/{$productId}/imagenes/main.webp";
                Storage::disk('public')->put($finalFilename, (string)$image->toWebp());
                $producto->$pathKey = Storage::url($finalFilename);
            }

            if ($request->hasFile('file_background')) {
                $pathKey = 'path_background';
                if (! empty($producto->$pathKey)) {
                    $storagePath = str_replace(Storage::url('/'), '', $producto->$pathKey);
                    if (Storage::disk('public')->exists($storagePath)) {
                        Storage::disk('public')->delete($storagePath);
                    }
                }
                $image         = Image::read($request->file('file_background'));
                $finalFilename = "productos/{$productId}/imagenes/background.webp";
                Storage::disk('public')->put($finalFilename, (string)$image->toWebp());
                $producto->$pathKey = Storage::url($finalFilename);
            }

            for ($pathIndex = 1; $pathIndex <= 3; $pathIndex++) {
                $fileKey = 'file' . $pathIndex;
                $pathKey = 'path' . $pathIndex;

                if ($request->hasFile($fileKey)) {
                    if (! empty($producto->$pathKey)) {
                        $storagePath = str_replace(Storage::url('/'), '', $producto->$pathKey);
                        if (Storage::disk('public')->exists($storagePath) && ! Str::contains($producto->$pathKey, 'http')) {
                            Storage::disk('public')->delete($storagePath);
                        }
                    }

                    $image         = Image::read($request->file($fileKey));
                    $finalFilename = "productos/{$id}/imagenes/imagen-{$pathIndex}.webp";
                    Storage::disk('public')->put($finalFilename, (string)$image->toWebp());
                    $producto->$pathKey = Storage::url($finalFilename);
                }
            }

            $producto->save();
            DB::commit();

            $images = [];
            for ($i = 1; $i <= 3; $i++) {
                $pathKey  = 'path' . $i;
                $titleKey = 'tituloimg' . $i;
                $descKey  = 'descripcionimg' . $i;

                if ($producto->$pathKey) {
                    $images[] = ['type' => 'secondary', 'url' => $producto->$pathKey, 'titulo' => $producto->$titleKey, 'descripcion' => $producto->$descKey];
                }
                unset($producto->$pathKey, $producto->$titleKey, $producto->$descKey);
            }

            if ($producto->path_main) {
                $images[] = ['url' => $producto->path_main, 'type' => 'main'];
                unset($producto->path_main);
            }
            if ($producto->path_background) {
                $images[] = ['url' => $producto->path_background, 'type' => 'background'];
                unset($producto->path_background);
            }

            $producto->images = $images;

            return response()->json([
                'status'  => 200,
                'message' => 'Producto actualizado exitosamente',
                'data'    => $producto->load('empleado'),
            ], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollback();

            return response()->json(['status' => 404, 'message' => 'Producto no encontrado'], 404);
        } catch (\Exception $ex) {
            DB::rollback();
            if (Str::contains($ex->getMessage(), 'foreign key constraint') || Str::contains($ex->getMessage(), '1452')) {
                return response()->json(['status' => 400, 'message' => 'Error de clave foránea. El id_empleado proporcionado no existe.', 'error' => $ex->getMessage()], 400);
            }

            return response()->json(['status' => 500, 'message' => 'Error al actualizar el producto', 'error' => $ex->getMessage()], 500);
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

            return response()->json(['status' => 200, 'message' => 'Producto y sus archivos eliminados exitosamente'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 404, 'message' => 'Producto no encontrado'], 404);
        } catch (\Exception $ex) {
            DB::rollback();

            return response()->json(['status' => 500, 'message' => 'Error al eliminar el producto', 'error' => $ex->getMessage()], 500);
        }
    }
}
