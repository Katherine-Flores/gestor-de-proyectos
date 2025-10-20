<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\UpdateController;
use App\Http\Controllers\API\ReportesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([ 'message' => 'La API funciona correctamente' ]);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

// Rutas protegidas
Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json($request->user()->load('role'));
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::put('/users/{id}/update-profile', [AuthController::class, 'updateProfile']);

    Route::apiResource('/projects', ProjectController::class);

    Route::apiResource('/updates', UpdateController::class)->only(['index', 'store', 'show']);

    // Rutas de reportes
    Route::get('/reportes/proyectos', [ReportesController::class, 'proyectosCreados']);
    Route::get('/reportes/en-ejecucion', [ReportesController::class, 'proyectosEnEjecucion']);
    Route::get('/reportes/finalizados', [ReportesController::class, 'proyectosFinalizados']);
    Route::get('/reportes/lideres', [ReportesController::class, 'proyectosPorLider']);
    Route::get('/reportes/clientes', [ReportesController::class, 'proyectosPorCliente']);

    Route::middleware('role:Líder')->group(function () {
        // Rutas exclusivas del lider
    });
});
