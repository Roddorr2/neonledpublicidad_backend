<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'abilities'  => CheckAbilities::class,
            'ability'    => CheckForAnyAbility::class,
            'role'       => CheckRole::class,
            'permission' => CheckPermission::class,
        ]);
        $middleware->statefulApi();

        // Esta API no tiene ruta 'login' (no hay vistas web). Sin esto, el
        // middleware "auth" intenta route('login') cuando una petición no
        // autenticada no pide JSON explícitamente, lanza RouteNotFoundException
        // sin capturar y termina en un 500 con página HTML en vez de un 401 JSON.
        $middleware->redirectGuestsTo(fn () => null);

        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(Illuminate\Http\Middleware\HandleCors::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, $request) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        });

        $exceptions->render(function (ThrottleRequestsException $e, $request) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Demasiados intentos fallidos. Intenta más tarde.',
            ], 429);
        });

        // Garantiza respuestas JSON para TODAS las rutas /api/*, sin importar el
        // header Accept que envíe el cliente (fetch() no siempre manda
        // "Accept: application/json", y sin esto Laravel/Ignition devolvían HTML).
        $exceptions->render(function (Throwable $e, $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'status'  => 422,
                    'message' => 'Los datos enviados no son válidos.',
                    'errors'  => $e->errors(),
                ], 422);
            }

            if ($e instanceof PostTooLargeException) {
                return response()->json([
                    'status'  => 413,
                    'message' => 'La solicitud (archivos incluidos) supera el tamaño máximo permitido por el servidor.',
                ], 413);
            }

            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Recurso no encontrado.',
                ], 404);
            }

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();

                return response()->json([
                    'status'  => $status,
                    'message' => $e->getMessage() ?: 'Error en la solicitud.',
                ], $status);
            }

            // Error realmente inesperado: se loguea con traza completa y se
            // responde 500 en JSON (nunca la página HTML de depuración).
            Log::error('Error no controlado en API: ' . $e->getMessage(), [
                'exception' => $e,
                'url'       => $request->fullUrl(),
            ]);

            return response()->json([
                'status'  => 500,
                'message' => config('app.debug') ? $e->getMessage() : 'Ha ocurrido un error interno en el servidor.',
            ], 500);
        });
    })->create();
