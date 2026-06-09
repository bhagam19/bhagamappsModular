<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * IMPL-APPS-006 — Legacy Application Registry Cleanup
 *
 * Elimina los 12 registros legacy de la tabla `apps` (IDs 1-12):
 *   - sin slug (NULL)
 *   - habilitada = false
 *   - user_id = 1 (seeder del sistema anterior)
 *   - creados el 2026-06-07 (antes de la implementación del catálogo actual)
 *
 * Efectos de integridad referencial automáticos:
 *   - app_role: ON DELETE CASCADE → se eliminan 8 pivot registros (app_id IN 1,2,3,4)
 *   - roles.app_id: ON DELETE SET NULL → los 7 roles quedan con app_id = NULL
 *   - app_user: ya vacía, sin efecto
 *
 * Origen: AUDIT-APPS-006 (H-001, H-002, H-003)
 * Registros legacy eliminados (backup documental):
 *   1  | User                 | NULL | /usuarios/user       | 0
 *   2  | Inventario           | NULL | /inventario/bienes   | 0
 *   3  | App                  | NULL | /app                 | 0
 *   4  | Biblioteca           | NULL | /biblioteca          | 0
 *   5  | SINAI vs SIMAT       | NULL | /SvS                 | 0
 *   6  | Planeador            | NULL | /planeador           | 0
 *   7  | EduInclusiva         | NULL | /eduInclusiva        | 0
 *   8  | CTE                  | NULL | /cte                 | 0
 *   9  | Creador de Exámenes  | NULL | /creadorExamenes     | 0
 *  10  | Tabletas             | NULL | /prestamoTabletas    | 0
 *  11  | Polla Mundialista    | NULL | /pollaMundialista    | 0
 *  12  | Evaluar para Avanzar | NULL | /evaluarParaAvanzar  | 0
 */
return new class extends Migration
{
    public function up(): void
    {
        // Eliminar apps sin slug — los FK CASCADE/SET NULL se encargan de las relaciones.
        DB::table('apps')->whereNull('slug')->delete();
    }

    public function down(): void
    {
        // Restaurar los 12 registros legacy.
        // Nota: los app_role y roles.app_id no se restauran (operación irreversible segura).
        DB::table('apps')->insert([
            ['id' => 1,  'nombre' => 'User',                 'slug' => null, 'ruta' => '/usuarios/user',       'imagen' => 'vendor/adminlte/dist/img/Apps/users.png',         'habilitada' => false, 'orden' => 99, 'user_id' => 1, 'created_at' => '2026-06-07 12:11:56', 'updated_at' => '2026-06-09 00:31:17'],
            ['id' => 2,  'nombre' => 'Inventario',           'slug' => null, 'ruta' => '/inventario/bienes',   'imagen' => 'vendor/adminlte/dist/img/Apps/inventario.png',    'habilitada' => false, 'orden' => 99, 'user_id' => 1, 'created_at' => '2026-06-07 12:11:56', 'updated_at' => '2026-06-09 00:31:12'],
            ['id' => 3,  'nombre' => 'App',                  'slug' => null, 'ruta' => '/app',                 'imagen' => 'vendor/adminlte/dist/img/Apps/apps.png',          'habilitada' => false, 'orden' => 99, 'user_id' => 1, 'created_at' => '2026-06-07 12:11:56', 'updated_at' => '2026-06-09 00:31:01'],
            ['id' => 4,  'nombre' => 'Biblioteca',           'slug' => null, 'ruta' => '/biblioteca',          'imagen' => 'vendor/adminlte/dist/img/Apps/biblioteca.png',    'habilitada' => false, 'orden' => 99, 'user_id' => 1, 'created_at' => '2026-06-07 12:11:56', 'updated_at' => '2026-06-09 00:30:34'],
            ['id' => 5,  'nombre' => 'SINAI vs SIMAT',       'slug' => null, 'ruta' => '/SvS',                 'imagen' => 'vendor/adminlte/dist/img/Apps/SvS.png',           'habilitada' => false, 'orden' => 99, 'user_id' => 1, 'created_at' => '2026-06-07 12:11:56', 'updated_at' => '2026-06-07 12:11:56'],
            ['id' => 6,  'nombre' => 'Planeador',            'slug' => null, 'ruta' => '/planeador',           'imagen' => 'vendor/adminlte/dist/img/Apps/planeador.png',     'habilitada' => false, 'orden' => 99, 'user_id' => 1, 'created_at' => '2026-06-07 12:11:56', 'updated_at' => '2026-06-07 12:11:56'],
            ['id' => 7,  'nombre' => 'EduInclusiva',         'slug' => null, 'ruta' => '/eduInclusiva',        'imagen' => 'vendor/adminlte/dist/img/Apps/DUA.png',           'habilitada' => false, 'orden' => 99, 'user_id' => 1, 'created_at' => '2026-06-07 12:11:56', 'updated_at' => '2026-06-07 12:11:56'],
            ['id' => 8,  'nombre' => 'CTE',                  'slug' => null, 'ruta' => '/cte',                 'imagen' => 'vendor/adminlte/dist/img/Apps/cteApp.jpg',        'habilitada' => false, 'orden' => 99, 'user_id' => 1, 'created_at' => '2026-06-07 12:11:56', 'updated_at' => '2026-06-07 12:11:56'],
            ['id' => 9,  'nombre' => 'Creador de Exámenes',  'slug' => null, 'ruta' => '/creadorExamenes',     'imagen' => 'vendor/adminlte/dist/img/Apps/examCreator.jpg',   'habilitada' => false, 'orden' => 99, 'user_id' => 1, 'created_at' => '2026-06-07 12:11:56', 'updated_at' => '2026-06-07 12:11:56'],
            ['id' => 10, 'nombre' => 'Tabletas',             'slug' => null, 'ruta' => '/prestamoTabletas',    'imagen' => 'vendor/adminlte/dist/img/Apps/tablet.jpg',        'habilitada' => false, 'orden' => 99, 'user_id' => 1, 'created_at' => '2026-06-07 12:11:56', 'updated_at' => '2026-06-07 12:11:56'],
            ['id' => 11, 'nombre' => 'Polla Mundialista',    'slug' => null, 'ruta' => '/pollaMundialista',    'imagen' => 'vendor/adminlte/dist/img/Apps/pollaMundialista.png', 'habilitada' => false, 'orden' => 99, 'user_id' => 1, 'created_at' => '2026-06-07 12:11:56', 'updated_at' => '2026-06-07 12:11:56'],
            ['id' => 12, 'nombre' => 'Evaluar para Avanzar', 'slug' => null, 'ruta' => '/evaluarParaAvanzar',  'imagen' => 'vendor/adminlte/dist/img/Apps/EvPAv.png',         'habilitada' => false, 'orden' => 99, 'user_id' => 1, 'created_at' => '2026-06-07 12:11:56', 'updated_at' => '2026-06-07 12:11:56'],
        ]);
    }
};
