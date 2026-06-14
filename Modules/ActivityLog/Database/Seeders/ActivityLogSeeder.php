<?php

namespace Modules\ActivityLog\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Apps\Entities\App;
use Modules\User\Entities\Permission;
use Modules\User\Entities\Role;

class ActivityLogSeeder extends Seeder
{
    private const PERMISOS = [
        // LOG-011: solo AdminPrincipal; Gate exige además is_principal=true
        [
            'slug'        => 'ver-activity-log',
            'nombre'      => 'ver activity log',
            'descripcion' => 'Permite ver el registro de auditoría institucional. Requiere es_principal = true.',
            'categoria'   => 'admin-sistema',
        ],
    ];

    public function run(): void
    {
        $admin = Role::where('nombre', 'Administrador')->first();

        foreach (self::PERMISOS as $data) {
            $permiso = Permission::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'nombre'      => $data['nombre'],
                    'descripcion' => $data['descripcion'],
                    'categoria'   => $data['categoria'],
                ]
            );

            if ($admin && !$admin->permissions->contains($permiso->id)) {
                $admin->permissions()->attach($permiso->id);
            }
        }
    }
}
