<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Permiso;
use App\Models\Rol;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!$request->user()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado'
            ], 401);
        }

        $user = $request->user();
        $userRoles = $user->currentAccessToken()->abilities;

        // Obtener slugs de permisos requeridos
        $requiredPermissionSlugs = array_values($permissions);

        // Verificar si el usuario tiene al menos uno de los permisos requeridos
        if ($this->hasPermission($userRoles, $requiredPermissionSlugs)) {
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'No tiene los permisos necesarios para acceder a este recurso'
        ], 403);
    }

    /**
     * Verifica si el usuario tiene al menos uno de los permisos requeridos.
     * Utiliza caché para evitar múltiples consultas a la base de datos.
     *
     * @param array $roleNames Nombres de roles del usuario
     * @param array $permissionSlugs Slugs de permisos requeridos
     * @return bool
     */
    private function hasPermission(array $roleNames, array $permissionSlugs): bool
    {
        // Obtener todos los permisos asociados a los roles del usuario (con caché)
        $userPermissions = $this->getUserPermissions($roleNames);

        // Verificar si existe intersección entre permisos requeridos y los del usuario
        foreach ($permissionSlugs as $slug) {
            if (in_array($slug, $userPermissions, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtiene todos los permisos para los roles del usuario usando caché.
     * Una sola consulta para todos los permisos en lugar de N+M consultas.
     *
     * @param array $roleNames Nombres de roles
     * @return array Array de slugs de permisos
     */
    private function getUserPermissions(array $roleNames): array
    {
        // Crear una clave de caché única basada en los roles del usuario
        $sortedRoles = $roleNames;
        sort($sortedRoles);
        $cacheKey = 'user_permissions:' . md5(implode('|', $sortedRoles));

        // Recuperar de caché o consultar la BD (UNA SOLA CONSULTA)
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($roleNames) {
            return Rol::whereIn('nombre', $roleNames)
                ->with('permisos:id_permiso,slug')
                ->get()
                ->flatMap(fn($rol) => $rol->permisos->pluck('slug'))
                ->unique()
                ->values()
                ->all();
        });
    }
}
