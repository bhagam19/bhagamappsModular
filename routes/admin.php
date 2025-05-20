<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;

use App\Http\Controllers\Admin\GrupoController;
use App\Http\Controllers\Admin\AsignarDocenteGrupoController;
use App\Http\Controllers\Admin\EncuestaController;
use App\Http\Controllers\Admin\PreguntaController;

Route::get('', [HomeController::class,'index'])->name('admin.index');

Route::resource('users', UserController::class)->names('admin.users');

Route::resource('roles', RoleController::class)->names('admin.roles');

Route::resource('permissions', PermissionController::class)->names('admin.permissions');


use App\Models\Role;
Route::get('/roles/{role}/editar-permisos', function (Role $role) {
    return view('admin.roles.permissions-role', compact('role'));
})->middleware('auth')->name('roles.editar-permisos');

Route::get('/permisos', \App\Livewire\Permissions\PermissionsIndex::class)->middleware(['auth'])->name('permisos.index');


Route::resource('grupos', GrupoController::class)->names('admin.grupos');
/*Route::resource('asignacionesDocenteGrupo', AsignarDocenteGrupoController::class)->names('admin.asignacionesDocenteGrupo');
Route::get('asignacionesDocenteGrupo/docente/{id}/grupos', [AsignarDocenteGrupoController::class, 'gruposDelDocente']);


Route::resource('encuestas', EncuestaController::class)->names('admin.encuestas');
Route::resource('preguntas', PreguntaController::class)->names('admin.preguntas');

*/
