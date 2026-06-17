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

## [v1.30.0] — 2026-06-16

### Added (DDOM-GESTION-006 — Modelo Operativo Institucional)

- **DDOM-GESTION-006:** Incorporado `docs/ddom/DDOM-GESTION-006.md` — Modelo Operativo
  Institucional. Transforma arquitectura conceptual en modelo funcional implementable.
  Define Hallazgos→Objetivos→Metas→Indicadores→Actividades→Tareas→Seguimientos→Resultados.
  Establece Actividades (RN-ACT-001/002), Tareas, Responsables, Evidencias, fórmulas de
  Avance por nivel, estados, Vista Árbol, Vista Gantt y Dashboard Ejecutivo.
  Depende de DDOM-GESTION-001 a 005A. Base para Diseño CORE, UI y ROADMAP-GESTION-001.
  Estado: APROBADO — VIGENTE — OBLIGATORIO PARA TODO APPSisGOE.

---

## [v1.29.0] — 2026-06-16

### Added (DDOM-GESTION-005A — Matriz de Cobertura Institucional)

- **DDOM-GESTION-005A:** Incorporado `docs/ddom/DDOM-GESTION-005A.md` — Matriz de Cobertura
  Institucional. Evalúa cobertura por niveles (0-3) sobre las cuatro gestiones Guía 34.
  Resultado consolidado: Directiva 8%, Académica 15%, Administrativa y Financiera 35%,
  Comunidad 45% — Global 26%. Identifica brecha arquitectura (90%) vs cobertura funcional (26%).
  Define prioridades Roadmap: Planeación, Calidad, Deserción, Académico.
  Estado: APROBADO — VIGENTE — OBLIGATORIO PARA TODO APPSisGOE.

---

## [v1.28.0] — 2026-06-16

### Added (DDOM-GESTION-005 — Matriz de Integración de Módulos y Componentes Institucionales)

- **DDOM-GESTION-005:** Incorporado `docs/ddom/DDOM-GESTION-005.md` — Matriz de Integración
  de Módulos y Componentes Institucionales. Formaliza relación Módulo→Métrica→Componente→
  Indicador→Meta→Objetivo→Proceso→Gestión. Mapea componentes GAF-02-03/04/05/06/07 e
  impacto de Inventario y Comunidad Educativa. Establece Inventario y Comunidad Educativa
  como primeros proveedores oficiales de métricas del CORE. Depende de DDOM-001/002/003/004.
  Estado: APROBADO — VIGENTE — OBLIGATORIO PARA TODO APPSisGOE.

---

## [v1.27.0] — 2026-06-16

### Added (DDOM-GESTION-004 — Modelo de Fuentes de Datos y Métricas Operativas)

- **DDOM-GESTION-004:** Incorporado `docs/ddom/DDOM-GESTION-004.md` — Modelo de Fuentes de
  Datos y Métricas Operativas. Define el flujo Módulo → Métricas Operativas → Componente →
  Indicadores. Establece Fuentes Institucionales Iniciales (Inventario, Comunidad Educativa,
  Académico, Convivencia, Planeación), relación N:M Métrica↔Indicador, y Reglas de Negocio
  RN-DAT-001 a RN-DAT-005. Depende de DDOM-001/002/003. Base para Dashboard Institucional.
  Estado: APROBADO — VIGENTE — OBLIGATORIO PARA TODO APPSisGOE.

---

## [v1.26.0] — 2026-06-16

### Added (DDOM-GESTION-003 — Modelo de Objetivos, Metas, Indicadores y Seguimientos)

- **DDOM-GESTION-003:** Incorporado `docs/ddom/DDOM-GESTION-003.md` — Modelo de Objetivos,
  Metas, Indicadores y Seguimientos. Define el ciclo Autoevaluación → Hallazgos → Objetivos →
  Metas → Indicadores → Seguimientos. Establece Reglas de Negocio RN-OBJ, RN-META, RN-IND,
  tipos de seguimiento (Manual/Automático), estados y frecuencias. Depende de DDOM-GESTION-001
  y DDOM-GESTION-002. Base para DDOM-GESTION-004/005/006.
  Estado: APROBADO — VIGENTE — OBLIGATORIO PARA TODO APPSisGOE.

---

## [v1.25.0] — 2026-06-16

### Added (DDOM-GESTION-002 — Modelo Institucional Oficial APPSisGOE)

- **DDOM-GESTION-002:** Incorporado `docs/ddom/DDOM-GESTION-002.md` — Modelo Institucional
  Oficial APPSisGOE. Formaliza la arquitectura conceptual de integración de módulos bajo la
  estructura CORE (Gestión → Proceso → Componentes). Define responsabilidades del CORE y módulos,
  regla de integración por Métricas Operativas, Metas, Indicadores y Seguimientos.
  Estado: APROBADO — VIGENTE — OBLIGATORIO PARA TODO APPSisGOE. Complementa DDOM-GESTION-001.
  Base para DDOM-GESTION-003/004/005/006.

