<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\CredencialesEmpleadoMail;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    private function createPassword(string $dni, string $nombre, string $apellidos)
    {

        $apellidoIniciales = strtoupper(substr($nombre, 0, 2));
        $nombreIniciales = strtolower(substr($apellidos, 0, 2));
        $dniParte = substr($dni, -3);

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);

        $password= "{$apellidoIniciales}{$dniParte}";

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
                "dni" => "required|string|unique:users|unique:clientes|max:8",
                "telefono" => "required|string|max:",
                "id_rol" => "required|exists:roles,id_rol",
            ]);

            if($validate->fails()) {
                return response()->json([
                    "status" => 400,
                    "message" => "Error al intentar crear empleado",
                    "errors" => $validate->errors()
                ], 400);
            }

            DB::beginTransaction();

            $generatedPassword = $this->createPassword($request->dni, $request->nombre, $request->apellido);

            $user = \App\Models\User::create([
                "name" => $request->nombre . " " . $request->apellido,
                "email" => $request->email,
                "password" => $generatedPassword
            ]);

            $customer = Client::create([
                "nombre" => $request->nombre,
                "apellido" => $request->apellido,
                "email" => $request->email,
                "dni" => $request->dni,
                "telefono" => $request->telefono,
                "id_user" => $user->id,
                "id_rol" => $request->id_rol,
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
                "cliente" => $customer
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
}
