# Apps — Changelog

Historial de cambios del módulo Apps.
Módulo: `Modules/Apps` — Rutas: `/apps/*`

---

## v1.5.0 — 2026-06-09

### Removed

- **[IMPL-APPS-006]** Eliminados 12 registros legacy del catálogo de aplicaciones (IDs 1-12).
  Estos registros correspondían al sistema anterior: sin `slug`, `habilitada=false`, `user_id=1`,
  creados el 2026-06-07. Duplicaban nombre y ruta con el catálogo oficial (IDs 13-24).
  La eliminación fue ejecutada vía migración `2026_06_09_200000_cleanup_legacy_apps`.
  - `app_role`: 8 registros de pivote legacy eliminados por CASCADE.
  - `roles.app_id`: 7 roles quedan con `app_id=NULL` por SET NULL (FK diseñada para este caso).

### Fixed

- **[IMPL-APPS-006]** `RoleSeeder`: corregida referencia de `App::where('nombre', 'user')`
  a `App::where('slug', 'user')`. El seeder referenciaba el registro legacy eliminado
  (nombre="User", ID=1) en lugar del registro oficial (slug="user", ID=16 "Usuarios").

---

## v1.4.3 — 2026-06-09

### Fixed

- **[IMPL-APPS-005F]** Rutas del módulo Apps ahora registradas bajo el middleware group `web`.
  `AppsServiceProvider` usaba `loadRoutesFrom()` directamente, sin envolverlas en `Route::middleware('web')`.
  Consecuencia: `StartSession` no se ejecutaba → `Auth::check() = false` → redirección a login
  → `RedirectIfAuthenticated` enviaba a `/Modular/` aunque el usuario tuviera permisos válidos.
  Solución: se registra `RouteServiceProvider` en `module.json` (Opción B), que ya implementaba
  `mapWebRoutes()` con `Route::middleware('web')`. Se elimina el `loadRoutesFrom()` redundante
  de `AppsServiceProvider`. Las rutas `/apps` y `/apps/admin` ahora ejecutan bajo `web`,
  `Authenticate`, `EnsureEmailIsVerified` y `CheckPermission:ver-apps`.

---

## v1.4.2 — 2026-06-08

### Fixed

- **[IMPL-APPS-005C]** `apps::index.blade.php` líneas 13 y 46: `href="{{ $app->ruta }}"`
  reemplazado por `href="{{ url($app->ruta) }}"`. En instalaciones desplegadas en subdirectorio
  (`APP_URL=https://bhagamapps.com/Modular`), el path absoluto `/inventario/bienes` se resolvía
  desde la raíz del dominio ignorando `/Modular`. El helper `url()` genera URLs absolutas
  respetando el subdirectorio. Sidebar (`left-sidebar.blade.php`) ya usaba `url()` — no afectado.

---

## v1.4.1 — 2026-06-08

### Fixed

- **[IMPL-H-005 / H-005]** `EditarSlugApp.guardar()` no invalidaba `apps.cache_version`.
  Dashboard y Sidebar podían mostrar el slug anterior hasta 300 segundos. Corregido.
- **[IMPL-H-005]** Misma omisión detectada y corregida en `EditarDescripcionApp.guardar()`
  y `EditarRutaApp.guardar()`. Los 7 componentes `Editar*` del módulo Apps tienen
  ahora cobertura uniforme: `$app->save()` → `cache()->increment('apps.cache_version')`.

---

## v1.4.0 — 2026-06-08

### Removed

- **[IMPL-ADR-009]** `app_user.role_id` eliminado del modelo de datos y de la
  migración de la tabla `app_user`. El campo no participaba en ninguna lógica del
  módulo Apps (ni en `visiblesPara()`, ni en `CheckAppAccess`, ni en `AppsIndex`,
  ni en los modales de gestión de usuarios). La relación `User ↔ App` queda
  simplificada a `(user_id, app_id, activo)`.
- FK `app_user_role_id_foreign` eliminada.
- DP-001 de ADR-008 resuelta y cerrada.

---

## v1.3.1 — 2026-06-08

### Security

- **[IMPL-AUTH-001 / H-001]** Ruta `GET /apps` (resource index) ahora requiere
  `permission:ver-apps`. Antes: cualquier usuario autenticado podía acceder al panel
  de administración de apps por esta URL alternativa, omitiendo la protección de
  `GET /apps/admin`. Corrección: `->middleware('permission:ver-apps')` agregado al
  `Route::resource`.
- **[IMPL-AUTH-001]** Ajuste de consistencia: ruta `GET /apps/admin` usa
  `permission:ver-apps` (acceso de lectura al panel). Las operaciones de escritura
  siguen protegidas por `administrar-apps` en los Livewire components.

