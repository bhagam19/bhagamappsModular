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

## [v1.6.4] — 2026-06-08

### Fixed

- **[IMPL-APPS-005C]** Dashboard (`apps::index.blade.php` líneas 13 y 46): `href="{{ $app->ruta }}"`
  reemplazado por `href="{{ url($app->ruta) }}"`. Corrige URLs que apuntaban a la raíz del dominio
  en instalaciones con subdirectorio (`APP_URL = https://bhagamapps.com/Modular`).
  Sidebar y menú estático no estaban afectados (ya usaban `url()`).
- **[IMPL-H-005]** `EditarSlugApp.guardar()` ahora invalida `apps.cache_version`
  tras guardar. Dashboard y Sidebar reflejan el cambio de slug de forma inmediata.
- **[IMPL-H-005]** Corrección extendida a `EditarDescripcionApp` y `EditarRutaApp`
  (misma omisión — omitidos en la implementación original). Los 7 componentes
  Editar* del módulo Apps ahora tienen cobertura completa de invalidación de caché.
- Apps: v1.4.2 | BhagamApps: v1.6.4

---

## [v1.6.3] — 2026-06-08

### Removed

- **[IMPL-ADR-009]** `app_user.role_id` eliminado de la base de datos. El campo no
  participaba en ninguna lógica de autorización, visibilidad, middleware ni RBAC.
  ADR-009 formalizó la decisión. La relación `User ↔ App` queda simplificada a
  `(user_id, app_id, activo)`.
- **[IMPL-ADR-009]** FK `app_user_role_id_foreign` eliminada.
- **[IMPL-ADR-009]** DP-001 de ADR-008 cerrada. ADR-008 actualizado.
- Apps: v1.4.0 | BhagamApps: v1.6.3

---

## [v1.6.2] — 2026-06-08

### Security

- **[IMPL-AUTH-002]** Eliminadas 4 dependencias de nombres de rol hardcoded en Gates
  de `AuthServiceProvider`. Gates `usuarios.user`, `admin.grupos`, `admin.evaldoc` y
  `admin.biblioteca` ahora verifican permisos efectivos via `hasPermission()`.
- **[IMPL-AUTH-002]** Creados permisos `ver-grupos`, `ver-evaluacion-docente` y
  `ver-biblioteca` (ids 33–35). Asignados a Administrador, Rector y Coordinador.
- **[IMPL-AUTH-002]** Permiso `ver-usuarios` extendido a Coordinador para mantener
  equivalencia funcional con el gate anterior.
- BhagamApps: v1.6.2 | User: v2.2.1

---

## [v1.6.1] — 2026-06-08

### Security

- **[IMPL-AUTH-001]** Corregidas 3 vulnerabilidades de autorización activas (AUDIT-AUTH-001A):
  - H-001: `GET /apps` ahora requiere `permission:ver-apps` (igual que `/apps/admin`).
  - H-003: 5 métodos de escritura en módulo User/Roles sin protección → agregado
    `abort_if` con permisos `crear-roles`, `editar-roles`, `eliminar-roles`,
    `asignar-permisos-a-roles`.
  - H-004: 5 métodos de escritura en módulo User/Permissions sin protección → agregado
    `abort_if` con permisos `crear-permisos`, `editar-permisos`, `eliminar-permisos`.
- Apps: v1.3.1 | User: v2.2.0

---

## [v1.6.0] — 2026-06-08

**Módulos afectados:** Apps → v1.3.0, User, Inventario, Core

### Added

- **[IMPL-013]** Middleware `CheckAppAccess` — enforcement de acceso a módulos
  via `App::visiblesPara()`. URL directa sin asignación en `app_role`/`app_user`
  retorna 403. Aplicado en Inventario (`app.access:inventario`) y User (`app.access:user`).
- **[IMPL-013]** Menú lateral dinámico en sidebar AdminLTE — sección "MIS MÓDULOS"
  desde `App::visiblesPara()`. Dashboard y menú comparten misma fuente de verdad.
- **[IMPL-013]** Gestión `app_user` — modal en `/apps/admin` para asignación directa
  usuario → app independientemente del rol.

### Fixed

- **[IMPL-013]** Invalidación de caché al cambiar rol de usuario en `EditarRolUser`.
- **[IMPL-013]** Referencia `AppsController` → `AppController` en Apps API routes.

---

## [v1.5.1] — 2026-06-08

**Módulos afectados:** Core (arquitectura / gobernanza)

### Added (Documentation)

- **[ADR-008]** Module Access and Functional Authorization Separation — decisión
  arquitectónica oficial que formaliza la separación de tres capas: visibilidad
  (Apps), acceso a módulos (Apps — enforcement vía IMPL-013), y autorización
  funcional (RBAC). Define `App::visiblesPara($user)` como fuente única de verdad.
  Establece Decisiones Pendientes DP-001 y DP-002 previas a IMPL-013.

