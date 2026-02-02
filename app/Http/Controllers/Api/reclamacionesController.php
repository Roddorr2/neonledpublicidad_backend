<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reclamacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReclamacionesController extends Controller
{
    public function get(Request $request)
    {
        $reclamaciones = Reclamacion::orderBy('id_reclamacion', 'asc')->paginate(4);
        return response()->json($reclamaciones, 200);
    }
    //  Obtener una reclamación por ID

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

    /* Guardar una reclamación */
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
