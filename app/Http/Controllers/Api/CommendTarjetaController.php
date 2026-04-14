<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommendTarjeta;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CommendTarjetaController extends Controller
{
    public function create(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'titulo' => 'nullable|string|max:255',
                'texto1' => 'nullable|string|max:255',
                'texto2' => 'nullable|string|max:255',
                'texto3' => 'nullable|string|max:255',
                'texto4' => 'nullable|string|max:255',
                'texto5' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            DB::beginTransaction();

            $commendTarjeta = CommendTarjeta::create($request->all());

            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "CommendTarjeta creada correctamente",
                "id" => $commendTarjeta->id_commend_tarjeta
            ],200);

        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request,int $id){
        try{

            $validator = Validator::make($request->all(), [
                'titulo' => 'nullable|string|max:255',
                'texto1' => 'nullable|string|max:255',
                'texto2' => 'nullable|string|max:255',
                'texto3' => 'nullable|string|max:255',
                'texto4' => 'nullable|string|max:255',
                'texto5' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $tarjeta = CommendTarjeta::find($id);

            if(!$tarjeta){
                return response()->json(
                    [
                        'status'=> 404,
                        'message'=> 'Tarjeta no encontrada'
                    ],404
                );
            }

            DB::beginTransaction();

            $tarjeta->update($request->all());

            DB::commit();

            return response()->json([
                'status'=> 200,
                'message'=> 'Tarjeta actualizada',
                'id'=> $tarjeta->id_commend_tarjeta
            ],200);

        }catch(\Exception $e){
            DB::rollback();
            return response()->json(
                [
                    'status'=> 500,
                    'message'=> 'Error interno del servidor',
                    'error' => $e->getMessage()
                ],500
            );
        }
    }

    public function destroy($id)
    {
        try{

            $commendTarjeta = CommendTarjeta::find($id);

            if (!$commendTarjeta) {
                return response()->json([
                    "status" => 404,
                    "message" => "CommendTarjeta no encontrada"
                ],404);
            }
            $commendTarjeta->delete();
            return response()->json([
                "status" => 200,
                "message" => "CommendTarjeta eliminada correctamente"
                ], 200);

        }catch(\Exception $ex){
            return response()->json([
                "status" => 500,
                "message" => "Error al eliminar el CommendTarjeta",
                "error" => $ex->getMessage()
                ], 500);
        }
    }
}
