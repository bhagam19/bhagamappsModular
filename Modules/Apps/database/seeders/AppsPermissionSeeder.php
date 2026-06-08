<?php

namespace Modules\Apps\database\seeders;

use Illuminate\Database\Seeder;
use Modules\User\Entities\Permission;
use Modules\User\Entities\Role;

class AppsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permiso = Permission::firstOrCreate(
            ['slug' => 'administrar-apps'],
            [
                'nombre'     => 'administrar apps',
                'descripcion'=> 'Permite habilitar/deshabilitar aplicaciones y asignar roles desde el panel de administración de apps.',
                'categoria'  => 'apps',
            ]
        );

        $rolesAdmin = Role::whereIn('nombre', ['Administrador', 'Rector'])->get();

        foreach ($rolesAdmin as $rol) {
            if (! $rol->permissions->contains($permiso->id)) {
                $rol->permissions()->attach($permiso->id);
            }
        }
    }
}
