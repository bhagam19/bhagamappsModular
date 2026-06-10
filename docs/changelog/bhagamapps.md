# BhagamApps — Changelog de Plataforma

Registra cambios **transversales** y **arquitectónicos** que afectan a la plataforma
como un todo o a múltiples módulos simultáneamente.

Changelogs de módulo:
- [`docs/changelog/inventario.md`](inventario.md)
- [`docs/changelog/user.md`](user.md)
- [`docs/changelog/apps.md`](apps.md)
- [`docs/changelog/crudgenerator.md`](crudgenerator.md)

---

## v1.11.0 — 2026-06-10

### Added

- **[IMPL-INV-006]** Inventario v2.10.0: Mantenimientos Programados — gestión CRUD completa
  sobre infraestructura pre-existente (`mantenimientos_programados`). Entidad completada,
  Livewire `MantenimientosProgramadosIndex`, 4 permisos RBAC, ruta
  `/inventario/mantenimientos/programados`, sidebar actualizado.
  Ver [`docs/changelog/inventario.md`](inventario.md) para detalle completo.

---

## v1.10.0 — 2026-06-10

### Added

- **[IMPL-INV-005]** Inventario v2.9.0: Historial de Ubicaciones — trazabilidad física completa
  de bienes. Tabla `historial_ubicaciones_bienes`, 2 Livewire components, 2 permisos RBAC,
  ruta `/inventario/ubicaciones/historial`, columna "Ubicación Actual" en BienesIndex.
  Ver [`docs/changelog/inventario.md`](inventario.md) para detalle completo.

---

## v1.9.4 — 2026-06-10

### Fixed

- **[IMPL-INV-004 suplemento]** Validaciones residuales post-remediación: sort SQL bug en `BienesIndex`,
  Gates huérfanos en `AuthServiceProvider`, código muerto en `EditarCampoBien` y vista bienes.
  Inventario v2.8.2. Ver [`docs/changelog/inventario.md`](inventario.md) para detalle.

---

## v1.9.3 — 2026-06-10

### Fixed

- **[IMPL-INV-004]** Paquete de remediación técnica del módulo Inventario (GAP-001/002/004 de AUDIT-INV-003).
  Inventario v2.8.1. Ver [`docs/changelog/inventario.md`](inventario.md) para detalle.

---

## v1.9.2 — 2026-06-09

### Added

- **[IMPL-INV-002A]** Inventario v2.8.0: integración completa de navegación para 7 catálogos y HEB.
  Gate HEB corregido. IMPL-INV-002 cerrado definitivamente.

---

## v1.9.1 — 2026-06-09

### Fixed

- **[IMPL-INV-003A]** Inventario v2.7.1: entrada "Responsables" añadida al menú lateral.
  Cierre formal de IMPL-INV-003. IMPL-INV-004 autorizado.

---

## v1.9.0 — 2026-06-09

### Added

- **[IMPL-INV-003]** Inventario: Gestión completa de Responsables y Custodios de bienes.
  Nueva sección `/inventario/responsables`, flujo de asignación/transferencia/liberación,
  historial completo inline, 4 permisos, 4 gates, columna Custodio en BienesIndex,
  relaciones `responsableActual()` y `bienesAsignados()`.

---

## v1.8.0 — 2026-06-09

### Added

- **[IMPL-INV-002]** Inventario: CRUD administrativos completos para 7 catálogos maestros
  (Categorías, Dependencias, Ubicaciones, Estados de Bien, Orígenes, Almacenamientos, Mantenimientos).
  28 nuevos permisos en la plataforma, nueva tabla `origenes`, 7 componentes Livewire,
  7 rutas protegidas, 28 Gates en AuthServiceProvider.
  Ver [`docs/changelog/inventario.md`](inventario.md) para detalle completo.
- Inventario: v2.5.0 → v2.6.0 | BhagamApps: v1.7.1 → v1.8.0

---

## v1.7.1 — 2026-06-09

### Fixed

- **[IMPL-INV-001]** Inventario: correcciones críticas derivadas de AUDIT-INV-001.
  Permiso HEB creado y asignado, tabla `bienes_responsables` creada, Coordinador
  habilitado para acceso al módulo, null check en HmbIndex corregido.
  Ver [`docs/changelog/inventario.md`](inventario.md) para detalle completo.
- Inventario: v2.5.0 | BhagamApps: v1.7.1

---

## v1.7.0 — 2026-06-09

### Removed

- **[IMPL-APPS-006]** Catálogo de aplicaciones saneado: 12 registros legacy eliminados de
  la tabla `apps` (IDs 1-12, sin slug, del sistema anterior). El catálogo oficial queda con
  12 registros únicos (IDs 13-24), todos con slug definido, sin duplicados de nombre ni ruta.
  Módulo Apps declarado **APTO PARA BASELINE ESTABLE**.