---

## [v1.24.0] — 2026-06-16

### Added (DDOM-GESTION-001 — Modelo Institucional Guía 34)

- **DDOM-GESTION-001:** Incorporado `docs/ddom/DDOM-GESTION-001.md` — Modelo Institucional
  de Gestión, Procesos y Componentes basado en la Guía 34 del MEN. Define las cuatro gestiones
  CORE obligatorias para toda la plataforma (GD, GA, GAF, GC) con sus procesos y componentes.
  Estado: APROBADO — VIGENTE — OBLIGATORIO PARA TODO APPSisGOE.

---

## [v1.23.0] — 2026-06-14

### Added (IMPL-CORE-002 Sprint 0 — Compatibilidad Arquitectónica APPSisGOE)

- **CORE-000.1 — Spatie Permission instalado:** `spatie/laravel-permission ^6.25` agregado
  a `composer.json`. Configuración publicada en `config/permission.php` con `teams=false`
  (ADR-003 §4). Migración publicada en `database/migrations/2026_06_14_185235_create_permission_tables.php`
  con guarda `Schema::hasTable()` para coexistencia segura con tablas legacy durante
  transición CORE-002.

- **CORE-000.2 — RolInstitucional enum creado:** `app/Auth/RolInstitucional.php` con
  7 roles fijos de la IEE colombiana (Administrador, Rector, Coordinador, Auxiliar,
  Docente, Estudiante, Invitado). Resuelve BLOCKER-002 del IMPLEMENTATION-READINESS-REPORT.
  `SincronizarRolesYPermisosCoreAction` ahora puede cargar sin error.

- **CORE-000.3 — Dependencias cross-módulo eliminadas:** `Modules/User/Entities/User.php`
  saneado — eliminadas 2 importaciones ilegales (`Modules\Inventario\Entities\Bien`,
  `Modules\Inventario\Entities\Dependencia`) y 3 métodos cross-módulo (`bienes()`,
  `bienesAsignados()`, `dependencias()`). Cumple ARCH-001 PRINC-03 Desacoplamiento
  Horizontal.

- **CORE-000.4 — roles.app_id analizado:** FK circular `roles → apps` ya nullable
  (migración `2026_06_08`). Todos los 7 roles tienen `app_id=1`. Auto-resuelve en
  CORE-002 cuando Spatie reemplaza tabla `roles`.

- **Documentación:** `docs/implementation/IMPLEMENTATION-READINESS-REPORT.md` (324 líneas),
  `docs/implementation/CORE-FOUNDATION-IMPLEMENTATION-PLAN.md` (764 líneas),
  `docs/implementation/IMPLEMENTATION-BACKLOG-CORE-FOUNDATION.md` (801 líneas).
  Reporte de Sprint 0: `docs/implementation/IMPL-CORE-002-REPORT.md`.

### Changed

- `Modules/User/Entities/User.php` — eliminadas relaciones cross-módulo ilegales
  (`bienes`, `bienesAsignados`, `dependencias`). Relación `apps()` mantenida (intra-CORE).

### Fixed

- Migración Spatie ahora es idempotente vía `Schema::hasTable()` — previene conflicto
  con tabla legacy `permissions` durante `migrate:fresh` en suite de pruebas.

---

## [v1.22.12] — 2026-06-13

### Added (AUDIT-BACKUP-005 — Certificación de Integridad Total del Snapshot Institucional)

- Auditoría de solo lectura sobre el snapshot `IEE-2026-06-13.zip`. 10 controles
  ejecutados (SNAP-AUDIT-001 a 010). Resultado: 23/33 tablas exportadas, cobertura
  de datos institucionales del 99.6%, integridad referencial perfecta (0 huérfanos en
  11 relaciones), todos los campos críticos de seguridad presentes. Hallazgos:
  2 permisos post-backup (ids 85/86) ausentes del ZIP (procedimiento de remediación
  documentado), `activity_logs` no incluida en export (mejora pendiente).
  Dictamen: **APTO PARA RESTAURACIÓN — 97% de confianza.**
  Documento: `docs/audits/AUDIT-BACKUP-005-Certificacion-De-Integridad-Total-Del-Snapshot-Institucional.md`.
  IEE v1.23.12 / BhagamApps v1.22.12.

---

## [v1.22.11] — 2026-06-13

### Added (IMPL-ACTIVITYLOG-001 — Módulo Global de Auditoría Institucional)

