<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmpleadoRequest;
use App\Http\Requests\UpdateEmpleadoPasswordRequest;
use App\Http\Requests\UpdateEmpleadoProfileImageRequest;
use App\Http\Requests\UpdateEmpleadoRequest;
use App\Mail\CredencialesEmpleadoMail;
use App\Models\Empleado;
use App\Models\User;
use App\Services\FileUploadService;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmpleadoController extends Controller
{
    /**
     * Verifica si el usuario autenticado es ADMINISTRADOR
     */
    private function isAdmin(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        $empleado = Empleado::with('rol')->where('id_user', $user->id)->first();
        if (!$empleado) return false;
        
        return strtolower($empleado->rol->nombre) === 'administrador';
    }

    /**
     * Verifica si el usuario autenticado puede modificar a un empleado específico
     * Reglas:
     * - Solo ADMINISTRADOR puede modificar a CUALQUIER empleado
     * - Un empleado puede modificar SU PROPIO perfil (solo ciertos campos)
     */
    private function canModifyEmployee(int $employeeId): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // Solo administradores pueden modificar a otros
        if ($this->isAdmin()) {
            return true;
        }

        // Un empleado puede modificar su propio perfil
        $empleado = Empleado::where('id_user', $user->id)->first();
        if ($empleado && $empleado->id_empleado == $employeeId) {
            return true;
        }

        return false;
    }

    /**
     * Verifica si puede eliminar foto de perfil
     */
    private function canDeleteProfileImage(int $employeeId): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        if ($this->isAdmin()) {
            return true;
        }

        $empleado = Empleado::where('id_user', $user->id)->first();
        if ($empleado && $empleado->id_empleado == $employeeId) {
            return true;
        }

        return false;
    }

    /**
     * Empleado actualiza su propio perfil
     */
    public function updateOwnProfile(UpdateEmpleadoRequest $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status'  => 401,
                'message' => 'No autenticado',
            ], 401);
        }

        $empleado = Empleado::where('id_user', $user->id)->first();

        if (!$empleado) {
            return response()->json([
                'status'  => 404,
                'message' => 'Empleado no encontrado',
            ], 404);
        }

        $data = $request->validated();

        // Restringir qué campos puede actualizar un empleado en su propio perfil
        $allowedFields = ['nombre', 'apellido', 'telefono', 'dni'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (isset($data['email']) && $data['email'] !== $empleado->email) {
            // Verificar que el email no esté en uso
            $emailExists = Empleado::where('email', $data['email'])
                ->where('id_empleado', '!=', $empleado->id_empleado)
                ->exists();
            
            if ($emailExists) {
                return response()->json([
                    'status'  => 422,
                    'message' => 'El email ya está en uso por otro empleado',
                ], 422);
            }
            $updateData['email'] = $data['email'];
            
            // Actualizar también el email del usuario asociado
            $user->email = $data['email'];
            $user->save();
        }

        // Actualizar nombre del usuario
        $nombre = $updateData['nombre'] ?? $empleado->nombre;
        $apellido = $updateData['apellido'] ?? $empleado->apellido;
        $user->name = $nombre . ' ' . $apellido;
        $user->save();

        $empleado->update($updateData);

        return response()->json([
            'status'  => 200,
            'message' => 'Perfil actualizado correctamente',
            'data'    => $empleado->fresh('rol'),
        ]);
    }

    /**
     * Empleado actualiza su propia foto de perfil
     */
    public function updateOwnProfileImage(UpdateEmpleadoProfileImageRequest $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status'  => 401,
                'message' => 'No autenticado',
            ], 401);
        }

        $empleado = Empleado::where('id_user', $user->id)->first();

        if (!$empleado) {
            return response()->json([
                'status'  => 404,
                'message' => 'Empleado no encontrado',
            ], 404);
        }

        try {
            $data = DB::transaction(function () use ($request, $empleado) {
                $service = app(FileUploadService::class);

                if ($request->hasFile('imagen')) {
                    $archivo = $request->file('imagen');
                    $carpeta = "empleados/perfiles/{$empleado->id_empleado}";
                    $ext = $archivo->getClientOriginalExtension();
                    $filename = 'profile' . ($ext ? ".{$ext}" : '');

                    $res = $service->subir(
                        $archivo,
                        $carpeta,
                        $empleado->imagen_perfil,
                        $empleado->imagen_perfil_url,
                        [
                            'delete_previous_cloud' => true,
                            'delete_previous_local' => true,
                            'filename' => $filename,
                            'entity_id' => $empleado->id_empleado,
                        ]
                    );

                    if (empty($res['url'])) {
                        throw new \Exception('Fallo al subir la imagen');
                    }

                    $empleado->imagen_perfil = $res['public_id'] ?? null;
                    $empleado->imagen_perfil_url = $res['url'];
                    $empleado->save();

                    return [
                        'public_id' => $res['public_id'] ?? null,
                        'url' => $res['url'] ?? null,
                        'version' => time(),
                    ];
                }

                $publicId = $request->public_id;
                $secureUrl = $request->secure_url;

                if (empty($publicId)) {
                    throw new \Exception('public_id de imagen no proporcionado');
                }

                // Validar que la URL sea de Cloudinary o almacenamiento local
                if (!(str_contains($secureUrl, 'res.cloudinary.com') || str_contains($secureUrl, '/storage/'))) {
                    throw new \Exception('URL de imagen no válida');
                }

                $empleado->imagen_perfil = $publicId;
                $empleado->imagen_perfil_url = $secureUrl;
                $empleado->save();

                return [
                    'public_id' => $empleado->imagen_perfil,
                    'url' => $empleado->imagen_perfil_url,
                    'version' => time(),
                ];
            });

            return response()->json([
                'status' => 200,
                'message' => 'Imagen actualizada correctamente',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando imagen propia: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Error al actualizar la imagen',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }

    /**
     * Empleado elimina su propia foto de perfil
     */
    public function deleteOwnProfileImage()
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status'  => 401,
                'message' => 'No autenticado',
            ], 401);
        }

        $empleado = Empleado::where('id_user', $user->id)->first();

        if (!$empleado) {
            return response()->json([
                'status' => 404,
                'message' => 'Empleado no encontrado',
            ], 404);
        }

        try {
            $service = app(FileUploadService::class);

            if ($empleado->imagen_perfil) {
                try {
                    $service->eliminarPublicId($empleado->imagen_perfil);
                } catch (\Exception $e) {
                    Log::warning('Error al eliminar imagen de Cloudinary: ' . $e->getMessage());
                }
            }

            if ($empleado->imagen_perfil_url && str_contains($empleado->imagen_perfil_url, '/storage/')) {
                try {
                    $relative = str_replace(asset('storage/'), '', $empleado->imagen_perfil_url);
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($relative);
                } catch (\Exception $e) {
                    Log::warning('No se pudo eliminar archivo local: ' . $e->getMessage());
                }
            }

            $empleado->imagen_perfil = null;
            $empleado->imagen_perfil_url = null;
            $empleado->save();

            return response()->json([
                'status' => 200,
                'message' => 'Imagen eliminada correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error eliminando imagen propia: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Error al eliminar la imagen',
            ], 500);
        }
    }

    public function getById(int $id)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'status' => 403,
                'message' => 'No tienes permiso para ver empleados',
            ], 403);
        }

        $validate = Validator::make(['id' => $id], [
            'id' => 'required|numeric',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => 422, 'message' => 'Error de validación', 'Errors' => $validate->errors()]);
        }

        $empleado = Empleado::with('rol')->where('id_empleado', $id)->first();

        if (!$empleado) {
            return response()->json(['status' => 404, 'message' => 'Empleado no encontrado']);
        }

        return response()->json([
            'status' => 200,
            'data' => $empleado,
        ]);
    }

    public function getAllByPage(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'status' => 403,
                'message' => 'No tienes permiso para ver empleados',
            ], 403);
        }

        try {
            $empleados = Empleado::with('rol')->orderBy('id_empleado', 'asc')->paginate(5);
            $empleados->getCollection()->transform(function ($empleado) {
                return [
                    'id_empleado' => $empleado->id_empleado,
                    'nombre' => $empleado->nombre,
                    'apellido' => $empleado->apellido,
                    'email' => $empleado->email,
                    'dni' => $empleado->dni,
                    'telefono' => $empleado->telefono,
                    'rol' => $empleado->rol->nombre,
                ];
            });

            return response()->json([
                'status' => 200,
                'data' => $empleados->items(),
                'total' => $empleados->total(),
                'page' => $empleados->currentPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create(StoreEmpleadoRequest $request)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'status' => 403,
                'message' => 'No tienes permiso para crear empleados',
            ], 403);
        }

        try {
            $data = $request->validated();

            $result = DB::transaction(function () use ($data) {
                $password = $this->createPassword(
                    $data['dni'],
                    $data['nombre'],
                    $data['apellido']
                );

                $user = User::create([
                    'name' => $data['nombre'] . ' ' . $data['apellido'],
                    'email' => $data['email'],
                    'password' => Hash::make($password),
                ]);

                $empleado = Empleado::create([
                    'nombre' => $data['nombre'],
                    'apellido' => $data['apellido'],
                    'email' => $data['email'],
                    'dni' => $data['dni'],
                    'telefono' => $data['telefono'] ?? null,
                    'id_user' => $user->id,
                    'id_rol' => $data['id_rol'],
                ]);

                return compact('user', 'empleado', 'password');
            });

            Mail::to($result['user']->email)
                ->send(new CredencialesEmpleadoMail($result['user'], $result['password']));

            return response()->json([
                'status' => 200,
                'message' => 'Empleado creado correctamente',
                'user' => $result['user'],
                'empleado' => $result['empleado'],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al crear empleado',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }

    public function update(UpdateEmpleadoRequest $request, int $id)
    {
        if (!$this->canModifyEmployee($id)) {
            return response()->json([
                'status' => 403,
                'message' => 'No tienes permiso para modificar empleados',
            ], 403);
        }

        $empleado = Empleado::where('id_empleado', $id)->first();

        if (!$empleado) {
            return response()->json([
                'status' => 404,
                'message' => 'Empleado no encontrado',
            ]);
        }

        $data = $request->validated();

        $user = User::find($empleado->id_user);

        if ($user) {
            if (isset($data['email']) && $data['email'] !== $empleado->email) {
                $user->email = $data['email'];
            }

            if (isset($data['nombre']) || isset($data['apellido'])) {
                $nombre = $data['nombre'] ?? $empleado->nombre;
                $apellido = $data['apellido'] ?? $empleado->apellido;
                $user->name = $nombre . ' ' . $apellido;
            }

            $user->save();
        }

        $empleado->update($data);

        return response()->json([
            'status' => 200,
            'message' => 'Empleado actualizado correctamente',
            'data' => $empleado,
        ]);
    }

    public function updateProfileImage(UpdateEmpleadoProfileImageRequest $request, int $id)
    {
        if (!$this->canModifyEmployee($id)) {
            return response()->json([
                'status' => 403,
                'message' => 'No tienes permiso para modificar fotos de otros empleados',
            ], 403);
        }

        $empleado = Empleado::where('id_empleado', $id)->first();

        if (!$empleado) {
            return response()->json([
                'status' => 404,
                'message' => 'Empleado no encontrado',
            ], 404);
        }

        try {
            $data = DB::transaction(function () use ($request, $empleado, $id) {
                $service = app(FileUploadService::class);

                if ($request->hasFile('imagen')) {
                    $archivo = $request->file('imagen');
                    $carpeta = "empleados/perfiles/{$id}";
                    $ext = $archivo->getClientOriginalExtension();
                    $filename = 'profile' . ($ext ? ".{$ext}" : '');

                    $res = $service->subir(
                        $archivo,
                        $carpeta,
                        $empleado->imagen_perfil,
                        $empleado->imagen_perfil_url,
                        [
                            'delete_previous_cloud' => true,
                            'delete_previous_local' => true,
                            'filename' => $filename,
                            'entity_id' => $id,
                        ]
                    );

                    if (empty($res['url'])) {
                        throw new \Exception('Fallo al subir la imagen');
                    }

                    $empleado->imagen_perfil = $res['public_id'] ?? null;
                    $empleado->imagen_perfil_url = $res['url'];
                    $empleado->save();

                    return [
                        'public_id' => $res['public_id'] ?? null,
                        'url' => $res['url'] ?? null,
                        'version' => time(),
                    ];
                }

                $publicId = $request->public_id;
                $secureUrl = $request->secure_url;

                if (empty($publicId)) {
                    throw new \Exception('public_id de imagen no proporcionado');
                }

                // Validar que la URL sea de Cloudinary o almacenamiento local
                if (!(str_contains($secureUrl, 'res.cloudinary.com') || str_contains($secureUrl, '/storage/'))) {
                    throw new \Exception('URL de imagen no válida');
                }

                $empleado->imagen_perfil = $publicId;
                $empleado->imagen_perfil_url = $secureUrl;
                $empleado->save();

                return [
                    'public_id' => $empleado->imagen_perfil,
                    'url' => $empleado->imagen_perfil_url,
                    'version' => time(),
                ];
            });

            return response()->json([
                'status' => 200,
                'message' => 'Imagen actualizada correctamente',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando imagen: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Error al actualizar la imagen',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }

    public function updatePass(UpdateEmpleadoPasswordRequest $request, int $id)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'status' => 403,
                'message' => 'No tienes permiso para modificar contraseñas de otros empleados',
            ], 403);
        }

        $empleado = Empleado::where('id_empleado', $id)->first();

        if (!$empleado) {
            return response()->json([
                'status' => 404,
                'message' => 'Empleado no encontrado',
            ]);
        }

        $user = User::find($empleado->id_user);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Usuario no encontrado',
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'Contraseña actualizada correctamente',
        ]);
    }

    public function delete(int $id)
    {
        if (!$this->canModifyEmployee($id)) {
            return response()->json([
                'status' => 403,
                'message' => 'No tienes permiso para eliminar empleados',
            ], 403);
        }

        $empleado = Empleado::where('id_empleado', $id)->first();

        if (!$empleado) {
            return response()->json([
                'status' => 404,
                'message' => 'Empleado no encontrado',
            ], 404);
        }

        $user = User::find($empleado->id_user);
        if ($user) {
            Log::info('Eliminando usuario vinculado con ID: ' . $user->id);
            $user->delete();
        }

        Log::info("Eliminando empleado con ID: $id");
        $empleado->delete();

        Log::info('Empleado eliminado correctamente');

        return response()->json([
            'status' => 200,
            'message' => 'Empleado eliminado correctamente',
        ], 200);
    }

    public function deleteProfileImage(int $id)
    {
        if (!$this->canModifyEmployee($id)) {
            return response()->json([
                'status' => 403,
                'message' => 'No tienes permiso para eliminar fotos de otros empleados',
            ], 403);
        }

        try {
            $empleado = Empleado::where('id_empleado', $id)->first();

            if (!$empleado) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Empleado no encontrado',
                ], 404);
            }

            $service = app(FileUploadService::class);

            if ($empleado->imagen_perfil) {
                try {
                    $service->eliminarPublicId($empleado->imagen_perfil);
                } catch (\Exception $e) {
                    Log::warning('Error al eliminar imagen de Cloudinary: ' . $e->getMessage());
                }
            }

            if ($empleado->imagen_perfil_url && str_contains($empleado->imagen_perfil_url, '/storage/')) {
                try {
                    $relative = str_replace(asset('storage/'), '', $empleado->imagen_perfil_url);
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($relative);
                } catch (\Exception $e) {
                    Log::warning('No se pudo eliminar archivo local previo: ' . $e->getMessage());
                }
            }

            $empleado->imagen_perfil = null;
            $empleado->imagen_perfil_url = null;
            $empleado->save();

            return response()->json([
                'status' => 200,
                'message' => 'Imagen eliminada correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error eliminando imagen de perfil: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Error al eliminar la imagen',
            ], 500);
        }
    }

    // ==================== MÉTODOS AUXILIARES ====================

    private function createPassword(string $dni, string $nombre, string $apellidos)
    {
        $apellidoIniciales = strtoupper(substr($nombre, 0, 2));
        $nombreIniciales = strtolower(substr($apellidos, 0, 2));
        $dniParte = substr($dni, -3);

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);

        $password = "{$apellidoIniciales}{$dniParte}";

        for ($i = 0; $i < 5; $i++) {
            $password .= $characters[rand(0, $charactersLength - 1)];
        }

        $password .= $nombreIniciales;

        return $password;
    }

    public function verifyPassword(Request $request)
    {
        try {
            $empleado = Empleado::with('user')->findOrFail($request->id_empleado);

            if (!$empleado->user) {
                return response()->json([
                    'valid' => false,
                    'message' => 'No se encontró el usuario asociado al empleado',
                ], 404);
            }

            if (!Hash::check($request->currentPassword, $empleado->user->password)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'La contraseña actual es incorrecta',
                ], 400);
            }

            return response()->json([
                'valid' => true,
                'message' => 'Contraseña verificada correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ocurrió un error al procesar la solicitud',
                'message' => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }
}