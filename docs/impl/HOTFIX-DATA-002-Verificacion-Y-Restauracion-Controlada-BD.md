# HOTFIX-DATA-002 — Verificación y Restauración Controlada de Base de Datos

**Fecha:** 2026-06-11
**Autorizado por:** PMO
**Prioridad:** CRÍTICA
**Ejecutado en:** `/home/adolfo/web/bhagamapps.com/private/bhagamappsModular`
**URL:** http://bhagamapps.com/iee

---

## FASE 1 — Diagnóstico

### DB-001 — Estado de migraciones

Todas las migraciones ejecutadas correctamente. Total: 48 migraciones en batch 1.
Última migración: `2026_06_11_100002_add_password_permissions` ✓

### DB-002 — Conexión activa

```
DB_CONNECTION = mysql
DB_HOST       = localhost
DB_PORT       = 3306
DB_DATABASE   = adolfo_bhagamappsModular
DB_USERNAME   = adolfo_bdModular
```

### DB-003 — Conteo de registros (pre-restauración)

| Tabla | Antes | Después |
|---|---|---|
| users | 116 | 116 |
| roles | 7 | 7 |
| permissions | 77 | 77 |
| permission_role | 178 | 178 |
| apps | 11 | 11 |
| bienes | 0 | **1420** |
| categorias | 0 | **28** |
| dependencias | 0 | **135** |
| ubicaciones | 0 | **4** |
| estados | 0 | **4** |
| origenes | 0 | 0 (sin seeder/CSV) |
| almacenamientos | 0 | **2** |
| bienes_responsables | 0 | **10** |
| mantenimientos | 0 | **3** |
| detalles | 0 | **1412** |

### DB-004 — Usuario rectoriaiee

| Campo | Valor |
|---|---|
| id | 54 |
| email | rectoriaiee@entrerrios.edu.co |
| nombres | Adolfo León Ruiz Hernández |
| userID | 71379517 |
| rol | Rector (id=2) |
| permisos del rol | 77 (completos) |
| bloqueado | No |
| forzar_cambio_password | No |

### DB-005 — Estado determinado

**`B — Base parcialmente poblada`**

- Módulo Auth/Usuarios: 116 usuarios institucionales reales (CSV oficial de IEE)
- Módulo Inventario: tablas vacías, datos en CSV oficial

---

## FASE 2 — Decisión tomada

**`migrate:fresh` → DETENIDO.**

Motivo: La base contiene 116 usuarios institucionales reales. Ejecutar `migrate:fresh` destruiría datos irrecuperables sin backup previo.

**Acción autorizada:** Ejecución de seeders del módulo Inventario sobre tablas vacías (operación aditiva, sin riesgo).

---

## FASE 3 — Restauración ejecutada

### Seeders ejecutados (módulo Inventario)

Todos ejecutados sobre tablas vacías confirmadas:

```
AlmacenamientosSeeder      ✓
CategoriasSeeder           ✓
EstadosSeeder              ✓
UbicacionesSeeder          ✓
DependenciasSeeder         ✓
MantenimientosSeeder       ✓
BienesSeeder               ✓  (1420 bienes)
DetallesSeeder             ✓  (1412 detalles)
BienesResponsablesSeeder   ✓  (corregido: fecha_inicio→fecha_asignacion)
MantenimientosProgramadosSeeder ✓
```

### Seeders omitidos

```
HistorialModificacionesBienesSeeder   — esquema desactualizado, datos sintéticos
HistorialDependenciasBienesSeeder     — ídem
HistorialEliminacionesBienesSeeder    — ídem
OrigenesSeeder                        — no existe (catálogo nuevo sin datos)
```

Los historiales comienzan vacíos. Se poblarán naturalmente con el uso del sistema.

### Corrección aplicada — BienesResponsablesSeeder

Columnas desactualizadas en el seeder (no en la migración):
- `fecha_inicio` → `fecha_asignacion`
- `fecha_fin` → `fecha_retiro` (null)

---

## FASE 4 — Usuario Rectoría

**rectoriaiee@entrerrios.edu.co** — existente ✓

Rol asignado: **Rector** (role_id=2)
Permisos del rol: **77** (totalidad de permisos del sistema)

El rol Rector tiene exactamente los mismos 77 permisos que el rol Administrador (asignados por `RoleSeeder` vía `sync($permisos)`). No se modifica el rol — la denominación "Rector" es semánticamente correcta para el cargo institucional.

Contraseña: generada automáticamente por fórmula oficial (`alrh9517@IEE`). Verificada ✓

---

## Validaciones

| ID | Validación | Estado |
|---|---|---|
| V-001 | Migraciones ejecutadas correctamente | ✓ 48/48 |
| V-002 | Roles cargados | ✓ 7 roles |
| V-003 | Permisos cargados | ✓ 77 permisos |
| V-004 | Usuario rectoriaiee@entrerrios.edu.co existente | ✓ id=54 |
| V-005 | Usuario con privilegios administrativos completos | ✓ 77 permisos vía rol Rector |
| V-006 | Login funcional | ✓ hash verificado |
| V-007 | Inventario cargado | ✓ 1420 bienes, 28 categorías, 135 dependencias |
| V-008 | Sin errores 500 | Pendiente verificación manual en browser |

---

## Riesgos residuales

- `origenes` vacío: catálogo nuevo sin datos base. Requiere definición y seeder.
- Historiales vacíos: normal en restauración inicial. Sin impacto operativo.
- Seeders de historial con esquema desactualizado: deben actualizarse antes del próximo `db:seed` completo.
- V-008 requiere verificación manual en browser.
