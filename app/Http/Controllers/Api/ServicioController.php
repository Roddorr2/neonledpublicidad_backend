<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Servicios",
 *     description="Operaciones para la gestión de servicios (listar, crear, actualizar, eliminar)"
 * )
 */
class ServicioController extends Controller
{

     /**
     * @OA\Get(
     *     path="/api/servicios",
     *     summary="Listar servicios",
     *     tags={"Servicios"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de servicios paginada"
     *     )
     * )
     */
    public function get(){
        return Servicio::orderBy('id_servicio', 'desc')
                        ->paginate(20);
    }

    /**
     * @OA\Post(
     *     path="/api/servicios",
     *     summary="Crear un nuevo servicio",
     *     tags={"Servicios"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "descripcion"},
     *             @OA\Property(property="nombre", type="string", maxLength=100),
     *             @OA\Property(property="descripcion", type="string", maxLength=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Servicio creado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function create(Request $request){
        $validator = Validator::make($request->all(),[
            'nombre' => 'required|string|max:100',
            'descripcion' => 'required|string|max:200'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $servicio = Servicio::create($request->all());

        return response()->json([
            'message' => 'Servicio creado exitosamente'
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/servicios/{id}",
     *     summary="Actualizar un servicio",
     *     tags={"Servicios"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del servicio",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "descripcion"},
     *             @OA\Property(property="nombre", type="string", maxLength=100),
     *             @OA\Property(property="descripcion", type="string", maxLength=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Servicio actualizado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Servicio no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            // Validar datos
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100',
                'descripcion' => 'required|string|max:200'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buscar servicio
            $servicio = Servicio::findOrFail($id);

            if (!$servicio) {
                return response()->json([
                    'status' => false,
                    'message' => 'Servicio no encontrado'
                ], 404);
            }

            // Actualizar campos
            $servicio->nombre = $request->nombre;

            // Guardar cambios
            $servicio->save();

            return response()->json([
                'status' => true,
                'message' => 'Servicio actualizado exitosamente',
                'data' => $servicio
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al actualizar el servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/servicios/{id}",
     *     summary="Eliminar un servicio",
     *     tags={"Servicios"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del servicio",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Servicio eliminado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Servicio no encontrado"
     *     )
     * )
     */
    public function delete($id){
        $servicio = Servicio::find($id);
        if(!$servicio) {
            return response()->json([
                'message' => 'Servicio no encontrado'
            ], 404);
        }

        $servicio->delete();
        return response()->json([
            'message' => 'Servicio eliminado exitosamente'
        ]);
    }
}
