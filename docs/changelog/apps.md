# Apps — Changelog

Historial de cambios del módulo Apps.
Módulo: `Modules/Apps` — Rutas: `/apps/*`

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
