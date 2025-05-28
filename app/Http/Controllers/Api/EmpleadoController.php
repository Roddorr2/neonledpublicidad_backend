<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Cloudinary\Cloudinary;
use App\Mail\CredencialesEmpleadoMail;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Empleados",
 *     description="Controlador para la gestión de empleados, incluyendo operaciones de obtención y modificación con control de permisos basado en la autenticación y restricciones específicas por email."
 * )
 *
 * Clase EmpleadoController
 * 
 * Esta clase gestiona las operaciones CRUD (lectura y modificación) sobre empleados.
 * Incluye mecanismos de autorización que:
 * - Permiten modificaciones solo a usuarios privilegiados o a los propios empleados sobre su perfil.
 * - Restringen modificaciones a ciertos emails protegidos.
 * 
 * Además, provee endpoints seguros con validación de entrada y respuesta estructurada en JSON.
 */
class EmpleadoController extends Controller
{

    private const RESTRICTED_EMAILS = [
        "joseluisjlgd123@gmail.com",
        "keving.kpg@gmail.com",
        "tmlighting@hotmail.com"
    ];

    private const PRIVILEGED_EMAIL = "tmlighting@hotmail.com";

    
    private function hasPermissionToModify($employeeEmail, $employeeId)
    {
        $user = Auth::user();
        $authenticatedUserEmail = $user->email;
        $empleadoUsuario = Empleado::where('id_user', $user->id)->first();
        $editarMiPerfil = $empleadoUsuario && $empleadoUsuario->id_empleado == $employeeId;

        if ($authenticatedUserEmail === self::PRIVILEGED_EMAIL) {
            return true;
        }
        if ($editarMiPerfil) {
            return true;
        }
        return !in_array($employeeEmail, self::RESTRICTED_EMAILS);
    }
     
    private function checkPermissionMiddleware($id)
    {
        $empleado = Empleado::where('id_empleado', $id)->first();

        if (!$empleado) {
            return response()->json([
                "status" => 404,
                "message" => "Empleado no encontrado"
            ], 404);
        }

        if (!$this->hasPermissionToModify($empleado->email, $id)) {
            Log::warning("Intento no autorizado de modificar empleado restringido", [
                'target_id' => $id,
                'target_email' => $empleado->email,
                'user_id' => Auth::id(),
                'user_email' => Auth::user()->email
            ]);

            return response()->json([
                "status" => 403,
                "message" => "No tienes permiso para modificar este empleado"
            ], 403);
        }

        return null;
    }

        /**
     * @OA\Get(
     *     path="/empleados/{id}",
     *     summary="Obtener un empleado por su ID",
     *     tags={"Empleados"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del empleado",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Empleado encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Empleado no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function getById($id)
    {
        $validate = Validator::make(["id" => $id], [
            "id" => "required|numeric",
        ]);

        if ($validate->fails()) {
            return response()->json(["status" => 422, "message" => "Error de validación", "Errors" => $validate->errors()]);
        }

        $empleado = Empleado::with('rol')->where('id_empleado', $id)->first();

        if (!$empleado) {
            return response()->json(["status" => 404, "message" => "Empleado no encontrado"]);
        }

        return response()->json([
            "status" => 200,
            "data" => $empleado
        ]);
    }

        /**
     * @OA\Get(
     *     path="/empleados",
     *     summary="Obtener todos los empleados paginados",
     *     tags={"Empleados"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de empleados",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="page", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function getAllByPage(Request $request)
    {
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
                "status" => 200,
                'data' => $empleados->items(),
                'total' => $empleados->total(),
                'page' => $empleados->currentPage()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Error interno del servidor",
                "error" => $e->getMessage()
            ], 500);
        }
    }
        /**
     * @OA\Post(
     *     path="/empleados",
     *     summary="Crear un nuevo empleado",
     *     tags={"Empleados"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre","apellido","email","dni","id_rol"},
     *             @OA\Property(property="nombre", type="string"),
     *             @OA\Property(property="apellido", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="dni", type="string"),
     *             @OA\Property(property="telefono", type="string"),
     *             @OA\Property(property="id_rol", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Empleado creado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="empleado", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al crear empleado"
     *     )
     * )
     */
    public function create(Request $request)
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

            $password = $this->createPassword($request->dni, $request->nombre, $request->apellido);

            $user = User::create([
                'name' => $request->nombre . ' ' . $request->apellido,
                'email' => $request->email,
                'password' => Hash::make($password),
            ]);

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

            Mail::to($user->email)->send(new CredencialesEmpleadoMail($user, $password));

