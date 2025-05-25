<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventario\Http\Controllers\BienController;

use Modules\Inventario\Entities\Role;
use Modules\Inventario\Livewire\Permissions\PermissionsIndex; // Asegúrate de que esta ruta sea correcta
use Livewire\Livewire;

Route::middleware(['web', 'auth'])->prefix('admin')->group(function () {
    Route::resource('bienes', BienController::class)->names('admin.bienes');
    Route::resource('roles', RoleController::class)->names('admin.roles');
    Route::resource('permissions', PermissionController::class)->names('admin.permissions');

    Route::get('/roles/{role}/editar-permisos', function (Role $role) {
        return view('users::roles.permissions-role', compact('role'));
    })->name('roles.editar-permisos');

    // Redirige a una vista donde se usará el componente Livewire
    Route::get('/permisos', function () {
        return view('admin.permissions.index');
    })->name('permisos.index');
});