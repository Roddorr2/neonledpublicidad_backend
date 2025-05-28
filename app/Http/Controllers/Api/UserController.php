<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{


    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required|string|min:4",
        ]);

        if ($validate->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Fallo de validación",
                "errors" => $validate->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                "success" => false,
                "message" => "Usuario no encontrado"
            ], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                "success" => false,
                "message" => "Credenciales incorrectas"
            ], 401);
        }

        return response()->json([
            "success" => true,
            "message" => "Inicio de sesión exitoso",
            "token" => $user->createToken('auth_token')->plainTextToken,
            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
            ]
        ], 200);
    }




        /**
     * @OA\Get(
     *     path="/api/user/{id}",
     *     summary="Obtener un usuario por su ID",
     *     tags={"Usuarios"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario obtenido correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User"))
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
    public function getById($id)
    {
        $validate = Validator::make(["id" => $id], [
            "id" => "required|numeric",
        ]);

        if ($validate->fails()) return response()->json(["status" => 422, "message" => "Error de validación", "Errors" => $validate->errors()]);

        $user = User::where("id", $id)->get();

        return response()->json([
            "status" => 200,
            "data" => $user
        ]);
    }


    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Obtener todos los usuarios paginados",
     *     tags={"Usuarios"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuarios",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *             @OA\Property(property="total", type="integer", example=100),
     *             @OA\Property(property="page", type="integer", example=1)
     *         )
     *     )
     * )
     */
    public function getAllByPage(Request $request)
    {

        $users = User::orderBy('id', 'desc')->paginate(20);

        return response()->json([
            "status" => 200,
            'data' => $users->items(),
            'total' => $users->total(),
            'page' => $users->currentPage()
        ]);
    }

   
  /**
 * @OA\Post(
 *     path="/api/users",
 *     summary="Crear un nuevo usuario",
 *     operationId="createUser",
 *     tags={"Usuarios"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "email", "password"},
 *             @OA\Property(property="name", type="string", example="Juan Pérez"),
 *             @OA\Property(property="email", type="string", format="email", example="juan@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="1234")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Registro creado correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="message", type="string", example="Registro creado correctamente")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=422),
 *             @OA\Property(property="message", type="string", example="Error de validación"),
 *             @OA\Property(
 *                 property="Errors",
 *                 type="object",
 *                 additionalProperties=@OA\Property(type="array", @OA\Items(type="string")),
 *                 example={"email": {"El campo email ya ha sido tomado."}}
 *             )
 *         )
 *     )
 * )
 */
    public function create(Request $request)
    {

        $validate = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email",
            "password" => "required|string|min:4",
        ]);

        if ($validate->fails()) return response()->json(["status" => 422, "message" => "Error de validación", "Errors" => $validate->errors()]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->remember_token = Hash::make(Str::random(25));

        $response = $user->save();

        if ($response) return response()->json(["status" => 200, "message" => "Registro creado correctamente"]);
    }

    /**
 * @OA\Put(
 *     path="/api/user/{id}",
 *     summary="Actualizar el nombre de un usuario",
 *     tags={"Usuarios"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID del usuario",
 *         @OA\Schema(type="integer", example=5)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="Carlos Mendoza")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Usuario actualizado correctamente"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación"
 *     )
 * )
 */
    public function update(Request $request, $id)
    {

        $validate = Validator::make(["id" => $request->id], [
            "id" => "required|numeric",
        ]);

        if ($validate->fails()) return response()->json(["status" => 422, "message" => "Error de validación", "Errors" => $validate->errors()]);

        $validate = Validator::make($request->all(), [
            "name" => "required|string|max:255",
        ]);
        if ($validate->fails()) return response()->json(["status" => 422, "message" => "Error de validación", "Errors" => $validate->errors()]);

        $response = User::where(["id" => intval($id)])->update(["name" => $request->name]);

        if ($response) return response()->json(["status" => 200, "message" => "Registro actualizado correctamente"]);
    }

    /**
 * @OA\Put(
 *     path="/api/user/update-pass/{id}",
 *     summary="Actualizar la contraseña de un usuario",
 *     tags={"Usuarios"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID del usuario",
 *         @OA\Schema(type="integer", example=5)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"password"},
 *             @OA\Property(property="password", type="string", format="password", example="nuevaPassword123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Contraseña actualizada correctamente"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación"
 *     )
 * )
 */
    public function updatePass(Request $request, $id)
    {

        $validate = Validator::make(["id" => $request->id], [
            "id" => "required|numeric",
        ]);
        if ($validate->fails()) return response()->json(["status" => 422, "message" => "Error de validación", "Errors" => $validate->errors()]);

        $validate = Validator::make($request->all(), [
            "password" => "required|string|min:4",
        ]);
        if ($validate->fails()) return response()->json(["status" => 422, "message" => "Error de validación", "Errors" => $validate->errors(), "data" => $request->all()]);

        $response = User::where(["id" => intval($id)])->update(["password" => Hash::make($request->password)]);

        if ($response) return response()->json(["status" => 200, "message" => "Registro actualizado correctamente"]);
    }

    /**
 * @OA\Delete(
 *     path="/api/user/{id}",
 *     summary="Eliminar un usuario",
 *     tags={"Usuarios"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID del usuario",
 *         @OA\Schema(type="integer", example=7)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Usuario eliminado correctamente"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación o intento de eliminar el administrador"
 *     )
 * )
 */
    public function delete(Request $request, $id)
    {

        $validate = Validator::make(["id" => $id], [
            "id" => "required|numeric",
        ]);

        if ($validate->fails()) return response()->json(["status" => 422, "message" => "Error de validación", "Errors" => $validate->errors()]);

        if (intval($id) == 1) return response()->json(["status" => 422, "message" => "El admin no puede ser eliminado"]);

        $response = User::where(["id" => intval($id)])->delete();

        if ($response) return response()->json(["status" => 200, "message" => "Registro eliminado correctamente"]);
    }

    /**
 * @OA\Post(
 *     path="/api/user/logout",
 *     summary="Cerrar sesión del usuario actual y revocar sus tokens",
 *     tags={"Usuarios"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Tokens revocados correctamente"
 *     )
 * )
 */
    public function logout(Request $request)
    {

        $request->user()->tokens()->delete(); // Revoca todos los tokens del usuario
        return response()->json(["status" => 200, 'message' => 'Tokens revoked']);
    }
}
