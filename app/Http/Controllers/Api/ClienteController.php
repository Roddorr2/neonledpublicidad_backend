<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileImageRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\VerifyPasswordRequest;
use App\Mail\CredencialesEmpleadoMail;
use App\Models\Cliente;
use App\Models\Rol;
use App\Models\User;
use App\Services\FileUploadService;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    private function createPassword(string $nombre, string $apellidos)
    {

        $apellidoIniciales = strtoupper(substr($nombre, 0, 2));
        $nombreIniciales   = strtolower(substr($apellidos, 0, 2));

        $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);

        $password = "{$apellidoIniciales}";

        for ($i = 0; $i < 5; $i++) {
            $password .= $characters[rand(0, $charactersLength - 1)];
        }

        $password .= $nombreIniciales;

        return $password;
    }

    public function create(
        // Request $request
        StoreClienteRequest $request
    ) {
        try {
            /*
            $validate = Validator::make($request->all(), [
                "nombre" => "required|string|max:191",
                "apellido" => "required|string|max:191",
                "email" => "required|email|unique:users|unique:clientes",
                "telefono" => "required|string|max:14",
                "distrito" => "nullable|string|max:191",
            ], [
                'email.unique' => 'El correo electrónico ya está en uso por otro cliente, porfavor ingrese otro correo',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    "status" => 400,
                    "message" => "Error al intentar crear cliente",
                    "errors" => $validate->errors()
                ], 400);
            }
            */
            $data = $request->validated();

            DB::beginTransaction();

            // $generatedPassword = $this->createPassword($request->nombre, $request->apellido);
            $generatedPassword = $this->createPassword($data['nombre'], $data['apellido']);

            $user = User::create([
                'name'     => $data['nombre'] . ' ' . $data['apellido'],
                'email'    => $data['email'],
                'password' => $generatedPassword,
            ]);

            $rol = Rol::where('nombre', 'cliente')->firstOrFail();

            $customer = Cliente::create([
                'nombre'   => $data['nombre'],
                'apellido' => $data['apellido'],
                'email'    => $data['email'],
                'telefono' => $data['telefono'],
                'distrito' => $data['distrito'] ?? null,
                'id_user'  => $user->id,
                // "id_rol" => Rol::where('nombre', 'cliente')->first()->id_rol
                'id_rol' => $rol->id_rol,
            ]);

            /**
             * Enviar correo de confirmación de creación de cuenta y contraseña
             * Se reutiliza el mail de empleados
             */
            Mail::to($user->email)->send(new CredencialesEmpleadoMail($user, $generatedPassword));
            DB::commit();

            return response()->json([
                'status'  => 201,
                'message' => 'Cliente creado exitosamente',
                'cliente' => $customer,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Error al crear cliente',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getById($id)
    {
        $validate = Validator::make(['id' => $id], [
            'id' => 'required|numeric',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => 422, 'message' => 'Error de validación', 'Errors' => $validate->errors()]);
        }

        $cliente = Cliente::with('rol')->find($id);

        if (! $cliente) {
            return response()->json(['status' => 404, 'message' => 'Cliente no encontrado']);
        }

        return response()->json([
            'status' => 200,
            'data'   => $cliente,
        ]);
    }

    public function getAllByPage(Request $request)
    {
        try {
            $searchTerm = $request->input('search');

            $query = Cliente::with('rol')
                ->withCount('propuestas')
                ->orderBy('id', 'asc');

            if ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('nombre', 'like', "%$searchTerm%")
                        ->orWhere('apellido', 'like', "%$searchTerm%")
                        ->orWhereRaw("CONCAT(nombre, ' ', apellido) like ?", ["%$searchTerm%"]);
                });
            }

            if ($request->has('all') && $request->input('all') === 'true') {
                $clientes = $query->get();
            } else {
                $clientes = $query->paginate(5);
            }

            $clientesData = $clientes->map(function ($cliente) {
                return [
                    'id_cliente' => $cliente->id,
                    'nombre'     => $cliente->nombre,
                    'apellido'   => $cliente->apellido,
                    'email'      => $cliente->email,
                    'telefono'   => $cliente->telefono,
                    'rol'        => $cliente->rol->nombre,
                    'distrito'   => $cliente->distrito,
                    'propuestas' => $cliente->propuestas_count,
                ];
            });

            return response()->json([
                'status' => 200,
                'data'   => $clientesData,
                'total'  => $request->has('all') ? count($clientesData) : $clientes->total(),
                'page'   => $request->has('all') ? 1 : $clientes->currentPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Error interno del servidor',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(
        // Request $request,
        UpdateClienteRequest $request,
        $id
    ) {
        /*
        $validate = Validator::make(["id" => $id], [
            "id" => "required|numeric",
        ]);

        if ($validate->fails()) {
            return response()->json([
                "status" => 422,
                "message" => "Error de validación",
                "Errors" => $validate->errors()
            ]);
        }

        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json([
                "status" => 404,
                "message" => "Cliente no encontrado"
            ]);
        }

        $validator = Validator::make($request->all(), [
            'nombre'    => 'sometimes|string|max:255',
            'apellido'  => 'sometimes|string|max:255',
            "email" => "sometimes|email|unique:users|unique:clientes",
            'telefono'  => 'nullable|string|max:14',
            'distrito'  => 'nullable|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($cliente->id_user);

        if ($user) {
            if ($request->has('email') && $request->email != $cliente->email) {
                $user->email = $request->email;
            }

            if (
                ($request->has('nombre') && $request->nombre != $cliente->nombre) ||
                ($request->has('apellido') && $request->apellido != $cliente->apellido)
            ) {
                $nombre   = $request->has('nombre') ? $request->nombre : $cliente->nombre;
                $apellido = $request->has('apellido') ? $request->apellido : $cliente->apellido;
                $user->name = $nombre . ' ' . $apellido;
            }

            $user->save();
        }

        $cliente->update($request->all());

        return response()->json([
            "status"  => 200,
            "message" => "Cliente actualizado correctamente",
            "data"    => $cliente
        ]);
        */
        $data = $request->validated();

        $cliente = Cliente::findOrFail($id);
        $user    = User::find($cliente->id_user);

        if ($user) {
            if (isset($data['email'])) {
                $user->email = $data['email'];
            }

            if (isset($data['nombre']) || isset($data['apellido'])) {
                $nombre     = $data['nombre'] ?? $cliente->nombre;
                $apellido   = $data['apellido'] ?? $cliente->apellido;
                $user->name = $nombre . ' ' . $apellido;
            }

            $user->save();
        }

        $cliente->update($data);

        return response()->json([
            'status'  => 200,
            'message' => 'Cliente actualizado correctamente',
            'data'    => $cliente,
        ]);
    }

    public function updateProfileImage(
        // Request $request,
        UpdateProfileImageRequest $request,
        $id
    ) {
        /*
        // Support multipart upload via server-side FileUploadService or client-provided public_id
        try {
            $cliente = Cliente::where('id', $id)->first();
            if (!$cliente) {
                return response()->json([
                    "status" => 404,
                    "message" => "Cliente no encontrado"
                ], 404);
            }

            DB::beginTransaction();

            if ($request->hasFile('imagen')) {
                $archivo = $request->file('imagen');
                $uploader = new FileUploadService();
                $ext = $archivo->getClientOriginalExtension() ?: 'jpg';
                $filename = "cliente_{$id}_" . time() . ".{$ext}";

                $res = $uploader->subir($archivo, 'clientes', $cliente->imagen_perfil, $cliente->imagen_perfil_url, [
                    'delete_previous_cloud' => true,
                    'delete_previous_local' => true,
                    'filename' => $filename,
                ]);

                if (empty($res['url'])) {
                    DB::rollBack();
                    return response()->json(["status" => 500, "message" => "Fallo al subir la imagen"], 500);
                }

                $cliente->imagen_perfil = $res['public_id'] ?? $cliente->imagen_perfil;
                $cliente->imagen_perfil_url = $res['url'];
                $cliente->save();

                DB::commit();

                return response()->json([
                    "status" => 200,
                    "message" => "Imagen actualizada correctamente",
                    "data" => [
                        'public_id' => $cliente->imagen_perfil,
                        'url' => $cliente->imagen_perfil_url,
                        'version' => time()
                    ]
                ]);
            }

            // Backwards-compatible: client sends public_id + secure_url
            $validate = Validator::make($request->all(), [
                'public_id' => 'required|string',
                'secure_url' => 'required|url'
            ]);

            if ($validate->fails()) {
                DB::rollBack();
                return response()->json([
                    "status" => 422,
                    "message" => "Error de validación",
                    "errors" => $validate->errors()
                ], 422);
            }

            // Attempt to remove previous cloud image via centralized service
            $uploader = new FileUploadService();
            if ($cliente->imagen_perfil) {
                $uploader->eliminarPublicId($cliente->imagen_perfil);
            }

            $cliente->imagen_perfil = $request->public_id;
            $cliente->imagen_perfil_url = $request->secure_url;
            $cliente->save();

            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "Imagen actualizada correctamente",
                "data" => [
                    'public_id' => $cliente->imagen_perfil,
                    'url' => $cliente->imagen_perfil_url,
                    'version' => time()
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error actualizando imagen: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                "status" => 500,
                "message" => "Error al actualizar la imagen",
                "error" => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
        */

        try {

            $cliente = Cliente::where('id', $id)->first();
            if (! $cliente) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Cliente no encontrado',
                ], 404);
            }

            $data = DB::transaction(function () use ($cliente, $request) {

                $uploader = new FileUploadService;

                if ($request->hasFile('imagen')) {

                    $archivo  = $request->file('imagen');
                    $ext      = $archivo->getClientOriginalExtension() ?: 'jpg';
                    $filename = "cliente_{$cliente->id}_" . time() . ".{$ext}";

                    $res = $uploader->subir(
                        $archivo,
                        'clientes',
                        $cliente->imagen_perfil,
                        $cliente->imagen_perfil_url,
                        [
                            'delete_previous_cloud' => true,
                            'delete_previous_local' => true,
                            'filename'              => $filename,
                        ]
                    );

                    if (empty($res['url'])) {
                        throw new \Exception('Fallo al subir la imagen');
                    }

                    $cliente->imagen_perfil     = $res['public_id'] ?? $cliente->imagen_perfil;
                    $cliente->imagen_perfil_url = $res['url'];
                } else {

                    if ($cliente->imagen_perfil) {
                        $uploader->eliminarPublicId($cliente->imagen_perfil);
                    }

                    $cliente->imagen_perfil     = $request->public_id;
                    $cliente->imagen_perfil_url = $request->secure_url;
                }

                $cliente->save();

                return [
                    'public_id' => $cliente->imagen_perfil,
                    'url'       => $cliente->imagen_perfil_url,
                    'version'   => time(),
                ];
            });

            return response()->json([
                'status'  => 200,
                'message' => 'Imagen actualizada correctamente',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando imagen: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status'  => 500,
                'message' => 'Error al actualizar la imagen',
                'error'   => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }

    public function updatePass(Request $request, $id)
    {
        $validate = Validator::make(['id' => $id], [
            'id' => 'required|numeric',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => 422, 'message' => 'Error de validación', 'Errors' => $validate->errors()]);
        }

        $cliente = Cliente::where('id', $id)->first();

        if (! $cliente) {
            return response()->json(['status' => 404, 'message' => 'Cliente no encontrado']);
        }

        $userId = $cliente->id_user;

        return $this->updatePass1($request, $userId);
    }

    private function updatePass1(
        // Request $request,
        UpdatePasswordRequest $request,
        $id
    ) {
        /*
        $validate = Validator::make(["id" => $request->id], [
            "id" => "required|numeric",
        ]);

        if ($validate->fails()) {
            return response()->json(["status" => 422, "message" => "Error de validación", "Errors" => $validate->errors()]);
        }

        $validate = Validator::make($request->all(), [
            "password" => "required|string|min:4",
        ]);

        if ($validate->fails()) {
            return response()->json(["status" => 422, "message" => "Error de validación", "Errors" => $validate->errors(), "data" => $request->all()]);
        }

        $response = User::where(["id" => intval($id)])->update(["password" => Hash::make($request->password)]);

        if ($response) {
            return response()->json(["status" => 200, "message" => "Registro actualizado correctamente"]);
        }
        */

        /*
        $user = User::findOrFail($id);

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            "status" => 200,
            "message" => "Registro actualizado correctamente"
        ]);
        */

        $data = $request->validated();

        $user = User::findOrFail($id);

        $user->password = Hash::make($data['password']);
        $user->save();

        return response()->json([
            'status'  => 200,
            'message' => 'Registro actualizado correctamente',
        ]);
    }

    /*
    public function verifyPassword(Request $request)
    {
        try {
            $request->validate([
                'currentPassword' => 'required',
                'id' => 'required|exists:clientes'
            ]);

            $cliente = Cliente::with('user')->findOrFail($request->id);

            if (!$cliente->user) {
                return response()->json([
                    'valid' => false,
                    'message' => 'No se encontró el usuario asociado al empleado'
                ], 404);
            }

            if (!Hash::check($request->currentPassword, $cliente->user->password)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'La contraseña actual es incorrecta'
                ], 400);
            }

            return response()->json([
                'valid' => true,
                'message' => 'Contraseña verificada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ocurrió un error al procesar la solicitud',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    */

    public function verifyPassword(VerifyPasswordRequest $request, Cliente $cliente)
    {
        try {
            $data = $request->validated();

            if (! $cliente->user) {
                return response()->json([
                    'valid'   => false,
                    'message' => 'No se encontró el usuario asociado al cliente',
                ], 404);
            }

            if (! Hash::check($data['currentPassword'], $cliente->user->password)) {
                return response()->json([
                    'valid'   => false,
                    'message' => 'La contraseña actual es incorrecta',
                ], 400);
            }

            return response()->json([
                'valid'   => true,
                'message' => 'Contraseña verificada correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Ocurrió un error al procesar la solicitud',
                'message' => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }

    public function delete($id)
    {
        $validate = Validator::make(['id' => $id], [
            'id' => 'required|numeric',
        ]);

        if ($validate->fails()) {
            Log::error('Validación fallida: ', $validate->errors()->toArray());

            return response()->json([
                'status'  => 422,
                'message' => 'Error de validación',
                'errors'  => $validate->errors(),
            ], 422);
        }

        $cliente = Cliente::find($id);

        if (! $cliente) {
            return response()->json([
                'status'  => 404,
                'message' => 'Cliente no encontrado',
            ], 404);
        }

        DB::beginTransaction();
        try {
            $folderPath = "cliente/{$id}/";

            if (Storage::disk('public')->exists($folderPath)) {
                Log::info('Eliminando carpeta del cliente: ' . $folderPath);
                Storage::disk('public')->deleteDirectory($folderPath);
            }

            $user = User::find($cliente->id_user);
            if ($user) {
                Log::info('Eliminando usuario vinculado con ID: ' . $user->id);
                $user->delete();
            }

            Log::info("Eliminando cliente con ID: $id");
            $cliente->delete();

            DB::commit();

            Log::info('Cliente eliminado correctamente');

            return response()->json([
                'status'  => 200,
                'message' => 'Cliente eliminado correctamente',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar cliente: ' . $e->getMessage());

            return response()->json([
                'status'  => 500,
                'message' => 'Error al eliminar el cliente',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteProfileImage($id)
    {
        try {
            $cliente = Cliente::find($id);

            if (! $cliente) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Cliente no encontrado',
                ], 404);
            }

            if ($cliente->imagen_perfil) {
                Log::info('Intentando eliminar imagen de perfil:', ['public_id' => $cliente->imagen_perfil]);

                try {
                    $cloudinary = new Cloudinary;

                    $result = $cloudinary->uploadApi()->destroy($cliente->imagen_perfil);
                    Log::info('Resultado de eliminación:', ['result' => $result]);
                } catch (\Exception $e) {
                    Log::warning('Error al eliminar imagen de Cloudinary: ' . $e->getMessage());
                    // Continuamos con la actualización en la base de datos
                }

                $cliente->imagen_perfil     = null;
                $cliente->imagen_perfil_url = null;
                $cliente->save();
            }

            return response()->json([
                'status'  => 200,
                'message' => 'Imagen eliminada correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error eliminando imagen de perfil: ' . $e->getMessage(), [
                'exception' => $e,
                'trace'     => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 500,
                'message' => 'Error al eliminar la imagen',
                'error'   => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }

    public function updateProfile(
        // Request $request
        UpdateProfileRequest $request
    ) {
        /*
        try {
            // $user = auth()->user();
            $user = $request->user();
            $cliente = Cliente::where('id_user', $user->id)->first();

            if (!$cliente) {
                return response()->json([
                    "status" => 404,
                    "message" => "Cliente no encontrado"
                ], 404);
            }

            // Validación
            $validate = Validator::make($request->all(), [
                "nombre"   => "required|string|max:191",
                "apellido" => "required|string|max:191",
                "email"    => "required|email|unique:users,email," . $user->id . "|unique:clientes,email," . $cliente->id,
                "telefono" => "required|string|max:14",
                "distrito" => "nullable|string|max:191",
            ], [
                'email.unique' => 'El correo electrónico ya está en uso por otro cliente, porfavor ingrese otro correo',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    "status" => 400,
                    "message" => "Error al intentar actualizar perfil",
                    "errors" => $validate->errors()
                ], 400);
            }

            DB::beginTransaction();

            // Actualizar tabla users
            $user->update([
                "name"  => $request->nombre . " " . $request->apellido,
                "email" => $request->email
            ]);

            // Actualizar tabla clientes
            $cliente->update([
                "nombre"   => $request->nombre,
                "apellido" => $request->apellido,
                "email"    => $request->email,
                "telefono" => $request->telefono,
                "distrito" => $request->distrito
            ]);

            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "Perfil actualizado exitosamente",
                "cliente" => $cliente
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "status" => 500,
                "message" => "Error al actualizar perfil",
                "error" => $e->getMessage()
            ], 500);
        }
        */
        try {
            $user = $request->user();
            // $cliente = $user->cliente; // mejor si tienes relación
            $cliente = Cliente::where('id_user', $user->id)->first();

            if (! $cliente) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Cliente no encontrado',
                ], 404);
            }

            $data = $request->validated();

            DB::transaction(function () use ($user, $cliente, $data) {

                $user->update([
                    'name'  => $data['nombre'] . ' ' . $data['apellido'],
                    'email' => $data['email'],
                ]);

                $cliente->update([
                    'nombre'   => $data['nombre'],
                    'apellido' => $data['apellido'],
                    'email'    => $data['email'],
                    'telefono' => $data['telefono'],
                    'distrito' => $data['distrito'] ?? null,
                ]);
            });

            return response()->json([
                'status'  => 200,
                'message' => 'Perfil actualizado exitosamente',
                'cliente' => $cliente,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Error al actualizar perfil',
                'error'   => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }
}
