<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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

        DB::table('roles')->delete();

        DB::table('roles')->insert([
            ['id' => 1, 'nombre' => 'Administrador', 'descripcion' => 'Usuario con acceso completo al sistema.',            'app_id' => $app->id, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nombre' => 'Rectoría',      'descripcion' => 'Orienta todos los procesos en general.',            'app_id' => $app->id, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nombre' => 'Coordinación',  'descripcion' => 'Supervisa procesos académicos o administrativos.',  'app_id' => $app->id, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nombre' => 'Auxiliar',      'descripcion' => 'Apoya todos los procesos académicos o administrativos.', 'app_id' => $app->id, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'nombre' => 'Docente',       'descripcion' => 'Encargado de impartir clases y evaluar a los estudiantes.', 'app_id' => $app->id, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'nombre' => 'Estudiante',    'descripcion' => 'Usuario que accede a contenidos y actividades académicas.', 'app_id' => $app->id, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'nombre' => 'Invitado',      'descripcion' => 'Acceso limitado para pruebas o demostraciones.',   'app_id' => $app->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Obtener los roles ya creados (con app_id)
        $admin = Role::where('nombre', 'Administrador')->where('app_id', $app->id)->first();
        $rector = Role::where('nombre', 'Rectoría')->where('app_id', $app->id)->first();
        $coordinador = Role::where('nombre', 'Coordinación')->where('app_id', $app->id)->first();
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
