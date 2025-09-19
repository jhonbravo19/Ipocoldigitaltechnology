<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // si usas Auth::routes()

Route::get('/', function () {
    return view('index');
})->name('home');

Auth::routes();

// incluye tus otros archivos de rutas
require __DIR__ . '/admin.php';
require __DIR__ . '/user.php';
require __DIR__ . '/public.php';

