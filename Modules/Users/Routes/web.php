<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\UserController;
use Modules\Users\Http\Controllers\RoleController;
use Modules\Users\Http\Controllers\PermissionController;
use Modules\Users\Models\Role;
use Modules\Users\Livewire\Permissions\PermissionsIndex; // Asegúrate de que esta ruta sea correcta
use Livewire\Livewire;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

Route::middleware(['web', 'auth'])->prefix('usuarios')->group(function () {
    Route::resource('users', UserController::class)->names('usuarios.users');
    Route::resource('roles', RoleController::class)->names('usuarios.roles');
    Route::resource('permissions', PermissionController::class)->names('usuarios.permissions');

    Route::get('/roles/{role}/editar-permisos', function (Role $role) {
        $user = Auth::user();

        if (!$user->hasPermission('editar-permisos')) {
            return redirect()->route('ppal.index');
        }

        return view('users::roles.permissions-role', compact('role'));
    })->name('roles.editar-permisos');

    // Redirige a una vista donde se usará el componente Livewire
    Route::get('/permisos', function () {
        return view('usuarios.permissions.index');
    })->name('permisos.index');
});

// Puedes registrar el componente Livewire así si quieres usarlo en Blade:
Livewire::component('permissions.index', PermissionsIndex::class);

