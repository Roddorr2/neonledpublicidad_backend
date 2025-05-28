<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogHead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;


/**
 * @OA\Tag(
 *     name="BlogHead",
 *     description="Operaciones relacionadas con la cabecera de los blogs"
 * )
 */
class BlogHeadController extends Controller
{   /**
     * @OA\Post(
     *     path="/api/blog-head",
     *     summary="Crear un nuevo BlogHead",
     *     tags={"BlogHead"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo", "texto_frase", "texto_descripcion", "public_image"},
     *             @OA\Property(property="titulo", type="string", maxLength=50),
     *             @OA\Property(property="texto_frase", type="string", maxLength=70),
     *             @OA\Property(property="texto_descripcion", type="string", maxLength=120),
     *             @OA\Property(property="public_image", type="string"),
     *             @OA\Property(property="url_image", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="BlogHead creado correctamente"),
     *     @OA\Response(response=400, description="Error de validación"),
     *     @OA\Response(response=500, description="Error interno del servidor")
     * )
     */
    public function create(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'titulo' => 'required|string|max:50',
                'texto_frase' => 'required|string|max:70',
                'texto_descripcion' => 'required|string|max:120',
                'public_image' => 'required|string',
                'url_image' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            DB::beginTransaction();

            $blogHead = BlogHead::create($request->all());

            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "BlogHead creado correctamente",
                "id" => $blogHead->id_blog_head
            ], 200);

        }catch(\Exception $ex){
            DB::rollback();
            return response()->json([
                "status" => 500,
                "message" => "Error interno del servidor",
                "error" => $ex->getMessage()
                ], 500);
        }
    }
    /**
     * @OA\Put(
     *     path="/api/blog-head/{id}",
     *     summary="Actualizar un BlogHead existente",
     *     tags={"BlogHead"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del BlogHead"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo", "texto_frase", "texto_descripcion", "public_image"},
     *             @OA\Property(property="titulo", type="string", maxLength=50),
     *             @OA\Property(property="texto_frase", type="string", maxLength=70),
     *             @OA\Property(property="texto_descripcion", type="string", maxLength=120),
     *             @OA\Property(property="public_image", type="string"),
     *             @OA\Property(property="url_image", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="BlogHead actualizado"),
     *     @OA\Response(response=400, description="Error de validación"),
     *     @OA\Response(response=404, description="BlogHead no encontrado"),
     *     @OA\Response(response=500, description="Error interno del servidor")
     * )
     */
    public function update(Request $request, int $id){
        try{
            $validator = Validator::make($request->all(), [
                'titulo' => 'required|string|max:50',
                'texto_frase' => 'required|string|max:70',
                'texto_descripcion' => 'required|string|max:120',
                'public_image' => 'required|string',
                'url_image' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $blogHead = BlogHead::find($id);

            if (!$blogHead){
                return response()->json([
                    'status'=> 404,
                    'message'=> 'BlogHead no encontrado'
                ], 404);
            }

            DB::beginTransaction();

            $blogHead->update($request->all());

            DB::commit();

            return response()->json([
                'status'=> 200,
                'message'=> 'BlogHead actualizado',
                'id'=> $blogHead->id_blog_head
            ], 200);

        }catch(\Exception $ex){
            DB::rollback();
            return response()->json([
                'status'=> 500,
                'message'=> 'Error interno del servidor',
                'error'=> $ex->getMessage()
            ], 500);
        }
    }

     /**
     * @OA\Get(
     *     path="/api/blog-head/{id}",
     *     summary="Obtener un BlogHead por ID",
     *     tags={"BlogHead"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del BlogHead"
     *     ),
     *     @OA\Response(response=200, description="Información del BlogHead"),
     *     @OA\Response(response=404, description="BlogHead no encontrado"),
     *     @OA\Response(response=500, description="Error interno del servidor")
     * )
     */
    public function show(int $id){
        try{

            $blogHead = BlogHead::find($id);
            if (!$blogHead) {
                return response()->json([
                    "status" => 404,
                    "message" => "BlogHead no encontrado"
                ],404);
            }

            return response()->json([
                "status" => 200,
                "data" => $blogHead
            ], 200);

        }catch(\Exception $ex){
            return response()->json([
                "status" => 500,
                "message" => "Error interno del servidor",
                "error" => $ex->getMessage()
                ], 500);
        }
    }
    /**
     * @OA\Delete(
     *     path="/api/blog-head/{id}",
     *     summary="Eliminar un BlogHead",
     *     tags={"BlogHead"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del BlogHead"
     *     ),
     *     @OA\Response(response=200, description="BlogHead eliminado correctamente"),
     *     @OA\Response(response=404, description="BlogHead no encontrado"),
     *     @OA\Response(response=500, description="Error al eliminar el BlogHead")
     * )
     */
    public function destroy($id)
    {
        try{

            $blogHead = BlogHead::find($id);

            if (!$blogHead) {
                return response()->json([
                    "status" => 404,
                    "message" => "BlogHead no encontrado"
                ]);
            }
            $blogHead->delete();
            return response()->json([
                "status" => 200,
                "message" => "BlogHead eliminado correctamente"
                ], 200);

        }catch(\Exception $ex){
            return response()->json([
                "status" => 500,
                "message" => "Error al eliminar el blogHead",
                "error" => $ex->getMessage()
                ], 500);
        }
    }
}
