<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reclamacion\StoreReclamacionRequest;
use App\Http\Requests\Reclamacion\UpdateReclamacionRequest;
use App\Models\Reclamacion;

class ReclamacionesController extends Controller
{
    public function get()
    {
        $reclamaciones = Reclamacion::orderBy('id_reclamacion', 'asc')->paginate(4);
        return response()->json($reclamaciones, 200);
    }

    public function getById($id)
    {
        $reclamacion = Reclamacion::find($id);

        if (! $reclamacion) {
            return response()->json(['error' => 'Reclamación no encontrada'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $reclamacion,
        ], 200);
    }

    public function create(StoreReclamacionRequest $request)
    {
        $datos                         = $request->validated();
        $datos['fechaReclamo']         = now();
        $datos['estadoReclamo']        = 'PENDIENTE';

        $reclamacion = Reclamacion::create($datos);

        return response()->json([
            'message' => 'Reclamación guardada exitosamente',
            'data'    => $reclamacion,
        ], 201);
    }

    public function update(UpdateReclamacionRequest $request, $id)
    {
        $reclamacion = Reclamacion::find($id);

        if (! $reclamacion) {
            return response()->json(['error' => 'Reclamación no encontrada'], 404);
        }

        $reclamacion->update($request->validated());

        return response()->json([
            'message' => 'Estado actualizado exitosamente',
            'data'    => $reclamacion,
        ], 200);
    }

    public function delete($id)
    {
        $reclamacion = Reclamacion::find($id);

        if (! $reclamacion) {
            return response()->json(['error' => 'Reclamación no encontrada'], 404);
        }

        $reclamacion->delete();

        return response()->json(['message' => 'Reclamación eliminada exitosamente'], 200);
    }
}