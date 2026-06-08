# BhagamApps — Changelog de Plataforma

Registra cambios **transversales** y **arquitectónicos** que afectan a la plataforma
como un todo o a múltiples módulos simultáneamente.

Changelogs de módulo:
- [`docs/changelog/inventario.md`](inventario.md)
- [`docs/changelog/user.md`](user.md)
- [`docs/changelog/apps.md`](apps.md)
- [`docs/changelog/crudgenerator.md`](crudgenerator.md)

---

## v1.4.0 — 2026-06-08

**Módulos afectados:** Core, User → v2.1.1

### Added

- **[AUDIT-001]** Sistema de Changelog Modal: cada módulo muestra su versión como
  enlace clicable en el footer. Al hacer clic se abre un modal con el historial
  completo del módulo, parseado desde `docs/changelog/<modulo>.md`.
  Implementado como Blade component `<x-changelog-modal module="X" />`.
  Módulos integrados: Inventario, User, BhagamApps (ppal).

### Fixed

- **[IMPL-003]** `permission_role`: eliminados 76 registros duplicados causados por
  doble ejecución del seeder `Permission_RoleSeeder`.
  Tabla reducida de 156 a 80 registros.

### Security

- **[IMPL-003]** Constraint `UNIQUE(role_id, permission_id)` aplicado en `permission_role`.
  Previene duplicados por re-ejecución de seeders o inserciones directas.

### Documentation

- `docs/audits/AUDIT-003-PermissionRole-Duplicates.md` — auditoría completa previa.
- `docs/impl/IMPL-003-PermissionRole-Cleanup.md` — proceso detallado.
- `docs/impl/backups/permission_role_before_cleanup.sql` — respaldo de los 156 registros originales.
- AUDIT-004 API Authentication Audit approved and persisted.

---

## v1.3.0 — 2026-06-08

**Módulos afectados:** Core, Inventario → v2.4.0, User → v2.1.0

### Security

- **[IMPL-002]** Registro público restringido a roles Docente (5) y Estudiante (6).
  Antes, el formulario enviaba `role_id=2` (Rector) etiquetado como "Docente" y
  `role_id=3` (Coordinador) etiquetado como "Estudiante".
- **[IMPL-002]** Middleware `CheckPermission` registrado en el pipeline de Laravel 11
  (`bootstrap/app.php`). El alias existía en `Kernel.php` pero no estaba disponible
  para rutas de módulos bajo el nuevo sistema de middleware de Laravel 11.
- **[IMPL-002]** Rate limiting para `/register`: 3 intentos por minuto por IP.

### Fixed

- **[IMPL-001]** `CreateNewUser`: import corregido de `App\Models\User` (inexistente)
  a `Modules\User\Entities\User`. El registro de usuarios fallaba en producción.
- **[IMPL-001]** `HomeController`: query de aplicaciones del usuario corregida.
  Usaba `App::where('user_id', ...)` que filtra por creador. Ahora usa la relación
  `apps()` con filtros de pivot `activo` y campo `habilitada`.

### Documentation

- Sistema de versionado modular establecido (`VERSIONING.md`, `docs/changelog/`).
- ADRs documentados: arquitectura modular, estrategia Livewire, CrudGenerator,
  versionado modular.
- `docs/audits/AUDIT-002A-Registro-Roles.md`: auditoría de usuarios con roles
  privilegiados. Conclusión: bug de registro no fue explotado; todos los usuarios
  son legítimos.

---

## v1.2.0 — 2025-06-23

**Módulos afectados:** CrudGenerator → v1.0.0 → v1.1.0, User → v2.0.0

### Added

- Implementado módulo `CrudGenerator` para generación automática de CRUDs.
- Comandos Artisan para crear y limpiar CRUDs generados.
- Gestión automática de permisos y configuración de `AuthServiceProvider`.
- Inclusión dinámica de CRUDs generados en el menú AdminLTE.
- Rutas web y API configuradas para los CRUDs generados.
- Componentes Livewire integrados para manejo dinámico de datos.
- Archivos `stub` editables para vistas, rutas, permisos y menú.

### Changed

- Refactor: módulo `Users` renombrado a `User` con nueva estructura modular.
- Reorganización del sistema de navegación y rutas generales.
- Limpieza de vistas antiguas y actualización de vistas administrativas.
- Refactor del sistema de notificaciones.
- Ajustes en assets y configuración del frontend.

---

## v1.1.0 — 2025-05-22

**Módulos afectados:** Users → v1.0.0, Inventario → v2.0.0

### Added

- Migración a estructura modular (nwidart/laravel-modules).
- Creación inicial de los módulos `Users` e `Inventario`.

---

## v1.0.0 — 2025-05-20

### Added

- Creación inicial de BhagamApps con Laravel 11.
- Integración con Jetstream y Livewire.
- Estructura modular inicial.
- Preparación del módulo Users (usuarios, roles y permisos).
