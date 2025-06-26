<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;
use Modules\User\Http\Controllers\RoleController;
use Modules\User\Http\Controllers\PermissionController;
use Livewire\Livewire;
use Illuminate\Support\Facades\Auth;
use Modules\User\Entities\Role;
use Modules\User\Livewire\Permissions\PermissionsIndex; // Asegúrate de que esta ruta sea correcta

Route::middleware(['web', 'auth'])->prefix('users')->group(function () {
    Route::resource('users', UserController::class)->names('user.users');
    Route::resource('roles', RoleController::class)->names('user.roles');
    Route::resource('permissions', PermissionController::class)->names('user.permissions');

    Route::get('/roles/{role}/editar-permisos', function (Role $role) {
        $user = Auth::user();

        if (!$user->hasPermission('editar-permisos')) {
            return redirect()->route('ppal.index');
        }

        return view('user::roles.permissions-role', compact('role'));
    })->name('roles.editar-permisos');

    // Redirige a una vista donde se usará el componente Livewire
    Route::get('/permisos', function () {
        return view('user::permissions.index');
    })->name('permisos.index');
});

// Puedes registrar el componente Livewire así si quieres usarlo en Blade:
Livewire::component('permissions.index', PermissionsIndex::class);
