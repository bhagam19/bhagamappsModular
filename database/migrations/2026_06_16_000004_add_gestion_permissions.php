<?php

use Illuminate\Database\Migrations\Migration;
use Modules\User\Entities\Permission;
use Modules\User\Entities\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permisos = [
            ['slug' => 'ver-gestiones',       'nombre' => 'ver gestiones',       'descripcion' => 'Permite ver la estructura institucional de gestiones.',     'categoria' => 'gestion-institucional'],
            ['slug' => 'crear-gestiones',     'nombre' => 'crear gestiones',     'descripcion' => 'Permite crear gestiones institucionales.',                  'categoria' => 'gestion-institucional'],
            ['slug' => 'editar-gestiones',    'nombre' => 'editar gestiones',    'descripcion' => 'Permite editar gestiones institucionales.',                 'categoria' => 'gestion-institucional'],
            ['slug' => 'ver-procesos',        'nombre' => 'ver procesos',        'descripcion' => 'Permite ver los procesos institucionales.',                 'categoria' => 'gestion-institucional'],
            ['slug' => 'crear-procesos',      'nombre' => 'crear procesos',      'descripcion' => 'Permite crear procesos institucionales.',                  'categoria' => 'gestion-institucional'],
            ['slug' => 'editar-procesos',     'nombre' => 'editar procesos',     'descripcion' => 'Permite editar procesos institucionales.',                 'categoria' => 'gestion-institucional'],
            ['slug' => 'ver-componentes',     'nombre' => 'ver componentes',     'descripcion' => 'Permite ver los componentes institucionales.',              'categoria' => 'gestion-institucional'],
            ['slug' => 'crear-componentes',   'nombre' => 'crear componentes',   'descripcion' => 'Permite crear componentes institucionales.',               'categoria' => 'gestion-institucional'],
            ['slug' => 'editar-componentes',  'nombre' => 'editar componentes',  'descripcion' => 'Permite editar componentes institucionales.',              'categoria' => 'gestion-institucional'],
        ];

        foreach ($permisos as $data) {
            Permission::firstOrCreate(['slug' => $data['slug']], $data);
        }

        $slugsAdmin = [
            'ver-gestiones', 'crear-gestiones', 'editar-gestiones',
            'ver-procesos', 'crear-procesos', 'editar-procesos',
            'ver-componentes', 'crear-componentes', 'editar-componentes',
        ];

        $ids = Permission::whereIn('slug', $slugsAdmin)->pluck('id');

        Role::where('nombre', 'Administrador')->get()
            ->each(fn (Role $rol) => $rol->permissions()->syncWithoutDetaching($ids));
    }

    public function down(): void
    {
        $slugs = [
            'ver-gestiones', 'crear-gestiones', 'editar-gestiones',
            'ver-procesos', 'crear-procesos', 'editar-procesos',
            'ver-componentes', 'crear-componentes', 'editar-componentes',
        ];

        $ids = Permission::whereIn('slug', $slugs)->pluck('id');
        Role::all()->each(fn (Role $rol) => $rol->permissions()->detach($ids));
        Permission::whereIn('slug', $slugs)->delete();
    }
};
