# Apps — Changelog

Historial de cambios del módulo Apps.
Módulo: `Modules/Apps` — Rutas: `/apps/*`

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
