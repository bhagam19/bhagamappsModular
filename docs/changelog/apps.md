# Apps — Changelog

Historial de cambios del módulo Apps.
Módulo: `Modules/Apps` — Rutas: `/apps/*`

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