---

## [v1.5.0] — 2026-06-08

**Módulos afectados:** Apps → v1.2.0, User (migración correctiva)

### Security

- **[IMPL-APPS-002]** Ruta `/apps/admin` protegida con middleware `permission:administrar-apps`.
  Antes: cualquier usuario autenticado podía acceder al panel de administración de apps.
- **[IMPL-APPS-002]** Métodos Livewire `toggleHabilitada`, `abrirModalRoles`, `guardarRoles`
  protegidos con verificación de permisos en servidor. Cierra vector de bypass de la UI.
- **[IMPL-APPS-002]** Permiso `administrar-apps` creado y asignado a Administrador y Rector.

### Fixed

- **[IMPL-APPS-002]** Migración correctiva `roles.app_id`: cambia `CASCADE DELETE` a `SET NULL`.
  Elimina riesgo de pérdida masiva de roles al eliminar una App del catálogo (DT-001 de AUDIT-APPS-002).

### Performance

- **[IMPL-APPS-002]** `App::visiblesPara($user)` cacheado 5 min por usuario con versión global.
  Reduce de 2 subqueries a 0 queries por usuario en cargas repetidas del dashboard.

---

## [v1.4.8] — 2026-06-08

**Módulos afectados:** User → v2.1.2

### Fixed

- **[IMPL-012]** Corregidos 9 slugs de permisos incorrectos en vistas Blade del módulo User.
  `crear-users`, `editar-user`, `editar-users`, `eliminar-users` → slugs correctos según
  `permissions.csv`. Afectaba a: formulario de creación, edición inline desktop/móvil y
  botones de eliminación — completamente invisibles pese a que el usuario tuviera permisos.
- **[IMPL-012]** Bug crítico en `EditarRolUser.mount()`: inicializaba `$role_id` con el
  nombre del rol (string) causando corrupción silenciosa de la FK integer al guardar.
  Corregido a `$user->role_id`.
- **[IMPL-012]** Roles cargados dinámicamente desde BD en `editar-rol-user.blade.php`.
  Eliminada lista hardcodeada de 7 opciones fijas.

---

## [v1.4.7] — 2026-06-08

**Módulos afectados:** Core (gobernanza)

### Added (Documentation)

- **[DG-015]** Autorización formal de IMPL-005 HTTPS and Secure Session Hardening
  persistida. Trazabilidad completa: AUDIT-006 → PLAN-IMPL-010 → DG-015 → IMPL-005.
- **[IMPL-005]** HTTPS and Secure Session Hardening persistido. Estado: SUSPENDIDA
  TEMPORALMENTE por bloqueo DNS/Let's Encrypt. Diagnóstico completo incluido.
  Requiere registro CAA en GoDaddy DNS para desbloquear emisión SSL.
- **[DG-016]** Autorización formal de IMPL-006 SMTP Configuration and Mail Delivery
  persistida. Trazabilidad completa: AUDIT-006 → PLAN-IMPL-010 → DG-016 → IMPL-006.
- **[IMPL-006]** SMTP Configuration and Mail Delivery persistido. Estado: EN DIAGNÓSTICO.
  Exim 4.97 disponible localmente. Sin credenciales externas ni registros DNS de correo.

---

## [v1.4.6] — 2026-06-08

**Módulos afectados:** Core (gobernanza)

### Added (Documentation)

- **[PLAN-IMPL-010]** Plan de hardening de seguridad e infraestructura de producción
  persistido formalmente. Cuatro fases: IMPL-005 (HTTPS), IMPL-006 (SMTP),
  IMPL-010 (Infraestructura), IMPL-011 (Mantenimiento). Estado: APROBADO.

---

## [v1.4.5] — 2026-06-08

**Módulos afectados:** Core (seguridad)

### Security

- **[IMPL-009]** Removed 3 publicly accessible diagnostic files from `public/`:
  `test_proc_open.php` (shell execution via `proc_open`), `test.php`, `info.php`.

---

## [v1.4.4] — 2026-06-08

**Módulos afectados:** Core (gobernanza)

### Added (Documentation)

- **[PLAN-IMPL-008]** Plan de reconciliación de versionado y documentación
  persistido formalmente. Estado: EJECUTADO. Riesgo residual: BAJO.

---

## [v1.4.3] — 2026-06-08

**Módulos afectados:** Core (gobernanza)

### Added (Documentation)

- **[AUDIT-005]** Versioning and Changelog Compliance Audit persistida formalmente.
  Estado: CERRADA. Riesgo residual: BAJO.

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
