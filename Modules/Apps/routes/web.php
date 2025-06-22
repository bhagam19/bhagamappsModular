<?php

use Illuminate\Support\Facades\Route;
use Modules\Apps\Http\Controllers\AppController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('apps', AppController::class)->names('apps.apps');
});
