<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\MovimientoController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('productos', ProductoController::class);