### Changed

- **[IMPL-APPS-006]** `User/Database/Seeders/RoleSeeder`: referencia de app por `nombre`
  corregida a referencia por `slug` para soportar fresh installs post-cleanup.

- Apps: v1.5.0 | BhagamApps: v1.7.0

### References

- AUDIT-APPS-006 (H-001, H-002, H-003), IMPL-APPS-006

---

## v1.6.5 — 2026-06-09

### Fixed

- **[IMPL-APPS-005F]** Módulo Apps: registro de rutas web restaurado bajo middleware group `web`.
  `RouteServiceProvider` activado en `module.json`; `loadRoutesFrom()` eliminado de
  `AppsServiceProvider`. Sesión, autenticación y permisos ahora funcionales en `/apps/admin`.
- Apps: v1.4.3

### References

- AUDIT-APPS-005F, IMPL-APPS-005F

---

## v1.6.4 — 2026-06-08

### Fixed

- **[IMPL-APPS-005C]** Dashboard: URLs de módulo ahora generadas con `url()`. En subdirectorios
  de despliegue (`APP_URL=https://bhagamapps.com/Modular`), `href="{{ $app->ruta }}"` directo
  producía `https://bhagamapps.com/inventario/bienes` (raíz de dominio). Corregido a
  `href="{{ url($app->ruta) }}"` → `https://bhagamapps.com/Modular/inventario/bienes`.
  Afectados: `apps::index.blade.php` líneas 13 (escritorio) y 46 (móvil).
- **[IMPL-H-005]** `EditarSlugApp`, `EditarDescripcionApp`, `EditarRutaApp`: invalidación de
  `apps.cache_version` tras `save()`. Dashboard y Sidebar reflejan cambios inmediatamente.
- Apps: v1.4.2

### References

- AUDIT-APPS-005B, IMPL-APPS-005C, IMPL-H-005

---

## v1.6.3 — 2026-06-08

### Removed

- **[IMPL-ADR-009]** `app_user.role_id` eliminado del modelo de datos. Campo sin
  semántica funcional: no participaba en `App::visiblesPara()`, `CheckAppAccess`,
  middleware, gates, permissions, dashboard ni sidebar.
- **[IMPL-ADR-009]** FK `app_user_role_id_foreign → roles(id) ON DELETE SET NULL`
  eliminada.
- **[IMPL-ADR-009]** DP-001 de ADR-008 cerrada formalmente. La relación `User ↔ App`
  queda reducida a `(user_id, app_id, activo)`.

### References

- ADR-009, AUDIT-APPS-003, AUDIT-APPS-004, ADR-008 (DP-001), IMPL-ADR-009

---

## v1.6.2 — 2026-06-08

### Security

- **[IMPL-AUTH-002]** Eliminadas 4 dependencias hardcoded de nombres de rol en Gates
  de `AuthServiceProvider`. `usuarios.user`, `admin.grupos`, `admin.evaldoc` y
  `admin.biblioteca` ahora delegan a `hasPermission()` en lugar de comparar
  `$user->role->nombre` directamente.
- **[IMPL-AUTH-002]** Creados 3 permisos stub para secciones futuras: `ver-grupos`,
  `ver-evaluacion-docente`, `ver-biblioteca`. Asignados a Administrador, Rector y
  Coordinador para mantener equivalencia funcional.
- **[IMPL-AUTH-002]** `ver-usuarios` asignado a Coordinador (correctivo de omisión
  en seeder original).
- Import `use Modules\User\Entities\User` eliminado de `AuthServiceProvider`
  (quedó huérfano al remover todos los `instanceof User` checks).

### References

- AUDIT-AUTH-001A (H-009), PLAN-AUTH-001, ADR-AUTHORIZATION-002, IMPL-AUTH-002

---

## v1.6.1 — 2026-06-08

### Security

- **[IMPL-AUTH-001]** Corregidas 3 vulnerabilidades de autorización activas (AUDIT-AUTH-001A):
  H-001 (`GET /apps` sin protección), H-003 (escritura de Roles sin abort_if),
  H-004 (escritura de Permissions sin abort_if).
- Apps: v1.3.1 | User: v2.2.0

---

## v1.6.0 — 2026-06-08

**Módulos afectados:** Apps → v1.3.0, User, Inventario, Core

### Added

- **[IMPL-013]** Middleware `CheckAppAccess` (`app/Http/Middleware/CheckAppAccess.php`).
  Verifica que la app solicitada esté en `App::visiblesPara($user)` antes de servir
  la ruta. Registrado como alias `app.access` en Kernel y bootstrap/app.php.
- **[IMPL-013]** Enforcement de acceso a módulos via `app.access:slug` en rutas de
  Inventario y User. URL directa a módulo sin asignación en `app_role`/`app_user`
  retorna 403.
