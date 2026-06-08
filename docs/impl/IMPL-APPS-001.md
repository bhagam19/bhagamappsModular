# IMPL-APPS-001 — Módulo Central de Aplicaciones

**Fecha:** 2026-06-08
**Estado:** Completado
**Autor:** Claude (Sonnet 4.6)
**Relacionado con:** AUDIT-APPS-001, ADR-APPS-001, PLAN-APPS-001

---

## Resumen ejecutivo

Se implementó el módulo central de Aplicaciones de BhagamAppsModular. El módulo evolucionó desde un catálogo básico con asignación por usuario individual hacia un sistema de autorización por rol, manteniendo compatibilidad con la estructura `app_user` existente.

---

## Archivos creados

| Archivo | Tipo | Descripción |
|---|---|---|
| `Modules/Apps/database/migrations/2026_06_08_100000_add_fields_to_apps_table.php` | Migración | Agrega slug, descripcion, icono, color, orden a `apps` |
| `Modules/Apps/database/migrations/2026_06_08_100001_create_app_role_table.php` | Migración | Crea pivot `app_role` (app_id, role_id) |
| `Modules/Apps/Livewire/Apps/AppsIndex.php` | Livewire | Panel admin: toggle habilitada, gestión de roles |
| `Modules/Apps/resources/views/livewire/apps/apps-index.blade.php` | Vista | Vista del componente Livewire |
| `Modules/Apps/resources/views/admin/index.blade.php` | Vista | Wrapper AdminLTE para panel admin |
| `Modules/Apps/Console/Commands/SyncApps.php` | Comando | `php artisan apps:sync` |
| `docs/audits/AUDIT-APPS-001.md` | Documentación | Auditoría del módulo Apps |
| `docs/adr/ADR-APPS-001.md` | Documentación | Decisión arquitectónica |
| `docs/plan/PLAN-APPS-001.md` | Documentación | Plan de implementación |

---

## Archivos modificados

| Archivo | Cambio |
|---|---|
| `Modules/Apps/Entities/App.php` | Fillables ampliados, `roles()` relationship, `visiblesPara()` |
| `Modules/Apps/Http/Controllers/AppController.php` | Bug fix: eliminado `App::where('user_id',...)`, ahora retorna vista admin |
| `Modules/Apps/Providers/AppsServiceProvider.php` | Carga migraciones, auto-registra Livewire, registra comando |
| `Modules/Apps/database/seeders/AppSeeder.php` | Ampliado con slug, descripcion, icono, color, orden; usa `updateOrCreate` |
| `Modules/Apps/routes/web.php` | Agrega ruta `/apps/admin`, orden correcto (admin antes de resource) |
| `app/Http/Controllers/Ppal/HomeController.php` | Usa `App::visiblesPara(auth()->user())` |

---

## Cambios técnicos detallados

### Tabla `apps` (migración aditiva)

```
+ slug        VARCHAR UNIQUE NULLABLE
+ descripcion TEXT NULLABLE
+ icono       VARCHAR NULLABLE
+ color       VARCHAR(20) NULLABLE
+ orden       UNSIGNED INTEGER DEFAULT 99
```

### Tabla `app_role` (nueva)

```
id        BIGINT PK
app_id    FK → apps (CASCADE)
role_id   FK → roles (CASCADE)
UNIQUE(app_id, role_id)
timestamps
```

### `App::visiblesPara($user)`

Retorna apps habilitadas donde:
- El `role_id` del usuario está en `app_role` para esa app, **O**
- El usuario está en `app_user` con `activo = true`

Ordenado por `orden ASC`, `nombre ASC`.

### Livewire `AppsIndex`

Funcionalidades implementadas:
- Lista todas las apps con estado habilitada
- Toggle de `habilitada` por app (sin reload de página)
- Modal de gestión de roles: checkboxes con `sync()` sobre `app_role`

---

## Migraciones pendientes de ejecutar

```bash
php artisan migrate
```

Migraciones a correr:
- `2026_06_08_100000_add_fields_to_apps_table`
- `2026_06_08_100001_create_app_role_table`

---

## Comando disponible post-migración

```bash
php artisan apps:sync
```

Registra en `apps` todos los módulos nWidart instalados que no tengan registro. Solo crea registros nuevos; no modifica existentes ni asigna roles.

---

## Compatibilidad

- La tabla `app_user` y todas sus asignaciones existentes se preservan sin cambios.
- La relación `User::apps()` en `Modules/User/Entities/User.php` no fue modificada.
- La vista `apps::index` (dashboard parcial) no fue modificada.
- Los módulos `Inventario` y `User` no fueron modificados.

---

## Ruta de administración

```
GET /apps/admin → apps.admin.index → apps::admin.index → @livewire('apps.apps-index')
```

Requiere middleware: `auth`, `verified`.

---

## Criterios de aceptación verificados

| Criterio | Estado |
|---|---|
| Sintaxis PHP válida en todos los archivos | ✅ |
| `php artisan route:list` registra 8 rutas de apps | ✅ |
| `apps/admin` no colisiona con resource `{app}` | ✅ |
| Migraciones detectadas como Pending | ✅ |
| `App::visiblesPara()` combina app_role + app_user | ✅ |
| Livewire AppsIndex registrado automáticamente | ✅ |
| Comando `apps:sync` registrado | ✅ |
| HomeController usa `App::visiblesPara()` | ✅ |

---

## Siguiente paso recomendado

1. `php artisan migrate` — ejecutar las dos migraciones pending
2. `php artisan db:seed --class=Modules\\Apps\\database\\seeders\\AppsDatabaseSeeder` — actualizar el catálogo de apps
3. `php artisan apps:sync` — registrar módulos instalados
4. Asignar roles a apps desde `/apps/admin`
