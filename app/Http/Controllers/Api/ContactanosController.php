<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactoRequest;
use App\Http\Requests\UpdateContactoEstadoRequest;
use App\Models\Contactanos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactanosController extends Controller
{
    public function get(Request $request)
    {
        $contactos = Contactanos::paginate(4);

        return response()->json($contactos, 200);
    }

    public function getById($id)
    {
        $contacto = Contactanos::find($id);

        if (! $contacto) {
            return response()->json(['error' => 'Contacto no encontrado'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $contacto,
        ], 200);
    }

    public function create(
        // Request $request
        StoreContactoRequest $request
    ) {
        /*
        $validated = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'distrito' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'tipo_reclamo' => 'required|string|max:1050',
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
        */
        $data = $request->validated();

        // La API recibe "tipo_reclamo", pero la columna en la BD se llama "detalle_reclamacion".
        $data['detalle_reclamacion'] = $data['tipo_reclamo'];
        unset($data['tipo_reclamo']);

        $data['estado']     = 0; // Estado inicial (pendiente)
        $data['fecha_hora'] = Carbon::now();

        Contactanos::create($data);

        return response()->json([
            'status'  => 201,
            'message' => 'Contacto guardado exitosamente',
        ], 201);
    }

    public function update(
        // Request $request,
        UpdateContactoEstadoRequest $request,
        $id
    ) {
        $contacto = Contactanos::find($id);

        if (! $contacto) {
            return response()->json(['error' => 'Contacto no encontrado'], 404);
        }

        /*
        $validated = $request->validate([
            'estado' => 'required|boolean',
        ]);
        */

        $request->validated();

        $contacto->update([
            'estado'                   => $request->estado,
            'fecha_hora_actualizacion' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Estado actualizado exitosamente',
            'data'    => $contacto,
        ], 200);
    }

    public function delete($id)
    {
        $contacto = Contactanos::find($id);

        if (! $contacto) {
            return response()->json(['error' => 'Contacto no encontrado'], 404);
        }

        $contacto->delete();

        return response()->json([
            'message' => 'Contacto eliminado exitosamente',
        ], 200);
    }
}