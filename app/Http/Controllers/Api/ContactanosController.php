<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contactanos;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;






 /**
 * @OA\Tag(
 *     name="Contactanos",
 *     description="Operaciones relacionadas con los contactos de reclamos"
 * )
 */
class ContactanosController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/contactanos",
     *     summary="Obtener lista paginada de contactos",
     *     tags={"Contactanos"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de contactos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Contactanos")
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function get(Request $request)
    {
        $contactos = Contactanos::paginate(4);
        return response()->json($contactos, 200);
    }
    /**
     * @OA\Get(
     *     path="/api/contactanos/{id}",
     *     summary="Obtener contacto por ID",
     *     tags={"Contactanos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del contacto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Contacto encontrado",
     *       @OA\JsonContent(ref="#/components/schemas/Contactanos")
     *     ),
     *     @OA\Response(response=404, description="Contacto no encontrado")
     * )
     */
    public function getById($id)
    {
        $contacto = Contactanos::find($id);

        if (!$contacto) {
            return response()->json(['error' => 'Contacto no encontrado'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $contacto
        ], 200);
    }

    /**
 * @OA\Post(
 *     path="/api/contactanos",
 *     summary="Crear un nuevo contacto",
 *     tags={"Contactanos"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"nombre", "apellido", "telefono", "distrito", "email", "detalle_reclamacion", "mensaje"},
 *             @OA\Property(property="nombre", type="string", maxLength=255),
 *             @OA\Property(property="apellido", type="string", maxLength=255),
 *             @OA\Property(property="telefono", type="string", maxLength=20),
 *             @OA\Property(property="distrito", type="string", maxLength=255),
 *             @OA\Property(property="email", type="string", format="email", maxLength=255),
 *             @OA\Property(property="detalle_reclamacion", type="string", maxLength=1050),
 *             @OA\Property(property="mensaje", type="string", maxLength=1050)
 *         )
 *     ),
 *     @OA\Response(response=201, description="Contacto guardado exitosamente"),
 *     @OA\Response(response=400, description="Error de validación")
 * )
 */
    public function create(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'distrito' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'detalle_reclamacion' => 'required|string|max:1050',
            'mensaje' => 'required|string|max:1050',
        ]);

        if($validated->fails()){
            return response()->json(['errors' => $validated->errors()], 400);
        }

        $data = $request->all();
        $data['estado'] = 0; // Estado inicial (pendiente)
        $data['fecha_hora'] = Carbon::now();

        Contactanos::create($data);

        return response()->json([
            'status' => 201,
            'message' => 'Contacto guardado exitosamente'
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/contactanos/{id}",
     *     summary="Actualizar el estado de un contacto",
     *     tags={"Contactanos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del contacto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"estado"},
     *             @OA\Property(property="estado", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Estado actualizado exitosamente"),
     *     @OA\Response(response=404, description="Contacto no encontrado"),
     *     @OA\Response(response=400, description="Error de validación")
     * )
     */

    public function update(Request $request, $id)
    {
        $contacto = Contactanos::find($id);

        if (!$contacto) {
            return response()->json(['error' => 'Contacto no encontrado'], 404);
        }

        $validated = $request->validate([
            'estado' => 'required|boolean',
        ]);

        $contacto->update([
            'estado' => $request->estado,
            'fecha_hora_actualizacion' => Carbon::now()
        ]);

        return response()->json([
            'message' => 'Estado actualizado exitosamente',
            'data' => $contacto,
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/contactanos/{id}",
     *     summary="Eliminar un contacto",
     *     tags={"Contactanos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del contacto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Contacto eliminado exitosamente"),
     *     @OA\Response(response=404, description="Contacto no encontrado")
     * )
     */
    public function delete($id)
    {
        $contacto = Contactanos::find($id);

        if (!$contacto) {
            return response()->json(['error' => 'Contacto no encontrado'], 404);
        }

        $contacto->delete();

        return response()->json([
            'message' => 'Contacto eliminado exitosamente'
        ], 200);
    }
}
