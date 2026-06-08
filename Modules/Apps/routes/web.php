<?php

use Illuminate\Support\Facades\Route;
use Modules\Apps\Http\Controllers\AppController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Ruta específica debe preceder al resource para evitar conflicto con {app}
    Route::get('/apps/admin', [AppController::class, 'index'])->name('apps.admin.index');
    Route::resource('apps', AppController::class)->names('apps.apps');
});
