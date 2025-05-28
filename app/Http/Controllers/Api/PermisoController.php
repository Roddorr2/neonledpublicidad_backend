<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *     name="Permisos",
 *     description="Operaciones relacionadas con permisos del sistema"
 * )
 */
class PermisoController extends Controller
{



     /**
     * @OA\Get(
     *     path="/api/permisos",
     *     tags={"Permisos"},
     *     summary="Listar todos los permisos",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de permisos obtenida correctamente"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener permisos"
     *     )
     * )
     */
    public function index()
    {
        try {
            $permisos = Permiso::all();
            return response()->json([
                'status' => 200,
                'data' => $permisos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Error al obtener permisos',
                'message' => $e->getMessage()
            ], 500);
        }
    }

       /**
     * @OA\Post(
     *     path="/api/permisos",
     *     tags={"Permisos"},
     *     summary="Crear un nuevo permiso",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre"},
     *             @OA\Property(property="nombre", type="string", example="Ver Usuarios"),
     *             @OA\Property(property="descripcion", type="string", example="Permite ver la lista de usuarios")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Permiso creado correctamente"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al crear permiso"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255|unique:permisos',
                'descripcion' => 'nullable|string',
            ]);

            $validatedData['slug'] = Str::slug($validatedData['nombre']);

            $permiso = Permiso::create($validatedData);

            return response()->json([
                'status' => 201,
                'message' => 'Permiso creado correctamente',
                'data' => $permiso,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Error al crear permiso',
                'message' => $e->getMessage()
            ], 500);
        }
    }


     /**
     * @OA\Get(
     *     path="/api/permisos/{id}",
     *     tags={"Permisos"},
     *     summary="Mostrar un permiso específico",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permiso obtenido correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Permiso no encontrado"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $permiso = Permiso::findOrFail($id);
            return response()->json([
                'status' => 200,
                'data' => $permiso,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 404,
                'error' => 'Permiso no encontrado',
            ], 404);
        }
    }


      /**
     * @OA\Put(
     *     path="/api/permisos/{id}",
     *     tags={"Permisos"},
     *     summary="Actualizar un permiso existente",
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
     *             @OA\Property(property="nombre", type="string", example="Editar Usuarios"),
     *             @OA\Property(property="descripcion", type="string", example="Permite editar usuarios")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permiso actualizado correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Permiso no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al actualizar permiso"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $permiso = Permiso::findOrFail($id);
            
            $validatedData = $request->validate([
                'nombre' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('permisos')->ignore($id, 'id_permiso'),
                ],
                'descripcion' => 'nullable|string',
            ]);

            if ($request->nombre !== $permiso->nombre) {
                $validatedData['slug'] = Str::slug($validatedData['nombre']);
            }

            $permiso->update($validatedData);

            return response()->json([
                'status' => 200,
                'message' => 'Permiso actualizado correctamente',
                'data' => $permiso,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => $e instanceof ModelNotFoundException ? 404 : 500,
                'error' => $e instanceof ModelNotFoundException ? 'Permiso no encontrado' : 'Error al actualizar permiso',
                'message' => $e->getMessage()
            ], $e instanceof ModelNotFoundException ? 404 : 500);
        }
    }


      /**
     * @OA\Delete(
     *     path="/api/permisos/{id}",
     *     tags={"Permisos"},
     *     summary="Eliminar un permiso",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permiso eliminado correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Permiso no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al eliminar permiso"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $permiso = Permiso::findOrFail($id);
            $permiso->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Permiso eliminado correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => $e instanceof ModelNotFoundException ? 404 : 500,
                'error' => $e instanceof ModelNotFoundException ? 'Permiso no encontrado' : 'Error al eliminar permiso',
                'message' => $e->getMessage()
            ], $e instanceof ModelNotFoundException ? 404 : 500);
        }
    }

}