- Nuevo módulo `Modules/ActivityLog` como infraestructura de auditoría transversal.
  Sin dependencias externas (sin spatie/activitylog). Tabla `activity_logs` con
  índices compuestos para 100k+ registros. Servicio estático `ActivityLogger::log()`
  y helper global `activity_log()` con captura de IP, user-agent y usuario autenticado.
  Integraciones completadas: Users (crear, editar, eliminar, bloquear, desbloquear,
  asignar-rol), Inventario (crear bien, editar campo, eliminar bien, aprobar cambio,
  rechazar cambio), Backups (generar, descargar, restaurar, importar), RBAC (asignar
  permisos a roles). Pantalla administrativa con filtros (usuario, módulo, acción,
  fecha) + paginación + dashboard rápido (acciones hoy, semana, últimos eventos).
  Permiso `ver-activity-log` (id=86) solo para AdminPrincipal. IEE v1.23.11 /
  BhagamApps v1.22.11.

---

## [v1.22.10] — 2026-06-13

### Added (IMPL-INFRA-BACKUP-007 — Importación y Restauración de Snapshot Externo desde CAB)

- Nueva sección "Importar Snapshot" en Administración del Sistema → Backups, accesible
  exclusivamente al Administrador Principal (`es_principal = true` + permiso
  `importar-snapshot-backup`). Permite subir un ZIP externo (descargado desde Google Drive
  u otro origen), validar su estructura, revisar una vista previa completa del contenido
  y ejecutar la restauración con doble confirmación (`RESTAURAR`). Cierra el ciclo de
  Disaster Recovery: instalación nueva + GitHub + migraciones + ZIP de Drive = plataforma
  restaurada sin acceso SSH al servidor original.
  Componente `ImportarSnapshot` con `WithFileUploads`, máquina de 4 estados
  (subir → vista-previa → confirmar → resultado). JSONL en `restore.log` con
  `origen: CAB-WEB-IMPORT`. Gate `importar-snapshot-backup` exige `hasPermission() +
  isAdminPrincipal()`. IEE v1.23.10 / BhagamApps v1.22.10.

---

## [v1.22.9] — 2026-06-13

### Added (IMPL-INFRA-BACKUP-006 — Restauración de Snapshot Institucional desde CAB)

- Nueva sección "Restaurar" en Administración del Sistema → Backups, accesible exclusivamente
  al Administrador Principal (`es_principal = true` + permiso `restaurar-backups`).
  Flujo de 4 estados: listado de respaldos → vista previa del snapshot → doble confirmación
  (debe escribir `RESTAURAR`) → resultado con salida completa del proceso.
  Reutiliza `backup:restore-from-zip` via `Artisan::call()` (RESTORE-WEB-006).
  Auditoría JSONL en `storage/logs/restore.log` con usuario, snapshot y resultado (RESTORE-WEB-007).
  Gate `restaurar-backups` exige `hasPermission()` + `isAdminPrincipal()` (RESTORE-WEB-005/009).
  IEE v1.23.9 / BhagamApps v1.22.9.

---

## [v1.22.8] — 2026-06-13

### Added (AUDIT-BACKUP-004 — Backup Generation & CAB Operational Validation)

- Auditoría operativa completa del flujo CAB post HOTFIX-BACKUP-002. 10 controles (CAB-001
  a CAB-010) aprobados. Confirma que el botón "Generar Respaldo" genera ZIP local sin
  dependencia de Drive, la descarga funciona sin SSH, Drive queda desacoplado, y la
  plataforma queda lista para IMPL-INFRA-BACKUP-006 (restauración desde UI web).
  IEE v1.23.8 / BhagamApps v1.22.8.

---

## [v1.22.7] — 2026-06-13

### Fixed (HOTFIX-BACKUP-002 — Drive desacoplado de generación local)

- **Causa raíz:** `DriveService::subirConRclone()` llamaba `Process::env()->run()` que
  requiere `proc_open`. Con `QUEUE_CONNECTION=sync`, `GenerarBackupJob` corre en el
  contexto web donde `proc_open` está deshabilitado → excepción bloqueaba la generación
  del backup aunque el ZIP local ya estaba completo.
- **`DriveService`** reescrito con detección automática de método:
  `proc_open` disponible → rclone; `curl_exec + openssl_sign` disponibles → API nativa
  Google Drive v3 con JWT RS256 (sin proc_open, funciona en web PHP);
  ninguno disponible → retorno silencioso sin excepción.
- **`subirConApiNativa()`**: JWT RS256 firmado con `openssl_sign()` → POST OAuth2 →
  upload multipart vía `curl_exec`. Sin dependencia de rclone ni proc_open.
