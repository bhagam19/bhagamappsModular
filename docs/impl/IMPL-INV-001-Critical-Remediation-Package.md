# IMPL-INV-001 — Critical Remediation Package

**Estado:** COMPLETADO  
**Origen:** AUDIT-INV-001  
**Fecha:** 2026-06-09  
**Versión Inventario:** 2.4.2 → 2.5.0  
**Versión BhagamApps:** 1.7.0 → 1.7.1

---

## 1. Contexto

AUDIT-INV-001 clasificó el módulo Inventario como `ESTABLE CON DEUDA TÉCNICA` e identificó cuatro hallazgos que representan riesgos operativos inmediatos. Esta implementación los resuelve en su totalidad antes de iniciar nuevas funcionalidades del roadmap.

---

## 2. Hallazgos corregidos

### H-CRIT-001 — Permiso inexistente para HEB

**Descripción del problema:**  
La ruta `GET /inventario/heb` requería el permiso `gestionar-historial-eliminaciones-bienes` mediante middleware `CheckPermission`. Este permiso no existía en la tabla `permissions`. Todos los roles obtenían HTTP 403 al intentar acceder al panel de gestión de solicitudes de eliminación.

**Decisión:** Crear el permiso y asignarlo a Administrador y Rector, coherente con el patrón del permiso gemelo `gestionar-historial-modificaciones-bienes` (ID 22).

**Implementación:**
- Migración: `2026_06_09_000004_add_heb_permission_and_assign_roles.php`
- Permiso creado: `gestionar-historial-eliminaciones-bienes` (ID 36)
- Categoría: `aprobaciones pendientes`
- Asignado a: Administrador (ID 1), Rector (ID 2)

**Validación V-001:**
```sql
SELECT * FROM permissions WHERE slug = 'gestionar-historial-eliminaciones-bienes';
-- ID: 36 | categoria: aprobaciones pendientes
```

**Validación V-002:**
```
GET /inventario/heb — middleware:
  web ✅ | auth ✅ | app.access:inventario ✅ | permission:gestionar-historial-eliminaciones-bienes ✅ (permiso existe en BD)
```

**Estado:** CERRADO ✅

---

### H-CRIT-002 — Tabla bienes_responsables inexistente

**Descripción del problema:**  
El modelo `BienResponsable`, la relación `Bien::responsables()`, el permiso `asignar-responsables-a-bienes` y el seeder `BienesResponsablesSeeder` existían pero la tabla `bienes_responsables` nunca fue creada. Cualquier llamada a `$bien->responsables` generaba `SQLSTATE[42S02]: Table 'bienes_responsables' doesn't exist`.

**Decisión:** Escenario A — La funcionalidad está vigente. La evidencia (modelo con fillable completo, seeder dedicado, permiso asignado a 4 roles) indica que la tabla fue omitida por error, no descartada. Se crea la migración sin implementar UI (fuera del alcance de este paquete).

**Implementación:**
- Migración: `2026_06_09_000005_create_bienes_responsables_table.php`
- Tabla `bienes_responsables` creada con FK a `bienes` (cascadeOnDelete) y `users` (nullOnDelete)
- Columnas: `id`, `bien_id`, `user_id`, `observaciones`, `fecha_asignacion`, `fecha_retiro`, `created_at`, `updated_at`
- UI de asignación de responsables: pendiente (Prioridad 3 del roadmap)

**Nota:** El seeder `BienesResponsablesSeeder` usa `fecha_inicio`/`fecha_fin` pero el modelo define `fecha_asignacion`/`fecha_retiro`. La migración sigue el modelo. El seeder requiere corrección separada si se ejecuta.

**Validación V-003:**
```
\Schema::hasTable('bienes_responsables') → true
DESCRIBE bienes_responsables: id, bien_id, user_id, observaciones, fecha_asignacion, fecha_retiro, created_at, updated_at
```

**Validación V-004:**
```php
\DB::table('bienes_responsables')->where('bien_id', 1)->count(); // → 0, sin error SQL
```

**Estado:** CERRADO ✅

---

### H-ALTO-001 — Coordinador sin acceso a Inventario

**Descripción del problema:**  
El rol Coordinador tenía permisos `ver-bienes`, `crear-bienes` y `editar-bienes` asignados pero la app `inventario` no estaba en `app_role` para dicho rol. El middleware `CheckAppAccess:inventario` evaluaba `App::visiblesPara($user)` que filtra por `app_role` — como no había registro, devolvía colección vacía y retornaba HTTP 403.

**Decisión:** Asignar la app `inventario` al rol Coordinador. Sus permisos declaran explícitamente qué puede hacer dentro del módulo, y el `app.access` es el control de acceso previo al permiso.

**Implementación:**
- Migración: `2026_06_09_000006_assign_inventario_app_to_coordinador.php`
- Registro insertado en `app_role`: `app_id=15` (inventario), `role_id=3` (Coordinador)

**Validación V-005:**
```
app_role WHERE role_id=3 AND app_id=15 → EXISTS ✅
App::visiblesPara(usuario Coordinador) → ['inventario'] (1 app) ✅
```

**Estado:** CERRADO ✅

---

