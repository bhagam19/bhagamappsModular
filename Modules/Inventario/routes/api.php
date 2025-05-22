<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventario\Http\Controllers\InventarioController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('inventarios', InventarioController::class)->names('inventario');
});
