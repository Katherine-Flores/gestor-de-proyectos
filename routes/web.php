<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProjectWebController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('projects', ProjectWebController::class);
