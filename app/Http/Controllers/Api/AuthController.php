<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Empleado;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPassword;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;


/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Endpoints para autenticación y gestión de usuarios"
 * )
 */
class AuthController extends Controller
{
        /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Registrar nuevo usuario y empleado",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre","apellido","email","dni","id_rol"},
     *             @OA\Property(property="nombre", type="string", example="Juan"),
     *             @OA\Property(property="apellido", type="string", example="Pérez"),
     *             @OA\Property(property="email", type="string", format="email", example="juan@example.com"),
     *             @OA\Property(property="dni", type="string", example="12345678"),
     *             @OA\Property(property="telefono", type="string", example="123456789"),
     *             @OA\Property(property="id_rol", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario registrado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Datos inválidos"
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:empleados|unique:users',
            'dni' => 'required|string|max:20|unique:empleados',
            'telefono' => 'nullable|string|max:20',
            'id_rol' => 'required|exists:roles,id_rol',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // crea usuario
            $user = User::create([
                'name' => $request->nombre . ' ' . $request->apellido,
                'email' => $request->email,
                'password' => Hash::make('1234'),
            ]);

            // crea empleado
            $empleado = Empleado::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'email' => $request->email,
                'dni' => $request->dni,
                'telefono' => $request->telefono,
                'id_user' => $user->id,
                'id_rol' => $request->id_rol,
            ]);

            DB::commit();

            // token incluyendo rol
            $rol = Rol::find($request->id_rol);
            $abilities = [$rol->nombre]; // capacidad del token => rol

            $token = $user->createToken('auth_token', $abilities)->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario registrado exitosamente',
                'user' => $user,
                'empleado' => $empleado,
                'rol' => $rol->nombre,
                'token' => $token,
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }


        /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Iniciar sesión",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="juan@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="1234")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inicio de sesión exitoso"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales incorrectas"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Esta cuenta no está registrada en Neon Led Publicidad.'
            ], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El email o la contraseña son incorrectos.'
            ], 401);
        }


        $empleado = $user->empleado;
        if (!$empleado || !$empleado->rol) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El usuario no tiene un rol asignado'
            ], 403);
        }

        $rol = $empleado->rol;
        $abilities = [$rol->nombre];

        $permisos = $rol->permisos->pluck('slug')->toArray();

        // quitar tokens anteriores
        $user->tokens()->delete();
        // token incluyendo rol (capcidad)
        $token = $user->createToken('auth_token', $abilities)->plainTextToken;

        return response()->json([
            'status'   => 'success',
            'user'     => $user,
            'empleado' => $empleado,
            'rol'      => $rol->nombre,
            'permisos' => $permisos,
            'token'    => $token,
        ]);
    }


            /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Cerrar sesión",
     *     tags={"Auth"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Sesión cerrada exitosamente"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

        /**
     * @OA\Post(
     *     path="/api/forgot-password",
     *     summary="Solicitar restablecimiento de contraseña",
     *     description="Envía un correo con un token para restablecer la contraseña.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token de restablecimiento enviado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado"
     *     )
     * )
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'El usuario no existe'
            ], 404);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => $token,
                'created_at' => now()
            ]
        );

        Mail::to($user->email)->send(new ForgotPassword($user, $token));

        return response()->json([
            'status' => 'success',
            'message' => 'Token de restablecimiento de contraseña enviado'
        ]);
    }

        /**
     * @OA\Post(
     *     path="/api/update-password",
     *     summary="Actualizar contraseña",
     *     description="Permite restablecer la contraseña usando un token enviado por correo.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "password", "password_confirmation"},
     *             @OA\Property(property="token", type="string", example="token123"),
     *             @OA\Property(property="password", type="string", format="password", example="newPassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newPassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contraseña actualizada correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Token inválido o usuario no encontrado"
     *     )
     * )
     */
    public function updatePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|min:6|confirmed',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }
    
        Log::info('Token recibido: ' . $request->token);
        
        $tokenUser = DB::table('password_reset_tokens')
            ->whereRaw('LOWER(token) = ?', [strtolower($request->token)])
            ->first();
    
        if (!$tokenUser) {
            $exactToken = DB::table('password_reset_tokens')
                ->where('token', $request->token)
                ->first();
                
            Log::info('Token no encontrado. Tokens disponibles: ' . 
                json_encode(DB::table('password_reset_tokens')->pluck('token')->toArray()));
                
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido o expirado'
            ], 404);
        }
    
        $user = User::where('email', $tokenUser->email)->first();
    
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no encontrado'
            ], 404);
        }
    
        $user->password = Hash::make($request->password);
        $user->save();
    
        DB::table('password_reset_tokens')->where('token', $request->token)->delete();
    
        return response()->json(['message' => 'Contraseña actualizada correctamente, ingresa desde el login'], 200);
    }

        /**
     * @OA\Get(
     *     path="/api/me",
     *     summary="Obtener información del usuario autenticado",
     *     description="Retorna información del usuario logueado, su empleado, rol y permisos.",
     *     tags={"Auth"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Datos del usuario autenticado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $empleado = $user->empleado;
        $rol = $empleado ? $empleado->rol : null;

        $permisos = $rol ? $rol->permisos->pluck('slug')->toArray() : [];

        return response()->json([
            'user' => $user,
            'empleado' => $empleado,
            'rol' => $rol ? $rol->nombre : null,
            'abilities' => $user->currentAccessToken()->abilities,
            'permisos' => $permisos
        ]);
    }
}