- **`subirConRclone()`**: ambos `Process::run()` envueltos en try-catch adicional.
- **`BackupExportSeeders::uploadToDrive()`**: try-catch como cinturón de seguridad.
  Drive falla → `$this->warn()` + comment → `handle()` retorna SUCCESS.
  `GenerarBackupJob` no lanza. UI muestra "Respaldo generado exitosamente."
- **`BackupSyncDrive`**: maneja nuevo estado `sin-soporte`.
- PHP lint limpio en los 3 archivos. Dry-run verifica sin errores. IEE v1.23.7.

---

## [v1.22.6] — 2026-06-13

### Added (IMPL-INFRA-BACKUP-005 — Google Drive Integration & Monitoring)

- `DriveService` centraliza toda la lógica Drive: upload, verificación post-subida,
  log de historial JSONL, alertas. `backup:export-seeders` ahora delega a `DriveService`.
- `backup:sync-drive [--file=] [--dry-run]`: sincronización manual del último ZIP
  (o de un ZIP específico) a Google Drive. Sin regenerar backup.
- `SincronizarDriveJob` + método `sincronizarDrive()` en `BackupDashboard` para
  dispatch desde la UI CAB.
- Dashboard CAB ampliado: tarjeta Estado Drive (DRIVE-003), última sync / carpeta /
  conteo backups (DRIVE-004), botón "Sincronizar ahora" (DRIVE-005), historial de
  5 últimas sincronizaciones (DRIVE-007), alerta amarilla/roja si Drive pendiente
  (DRIVE-008), fila Estado General Local+Drive (DRIVE-010).
- Permisos `ver-backup-drive` (id=82) y `sincronizar-backup-drive` (id=83) creados,
  asignados exclusivamente a Administrador (role_id=1). PHP lint limpio, 0 regresiones.
  IEE v1.23.6. DRIVE activo en producción desde que se configure BACKUP_GDRIVE_SA_JSON.

---

## [v1.22.5] — 2026-06-13

### Added (IMPL-INFRA-BACKUP-004 — Restauración Automatizada desde Snapshot)

- `backup:restore-from-zip --file=backups/IEE-YYYY-MM-DD.zip` implementado.
  Valida el ZIP (RESTORE-002), lee metadata (RESTORE-003), extrae a temp (RESTORE-004),
  sincroniza 12 CSV a `Seeders/data/` (RESTORE-005), ejecuta `InstitutionalRestoreSeeder`
  en `DB::transaction()` con rollback automático (RESTORE-006+007), valida conteos
  post-restauración vs metadata.json (RESTORE-008), registra auditoría en
  `storage/logs/restore.log` (RESTORE-009). Opción `--dry-run` para simular sin tocar
  la BD. Opción `--force` para uso en scripts. GAP-DR-001 cerrado. IEE v1.23.5.

---

## [v1.22.4] — 2026-06-13

### Fixed (IMPL-INFRA-BACKUP-003B — Disaster Recovery Hardening)

- Cerrados 4 gaps bloqueantes de AUDIT-BACKUP-002. GAP-DR-002: `BienesResponsablesSeeder`
  eliminó dummy y ahora lee `data/bienes_responsables.csv`. GAP-DR-003:
  `MantenimientosProgramadosSeeder` eliminó Faker y lee CSV real. GAP-DR-005:
  `AppSeeder` incluye `admin-sistema` — CAB operativo post-restore. GAP-DR-009:
  `InstitutionalRestoreSeeder` creado como orquestador oficial de 5 etapas.
  HARDENING-001: `data/*.csv` sincronizados con producción (permisos: 38→80,
  permission_role: 104→167, app_role: 10→11, users: 116→117). PHP lint limpio.
  Sin regresiones. IEE v1.23.4. Prerrequisitos de IMPL-INFRA-BACKUP-004 cumplidos.

---

## [v1.22.3] — 2026-06-13

### Security / Audit (AUDIT-BACKUP-002 — Disaster Recovery Certification)

- Certificación DR del Snapshot Institucional `IEE-2026-06-13.zip` completada.
  Resultado: **B — CERTIFICADO CON AJUSTES MENORES**. ZIP íntegro: 24 archivos,
  3.447 registros, 23/23 tablas, 0 FK huérfanas. Cobertura funcional post-DR: ~85%.
  9 gaps identificados (2 CRÍTICO, 4 ALTO, 2 MEDIO, 1 BAJO). P1: crear
  `backup:restore-from-zip`, corregir `BienesResponsablesSeeder` y
  `MantenimientosProgramadosSeeder`. IEE v1.23.3. BhagamApps v1.22.3.
  Informe: `docs/audits/AUDIT-BACKUP-002-Disaster-Recovery-Certification.md`.
  HOTFIX-BACKUP-001 también incluido: `exec()` reemplazado por `Process facade`
  en `BackupExportSeeders::uploadToDrive()`.

