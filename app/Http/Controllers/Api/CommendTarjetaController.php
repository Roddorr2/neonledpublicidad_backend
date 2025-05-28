<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommendTarjeta;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


/**
 * @OA\Tag(
 *     name="CommendTarjeta",
 *     description="Operaciones relacionadas con CommendTarjeta"
 * )
 */
class CommendTarjetaController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/commendtarjeta",
     *     summary="Crear una nueva CommendTarjeta",
     *     tags={"CommendTarjeta"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="titulo", type="string", maxLength=255, nullable=true),
     *             @OA\Property(property="texto1", type="string", maxLength=255, nullable=true),
     *             @OA\Property(property="texto2", type="string", maxLength=255, nullable=true),
     *             @OA\Property(property="texto3", type="string", maxLength=255, nullable=true),
     *             @OA\Property(property="texto4", type="string", maxLength=255, nullable=true),
     *             @OA\Property(property="texto5", type="string", maxLength=255, nullable=true),
     *         )
     *     ),
     *     @OA\Response(response=200, description="CommendTarjeta creada correctamente",
     *       @OA\JsonContent(
     *         @OA\Property(property="status", type="integer", example=200),
     *         @OA\Property(property="message", type="string", example="CommendTarjeta creada correctamente"),
     *         @OA\Property(property="id", type="integer", example=1)
     *       )
     *     ),
     *     @OA\Response(response=400, description="Errores de validación")
     * )
     */
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

     /**
     * @OA\Put(
     *     path="/api/commendtarjeta/{id}",
     *     summary="Actualizar una CommendTarjeta",
     *     tags={"CommendTarjeta"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la CommendTarjeta",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo", "texto1", "texto2", "texto3"},
     *             @OA\Property(property="titulo", type="string", maxLength=255),
     *             @OA\Property(property="texto1", type="string", maxLength=255),
     *             @OA\Property(property="texto2", type="string", maxLength=255),
     *             @OA\Property(property="texto3", type="string", maxLength=255),
     *             @OA\Property(property="texto4", type="string", maxLength=255, nullable=true),
     *             @OA\Property(property="texto5", type="string", maxLength=255, nullable=true),
     *         )
     *     ),
     *     @OA\Response(response=200, description="CommendTarjeta actualizada",
     *       @OA\JsonContent(
     *         @OA\Property(property="status", type="integer", example=200),
     *         @OA\Property(property="message", type="string", example="Tarjeta actualizada"),
     *         @OA\Property(property="id", type="integer", example=1)
     *       )
     *     ),
     *     @OA\Response(response=400, description="Errores de validación"),
     *     @OA\Response(response=404, description="CommendTarjeta no encontrada")
     * )
     */
    public function update(Request $request,int $id){
        try{

            $validator = Validator::make($request->all(), [
                'titulo' => 'required|string|max:255',
                'texto1' => 'required|string|max:255',
                'texto2' => 'required|string|max:255',
                'texto3' => 'required|string|max:255',
                'texto4' => 'nullable|string|max:255',
                'texto5' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $tarjeta = CommendTarjeta::find($id);

            if(! $tarjeta){
                return response()->json(
                    [
                        'status'=> 404,
                        'message'=> 'Tarjeta no encontrada'
                    ],200
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
                    'status'=> 400,
                    'message'=> 'Error interno del servidor',
                    'error' => $e->getMessage()
                ],200
            );
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/commendtarjeta/{id}",
     *     summary="Eliminar una CommendTarjeta",
     *     tags={"CommendTarjeta"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la CommendTarjeta",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="CommendTarjeta eliminada correctamente",
     *       @OA\JsonContent(
     *         @OA\Property(property="status", type="integer", example=200),
     *         @OA\Property(property="message", type="string", example="CommendTarjeta eliminada correctamente")
     *       )
     *     ),
     *     @OA\Response(response=404, description="CommendTarjeta no encontrada"),
     *     @OA\Response(response=500, description="Error del servidor")
     * )
     */
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
