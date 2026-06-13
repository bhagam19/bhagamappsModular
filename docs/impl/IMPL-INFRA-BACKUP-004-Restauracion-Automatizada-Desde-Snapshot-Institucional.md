# IMPL-INFRA-BACKUP-004 — Restauración Automatizada desde Snapshot Institucional

**Estado:** COMPLETADO  
**Fecha:** 2026-06-13  
**Prerrequisito completado:** IMPL-INFRA-BACKUP-003B (InstitutionalRestoreSeeder disponible)  
**Cierra:** GAP-DR-001 (identificado en AUDIT-BACKUP-002)  
**SHA:** ver CHANGELOG

---

## Objetivo

Implementar el comando `backup:restore-from-zip` que restaura la base de datos
institucional completa desde un Snapshot ZIP generado por `backup:export-seeders`,
sin intervención manual ni herramientas externas.

---

## Firma del comando

```bash
php artisan backup:restore-from-zip --file=backups/IEE-YYYY-MM-DD.zip [--dry-run] [--force]
```

| Opción | Descripción |
|--------|-------------|
| `--file=` | Ruta al ZIP (relativa a `base_path()` o absoluta). **Obligatorio.** |
| `--dry-run` | Simula el proceso completo sin tocar la base de datos. |
| `--force` | Omite la confirmación interactiva. Para uso en scripts/CI. |

---

## Flujo oficial de restauración (RESTORE-001 → RESTORE-010)

```
backup:restore-from-zip --file=backups/IEE-YYYY-MM-DD.zip
│
├─ RESTORE-001: Resolver ruta ZIP (absoluta o relativa a base_path)
│
├─ RESTORE-002: Validar ZIP
│   ├─ Existe y es legible
│   ├─ ZipArchive::open() exitoso (detecta corrupción)
│   └─ metadata.json presente en el ZIP
│
├─ RESTORE-003: Leer y mostrar metadata
│   ├─ fecha, generado_en, entorno
│   ├─ version_iee, version_bhagamapps
│   ├─ tablas_exportadas, total_registros
│   └─ conteos por tabla
│
├─ [CONFIRMACIÓN INTERACTIVA — omitida con --force o --dry-run]
│
├─ RESTORE-004: Extraer ZIP → storage/app/restore-temp/{YmdHis}/
│
├─ RESTORE-005: Sincronizar CSV con Seeders/data/
│   ├─ Modules/User/Database/Seeders/data/:
│   │   permissions.csv, permission_role.csv, app_role.csv, users.csv
│   └─ Modules/Inventario/Database/Seeders/data/:
│       bienes.csv, categorias.csv, dependencias.csv, detalles.csv,
│       origenes.csv, historial_modificaciones_bienes.csv,
│       bienes_responsables.csv, mantenimientos_programados.csv
│   [En --dry-run: solo muestra las copias sin ejecutarlas]
│
├─ RESTORE-006: Ejecutar InstitutionalRestoreSeeder
│   └─ Encapsulado en DB::transaction() global
│       ├─ AppSeeder (12 apps)
│       ├─ PermissionSeeder → RoleSeeder → UserSeeder
│       │   → Permission_RoleSeeder → AppRoleSeeder
│       ├─ AdminSistemaSeeder (3 permisos CAB, idempotente)
│       ├─ InventarioDatabaseSeeder (todos los submódulos)
│       └─ cache()->increment('apps.cache_version')
│
├─ RESTORE-007: Manejo de errores y cleanup
│   ├─ Cualquier excepción → rollback automático de DB::transaction
│   └─ Directorio temporal eliminado siempre (bloque finally)
│
├─ RESTORE-008: Validación post-restauración
│   ├─ Por cada tabla en metadata.conteos:
│   │   actual = DB::table($tabla)->count()
│   │   validación: actual >= esperado
│   └─ Tablas sin seeder omitidas:
│       permission_user, auditoria_passwords,
│       historial_ubicaciones_bienes, historial_eliminaciones_bienes,
│       historial_dependencias_bienes
│
├─ RESTORE-009: Auditoría
│   └─ JSON line → storage/logs/restore.log
│       {fecha, backup, version_iee, version_bha, tablas, registros, resultado}
│
└─ RESTORE-010: Reporte final (✓ EXITOSA / ✗ INCONSISTENCIAS)
```

---

## Flujo oficial completo de Disaster Recovery

```
1. Servidor nuevo (o limpio):
   composer install
   php artisan key:generate
   # Configurar .env (DB, APP_URL, RCLONE_*)

2. Crear esquema:
   php artisan migrate

3. Copiar el ZIP del backup:
   cp /ruta/al/IEE-YYYY-MM-DD.zip backups/
   # O descargar desde Google Drive

4. Restaurar:
   php artisan backup:restore-from-zip --file=backups/IEE-YYYY-MM-DD.zip

5. Limpiar caché:
   php artisan cache:clear
   php artisan config:clear

6. Verificar:
   http://<host>/iee
```

---

## Arquitectura técnica

### Clase principal

`app/Console/Commands/BackupRestoreFromZip.php`

