<?php

use Illuminate\Support\Facades\Route;
use Modules\Apps\Http\Controllers\AppController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('apps', AppController::class)->names('apps');
});
