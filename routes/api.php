<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([ 'message' => 'La API funciona correctamente' ]);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json($request->user()->load('role'));
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('/projects', ProjectController::class);

    Route::middleware('role:Líder')->group(function () {
        // Rutas exclusivas del lider
    });
});
