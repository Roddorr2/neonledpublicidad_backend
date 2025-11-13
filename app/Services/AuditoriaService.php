<?php

namespace App\Services;

use App\Models\BlogAuditoria;
use Carbon\Carbon;

class AuditoriaService
{
    public static function registrar(int $idBlog, int $idEmpleado, string $accion, ?string $descripcion = null): BlogAuditoria
    {
        return BlogAuditoria::create([
            'id_blog' => $idBlog,
            'id_empleado' => $idEmpleado,
            'accion' => strtoupper($accion),
            'descripcion' => $descripcion,
            'fecha_hora' => Carbon::now(),
        ]);
    }
}
