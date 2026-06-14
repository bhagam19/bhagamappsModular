<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminSistema\Http\Controllers\BackupsController;
use Modules\AdminSistema\Http\Controllers\ImportarSnapshotController;
use Modules\AdminSistema\Http\Controllers\RestaurarController;

Route::middleware(['web', 'auth', 'app.access:admin-sistema'])->prefix('admin')->group(function () {

    Route::get('/backups', [BackupsController::class, 'index'])
        ->name('admin.backups.index')
        ->middleware('permission:ver-backups');

    // RESTORE-WEB-001: antes de /{fecha} para evitar conflicto de ruta
    Route::get('/backups/restaurar', [RestaurarController::class, 'index'])
        ->name('admin.backups.restaurar')
        ->middleware('permission:restaurar-backups');

    // SNAP-001: antes de /{fecha} para evitar conflicto de ruta
    Route::get('/backups/importar', [ImportarSnapshotController::class, 'index'])
        ->name('admin.backups.importar')
        ->middleware('permission:importar-snapshot-backup');

    Route::get('/backups/{fecha}', [BackupsController::class, 'detalle'])
        ->name('admin.backups.detalle')
        ->middleware('permission:ver-backups');

    Route::get('/backups/{fecha}/descargar', [BackupsController::class, 'descargar'])
        ->name('admin.backups.descargar')
        ->middleware('permission:descargar-backups');
});
