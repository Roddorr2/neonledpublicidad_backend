<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\CredencialesEmpleadoMail;
use App\Models\Cliente;
use App\Models\Rol;
use App\Models\User;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{

    private function createPassword(string $nombre, string $apellidos)
    {

        $apellidoIniciales = strtoupper(substr($nombre, 0, 2));
        $nombreIniciales = strtolower(substr($apellidos, 0, 2));

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);

        $password= "{$apellidoIniciales}";

        for ($i = 0; $i < 5; $i++) {
            $password .= $characters[rand(0, $charactersLength - 1)];
        }

        $password .= $nombreIniciales;

        return $password;
    }

    public function create(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                "nombre" => "required|string|max:191",
                "apellido" => "required|string|max:191",
                "email" => "required|email|unique:users|unique:clientes",
                "telefono" => "required|string|max:14",
                "distrito" => "nullable|string|max:191",
            ]);

            if($validate->fails()) {
                return response()->json([
                    "status" => 400,
                    "message" => "Error al intentar crear cliente",
                    "errors" => $validate->errors()
                ], 400);
            }

            DB::beginTransaction();

            $generatedPassword = $this->createPassword($request->nombre, $request->apellido);

            $user = \App\Models\User::create([
                "name" => $request->nombre . " " . $request->apellido,
                "email" => $request->email,
                "password" => $generatedPassword
            ]);

            $customer = Cliente::create([
                "nombre" => $request->nombre,
                "apellido" => $request->apellido,
                "email" => $request->email,
                "telefono" => $request->telefono,
                "distrito" => $request->distrito,
                "id_user" => $user->id,
                "id_rol" => Rol::where('nombre', 'cliente')->first()->id_rol
            ]);

            /**
             * Enviar correo de confirmación de creación de cuenta y contraseña
             * Se reutiliza el mail de empleados
             */
            Mail::to($user->email)->send(new CredencialesEmpleadoMail($user, $generatedPassword));
            DB::commit();

            return response()->json([
                "status" => 201,
                "message" => "Cliente creado exitosamente",
                "cliente" => $customer,
                "password"=>$generatedPassword//borrar ;v
            ], 201);

        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json([
                "status" => 500,
                "message" => "Error al crear cliente",
                "error" => $e->getMessage()
            ], 500);
        }
    }
    
    public function getById($id)
    {
        $validate = Validator::make(["id" => $id], [
            "id" => "required|numeric",
        ]);

        if ($validate->fails()) {
            return response()->json(["status" => 422, "message" => "Error de validación", "Errors" => $validate->errors()]);
        }

        $cliente = Cliente::with('rol')->find($id);

        if (!$cliente) {
            return response()->json(["status" => 404, "message" => "Cliente no encontrado"]);
        }

        return response()->json([
            "status" => 200,
            "data" => $cliente
        ]);
    }

    public function getAllByPage(Request $request)
    {
        try {
            $clientes = Cliente::with('rol')
            ->withCount('propuestas')
            ->orderBy('id', 'asc')
            ->paginate(5);
            $clientes->getCollection()->transform(function ($cliente) {
                return [
                    'id_cliente' => $cliente->id,
                    'nombre' => $cliente->nombre,
                    'apellido' => $cliente->apellido,
                    'email' => $cliente->email,
                    'telefono' => $cliente->telefono,
                    'rol' => $cliente->rol->nombre,
                    'distrito' => $cliente->distrito,
                    'propuestas' => $cliente->propuestas_count
                ];
            });

            return response()->json([
                "status" => 200,
                'data' => $clientes->items(),
                'total' => $clientes->total(),
                'page' => $clientes->currentPage()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Error interno del servidor",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    

    public function update(Request $request, $id)
    {
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
    }


    public function updateProfileImage(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'public_id' => 'required|string',
            'secure_url' => 'required|url'
        ]);

        if ($validate->fails()) {
            return response()->json([
                "status" => 422,
                "message" => "Error de validación",
                "errors" => $validate->errors()
            ], 422);
        }

        try {
            $cliente = Cliente::where('id', $id)->first();
            if (!$cliente) {
                return response()->json([
                    "status" => 404,
                    "message" => "Cliente no encontrado"
                ], 404);
            }

            DB::beginTransaction();

            if ($cliente->imagen_perfil) {
                try {

                    $cloudinary = new Cloudinary();

                    $result = $cloudinary->uploadApi()->destroy($cliente->imagen_perfil);

                } catch (\Exception $e) {
                    Log::warning("Error al eliminar imagen anterior, continuando con actualización: " . $e->getMessage());
                }
            }

            $cliente->imagen_perfil_url = null;
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
    }


    public function updatePass(Request $request, $id)
    {
        $validate = Validator::make(["id" => $id], [
            "id" => "required|numeric",
        ]);

        if ($validate->fails()) {
            return response()->json(["status" => 422, "message" => "Error de validación", "Errors" => $validate->errors()]);
        }

        $cliente = Cliente::where('id', $id)->first();

        if (!$cliente) {
            return response()->json(["status" => 404, "message" => "Cliente no encontrado"]);
        }

        $userId = $cliente->id_user;

        return $this->updatePass1($request, $userId);
    }

    private function updatePass1(Request $request, $id)
    {
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
    }

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



    public function delete($id)
    {
        $validate = Validator::make(["id" => $id], [
            "id" => "required|numeric",
        ]);

        if ($validate->fails()) {
            Log::error("Validación fallida: ", $validate->errors()->toArray());
            return response()->json([
                "status" => 422,
                "message" => "Error de validación",
                "errors" => $validate->errors()
            ], 422);
        }

       $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json([
                "status" => 404,
                "message" => "Cliente no encontrado"
            ], 404);
        }


        $user = User::find($cliente->id_user);
        if ($user) {
            Log::info("Eliminando usuario vinculado con ID: " . $user->id);
            $user->delete();
        }

        Log::info("Eliminando cliente con ID: $id");
        $cliente->delete();

        Log::info("Cliente eliminado correctamente");
        return response()->json([
            "status" => 200,
            "message" => "Cliente eliminado correctamente"
        ], 200);
    }

    public function deleteProfileImage($id)
    {
        try {
            $cliente = Cliente::find($id);

            if (!$cliente) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            if ($cliente->imagen_perfil) {
                Log::info('Intentando eliminar imagen de perfil:', ['public_id' => $cliente->imagen_perfil]);

                try {
                    $cloudinary = new Cloudinary();

                    $result = $cloudinary->uploadApi()->destroy($cliente->imagen_perfil);
                    Log::info('Resultado de eliminación:', ['result' => $result]);
                } catch (\Exception $e) {
                    Log::warning("Error al eliminar imagen de Cloudinary: " . $e->getMessage());
                    // Continuamos con la actualización en la base de datos
                }

                $cliente->imagen_perfil = null;
                $cliente->imagen_perfil_url = null;
                $cliente->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Imagen eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error("Error eliminando imagen de perfil: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Error al eliminar la imagen',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}