<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Permiso;
use Illuminate\Database\Eloquent\ModelNotFoundException;


/**
 * @OA\Tag(
 *     name="Roles",
 *     description="Operaciones relacionadas con la gestión de roles y sus permisos en el sistema"
 * )
 */
class RolController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     /**
 * @OA\Get(
 *     path="/api/roles",
 *     summary="Listar todos los roles",
 *     tags={"Roles"},
 *     @OA\Response(
 *         response=200,
 *         description="Lista de roles obtenida exitosamente"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error al obtener roles"
 *     )
 * )
 */
    public function index()
    {
        try {
            $roles = Rol::select('id_rol', 'nombre')->get();
            return response()->json([
                'status' => 200,
                'data' => $roles,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Error al obtener roles',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */

     /**
 * @OA\Post(
 *     path="/api/roles",
 *     summary="Crear un nuevo rol",
 *     tags={"Roles"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"nombre"},
 *             @OA\Property(property="nombre", type="string", example="Administrador"),
 *             @OA\Property(property="permisos", type="array", @OA\Items(type="integer"))
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Rol creado correctamente"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error al crear rol"
 *     )
 * )
 */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255|unique:roles',
                'permisos' => 'nullable|array',
                'permisos.*' => 'exists:permisos,id_permiso',
            ]);

            $rol = Rol::create([
                'nombre' => $validatedData['nombre'],
            ]);

            if (!empty($validatedData['permisos'])) {
                $rol->permisos()->attach($validatedData['permisos']);
            }

            return response()->json([
                'status' => 201,
                'message' => 'Rol creado correctamente',
                'data' => $rol,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Error al crear rol',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */


     /**
 * @OA\Get(
 *     path="/api/roles/{id}",
 *     summary="Obtener un rol por ID",
 *     tags={"Roles"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Rol obtenido correctamente"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Rol no encontrado"
 *     )
 * )
 */
    public function show($id)
    {
        try {
            $rol = Rol::with('permisos')->findOrFail($id);
            return response()->json([
                'status' => 200,
                'data' => $rol,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 404,
                'error' => 'Rol no encontrado',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */

     /**
 * @OA\Put(
 *     path="/api/roles/{id}",
 *     summary="Actualizar un rol",
 *     tags={"Roles"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"nombre"},
 *             @OA\Property(property="nombre", type="string", example="Editor"),
 *             @OA\Property(property="permisos", type="array", @OA\Items(type="integer"))
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Rol actualizado correctamente"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Rol no encontrado"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error al actualizar rol"
 *     )
 * )
 */
    public function update(Request $request, $id)
    {
        try {
            $rol = Rol::findOrFail($id);
            
            $validatedData = $request->validate([
                'nombre' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('roles')->ignore($id, 'id_rol'),
                ],
                'permisos' => 'nullable|array',
                'permisos.*' => 'exists:permisos,id_permiso',
            ]);

            $rol->update([
                'nombre' => $validatedData['nombre'],
            ]);

            if (isset($validatedData['permisos'])) {
                $rol->permisos()->sync($validatedData['permisos']);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Rol actualizado correctamente',
                'data' => $rol,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => $e instanceof ModelNotFoundException ? 404 : 500,
                'error' => $e instanceof ModelNotFoundException ? 'Rol no encontrado' : 'Error al actualizar rol',
                'message' => $e->getMessage()
            ], $e instanceof ModelNotFoundException ? 404 : 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */

     /**
 * @OA\Delete(
 *     path="/api/roles/{id}",
 *     summary="Eliminar un rol",
 *     tags={"Roles"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Rol eliminado correctamente"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="El rol tiene empleados asociados"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Rol no encontrado"
 *     )
 * )
 */
    public function destroy($id)
    {
        try {
            $rol = Rol::findOrFail($id);
            
            // verificar si hay empleados con este rol
            if ($rol->empleados()->count() > 0) {
                return response()->json([
                    'status' => 400,
                    'error' => 'No se puede eliminar el rol porque tiene empleados asociados',
                ], 400);
            }
            
            // eliminar la relación con permisos
            $rol->permisos()->detach();
            $rol->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Rol eliminado correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500,
                'error' => $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 'Rol no encontrado' : 'Error al eliminar rol',
                'message' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }


    /**
 * @OA\Get(
 *     path="/api/roles/{id}/permisos",
 *     summary="Obtener permisos asociados a un rol",
 *     tags={"Roles"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Permisos obtenidos correctamente"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Rol no encontrado"
 *     )
 * )
 */
    public function getPermisos($id)
    {
        try {
            $rol = Rol::findOrFail($id);
            $permisos = $rol->permisos;
            
            return response()->json([
                'status' => 200,
                'data' => $permisos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 404,
                'error' => 'Rol no encontrado',
            ], 404);
        }
    }

    /**
 * @OA\Post(
 *     path="/api/roles/{id}/permisos",
 *     summary="Sincronizar permisos de un rol",
 *     tags={"Roles"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"permisos"},
 *             @OA\Property(property="permisos", type="array", @OA\Items(type="integer"))
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Permisos actualizados correctamente"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Rol no encontrado"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error al actualizar permisos"
 *     )
 * )
 */
    public function syncPermisos(Request $request, $id)
    {
        try {
            $rol = Rol::findOrFail($id);
            
            $validatedData = $request->validate([
                'permisos' => 'required|array',
                'permisos.*' => 'exists:permisos,id_permiso',
            ]);

            $rol->permisos()->sync($validatedData['permisos']);

            return response()->json([
                'status' => 200,
                'message' => 'Permisos actualizados correctamente',
                'data' => $rol->permisos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => $e instanceof ModelNotFoundException ? 404 : 500,
                'error' => $e instanceof ModelNotFoundException ? 'Rol no encontrado' : 'Error al actualizar permisos',
                'message' => $e->getMessage()
            ], $e instanceof ModelNotFoundException ? 404 : 500);
        }
    }
}