### H-ALTO-002 — Null Check en HmbIndex::aprobarModificacion()

**Descripción del problema:**  
En `HmbIndex::aprobarModificacion()`, la variable `$modificacion` era accedida en `$bien = Bien::with('dependencia')->find($modificacion->bien_id)` antes del guard `if (!$modificacion)`. Si `$modificacion` era null (por concurrencia o doble clic), se lanzaba `TypeError: Cannot access property "bien_id" on null` antes de ejecutar el guard.

Adicionalmente, el evento de dispatch en el bloque `catch` tenía un typo: `modificacionActualizad` (sin la 'a' final) que impedía que el listener `['modificacionActualizada' => '$refresh']` reaccionara correctamente en caso de error.

**Implementación:**
- Archivo: `Modules/Inventario/Livewire/Hmb/HmbIndex.php`
- Null check movido antes de `$bien = Bien::with('dependencia')->find($modificacion->bien_id)`
- Typo corregido: `modificacionActualizad` → `modificacionActualizada`

**Validación V-006:**
```
Posición null check (línea 69) < Primer acceso a $modificacion->bien_id (línea 75) ✅
```

**Diff aplicado:**
```php
// ANTES (incorrecto):
$bien = Bien::with('dependencia')->find($modificacion->bien_id); // acceso prematuro
$user = $bien->dependencia->user_id;
if (!$modificacion) { ... } // guard tardío e inútil

// DESPUÉS (correcto):
if (!$modificacion) { ... } // guard al inicio
$bien = Bien::with('dependencia')->find($modificacion->bien_id); // acceso seguro
```

**Estado:** CERRADO ✅

---

## 3. Validaciones V-001 a V-006

| Validación | Descripción | Resultado |
|---|---|---|
| V-001 | `hasPermission('gestionar-historial-eliminaciones-bienes')` funciona | ✅ PASS |
| V-002 | `/inventario/heb` accesible para autorizados | ✅ PASS |
| V-003 | Sin consultas SQL a tablas inexistentes | ✅ PASS |
| V-004 | Relación `bien->responsables` funciona o fue removida | ✅ PASS (tabla creada) |
| V-005 | Coordinador obtiene comportamiento esperado en Inventario | ✅ PASS |
| V-006 | Sin errores por registros inexistentes en HMB | ✅ PASS |

---

## 4. Archivos modificados

| Archivo | Tipo | Descripción |
|---|---|---|
| `Modules/Inventario/Database/Migrations/2026_06_09_000004_add_heb_permission_and_assign_roles.php` | NUEVO | Permiso HEB + asignación roles |
| `Modules/Inventario/Database/Migrations/2026_06_09_000005_create_bienes_responsables_table.php` | NUEVO | Tabla bienes_responsables |
| `Modules/Inventario/Database/Migrations/2026_06_09_000006_assign_inventario_app_to_coordinador.php` | NUEVO | App inventario → Coordinador |
| `Modules/Inventario/Livewire/Hmb/HmbIndex.php` | MODIFICADO | Null check corregido + typo dispatch |
| `config/versiones.php` | MODIFICADO | Inventario 2.4.2→2.5.0, BhagamApps 1.7.0→1.7.1 |
| `CHANGELOG.md` | MODIFICADO | Entrada v1.7.1 |
| `VERSIONING.md` | MODIFICADO | Versiones actualizadas |
| `docs/changelog/inventario.md` | MODIFICADO | Entrada v2.5.0 |
| `docs/changelog/bhagamapps.md` | MODIFICADO | Entrada v1.7.1 |

---

## 5. Decisiones arquitectónicas

| Decisión | Razonamiento |
|---|---|
| H-CRIT-002 → Escenario A (crear tabla) | Modelo, fillable, seeder y permiso evidencian funcionalidad planificada. Sin evidencia de descarte formal. |
| No implementar UI de responsables | Fuera del alcance de este paquete de estabilización. Queda como Prioridad 3 del roadmap. |
| Coordinador accede a inventario | Sus permisos declarados (`ver-bienes`, `crear-bienes`, `editar-bienes`) hacen incoherente el bloqueo de acceso al módulo. |
| Minor bump Inventario (2.4.2→2.5.0) | Nueva tabla + nuevo permiso + cambio de acceso = cambio de comportamiento significativo. |

---

## 6. Riesgos mitigados

| Riesgo | Antes | Después |
|---|---|---|
| HEB inaccesible para todos los roles | CRÍTICO | ELIMINADO |
| Error SQL fatal en bien->responsables | CRÍTICO | ELIMINADO |
| Coordinador bloqueado por app.access | ALTO | ELIMINADO |
| TypeError en HMB por null check tardío | ALTO | ELIMINADO |

---

## 7. Estado final

```
IMPL-INV-001 — COMPLETADO

H-CRIT-001: CERRADO ✅
H-CRIT-002: CERRADO ✅ (Escenario A)
H-ALTO-001: CERRADO ✅
H-ALTO-002: CERRADO ✅

V-001 → V-006: TODAS SATISFACTORIAS

Inventario:   v2.4.2 → v2.5.0
BhagamApps:   v1.7.0 → v1.7.1
```

---

*Generado — 2026-06-09*
