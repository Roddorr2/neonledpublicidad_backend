<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogFooterRequest;
use App\Http\Requests\UpdateBlogFooterRequest;
use App\Models\BlogFooter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BlogFooterController extends Controller
{
    public function create(StoreBlogFooterRequest $request)
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            $blogFooter = BlogFooter::create($validated);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'BlogFooter creado correctamente',
                'id'      => $blogFooter->id_blog_footer,
            ], 200);
        } catch (\Exception $ex) {
            DB::rollback();
            Log::error('BlogFooter create error: ' . $ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            return response()->json([
                'status'  => 500,
                'message' => 'Error interno del servidor',
                'error'   => 'Error interno del servidor',
            ], 500);
        }
    }

    public function update(UpdateBlogFooterRequest $request, int $id)
    {
        try {
            $validated = $request->validated();

            $blogFooter = BlogFooter::find($id);

            if (! $blogFooter) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'BlogFooter no encontrado',
                ], 404);
            }

            DB::beginTransaction();

            $blogFooter->update($validated);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'BlogFooter actualizado',
                'id'      => $blogFooter->id_blog_footer,
            ], 200);

        } catch (\Exception $ex) {
            DB::rollback();
            Log::error('BlogFooter update error: ' . $ex->getMessage(), ['trace' => $ex->getTraceAsString()]);

            return response()->json([
                'status'  => 500,
                'message' => 'Error al actualizar el blogFooter',
                'error'   => 'Error interno del servidor',
            ], 500);
        }
    }

    public function show(int $id)
    {
        try {

            $blogFooter = BlogFooter::find($id);
            if (! $blogFooter) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'BlogFooter no encontrado',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'data'   => $blogFooter,
            ], 200);

        } catch (\Exception $ex) {
            Log::error('BlogFooter show error: ' . $ex->getMessage(), ['exception' => $ex]);

            return response()->json([
                'status'  => 500,
                'message' => 'Error interno del servidor',
                'error'   => 'Error interno del servidor',
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {

            $blogFooter = BlogFooter::find($id);

            if (! $blogFooter) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'BlogFooter no encontrado',
                ]);
            }
            $blogFooter->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'BlogFooter eliminado correctamente',
            ], 200);

        } catch (\Exception $ex) {
            Log::error('BlogFooter delete error: ' . $ex->getMessage(), ['exception' => $ex]);

            return response()->json([
                'status'  => 500,
                'message' => 'Error al eliminar el blogFooter',
                'error'   => 'Error interno del servidor',
            ], 500);
        }
    }
}
