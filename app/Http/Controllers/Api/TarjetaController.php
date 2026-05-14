<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tarjeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TarjetaController extends Controller
{
    public function index()
    {
        try {
            $tarjetas = Tarjeta::all();

            return response()->json($tarjetas, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function showAll(int $id)
    {
        try {

            $tarjetas = Tarjeta::where('id_tarjeta', $id)->all();

            if (! $tarjetas) {
                return response()->json(['error' => 'No se encontraron tarjetas'], 404);
            }

            return response()->json($tarjetas, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'titulo'       => 'required|string|max:70',
                'descripcion'  => 'required|string',
                'keyword'      => 'nullable|string',
                'link'         => 'nullable|string',
                'id_blog_body' => 'required|integer|exists:blog_bodies,id_blog_body',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            DB::beginTransaction();

            $tarjeta = Tarjeta::create($request->all());

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Tarjeta creada correctamente',
                'id'      => $tarjeta->id_tarjeta,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'titulo'       => 'required|string|max:70',
                'descripcion'  => 'required|string',
                'keyword'      => 'nullable|string',
                'link'         => 'nullable|string',
                'id_blog_body' => 'required|integer|exists:blog_bodies,id_blog_body',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $tarjeta = Tarjeta::find($id);

            if (! $tarjeta) {
                return response()->json([
                    'status'  => 400,
                    'message' => 'Tarjeta no encontrada',
                ], 404);
            }

            DB::beginTransaction();

            $tarjeta->update($request->all());

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Tarjeta creada correctamente',
                'id'      => $tarjeta->id_tarjeta,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['error' => $e->getMessage()], 500);
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
            return response()->json(['error' => $e->getMessage()], 500);
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
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
