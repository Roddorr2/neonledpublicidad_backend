<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogHeadRequest;
use App\Http\Requests\UpdateBlogHeadRequest;
use App\Models\BlogHead;
use Illuminate\Support\Facades\DB;

class BlogHeadController extends Controller
{
    public function create(StoreBlogHeadRequest $request)
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            $blogHead = BlogHead::create($validated);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'BlogHead creado correctamente',
                'id'      => $blogHead->id_blog_head,
            ], 200);

        } catch (\Exception $ex) {
            DB::rollback();

            return response()->json([
                'status'  => 500,
                'message' => 'Error interno del servidor',
                'error'   => $ex->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateBlogHeadRequest $request, int $id)
    {
        try {
            $validated = $request->validated();

            $blogHead = BlogHead::find($id);

            if (! $blogHead) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'BlogHead no encontrado',
                ], 404);
            }

            DB::beginTransaction();

            $blogHead->update($validated);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'BlogHead actualizado',
                'id'      => $blogHead->id_blog_head,
            ], 200);

        } catch (\Exception $ex) {
            DB::rollback();

            return response()->json([
                'status'  => 500,
                'message' => 'Error interno del servidor',
                'error'   => $ex->getMessage(),
            ], 500);
        }
    }

    public function show(int $id)
    {
        try {

            $blogHead = BlogHead::find($id);
            if (! $blogHead) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'BlogHead no encontrado',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'data'   => $blogHead,
            ], 200);

        } catch (\Exception $ex) {
            return response()->json([
                'status'  => 500,
                'message' => 'Error interno del servidor',
                'error'   => $ex->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {

            $blogHead = BlogHead::find($id);

            if (! $blogHead) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'BlogHead no encontrado',
                ]);
            }
            $blogHead->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'BlogHead eliminado correctamente',
            ], 200);

        } catch (\Exception $ex) {
            return response()->json([
                'status'  => 500,
                'message' => 'Error al eliminar el blogHead',
                'error'   => $ex->getMessage(),
            ], 500);
        }
    }
}
