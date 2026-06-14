<?php

use Illuminate\Support\Facades\Route;
use Modules\ActivityLog\Http\Controllers\ActivityLogController;

Route::middleware(['web', 'auth', 'app.access:admin-sistema'])->prefix('admin')->group(function () {

    Route::get('/activity-log', [ActivityLogController::class, 'index'])
        ->name('admin.activity-log.index')
        ->middleware('permission:ver-activity-log');
});
