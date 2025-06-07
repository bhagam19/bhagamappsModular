<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Users\Models\Role;
use Modules\Users\Models\Permission;
use Modules\Apps\Entities\Aplicacion; // Asumiendo que este modelo existe

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener la aplicación 'usuarios' para asignar el aplicacion_id
        $aplicacion = Aplicacion::where('slug', 'usuarios')->first();

        if (!$aplicacion) {
            $this->command->error("No se encontró la aplicación con slug 'usuarios'. Crea la aplicación antes de correr este seeder.");
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
                ['nombre' => $rolData['nombre'], 'aplicacion_id' => $aplicacion->id],
                ['descripcion' => $rolData['descripcion']]
            );
        }

        // Obtener los roles ya creados (con aplicacion_id)
        $admin = Role::where('nombre', 'Administrador')->where('aplicacion_id', $aplicacion->id)->first();
        $rector = Role::where('nombre', 'Rector')->where('aplicacion_id', $aplicacion->id)->first();
        $coordinador = Role::where('nombre', 'Coordinador')->where('aplicacion_id', $aplicacion->id)->first();
        $docente = Role::where('nombre', 'Docente')->where('aplicacion_id', $aplicacion->id)->first();
        $auxiliar = Role::where('nombre', 'Auxiliar')->where('aplicacion_id', $aplicacion->id)->first();

        // Asignar permisos si existen
        $permisos = Permission::pluck('id');
        if ($permisos->isNotEmpty()) {
            $admin->permissions()->sync($permisos);
            $rector->permissions()->sync($permisos);
        }

        // Permisos categoría 'usuarios' y 'bienes' para coordinador
        $permisos = Permission::whereIn('categoria', ['usuarios', 'bienes'])->pluck('id');
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
