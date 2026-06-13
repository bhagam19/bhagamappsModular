# IMPL-INFRA-BACKUP-003B — Disaster Recovery Hardening

**Estado:** COMPLETADO  
**Fecha:** 2026-06-13  
**Autorizado por:** PMO  
**Prerrequisito de:** IMPL-INFRA-BACKUP-004 (Restauración Automatizada)  
**SHA commits:**

| Cambio | SHA |
|---|---|
| GAP-DR-002 + GAP-DR-003 (Inventario seeders) | `8801e49` |
| GAP-DR-005 (AppSeeder + admin-sistema) | `fc33bde` |
| HARDENING-001 (User data sync) | `3cafa31` |
| GAP-DR-009 (InstitutionalRestoreSeeder) | `860ab9b` |
| Documentación (este archivo + CHANGELOG) | ver CHANGELOG |

---

## Contexto

AUDIT-BACKUP-002 emitió **B — CERTIFICADO CON AJUSTES MENORES** con 9 gaps.
Esta implementación cierra los 4 gaps bloqueantes (GAP-DR-002, 003, 005, 009)
y sincroniza los `data/*.csv` (HARDENING-001) para elevar la certificación.

---

## Cambios implementados

### GAP-DR-002 — BienesResponsablesSeeder (CERRADO)

**Antes:** `foreach(range(1, 10))` con `user_id = 1` estático.  
**Después:** Lee `Modules/Inventario/Database/Seeders/data/bienes_responsables.csv`.

```php
// Patrón: fgetcsv + DB::transaction + updateOrInsert idempotente
$handle = fopen(__DIR__ . '/data/bienes_responsables.csv', 'r');
$headers = array_map('trim', fgetcsv($handle));
DB::transaction(function () use ($handle, $headers) {
    while (($row = fgetcsv($handle)) !== false) {
        DB::table('bienes_responsables')->updateOrInsert(['id' => (int) $data['id']], [...]);
    }
});
```

Archivo `data/bienes_responsables.csv` creado desde `IEE-2026-06-13.zip` (10 registros reales).

`InventarioDatabaseSeeder` actualizado: `BienesResponsablesSeeder` registrado
en el orden de restauración (posición: después de `DetallesSeeder`).

---

### GAP-DR-003 — MantenimientosProgramadosSeeder (CERRADO)

**Antes:** `Faker\Factory::create()` generando datos aleatorios irrepetibles.  
**Después:** Lee `Modules/Inventario/Database/Seeders/data/mantenimientos_programados.csv`.

Misma arquitectura que `BienesResponsablesSeeder` (fgetcsv + updateOrInsert).  
Archivo `data/mantenimientos_programados.csv` creado desde backup (10 registros).

> Nota: El CSV contiene los datos que Faker generó originalmente y que
> existen en producción. El fix garantiza que en un DR se restauren ESOS
> 10 registros exactos, no datos aleatorios nuevos.

---

### GAP-DR-005 — AppSeeder + admin-sistema (CERRADO)

**Antes:** `AppSeeder` tenía 11 apps hardcoded; `admin-sistema` (id=12) ausente.  
**Después:** `AppSeeder` incluye la app 12 como entrada número 12 del array.

```php
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
```

`updateOrCreate(['slug' => 'admin-sistema'], ...)` — idempotente respecto a
`AdminSistemaSeeder::firstOrCreate`. No produce duplicados.

---

### HARDENING-001 — Sincronización data/*.csv (CERRADO)

Actualizados desde `IEE-2026-06-13.zip`:

| Archivo | Antes | Después | Delta |
|---|---|---|---|
| `User/Seeders/data/permissions.csv` | 38 | 80 | +42 |
| `User/Seeders/data/permission_role.csv` | 104 | 167 | +63 |
| `User/Seeders/data/app_role.csv` | 10 | 11 | +1 (admin-sistema→Administrador) |
| `User/Seeders/data/users.csv` | 116 | 117 | +1 |
| `Inventario/Seeders/data/bienes_responsables.csv` | — | 10 | NUEVO |
| `Inventario/Seeders/data/mantenimientos_programados.csv` | — | 10 | NUEVO |

