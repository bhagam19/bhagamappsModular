<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Users\Models\Role;
use Modules\Users\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['descripcion' => 'Usuario con acceso completo al sistema.']
        );

        $rector = Role::firstOrCreate(
            ['nombre' => 'Rector'],
            ['descripcion' => 'Orienta todos los procesos en general.']
        );

        $coordinador = Role::firstOrCreate(
            ['nombre' => 'Coordinador'],
            ['descripcion' => 'Supervisa procesos académicos o administrativos.']
        );

        $auxiliar = Role::firstOrCreate(
            ['nombre' => 'Auxiliar'],
            ['descripcion' => 'Apoya todos los procesos académicos o administrativos.']
        );

        $docente = Role::firstOrCreate(
            ['nombre' => 'Docente'],
            ['descripcion' => 'Encargado de impartir clases y evaluar a los estudiantes.']
        );

        $estudiante = Role::firstOrCreate(
            ['nombre' => 'Estudiante'],
            ['descripcion' => 'Usuario que accede a contenidos y actividades académicas.']
        );

        $invitado = Role::firstOrCreate(
            ['nombre' => 'Invitado'],
            ['descripcion' => 'Acceso limitado para pruebas o demostraciones.']
        );

        // Si ya existen permisos, asignarlos
        $permisos = Permission::pluck('id');
        if ($permisos->isNotEmpty()) {
            $admin->permissions()->sync($permisos);
            $rector->permissions()->sync($permisos);
        }

        // Obtener solo los permisos de la categoría 'usuarios' y 'bienes' y asignarlos al coordinador
        $permisos = Permission::whereIn('categoria', ['usuarios', 'bienes'])->pluck('id');
        if ($permisos->isNotEmpty()) {
            $coordinador->permissions()->sync($permisos);
        }

        // Obtener solo los permisos de la categoría 'bienes' y asignarlos a docentes y auxiliares
        $permisos = Permission::whereIn('categoria', ['bienes'])->pluck('id');
        if ($permisos->isNotEmpty()) {
            $docente->permissions()->sync($permisos);
            $auxiliar->permissions()->sync($permisos);
        }
    }
}
