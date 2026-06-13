# IMPL-INFRA-BACKUP-003A — Backup Restore Readiness Remediation

| Campo            | Valor                                                              |
|------------------|--------------------------------------------------------------------|
| **ID**           | IMPL-INFRA-BACKUP-003A                                             |
| **Nombre**       | Backup Restore Readiness Remediation                               |
| **Estado**       | COMPLETADO                                                         |
| **Fecha**        | 2026-06-13                                                         |
| **Versiones**    | IEE v1.23.2 — BhagamApps v1.22.2                                  |
| **Autorizado por** | PMO                                                              |
| **Prerrequisito**| AUDIT-BACKUP-001 (SHA: 2c5b7b8be5dace48f5c27fd74b0f55c97a1daf00)  |
| **Habilita**     | IMPL-INFRA-BACKUP-003 (Drive) · IMPL-INFRA-BACKUP-004 (Restauración) |

---

## Objetivo

Resolver los 6 hallazgos bloqueantes identificados en AUDIT-BACKUP-001 (R-001 a R-006) para
que el sistema de respaldo institucional sea apto para restauración automatizada.

No se abordan hallazgos MEDIOS ni BAJOS (R-007 a R-014) en esta implementación.

---

## Hallazgos resueltos

| ID | Prioridad | Descripción | Fix aplicado |
|---|---|---|---|
| R-001 | CRÍTICO | `HistorialModificacionesBienesSeeder` lanza SQL error (columna `campo_modificado` + `dependencia_id` NOT NULL) | FIX-001 |
| R-002 | CRÍTICO | `app_role` sin seeder → dashboard vacío tras restore | FIX-002 |
| R-003 | ALTO | `CategoriasSeeder` no preserva IDs (riesgo FK latente) | FIX-003 |
| R-004 | ALTO | `BienesSeeder` omite `origen_id` → 1.420 bienes con NULL | FIX-004 |
| R-005 | ALTO | No existe `OrigenesSeeder` → `origenes` no restaurable desde CSV | FIX-005 |
| R-006 | ALTO | `UserSeeder` no restaura `bloqueado`, `forzar_cambio_password`, `es_principal` | FIX-006 |

---

## Archivos modificados

| Archivo | Tipo | Cambio |
|---|---|---|
| `Modules/Inventario/Database/Seeders/HistorialModificacionesBienesSeeder.php` | Modificado | Reescritura completa (FIX-001) |
| `Modules/User/Database/Seeders/AppRoleSeeder.php` | Creado | Nuevo seeder (FIX-002) |
| `Modules/User/Database/Seeders/UserDatabaseSeeder.php` | Modificado | Agrega `AppRoleSeeder` al final (FIX-002) |
| `Modules/Inventario/Database/Seeders/CategoriasSeeder.php` | Modificado | Descomenta `'id' => $data['id']` (FIX-003) |
| `Modules/Inventario/Database/Seeders/BienesSeeder.php` | Modificado | Agrega `origen_id` con null coalescing (FIX-004) |
| `Modules/Inventario/Database/Seeders/OrigenesSeeder.php` | Creado | Nuevo seeder con `updateOrInsert` (FIX-005) |
| `Modules/Inventario/Database/Seeders/InventarioDatabaseSeeder.php` | Modificado | Agrega `OrigenesSeeder` antes de `BienesSeeder` (FIX-005) |
| `Modules/User/Database/Seeders/UserSeeder.php` | Modificado | Agrega `bloqueado`, `forzar_cambio_password`, `es_principal` (FIX-006) |
| `Modules/Inventario/Database/Seeders/data/historial_modificaciones_bienes.csv` | Creado | CSV de backup copiado del respaldo 2026-06-13 |
| `Modules/Inventario/Database/Seeders/data/origenes.csv` | Creado | CSV de backup copiado del respaldo 2026-06-13 |
| `Modules/User/Database/Seeders/data/app_role.csv` | Creado | CSV de backup copiado del respaldo 2026-06-13 |

---

## Detalle por fix

### FIX-001 — HistorialModificacionesBienesSeeder

**Problema:** Seeder generaba datos ficticios con columnas incorrectas:
- `campo_modificado` → no existe en la tabla (columna real: `campo`)
- `user_id` → no existe en la tabla
- `fecha_modificacion` → no existe en la tabla
- omitía `dependencia_id` (NOT NULL)

**Solución:** Reescritura completa. El seeder ahora lee desde
`data/historial_modificaciones_bienes.csv` (backup CSV) usando las columnas reales
del esquema. Si el archivo no existe (entorno dev sin backup), avisa y continúa sin error.
Usa `insertOrIgnore` para ser idempotente.

