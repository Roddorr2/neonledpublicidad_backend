<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\StoreBlogRequest;
use App\Http\Requests\Blog\UpdateBlogRequest;
use App\Http\Requests\Blog\DestroyBlogRequest;
use App\Models\Blog;
use App\Models\BlogBody;
use App\Models\BlogHead;
use App\Services\AuditoriaService;
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

    public function create(StoreBlogRequest $request)
    {
        try {
            $id_empleado = $request->id_empleado;

            DB::beginTransaction();

            if ($request->has('link') && !empty($request->link)) {
                $link = Str::slug($request->link);
            } else {
                $blogHead = BlogHead::findOrFail($request->id_blog_head);
                $titulo = $blogHead->titulo ?? "blog";
                $link = Str::slug($titulo);
            }

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
                (BlogHead::findOrFail($request->id_blog_head))->titulo,
            );

            DB::commit();

            return response()->json([
                "status"  => 200,
                "message" => "Blog creado correctamente",
                "id"      => $blog->id_blog,
                "link"    => $link
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateBlogRequest $request, $id)
    {
        try {
            $id_empleado = $request->id_empleado;
            $descripcion = $request->descripcion;

            $blog = Blog::find($id);

            if (!$blog) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Blog no encontrado'
                ], 404);
            }

            DB::beginTransaction();

            if ($request->has('link') && !empty($request->link)) {
                $link = Str::slug($request->link);
            } else {
                $blogHead = BlogHead::findOrFail($request->id_blog_head);
                $titulo = $blogHead->titulo ?? "blog";
                $link = Str::slug($titulo);
            }

            $baseLink = $link;
            $counter = 1;
            while (Blog::where("link", $link)
                       ->where("id_blog", "!=", $id)
                       ->exists()) {
                $link = $baseLink . '-' . $counter;
                $counter++;
            }

            $data = $request->all();
            $data["link"] = $link;

            $blog->update($data);

            AuditoriaService::registrar(
                $blog->id_blog,
                $id_empleado,
                'ACTUALIZAR',
                BlogHead::findOrFail($request->id_blog_head)->titulo,
                $descripcion
            );

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Blog actualizado',
                'id'      => $blog->id_blog,
                'link'    => $link
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(int $id)
    {
        try {
            $blog = Blog::with('card')->find($id);

            if (!$blog) {
                return response()->json([
                    "status"  => 404,
                    "message" => "Blog no encontrado"
                ], 400);
            }

            return response()->json([
                "status" => 200,
                'data'   => $blog
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function showByLink(string $link)
    {
        $blog = Blog::with(['card', 'body', 'head'])->where('link', $link)->first();

        if (!$blog) {
            return response()->json([
                "status"  => 400,
                "message" => "Blog no encontrado"
            ], 400);
        }

        return response()->json([
            "status"  => 200,
            "message" => "Blog encontrado",
            "blog"    => $blog
        ], 200);
    }

    public function destroy(int $id, DestroyBlogRequest $request)
    {
        try {
            $id_empleado = $request->id_empleado;

            $blog = Blog::with(['card', 'head'])->find($id);

            if (!$blog) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Blog no encontrado'
                ], 404);
            }

            DB::beginTransaction();

            AuditoriaService::registrar(
                $id,
                $id_empleado,
                'ELIMINAR',
                $titulo = $blog->head->titulo ?? 'blog',
            );

            $id_header_blog = $blog->id_blog_head;
            $id_body_blog   = $blog->id_blog_body;
            $id_footer_blog = $blog->id_blog_footer;

            $relativePath = "images/templates/plantilla{$blog->card->id_plantilla}/" . Str::slug($blog->head->titulo) . $blog->id_blog;

            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->deleteDirectory($relativePath);
            }

            $card_object = new CardController();
            $card_object->destroy($blog->card->id_card);

            $blog->delete();

            $blog_head = new BlogHeadController();
            $blog_head->destroy($id_header_blog);

            $blog_footer = new BlogFooterController();
            $blog_footer->destroy($id_footer_blog);

            $tarjeta = new TarjetaController();
            $tarjeta->destroyAll($id_body_blog);

            $blog_body_model = BlogBody::find($id_body_blog);
            $commend_tarjeta = new CommendTarjetaController();
            $commend_tarjeta->destroy($blog_body_model->id_commend_tarjeta);

            $blog_body_model->delete();

            DB::commit();

            return response()->json([
                "status"  => 200,
                "message" => "Blog eliminado correctamente"
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}