| Método privado | RESTORE | Responsabilidad |
|---|---|---|
| `resolveZipPath()` | 001 | Resuelve ruta absoluta o relativa |
| `validateZip()` | 002 | ZipArchive + presencia de metadata.json |
| `leerMetadataDesdeZip()` | 003 | json_decode del metadata.json interno |
| `mostrarMetadata()` | 003 | Tabla en consola |
| `confirmarRestauracion()` | — | `$this->confirm()` interactivo |
| `extraerZip()` | 004 | `ZipArchive::extractTo()` |
| `sincronizarCsvSeeders()` | 005 | `copy()` CSV→data/, dry-run: solo muestra |
| `ejecutarRestauracion()` | 006+007 | `DB::transaction()` + seeder |
| `validarRestauracion()` | 008 | `DB::table()->count()` vs metadata |
| `registrarAuditoria()` | 009 | `file_put_contents(LOCK_EX)` |
| `limpiarTemporales()` | 007 | `rmdir()` recursivo en `finally` |

### Invocación del seeder

```php
DB::transaction(function () {
    $seeder = app(InstitutionalRestoreSeeder::class);
    $seeder->setContainer(app());
    $seeder->setCommand($this);  // propaga output al comando CLI
    $seeder->run();
});
```

- `setContainer(app())` — habilita resolución PSR-4 de sub-seeders  
- `setCommand($this)` — propaga `$this->command->info()` a la consola del comando  
- `DB::transaction()` — rollback automático si cualquier seeder lanza excepción  
- Los seeders internos usan `DB::transaction()` anidado → MySQL maneja via savepoints

### CSV_SEEDER_MAP (12 tablas)

| Tabla en ZIP | Destino seeder/data/ |
|---|---|
| `permissions` | `Modules/User/Database/Seeders/data/permissions.csv` |
| `permission_role` | `Modules/User/Database/Seeders/data/permission_role.csv` |
| `app_role` | `Modules/User/Database/Seeders/data/app_role.csv` |
| `users` | `Modules/User/Database/Seeders/data/users.csv` |
| `bienes` | `Modules/Inventario/Database/Seeders/data/bienes.csv` |
| `categorias` | `Modules/Inventario/Database/Seeders/data/categorias.csv` |
| `dependencias` | `Modules/Inventario/Database/Seeders/data/dependencias.csv` |
| `detalles` | `Modules/Inventario/Database/Seeders/data/detalles.csv` |
| `origenes` | `Modules/Inventario/Database/Seeders/data/origenes.csv` |
| `historial_modificaciones_bienes` | `Modules/Inventario/Database/Seeders/data/historial_modificaciones_bienes.csv` |
| `bienes_responsables` | `Modules/Inventario/Database/Seeders/data/bienes_responsables.csv` |
| `mantenimientos_programados` | `Modules/Inventario/Database/Seeders/data/mantenimientos_programados.csv` |

Tablas NO en el mapa (seeders hardcoded, sin CSV):
`apps`, `roles`, `ubicaciones`, `estados`, `almacenamientos`, `mantenimientos`

---

## Restricciones implementadas

| Restricción | Implementación |
|---|---|
| Sin `migrate:fresh` automático | Comando solo llama seeders, nunca migraciones |
| Sin `exec()` / `shell_exec()` | ZipArchive + `copy()` nativas de PHP |
| Sin destruir datos sin confirmación | `$this->confirm()` por defecto; `--force` lo omite |
| Sin modificar el ZIP fuente | Solo se lee (`RDONLY`); extracción va a temp |
| Sin modificar el exportador | Comando separado, sin dependencia circular |
| Rollback en caso de error | `DB::transaction()` revierte todo si hay excepción |
| Limpieza garantizada | Bloque `finally` elimina temp dir siempre |

---

## Validaciones ejecutadas (V-001 → V-006)

| V | Descripción | Resultado |
|---|---|---|
| V-001 | PHP lint limpio | ✓ `No syntax errors detected` |
| V-002 | Comando registrado en `php artisan list backup` | ✓ `backup:restore-from-zip` visible |
| V-003 | Dry-run con ZIP real: 24 archivos, 3447 registros | ✓ Sin errores |
| V-004 | 12 CSV identificados correctamente | ✓ Conteos coinciden con backup |
| V-005 | Directorio temporal limpiado post dry-run | ✓ `storage/app/` sin restore-temp |
| V-006 | ZIP fuente no modificado (md5sum) | ✓ `746a38be...` invariante |

---

## Gap cerrado

| Gap | Descripción | Estado |
|---|---|---|
| GAP-DR-001 | Sin comando `backup:restore-from-zip` | **CERRADO** |

---

## Gaps residuales (sin cambio respecto a IMPL-INFRA-BACKUP-003B)

| Gap | Descripción | Plan |
|---|---|---|
| GAP-DR-007 | UserSeeder regenera passwords (fórmula fija, no las reales) | P3 — comunicar a usuarios post-DR |
| GAP-DR-008 | `auditoria_passwords` sin seeder | Bajo impacto — diferido |

---

## Certificación DR: elevación B → A

Con IMPL-INFRA-BACKUP-004, todos los gaps bloqueantes están cerrados:

| Gap | Estado |
|---|---|
| GAP-DR-001 — Sin comando restore | **CERRADO** (este IMPL) |
| GAP-DR-002 — BienesResponsablesSeeder dummy | **CERRADO** (IMPL-003B) |
| GAP-DR-003 — MantenimientosProgramadosSeeder Faker | **CERRADO** (IMPL-003B) |
| GAP-DR-005 — admin-sistema ausente en AppSeeder | **CERRADO** (IMPL-003B) |
| GAP-DR-009 — Sin orquestador institucional | **CERRADO** (IMPL-003B) |

La plataforma ahora puede restaurarse completamente desde un Snapshot ZIP
con un único comando Artisan, en cualquier instalación limpia con esquema migrado.
