<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tarjeta\StoreTarjetaRequest;
use App\Http\Requests\Tarjeta\UpdateTarjetaRequest;
use App\Models\Tarjeta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TarjetaController extends Controller
{
    public function index()
    {
        try {
            $tarjetas = Tarjeta::all();

            return response()->json($tarjetas, 200);
        } catch (\Exception $e) {
            Log::error('Error al listar tarjetas: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status'  => 500,
                'message' => 'Error al consultar tarjetas',
                'error'   => 'Error interno del servidor',
            ], 500);
        }
    }

    public function showAll(int $id)
    {
        try {
            $tarjetas = Tarjeta::where('id_blog_body', $id)->get();

            if ($tarjetas->isEmpty()) {
                return response()->json(['error' => 'No se encontraron tarjetas'], 404);
            }

            return response()->json($tarjetas, 200);
        } catch (\Exception $e) {
            Log::error('Error al consultar tarjetas de blog_body: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status'  => 500,
                'message' => 'Error interno del servidor',
                'error'   => 'Error interno del servidor',
            ], 500);
        }
    }

    public function create(StoreTarjetaRequest $request)
    {
        try {
            DB::beginTransaction();

            $tarjeta = Tarjeta::create($request->validated());

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Tarjeta creada correctamente',
                'id'      => $tarjeta->id_tarjeta,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error al crear tarjeta: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status'  => 500,
                'message' => 'Error al crear la tarjeta',
                'error'   => 'Error interno del servidor',
            ], 500);
        }
    }

    public function update(UpdateTarjetaRequest $request, int $id)
    {
        try {
            $tarjeta = Tarjeta::find($id);

            if (! $tarjeta) {
                return response()->json([
                    'status'  => 400,
                    'message' => 'Tarjeta no encontrada',
                ], 404);
            }

            DB::beginTransaction();

            $tarjeta->update($request->validated());

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Tarjeta actualizada correctamente',
                'id'      => $tarjeta->id_tarjeta,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error al actualizar tarjeta: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status'  => 500,
                'message' => 'Error al actualizar la tarjeta',
                'error'   => 'Error interno del servidor',
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $tarjeta = Tarjeta::find($id);

            if (! $tarjeta) {
                return response()->json(['error' => 'Tarjeta no encontrada'], 404);
            }

            $tarjeta->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Tarjeta eliminada correctamente',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar tarjeta: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status'  => 500,
                'message' => 'Error al eliminar la tarjeta',
                'error'   => 'Error interno del servidor',
            ], 500);
        }
    }

    public function destroyAll(int $id)
    {
        try {
            $deletedRows = Tarjeta::where('id_blog_body', $id)->delete();

            if ($deletedRows === 0) {
                return response()->json(['error' => 'No se encontraron tarjetas'], 404);
            }

            return response()->json([
                'status'  => 200,
                'message' => 'Tarjetas eliminadas correctamente',
                'deleted' => $deletedRows,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar tarjetas por blog_body: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status'  => 500,
                'message' => 'Error al eliminar las tarjetas',
                'error'   => 'Error interno del servidor',
            ], 500);
        }
    }
}