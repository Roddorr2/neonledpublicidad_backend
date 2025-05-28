<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reclamacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Reclamaciones",
 *     description="Operaciones relacionadas con la gestión de reclamaciones de usuarios"
 * )
 */
class ReclamacionesController extends Controller
{

     /**
     * @OA\Get(
     *     path="/api/reclamaciones",
     *     tags={"Reclamaciones"},
     *     summary="Listar todas las reclamaciones paginadas",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de reclamaciones"
     *     )
     * )
     */
    public function get(Request $request)
    {
        $reclamaciones = Reclamacion::orderBy('id_reclamacion', 'asc')->paginate(4);
        return response()->json($reclamaciones, 200);
    }


    /**
     * @OA\Get(
     *     path="/api/reclamaciones/{id}",
     *     tags={"Reclamaciones"},
     *     summary="Obtener una reclamación por ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la reclamación",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reclamación encontrada"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Reclamación no encontrada"
     *     )
     * )
     */
    public function getById($id){
        $reclamacion = Reclamacion::find($id);

        if (!$reclamacion) {
            return response()->json(['error' => 'Reclamación no encontrada'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $reclamacion
        ], 200);
    }

   /**
     * @OA\Post(
     *     path="/api/reclamaciones",
     *     tags={"Reclamaciones"},
     *     summary="Crear una nueva reclamación",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={
     *                 "nombre", "apellido", "email", "telefono", "direccion", 
     *                 "distrito", "id_servicio", "fechaIncidente", 
     *                 "descripcionServicio", "checkReclamoForm", "aceptaPoliticaPrivacidad"
     *             },
     *             @OA\Property(property="nombre", type="string", example="Carlos"),
     *             @OA\Property(property="apellido", type="string", example="Gómez"),
     *             @OA\Property(property="email", type="string", example="carlos@example.com"),
     *             @OA\Property(property="telefono", type="string", example="987654321"),
     *             @OA\Property(property="departamento", type="string", example="Lima"),
     *             @OA\Property(property="direccion", type="string", example="Av. Siempre Viva 123"),
     *             @OA\Property(property="distrito", type="string", example="Miraflores"),
     *             @OA\Property(property="id_servicio", type="integer", example=1),
     *             @OA\Property(property="fechaIncidente", type="string", format="date", example="2024-05-01"),
     *             @OA\Property(property="montoReclamado", type="number", example=150.50),
     *             @OA\Property(property="descripcionServicio", type="string", example="Servicio de instalación defectuoso"),
     *             @OA\Property(property="checkReclamoForm", type="boolean", example=true),
     *             @OA\Property(property="aceptaPoliticaPrivacidad", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Reclamación creada exitosamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación"
     *     )
     * )
     */
    public function create(Request $request)
    {
        // Validación de datos
        $validated = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'telefono' => 'required|string|max:20',
            'departamento' => 'nullable|string|max:100',
            'direccion' => 'required|string|max:250',
            'distrito' => 'required|string|max:250',
            'id_servicio' => 'required|integer',
            'fechaIncidente' => 'required|date',
            'montoReclamado' => 'nullable|numeric',
            'descripcionServicio' => 'required|string|max:1050',
            'checkReclamoForm' => 'required|boolean',
            'aceptaPoliticaPrivacidad' => 'required|boolean',
        ]);

        if($validated->fails()){
            return response()->json(['errors' => $validated->errors()], 400);
        }

        $datos = $request->all();
        $datos['fechaReclamo'] = now();
        $datos['estadoReclamo'] = 'PENDIENTE';

        $reclamacion = Reclamacion::create($datos);

        return response()->json([
            'message' => 'Reclamación guardada exitosamente',
            'data' => $reclamacion,
        ], 201);
    }

     /**
     * @OA\Put(
     *     path="/api/reclamaciones/{id}",
     *     tags={"Reclamaciones"},
     *     summary="Actualizar el estado de una reclamación",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la reclamación",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"estadoReclamo"},
     *             @OA\Property(property="estadoReclamo", type="string", enum={"PENDIENTE", "ATENDIDO"}, example="ATENDIDO")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estado de reclamación actualizado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Reclamación no encontrada"
     *     )
     * )
     */
    public function update(Request $request, $id){
        $reclamacion = Reclamacion::find($id);

        if (!$reclamacion) {
            return response()->json(['error' => 'Reclamación no encontrada'], 404);
        }

        $validated = $request->validate([
            'estadoReclamo' => 'required|in:PENDIENTE,ATENDIDO',
        ]);

        $reclamacion->update([
            'estadoReclamo' => $request->estadoReclamo,
        ]);

        return response()->json([
            'message' => 'Estado actualizado exitosamente',
            'data' => $reclamacion,
        ], 200);
    }

     /**
     * @OA\Delete(
     *     path="/api/reclamaciones/{id}",
     *     tags={"Reclamaciones"},
     *     summary="Eliminar una reclamación",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la reclamación",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reclamación eliminada exitosamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Reclamación no encontrada"
     *     )
     * )
     */
    public function delete($id)
    {
        $reclamacion = Reclamacion::find($id);

        if (!$reclamacion) {
            return response()->json(['error' => 'Reclamación no encontrada'], 404);
        }

        $reclamacion->delete();

        return response()->json(['message' => 'Reclamación eliminada exitosamente'], 200);
    }
}
