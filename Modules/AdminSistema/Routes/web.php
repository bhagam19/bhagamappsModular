<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminSistema\Http\Controllers\BackupsController;

Route::middleware(['web', 'auth', 'app.access:admin-sistema'])->prefix('admin')->group(function () {

    Route::get('/backups', [BackupsController::class, 'index'])
        ->name('admin.backups.index')
        ->middleware('permission:ver-backups');

    Route::get('/backups/{fecha}', [BackupsController::class, 'detalle'])
        ->name('admin.backups.detalle')
        ->middleware('permission:ver-backups');

    Route::get('/backups/{fecha}/descargar', [BackupsController::class, 'descargar'])
        ->name('admin.backups.descargar')
        ->middleware('permission:descargar-backups');
});
