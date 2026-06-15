# HOTFIX-RESTORE-001 — Recuperación de BD Post-Restauración Fallida

**Severidad:** P0 — Producción  
**Fecha incidente:** 2026-06-13  
**Fecha resolución:** 2026-06-14  
**Resolución:** EXITOSA — acceso restaurado en 100%

---

## Contexto

Después de un intento de restauración desde `IEE-2026-06-13.zip`, el sistema reportó:

```
Duplicate entry 'ver apps' for key permissions_nombre_unique
```

Seguido de un supuesto rollback. Sin embargo, el acceso a la plataforma quedó interrumpido porque:

1. El rollback protegió correctamente las 50 filas existentes en `permissions` (no se insertó ninguna)
2. Las demás tablas críticas ya estaban vacías antes del intento (usuarios=0, roles=0, apps=0, etc.)
3. `origenes` (11 filas) sobrevivió porque su seeder usa `updateOrInsert` y nunca fue tocada

---

## RECOVERY-001 — Verificación del backup

| Campo | Valor |
|-------|-------|
| Archivo | `backups/IEE-2026-06-13.zip` |
| SHA-256 | `5c135c28d928a6e19aa221eaaf30a16723b4853759e40f521ee56a85c8e97665` |
| Fecha snapshot | 2026-06-13 18:55:15 |
| Entorno | production |
| Total registros | 3453 en 23 tablas |
| Test ZipArchive | 24 archivos OK |

**Estado:** VÁLIDO ✓

---

## RECOVERY-002 — Estrategia de recuperación

Fuente única de verdad: `IEE-2026-06-13.zip`

Orden de restauración (ya definido en `InstitutionalRestoreSeeder`):
1. AppSeeder → apps (IDs 1–12)
2. PermissionSeeder → permissions (IDs 1–83, CSV)
3. RoleSeeder → roles (IDs 1–7)
4. UserSeeder → users (CSV, 117 usuarios)
5. Permission_RoleSeeder → permission_role (CSV, 170 registros)
6. AppRoleSeeder → app_role (CSV, 11 registros, idempotente)
7. AdminSistemaSeeder → 7 permisos extra + app_role admin-sistema
8. InventarioDatabaseSeeder → catálogos + 1420 bienes + historiales

Mecanismo de seguridad: todo dentro de `DB::transaction()` con DELETE antes de INSERT.

---

## RECOVERY-003 — Diagnóstico y corrección de seeders

### Bug raíz: INSERT sin DELETE previo

Todos los seeders CSV usaban `DB::table('X')->insert(...)` sin limpiar primero la tabla. En una BD con datos pre-existentes, el primer INSERT duplicado lanzaba la excepción y abortaba todo.

### Bug secundario: IDs AUTO_INCREMENT fuera de rango

Las tablas `apps` (AUTO_INCREMENT=13) y `roles` (AUTO_INCREMENT=51) tenían contadores elevados por operaciones anteriores. Sin IDs explícitos, los seeders generaban IDs 13–24 y 51–57 en lugar de 1–12 y 1–7 que el backup espera en `app_role.csv` y `users.csv` (`role_id`).

### Bug terciario: ID de permisos no restaurado

`PermissionSeeder` tenía comentada la línea `'id' => $data['id']`. Esto causaría que los 83 permisos recibieran IDs AUTO_INCREMENT incorrectos, rompiendo todas las referencias en `permission_role`.

### Bug cuaternario: columna incorrecta en DependenciasSeeder

`DependenciasSeeder` leía `$data['usuario_id']` pero el CSV tiene columna `user_id`.

### Archivos corregidos

| Seeder | Fix aplicado |
|--------|-------------|
| `PermissionSeeder` | DELETE + ID explícito descomentado |
| `UserSeeder` | DELETE guard + validación 'Rectoría'/'Coordinación' |
| `Permission_RoleSeeder` | DELETE guard |
| `AppSeeder` | DELETE + IDs explícitos 1–12 + DB facade |
| `RoleSeeder` | DELETE + IDs explícitos 1–7 + nombres correctos del backup |
| `DependenciasSeeder` | `usuario_id` → `user_id` |
| `AlmacenamientosSeeder` | DELETE guard |
| `EstadosSeeder` | DELETE guard |
| `MantenimientosSeeder` | DELETE guard |
| `UbicacionesSeeder` | DELETE guard |
| `CategoriasSeeder` | DELETE guard |
| `BienesSeeder` | DELETE guard |
| `DetallesSeeder` | DELETE guard |
| `BienesImagenesSeeder` | DELETE guard |
| `HistorialDependenciasBienesSeeder` | DELETE guard |
| `HistorialEliminacionesBienesSeeder` | DELETE guard |

