<?php

use Illuminate\Support\Facades\Route;
use Modules\CrudGenerator\Http\Controllers\CrudGeneratorController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('crudgenerators', CrudGeneratorController::class)->names('crudgenerator');
});
