<?php

use Illuminate\Database\Migrations\Migration;

/**
 * HOTFIX-RBAC-001 — Recuperación de vínculos app_role
 *
 * Causa raíz: cleanup_legacy_apps eliminó por CASCADE los registros de app_role.
 * El AppSeeder no puebla app_role. assign_inventario_app_to_coordinador no encontró
 * las apps con slug porque corrió antes del AppSeeder.
 *
 * Matriz de acceso por slug (dinámica, no depende de IDs fijos):
 *   Administrador → user, inventario, apps
 *   Rector        → user, inventario, apps
 *   Coordinador   → user, inventario
 *   Auxiliar      → inventario
 *   Docente       → inventario
 */
return new class extends Migration
{
    private array $matrix = [
        'Administrador' => ['user', 'inventario', 'apps'],
        'Rector'        => ['user', 'inventario', 'apps'],
        'Coordinador'   => ['user', 'inventario'],
        'Auxiliar'      => ['inventario'],
        'Docente'       => ['inventario'],
    ];

    public function up(): void
    {
        foreach ($this->matrix as $rolNombre => $appSlugs) {
            $roleId = DB::table('roles')->where('nombre', $rolNombre)->value('id');
            if (! $roleId) {
                continue;
            }

            foreach ($appSlugs as $slug) {
                $appId = DB::table('apps')->where('slug', $slug)->value('id');
                if (! $appId) {
                    continue;
                }

                $exists = DB::table('app_role')
                    ->where('app_id', $appId)
                    ->where('role_id', $roleId)
                    ->exists();

                if (! $exists) {
                    DB::table('app_role')->insert([
                        'app_id'     => $appId,
                        'role_id'    => $roleId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->matrix as $rolNombre => $appSlugs) {
            $roleId = DB::table('roles')->where('nombre', $rolNombre)->value('id');
            if (! $roleId) {
                continue;
            }

            foreach ($appSlugs as $slug) {
                $appId = DB::table('apps')->where('slug', $slug)->value('id');
                if (! $appId) {
                    continue;
                }

                DB::table('app_role')
                    ->where('app_id', $appId)
                    ->where('role_id', $roleId)
                    ->delete();
            }
        }
    }
};