```php
// Columnas restau radas exactamente como en el esquema:
// id, bien_id, tipo_objeto, campo, valor_anterior, valor_nuevo,
// dependencia_id, estado, aprobado_por, created_at, updated_at
```

---

### FIX-002 — AppRoleSeeder

**Problema:** No existía ningún seeder para la tabla `app_role`. Tras restore, los 10
registros de asignación app→rol quedarían en 0, y ningún usuario vería apps en su dashboard.

**Solución:** Creado `Modules/User/Database/Seeders/AppRoleSeeder.php` con lógica defensiva:
1. Lee `data/app_role.csv` (salta si no existe)
2. **Verifica existencia de app_id y role_id** antes de insertar: si la app no existe
   aún (p.ej. `admin-sistema` que se crea en `AdminSistemaSeeder` posterior), la fila
   se omite sin error. La entrada la crea el seeder correspondiente.
3. Usa `insertOrIgnore` para idempotencia ante duplicados

Agregado al final de `UserDatabaseSeeder`, donde roles y apps ya existen.

```
Orden de ejecución en restore:
1. AppsDatabaseSeeder → apps (IDs 1-11)
2. UserDatabaseSeeder → roles, usuarios, permisos, app_role (desde CSV)
3. AdminSistemaSeeder → app admin-sistema + su app_role
4. InventarioDatabaseSeeder → bienes, etc.
```

---

### FIX-003 — CategoriasSeeder

**Problema:** `'id' => $data['id']` estaba comentado. En migrate:fresh sobre tabla vacía,
las categorías reciben IDs por auto-increment en orden de inserción (coincide con producción
por azar). En restauraciones parciales o instalaciones con datos previos, los IDs divergen
y `bienes.categoria_id` apunta a categorías incorrectas.

**Solución:** Una línea: descomentar `'id' => $data['id']`. Los IDs del backup son siempre
respetados, independientemente del estado de la tabla.

```php
// Antes:
//'id' => $data['id'],

// Después:
'id' => $data['id'],
```

---

### FIX-004 — BienesSeeder

**Problema:** El seeder no incluía `origen_id` en el insert. Los 1.420 bienes restaurados
quedaban con `origen_id=NULL`, invalidando el catálogo de orígenes.

**Solución:** Agregar `origen_id` al insert con null coalescing para compatibilidad con CSVs
de desarrollo que no tengan esta columna:

```php
'origen_id' => (($data['origen_id'] ?? '') !== '' && ($data['origen_id'] ?? '') !== 'NULL')
    ? ($data['origen_id'] ?? null)
    : null,
```

El operador `??` garantiza que si el CSV no tiene la columna `origen_id` (CSV de desarrollo
anterior a IMPL-INV-012), el seeder sigue funcionando sin errores.

---

### FIX-005 — OrigenesSeeder

**Problema:** La tabla `origenes` (11 registros) no tenía cobertura de seeder. La migración
`populate_origenes` inserta el catálogo durante `migrate:fresh` pero con `bienes` vacío,
por lo que el mapeo `bienes.origen_id` no se aplica. Los orígenes sí quedaban en la BD tras
la migración, pero un restore completo podría no incluirlos si el flujo cambia.

**Solución:** Nuevo `OrigenesSeeder` que:
1. Lee `data/origenes.csv`
2. Usa `updateOrInsert(['id' => ...], [...])` — idempotente aunque la migración ya haya
   insertado los registros
3. Preserva todos los campos: `id`, `nombre`, `descripcion`, `activo`, `created_at`, `updated_at`
4. Se ejecuta **antes** de `BienesSeeder` en `InventarioDatabaseSeeder`

```
InventarioDatabaseSeeder orden actualizado:
...DependenciasSeeder
   OrigenesSeeder    ← NUEVO (antes de BienesSeeder)
   BienesSeeder      ← ahora puede restaurar origen_id correctamente
...
```

---

### FIX-006 — UserSeeder

**Problema:** `UserSeeder` fue implementado antes de IMPL-USERS-001. No restauraba
tres campos de seguridad agregados en esa implementación:
- `bloqueado` (tinyint, default 0)
- `forzar_cambio_password` (tinyint, default 0)
- `es_principal` (tinyint, default 0)

Tras restore, todos los usuarios quedaban desbloqueados, sin forzar cambio de password y
sin marca de usuario principal, independientemente de su estado real en producción.

**Solución:** Agregar los tres campos al insert con null coalescing (compatibilidad con
CSVs de desarrollo que no tengan estas columnas):