- **[IMPL-013]** Menú lateral dinámico: sección "MIS MÓDULOS" en sidebar de AdminLTE
  construida desde `App::visiblesPara(auth()->user())`. Dashboard y menú comparten
  la misma fuente de verdad.
- **[IMPL-013]** Gestión funcional de `app_user`: modal en `/apps/admin` para asignar
  apps directamente a usuarios individuales con `activo = true`.

### Fixed

- **[IMPL-013]** Invalidación de caché al cambiar rol de usuario (`EditarRolUser::guardar()`).
  El cambio de rol ahora incrementa `apps.cache_version`, forzando recálculo inmediato
  de apps visibles para el usuario afectado.

### References

- AUDIT-APPS-003, ADR-008, IMPL-013

---

## v1.5.1 — 2026-06-08

**Módulos afectados:** Core (arquitectura / gobernanza)

### Added (Documentation)

- `docs/adr/ADR-008-Module-Access-and-Functional-Authorization-Separation.md` —
  Decisión arquitectónica oficial que formaliza la separación entre:
  visibilidad de módulos (Apps), acceso a módulos (Apps — enforcement pendiente)
  y autorización funcional (RBAC personalizado). Derivado de AUDIT-APPS-003.
  Define `App::visiblesPara($user)` como fuente oficial de verdad para visibilidad
  y acceso. Establece DP-001 (semántica de `app_user.role_id`) y DP-002 (contrato
  del middleware de acceso) como decisiones pendientes previas a IMPL-013.

---

## v1.4.8 — 2026-06-08

**Módulos afectados:** User → v2.1.2

### Fixed

- **[IMPL-012]** Users CRUD Parity Fix: corregidos 9 slugs de permisos incorrectos
  en vistas Blade del módulo User (`crear-users`, `editar-user`, `editar-users`,
  `eliminar-users`). Formulario de creación, edición inline y eliminación estaban
  completamente invisibles para usuarios con permisos válidos.
- **[IMPL-012]** Bug crítico en `EditarRolUser`: `role_id` se inicializaba con
  el nombre del rol (string) en lugar de la FK integer. Corregido a `$user->role_id`.
- **[IMPL-012]** Roles cargados dinámicamente en `editar-rol-user.blade.php`
  desde base de datos. Eliminada lista hardcodeada de 7 roles fijos.

---

## v1.4.7 — 2026-06-08

**Módulos afectados:** Core (gobernanza)

### Documentation

- `docs/dg/DG-015-Ejecucion-IMPL-005-HTTPS-and-Secure-Session-Hardening.md` —
  DG-015 persistido. Autorización de IMPL-005 HTTPS and Secure Session Hardening.
  Trazabilidad: AUDIT-006 → PLAN-IMPL-010 → DG-015 → IMPL-005.
- `docs/impl/IMPL-005-HTTPS-and-Secure-Session-Hardening.md` —
  IMPL-005 persistido. Estado: SUSPENDIDA TEMPORALMENTE. Bloqueo externo:
  error Let's Encrypt DNS SERVFAIL en CAA — requiere registro CAA en GoDaddy.
  Diagnóstico completo documentado. Hallazgos H-001, H-002, H-004 pendientes.
- `docs/dg/DG-016-Ejecucion-IMPL-006-SMTP-Configuration-and-Mail-Delivery.md` —
  DG-016 persistido. Autorización de IMPL-006 SMTP Configuration and Mail Delivery.
  Trazabilidad: AUDIT-006 → PLAN-IMPL-010 → DG-016 → IMPL-006.
- `docs/impl/IMPL-006-SMTP-Configuration-and-Mail-Delivery.md` —
  IMPL-006 persistido. Estado: AUTORIZADA — EN DIAGNÓSTICO. Diagnóstico inicial
  completo: MAIL_MAILER=log, sin credenciales SMTP, Exim 4.97 disponible en
  localhost, sin MX/SPF/DKIM en DNS. Hallazgo H-005 pendiente.

---

## v1.4.6 — 2026-06-08

**Módulos afectados:** Core (gobernanza)

### Documentation

- `docs/plan/PLAN-IMPL-010-Production-Security-and-Infrastructure-Hardening.md` —
  PLAN-IMPL-010 persistido. Plan de hardening de seguridad e infraestructura de
  producción derivado de AUDIT-006: cuatro fases (IMPL-005, IMPL-006, IMPL-010,
  IMPL-011), estado APROBADO, riesgo residual esperado BAJO.

---

## v1.4.5 — 2026-06-08

**Módulos afectados:** Core (seguridad)

### Security

- **[IMPL-009]** Removed publicly accessible diagnostic files from `public/`:
  `test_proc_open.php` (ejecutaba `proc_open('ls')` sin autenticación),
  `test.php`, `info.php`. Identified by AUDIT-006 H-003.

