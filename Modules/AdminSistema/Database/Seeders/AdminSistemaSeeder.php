<?php

namespace Modules\AdminSistema\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Apps\Entities\App;
use Modules\User\Entities\Permission;
use Modules\User\Entities\Role;

class AdminSistemaSeeder extends Seeder
{
    private const PERMISOS = [
        [
            'slug'        => 'ver-backups',
            'nombre'      => 'ver backups',
            'descripcion' => 'Permite ver el listado de respaldos y sus detalles.',
            'categoria'   => 'admin-sistema',
        ],
        [
            'slug'        => 'generar-backups',
            'nombre'      => 'generar backups',
            'descripcion' => 'Permite generar un respaldo manual del sistema.',
            'categoria'   => 'admin-sistema',
        ],
        [
            'slug'        => 'descargar-backups',
            'nombre'      => 'descargar backups',
            'descripcion' => 'Permite descargar el archivo ZIP de un respaldo.',
            'categoria'   => 'admin-sistema',
        ],
        [
            'slug'        => 'ver-backup-drive',
            'nombre'      => 'ver backup drive',
            'descripcion' => 'Permite ver el estado y el historial de sincronización con Google Drive.',
            'categoria'   => 'admin-sistema',
        ],
        [
            'slug'        => 'sincronizar-backup-drive',
            'nombre'      => 'sincronizar backup drive',
            'descripcion' => 'Permite ejecutar una sincronización manual del último respaldo a Google Drive.',
            'categoria'   => 'admin-sistema',
        ],
    ];

    public function run(): void
    {
        $admin = Role::where('nombre', 'Administrador')->first();

        // Crear permisos y asignar al Administrador
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

        // Registrar la aplicación en el catálogo
        $app = App::firstOrCreate(
            ['slug' => 'admin-sistema'],
            [
                'nombre'      => 'Administración del Sistema',
                'ruta'        => '/admin/backups',
                'descripcion' => 'Centro de administración del sistema: backups, monitoreo y configuración.',
                'imagen'      => 'vendor/adminlte/dist/img/Apps/apps.png',
                'icono'       => 'fas fa-server',
                'color'       => '#6c757d',
                'orden'       => 0,
                'habilitada'  => true,
            ]
        );

        // Asignar la app únicamente al rol Administrador
        if ($admin && !$app->roles->contains($admin->id)) {
            $app->roles()->attach($admin->id);
        }

        // Invalidar caché de apps
        cache()->increment('apps.cache_version');
    }
}
