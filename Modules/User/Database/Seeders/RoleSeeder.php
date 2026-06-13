<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Entities\Role;
use Modules\User\Entities\Permission;
use Modules\Apps\Entities\App;

class RoleSeeder extends Seeder
{
    public function run(): void
    {

        $app = App::where('slug', 'user')->first();

        if (!$app) {
            $this->command->error("No se encontró la aplicación con slug 'user'. Crea la aplicación antes de correr este seeder.");
            return;
        }

        $roles = [
            ['nombre' => 'Administrador', 'descripcion' => 'Usuario con acceso completo al sistema.'],
            ['nombre' => 'Rector', 'descripcion' => 'Orienta todos los procesos en general.'],
            ['nombre' => 'Coordinador', 'descripcion' => 'Supervisa procesos académicos o administrativos.'],
            ['nombre' => 'Auxiliar', 'descripcion' => 'Apoya todos los procesos académicos o administrativos.'],
            ['nombre' => 'Docente', 'descripcion' => 'Encargado de impartir clases y evaluar a los estudiantes.'],
            ['nombre' => 'Estudiante', 'descripcion' => 'Usuario que accede a contenidos y actividades académicas.'],
            ['nombre' => 'Invitado', 'descripcion' => 'Acceso limitado para pruebas o demostraciones.'],
        ];

        foreach ($roles as $rolData) {
            $rol = Role::updateOrCreate(
                ['nombre' => $rolData['nombre'], 'app_id' => $app->id],
                ['descripcion' => $rolData['descripcion']]
            );
        }

        // Obtener los roles ya creados (con app_id)
        $admin = Role::where('nombre', 'Administrador')->where('app_id', $app->id)->first();
        $rector = Role::where('nombre', 'Rector')->where('app_id', $app->id)->first();
        $coordinador = Role::where('nombre', 'Coordinador')->where('app_id', $app->id)->first();
        $docente = Role::where('nombre', 'Docente')->where('app_id', $app->id)->first();
        $auxiliar = Role::where('nombre', 'Auxiliar')->where('app_id', $app->id)->first();

        // Administrador: acceso completo
        $permisosAdmin = Permission::pluck('id');
        if ($permisosAdmin->isNotEmpty() && $admin) {
            $admin->permissions()->sync($permisosAdmin);
        }

        // Rector: acceso completo excepto gestión de roles y permisos (MENU-004 / V-008 / V-009)
        $permisosRector = Permission::whereNotIn('categoria', ['roles', 'permisos'])->pluck('id');
        if ($permisosRector->isNotEmpty() && $rector) {
            $rector->permissions()->sync($permisosRector);
        }

        // Permisos categoría 'user' y 'bienes' para coordinador
        $permisos = Permission::whereIn('categoria', ['user', 'bienes'])->pluck('id');
        if ($permisos->isNotEmpty() && $coordinador) {
            $coordinador->permissions()->sync($permisos);
        }

        // Permisos categoría 'bienes' para docente y auxiliar
        $permisos = Permission::whereIn('categoria', ['bienes'])->pluck('id');
        if ($permisos->isNotEmpty()) {
            if ($docente) {
                $docente->permissions()->sync($permisos);
            }
            if ($auxiliar) {
                $auxiliar->permissions()->sync($permisos);
            }
        }
    }
}
