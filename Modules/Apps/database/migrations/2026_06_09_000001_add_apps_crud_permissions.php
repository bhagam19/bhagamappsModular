<?php

use Illuminate\Database\Migrations\Migration;
use Modules\User\Entities\Permission;
use Modules\User\Entities\Role;

return new class extends Migration
{
    public function up(): void
    {
        $nuevos = [
            [
                'slug'        => 'ver-apps',
                'nombre'      => 'ver apps',
                'descripcion' => 'Permite acceder al panel de administración de aplicaciones.',
                'categoria'   => 'apps',
            ],
            [
                'slug'        => 'crear-apps',
                'nombre'      => 'crear apps',
                'descripcion' => 'Permite registrar una nueva aplicación en el catálogo.',
                'categoria'   => 'apps',
            ],
            [
                'slug'        => 'editar-apps',
                'nombre'      => 'editar apps',
                'descripcion' => 'Permite modificar los datos de una aplicación existente.',
                'categoria'   => 'apps',
            ],
            [
                'slug'        => 'eliminar-apps',
                'nombre'      => 'eliminar apps',
                'descripcion' => 'Permite eliminar una aplicación del catálogo de forma permanente.',
                'categoria'   => 'apps',
            ],
        ];

        foreach ($nuevos as $data) {
            Permission::firstOrCreate(['slug' => $data['slug']], $data);
        }

        $slugsApps = ['ver-apps', 'crear-apps', 'editar-apps', 'eliminar-apps', 'administrar-apps'];
        $permissionIds = Permission::whereIn('slug', $slugsApps)->pluck('id');

        Role::whereIn('nombre', ['Administrador', 'Rector', 'Coordinador'])->get()
            ->each(fn (Role $rol) => $rol->permissions()->syncWithoutDetaching($permissionIds));
    }

    public function down(): void
    {
        $slugs = ['ver-apps', 'crear-apps', 'editar-apps', 'eliminar-apps'];
        $ids   = Permission::whereIn('slug', $slugs)->pluck('id');

        Role::all()->each(fn (Role $rol) => $rol->permissions()->detach($ids));

        Permission::whereIn('slug', $slugs)->delete();
    }
};
