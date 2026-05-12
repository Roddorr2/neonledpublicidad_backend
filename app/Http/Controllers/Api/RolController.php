<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rol\StoreRolRequest;
use App\Http\Requests\Rol\UpdateRolRequest;
use App\Http\Requests\Rol\SyncPermisosRolRequest;
use App\Models\Rol;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RolController extends Controller
{
    public function index()
    {
        try {
            $roles = Rol::select('id_rol', 'nombre')->get();
            return response()->json([
                'status' => 200,
                'data'   => $roles,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error'  => 'Error al obtener roles',
            ], 500);
        }
    }

    public function store(StoreRolRequest $request)
    {
        try {
            $data = $request->validated();

            $rol = Rol::create(['nombre' => $data['nombre']]);

            if (!empty($data['permisos'])) {
                $rol->permisos()->attach($data['permisos']);
            }

            return response()->json([
                'status'  => 201,
                'message' => 'Rol creado correctamente',
                'data'    => $rol,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'error'   => 'Error al crear rol',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $rol = Rol::with('permisos')->findOrFail($id);
            return response()->json([
                'status' => 200,
                'data'   => $rol,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 404,
                'error'  => 'Rol no encontrado',
            ], 404);
        }
    }

    public function update(UpdateRolRequest $request, $id)
    {
        try {
            $rol  = Rol::findOrFail($id);
            $data = $request->validated();

            $rol->update(['nombre' => $data['nombre']]);

            if (isset($data['permisos'])) {
                $rol->permisos()->sync($data['permisos']);
            }

            return response()->json([
                'status'  => 200,
                'message' => 'Rol actualizado correctamente',
                'data'    => $rol,
            ]);
        } catch (\Exception $e) {
            $is404 = $e instanceof ModelNotFoundException;
            return response()->json([
                'status'  => $is404 ? 404 : 500,
                'error'   => $is404 ? 'Rol no encontrado' : 'Error al actualizar rol',
                'message' => $e->getMessage(),
            ], $is404 ? 404 : 500);
        }
    }

    public function destroy($id)
    {
        try {
            $rol = Rol::findOrFail($id);

            if ($rol->empleados()->count() > 0) {
                return response()->json([
                    'status' => 400,
                    'error'  => 'No se puede eliminar el rol porque tiene empleados asociados',
                ], 400);
            }

            $rol->permisos()->detach();
            $rol->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Rol eliminado correctamente',
            ]);
        } catch (\Exception $e) {
            $is404 = $e instanceof ModelNotFoundException;
            return response()->json([
                'status'  => $is404 ? 404 : 500,
                'error'   => $is404 ? 'Rol no encontrado' : 'Error al eliminar rol',
                'message' => $e->getMessage(),
            ], $is404 ? 404 : 500);
        }
    }

    public function getPermisos($id)
    {
        try {
            $rol     = Rol::findOrFail($id);
            $permisos = $rol->permisos;

            return response()->json([
                'status' => 200,
                'data'   => $permisos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 404,
                'error'  => 'Rol no encontrado',
            ], 404);
        }
    }

    public function syncPermisos(SyncPermisosRolRequest $request, $id)
    {
        try {
            $rol  = Rol::findOrFail($id);
            $data = $request->validated();

            $rol->permisos()->sync($data['permisos']);

            return response()->json([
                'status'  => 200,
                'message' => 'Permisos actualizados correctamente',
                'data'    => $rol->permisos,
            ]);
        } catch (\Exception $e) {
            $is404 = $e instanceof ModelNotFoundException;
            return response()->json([
                'status'  => $is404 ? 404 : 500,
                'error'   => $is404 ? 'Rol no encontrado' : 'Error al actualizar permisos',
                'message' => $e->getMessage(),
            ], $is404 ? 404 : 500);
        }
    }
}