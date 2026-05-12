<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogBody\StoreBlogBodyRequest;
use App\Http\Requests\BlogBody\UpdateBlogBodyRequest;
use App\Models\BlogBody;
use Illuminate\Support\Facades\DB;

class BlogBodyController extends Controller
{
    public function create(StoreBlogBodyRequest $request)
    {
        try {
            DB::beginTransaction();

            $blogBody = BlogBody::create($request->all());

            DB::commit();

            return response()->json([
                "status"  => 200,
                "message" => "BlogBody creado correctamente",
                "id"      => $blogBody->id_blog_body
            ], 200);

        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                "status"  => 500,
                "message" => "Error al crear el blogBody",
                "error"   => $ex->getMessage()
            ], 500);
        }
    }

    public function update(UpdateBlogBodyRequest $request, int $id)
    {
        try {
            $blogBody = BlogBody::find($id);

            if (!$blogBody) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'BlogBody no encontrado'
                ], 404);
            }

            DB::beginTransaction();

            $blogBody->update($request->all());

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Blog Body actualizado',
                'id'      => $blogBody->id_blog_body
            ], 200);

        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                "status"  => 500,
                "message" => $ex->getMessage(),
                "error"   => "Error interno del servidor"
            ], 500);
        }
    }

    public function show(int $id)
    {
        try {
            $blogBody = BlogBody::with('commend_tarjeta', 'tarjetas')->find($id);

            if (!$blogBody) {
                return response()->json([
                    "status"  => 404,
                    "message" => "BlogBody no encontrada"
                ], 404);
            }

            return response()->json([
                "status" => 200,
                "data"   => $blogBody
            ], 200);

        } catch (\Exception $ex) {
            return response()->json([
                "status"  => 500,
                "message" => "Error interno",
                "error"   => $ex->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $blogBody = BlogBody::find($id);

            if (!$blogBody) {
                return response()->json([
                    "status"  => 404,
                    "message" => "BlogBody no encontrada"
                ], 404);
            }

            $blogBody->delete();

            return response()->json([
                "status"  => 200,
                "message" => "BlogBody eliminada correctamente"
            ], 200);

        } catch (\Exception $ex) {
            return response()->json([
                "status"  => 500,
                "message" => "Error al eliminar el BlogBody",
                "error"   => $ex->getMessage()
            ], 500);
        }
    }
}