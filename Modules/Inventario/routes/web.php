<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventario\Http\Controllers\BienController;

use Modules\Inventario\Entities\Role;
use Modules\Inventario\Livewire\Permissions\PermissionsIndex; // Asegúrate de que esta ruta sea correcta
use Livewire\Livewire;

Route::middleware(['web', 'auth'])->prefix('inventario')->group(function () {
    Route::resource('bienes', BienController::class)->names('inventario.bienes');
    
});