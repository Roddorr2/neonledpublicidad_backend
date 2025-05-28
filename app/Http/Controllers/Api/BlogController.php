<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\BlogBody;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Blog",
 *     description="Operaciones para la gestión de blogs"
 * )
 */
class BlogController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/blogs",
     *     summary="Listar todos los blogs",
     *     tags={"Blog"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de blogs retornada correctamente"
     *     )
     * )
     */
    public function index()
    {
        $blogs = Blog::with('card')->get();
        return response()->json($blogs, 200);
    }
     /**
     * @OA\Post(
     *     path="/api/blogs",
     *     summary="Crear un nuevo blog",
     *     tags={"Blog"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_blog_head", "id_blog_body", "id_blog_footer", "fecha"},
     *             @OA\Property(property="id_blog_head", type="integer"),
     *             @OA\Property(property="id_blog_body", type="integer"),
     *             @OA\Property(property="id_blog_footer", type="integer"),
     *             @OA\Property(property="fecha", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog creado correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function create(Request $request)
    {
        try{

            $validator = Validator::make($request->all(), [
                'id_blog_head' => 'required|integer|exists:blog_heads,id_blog_head',
                'id_blog_body' => 'required|integer|exists:blog_bodies,id_blog_body',
                'id_blog_footer' => 'required|integer|exists:blog_footers,id_blog_footer',
                'fecha' => 'required|date'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            DB::beginTransaction();

            $blog = Blog::create($request->all());

            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "Blog creada correctamente",
                "id" => $blog->id_blog
            ], 200);

        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
     /**
     * @OA\Put(
     *     path="/api/blogs/{id}",
     *     summary="Actualizar un blog existente",
     *     tags={"Blog"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del blog"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_blog_head", "id_blog_body", "id_blog_footer", "fecha"},
     *             @OA\Property(property="id_blog_head", type="integer"),
     *             @OA\Property(property="id_blog_body", type="integer"),
     *             @OA\Property(property="id_blog_footer", type="integer"),
     *             @OA\Property(property="fecha", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog actualizado correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog no encontrado"
     *     )
     * )
     */
    public function update(Request $request, $id){
        try{
            $validator = Validator::make($request->all(), [
                'id_blog_head' => 'required|integer|exists:blog_heads,id_blog_head',
                'id_blog_body' => 'required|integer|exists:blog_bodies,id_blog_body',
                'id_blog_footer' => 'required|integer|exists:blog_footers,id_blog_footer',
                'fecha' => 'required|date'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors'=> $validator->errors()], 400);
            }

            $blog = Blog::find($id);

            if (!$blog){
                return response()->json([
                    'status'=> 404,
                    'message'=> 'Blog no encontrado'
                ], 404);
            }

            DB::beginTransaction();

            $blog->update($request->all());

            DB::commit();

            return response()->json([
                'status'=> 200,
                'message'=> 'Blog actualizado',
                'id'=> $blog->id_blog,
            ],200);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error'=> $e->getMessage()], 500);
        }
    }
    
    /**
     * @OA\Get(
     *     path="/api/blogs/{id}",
     *     summary="Obtener un blog por ID",
     *     tags={"Blog"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del blog"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog retornado correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog no encontrado"
     *     )
     * )
     */
    public function show(int $id)
    {
        try{

            $blog = Blog::with('card')->find($id);

            if (!$blog) {
                return response()->json([
                    "status" => 404,
                    "message" => "Blog no encontrada"
                ],400);
            }

            return response()->json([
                "status" => 200,
                'data' => $blog
            ],200);

        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
     public function showLink(string $link)
    {
        try{

            $blog = Blog::with('card')->where('link', $link)->first();

            if (!$blog) {
                return response()->json([
                    "status" => 404,
                    "message" => "Blog no encontrada"
                ],400);
            }

            return response()->json([
                "status" => 200,
                'data' => $blog
            ],200);

        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
     /**
     * @OA\Delete(
     *     path="/api/blogs/{id}",
     *     summary="Eliminar un blog",
     *     tags={"Blog"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del blog"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog eliminado correctamente"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno al eliminar el blog"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        try{

            $blog = Blog::with(['card', 'head'])->find($id);

            $id_header_blog = $blog->id_blog_head;

            $id_body_blog = $blog->id_blog_body;

            $id_footer_blog = $blog->id_blog_footer;

            $relativePath = "images/templates/plantilla{$blog->card->id_plantilla}/" . Str::slug($blog->head->titulo) . $blog->id_blog;

            //eliminarla pero ver si existe asi que normal obvia la anterior
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->deleteDirectory($relativePath);
            }

            //primero card
            $card_object = new CardController();

            $card_object->destroy($blog->card->id_card);

            //segundo blog
            $blog->delete();

            //tecero blog_head
            $blog_head = new BlogHeadController();
            $blog_head->destroy($id_header_blog);

            //cuarto blog_footer
            $blog_footer = new BlogFooterController();
            $blog_footer->destroy($id_footer_blog);

            //quinto tarjetas
            $tarjeta = new TarjetaController();
            $tarjeta->destroyAll($id_body_blog);

            //sexto commend_tarjeta
            $blog_body_model = BlogBody::find($id_body_blog);
            $commend_tarjeta = new CommendTarjetaController();
            $commend_tarjeta->destroy($blog_body_model->id_commend_tarjeta);

            //por ultimo blog_body
            $blog_body_model->delete();

            return response()->json([
                "status" => 200,
                "message" => "Blog eliminado correctamente"
            ],200);

        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