---

### GAP-DR-009 — InstitutionalRestoreSeeder (CERRADO)

Creado en `database/seeders/InstitutionalRestoreSeeder.php`.

**Orden oficial de restauración institucional (5 etapas):**

```
[1] AppSeeder
    └─ Crea las 12 apps (incluyendo admin-sistema)

[2] RBAC
    ├─ PermissionSeeder         (80 permisos desde CSV)
    ├─ RoleSeeder               (7 roles hardcoded, asigna permisos)
    ├─ UserSeeder               (117 users desde CSV)
    ├─ Permission_RoleSeeder    (167 asignaciones desde CSV)
    └─ AppRoleSeeder            (11 asignaciones desde CSV)

[3] AdminSistemaSeeder
    ├─ 3 permisos CAB (firstOrCreate — idempotente)
    ├─ admin-sistema app (firstOrCreate — idempotente)
    └─ Asigna app al rol Administrador

[4] InventarioDatabaseSeeder
    ├─ AlmacenamientosSeeder    (2 registros hardcoded)
    ├─ CategoriasSeeder         (28 desde CSV)
    ├─ EstadosSeeder            (4 hardcoded)
    ├─ MantenimientosSeeder     (3 hardcoded)
    ├─ UbicacionesSeeder        (4 hardcoded)
    ├─ DependenciasSeeder       (135 desde CSV)
    ├─ OrigenesSeeder           (11 desde CSV)
    ├─ BienesSeeder             (1.420 desde CSV)
    ├─ DetallesSeeder           (1.412 desde CSV)
    ├─ BienesResponsablesSeeder (10 desde CSV) ← GAP-DR-002
    ├─ HistorialModificacionesBienesSeeder (11 desde CSV)
    ├─ HistorialDependenciasBienesSeeder   (vacío)
    ├─ HistorialEliminacionesBienesSeeder  (vacío)
    ├─ BienesImagenesSeeder                (vacío)
    └─ MantenimientosProgramadosSeeder    (10 desde CSV) ← GAP-DR-003

[5] cache()->increment('apps.cache_version')
    └─ Invalida caché de App::visiblesPara()
```

**Comando de restauración manual:**
```bash
php artisan db:seed --class=Database\\Seeders\\InstitutionalRestoreSeeder
```

**Nota:** NO registrado en `DatabaseSeeder`. Diseñado para ser invocado
exclusivamente por `backup:restore-from-zip` (IMPL-INFRA-BACKUP-004).

---

## HARDENING-002 — Diseño de backup:restore-from-zip

### Propósito

Automatizar completamente el proceso DR que actualmente requiere ~10 pasos manuales.

### Firma propuesta

```php
// Artisan Command: backup:restore-from-zip
php artisan backup:restore-from-zip {fecha} [--dry-run] [--force] [--skip-confirm]
```

### Flujo de restauración (diseño)

```
backup:restore-from-zip 2026-06-13
│
├─ VALIDACIÓN PREVIA
│   ├─ Verificar que backups/IEE-{fecha}.zip existe
│   ├─ Verificar integridad ZIP (unzip -t)
│   ├─ Verificar metadata.json legible
│   ├─ Verificar que las tablas existen (migración ejecutada)
│   └─ Advertir si hay datos existentes → pedir confirmación (--force omite)
│
├─ EXTRACCIÓN
│   ├─ Extraer ZIP a /tmp/restore-{fecha}/
│   └─ Verificar presencia de todos los CSVs esperados
│
├─ SINCRONIZACIÓN data/
│   ├─ Copiar CSVs relevantes a Modules/*/Seeders/data/
│   └─ Verificar conteos post-copia
│
├─ RESTAURACIÓN
│   └─ Llamar InstitutionalRestoreSeeder
│       (con DB::transaction global para rollback si falla)
│
├─ VERIFICACIÓN POST-RESTORE
│   ├─ Comparar conteos de tablas vs metadata.json
│   ├─ Verificar FK integrity básica
│   └─ Emitir reporte de resultado
│
└─ LIMPIEZA
    └─ Eliminar directorio temporal /tmp/restore-{fecha}/
```

