<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function getById($id)
    {
        $validate = Validator::make(['id' => $id], [
            'id' => 'required|numeric',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => 422, 'message' => 'Error de validación', 'Errors' => $validate->errors()]);
        }

        $user = User::where('id', $id)->get();

        return response()->json([
            'status' => 200,
            'data'   => $user,
        ]);
    }

    public function getAllByPage(Request $request)
    {

        $users = User::orderBy('id', 'desc')->paginate(20);

        return response()->json([
            'status' => 200,
            'data'   => $users->items(),
            'total'  => $users->total(),
            'page'   => $users->currentPage(),
        ]);
    }

    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:4',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Fallo de validación',
                'errors'  => $validate->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'token'   => $user->createToken('auth_token')->plainTextToken,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    public function create(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:4',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => 422, 'message' => 'Error de validación', 'Errors' => $validate->errors()]);
        }

        $user                 = new User;
        $user->name           = $request->name;
        $user->email          = $request->email;
        $user->password       = Hash::make($request->password);
        $user->remember_token = Hash::make(Str::random(25));

        $response = $user->save();

        if ($response) {
            return response()->json(['status' => 200, 'message' => 'Registro creado correctamente']);
        }
    }

    public function update(Request $request, $id)
    {

        $validate = Validator::make(['id' => $request->id], [
            'id' => 'required|numeric',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => 422, 'message' => 'Error de validación', 'Errors' => $validate->errors()]);
        }

        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validate->fails()) {
            return response()->json(['status' => 422, 'message' => 'Error de validación', 'Errors' => $validate->errors()]);
        }

        $response = User::where(['id' => intval($id)])->update(['name' => $request->name]);

        if ($response) {
            return response()->json(['status' => 200, 'message' => 'Registro actualizado correctamente']);
        }
    }

    public function updatePass(Request $request, $id)
    {

        $validate = Validator::make(['id' => $request->id], [
            'id' => 'required|numeric',
        ]);
        if ($validate->fails()) {
            return response()->json(['status' => 422, 'message' => 'Error de validación', 'Errors' => $validate->errors()]);
        }

        $validate = Validator::make($request->all(), [
            'password' => 'required|string|min:4',
        ]);
        if ($validate->fails()) {
            return response()->json(['status' => 422, 'message' => 'Error de validación', 'Errors' => $validate->errors(), 'data' => $request->all()]);
        }

        $response = User::where(['id' => intval($id)])->update(['password' => Hash::make($request->password)]);

        if ($response) {
            return response()->json(['status' => 200, 'message' => 'Registro actualizado correctamente']);
        }
    }

    public function delete(Request $request, $id)
    {

        $validate = Validator::make(['id' => $id], [
            'id' => 'required|numeric',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => 422, 'message' => 'Error de validación', 'Errors' => $validate->errors()]);
        }

        if (intval($id) == 1) {
            return response()->json(['status' => 422, 'message' => 'El admin no puede ser eliminado']);
        }

        $response = User::where(['id' => intval($id)])->delete();

        if ($response) {
            return response()->json(['status' => 200, 'message' => 'Registro eliminado correctamente']);
        }
    }

    public function logout(Request $request)
    {

        $request->user()->tokens()->delete(); // Revoca todos los tokens del usuario

        return response()->json(['status' => 200, 'message' => 'Tokens revoked']);
    }
}
