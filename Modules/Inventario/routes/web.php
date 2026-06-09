<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventario\Http\Controllers\BienController;
use Modules\Inventario\Http\Controllers\HmbController;
use Modules\Inventario\Http\Controllers\HebController;
use Modules\Inventario\Http\Controllers\ActaController;
use Modules\Inventario\Http\Controllers\ActaPDFController;

Route::middleware(['web', 'auth', 'app.access:inventario'])->prefix('inventario')->group(function () {

    Route::get('/bienes', [BienController::class, 'index'])
        ->name('inventario.bienes.index')
        ->middleware('permission:ver-bienes');

    Route::resource('bienes', BienController::class)
        ->names('inventario.bienes')
        ->except(['index']);

    Route::get('/actas', [ActaController::class, 'index'])
        ->name('inventario.actas.index')
        ->middleware('permission:ver-actas-de-entrega');

    Route::resource('actas', ActaController::class)
        ->names('inventario.actas')
        ->except(['index']);

    Route::get('/actas/{userId}/pdf', [ActaPDFController::class, 'show'])
        ->name('inventario.actas.pdf')
        ->middleware('permission:ver-actas-de-entrega');

    Route::get('/hmb', [HmbController::class, 'index'])
        ->name('inventario.hmb')
        ->middleware('permission:gestionar-historial-modificaciones-bienes');

    Route::get('/heb', [HebController::class, 'index'])
        ->name('inventario.heb')
        ->middleware('permission:gestionar-historial-eliminaciones-bienes');
});