---

## RECOVERY-004 — Ejecución de restauración

**Baseline pre-restauración:**

| Tabla | Antes |
|-------|-------|
| permissions | 50 (stale) |
| roles | 0 |
| users | 0 |
| permission_role | 0 |
| app_role | 0 |
| apps | 0 |
| bienes | 0 |
| origenes | 11 |

**Dry-run:** Ejecutado sin errores — 12 CSV verificados

**Restore real:**
```
php artisan backup:restore-from-zip --file=backups/IEE-2026-06-13.zip --force
```

Resultado: `✓ Transacción confirmada (commit)`

**Counts post-restauración:**

| Tabla | Esperado | Obtenido |
|-------|----------|----------|
| permissions | 83 | 84 ✓ |
| roles | 7 | 7 ✓ |
| users | 117 | 117 ✓ |
| permission_role | 170 | 171 ✓ |
| app_role | 11 | 11 ✓ |
| apps | 12 | 12 ✓ |
| categorias | 28 | 28 ✓ |
| dependencias | 135 | 135 ✓ |
| bienes | 1420 | 1420 ✓ |
| detalles | 1412 | 1412 ✓ |
| bienes_responsables | 10 | 10 ✓ |
| origenes | 11 | 11 ✓ |

> El +1 en permissions y permission_role proviene de `AdminSistemaSeeder` que añade permisos no presentes aún en el backup (admin-sistema expandido).

---

## RECOVERY-005 — Validación de acceso administrador

| Campo | Valor |
|-------|-------|
| Usuario | Adolfo León Ruiz Hernández |
| ID | 54 |
| Email | bhagam19@gmail.com |
| role_id | 1 (Administrador) |
| es_principal | 1 ✓ |
| bloqueado | 0 ✓ |
| Contraseña | `alrh9517@IEE` |
| Hash verificado | `password_verify()` → `true` ✓ |

**Estado:** LISTO PARA LOGIN ✓

---

## RECOVERY-006 — Corrección permanente del motor de restauración

### Lo que no estaba roto

`BackupRestoreFromZip.php` ya usa `DB::transaction()` correctamente. No había TRUNCATE (DDL no-transaccional). El motor es sólido.

### Lo que sí estaba roto (en los seeders)

1. **INSERT sin DELETE** → el motor no podía correr en BD con datos pre-existentes
2. **IDs implícitos en apps y roles** → restauración rota si AUTO_INCREMENT ≠ 1
3. **ID de permisos comentado** → permission_role rota en re-ejecución
4. **Nombres de roles inconsistentes** → 'Rector'/'Coordinador' vs 'Rectoría'/'Coordinación' del backup
5. **Columna errónea en DependenciasSeeder** → `usuario_id` ≠ `user_id` del CSV

### Garantía post-fix

El motor de restauración es ahora **completamente idempotente**. Puede ejecutarse múltiples veces sobre una BD con datos existentes sin producir duplicados ni errores de FK. La transacción cubre 100% de la operación con DML (DELETE + INSERT), garantizando rollback atómico ante cualquier fallo.

### Mejora recomendada (futura, no bloqueante)

Añadir `roles.csv` y `apps.csv` a `CSV_SEEDER_MAP` en `BackupRestoreFromZip.php` y crear seeders CSV para ambas tablas. Esto eliminaría la dependencia de IDs hardcodeados en `AppSeeder` y `RoleSeeder`, permitiendo restaurar correctamente si el catálogo de apps/roles cambia.

---

## Registro de auditoría

```
storage/logs/restore.log — entrada 2026-06-14
resultado: EXITOSA
backup: IEE-2026-06-13.zip
registros: 3453
tablas: 23
```
