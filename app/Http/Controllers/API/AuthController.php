<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|max:150',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'role_id' => 'sometimes|exists:roles,id',
        ]);

        if (!isset($validatedData['role_id'])) {
            $clienteRole = Role::where('nombre', 'Cliente')->first();
            $validatedData['role_id'] = $clienteRole->id;
        }

        $validatedData['password'] = Hash::make($request->password);

        $user = User::create($validatedData);
        $accessToken = $user->createToken('authToken')->accessToken;
        return response([
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'role' => $user->role->nombre,
            ],
            'access_token' => $accessToken], 201);
    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
           'email' => 'email|required',
           'password' => 'required'
        ]);

        if (!auth()->attempt($loginData)) {
            return response(['message' => 'Credenciales incorrectas'], 401);
        }

        $user = auth()->user();
        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response([
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'role' => $user->role->nombre,
            ],
            'access_token' => $accessToken]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response(['message' => 'Sesión cerrada correctamente']);
    }

}
