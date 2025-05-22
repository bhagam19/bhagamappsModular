<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Users\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'usuarios' => [
                'ver usuarios' => 'Permite visualizar la lista de todos los usuarios registrados en el sistema.',
                'crear usuarios' => 'Permite registrar un nuevo usuario en el sistema.',
                'editar usuarios' => 'Permite modificar la información de un usuario existente.',
                'eliminar usuarios' => 'Permite eliminar usuarios del sistema de forma permanente.',
            ],
            'roles' => [
                'ver roles' => 'Permite visualizar todos los roles existentes y sus respectivos permisos.',
                'crear roles' => 'Permite crear nuevos roles con un conjunto de permisos definidos.',
                'editar roles' => 'Permite modificar el nombre o los permisos de un rol existente.',
                'eliminar roles' => 'Permite eliminar un rol del sistema, siempre que no esté en uso.',
                'asignar permisos a roles' => 'Permite asignar o revocar permisos a los distintos roles del sistema.',
            ],
            'permisos' => [
                'ver permisos' => 'Permite consultar la lista completa de permisos disponibles en el sistema.',
                'crear permisos' => 'Permite registrar un nuevo permiso en el sistema.',
                'editar permisos' => 'Permite modificar el nombre, descripción o categoría de un permiso.',
                'eliminar permisos' => 'Permite eliminar un permiso del sistema permanentemente.',
            ],
        ];

        foreach ($permissions as $categoria => $perms) {
            foreach ($perms as $nombre => $descripcion) {
                Permission::firstOrCreate(
                    ['nombre' => $nombre],
                    [
                        'descripcion' => $descripcion,
                        'categoria' => ucfirst($categoria),
                        'slug' => Str::slug($nombre),
                    ]
                );
            }
        }
    }
}
