<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validación fallida',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        if (!$user->estado) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario se encuentra inactivo'
            ], 403);
        }

        // Verifica hash de Laravel o texto plano directo (del seed de SQL)
        $passwordMatches = false;
        try {
            $passwordMatches = Hash::check($request->password, $user->password);
        } catch (\RuntimeException $e) {
            // No es un hash Bcrypt válido
        }
        if (!$passwordMatches) {
            $passwordMatches = ($request->password === $user->password);
        }

        if (!$passwordMatches) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ], 401);
        }

        // Generar token Sanctum
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'user' => [
                'id_usuario' => $user->id_usuario,
                'nombres' => $user->nombres,
                'username' => $user->username,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'user' => [
                'id_usuario' => $user->id_usuario,
                'nombres' => $user->nombres,
                'username' => $user->username,
            ]
        ]);
    }
}
