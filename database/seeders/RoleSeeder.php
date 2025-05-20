<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::create([
            'nombre' => 'Administrador',
            'descripcion' => 'Usuario con acceso completo al sistema.',
        ]);

        $rector = Role::create([
            'nombre' => 'Rector',
            'descripcion' => 'Orienta todos los procesos en general.',
        ]);

        $coordinador = Role::create([
            'nombre' => 'Coordinador',
            'descripcion' => 'Supervisa procesos académicos o administrativos.',
        ]);

        $auxiliar = Role::create([
            'nombre' => 'Auxiliar',
            'descripcion' => 'Apoya todos los procesos académicos o administrativos.',
        ]);

        $docente = Role::create([
            'nombre' => 'Docente',
            'descripcion' => 'Encargado de impartir clases y evaluar a los estudiantes.',
        ]);

        $estudiante = Role::create([
            'nombre' => 'Estudiante',
            'descripcion' => 'Usuario que accede a contenidos y actividades académicas.',
        ]);        

        $invitado = Role::create([
            'nombre' => 'Invitado',
            'descripcion' => 'Acceso limitado para pruebas o demostraciones.',
        ]);

        // Asignar todos los permisos al Administrador y al rector
        $admin->permissions()->sync(Permission::pluck('id'));
        $rector->permissions()->sync(Permission::pluck('id'));

        // Obtener solo los permisos de la categoría 'usuarios'
        $permisosUsuarios = Permission::where('categoria', 'usuarios')->pluck('id');
        // Asignar esos permisos al coordinador
        $coordinador->permissions()->sync($permisosUsuarios);
    }
}
