# IMPL-APPS-006 — Legacy Application Registry Cleanup

**Estado:** COMPLETADO  
**Fecha:** 2026-06-09  
**Origen:** AUDIT-APPS-006 (H-001, H-002, H-003)  
**Versión Apps:** 1.4.3 → 1.5.0  
**Versión BhagamApps:** 1.6.5 → 1.7.0  

---

## 1. Contexto

AUDIT-APPS-006 identificó que el módulo Apps contenía dos capas de registros en la tabla
`apps` que coexistían de forma conflictiva:

**Capa Legacy (IDs 1-12):**
- Sin `slug` (NULL)
- `habilitada = false`
- `user_id = 1` (seeder del sistema anterior)
- Creados el 2026-06-07
- 8 duplicados de nombre con el catálogo oficial
- 9 duplicados de ruta con el catálogo oficial

**Catálogo Oficial (IDs 13-24):**
- `slug` definido
- Datos completos
- Utilizado por Apps, Dashboard y autorización actual

Esta implementación elimina la capa legacy preservando la integridad referencial completa.

---

## 2. Análisis de Dependencias (Paso 2 del Plan)

### 2.1 Tabla `app_role`

| id | app_id | role_id | Clasificación |
|----|--------|---------|---------------|
| 1  | 1      | 1       | Obsoleta — app legacy "User" |
| 2  | 1      | 2       | Obsoleta — app legacy "User" |
| 3  | 2      | 1       | Obsoleta — app legacy "Inventario" |
| 4  | 2      | 2       | Obsoleta — app legacy "Inventario" |
| 5  | 3      | 1       | Obsoleta — app legacy "App" |
| 6  | 3      | 2       | Obsoleta — app legacy "App" |
| 7  | 4      | 1       | Obsoleta — app legacy "Biblioteca" |
| 8  | 4      | 2       | Obsoleta — app legacy "Biblioteca" |

**Conclusión:** Las 8 referencias en `app_role` corresponden a apps legacy. Son
dependencias obsoletas. El catálogo oficial ya tiene su propio `app_role` (IDs 9-16).

### 2.2 Tabla `app_user`

Vacía. Sin referencias a IDs 1-12.

### 2.3 `roles.app_id`

Todos los 7 roles tienen `app_id = 1` (app legacy "User"). La FK `roles_app_id_foreign`
tiene `ON DELETE SET NULL`. Al eliminar el registro ID=1, `app_id` queda `NULL` en todos
los roles. Los roles conservan todos sus datos, permisos y asignaciones a usuarios.

**FK activa confirmada:**
```
roles_app_id_foreign → SET NULL
```

### 2.4 Código

**`SyncApps` (apps:sync):** Usa `firstOrCreate(['slug' => $slug])`. No puede recrear
registros sin slug. **Sin riesgo de recreación.**

**`AppSeeder`:** Usa `updateOrCreate(['slug' => $data['slug']])`. Nunca inserta sin slug.
**Sin riesgo de recreación.**

**`RoleSeeder`:** Usaba `App::where('nombre', 'user')` → referenciaba el legacy ID=1.
**Bug identificado y corregido** (ver sección 3.2).

**`App::visiblesPara()`:** Filtra por `habilitada = true`. Todos los legacy tenían
`habilitada = false`. **Sin impacto en la autorización activa.**

---

## 3. Cambios Implementados

### 3.1 Migración de limpieza

**Archivo:** `Modules/Apps/database/migrations/2026_06_09_200000_cleanup_legacy_apps.php`

```php
DB::table('apps')->whereNull('slug')->delete();
```

**Efectos de integridad referencial automáticos:**
- `app_role.app_id` FK `ON DELETE CASCADE` → 8 registros de pivote eliminados
- `roles.app_id` FK `ON DELETE SET NULL` → 7 roles: `app_id` → NULL

**Rollback disponible:** El método `down()` restaura los 12 registros (sin sus relaciones
de `app_role`, las cuales eran obsoletas).

### 3.2 Fix en RoleSeeder

**Archivo:** `Modules/User/Database/Seeders/RoleSeeder.php`

```php
// Antes (referencia al legacy eliminado):
$app = App::where('nombre', 'user')->first();

// Después (referencia al registro oficial por slug):
$app = App::where('slug', 'user')->first();
```

**Motivo:** El seeder referenciaba por nombre "user" (coincidía con el legacy "User" ID=1,
case-insensitive en MySQL). Con la eliminación del legacy, el seeder habría fallado en
fresh installs. Ahora referencia correctamente al registro "Usuarios" (slug="user", ID=16).

