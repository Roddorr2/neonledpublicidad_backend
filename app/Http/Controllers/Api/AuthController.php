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
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
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

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'turnstile_token' => 'required|string',
            ]);

            $normalizedEmail = strtolower($request->email);
            $backoffKey = 'login_backoff:' . $normalizedEmail;
            $attemptsKey = 'login_attempts:' . $normalizedEmail;

            $activeBackoffSeconds = $this->obtenerEsperaBackoffSegundos($backoffKey);
            if ($activeBackoffSeconds > 0) {
                $attemptResult = $this->registrarIntentoFallido(
                    $attemptsKey,
                    $backoffKey,
                    $request,
                    'blocked_by_backoff'
                );

                return $this->responderBloqueoBackoff(
                    $request,
                    max($activeBackoffSeconds, $attemptResult['wait_seconds'])
                );
            }

            if (!$this->validarTurnstile($request->turnstile_token, $request->ip())) {
                Log::warning('Turnstile verification failed', [
                    'email' => $request->email,
                    'ip' => $request->ip()
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Verificación de seguridad fallida. Recarga la página e inténtalo de nuevo.'
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                $attemptResult = $this->registrarIntentoFallido(
                    $attemptsKey,
                    $backoffKey,
                    $request,
                    'user_not_found'
                );

                if ($attemptResult['wait_seconds'] > 0) {
                    return $this->responderBloqueoBackoff($request, $attemptResult['wait_seconds']);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'El email o la contraseña son incorrectos.'
                ], 401);
            }

            if (!Hash::check($request->password, $user->password)) {
                $attemptResult = $this->registrarIntentoFallido(
                    $attemptsKey,
                    $backoffKey,
                    $request,
                    'wrong_password'
                );

                if ($attemptResult['wait_seconds'] > 0) {
                    return $this->responderBloqueoBackoff($request, $attemptResult['wait_seconds']);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'El email o la contraseña son incorrectos.',
                ], 401);
            }

            Cache::forget($backoffKey);
            Cache::forget($attemptsKey);

            $empleado = $user->empleado;
            $cliente = $user->cliente;

            if ($empleado && $empleado->rol) {
                $rol = $empleado->rol;
                $permisos = $rol->permisos->pluck('slug')->toArray();
                $tipo = 'empleado';
                $info = $empleado;
            } elseif ($cliente && $cliente->rol) {
                $rol = $cliente->rol;
                $permisos = $rol->permisos->pluck('slug')->toArray();
                $tipo = 'cliente';
                $info = $cliente;
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El usuario no tiene un rol asignado'
                ], 403);
            }

            $user->tokens()->delete();

            $token = $user->createToken('auth_token', [$rol->nombre])->plainTextToken;


            return response()->json([
                'status' => 'success',
                'user' => $user,
                $tipo => $info,
                'rol' => $rol->nombre,
                'permisos' => $permisos,
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error en el servidor',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    //logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

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

    public function updatePassword(Request $request)
    {
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

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'error' => 'Usuario no autenticado'
            ], 401);
        }

        // Verificar contraseña actual
        if (!Hash::check($request->currentPassword, $user->password)) {
            return response()->json([
                'error' => 'La contraseña actual es incorrecta'
            ], 400);
        }

        // Cambiar contraseña
        $user->password = Hash::make($request->newPassword);
        $user->save();

        return response()->json([
            'message' => 'Contraseña cambiada exitosamente'
        ]);
    }

    private function registrarIntentoFallido(
        string $attemptsKey,
        string $backoffKey,
        Request $request,
        string $reason
    ): array {
        if (Cache::has($attemptsKey)) {
            $attempts = Cache::increment($attemptsKey);
        } else {
            $attempts = 1;
        }

        Cache::put($attemptsKey, $attempts, now()->addMinutes(120));

        $lockoutMinutes = $this->obtenerMinutosBloqueo($attempts);
        $waitSeconds = 0;

        if ($lockoutMinutes > 0) {
            $backoffExpiry = now()->addMinutes($lockoutMinutes);
            Cache::put($backoffKey, $backoffExpiry->timestamp, $backoffExpiry);
            $waitSeconds = $this->obtenerEsperaBackoffSegundos($backoffKey);

            Log::warning('Backoff progresivo activado', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'attempts' => $attempts,
                'lockout_minutes' => $lockoutMinutes,
            ]);
        }

        Log::info('Login fallido', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'reason' => $reason,
            'accumulated_attempts' => $attempts,
        ]);

        return [
            'attempts' => $attempts,
            'wait_seconds' => $waitSeconds,
        ];
    }

    private function obtenerMinutosBloqueo(int $attempts): int
    {
        return match (true) {
            $attempts >= 15 => 60,
            $attempts >= 10 => 15,
            $attempts >= 7 => 5,
            $attempts >= 4 => 2,
            default => 0,
        };
    }

    private function obtenerEsperaBackoffSegundos(string $backoffKey): int
    {
        $rawExpiry = Cache::get($backoffKey);
        if (!$rawExpiry) {
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

    private function responderBloqueoBackoff(Request $request, int $waitSeconds): \Illuminate\Http\JsonResponse
    {
        $waitMinutes = (int) ceil($waitSeconds / 60);

        Log::warning('Login bloqueado por backoff progresivo', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'wait_minutes' => $waitMinutes,
            'retry_after_seconds' => $waitSeconds,
        ]);

        return response()->json([
            'status' => 'error',
            'message' => "Cuenta temporalmente bloqueada. Intenta de nuevo en {$waitMinutes} minuto(s).",
            'retry_after' => $waitSeconds,
        ], 429);
    }

    private function validarTurnstile(string $token, string $ip): bool
    {
        try {
            $response = Http::withoutVerifying()->asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => env('TURNSTILE_SECRET_KEY'),
                'response' => $token,
                'remoteip' => $ip,
            ]);

            $success = $response->json('success', false);

            if (!$success) {
                Log::warning('Turnstile validation failed', [
                    'ip' => $ip,
                    'error_codes' => $response->json('error-codes', [])
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Turnstile validation error', [
                'error' => $e->getMessage(),
                'ip' => $ip
            ]);
            return false;
        }
    }
}