---

## [v1.22.2] — 2026-06-13

### Fixed (IMPL-INFRA-BACKUP-003A — Backup Restore Readiness Remediation)

- Resueltos los 6 hallazgos bloqueantes de AUDIT-BACKUP-001 (2 CRÍTICO + 4 ALTO).
  `HistorialModificacionesBienesSeeder` reescrito con columnas correctas del esquema real.
  Nuevo `AppRoleSeeder` en User module restaura las 10 asignaciones app→rol.
  `CategoriasSeeder` preserva IDs explícitamente. `BienesSeeder` restaura `origen_id`.
  Nuevo `OrigenesSeeder` con `updateOrInsert`. `UserSeeder` restaura campos de seguridad.
  IEE v1.23.2. Prerrequisito de IMPL-INFRA-BACKUP-004 cumplido.

---

## [v1.22.1] — 2026-06-13

### Added (AUDIT-BACKUP-001 — Backup Restore Readiness Assessment)

- Auditoría de restaurabilidad del sistema de respaldo institucional completada.
  14 hallazgos identificados (2 CRÍTICO, 4 ALTO, 4 MEDIO, 4 BAJO).
  Clasificación: C — REQUIERE CORRECCIONES ANTES DE IMPLEMENTAR RESTAURACIÓN.
  Informe completo en `docs/audits/AUDIT-BACKUP-001-Backup-Restore-Readiness-Assessment.md`.

---

## [v1.22.0] — 2026-06-13

### Added (IMPL-INFRA-BACKUP-002 — Centro de Administración de Backups)

- Nuevo módulo `AdminSistema` v1.0.0: Centro de Administración de Backups accesible
  desde la interfaz web sin necesidad de SSH. Dashboard con KPIs de estado, listado
  de respaldos, ficha técnica, descarga ZIP y generación manual con Job.
  Visible únicamente para el Administrador Principal. IEE v1.23.0.

---

## [v1.21.0] — 2026-06-13

### Added (IMPL-INFRA-BACKUP-001 — Sistema de Respaldo Institucional)

- Comando `php artisan backup:export-seeders`: exporta 23 tablas a CSV,
  genera metadata.json con versiones y conteos, comprime a ZIP, aplica retención
  30 daily / 12 monthly, sube a Google Drive via rclone (SA o remote pre-configurado).
  Schedule: diariamente 02:00. BhagamApps v1.21.0 | IEE v1.22.0.

---

## [v1.20.1] — 2026-06-12

### Fixed (IMPL-CORE-MENU-001 completion — RBAC Rector + Biblioteca disabled)

- RBAC Rectoría corregido: RoleSeeder excluye categorías `roles` y `permisos` del rol Rector
  (V-008, V-009). Biblioteca deshabilitada en AppSeeder (`habilitada: false`).
  User v2.5.1 | Apps v1.5.2 | IEE v1.21.1.

---

## [v1.20.0] — 2026-06-12

### Added (IMPL-CORE-MENU-001 — Reorganización Menú y RBAC Visual)

- Menú lateral reorganizado: contenedor "Mis Módulos", Gestión de Acceso con RBAC visual
  individual para Roles/Permisos, Inventario en orden alfabético, módulos placeholder eliminados.
  BhagamApps v1.20.0 | IEE v1.21.0.

---

## [v1.19.0] — 2026-06-12

### Added (IMPL-INV-012 — Catálogo de Orígenes)

- Catálogo institucional de 11 orígenes + migración automática de 1,420 bienes.
  FK `bienes.origen_id` oficial; `bienes.origen` legacy conservado.
  Inventario v2.15.0 | IEE v1.20.0.

---

## [v1.18.0] — 2026-06-12

### Added (IMPL-INV-011 — Búsqueda Facetada de Bienes)

- Filtros facetados dinámicos en el listado de bienes: 6 facetas con conteos en tiempo real,
  calculadas via GROUP BY sin N+1. Inventario v2.14.0 | IEE v1.19.0.

---

## [v1.17.1] — 2026-06-12

### Fixed (HOTFIX-INV-010 — Error 419 Bienes)

- 419 en búsqueda/filtros de Inventario resuelto: hijos Livewire móvil eliminados,
  `$listaNombresBienes` sacado del snapshot. Inventario v2.13.1 | IEE v1.18.1.

---

## [v1.17.0] — 2026-06-12

### Added (IMPL-INV-009 — Buscador Inteligente de Bienes)

