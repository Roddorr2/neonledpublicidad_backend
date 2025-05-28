<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogFooter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
/**
 * @OA\Tag(
 *     name="BlogFooter",
 *     description="Operaciones relacionadas con el pie de página de los blogs"
 * )
 */
class BlogFooterController extends Controller
{
     /**
     * @OA\Post(
     *     path="/api/blog-footer",
     *     summary="Crear un nuevo BlogFooter",
     *     tags={"BlogFooter"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo", "descripcion", "public_image1", "public_image2", "public_image3"},
     *             @OA\Property(property="titulo", type="string", maxLength=255),
     *             @OA\Property(property="descripcion", type="string"),
     *             @OA\Property(property="public_image1", type="string"),
     *             @OA\Property(property="url_image1", type="string"),
     *             @OA\Property(property="public_image2", type="string"),
     *             @OA\Property(property="url_image2", type="string"),
     *             @OA\Property(property="public_image3", type="string"),
     *             @OA\Property(property="url_image3", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="BlogFooter creado correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function create(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'public_image1' => 'required|string',
                'url_image1' => 'nullable|string',
                'public_image2' => 'required|string',
                'url_image2' => 'nullable|string',
                'public_image3' => 'required|string',
                'url_image3' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            DB::beginTransaction();

            $blogFooter = BlogFooter::create($request->all());

            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "BlogFooter creado correctamente",
                "id" => $blogFooter->id_blog_footer
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
     *     path="/api/blog-footer/{id}",
     *     summary="Actualizar un BlogFooter existente",
     *     tags={"BlogFooter"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del BlogFooter"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo", "descripcion", "public_image1", "public_image2", "public_image3"},
     *             @OA\Property(property="titulo", type="string", maxLength=255),
     *             @OA\Property(property="descripcion", type="string"),
     *             @OA\Property(property="public_image1", type="string"),
     *             @OA\Property(property="url_image1", type="string"),
     *             @OA\Property(property="public_image2", type="string"),
     *             @OA\Property(property="url_image2", type="string"),
     *             @OA\Property(property="public_image3", type="string"),
     *             @OA\Property(property="url_image3", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="BlogFooter actualizado correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="BlogFooter no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function update(Request $request, int $id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'public_image1' => 'required|string',
                'url_image1' => 'nullable|string',
                'public_image2' => 'required|string',
                'url_image2' => 'nullable|string',
                'public_image3' => 'required|string',
                'url_image3' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors'=> $validator->errors()], 400);
            }

            $blogFooter = BlogFooter::find($id);

            if (!$blogFooter){
                return response()->json([
                    'status'=> 404,
                    'message'=> 'BlogFooter no encontrado'
                ], 404);
            }

            DB::beginTransaction();

            $blogFooter->update($request->all());

            DB::commit();
            return response()->json([
                'status'=> 200,
                'message'=> 'BlogFooter actualizado',
                'id'=> $blogFooter->id_blog_footer,
            ],200);

        }catch(\Exception $ex){
            DB::rollback();
            return response()->json([
                'status'=> 500,
                'message'=> $ex->getMessage()
            ], 500);
        }
    }
     /**
     * @OA\Get(
     *     path="/api/blog-footer/{id}",
     *     summary="Obtener un BlogFooter por ID",
     *     tags={"BlogFooter"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del BlogFooter"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Información del BlogFooter"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="BlogFooter no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function show(int $id){
        try{

            $blogFooter = BlogFooter::find($id);
            if (!$blogFooter) {
                return response()->json([
                    "status" => 404,
                    "message" => "BlogFooter no encontrado"
                ],404);
            }

            return response()->json([
                "status" => 200,
                "data" => $blogFooter
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
     *     path="/api/blog-footer/{id}",
     *     summary="Eliminar un BlogFooter",
     *     tags={"BlogFooter"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del BlogFooter"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="BlogFooter eliminado correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="BlogFooter no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno al eliminar el BlogFooter"
     *     )
     * )
     */
    public function destroy($id)
    {
        try{

            $blogFooter = BlogFooter::find($id);

            if (!$blogFooter) {
                return response()->json([
                    "status" => 404,
                    "message" => "BlogFooter no encontrado"
                ]);
            }
            $blogFooter->delete();
            return response()->json([
                "status" => 200,
                "message" => "BlogFooter eliminado correctamente"
                ], 200);

        }catch(\Exception $ex){
            return response()->json([
                "status" => 500,
                "message" => "Error al eliminar el blogFooter",
                "error" => $ex->getMessage()
                ], 500);
        }
    }
}
