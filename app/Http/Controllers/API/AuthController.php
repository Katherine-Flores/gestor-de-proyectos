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

    public function updateProfile(Request $request, $id)
    {
        $authUser = $request->user();
        $user = User::findOrFail($id);

        if ($authUser->id !== $user->id && !$authUser->isLider()) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $rules = [
            'nombre' => 'sometimes|string|max:150',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|confirmed|min:6',
            'current_password' => 'sometimes|required_with:email,password'
        ];

        // Si el líder edita a otro usuario, puede cambiar el rol
        if ($authUser->isLider() && $authUser->id !== $user->id) {
            $rules['role_id'] = 'required|exists:roles,id';
        }

        $data = $request->validate($rules);

        // Validar contraseña actual si va a cambiar email o password
        if (($request->has('email') || $request->has('password')) &&
            !Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Contraseña actual incorrecta'], 401);
        }

        // Evitar que un líder cambie su propio rol
        if ($authUser->isLider() && $authUser->id === $user->id && isset($data['role_id'])) {
            unset($data['role_id']);
        }

        // Encriptar contraseña si fue actualizada
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'role' => $user->role->nombre,
            ]
        ]);
    }

}
