<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogAuditoria;
use Illuminate\Http\Request;

class BlogAuditoriaController extends Controller
{
    /**
     * Mostrar las auditorías de un blog específico.
     */
    public function show()
    {
        try {
            $auditorias = BlogAuditoria::with([
                'empleado:id_empleado,nombre,apellido',
                'blog.card:id_card,titulo,descripcion,public_image,url_image,id_blog',])
                ->orderBy('fecha_hora', 'desc')
                ->paginate(20);

            if ($auditorias->count() === 0) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No se encontraron registros de auditoría para este blog'
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'data' => $auditorias
            ], 200);

        } catch (\Exception $ex) {
            return response()->json([
                'status' => 500,
                'message' => 'Error interno del servidor',
                'error' => $ex->getMessage()
            ], 500);
        }
    }
}
