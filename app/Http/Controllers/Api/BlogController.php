<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\BlogBody;
use App\Services\AuditoriaService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::with('card')->get();
        return response()->json($blogs, 200);
    }

    public function create(Request $request)
    {
        try{

            $validator = Validator::make($request->all(), [
                'id_blog_head' => 'required|integer|exists:blog_heads,id_blog_head',
                'id_blog_body' => 'required|integer|exists:blog_bodies,id_blog_body',
                'id_blog_footer' => 'required|integer|exists:blog_footers,id_blog_footer',
                'fecha' => 'required|date',
                'id_empleado' => 'required|integer|exists:empleados,id_empleado',
                'link' => 'nullable|string|max:255' // Campo opcional para el link
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $id_empleado = $request->id_empleado;

            DB::beginTransaction();

            // Generar link desde el request o usar blog_heads.titulo como fallback
            if ($request->has('link') && !empty($request->link)) {
                $link = Str::slug($request->link);
            } else {
                $blogHead = \App\Models\BlogHead::findOrFail($request->id_blog_head);
                $titulo = $blogHead->titulo ?? "blog";
                $link = Str::slug($titulo);
            }

            // Asegurar unicidad del link
            $baseLink = $link;
            $counter = 1;
            while (Blog::where("link", $link)->exists()) {
                $link = $baseLink . '-' . $counter;
                $counter++;
            }

            $data = $request->all();
            $data["link"] = $link;

            $blog = Blog::create($data);

            AuditoriaService::registrar(
                $blog->id_blog,
                $id_empleado,
                'CREAR',
                (\App\Models\BlogHead::findOrFail($request->id_blog_head))->titulo,
            );
            DB::commit();
            return response()->json([
                "status" => 200,
                "message" => "Blog creado correctamente",
                "id" => $blog->id_blog,
                "link" => $link
            ], 200);

        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id){
        try{
            $validator = Validator::make($request->all(), [
                'id_blog_head' => 'required|integer|exists:blog_heads,id_blog_head',
                'id_blog_body' => 'required|integer|exists:blog_bodies,id_blog_body',
                'id_blog_footer' => 'required|integer|exists:blog_footers,id_blog_footer',
                'fecha' => 'required|date',
                'id_empleado' => 'required|integer|exists:empleados,id_empleado',
                'link' => 'nullable|string|max:255', // Campo opcional para el link
                'descripcion' => 'nullable|string', // Descripción para la auditoría
            ]);

            if ($validator->fails()) {
                return response()->json(['errors'=> $validator->errors()], 400);
            }

            $id_empleado = $request->id_empleado;
            $descripcion = $request->descripcion;

            $blog = Blog::find($id);

            if (!$blog){
                return response()->json([
                    'status'=> 404,
                    'message'=> 'Blog no encontrado'
                ], 404);
            }

            DB::beginTransaction();

            // Generar link desde el request o usar blog_heads.titulo como fallback
            if ($request->has('link') && !empty($request->link)) {
                $link = Str::slug($request->link);
            } else {
                $blogHead = \App\Models\BlogHead::findOrFail($request->id_blog_head);
                $titulo = $blogHead->titulo ?? "blog";
                $link = Str::slug($titulo);
            }

            // Asegurar unicidad del link (excluyendo el blog actual)
            $baseLink = $link;
            $counter = 1;
            while (Blog::where("link", $link)
                       ->where("id_blog", "!=", $id)
                       ->exists())
            {
                $link = $baseLink . '-' . $counter;
                $counter++;
            }

            $data = $request->all();
            $data["link"] = $link;

            $blog->update($data);

            DB::commit();

            AuditoriaService::registrar(
                $blog->id_blog,
                $id_empleado,
                'ACTUALIZAR',
                (\App\Models\BlogHead::findOrFail($request->id_blog_head))->titulo, 
                $descripcion,
            );

            return response()->json([
                'status'=> 200,
                'message'=> 'Blog actualizado',
                'id'=> $blog->id_blog,
                'link' => $link
            ],200);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error'=> $e->getMessage()], 500);
        }
    }

    public function show(int $id)
    {
        try{

            $blog = Blog::with('card')->find($id);

            if (!$blog) {
                return response()->json([
                    "status" => 404,
                    "message" => "Blog no encontrado"
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

    public function showByLink(string $link)
    {
        $blog = Blog::with(['card', 'body', 'head'])->where('link', $link)->first();

        if(!$blog) {
            return response()->json([
                "status" => 400,
                "message" => "Blog no encontrado"
            ], 400);
        }

        return response()->json([
            "status" => 200,
            "message" => "Blog encontrado",
            "blog" => $blog
        ], 200);
    }

    public function destroy(int $id, Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'id_empleado' => 'required|integer|exists:empleados,id_empleado',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors'=> $validator->errors()], 400);
            }

            $id_empleado = $request->id_empleado;

            $blog = Blog::with(['card', 'head'])->find($id);

            if (!$blog){
                return response()->json([
                    'status'=> 404,
                    'message'=> 'Blog no encontrado'
                ], 404);
            }

            DB::beginTransaction();

            AuditoriaService::registrar(
                $id,
                $id_empleado,
                'ELIMINAR',
                (\App\Models\BlogHead::findOrFail((\App\Models\Blog::findOrFail($id))->id_blog_head))->titulo,
            );

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

            //tercero blog_head
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

            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "Blog eliminado correctamente"
            ],200);

        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
