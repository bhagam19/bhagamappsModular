# IMPL-APPS-002 — Cierre de Bloqueantes Críticos del Módulo Apps

**Fecha:** 2026-06-08
**Estado:** Completado
**Autor:** Claude (Sonnet 4.6)
**Relacionado con:** AUDIT-APPS-002, PLAN-APPS-002, IMPL-APPS-001

---

## Resumen ejecutivo

Se resolvieron los tres bloqueantes identificados en AUDIT-APPS-002 que impedían declarar
el módulo Apps apto para producción:

1. **Seguridad** — `/apps/admin` y métodos Livewire protegidos con permiso `administrar-apps`.
2. **Integridad** — Riesgo de pérdida masiva de roles por CASCADE eliminado con migración correctiva.
3. **Rendimiento** — `App::visiblesPara()` cacheado con estrategia de versión global.

---

## Archivos creados

| Archivo | Tipo | Descripción |
|---|---|---|
| `Modules/Apps/database/seeders/AppsPermissionSeeder.php` | Seeder | Crea permiso `administrar-apps` e idempotentemente lo asigna a Administrador y Rector |
| `Modules/User/Database/Migrations/2026_06_08_120000_make_roles_app_id_nullable_set_null.php` | Migración | Hace `roles.app_id` nullable con `SET NULL` on delete |
| `docs/plan/PLAN-APPS-002.md` | Documentación | Plan de implementación |

---

## Archivos modificados

| Archivo | Cambio |
|---|---|
| `Modules/Apps/Livewire/Apps/AppsIndex.php` | `abort_if(!hasPermission('administrar-apps'), 403)` en `toggleHabilitada`, `abrirModalRoles`, `guardarRoles`; invalidación de caché en escritura |
| `Modules/Apps/routes/web.php` | Middleware `permission:administrar-apps` en `GET /apps/admin` |
| `Modules/Apps/Entities/App.php` | Caché en `visiblesPara()` con clave `apps.visibles.{user_id}.v{version}` |
| `Modules/Apps/database/seeders/AppsDatabaseSeeder.php` | Incluye `AppsPermissionSeeder` |
| `Modules/User/Database/Seeders/data/permissions.csv` | Nuevo registro id 27: `administrar-apps` |

---

## Detalle técnico por fase

### Fase 1 — Seguridad

**Permiso creado:** `administrar-apps`

| Campo | Valor |
|---|---|
| slug | `administrar-apps` |
| nombre | administrar apps |
| categoria | apps |
| Roles con acceso | Administrador, Rector |

**Protección de ruta:**

```php
// routes/web.php — antes
Route::get('/apps/admin', [AppController::class, 'index'])->name('apps.admin.index');

// después
Route::get('/apps/admin', [AppController::class, 'index'])
    ->name('apps.admin.index')
    ->middleware('permission:administrar-apps');
```

**Protección Livewire (en los tres métodos de escritura):**

```php
abort_if(! auth()->user()->hasPermission('administrar-apps'), 403);
```

El `CheckPermission` middleware ya existía; se reutiliza el mismo mecanismo. No se crean
mecanismos nuevos.

**Seeder idempotente para instalaciones existentes:**

```bash
php artisan db:seed --class="Modules\Apps\database\seeders\AppsDatabaseSeeder"
```

Usa `Permission::firstOrCreate` y `$rol->permissions()->attach()` con verificación previa
— puede ejecutarse múltiples veces sin efectos secundarios.

---

### Fase 2 — Integridad

**Migración:** `2026_06_08_120000_make_roles_app_id_nullable_set_null.php`

```
Antes: roles.app_id BIGINT NOT NULL FK → apps(id) ON DELETE CASCADE
Después: roles.app_id BIGINT NULL FK → apps(id) ON DELETE SET NULL
```

**Impacto en producción:**

| Escenario | Antes | Después |
|---|---|---|
| Eliminar App con roles sin usuarios | Roles eliminados en cascada (silencioso) | `app_id = NULL`, roles intactos |
| Eliminar App con roles que tienen usuarios | Error FK RESTRICT (falla ruidoso) | `app_id = NULL`, roles y usuarios intactos |
| RoleSeeder | `app_id` required | `app_id` ahora nullable — compatible sin cambios |

**Ejecutar:**
```bash
php artisan migrate
```

---

### Fase 3 — Rendimiento

**Estrategia de caché:**

```php
// Clave: apps.visibles.{user_id}.v{version}
// TTL: 300 segundos (5 minutos)
// Invalidación: cache()->increment('apps.cache_version')
```

El contador `apps.cache_version` actúa como "generation counter":
- Todas las claves antiguas quedan huérfanas (expiran naturalmente a los 5 min)
- No requiere listar ni borrar claves individualmente
- Compatible con cualquier driver de caché (file, redis, memcached, array)

**Cuándo se invalida:**
- `toggleHabilitada()` — cambio de estado de una app afecta a todos los usuarios
- `guardarRoles()` — cambio de roles asignados afecta a usuarios de esos roles

---

## Procedimiento de activación en producción

```bash
# 1. Ejecutar migración correctiva (Fase 2)
php artisan migrate

# 2. Crear el permiso administrar-apps y asignarlo a Administrador/Rector (Fase 1)
php artisan db:seed --class="Modules\Apps\database\seeders\AppsDatabaseSeeder"

# 3. La caché se activa automáticamente al primer acceso (Fase 3)
# No se requiere paso adicional.
```

---

## Bloqueantes resueltos

| ID | Descripción | Estado |
|---|---|---|
| B2 | `/apps/admin` sin protección | ✅ Resuelto — middleware `permission:administrar-apps` |
| B3 | Métodos Livewire sin verificación | ✅ Resuelto — `abort_if` en `toggleHabilitada`, `abrirModalRoles`, `guardarRoles` |
| DT-001 | CASCADE DELETE en `roles.app_id` | ✅ Resuelto — migración a `SET NULL` |
| DT-005/009 | `visiblesPara()` sin caché | ✅ Resuelto — caché con versión global, TTL 5 min |

## Bloqueantes pendientes (fuera del alcance de esta iteración)

| ID | Descripción |
|---|---|
| DT-003 | Tres sistemas de autorización paralelos sin coordinación → IMPL-AUTH-001 |
| DT-004 | Gates con nombres de roles hardcoded → IMPL-AUTH-001 |
| Caso A | App visible en dashboard pero 403 al ingresar → IMPL-AUTH-001 |
