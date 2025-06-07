<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Modules\Users\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
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
            'bienes' => [
                'ver bienes' => 'Permite visualizar la lista de todos los bienes registrados en el sistema.',
                'crear bienes' => 'Permite registrar un nuevo bien en el sistema.',
                'editar bienes' => 'Permite modificar la información de un bien existente.',
                'eliminar bienes' => 'Permite eliminar bienes del sistema de forma permanente.',
                'aprobar bienes' => 'Permite aprobar o rechazar bienes que están pendientes de revisión.',
                'ver historial bienes' => 'Permite consultar el historial de modificaciones y ubicaciones de los bienes.',
                'asignar responsables a bienes' => 'Permite asignar o cambiar los custodios o responsables de un bien.',
                'ver imagenes de bienes' => 'Permite visualizar la galería de imágenes asociadas a un bien.',
            ],
            'aprobaciones pendientes' => [
                'ver aprobaciones pendientes bienes' => 'Permite visualizar la lista de bienes o modificaciones que están pendientes de aprobación.',
                'aprobar pendientes bienes' => 'Permite aprobar o rechazar las modificaciones o nuevos bienes que están pendientes.',
                'editar aprobaciones pendientes bienes' => 'Permite modificar la información relacionada con una aprobación pendiente antes de su resolución.',
                'eliminar aprobaciones pendientes bienes' => 'Permite eliminar registros de aprobaciones pendientes del sistema.',
            ],
            'actas de entrega' => [
                'ver actas de entrega' => 'Permite visualizar las actas de entrega generadas en el sistema.',
            ],
        ];

        foreach ($permissions as $categoria => $permisos) {
            foreach ($permisos as $nombre => $descripcion) {
                DB::table('permissions')->updateOrInsert(
                    ['nombre' => $nombre],
                    [
                        'slug' => Str::slug($nombre, '-'),
                        'descripcion' => $descripcion,
                        'categoria' => $categoria,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}