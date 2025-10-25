<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'projects'], function () {
    // Listado de proyectos (index.blade.php)
    Route::get('/', function () {
        return view('projects.index');
    })->name('projects.index');

    // Formulario de creación (create.blade.php)
    Route::get('/create', function () {
        return view('projects.create');
    })->name('projects.create');

    // Detalle del proyecto (show.blade.php)
    Route::get('/{id}', function ($id) {
        return view('projects.show', ['project_id' => $id]);
    })->name('projects.show');

    // Formulario de edición (edit.blade.php)
    Route::get('/{id}/edit', function ($id) {
        return view('projects.edit', ['project_id' => $id]);
    })->name('projects.edit');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/configuracion', function () {
    return view('configuracion.index');
})->name('configuracion.index');

Route::get('/reportes', function () {
    return view('reportes.index');
})->name('reportes.index');

Route::get('/users', function () {
    return view('users.index');
})->name('users.index');
