<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventario\Http\Controllers\BienController;
use Modules\Inventario\Http\Controllers\ActaController;
use Modules\Inventario\Http\Controllers\ActaPDFController;



Route::middleware(['web', 'auth'])->prefix('inventario')->group(function () {
    Route::resource('bienes', BienController::class)->names('inventario.bienes');
    Route::resource('actas', ActaController::class)->names('inventario.actas');

    Route::get('/actas/{userId}/pdf', [ActaPDFController::class, 'show'])->name('inventario.actas.pdf');
    
});