- Búsqueda global reactiva en el módulo Inventario: 11 campos simultáneos, debounce 300ms,
  sin botón de búsqueda. Filtros nuevos de Origen y Custodio. `wire:key` en loops.
  Inventario v2.13.0 | IEE v1.18.0.

---

## [v1.16.1] — 2026-06-11

### Fixed (HOTFIX-USERS-003)

- Error 500 en `/users/users` introducido por IMPL-USERS-002.
  `mount(): void` con `return redirect()` lanzaba FatalError. Corregido: eliminado `: void`.
  User v2.4.1 | IEE v1.16.1 | BhagamApps v1.16.1.

---

## [v1.16.0] — 2026-06-11

### Added (IMPL-USERS-002)

- Módulo User: búsqueda reactiva, filtros por rol/estado y ordenamiento por columnas en gestión de usuarios.
  User v2.4.0 | IEE v1.16.0 | BhagamApps v1.16.0.

---

## [v1.15.3] — 2026-06-11

### Fixed (HOTFIX-DEP-001)

- Error 500 en Catálogo Dependencias de Inventario.
  `DependenciasIndex::mount()` consultaba columna `name` en `users`; corregido a `nombres`/`apellidos`.
  Inventario v2.11.3 | IEE v1.15.3 | BhagamApps v1.15.3.

---

## [v1.15.2] — 2026-06-11

### Fixed (HOTFIX-INV-DASH-002)

- Error 500 en Dashboard Ejecutivo de Inventario.
  MySQL ONLY_FULL_GROUP_BY rechazaba `GROUP BY CASE WHEN origen...` en DASH-005.
  Corregido: `GROUP BY origen` en SQL + normalización PHP.
  Inventario v2.11.2 | IEE v1.15.2 | BhagamApps v1.15.2.

---

## [v1.15.1] — 2026-06-11

### Fixed (HOTFIX-INV-DASH-001)

- Menú sidebar, Apps y tabla `apps` en BD apuntan ahora a `/inventario` (dashboard ejecutivo).
  Inventario v2.11.1 | IEE v1.15.1 | BhagamApps v1.15.1.

---

## [v1.15.0] — 2026-06-11

### Added (IMPL-INV-DASH-001)

- Dashboard Ejecutivo de Inventario IEE.
  Página principal de `/inventario` convertida en tablero ejecutivo institucional.
  KPIs, gráficas Chart.js (categorías, dependencias, estado, origen), alertas,
  accesos rápidos e indicadores de calidad de datos.
  Livewire 3 + Alpine.js. Consultas 100% agregadas, sin N+1. Responsive AdminLTE.
  Inventario v2.11.0 | IEE v1.15.0 | BhagamApps v1.15.0.

---

## [v1.14.1] — 2026-06-11

### Fixed

- **[HOTFIX-RBAC-001]** Recuperación de acceso RBAC tras restauración de datos.
  La tabla `app_role` quedó vacía por CASCADE al ejecutar `cleanup_legacy_apps` y los seeders
  no la repoblaron. Todos los roles perdieron acceso a las apps, causando 403 en todas las rutas.
  Migración `2026_06_11_200000_assign_app_roles_rbac_recovery` restaura la matriz completa
  (Rector+Admin→user/inventario/apps; Coordinador→user/inventario; Auxiliar+Docente→inventario).
  Apps v1.5.1 | IEE v1.14.1 | BhagamApps v1.14.1.

---

## [v1.14.0] — 2026-06-11

### Added

- **[IMPL-USERS-001]** Administración institucional de contraseñas y estados de usuario.
  `GestionPasswordUser` y `GestionEstadoUser` Livewire integrados en `UserIndex`.
  Fortify extendido con `authenticateUsing` para bloqueo de cuentas.
  Middleware `CheckForzarCambioPassword` en grupo web para forzado de cambio.
  Tabla `auditoria_passwords` con 4 tipos de acción. 4 nuevos permisos RBAC.
  8 tests V-001→V-008 todos PASS. 15 tests previos sin regresiones.
  IEE v1.14.0 | BhagamApps v1.14.0 | User v2.3.0.

---

## [v1.13.1] — 2026-06-11

### Fixed

- **[IMPL-CORE-CLEANUP-001 Fase 2]** Suite de pruebas de User/Auth migrada al modelo activo.
  12 archivos de prueba actualizados de `App\Models\User` → `Modules\User\Entities\User`.
  Factories creadas: `Modules\User\Database\Factories\UserFactory` y `RoleFactory`.
  `ProfileInformationTest` adaptado a campos reales (`nombres`, `apellidos`).
  `RegistrationTest` adaptado a campos IEE con roles 5/6 para validación `in:5,6`.
  10 tests de User/Auth/Fortify/Jetstream pasan; 7 correctamente omitidos (features off).
  IEE v1.13.1 | BhagamApps v1.13.1 | User v2.2.2.

