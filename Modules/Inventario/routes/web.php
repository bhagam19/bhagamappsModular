<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventario\Http\Controllers\BienController;
use Modules\Inventario\Http\Controllers\ActaController;



Route::middleware(['web', 'auth'])->prefix('inventario')->group(function () {
    Route::resource('bienes', BienController::class)->names('inventario.bienes');
    Route::resource('actas', ActaController::class)->names('inventario.actas');   
    
});