<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Mail\ForgotPassword;
use App\Models\Empleado;
use App\Models\Rol;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function register(
        // Request $request
        RegisterRequest $request
    ) {
        /*
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
        */
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // crear usuario
            $user = User::create([
                'name'     => $data['nombre'] . ' ' . $data['apellido'],
                'email'    => $data['email'],
                'password' => Hash::make('1234'),
            ]);

            $rol = Rol::where('nombre','cliente')->firstOrFail();

            // crear empleado
            $empleado = Empleado::create([
                'nombre'   => $data['nombre'],
                'apellido' => $data['apellido'],
                'email'    => $data['email'],
                'dni'      => $data['dni'],
                'telefono' => $data['telefono'] ?? null,
                'id_user'  => $user->id,
                'id_rol'   => $rol->id_rol,
            ]);

            DB::commit();

            $token = $user->createToken('auth_token', [$rol->nombre])->plainTextToken;

            return response()->json([
                'status'   => 'success',
                'message'  => 'Usuario registrado exitosamente',
                'user'     => $user,
                'empleado' => $empleado,
                'rol'      => $rol->nombre,
                'token'    => $token,
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al registrar usuario',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $data = $request->validated();

            // Bloqueo por IP para evitar evasión desde otros navegadores/cuentas
            $normalizedEmail = strtolower(trim($data['email']));
            $ip              = $request->ip();
            $backoffKey      = 'login_backoff:ip:' . $ip;
            $attemptsKey     = 'login_attempts:ip:' . $ip;

            // Si ya está bloqueado, no contamos nuevos intentos ni extendemos el bloqueo.
            $activeBackoffSeconds = $this->obtenerEsperaBackoffSegundos($backoffKey);
            if ($activeBackoffSeconds > 0) {
                return $this->responderBloqueoBackoff(
                    $request,
                    $activeBackoffSeconds
                );
            }

            // Turnstile es independiente del contador de credenciales.
            // Un CAPTCHA inválido no consume uno de los 5 intentos.
            if (! $this->validarTurnstile($data['turnstile_token'], $request->ip())) {
                Log::warning('Turnstile verification failed', [
                    'email' => $normalizedEmail,
                    'ip'    => $request->ip(),
                ]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Verificación de seguridad fallida. Completa nuevamente el CAPTCHA.',
                ], 422);
            }

            $user = User::with('empleado.rol.permisos', 'cliente.rol.permisos')
                ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
                ->first();

            if (! $user || ! Hash::check($data['password'], $user->password)) {
                $reason = $user ? 'wrong_password' : 'user_not_found';

                $attemptResult = $this->registrarIntentoFallido(
                    $attemptsKey,
                    $backoffKey,
                    $request,
                    $reason
                );

                // En el quinto intento el backend responde 429 y bloquea por 5 minutos.
                if ($attemptResult['wait_seconds'] > 0) {
                    return $this->responderBloqueoBackoff(
                        $request,
                        $attemptResult['wait_seconds']
                    );
                }

                return response()->json([
                    'status'             => 'error',
                    'message'            => 'El email o la contraseña son incorrectos.',
                    'remaining_attempts' => $attemptResult['remaining_attempts'],
                ], 401);
            }

            // Un inicio correcto reinicia el contador y cualquier bloqueo previo.
            Cache::forget($backoffKey);
            \Illuminate\Support\Facades\RateLimiter::clear($attemptsKey);

            $empleado = $user->empleado;
            $cliente  = $user->cliente;

            if ($empleado && $empleado->rol) {
                $rol      = $empleado->rol;
                $permisos = $rol->permisos->pluck('slug')->toArray();
                $tipo     = 'empleado';
                $info     = $empleado;
            } elseif ($cliente && $cliente->rol) {
                $rol      = $cliente->rol;
                $permisos = $rol->permisos->pluck('slug')->toArray();
                $tipo     = 'cliente';
                $info     = $cliente;
            } else {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'El usuario no tiene un rol asignado',
                ], 403);
            }

            $user->tokens()->delete();
            $token = $user->createToken('auth_token', [$rol->nombre])->plainTextToken;

            return response()->json([
                'status'   => 'success',
                'user'     => $user,
                $tipo      => $info,
                'rol'      => $rol->nombre,
                'permisos' => $permisos,
                'token'    => $token,
            ]);
        } catch (\Exception $e) {
            Log::error('Error durante el inicio de sesión', [
                'error' => $e->getMessage(),
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Ocurrió un error en el servidor',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Sesión cerrada exitosamente',
        ]);
    }

    public function forgotPassword(
        // Request $request
        ForgotPasswordRequest $request
    ) {
        /*
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
        */
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El usuario no existe',
            ], 404);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token'      => $token,
                'created_at' => now(),
            ]
        );

        Mail::to($user->email)->send(new ForgotPassword($user, $token));

        return response()->json([
            'status'  => 'success',
            'message' => 'Token de restablecimiento de contraseña enviado',
        ]);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token'    => 'required|string',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        Log::info('Token recibido: ' . $request->token);

        $tokenUser = DB::table('password_reset_tokens')
            ->whereRaw('LOWER(token) = ?', [strtolower($request->token)])
            ->first();

        if (! $tokenUser) {
            $exactToken = DB::table('password_reset_tokens')
                ->where('token', $request->token)
                ->first();

            Log::info('Token no encontrado. Tokens disponibles: ' .
                json_encode(DB::table('password_reset_tokens')->pluck('token')->toArray()));

            return response()->json([
                'status'  => 'error',
                'message' => 'Token inválido o expirado',
            ], 404);
        }

        $user = User::where('email', $tokenUser->email)->first();

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('token', $request->token)->delete();

        return response()->json(['message' => 'Contraseña actualizada correctamente, ingresa desde el login'], 200);
    }

    #[OA\Get(
        path: '/api/me',
        operationId: 'getAuthenticatedUser',
        tags: ['Auth'],
        summary: 'Obtener datos del usuario autenticado',
        description: 'Retorna los datos del usuario actualmente autenticado, incluyendo roles y permisos',
        security: [
            ['bearerAuth' => []],
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Datos del usuario autenticado',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthErrorResponse')
            ),
        ]
    )]
    public function me(Request $request)
    {
        $user     = $request->user();
        $empleado = $user->empleado;
        $rol      = $empleado ? $empleado->rol : null;

        $permisos = $rol ? $rol->permisos->pluck('slug')->toArray() : [];

        return response()->json([
            'user'      => $user,
            'empleado'  => $empleado,
            'rol'       => $rol ? $rol->nombre : null,
            'abilities' => $user->currentAccessToken()->abilities,
            'permisos'  => $permisos,
        ]);
    }

    #[OA\Post(
        path: '/api/change-password',
        operationId: 'changePassword',
        tags: ['Auth'],
        summary: 'Cambiar contraseña del usuario autenticado',
        description: 'Permite al usuario autenticado cambiar su contraseña. Requiere la contraseña actual para validar.',
        security: [
            ['bearerAuth' => []],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ChangePasswordRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Contraseña cambida exitosamente',
                content: new OA\JsonContent(ref: '#/components/schemas/ChangePasswordSuccessResponse')
            ),
            new OA\Response(
                response: 400,
                description: 'Validación fallida o contraseña actual incorrecta',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'La contraseña actual es incorrecta'),
                        new OA\Property(property: 'errors', type: 'object', example: ['currentPassword' => ['Campo requerido']]),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Datos de validación inválidos',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
        ]
    )]
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currentPassword' => 'required',
            'newPassword'     => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        if (! $user) {
            return response()->json([
                'error' => 'Usuario no autenticado',
            ], 401);
        }

        // Verificar contraseña actual
        if (! Hash::check($request->currentPassword, $user->password)) {
            return response()->json([
                'error' => 'La contraseña actual es incorrecta',
            ], 400);
        }

        // Cambiar contraseña
        $user->password = Hash::make($request->newPassword);
        $user->save();

        return response()->json([
            'message' => 'Contraseña cambiada exitosamente',
        ]);
    }

    /** Máximo de credenciales incorrectas antes del bloqueo. */
    private const MAX_INTENTOS = 5;

    /** Duración fija del bloqueo. */
    private const MINUTOS_BLOQUEO = 5;

    /** Ventana durante la cual se conservan los intentos incompletos. */
    private const MINUTOS_VENTANA_INTENTOS = 120;

    private function registrarIntentoFallido(
        string $attemptsKey,
        string $backoffKey,
        Request $request,
        string $reason
    ): array {
        \Illuminate\Support\Facades\RateLimiter::hit($attemptsKey, self::MINUTOS_VENTANA_INTENTOS * 60);
        $attempts = \Illuminate\Support\Facades\RateLimiter::attempts($attemptsKey);

        $waitSeconds = 0;

        if ($attempts >= self::MAX_INTENTOS) {
            $backoffExpiry = now()->addMinutes(self::MINUTOS_BLOQUEO);

            Cache::put(
                $backoffKey,
                $backoffExpiry->timestamp,
                $backoffExpiry
            );

            // El próximo ciclo debe empezar nuevamente desde cero.
            \Illuminate\Support\Facades\RateLimiter::clear($attemptsKey);
            $waitSeconds = $this->obtenerEsperaBackoffSegundos($backoffKey);

            Log::warning('Cuenta bloqueada por intentos fallidos', [
                'email'           => strtolower(trim((string) $request->email)),
                'ip'              => $request->ip(),
                'attempts'        => $attempts,
                'lockout_minutes' => self::MINUTOS_BLOQUEO,
            ]);
        }

        Log::info('Login fallido', [
            'email'                => strtolower(trim((string) $request->email)),
            'ip'                   => $request->ip(),
            'reason'               => $reason,
            'accumulated_attempts' => $attempts,
        ]);

        return [
            'attempts'           => $attempts,
            'wait_seconds'       => $waitSeconds,
            'remaining_attempts' => max(0, self::MAX_INTENTOS - $attempts),
        ];
    }

    private function obtenerEsperaBackoffSegundos(string $backoffKey): int
    {
        $rawExpiry = Cache::get($backoffKey);

        if (! $rawExpiry) {
            return 0;
        }

        if ($rawExpiry instanceof \DateTimeInterface) {
            $expiryTimestamp = $rawExpiry->getTimestamp();
        } elseif (is_numeric($rawExpiry)) {
            $expiryTimestamp = (int) $rawExpiry;
        } else {
            try {
                $expiryTimestamp = Carbon::parse((string) $rawExpiry)->timestamp;
            } catch (\Throwable $e) {
                Cache::forget($backoffKey);

                return 0;
            }
        }

        $waitSeconds = $expiryTimestamp - now()->timestamp;

        if ($waitSeconds <= 0) {
            Cache::forget($backoffKey);

            return 0;
        }

        return $waitSeconds;
    }

    private function responderBloqueoBackoff(
        Request $request,
        int $waitSeconds
    ): \Illuminate\Http\JsonResponse {
        $waitSeconds = max(1, $waitSeconds);
        $waitMinutes = (int) ceil($waitSeconds / 60);

        Log::warning('Intento de login durante bloqueo temporal', [
            'email'               => strtolower(trim((string) $request->email)),
            'ip'                  => $request->ip(),
            'wait_minutes'        => $waitMinutes,
            'retry_after_seconds' => $waitSeconds,
        ]);

        return response()->json([
            'status'             => 'error',
            'message'            => "Cuenta temporalmente bloqueada. Intenta de nuevo en {$waitMinutes} minuto(s).",
            'remaining_attempts' => 0,
            'retry_after'        => $waitSeconds,
            'lockout_minutes'    => self::MINUTOS_BLOQUEO,
        ], 429)->header('Retry-After', (string) $waitSeconds);
    }

    private function validarTurnstile(string $token, string $ip): bool
    {
        try {
            $response = Http::withoutVerifying()->asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret'   => env('TURNSTILE_SECRET_KEY'),
                'response' => $token,
                'remoteip' => $ip,
            ]);

            $success = $response->json('success', false);

            if (! $success) {
                Log::warning('Turnstile validation failed', [
                    'ip'          => $ip,
                    'error_codes' => $response->json('error-codes', []),
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Turnstile validation error', [
                'error' => $e->getMessage(),
                'ip'    => $ip,
            ]);

            return false;
        }
    }
}