---

## [v1.13.0] — 2026-06-11

### Fixed

- **[IMPL-CORE-CLEANUP-001]** Remediación crítica de Fortify Actions y modelo User.
  Corregidos 4 Actions (`UpdateUserPassword`, `ResetUserPassword`,
  `UpdateUserProfileInformation`, `DeleteUser`) para usar el modelo activo
  `Modules\User\Entities\User`. Binding de `LoginResponse` en `FortifyServiceProvider`
  corregido. `app/Models/User.php` neutralizado (eliminadas referencias a Spatie y
  RolSistema inexistentes). Las funciones de gestión de contraseña, perfil y cuenta
  — antes inoperativas con HTTP 500 — son ahora funcionales.
  IEE v1.13.0 | BhagamApps v1.13.0.

---

## [v1.12.1] — 2026-06-10

### Fixed

- **[IMPL-INFRA-001]** Migración de alias público `/Modular` → `/iee`.
  Creado symlink `/iee` en `public_html/public/` apuntando al mismo destino que `/Modular`.
  Actualizado `APP_URL`, `ASSET_URL` y `SESSION_PATH` a `/iee` en producción.
  `/Modular` se mantiene operativo en paralelo durante transición.
  IEE v1.12.1 | BhagamApps v1.12.1.

---

## [v1.12.0] — 2026-06-10

### Changed

- **[IMPL-CORE-BRANDING-001]** Migración institucional de identidad visible: BhagamApps Modular → IEE
  (Institución Educativa Entrerríos — Sistema de Inventario Institucional).
  APP_NAME, APP_URL, ASSET_URL, SESSION_PATH, adminlte logo/título, footer y welcome actualizados.
  Nuevo key `IEE` en `config/versiones.php`. Arquitectura interna sin modificación.
  IEE v1.12.0 | BhagamApps v1.12.0.

---

## [v1.11.5] — 2026-06-10

### Added

- **[IMPL-INV-QA-001]** Inventario v2.10.5: primera suite formal de tests automatizados.
  50 tests / 73 assertions en 5 archivos Feature. Cubre autorización (17 tests),
  flujos críticos de negocio (Bienes, Notificaciones, HistorialUbicaciones, Responsables)
  y regresiones documentadas (GAP-001, GAP-002, IMPL-INV-005, IMPL-INV-008, IMPL-INV-NOTIF-001B).
  Inventario v2.10.5 | BhagamApps v1.11.5.

---

## [v1.11.4] — 2026-06-10

### Fixed

- **[IMPL-INV-NOTIF-001B]** Inventario v2.10.4: consistencia y persistencia del sistema de
  notificaciones. Corrige eliminaciones indebidas de evidencia histórica en `aprobarCambio` y
  `rechazarCambio` del dropdown. Activa canal `database` en `NotificacionHmb`. Contador
  `NotificacionesIcono` ahora reacciona a eventos sin wire:poll.
  Inventario v2.10.4 | BhagamApps v1.11.4.

---

## [v1.11.3] — 2026-06-10

### Added

- **[IMPL-INV-NOTIF-001A]** Inventario: activación de notificaciones in-app (DF-001/002/004).
  Dropdown de aprobaciones HMB y badge contador de pendientes activados en navbar para
  Administrador y Rector. Registro Livewire huérfano eliminado de InventarioServiceProvider.
  Inventario v2.10.3 | BhagamApps v1.11.3.

---

## [v1.11.2] — 2026-06-10

### Fixed

- **[IMPL-INV-008]** Inventario: eliminación de `wire:poll` innecesario en BienesIndex, HEB y HMB.
  Corrige error 419 PAGE EXPIRED en tabs en segundo plano (AUDIT-LIVEWIRE-419-001). Eliminado
  listener muerto `bienCreado` de BienesIndex. Sin regresiones funcionales.
  Inventario v2.10.2 | BhagamApps v1.11.2.

---

## [v1.11.1] — 2026-06-10

### Fixed

- **[IMPL-INV-007]** Inventario: limpieza de deuda técnica DT-001/DT-003/DT-005 post AUDIT-INV-005.
  Eliminados 4 gates huérfanos de `AuthServiceProvider`; HMB "Historial Modificaciones" añadido
  al sidebar; migración duplicada eliminada de `database/migrations/`. Sin regresiones.
  Inventario v2.10.1 | BhagamApps v1.11.1.

---

## [v1.11.0] — 2026-06-10

### Added