            return response()->json([
                "status" => 200,
                "message" => "Empleado creado correctamente",
                "user" => $user,
                "empleado" => $empleado,
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                "status" => 500,
                "message" => "Error al crear empleado",
                "error" => $e->getMessage()
            ], 500);
        }
    }
     
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

        /**
     * @OA\Put(
     *     path="/empleados/{id}",
     *     summary="Actualizar datos de un empleado",
     *     tags={"Empleados"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del empleado a actualizar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string"),
     *             @OA\Property(property="apellido", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="dni", type="string"),
     *             @OA\Property(property="telefono", type="string"),
     *             @OA\Property(property="id_rol", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Empleado actualizado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No tienes permiso para modificar este empleado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Empleado no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
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

        $permissionCheck = $this->checkPermissionMiddleware($id);
        if ($permissionCheck) {
            return $permissionCheck;
        }

        $empleado = Empleado::where('id_empleado', $id)->first();

        if (!$empleado) {
            return response()->json([
                "status" => 404,
                "message" => "Empleado no encontrado"
            ]);
        }

        $validator = Validator::make($request->all(), [
            'nombre'    => 'sometimes|string|max:255',
            'apellido'  => 'sometimes|string|max:255',
            'email'     => 'sometimes|string|email|max:255|unique:empleados,email,' . $id . ',id_empleado|unique:users,email,' . $empleado->id_user,
            'dni'       => 'sometimes|string|max:20|unique:empleados,dni,' . $id . ',id_empleado',
            'telefono'  => 'nullable|string|max:20',
            'id_rol'    => 'sometimes|exists:roles,id_rol',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($empleado->id_user);

        if ($user) {
            if ($request->has('email') && $request->email != $empleado->email) {
                $user->email = $request->email;
            }

            if (
                ($request->has('nombre') && $request->nombre != $empleado->nombre) ||
                ($request->has('apellido') && $request->apellido != $empleado->apellido)
            ) {
                $nombre   = $request->has('nombre') ? $request->nombre : $empleado->nombre;
                $apellido = $request->has('apellido') ? $request->apellido : $empleado->apellido;
                $user->name = $nombre . ' ' . $apellido;
            }

            $user->save();
        }

        $empleado->update($request->all());

        return response()->json([
            "status"  => 200,
            "message" => "Empleado actualizado correctamente",
            "data"    => $empleado
        ]);
    }

      /**
     * @OA\Put(
     *     path="/api/empleados/{id}/imagen",
     *     summary="Actualizar imagen de perfil del empleado",
     *     description="Actualiza la imagen de perfil de un empleado y elimina la imagen anterior si existe.",
     *     tags={"Empleados"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del empleado",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"public_id", "secure_url"},
     *             @OA\Property(property="public_id", type="string", example="empleados/perfil123"),
     *             @OA\Property(property="secure_url", type="string", format="url", example="https://res.cloudinary.com/demo/image/upload/v1234567890/empleados/perfil123.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Imagen actualizada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Imagen actualizada correctamente"),
     *             @OA\Property(property="empleado", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="imagen", type="string", example="https://..."),
     *                 @OA\Property(property="public_id", type="string", example="empleados/perfil123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Empleado no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Empleado no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al actualizar la imagen",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Error al actualizar la imagen"),
     *             @OA\Property(property="error", type="string", example="Detalles del error")
     *         )
     *     )
     * )
     */
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
            $empleado = Empleado::where('id_empleado', $id)->first();
            if (!$empleado) {
                return response()->json([
                    "status" => 404,
                    "message" => "Empleado no encontrado"
                ], 404);
            }

            DB::beginTransaction();

            if ($empleado->imagen_perfil) {
                try {

                    $cloudinary = new Cloudinary();

                    $result = $cloudinary->uploadApi()->destroy($empleado->imagen_perfil);

                } catch (\Exception $e) {
                    Log::warning("Error al eliminar imagen anterior, continuando con actualización: " . $e->getMessage());
                }
            }

            $empleado->imagen_perfil_url = null;
            $empleado->imagen_perfil = $request->public_id;
            $empleado->imagen_perfil_url = $request->secure_url;
            $empleado->save();

            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "Imagen actualizada correctamente",
                "data" => [
                    'public_id' => $empleado->imagen_perfil,
                    'url' => $empleado->imagen_perfil_url,
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

        /**
 * @OA\Post(
 *     path="/empleados/{id}/update-password",
 *     summary="Actualizar contraseña de usuario asociado a un empleado",
 *     tags={"Empleados"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del empleado",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"password"},
 *             @OA\Property(property="password", type="string", minLength=4, example="newpassword123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Contraseña actualizada correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="message", type="string", example="Registro actualizado correctamente")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Empleado no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=404),
 *             @OA\Property(property="message", type="string", example="Empleado no encontrado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=422),
 *             @OA\Property(property="message", type="string", example="Error de validación"),
 *             @OA\Property(property="Errors", type="object")
 *         )
 *     )
 * )
 */
    public function updatePass(Request $request, $id)
    {
        $validate = Validator::make(["id" => $id], [
            "id" => "required|numeric",
        ]);

        if ($validate->fails()) {
            return response()->json(["status" => 422, "message" => "Error de validación", "Errors" => $validate->errors()]);
        }

        $empleado = Empleado::where('id_empleado', $id)->first();

        if (!$empleado) {
            return response()->json(["status" => 404, "message" => "Empleado no encontrado"]);
        }

        $userId = $empleado->id_user;

        return $this->updatePass1($request, $userId);
    }

    /**
 * Actualiza la contraseña del usuario identificado por el ID proporcionado.
 * 
 * Valida que el ID y la nueva contraseña sean correctos. Luego encripta la contraseña 
 * y actualiza el registro del usuario en la base de datos. Devuelve una respuesta JSON 
 * indicando el resultado de la operación.
 *
 * @param \Illuminate\Http\Request $request Objeto con los datos de la solicitud, incluyendo 'password'.
 * @param int $id ID del usuario cuya contraseña será actualizada.
 * 
 * @return \Illuminate\Http\JsonResponse Respuesta JSON con el estado y mensaje de la actualización.
 */
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
    /**
 * @OA\Post(
 *     path="/empleados/verify-password",
 *     summary="Verificar contraseña actual de usuario asociado a empleado",
 *     tags={"Empleados"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"currentPassword", "id_empleado"},
 *             @OA\Property(property="currentPassword", type="string", example="currentPass123"),
 *             @OA\Property(property="id_empleado", type="integer", example=123)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Contraseña verificada correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="valid", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Contraseña verificada correctamente")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Contraseña actual incorrecta",
 *         @OA\JsonContent(
 *             @OA\Property(property="valid", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="La contraseña actual es incorrecta")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Usuario asociado no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="valid", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="No se encontró el usuario asociado al empleado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Ocurrió un error al procesar la solicitud"),
 *             @OA\Property(property="message", type="string", example="Detalle del error")
 *         )
 *     )
 * )
 */
    public function verifyPassword(Request $request)
    {
        try {
            $request->validate([
                'currentPassword' => 'required',
                'id_empleado' => 'required|exists:empleados,id_empleado'
            ]);

            $empleado = Empleado::with('user')->findOrFail($request->id_empleado);

            if (!$empleado->user) {
                return response()->json([
                    'valid' => false,
                    'message' => 'No se encontró el usuario asociado al empleado'
                ], 404);
            }

            if (!Hash::check($request->currentPassword, $empleado->user->password)) {
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
    /**
 * @OA\Delete(
 *     path="/empleados/{id}",
 *     summary="Eliminar empleado y usuario asociado",
 *     tags={"Empleados"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del empleado a eliminar",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Empleado eliminado correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="message", type="string", example="Empleado eliminado correctamente")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Empleado no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=404),
 *             @OA\Property(property="message", type="string", example="Empleado no encontrado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=422),
 *             @OA\Property(property="message", type="string", example="Error de validación"),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
    public function delete(Request $request, $id)
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

        $permissionCheck = $this->checkPermissionMiddleware($id);
        if ($permissionCheck) {
            return $permissionCheck;
        }

        $empleado = Empleado::where('id_empleado', $id)->first();

        if (!$empleado) {
            return response()->json([
                "status" => 404,
                "message" => "Empleado no encontrado"
            ], 404);
        }


        $user = User::find($empleado->id_user);
        if ($user) {
            Log::info("Eliminando usuario vinculado con ID: " . $user->id);
            $user->delete();
        }

        Log::info("Eliminando empleado con ID: $id");
        $empleado->delete();

        Log::info("Empleado eliminado correctamente");
        return response()->json([
            "status" => 200,
            "message" => "Empleado eliminado correctamente"
        ], 200);
    }
    /**
 * @OA\Delete(
 *     path="/empleados/{id}/profile-image",
 *     summary="Eliminar imagen de perfil del empleado",
 *     tags={"Empleados"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del empleado",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Imagen eliminada correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="message", type="string", example="Imagen eliminada correctamente")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Empleado no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=404),
 *             @OA\Property(property="message", type="string", example="Empleado no encontrado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error al eliminar la imagen",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=500),
 *             @OA\Property(property="message", type="string", example="Error al eliminar la imagen"),
 *             @OA\Property(property="error", type="string", example="Error interno del servidor")
 *         )
 *     )
 * )
 */
    public function deleteProfileImage($id)
    {
        try {
            $empleado = Empleado::where('id_empleado', $id)->first();

            if (!$empleado) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Empleado no encontrado'
                ], 404);
            }

            if ($empleado->imagen_perfil) {
                Log::info('Intentando eliminar imagen de perfil:', ['public_id' => $empleado->imagen_perfil]);

                try {
                    $cloudinary = new Cloudinary();

                    $result = $cloudinary->uploadApi()->destroy($empleado->imagen_perfil);
                    Log::info('Resultado de eliminación:', ['result' => $result]);
                } catch (\Exception $e) {
                    Log::warning("Error al eliminar imagen de Cloudinary: " . $e->getMessage());
                    // Continuamos con la actualización en la base de datos
                }

                $empleado->imagen_perfil = null;
                $empleado->imagen_perfil_url = null;
                $empleado->save();
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
