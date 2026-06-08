# PLAN-APPS-002 — Cierre de Bloqueantes Críticos del Módulo Apps

**Fecha:** 2026-06-08
**Estado:** Ejecutado → ver IMPL-APPS-002
**Relacionado con:** AUDIT-APPS-002, IMPL-APPS-001

---

## Objetivo

Resolver los bloqueantes B2 y B3 identificados en AUDIT-APPS-002 y la deuda técnica DT-001
(riesgo de cascada en `roles.app_id`) antes de declarar el módulo Apps apto para producción.

---

## Alcance

### Fase 1 — Seguridad (bloqueantes B2 y B3)

| Bloqueante | Descripción | Acción |
|---|---|---|
| **B2** | `/apps/admin` sin protección de permisos | Agregar middleware `permission:administrar-apps` a la ruta |
| **B3** | Métodos Livewire sin verificación | Agregar `abort_if(!hasPermission(...))` en `toggleHabilitada` y `guardarRoles` |

Permiso nuevo: `administrar-apps` (slug único, categoría `apps`).

Asignado por defecto a: **Administrador**, **Rector**.

### Fase 2 — Integridad (deuda técnica DT-001)

| DT | Descripción | Acción |
|---|---|---|
| **DT-001** | `roles.app_id CASCADE DELETE` destruye roles si se elimina una App | Migración que hace `app_id` nullable con `SET NULL` |

La cadena de riesgo:
```
DELETE apps WHERE id=X
  → DELETE roles WHERE app_id=X   (CASCADE — actual)
  → FK violation en users.role_id (RESTRICT — protege parcialmente)
```

Tras la corrección:
```
DELETE apps WHERE id=X
  → UPDATE roles SET app_id=NULL WHERE app_id=X   (SET NULL)
  → Roles intactos, usuarios intactos
```

### Fase 3 — Rendimiento (deuda técnica DT-005 y DT-009)

| DT | Descripción | Acción |
|---|---|---|
| **DT-005/009** | `App::visiblesPara()` recalcula 2 subqueries por cada carga del dashboard | Caché con versión global, TTL 5 min |

Estrategia de caché:
- Clave: `apps.visibles.{user_id}.v{cache_version}`
- Versión global: `apps.cache_version` (integer en caché)
- Invalidación: `cache()->increment('apps.cache_version')` en `toggleHabilitada` y `guardarRoles`
- Funciona con cualquier driver (file, redis, memcached)

---

## Archivos a crear/modificar

| Archivo | Tipo | Acción |
|---|---|---|
| `Modules/Apps/Livewire/Apps/AppsIndex.php` | Livewire | Agregar `abort_if` en métodos de escritura + invalidar caché |
| `Modules/Apps/routes/web.php` | Rutas | Agregar `permission:administrar-apps` a `/apps/admin` |
| `Modules/Apps/Entities/App.php` | Modelo | Caché en `visiblesPara()` |
| `Modules/Apps/database/seeders/AppsPermissionSeeder.php` | Seeder | Nuevo — crea permiso y asigna a roles admin |
| `Modules/Apps/database/seeders/AppsDatabaseSeeder.php` | Seeder | Incluir `AppsPermissionSeeder` |
| `Modules/User/Database/Migrations/2026_06_08_120000_make_roles_app_id_nullable_set_null.php` | Migración | Nuevo — corrige FK roles.app_id |
| `Modules/User/Database/Seeders/data/permissions.csv` | CSV | Agregar permiso id 27 |

---

## Restricciones

- No iniciar IMPL-AUTH-001 todavía.
- No unificar los tres sistemas de autorización (DT-003/004) en esta iteración.
- No modificar `app_user.role_id` ni `app_role` — fuera del alcance.