---

## 4. Backup Documental (Registros Eliminados)

Los 12 registros eliminados están preservados en el método `down()` de la migración y
en AUDIT-APPS-006. Para referencia:

| id | nombre | ruta | created_at |
|----|--------|------|------------|
| 1  | User | /usuarios/user | 2026-06-07 |
| 2  | Inventario | /inventario/bienes | 2026-06-07 |
| 3  | App | /app | 2026-06-07 |
| 4  | Biblioteca | /biblioteca | 2026-06-07 |
| 5  | SINAI vs SIMAT | /SvS | 2026-06-07 |
| 6  | Planeador | /planeador | 2026-06-07 |
| 7  | EduInclusiva | /eduInclusiva | 2026-06-07 |
| 8  | CTE | /cte | 2026-06-07 |
| 9  | Creador de Exámenes | /creadorExamenes | 2026-06-07 |
| 10 | Tabletas | /prestamoTabletas | 2026-06-07 |
| 11 | Polla Mundialista | /pollaMundialista | 2026-06-07 |
| 12 | Evaluar para Avanzar | /evaluarParaAvanzar | 2026-06-07 |

---

## 5. Estado Post-Implementación del Catálogo

```
Catálogo oficial (12 registros únicos, todos con slug):

id=16 | Usuarios        | slug=user              | /user              | habilitada=1
id=15 | Inventario      | slug=inventario        | /inventario/bienes | habilitada=1
id=13 | Aplicaciones    | slug=apps              | /apps/admin        | habilitada=0
id=17 | Biblioteca      | slug=biblioteca        | /biblioteca        | habilitada=0
id=18 | SINAI vs SIMAT  | slug=sinai-vs-simat    | /SvS               | habilitada=0
id=19 | Planeador       | slug=planeador         | /planeador         | habilitada=0
id=20 | EduInclusiva    | slug=edu-inclusiva     | /eduInclusiva      | habilitada=0
id=21 | CTE             | slug=cte               | /cte               | habilitada=0
id=22 | Creador Exámen. | slug=creador-examenes  | /creadorExamenes   | habilitada=0
id=23 | Prést. Tabletas | slug=prestamo-tabletas | /prestamoTabletas  | habilitada=0
id=24 | Eval. Avanzar   | slug=evaluar-para-avanzar | /evaluarParaAvanzar | habilitada=0
id=14 | crudgenerator   | slug=crudgenerator     | /crudgenerator     | habilitada=0
```

---

## 6. Validaciones Ejecutadas

| ID | Validación | Resultado |
|----|------------|-----------|
| V-001 | No existen `apps.id` 1-12 | PASS — 0 registros |
| V-002 | No existen referencias huérfanas en `app_role`/`app_user` | PASS |
| V-003 | No existen rutas duplicadas | PASS |
| V-004 | No existen nombres duplicados | PASS |
| V-005 | `App::visiblesPara()` funciona correctamente | PASS — Administrador ve 2 apps activas |
| V-006 | Dashboard continúa funcionando | PASS — Apps habilitadas con rutas válidas |
| V-007 | Sidebar continúa funcionando | PASS — Rutas `/user`, `/inventario/bienes` válidas |
| V-008 | Acceso a rutas críticas operativo | PASS — `/user` ✓, `/inventario/bienes` ✓, `/apps/admin` ✓ |

---

## 7. Riesgos Mitigados

| Riesgo | Mitigación |
|--------|------------|
| Pérdida de datos útiles | Los 12 registros eran legacy (habilitada=false, sin uso activo). Backup en `down()`. |
| Ruptura de FKs | CASCADE y SET NULL manejados por la BD — sin acción manual requerida. |
| Recreación de legacy | `SyncApps` y `AppSeeder` usan slug como clave — imposible recrear sin slug. |
| Fallo de RoleSeeder en fresh install | Corregido: ahora busca por `slug` en lugar de `nombre`. |
| Impacto en autorización activa | Ninguno — los legacy ya tenían `habilitada=false`, nunca aparecían en `visiblesPara()`. |

---

## 8. Estado Final

```
IMPL-APPS-006 — Legacy Application Registry Cleanup
Estado: COMPLETADO
Fecha: 2026-06-09

Apps:        1.4.3 → 1.5.0
BhagamApps:  1.6.5 → 1.7.0

Módulo Apps: APTO PARA BASELINE ESTABLE
```

---

*Generado por Claude Code — BhagamAppsModular — 2026-06-09*
