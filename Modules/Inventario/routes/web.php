<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventario\Http\Controllers\BienController;
use Modules\Inventario\Http\Controllers\HmbController;
use Modules\Inventario\Http\Controllers\HebController;
use Modules\Inventario\Http\Controllers\ActaController;
use Modules\Inventario\Http\Controllers\ActaPDFController;
use Modules\Inventario\Http\Controllers\CatalogosController;
use Modules\Inventario\Http\Controllers\ResponsablesController;
use Modules\Inventario\Http\Controllers\UbicacionesHistorialController;

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

    // Catálogos maestros
    Route::get('/catalogos/categorias', [CatalogosController::class, 'categorias'])
        ->name('inventario.catalogos.categorias')
        ->middleware('permission:ver-categorias');

    Route::get('/catalogos/dependencias', [CatalogosController::class, 'dependencias'])
        ->name('inventario.catalogos.dependencias')
        ->middleware('permission:ver-dependencias');

    Route::get('/catalogos/ubicaciones', [CatalogosController::class, 'ubicaciones'])
        ->name('inventario.catalogos.ubicaciones')
        ->middleware('permission:ver-ubicaciones');

    Route::get('/catalogos/estados', [CatalogosController::class, 'estados'])
        ->name('inventario.catalogos.estados')
        ->middleware('permission:ver-estados');

    Route::get('/catalogos/origenes', [CatalogosController::class, 'origenes'])
        ->name('inventario.catalogos.origenes')
        ->middleware('permission:ver-origenes');

    Route::get('/catalogos/almacenamientos', [CatalogosController::class, 'almacenamientos'])
        ->name('inventario.catalogos.almacenamientos')
        ->middleware('permission:ver-almacenamientos');

    Route::get('/catalogos/mantenimientos', [CatalogosController::class, 'mantenimientos'])
        ->name('inventario.catalogos.mantenimientos')
        ->middleware('permission:ver-mantenimientos');

    // Responsables y Custodios
    Route::get('/responsables', [ResponsablesController::class, 'index'])
        ->name('inventario.responsables.index')
        ->middleware('permission:ver-responsables-bienes');

    // Historial de Ubicaciones
    Route::get('/ubicaciones/historial', [UbicacionesHistorialController::class, 'index'])
        ->name('inventario.ubicaciones.historial')
        ->middleware('permission:ver-historial-ubicaciones-bienes');
});
