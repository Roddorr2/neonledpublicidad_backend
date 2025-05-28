<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tarjeta;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
/**
 * @OA\Tag(
 *     name="Tarjetas",
 *     description="Operaciones relacionadas con tarjetas enlazadas a un BlogBody"
 * )
 */

class TarjetaController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/tarjeta/{id}",
 *     summary="Obtener tarjeta por ID",
 *     tags={"Tarjetas"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID de la tarjeta",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Tarjeta encontrada",
 *         @OA\JsonContent(type="object")
 *     ),
 *     @OA\Response(response=404, description="Tarjeta no encontrada"),
 *     @OA\Response(response=500, description="Error del servidor")
 * )
 */

    public function showAll(int $id)
    {
        try{

            $tarjetas = Tarjeta::where('id_tarjeta', $id)->all();

            if (!$tarjetas) {
                return response()->json(['error' => 'No se encontraron tarjetas'], 404);
            }

            return response()->json($tarjetas, 200);

        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    

     /**
     * @OA\Post(
     *     path="/api/tarjeta",
     *     summary="Crear nueva tarjeta",
     *     tags={"Tarjetas"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo", "descripcion", "id_blog_body"},
     *             @OA\Property(property="titulo", type="string", maxLength=70),
     *             @OA\Property(property="descripcion", type="string"),
     *             @OA\Property(property="id_blog_body", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Tarjeta creada correctamente"),
     *     @OA\Response(response=400, description="Datos inválidos"),
     *     @OA\Response(response=500, description="Error del servidor")
     * )
     */
    public function create(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'titulo' => 'required|string|max:70',
                'descripcion' => 'required|string',
                'id_blog_body' => 'required|integer|exists:blog_bodies,id_blog_body',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            DB::beginTransaction();

            $tarjeta = Tarjeta::create($request->all());

            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "Tarjeta creada correctamente",
                "id" => $tarjeta->id_tarjeta
            ],200);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
        
    /**
     * @OA\Put(
     *     path="/api/tarjeta/{id}",
     *     summary="Actualizar tarjeta existente",
     *     tags={"Tarjetas"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la tarjeta a actualizar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo", "descripcion", "id_blog_body"},
     *             @OA\Property(property="titulo", type="string", maxLength=70),
     *             @OA\Property(property="descripcion", type="string"),
     *             @OA\Property(property="id_blog_body", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Tarjeta actualizada correctamente"),
     *     @OA\Response(response=400, description="Datos inválidos o tarjeta no encontrada"),
     *     @OA\Response(response=500, description="Error del servidor")
     * )
     */
    public function update(Request $request, int $id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'titulo' => 'required|string|max:70',
                'descripcion' => 'required|string',
                'id_blog_body' => 'required|integer|exists:blog_bodies,id_blog_body',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $tarjeta = Tarjeta::find($id);

            if(!$tarjeta){
                return response()->json([
                    'status'=> 400,
                    'message'=> 'Tarjeta no encontrada'
                ],404);
            }

            DB::beginTransaction();

            $tarjeta->update($request->all());

            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "Tarjeta creada correctamente",
                "id" => $tarjeta->id_tarjeta
            ],200);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

        /**
     * @OA\Delete(
     *     path="/api/tarjeta/{id}",
     *     summary="Eliminar una tarjeta",
     *     tags={"Tarjetas"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la tarjeta a eliminar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Tarjeta eliminada correctamente"),
     *     @OA\Response(response=404, description="Tarjeta no encontrada"),
     *     @OA\Response(response=500, description="Error del servidor")
     * )
     */
    public function destroy(int $id)
    {
        try{

            $tarjeta = Tarjeta::find($id);

            if (!$tarjeta) {
                return response()->json(['error' => 'Tarjeta no encontrada'], 404);
            }
            $tarjeta->delete();

            return response()->json([
                "status" => 200,
                "message" => "Tarjeta eliminada correctamente"
                ], 200);
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/tarjeta/borrar-todas/{id}",
     *     summary="Eliminar todas las tarjetas por ID de blog body",
     *     tags={"Tarjetas"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del blog body asociado",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Tarjetas eliminadas correctamente"),
     *     @OA\Response(response=404, description="No se encontraron tarjetas"),
     *     @OA\Response(response=500, description="Error del servidor")
     * )
     */
    public function destroyAll(int $id)
    {
        try{

            $tarjetas = Tarjeta::where('id_blog_body', $id)->get();

            if (!$tarjetas) {
                return response()->json(['error' => 'No se encontraron tarjetas'], 404);
            }

            foreach ($tarjetas as $tarjeta) {
                $tarjeta->delete();
            }

            return response()->json([
                "status" => 200,
                "message" => "Tarjetas eliminadas correctamente"
                ], 200);
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
