<?php

use Illuminate\Support\Facades\Route;
use Modules\Apps\Http\Controllers\AppsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('apps', AppsController::class)->names('apps');
});