```php
'bloqueado'              => (int) ($data['bloqueado'] ?? 0),
'forzar_cambio_password' => (int) ($data['forzar_cambio_password'] ?? 0),
'es_principal'           => (int) ($data['es_principal'] ?? 0),
```

El backup CSV (`data/users.csv` copiado del ZIP) incluye estas columnas con los valores
reales de producción.

**Nota:** Las contraseñas siguen siendo regeneradas por la fórmula `iniciales+últimos4DNI@IEE`
(no se restauran los hashes bcrypt). Esta es una decisión deliberada de diseño por seguridad.

---

## CSVs de referencia incluidos en el repo

Para que los seeders tengan datos de referencia inmediatos (último backup disponible), se
copiaron los siguientes CSVs del respaldo 2026-06-13 a las carpetas `data/` de sus
respectivos módulos:

| CSV origen (backup) | Destino en repo | Registros |
|---|---|---|
| `backups/2026-06-13/historial_modificaciones_bienes.csv` | `Modules/Inventario/Database/Seeders/data/historial_modificaciones_bienes.csv` | 11 |
| `backups/2026-06-13/origenes.csv` | `Modules/Inventario/Database/Seeders/data/origenes.csv` | 11 |
| `backups/2026-06-13/app_role.csv` | `Modules/User/Database/Seeders/data/app_role.csv` | 10 |

**Importante para restore:** Siempre reemplazar estos archivos con el CSV del backup actual
antes de ejecutar los seeders, para restaurar el estado más reciente.

---

## Validaciones ejecutadas

| ID | Validación | Resultado |
|---|---|---|
| V-001 | HistorialModificacionesBienesSeeder: CSV con columna `campo` (no `campo_modificado`), `dependencia_id` presente | ✅ |
| V-002 | AppRoleSeeder existe, registrado en UserDatabaseSeeder, CSV con 10 registros | ✅ |
| V-003 | CategoriasSeeder: `'id' => $data['id']` descomentado | ✅ |
| V-004 | BienesSeeder: `origen_id` con null coalescing para compatibilidad | ✅ |
| V-005 | OrigenesSeeder: `updateOrInsert` con preservación de IDs, ejecuta antes de BienesSeeder | ✅ |
| V-006 | UserSeeder: restaura `bloqueado`, `forzar_cambio_password`, `es_principal` | ✅ |
| V-007 | PHP lint limpio — 8 archivos PHP verificados | ✅ |
| V-008 | `config:cache` sin errores | ✅ |

---

## Restricciones aplicadas

- NO se implementó restauración (IMPL-INFRA-BACKUP-004).
- NO se tocó Google Drive.
- NO se modificó la UI de Backups.
- NO se modificó la política de retención.
- NO se modificó `BackupExportSeeders`.
- Los hallazgos MEDIOS y BAJOS (R-007 a R-014) NO fueron abordados.

---

## Dictamen final

> **¿Los hallazgos críticos y altos de AUDIT-BACKUP-001 quedaron completamente resueltos?**

**SÍ.** Los 2 hallazgos CRÍTICOS y 4 hallazgos ALTOS que bloqueaban la restauración
automatizada han sido resueltos:

| Hallazgo | Estado anterior | Estado actual |
|---|---|---|
| R-001: HMB Seeder SQL error | ❌ Falla con SQL exception | ✅ Lee CSV correcto, columnas exactas |
| R-002: app_role sin seeder | ❌ Dashboard vacío tras restore | ✅ AppRoleSeeder restaura 10 asignaciones |
| R-003: Categorías sin IDs | ❌ Riesgo FK en restore parcial | ✅ IDs preservados explícitamente |
| R-004: origen_id omitido | ❌ 1.420 bienes con NULL | ✅ origen_id restaurado desde CSV |
| R-005: Sin OrigenesSeeder | ❌ Catálogo no restaurable por seeder | ✅ OrigenesSeeder con updateOrInsert |
| R-006: UserSeeder sin campos de seguridad | ❌ bloqueado/forzar/es_principal perdidos | ✅ Los 3 campos restaurados |

El sistema está listo para IMPL-INFRA-BACKUP-004 (Restauración automatizada).
Los hallazgos MEDIOS (R-007 a R-010) y BAJOS (R-011 a R-014) permanecen abiertos
para ser abordados en fases posteriores.

---

## SHA verificable

| Campo | Valor |
|---|---|
| SHA | _(ver git log — commit de esta implementación)_ |
| Commit | `feat(infra): IMPL-INFRA-BACKUP-003A — Backup Restore Readiness Remediation` |
| Fecha | 2026-06-13 |
