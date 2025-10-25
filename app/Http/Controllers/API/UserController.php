<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Log;
use App\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        if (!auth()->user()->isLider()) {
            return response(['message' => 'Acceso denegado. Solo el Líder puede ver los usuarios.'], 403);
        }

        $users = User::with('role:id,nombre')
            ->select('id', 'nombre', 'email', 'role_id')
            ->latest()
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                    'role' => $user->role->nombre ?? 'Sin Rol',
                ];
            });

        return response([
            'users' => $users,
            'message' => 'Usuarios recuperados correctamente'
        ], 200);
    }

    public function updateRole(Request $request, User $user)
    {
        if (!auth()->user()->isLider()) {
            return response(['message' => 'Acceso denegado. Solo el Líder puede modificar roles.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 400);
        }

        if ($user->id === auth()->id()) {
            return response(['message' => 'No puedes modificar tu propio rol.'], 400);
        }

        $oldRole = $user->role->nombre ?? 'Sin Rol';
        $newRoleId = $request->role_id;

        $newRole = \App\Models\Role::find($newRoleId);
        if (!$newRole) {
            return response(['message' => "El ID de Rol '{$newRoleId}' no es válido."], 404);
        }
        $newRoleName = $newRole->nombre;

        $user->role_id = $newRoleId;
        $user->save();

        Log::create([
            'user_id' => auth()->id(),
            'accion' => "Actualizó el rol del usuario '{$user->nombre}' (ID {$user->id}) de '{$oldRole}' a '{$newRoleName}'.",
            'created_at' => now(),
        ]);

        return response([
            'message' => "Rol del usuario {$user->nombre} actualizado a {$newRoleName} correctamente.",
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'role_id' => $newRoleId,
                'role' => $newRoleName, // Devolver el nombre para la actualización del frontend
            ]
        ], 200);
    }
}
