<?php

namespace App\Http\Middleware;

use App\Models\Permiso;
use App\Models\Rol;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, ...$requirements): Response
    {
        if (! $request->user()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No autorizado',
            ], 401);
        }

        // capacidades del token
        $tokenAbilities = $request->user()->currentAccessToken()->abilities;

        foreach ($requirements as $requirement) {
            // verificar si es rol
            if (in_array($requirement, $tokenAbilities)) {
                return $next($request);
            }

            // obtenemos los roles desde tokenAbilities
            foreach ($tokenAbilities as $role) {
                $rolModel = Rol::where('nombre', $role)->first();

                if ($rolModel) {
                    // verifica si el rol tiene ese permiso en específico
                    $permisoModel = Permiso::where('slug', $requirement)->first();

                    if ($permisoModel && $rolModel->permisos()->where('permisos.id_permiso', $permisoModel->id_permiso)->exists()) {
                        return $next($request);
                    }
                }
            }
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'No tiene los permisos necesarios para acceder a este recurso',
        ], 403);
    }
}
