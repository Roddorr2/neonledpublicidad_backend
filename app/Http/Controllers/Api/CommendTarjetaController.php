<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommendTarjeta;
use App\Http\Requests\StoreCommendTarjetaRequest;
use App\Http\Requests\UpdateCommendTarjetaRequest;
use Illuminate\Support\Facades\DB;

class CommendTarjetaController extends Controller
{
   public function create(StoreCommendTarjetaRequest $request)
    {
        try{
           

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

    public function update(UpdateCommendTarjetaRequest $request, int $id){
        try{

           

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
