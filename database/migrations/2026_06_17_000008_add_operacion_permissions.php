<?php

use Illuminate\Database\Migrations\Migration;
use Modules\User\Entities\Permission;
use Modules\User\Entities\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permisos = [
            ['slug' => 'ver-operacion',       'nombre' => 'ver operación',        'descripcion' => 'Permite ver la operación institucional.',              'categoria' => 'operacion-institucional'],
            ['slug' => 'crear-actividades',   'nombre' => 'crear actividades',    'descripcion' => 'Permite crear actividades institucionales.',            'categoria' => 'operacion-institucional'],
            ['slug' => 'editar-actividades',  'nombre' => 'editar actividades',   'descripcion' => 'Permite editar actividades institucionales.',           'categoria' => 'operacion-institucional'],
            ['slug' => 'crear-tareas',        'nombre' => 'crear tareas',         'descripcion' => 'Permite crear tareas institucionales.',                 'categoria' => 'operacion-institucional'],
            ['slug' => 'editar-tareas',       'nombre' => 'editar tareas',        'descripcion' => 'Permite editar tareas institucionales.',                'categoria' => 'operacion-institucional'],
        ];

        foreach ($permisos as $data) {
            Permission::firstOrCreate(['slug' => $data['slug']], $data);
        }

        $slugsAdmin = [
            'ver-operacion',
            'crear-actividades', 'editar-actividades',
            'crear-tareas',      'editar-tareas',
        ];

        $ids = Permission::whereIn('slug', $slugsAdmin)->pluck('id');

        Role::where('nombre', 'Administrador')->get()
            ->each(fn (Role $rol) => $rol->permissions()->syncWithoutDetaching($ids));
    }

    public function down(): void
    {
        $slugs = [
            'ver-operacion',
            'crear-actividades', 'editar-actividades',
            'crear-tareas',      'editar-tareas',
        ];

        $ids = Permission::whereIn('slug', $slugs)->pluck('id');
        Role::all()->each(fn (Role $rol) => $rol->permissions()->detach($ids));
        Permission::whereIn('slug', $slugs)->delete();
    }
};