---

## v1.3.0 — 2026-06-08

### Added

- **[IMPL-013]** Gestión funcional de usuarios directos (`app_user`): nuevo modal en
  `/apps/admin` para asignar y remover usuarios individualmente a una app. El modal
  lista todos los usuarios con su rol, ordenados por apellidos. Sincronización via
  `app->user()->sync()` con `activo = true`. Botón en columna Acciones con badge
  de conteo de usuarios directos.
- **[IMPL-013]** Métodos `abrirModalUsuarios`, `guardarUsuarios`, `cerrarModalUsuarios`
  en `AppsIndex`. Siguen el mismo patrón que la gestión de roles existente.

### Fixed

- **[IMPL-013]** Corregida referencia `AppsController` (inexistente) → `AppController`
  en `Modules/Apps/routes/api.php`. Las rutas API dejarán de fallar con "class not found"
  al ser invocadas.

### References

- AUDIT-APPS-003, ADR-008, IMPL-013


---

## v1.2.0 — 2026-06-08

### Security

- Ruta `GET /apps/admin` protegida con middleware `permission:administrar-apps`.
  Cualquier usuario sin ese permiso recibe 403. Antes: solo requería `auth` + `verified`.
- Métodos Livewire `toggleHabilitada`, `abrirModalRoles`, `guardarRoles` protegidos con
  `abort_if(! hasPermission('administrar-apps'), 403)`. Previene invocación directa por
  usuarios sin permisos desde el cliente Livewire.

### Added

- Permiso `administrar-apps` (slug: `administrar-apps`, categoría: `apps`).
  Asignado por defecto a roles Administrador y Rector.
- `AppsPermissionSeeder` — seeder idempotente para crear el permiso en instalaciones existentes.
  Ejecutar: `php artisan db:seed --class="Modules\Apps\database\seeders\AppsDatabaseSeeder"`.

### Fixed

- Migración `2026_06_08_120000_make_roles_app_id_nullable_set_null.php` (en módulo User):
  cambia `roles.app_id` de `NOT NULL + CASCADE` a `NULLABLE + SET NULL`.
  Elimina el riesgo de pérdida masiva de roles al eliminar una App.

### Performance

- `App::visiblesPara($user)` ahora cacheado 5 min por usuario con clave versionada
  `apps.visibles.{user_id}.v{version}`. El contador `apps.cache_version` se incrementa
  en `toggleHabilitada` y `guardarRoles`, invalidando eficientemente la caché global.

### References

- AUDIT-APPS-002, PLAN-APPS-002, IMPL-APPS-002

---

## v1.1.0 — 2026-06-08

### Added

- Columnas `slug`, `descripcion`, `icono`, `color`, `orden` en tabla `apps` (migración aditiva).
- Tabla pivot `app_role` para autorización de apps por rol.
- Método estático `App::visiblesPara($user)` — combina visibilidad por rol (`app_role`) y por usuario (`app_user`).
- Relación `App::roles()` → `belongsToMany` con tabla `app_role`.
- Componente Livewire `AppsIndex` — panel admin con toggle de habilitada y gestión de roles.
- Vista `apps::admin.index` — wrapper AdminLTE para panel admin.
- Comando Artisan `php artisan apps:sync` — sincroniza módulos nWidart en la tabla `apps`.
- Ruta `GET /apps/admin` → `apps.admin.index`.

### Changed

- `AppsServiceProvider` actualizado: carga migraciones, auto-registra Livewire, registra comando.
- `AppController::index()` corregido: eliminado bug `App::where('user_id', ...)`, ahora retorna vista admin.
- `HomeController::index()` simplificado con `App::visiblesPara(auth()->user())`.
- `AppSeeder` actualizado: agrega slug, descripcion, icono, color, orden; usa `updateOrCreate`.

### References

- AUDIT-APPS-001, ADR-APPS-001, PLAN-APPS-001, IMPL-APPS-001

---

## v1.0.0 — 2025-06-07

### Added

- Creación inicial del módulo Apps como módulo central para la plataforma.
- Catálogo de aplicaciones asignables a usuarios.
- Relación many-to-many `app_user` con campo `activo` en pivot y `habilitada` en `apps`.
- Las apps asignadas al usuario se muestran en el dashboard principal.

> **Nota:** La corrección del `HomeController` (IMPL-001, 2026-06-08) afecta cómo
> el core consulta las apps del usuario (`auth()->user()->apps()->wherePivot(...)`),
> pero el módulo Apps en sí no fue modificado. Ese fix pertenece al core
> (`app/Http/Controllers/Ppal/HomeController.php`).
