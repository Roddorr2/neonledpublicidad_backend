<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\DestroyBlogRequest;
use App\Http\Requests\Blog\StoreBlogRequest;
use App\Http\Requests\Blog\UpdateBlogRequest;
use App\Models\Blog;
use App\Models\BlogBody;
use App\Models\BlogHead;
use App\Services\AuditoriaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Controlador de Gestión de Artículos de Blog (API).
 *
 * =========================================================================================
 * GUÍA DE ALINEACIÓN SEO Y ARQUITECTURA DE ENLACES (SEO-13 / DevOps & SEO Tech)
 * =========================================================================================
 * 1. Estructura de URLs Canónicas en Frontend (Next.js App Router):
 *    - La ruta pública canónica esperada por el cliente web es estrictamente:
 *      https://www.ledneonpublicidad.com/blog/plantilla{id_plantilla}/{slug}/
 *    - Donde:
 *      * {id_plantilla}: Identificador de la plantilla asignada en la tarjeta del blog ($blog->card->id_plantilla).
 *        Actualmente se soportan las rutas dinámicas: /blog/plantilla1/[slug], /blog/plantilla2/[slug], /blog/plantilla3/[slug].
 *      * {slug}: Corresponde al campo 'link' guardado en esta tabla ($blog->link).
 *    - NUNCA compartir ni generar URLs con parámetros obsoletos tipo '?blog=...' en sitemaps o enlaces externos.
 *
 * 2. Reglas Editoriales para Redactores y Administradores de Contenido:
 *    - Inmutabilidad de Slugs Indexados:
 *      Una vez que un artículo es publicado e indexado por Googlebot, su slug ('link') NO debe cambiarse arbitrariamente.
 *    - Coordinación Obligatoria con SEO ante Modificaciones o Bajas:
 *      Si por razones de fuerza mayor se modifica el 'link' (slug) de un blog existente o se elimina el artículo:
 *      a) Notificar inmediatamente al equipo de SEO / DevOps.
 *      b) Registrar una regla de redirección 301 permanente en 'next.config.mjs' (async redirects()) apuntando
 *         la URL antigua hacia la nueva URL del artículo o hacia la página de producto/categoría afín.
 *      c) Evitar la generación de respuestas 404 (Not Found) que penalizan el posicionamiento orgánico del dominio.
 * =========================================================================================
 */
class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::with('card')->get();

        return response()->json($blogs, 200);
    }

    /**
     * Registra un nuevo blog y genera un slug ('link') único y seguro para SEO.
     *
     * URL resultante esperada en frontend: /blog/plantilla{id_plantilla}/{link}/
     */
    public function create(StoreBlogRequest $request)
    {
        try {
            $id_empleado = $request->id_empleado;

            DB::beginTransaction();

            if ($request->has('link') && ! empty($request->link)) {
                $link = Str::slug($request->link);
            } else {
                $blogHead = BlogHead::findOrFail($request->id_blog_head);
                $titulo   = $blogHead->titulo ?? 'blog';
                $link     = Str::slug($titulo);
            }

            $baseLink = $link;
            $counter  = 1;
            while (Blog::where('link', $link)->exists()) {
                $link = $baseLink . '-' . $counter;
                $counter++;
            }

            $data         = $request->all();
            $data['link'] = $link;

            $blog = Blog::create($data);

            AuditoriaService::registrar(
                $blog->id_blog,
                $id_empleado,
                'CREAR',
                (BlogHead::findOrFail($request->id_blog_head))->titulo,
            );

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Blog creado correctamente',
                'id'      => $blog->id_blog,
                'link'    => $link,
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza el blog y su slug ('link').
     *
     * ADVERTENCIA SEO: Si el 'link' cambia para un blog ya indexado, se debe coordinar
     * de inmediato una redirección 301 en Next.js (next.config.mjs) desde el slug anterior
     * hacia el nuevo slug para prevenir errores 404.
     */
    public function update(UpdateBlogRequest $request, $id)
    {
        try {
            $id_empleado = $request->id_empleado;
            $descripcion = $request->descripcion;

            $blog = Blog::find($id);

            if (! $blog) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Blog no encontrado',
                ], 404);
            }

            DB::beginTransaction();

            if ($request->has('link') && ! empty($request->link)) {
                $link = Str::slug($request->link);
            } else {
                $blogHead = BlogHead::findOrFail($request->id_blog_head);
                $titulo   = $blogHead->titulo ?? 'blog';
                $link     = Str::slug($titulo);
            }

            $baseLink = $link;
            $counter  = 1;
            while (Blog::where('link', $link)
                ->where('id_blog', '!=', $id)
                ->exists()) {
                $link = $baseLink . '-' . $counter;
                $counter++;
            }

            $data         = $request->all();
            $data['link'] = $link;

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
                'link'    => $link,
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

            if (! $blog) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Blog no encontrado',
                ], 400);
            }

            return response()->json([
                'status' => 200,
                'data'   => $blog,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function showByLink(string $link)
    {
        $blog = Blog::with(['card', 'body', 'head'])->where('link', $link)->first();

        if (! $blog) {
            return response()->json([
                'status'  => 400,
                'message' => 'Blog no encontrado',
            ], 400);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Blog encontrado',
            'blog'    => $blog,
        ], 200);
    }

    /**
     * Elimina el blog y sus recursos asociados.
     *
     * ADVERTENCIA SEO: Al eliminar un blog indexado, se debe registrar una redirección 301
     * en Next.js (next.config.mjs) desde su URL canónica hacia la categoría o producto correspondiente,
     * y remover la URL del sitemap para no generar respuestas 404 en Googlebot.
     */
    public function destroy(int $id, DestroyBlogRequest $request)
    {
        try {
            $id_empleado = $request->id_empleado;

            $blog = Blog::with(['card', 'head'])->find($id);

            if (! $blog) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Blog no encontrado',
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

            $card_object = new CardController;
            $card_object->destroy($blog->card->id_card);

            $blog->delete();

            $blog_head = new BlogHeadController;
            $blog_head->destroy($id_header_blog);

            $blog_footer = new BlogFooterController;
            $blog_footer->destroy($id_footer_blog);

            $tarjeta = new TarjetaController;
            $tarjeta->destroyAll($id_body_blog);

            $blog_body_model = BlogBody::find($id_body_blog);
            $commend_tarjeta = new CommendTarjetaController;
            $commend_tarjeta->destroy($blog_body_model->id_commend_tarjeta);

            $blog_body_model->delete();

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Blog eliminado correctamente',
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
