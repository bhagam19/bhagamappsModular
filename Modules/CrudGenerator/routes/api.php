<?php

use Illuminate\Support\Facades\Route;
use Modules\CrudGenerator\Http\Controllers\CrudGeneratorController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('crudgenerators', CrudGeneratorController::class)->names('crudgenerator');
});
