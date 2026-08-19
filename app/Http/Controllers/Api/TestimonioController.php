<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Testimonio\StoreTestimonioRequest;
use App\Http\Requests\Testimonio\UpdateTestimonioRequest;
use App\Models\Testimonio;
use App\Services\FileUploadService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestimonioController extends Controller
{
    /**
     * Listado completo para el dashboard (incluye inactivos), ordenado
     * por el campo "orden".
     */
    public function index()
    {
        try {
            $testimonios = Testimonio::orderBy('orden', 'asc')->orderBy('id', 'asc')->get();

            return response()->json(['status' => 200, 'data' => $testimonios], 200);
        } catch (\Exception $ex) {
            return response()->json(['status' => 500, 'message' => 'Error al obtener los testimonios', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Listado público para el carrusel de /nosotros: solo activos, ordenados.
     */
    public function publico()
    {
        try {
            $testimonios = Testimonio::where('activo', true)
                ->orderBy('orden', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            return response()->json(['status' => 200, 'data' => $testimonios], 200);
        } catch (\Exception $ex) {
            return response()->json(['status' => 500, 'message' => 'Error al obtener los testimonios', 'error' => $ex->getMessage()], 500);
        }
    }

    public function store(StoreTestimonioRequest $request, FileUploadService $fileUploadService)
    {
        try {
            $datos = $request->validated();

            if ($request->hasFile('avatar')) {
                $subida = $fileUploadService->subir($request->file('avatar'), 'testimonios');
                $datos['avatar_url']       = $subida['url'];
                $datos['avatar_public_id'] = $subida['public_id'];
            }

            // Si no mandan "fecha", se usa la fecha de hoy
            if (! isset($datos['fecha'])) {
                $datos['fecha'] = now()->toDateString();
            }

            // Si no mandan "orden", va al final de la lista
            if (! isset($datos['orden'])) {
                $datos['orden'] = (int) (Testimonio::max('orden') ?? 0) + 1;
            }

            $testimonio = Testimonio::create($datos);

            return response()->json(['status' => 201, 'message' => 'Testimonio creado correctamente', 'data' => $testimonio], 201);
        } catch (\Exception $ex) {
            return response()->json(['status' => 500, 'message' => 'Error al crear el testimonio', 'error' => $ex->getMessage()], 500);
        }
    }

    public function update(UpdateTestimonioRequest $request, int $id, FileUploadService $fileUploadService)
    {
        try {
            $testimonio = Testimonio::findOrFail($id);
            $datos      = $request->validated();

            if ($request->hasFile('avatar')) {
                $subida = $fileUploadService->subir(
                    $request->file('avatar'),
                    'testimonios',
                    $testimonio->avatar_public_id,
                    $testimonio->avatar_url
                );
                $datos['avatar_url']       = $subida['url'];
                $datos['avatar_public_id'] = $subida['public_id'];
            }

            $testimonio->update($datos);

            return response()->json(['status' => 200, 'message' => 'Testimonio actualizado correctamente', 'data' => $testimonio], 200);
        } catch (ModelNotFoundException $ex) {
            return response()->json(['status' => 404, 'message' => 'Testimonio no encontrado'], 404);
        } catch (\Exception $ex) {
            return response()->json(['status' => 500, 'message' => 'Error al actualizar el testimonio', 'error' => $ex->getMessage()], 500);
        }
    }

    public function destroy(int $id, FileUploadService $fileUploadService)
    {
        try {
            $testimonio = Testimonio::findOrFail($id);

            if ($testimonio->avatar_public_id) {
                $fileUploadService->eliminarPublicId($testimonio->avatar_public_id);
            }

            $testimonio->delete();

            return response()->json(['status' => 200, 'message' => 'Testimonio eliminado correctamente'], 200);
        } catch (ModelNotFoundException $ex) {
            return response()->json(['status' => 404, 'message' => 'Testimonio no encontrado'], 404);
        } catch (\Exception $ex) {
            return response()->json(['status' => 500, 'message' => 'Error al eliminar el testimonio', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Reordena varios testimonios de una sola vez.
     * Body esperado: { "orden": [{ "id": 3, "orden": 0 }, { "id": 1, "orden": 1 }, ...] }
     */
    public function reordenar(Request $request)
    {
        $request->validate([
            'orden'             => 'required|array|min:1',
            'orden.*.id'        => 'required|integer|exists:testimonios,id',
            'orden.*.orden'     => 'required|integer|min:0',
        ]);

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->input('orden') as $item) {
                    Testimonio::where('id', $item['id'])->update(['orden' => $item['orden']]);
                }
            });

            $testimonios = Testimonio::orderBy('orden', 'asc')->orderBy('id', 'asc')->get();

            return response()->json(['status' => 200, 'message' => 'Orden actualizado correctamente', 'data' => $testimonios], 200);
        } catch (\Exception $ex) {
            return response()->json(['status' => 500, 'message' => 'Error al reordenar los testimonios', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Activa/desactiva un testimonio sin borrarlo.
     */
    public function toggleActivo(int $id)
    {
        try {
            $testimonio = Testimonio::findOrFail($id);
            $testimonio->update(['activo' => ! $testimonio->activo]);

            return response()->json(['status' => 200, 'message' => 'Estado actualizado correctamente', 'data' => $testimonio], 200);
        } catch (ModelNotFoundException $ex) {
            return response()->json(['status' => 404, 'message' => 'Testimonio no encontrado'], 404);
        } catch (\Exception $ex) {
            return response()->json(['status' => 500, 'message' => 'Error al actualizar el estado', 'error' => $ex->getMessage()], 500);
        }
    }
}