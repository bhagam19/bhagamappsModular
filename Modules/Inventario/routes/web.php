<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventario\Http\Controllers\BienController;
use Modules\Inventario\Http\Controllers\BapController;
use Modules\Inventario\Http\Controllers\HebController;
use Modules\Inventario\Http\Controllers\ActaController;
use Modules\Inventario\Http\Controllers\ActaPDFController;



Route::middleware(['web', 'auth'])->prefix('inventario')->group(function () {
    Route::resource('bienes', BienController::class)->names('inventario.bienes');
    Route::resource('bap', BapController::class)->names('inventario.bap');
    Route::resource('heb', HebController::class)->names('inventario.heb');
    Route::resource('actas', ActaController::class)->names('inventario.actas');

    Route::get('/actas/{userId}/pdf', [ActaPDFController::class, 'show'])->name('inventario.actas.pdf');

    Route::get('/bap', [BapController::class, 'index'])
        ->name('inventario.bap');
    Route::get('/heb', [HebController::class, 'index'])
        ->name('inventario.heb');
});
