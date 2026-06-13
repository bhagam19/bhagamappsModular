<?php

namespace Modules\Apps\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Apps\Entities\App;

class AppSeeder extends Seeder
{
    public function run(): void
    {
        $apps = [
            [
                'nombre'      => 'Usuarios',
                'slug'        => 'user',
                'ruta'        => '/user',
                'descripcion' => 'Gestión de usuarios, roles y permisos.',
                'imagen'      => 'vendor/adminlte/dist/img/Apps/users.png',
                'icono'       => 'fas fa-users',
                'color'       => '#3a3f8c',
                'orden'       => 1,
                'habilitada'  => true,
            ],
            [
                'nombre'      => 'Inventario',
                'slug'        => 'inventario',
                'ruta'        => '/inventario',
                'descripcion' => 'Inventario de bienes institucionales.',
                'imagen'      => 'vendor/adminlte/dist/img/Apps/inventario.png',
                'icono'       => 'fas fa-boxes',
                'color'       => '#28a745',
                'orden'       => 2,
                'habilitada'  => true,
            ],
            [
                'nombre'      => 'Aplicaciones',
                'slug'        => 'apps',
                'ruta'        => '/apps/admin',
                'descripcion' => 'Administración del catálogo de aplicaciones.',
                'imagen'      => 'vendor/adminlte/dist/img/Apps/apps.png',
                'icono'       => 'fas fa-th-large',
                'color'       => '#6610f2',
                'orden'       => 3,
                'habilitada'  => true,
            ],
            [
                'nombre'      => 'Biblioteca',
                'slug'        => 'biblioteca',
                'ruta'        => '/biblioteca',
                'descripcion' => 'Gestión de recursos bibliográficos.',
                'imagen'      => 'vendor/adminlte/dist/img/Apps/biblioteca.png',
                'icono'       => 'fas fa-book',
                'color'       => '#fd7e14',
                'orden'       => 4,
                'habilitada'  => false,
            ],
            [
                'nombre'      => 'SINAI vs SIMAT',
                'slug'        => 'sinai-vs-simat',
                'ruta'        => '/SvS',
                'descripcion' => 'Comparativo SINAI - SIMAT.',
                'imagen'      => 'vendor/adminlte/dist/img/Apps/SvS.png',
                'icono'       => 'fas fa-balance-scale',
                'color'       => '#17a2b8',
                'orden'       => 10,
                'habilitada'  => false,
            ],
            [
                'nombre'      => 'Planeador',
                'slug'        => 'planeador',
                'ruta'        => '/planeador',
                'descripcion' => 'Herramienta de planeación académica.',
                'imagen'      => 'vendor/adminlte/dist/img/Apps/planeador.png',
                'icono'       => 'fas fa-calendar-alt',
                'color'       => '#20c997',
                'orden'       => 11,
                'habilitada'  => false,
            ],
            [
                'nombre'      => 'EduInclusiva',
                'slug'        => 'edu-inclusiva',
                'ruta'        => '/eduInclusiva',
                'descripcion' => 'Herramientas de educación inclusiva (DUA).',
                'imagen'      => 'vendor/adminlte/dist/img/Apps/DUA.png',
                'icono'       => 'fas fa-universal-access',
                'color'       => '#e83e8c',
                'orden'       => 12,
                'habilitada'  => false,
            ],
            [
                'nombre'      => 'CTE',
                'slug'        => 'cte',
                'ruta'        => '/cte',
                'descripcion' => 'Comisión de Trabajo y Evaluación.',
                'imagen'      => 'vendor/adminlte/dist/img/Apps/cteApp.jpg',
                'icono'       => 'fas fa-chalkboard-teacher',
                'color'       => '#6c757d',
                'orden'       => 13,
                'habilitada'  => false,
            ],
            [
                'nombre'      => 'Creador de Exámenes',
                'slug'        => 'creador-examenes',
                'ruta'        => '/creadorExamenes',
                'descripcion' => 'Herramienta para crear y gestionar exámenes.',
                'imagen'      => 'vendor/adminlte/dist/img/Apps/examCreator.jpg',
                'icono'       => 'fas fa-file-alt',
                'color'       => '#007bff',
                'orden'       => 14,
                'habilitada'  => false,
            ],
            [
                'nombre'      => 'Préstamo Tabletas',
                'slug'        => 'prestamo-tabletas',
                'ruta'        => '/prestamoTabletas',
                'descripcion' => 'Control de préstamo de dispositivos.',
                'imagen'      => 'vendor/adminlte/dist/img/Apps/tablet.jpg',
                'icono'       => 'fas fa-tablet-alt',
                'color'       => '#343a40',
                'orden'       => 15,
                'habilitada'  => false,
            ],
            [
                'nombre'      => 'Evaluar para Avanzar',
                'slug'        => 'evaluar-para-avanzar',
                'ruta'        => '/evaluarParaAvanzar',
                'descripcion' => 'Seguimiento a pruebas diagnósticas.',
                'imagen'      => 'vendor/adminlte/dist/img/Apps/EvPAv.png',
                'icono'       => 'fas fa-chart-line',
                'color'       => '#ffc107',
                'orden'       => 16,
                'habilitada'  => false,
            ],
            [
                'nombre'      => 'Administración del Sistema',
                'slug'        => 'admin-sistema',
                'ruta'        => '/admin/backups',
                'descripcion' => 'Centro de administración del sistema: backups, monitoreo y configuración.',
                'imagen'      => 'vendor/adminlte/dist/img/Apps/apps.png',
                'icono'       => 'fas fa-server',
                'color'       => '#6c757d',
                'orden'       => 0,
                'habilitada'  => true,
            ],
        ];

        foreach ($apps as $data) {
            App::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
