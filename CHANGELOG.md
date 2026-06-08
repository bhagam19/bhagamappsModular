# CHANGELOG — BhagamApps Modular

Resumen ejecutivo de la plataforma. Registra únicamente:
- Versiones globales de BhagamApps.
- Cambios transversales (afectan a múltiples módulos).
- Cambios arquitectónicos.

**Los cambios específicos de cada módulo están en `docs/changelog/`:**
- [`docs/changelog/bhagamapps.md`](docs/changelog/bhagamapps.md) — plataforma completa
- [`docs/changelog/inventario.md`](docs/changelog/inventario.md)
- [`docs/changelog/user.md`](docs/changelog/user.md)
- [`docs/changelog/apps.md`](docs/changelog/apps.md)
- [`docs/changelog/crudgenerator.md`](docs/changelog/crudgenerator.md)

Formato: [Keep a Changelog](https://keepachangelog.com/es/1.0.0/) /
Versionado: [SemVer](https://semver.org/lang/es/) — ver [`VERSIONING.md`](VERSIONING.md)

---

## [v1.4.2] — 2026-06-08

**Módulos afectados:** Core (gobernanza)

### Added (Documentation)

- **[ADR-007]** Registro Oficial de Decisiones Estratégicas de Dirección General.
  Establece `docs/dg/` como directorio oficial para documentos DG-XXX.

---

## [v1.4.1] — 2026-06-08

**Módulos afectados:** Inventario → v2.4.1, v2.4.2

### Fixed

- **[IMPL-004]** `bienes.precio` FLOAT → DECIMAL(12,2): corrección de errores de redondeo monetario.

### Added

- **[IMPL-007]** Carga inicial de 1,420 bienes en producción.
- **[IMPL-008]** Reconciliación completa de versionado y documentación (AUDIT-005).

### Added (Documentation)

- `IMPL-GIT-001`, `ADR-005`, `ADR-006`, `BASELINE-001`, `PMP-001`, `ROADMAP-001`, `EVIDENCE-AUDIT-005`.

---

## [v1.4.0] — 2026-06-08

**Módulos afectados:** Core, User → v2.1.1

### Added

- **[AUDIT-001]** Sistema de Changelog Modal: versiones en footers son ahora enlaces clicables
  que abren un modal Bootstrap 4 con el historial completo del módulo, parseado desde
  `docs/changelog/<modulo>.md`. Implementado como Blade component `<x-changelog-modal module="X" />`.
  Módulos integrados: Inventario, User, BhagamApps (ppal).

### Fixed

- **[IMPL-003]** `permission_role`: eliminados 76 duplicados. Tabla reducida de 156 a 80 registros.

### Security

- **[IMPL-003]** Constraint `UNIQUE(role_id, permission_id)` aplicado en `permission_role`.

### Documentation

- `docs/audits/AUDIT-003-PermissionRole-Duplicates.md` — auditoría previa completa.
- `docs/impl/IMPL-003-PermissionRole-Cleanup.md` — detalle del proceso.
- `docs/impl/backups/permission_role_before_cleanup.sql` — respaldo de los 156 registros originales.

---

## [v1.3.0] — 2026-06-08

**Módulos afectados:** Core, Inventario v2.4.0, User v2.1.0

### Security

- Registro público restringido a roles Docente/Estudiante (IMPL-002).
- Middleware `CheckPermission` operativo en el pipeline de Laravel 11 (IMPL-002).
- Rate limiting en endpoint `/register`: 3 req/min (IMPL-002).

### Fixed

- `CreateNewUser` usaba `App\Models\User` inexistente → corregido a `Modules\User\Entities\User` (IMPL-001).
- `HomeController` consultaba apps por creador, no por usuario asignado (IMPL-001).

### Documentation

- Sistema de versionado modular establecido (`VERSIONING.md`, `docs/changelog/`).
- ADRs: arquitectura modular, estrategia Livewire, CrudGenerator, versionado modular.
- Auditoría AUDIT-002A: ningún usuario con rol privilegiado fue creado mediante registro público.

---

## [v1.2.0] — [antes de 2026-06-08]

> Versión de referencia antes de las fases IMPL-001 e IMPL-002.
> 116 usuarios en producción. 4 módulos operativos.

---

## [v1.0.0] — [puesta en producción]

> Stack inicial: Laravel 11 / PHP 8.4 / nwidart/laravel-modules / Livewire 3 / Fortify.
