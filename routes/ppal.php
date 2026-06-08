<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ppal\HomeController;

Route::get('', [HomeController::class,'index'])->name('ppal.index');