---

## v1.4.4 — 2026-06-08

**Módulos afectados:** Core (gobernanza)

### Documentation

- `docs/plan/PLAN-IMPL-008-Versioning-and-Documentation-Reconciliation.md` —
  PLAN-IMPL-008 persisted. Plan de reconciliación de versionado y documentación:
  estado EJECUTADO, resultado EXITOSO.

---

## v1.4.3 — 2026-06-08

**Módulos afectados:** Core (gobernanza)

### Documentation

- `docs/audits/AUDIT-005-Versioning-and-Changelog-Compliance.md` — AUDIT-005
  Versioning and Changelog Compliance persisted. Auditoría de cumplimiento de
  versionado y changelogs: estado CERRADA, riesgo residual BAJO.

---

## v1.4.2 — 2026-06-08

**Módulos afectados:** Core (gobernanza)

### Added (Documentation)

- `docs/adr/ADR-007-Strategic-Decisions-Registry.md` — Registro Oficial de
  Decisiones Estratégicas de Dirección General. Establece `docs/dg/` como
  ubicación de documentos DG-XXX (aprobaciones, repriorización, gobierno).
- `docs/dg/` — Directorio creado y reservado para futuras decisiones DG-013 a DG-018.

---

## v1.4.1 — 2026-06-08

**Módulos afectados:** Inventario → v2.4.1, v2.4.2

### Fixed

- **[IMPL-004]** `bienes.precio` migrado de `FLOAT` a `DECIMAL(12,2)` para eliminar
  errores de redondeo en valores monetarios. Ver `docs/impl/IMPL-004-Migración de FLOAT a DECIMAL(12,2) en bienes.precio.md`.

### Added

- **[IMPL-007]** Carga inicial del catálogo institucional de bienes: 1,420 activos
  importados en producción. Ver `docs/impl/IMPL-007-Initial Inventory Data Load.md`.

### Changed

- **[IMPL-008]** Reconciliación de versionado y documentación: entradas faltantes
  añadidas a changelogs, tags Git creados para versiones sin tag, ADR-004
  actualizado para reflejar la implementación real. Ver `docs/impl/IMPL-008-Versioning and Documentation Reconciliation.md`.

### Added (Documentation)

- `docs/impl/IMPL-GIT-001.md` — recuperación del repositorio Git tras ~11 meses
  sin control de versiones. Proyecto migrado a GitHub.
- `docs/adr/ADR-005-Documentation-and-Repository-Governance.md` — gobernanza
  de documentación y sincronización obligatoria con repositorio.
- `docs/adr/ADR-006-Agent-Responsibilities-and-Delivery-Contracts.md` — contratos
  de entrega y responsabilidades entre agentes del sistema de desarrollo.
- `docs/architecture/BASELINE-001.md` — auditoría del estado del proyecto al
  2026-06-08 (baseline de referencia).
- `docs/pmp/PMP-001.md` — plan maestro del proyecto.
- `docs/roadmap/ROADMAP-001.md` — hoja de ruta estratégica.
- `docs/audits/EVIDENCE-AUDIT-005.md` — evidencia recopilada para AUDIT-005
  Versioning and Changelog Compliance Audit.

---

## v1.4.0 — 2026-06-08

**Módulos afectados:** Core, User → v2.1.1

### Added

- **[AUDIT-001]** Sistema de Changelog Modal: cada módulo muestra su versión como
  enlace clicable en el footer. Al hacer clic se abre un modal con el historial
  completo del módulo, parseado desde `docs/changelog/<modulo>.md`.
  Implementado como Blade component `<x-changelog-modal module="X" />`.
  Módulos integrados: Inventario, User, BhagamApps (ppal).
- **[IMPL-007]** Carga inicial del catálogo de bienes: 1,420 activos importados
  en producción. (Registrado en v1.4.1 — ver nota abajo.)

### Fixed

- **[IMPL-003]** `permission_role`: eliminados 76 registros duplicados causados por
  doble ejecución del seeder `Permission_RoleSeeder`.
  Tabla reducida de 156 a 80 registros.

### Security

- **[IMPL-003]** Constraint `UNIQUE(role_id, permission_id)` aplicado en `permission_role`.
  Previene duplicados por re-ejecución de seeders o inserciones directas.

### Added (Documentation)

- `docs/audits/AUDIT-003-PermissionRole-Duplicates.md` — auditoría completa previa.
- `docs/impl/IMPL-003-PermissionRole-Cleanup.md` — proceso detallado.
- `docs/impl/backups/permission_role_before_cleanup.sql` — respaldo de los 156 registros originales.
- `docs/audits/AUDIT-004-API-Authentication.md` — auditoría de autenticación API aprobada y persistida.

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
