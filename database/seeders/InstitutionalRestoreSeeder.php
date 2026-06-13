<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AdminSistema\Database\Seeders\AdminSistemaSeeder;
use Modules\Apps\database\seeders\AppSeeder;
use Modules\Inventario\Database\Seeders\InventarioDatabaseSeeder;
use Modules\User\Database\Seeders\AppRoleSeeder;
use Modules\User\Database\Seeders\Permission_RoleSeeder;
use Modules\User\Database\Seeders\PermissionSeeder;
use Modules\User\Database\Seeders\RoleSeeder;
use Modules\User\Database\Seeders\UserSeeder;

/**
 * Orquestador oficial de restauración institucional.
 *
 * Define el orden canónico de restauración del Snapshot Institucional ZIP.
 * Debe ejecutarse DESPUÉS de:
 *   1. php artisan migrate          (crea el esquema completo)
 *   2. Copiar CSVs del ZIP a data/  (actualizar Seeders/data/ con el backup)
 *
 * NO llamado por DatabaseSeeder. Será invocado por backup:restore-from-zip
 * (IMPL-INFRA-BACKUP-004) una vez implementado.
 *
 * Para restauración manual:
 *   php artisan db:seed --class=Database\\Seeders\\InstitutionalRestoreSeeder
 *
 * Requisitos antes de ejecutar:
 *   - Módulos/User/Database/Seeders/data/*.csv actualizados desde el ZIP
 *   - Módulos/Inventario/Database/Seeders/data/*.csv actualizados desde el ZIP
 */
class InstitutionalRestoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  IEE — Restauración Institucional');
        $this->command->info('  Orden oficial IMPL-INFRA-BACKUP-003B');
        $this->command->info('═══════════════════════════════════════════════════════');

        // ── ETAPA 1: Catálogo de aplicaciones ────────────────────────────────
        // Debe ejecutarse primero; RoleSeeder depende de que la app 'user' exista.
        // AppSeeder incluye admin-sistema (GAP-DR-005 resuelto).
        $this->command->info('[1/5] Apps...');
        $this->call(AppSeeder::class);

        // ── ETAPA 2: RBAC — permisos, roles, usuarios, asignaciones ──────────
        // Orden estricto por dependencias FK:
        //   permissions → roles (necesita apps) → users (necesita roles)
        //   → permission_role → app_role
        $this->command->info('[2/5] RBAC (permissions → roles → users → permission_role → app_role)...');
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(Permission_RoleSeeder::class);
        $this->call(AppRoleSeeder::class);

        // ── ETAPA 3: AdminSistema — permisos CAB y asignación app ────────────
        // firstOrCreate: idempotente, no duplica si AppSeeder ya creó admin-sistema.
        // Asigna los 3 permisos de backups al rol Administrador.
        $this->command->info('[3/5] AdminSistema (permisos CAB + app_role admin-sistema)...');
        $this->call(AdminSistemaSeeder::class);

        // ── ETAPA 4: Inventario completo ──────────────────────────────────────
        // Orden interno gestionado por InventarioDatabaseSeeder:
        //   almacenamientos → categorias → estados → mantenimientos → ubicaciones
        //   → dependencias → origenes → bienes → detalles → bienes_responsables
        //   → historiales → mantenimientos_programados
        $this->command->info('[4/5] Inventario (catálogos + bienes + responsables + historiales)...');
        $this->call(InventarioDatabaseSeeder::class);

        // ── ETAPA 5: Invalidar caché ──────────────────────────────────────────
        $this->command->info('[5/5] Invalidando caché de apps...');
        cache()->increment('apps.cache_version');

        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  Restauración institucional completada.');
        $this->command->info('  Verificar: php artisan route:list | grep admin');
        $this->command->info('═══════════════════════════════════════════════════════');
    }
}