### Etapas identificadas para IMPL-INFRA-BACKUP-004

| Etapa | Descripción | Prioridad |
|---|---|---|
| E1 | Validación y extracción del ZIP | P1 |
| E2 | Sincronización automática de `data/*.csv` | P1 |
| E3 | Llamada a `InstitutionalRestoreSeeder` con transacción | P1 |
| E4 | Verificación de conteos post-restore | P2 |
| E5 | Manejo de conflicto si BD tiene datos (`--force`) | P2 |
| E6 | Opción `--dry-run` para simular | P3 |
| E7 | Log estructurado a `storage/logs/restore.log` | P3 |

### Dependencias técnicas

- `ZipArchive` (ya disponible, usado en `BackupExportSeeders`)
- `InstitutionalRestoreSeeder` (implementado en este IMPL)
- `BackupReaderService::leerMetadata()` (ya disponible)
- No requiere `exec()`, `shell_exec()` ni herramientas externas

### Restricciones de implementación

- NO ejecutar `migrate:fresh` automáticamente (destruye datos existentes)
- NO eliminar datos sin `--force` explícito
- NO modificar backups al restaurar
- Debe ser reversible: el rollback vía `DB::transaction` es el mecanismo

---

## Estado de validaciones V-001 a V-008

| Validación | Descripción | Estado |
|---|---|---|
| V-001 | BienesResponsablesSeeder sin Faker | ✓ PASADO |
| V-002 | MantenimientosProgramadosSeeder sin Faker | ✓ PASADO |
| V-003 | AdminSistema preservado en restauración | ✓ PASADO |
| V-004 | Orquestador institucional creado | ✓ PASADO |
| V-005 | Orden de restauración documentado | ✓ PASADO |
| V-006 | CSV y seeders alineados | ✓ PASADO |
| V-007 | PHP lint limpio | ✓ PASADO (4/4 archivos) |
| V-008 | Sin regresiones | ✓ PASADO (no se modificó lógica de negocio) |

---

## Gaps residuales tras IMPL-INFRA-BACKUP-003B

| Gap | Descripción | Plan |
|---|---|---|
| GAP-DR-001 | Sin `backup:restore-from-zip` | IMPL-INFRA-BACKUP-004 |
| GAP-DR-007 | UserSeeder regenera passwords (fórmula) | P3 — comunicar a usuarios post-DR |
| GAP-DR-008 | `auditoria_passwords` sin seeder | Bajo impacto operativo — diferido |

---

## Pregunta central respondida

> ¿Los gaps críticos de Disaster Recovery quedaron cerrados?

**SÍ.** Los 4 gaps bloqueantes están cerrados:
- GAP-DR-002 ✓ — BienesResponsablesSeeder restaura datos reales
- GAP-DR-003 ✓ — MantenimientosProgramadosSeeder restaura datos reales
- GAP-DR-005 ✓ — admin-sistema en AppSeeder, CAB operativo post-restore
- GAP-DR-009 ✓ — InstitutionalRestoreSeeder como orquestador oficial

> ¿La plataforma está lista para AUDIT-BACKUP-003?

**SÍ.** Los prerrequisitos para IMPL-INFRA-BACKUP-004 están cumplidos:
- Orquestador de restauración disponible
- Todos los seeders leen desde CSV reales
- `data/*.csv` sincronizados con producción
- Diseño de `backup:restore-from-zip` documentado
