<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventario\Http\Controllers\InventarioController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('inventarios', InventarioController::class)->names('inventario');
});