- **[IMPL-INV-006]** Inventario: Mantenimientos Programados de Bienes — gestión completa CRUD.
  Entidad `MantenimientoProgramado` completada, 4 permisos RBAC, componente Livewire
  `MantenimientosProgramadosIndex`, ruta `/inventario/mantenimientos/programados`,
  entrada sidebar "Mantenimientos". Inventario v2.10.0 | BhagamApps v1.11.0.

---

## [v1.10.0] — 2026-06-10

### Added

- **[IMPL-INV-005]** Inventario: Historial de Ubicaciones de Bienes — trazabilidad física completa.
  Tabla `historial_ubicaciones_bienes`, entidad `HistorialUbicacionBien`, relaciones `ubicacionActual()`
  e `historialUbicaciones()` en `Bien`, 2 permisos RBAC (`ver-historial-ubicaciones-bienes`,
  `cambiar-ubicacion-bienes`), 2 componentes Livewire (`HistorialUbicacionesBien`,
  `CambiarUbicacionBien`), ruta `/inventario/ubicaciones/historial`, columna "Ubicación Actual"
  disponible en BienesIndex (oculta por defecto). Inventario v2.9.0 | BhagamApps v1.10.0.

---

## [v1.9.4] — 2026-06-10

### Fixed

- **[IMPL-INV-004 suplemento]** Corrección de sort SQL bug en `BienesIndex`: columnas virtuales
  (`user_id`, `detalle`) en `$ordenBase` podían generar `orderBy` sobre columnas inexistentes en
  `bienes`. Guard de allowlist añadido. Eliminados Gates huérfanos `aprobar-cambios-bienes` /
  `rechazar-cambios-bienes` de `AuthServiceProvider` (permiso base nunca seeded). Limpieza de código
  muerto en `EditarCampoBien` y vista `bienes-index`. Inventario v2.8.2.

---

## [v1.9.3] — 2026-06-10

### Fixed

- **[IMPL-INV-004]** Remediación técnica módulo Inventario: eliminación de componente Livewire roto
  con permisos inexistentes, corrección de query activa con columna ausente en BD, limpieza de
  columna virtual inexistente en UI de bienes. Inventario v2.8.1.

---

## [v1.9.2] — 2026-06-09

### Added

- **[IMPL-INV-002A]** Inventario: navegación completa — 7 catálogos + HEB integrados al sidebar.
  Gate HEB añadido a AuthServiceProvider. IMPL-INV-002 cerrado. Inventario v2.8.0 | BhagamApps v1.9.2.

---

## [v1.9.1] — 2026-06-09

### Fixed

- **[IMPL-INV-003A]** Inventario: entrada "Responsables" añadida al submenú lateral.
  Cierre formal de IMPL-INV-003. Inventario v2.7.1 | BhagamApps v1.9.1.

---

## [v1.9.0] — 2026-06-09

### Added

- **[IMPL-INV-003]** Inventario: Gestión completa de Responsables y Custodios de bienes.
  Nueva sección `/inventario/responsables`, asignación, transferencia y liberación de custodios,
  historial completo por bien, 4 nuevos permisos, 4 gates, columna Custodio en BienesIndex.
- Inventario: v2.7.0 | BhagamApps: v1.9.0

---

## [v1.8.0] — 2026-06-09

### Added

- **[IMPL-INV-002]** Inventario: CRUD administrativos completos para los 7 catálogos maestros del módulo.
  28 nuevos permisos en la plataforma, nueva tabla `origenes`, 7 rutas, 7 componentes Livewire.
- Inventario: v2.6.0 | BhagamApps: v1.8.0

---

## [v1.7.1] — 2026-06-09

### Fixed

- **[IMPL-INV-001]** Inventario: cuatro hallazgos críticos de AUDIT-INV-001 corregidos.
  Permiso HEB creado, tabla bienes_responsables creada, Coordinador habilitado para
  acceder al módulo Inventario, null check en HmbIndex corregido.
- Inventario: v2.5.0 | BhagamApps: v1.7.1

---

## [v1.7.0] — 2026-06-09

### Removed

- **[IMPL-APPS-006]** Catálogo legacy de aplicaciones depurado: 12 registros sin slug
  (IDs 1-12) eliminados. Catálogo oficial consolidado en 12 registros únicos con slug.
  Módulo Apps: APTO PARA BASELINE ESTABLE.
- Apps: v1.5.0 | BhagamApps: v1.7.0

---

## [v1.6.5] — 2026-06-09

### Fixed

- **[IMPL-APPS-005F]** Módulo Apps: middleware group `web` restaurado para todas las rutas.
  `RouteServiceProvider` activado en `module.json`; `loadRoutesFrom()` eliminado de
  `AppsServiceProvider`. Resuelve fallo de autenticación en `/apps/admin`.
- Apps: v1.4.3 | BhagamApps: v1.6.5

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
