<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permiso\StorePermisoRequest;
use App\Http\Requests\Permiso\UpdatePermisoRequest;
use App\Models\Permiso;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class PermisoController extends Controller
{
    public function index()
    {
        try {
            $permisos = Permiso::all();
            return response()->json([
                'status' => 200,
                'data'   => $permisos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'error'   => 'Error al obtener permisos',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StorePermisoRequest $request)
    {
        try {
            $data         = $request->validated();
            $data['slug'] = Str::slug($data['nombre']);

            $permiso = Permiso::create($data);

            return response()->json([
                'status'  => 201,
                'message' => 'Permiso creado correctamente',
                'data'    => $permiso,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'error'   => 'Error al crear permiso',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $permiso = Permiso::findOrFail($id);
            return response()->json([
                'status' => 200,
                'data'   => $permiso,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 404,
                'error'  => 'Permiso no encontrado',
            ], 404);
        }
    }

    public function update(UpdatePermisoRequest $request, $id)
    {
        try {
            $permiso = Permiso::findOrFail($id);

            $data = $request->validated();

            if ($data['nombre'] !== $permiso->nombre) {
                $data['slug'] = Str::slug($data['nombre']);
            }

            $permiso->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Permiso actualizado correctamente',
                'data'    => $permiso,
            ]);
        } catch (\Exception $e) {
            $is404 = $e instanceof ModelNotFoundException;
            return response()->json([
                'status'  => $is404 ? 404 : 500,
                'error'   => $is404 ? 'Permiso no encontrado' : 'Error al actualizar permiso',
                'message' => $e->getMessage(),
            ], $is404 ? 404 : 500);
        }
    }

    public function destroy($id)
    {
        try {
            $permiso = Permiso::findOrFail($id);
            $permiso->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Permiso eliminado correctamente',
            ]);
        } catch (\Exception $e) {
            $is404 = $e instanceof ModelNotFoundException;
            return response()->json([
                'status'  => $is404 ? 404 : 500,
                'error'   => $is404 ? 'Permiso no encontrado' : 'Error al eliminar permiso',
                'message' => $e->getMessage(),
            ], $is404 ? 404 : 500);
        }
    }
}