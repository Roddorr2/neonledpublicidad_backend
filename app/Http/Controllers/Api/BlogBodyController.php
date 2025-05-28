<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogBody;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;


/**
 * @OA\Tag(
 *     name="BlogBody",
 *     description="Operaciones relacionadas con el contenido del cuerpo del blog"
 * )
 */
class BlogBodyController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/blog-body",
     *     summary="Crear un nuevo BlogBody",
     *     tags={"BlogBody"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo", "descripcion"},
     *             @OA\Property(property="titulo", type="string"),
     *             @OA\Property(property="descripcion", type="string"),
     *             @OA\Property(property="id_commend_tarjeta", type="integer"),
     *             @OA\Property(property="public_image1", type="string"),
     *             @OA\Property(property="url_image1", type="string"),
     *             @OA\Property(property="public_image2", type="string"),
     *             @OA\Property(property="url_image2", type="string"),
     *             @OA\Property(property="public_image3", type="string"),
     *             @OA\Property(property="url_image3", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="BlogBody creado correctamente"),
     *     @OA\Response(response=400, description="Errores de validación"),
     *     @OA\Response(response=500, description="Error interno del servidor")
     * )
     */
    public function create(Request $request)
    {
        try{

            $validator = Validator::make($request->all(), [
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'id_commend_tarjeta' => 'nullable|integer|exists:commend_tarjetas,id_commend_tarjeta',
                'public_image1' => 'nullable|string',
                'url_image1' => 'nullable|string',
                'public_image2' => 'nullable|string',
                'url_image2' => 'nullable|string',
                'public_image3' => 'nullable|string',
                'url_image3' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            DB::beginTransaction();

            $blogBody = BlogBody::create($request->all());

            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "BlogBody creado correctamente",
                "id" => $blogBody->id_blog_body
            ], 200);

        }catch(\Exception $ex){
            DB::rollback();
            return response()->json([
                "status" => 500,
                "message" => "Error al crear el blogBody",
                "error" => $ex->getMessage()
            ], 500);
        }
    }
    /**
     * @OA\Put(
     *     path="/api/blog-body/{id}",
     *     summary="Actualizar un BlogBody existente",
     *     tags={"BlogBody"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo", "descripcion"},
     *             @OA\Property(property="titulo", type="string"),
     *             @OA\Property(property="descripcion", type="string"),
     *             @OA\Property(property="id_commend_tarjeta", type="integer"),
     *             @OA\Property(property="public_image1", type="string"),
     *             @OA\Property(property="url_image1", type="string"),
     *             @OA\Property(property="public_image2", type="string"),
     *             @OA\Property(property="url_image2", type="string"),
     *             @OA\Property(property="public_image3", type="string"),
     *             @OA\Property(property="url_image3", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="BlogBody actualizado"),
     *     @OA\Response(response=400, description="Errores de validación"),
     *     @OA\Response(response=404, description="BlogBody no encontrado"),
     *     @OA\Response(response=500, description="Error interno del servidor")
     * )
     */
    public function update(Request $request, int $id){
        try{
            $validator =  Validator::make($request->all(), [
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'id_commend_tarjeta' => 'nullable|integer|exists:commend_tarjetas,id_commend_tarjeta',
                'public_image1' => 'nullable|string',
                'url_image1' => 'nullable|string',
                'public_image2' => 'nullable|string',
                'url_image2' => 'nullable|string',
                'public_image3' => 'nullable|string',
                'url_image3' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors'=> $validator->errors()], 400);
            }

            $blogBody = BlogBody::find($id);

            if (!$blogBody){
                return response()->json([
                    'status'=> 404,
                    'message'=> 'BlogBody no encontrado'
                ], 404);
            }

            DB::beginTransaction();

            $blogBody->update($request->all());

            DB::commit();
            return response()->json([
                'status'=> 200,
                'message'=> 'Blog Body actualizado',
                'id'=> $blogBody->id_blog_body
            ], 200);
        }catch(\Exception $ex){
            DB::rollback();
            return response()->json([
                "status"=> 500,
                "message"=> "",
                "id"=> $id,
                ],500);
            }
    }
     /**
     * @OA\Get(
     *     path="/api/blog-body/{id}",
     *     summary="Mostrar un BlogBody por ID",
     *     tags={"BlogBody"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="BlogBody encontrado"),
     *     @OA\Response(response=404, description="BlogBody no encontrado"),
     *     @OA\Response(response=500, description="Error interno del servidor")
     * )
     */
    public function show(int $id){
        try{
            $blogBody = BlogBody::with('commend_tarjeta','tarjetas')->find($id);
            if (!$blogBody) {
                return response()->json([
                    "status" => 404,
                    "message" => "BlogBody no encontrada"
                ],404);
            }
            return response()->json([
                "status" => 200,
                "data" => $blogBody
            ], 200);

        }catch(\Exception $ex){
            return response()->json([
                "status" => 500,
                "message" => "Error interno",
                "error" => $ex->getMessage()
            ], 500);
        }
    }
    /**
     * @OA\Delete(
     *     path="/api/blog-body/{id}",
     *     summary="Eliminar un BlogBody",
     *     tags={"BlogBody"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="BlogBody eliminado correctamente"),
     *     @OA\Response(response=404, description="BlogBody no encontrado"),
     *     @OA\Response(response=500, description="Error interno del servidor")
     * )
     */
    public function destroy(int $id)
    {
        try{

            $blogBody = BlogBody::find($id);

            if (!$blogBody) {
                return response()->json([
                    "status" => 404,
                    "message" => "BlogBody no encontrada"
                ],404);
            }

            $blogBody->delete();

            return response()->json([
                "status" => 200,
                "message" => "BlogBody eliminada correctamente"
            ], 200);

        }catch(\Exception $ex){
            return response()->json([
                "status" => 500,
                "message" => "Error al eliminar el BlogBody",
                "error" => $ex->getMessage()
            ], 500);
        }
    }
}
