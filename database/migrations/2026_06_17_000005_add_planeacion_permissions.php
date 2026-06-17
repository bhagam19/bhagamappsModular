<?php

use Illuminate\Database\Migrations\Migration;
use Modules\User\Entities\Permission;
use Modules\User\Entities\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permisos = [
            ['slug' => 'ver-planeacion',       'nombre' => 'ver planeación',       'descripcion' => 'Permite ver la planeación institucional.',              'categoria' => 'planeacion-institucional'],
            ['slug' => 'crear-objetivos',      'nombre' => 'crear objetivos',      'descripcion' => 'Permite crear objetivos institucionales.',              'categoria' => 'planeacion-institucional'],
            ['slug' => 'editar-objetivos',     'nombre' => 'editar objetivos',     'descripcion' => 'Permite editar objetivos institucionales.',             'categoria' => 'planeacion-institucional'],
            ['slug' => 'crear-metas',          'nombre' => 'crear metas',          'descripcion' => 'Permite crear metas institucionales.',                  'categoria' => 'planeacion-institucional'],
            ['slug' => 'editar-metas',         'nombre' => 'editar metas',         'descripcion' => 'Permite editar metas institucionales.',                 'categoria' => 'planeacion-institucional'],
            ['slug' => 'crear-indicadores',    'nombre' => 'crear indicadores',    'descripcion' => 'Permite crear indicadores institucionales.',             'categoria' => 'planeacion-institucional'],
            ['slug' => 'editar-indicadores',   'nombre' => 'editar indicadores',   'descripcion' => 'Permite editar indicadores institucionales.',            'categoria' => 'planeacion-institucional'],
        ];

        foreach ($permisos as $data) {
            Permission::firstOrCreate(['slug' => $data['slug']], $data);
        }

        $slugsAdmin = [
            'ver-planeacion',
            'crear-objetivos', 'editar-objetivos',
            'crear-metas',     'editar-metas',
            'crear-indicadores', 'editar-indicadores',
        ];

        $ids = Permission::whereIn('slug', $slugsAdmin)->pluck('id');

        Role::where('nombre', 'Administrador')->get()
            ->each(fn (Role $rol) => $rol->permissions()->syncWithoutDetaching($ids));
    }

    public function down(): void
    {
        $slugs = [
            'ver-planeacion',
            'crear-objetivos', 'editar-objetivos',
            'crear-metas',     'editar-metas',
            'crear-indicadores', 'editar-indicadores',
        ];

        $ids = Permission::whereIn('slug', $slugs)->pluck('id');
        Role::all()->each(fn (Role $rol) => $rol->permissions()->detach($ids));
        Permission::whereIn('slug', $slugs)->delete();
    }
};
