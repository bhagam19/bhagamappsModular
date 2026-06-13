# IEE — Changelog de Producto

Registra cambios de identidad y configuración del producto institucional **IEE**
(Institución Educativa Entrerríos — Sistema de Inventario Institucional).

La plataforma técnica subyacente se documenta en [`docs/changelog/bhagamapps.md`](bhagamapps.md).

---

## v1.23.5 — 2026-06-13

### Added (IMPL-INFRA-BACKUP-004 — Restauración Automatizada desde Snapshot)

- IEE ahora tiene restauración automatizada completa desde un Snapshot Institucional ZIP.
  El comando `php artisan backup:restore-from-zip --file=backups/IEE-YYYY-MM-DD.zip`
  valida el respaldo, sincroniza los datos con los seeders, ejecuta la restauración
  en una transacción de base de datos con rollback automático, valida los conteos
  post-restauración y registra la operación en un log de auditoría. El DR completo
  (Disaster Recovery) ahora requiere: `php artisan migrate` seguido de
  `php artisan backup:restore-from-zip`. GAP-DR-001 cerrado. BhagamApps v1.22.5.

---

## v1.23.4 — 2026-06-13

### Fixed (IMPL-INFRA-BACKUP-003B — Disaster Recovery Hardening)

- Cerrados los 4 gaps bloqueantes de AUDIT-BACKUP-002. Las asignaciones de
  responsables de bienes (10 registros) y los mantenimientos programados
  (10 registros) ahora se restauran exactamente desde el snapshot ZIP,
  sin datos ficticios. El Centro de Administración de Backups permanece
  operativo después de un restore — `admin-sistema` ahora está incluido
  en el catálogo base de apps. La restauración institucional tiene un
  orquestador oficial con orden canónico de 5 etapas. Los datos de permisos
  y RBAC en el seeder están sincronizados con producción (80 permisos, 167
  asignaciones). Lista para IMPL-INFRA-BACKUP-004. BhagamApps v1.22.4.

---

## v1.23.3 — 2026-06-13

### Security / Audit (AUDIT-BACKUP-002 — Disaster Recovery Certification)

- Certificación DR formal del Snapshot Institucional completada contra respaldo real
  `IEE-2026-06-13.zip`. Dictamen: **B — CERTIFICADO CON AJUSTES MENORES**.
  El respaldo contiene el 100% de la información crítica (1.420 bienes, 135 dependencias,
  117 usuarios, RBAC completo) con integridad referencial perfecta (0 FK huérfanas).
  Cobertura funcional post-DR sin correcciones: ~85%. 9 gaps identificados con
  plan de remediación. Producción permanece intacta. BhagamApps v1.22.3.
  HOTFIX-BACKUP-001: `exec()` eliminado de `BackupExportSeeders` — reemplazado por
  `Process::env()->run()`. La generación de respaldos desde el CAB ya no falla.

---

## v1.23.2 — 2026-06-13

### Fixed (IMPL-INFRA-BACKUP-003A — Remediación de Restaurabilidad de Respaldos)

- El sistema de respaldo institucional ahora puede restaurarse de forma completa y
  automatizada. Seis correcciones críticas: el historial de modificaciones se restaura
  desde el CSV del respaldo (no datos ficticios). Las asignaciones de apps por rol
  (app_role) se restauran completamente, por lo que el dashboard vuelve a ser funcional
  al instante tras restore. Las categorías preservan sus IDs evitando relaciones incorrectas.
  Los orígenes de bienes se restauran desde CSV. Los 1.420 bienes recuperan su clasificación
  de origen. Los estados de seguridad de usuarios (bloqueado, forzar cambio de contraseña,
  usuario principal) se restauran del backup. BhagamApps v1.22.2.

---

## v1.23.1 — 2026-06-13

### Added (AUDIT-BACKUP-001 — Auditoría de Restaurabilidad de Respaldos)

- Auditada la restaurabilidad completa de los respaldos institucionales.
  Resultado: el sistema puede recuperarse parcialmente en restore manual (≈4h operador técnico)
  pero requiere 6 correcciones de código antes de implementar restauración automatizada.
  La información institucional crítica (bienes, usuarios, dependencias) es 95% recuperable.
  El RBAC visual (apps visibles por rol) y los orígenes de bienes no se restauran automáticamente.
  BhagamApps v1.22.1.

---

## v1.23.0 — 2026-06-13

### Added (IMPL-INFRA-BACKUP-002 — Centro de Administración de Backups)

- IEE ahora tiene un Centro de Administración de Backups accesible desde el panel web,
  sin necesidad de SSH. El Administrador Principal puede ver el estado del sistema de
  respaldo, listar todos los respaldos disponibles con sus metadatos (versiones, conteos
  por tabla), ver fichas técnicas individuales, descargar ZIPs y generar respaldos
  manuales con un solo clic. Alertas visuales por antigüedad: verde (<24h),
  amarillo (>24h), rojo (>48h). BhagamApps v1.22.0.

---

## v1.22.0 — 2026-06-13

### Added (IMPL-INFRA-BACKUP-001 — Sistema de Respaldo Institucional)

- IEE ahora tiene respaldo automático diario de todos sus datos institucionales.
  El comando `backup:export-seeders` exporta 23 tablas (bienes, usuarios, permisos, etc.)
  a CSV, genera un ZIP comprimido con metadata de versión y lo sube a Google Drive.
  La política de retención mantiene 30 respaldos diarios y 12 mensuales.
  Guía de restauración en `docs/operations/BACKUP-RESTORE-GUIDE.md`. BhagamApps v1.21.0.

---

## v1.21.1 — 2026-06-12

### Fixed (IMPL-CORE-MENU-001 completion — RBAC Rector + Biblioteca)

- La Rectoría ya no ve las secciones de Roles ni Permisos en el menú lateral.
  El RoleSeeder excluye correctamente las categorías `roles` y `permisos` del rol Rector.
  Biblioteca deshabilitada en el catálogo de apps (`habilitada: false`) — no puede
  aparecer en la pantalla principal vía `App::visiblesPara()`. BhagamApps v1.20.1.

---

## v1.21.0 — 2026-06-12

### Added (IMPL-CORE-MENU-001 — Reorganización Menú y RBAC Visual)

- La navegación lateral ahora refleja la arquitectura funcional de IEE.
  Todos los módulos quedan bajo el encabezado **Mis Módulos**.
  El acceso a Roles y Permisos solo aparece para usuarios con los permisos correspondientes.
  Los ítems de Inventario están en orden alfabético. Los módulos sin implementar
  (Grupos, Evaluación Docente, Biblioteca) fueron eliminados de la navegación. BhagamApps v1.20.0.

---

## v1.20.0 — 2026-06-12

### Added (IMPL-INV-012 — Catálogo de Orígenes)

- Los orígenes de bienes ahora están normalizados en un catálogo institucional administrable.
  Los 1,420 bienes existentes fueron clasificados automáticamente en 11 categorías.
  El formulario de creación usa un select del catálogo. El dashboard y los filtros facetados
  reflejan los orígenes normalizados. Inventario v2.15.0.

---

## v1.19.0 — 2026-06-12

### Added (IMPL-INV-011 — Búsqueda Facetada de Bienes)

- Los filtros del listado de bienes ahora muestran solo las opciones disponibles en el
  resultado actual, con conteos `Nombre (N)`. Al seleccionar un filtro, todos los demás
  se actualizan automáticamente reflejando la nueva distribución de resultados.
  Implementado con 6 facetas (Coordinador, Categoría, Dependencia, Estado, Origen, Custodio)
  calculadas via GROUP BY en BD. Inventario v2.14.0.

---

## v1.18.1 — 2026-06-12

### Fixed (HOTFIX-INV-010 — Error 419 en Búsqueda/Filtros de Bienes)

- Búsqueda reactiva, filtros y ordenamiento en `/inventario/bienes` ya no producen
  error 419. El acordeón móvil usa HTML estático y los catálogos de nombres ya no
  se serializan en el snapshot de Livewire. Inventario v2.13.1.

---

## v1.18.0 — 2026-06-12

### Added (IMPL-INV-009 — Buscador Inteligente de Bienes)

- **Búsqueda global reactiva** en el listado de bienes: busca simultáneamente por ID,
  nombre, serial, marca, categoría, dependencia, estado, origen, custodio, coordinador y
  más campos de detalle. Sin botón de búsqueda — reactivo con debounce 300ms.
- **Filtros nuevos**: Origen y Custodio (responsable actual) disponibles en escritorio y móvil.
- **Filtros reactivos**: todos los selects de filtro son ahora reactivos automáticos (sin blur).
- **Persistencia**: búsqueda, filtros, ordenamiento y paginación se conservan en la URL.
- **Correcciones de calidad**: `wire:key` en todos los loops de bienes, ordenamiento del
  servidor respetado para todos los roles.

---

## v1.17.0 — 2026-06-11

### Added / Fixed (IMPL-INV-DASH-002 — Optimización Dashboard Ejecutivo Inventario)

- Dashboard ejecutivo de Inventario optimizado con 10 mejoras funcionales y visuales (DASH-011→020).
  KPIs con porcentajes, gráfica de origen corregida, Accesos Rápidos al tope, Calidad de Datos
  ampliada (5 indicadores + conteos absolutos), Top 10 Dependencias, Top 10 Responsables,
  Resumen Ejecutivo y `wire:key` en todos los loops para morfología Livewire correcta.
  Ver `docs/impl/IMPL-INV-DASH-002-Optimizacion-Dashboard-Ejecutivo.md`.

---

## v1.16.4 — 2026-06-11

### Fixed (HOTFIX-USERS-007)

- Listado de usuarios incompleto al ordenar corregido.
  Causa: `wire:key` faltante en los loops `@forelse`, provocando que Livewire 3 usara
  morfología posicional y corrompiera snapshots de componentes hijo al cambiar el sort.
  Corrección: `wire:key="row-{id}"` en `<tr>` y `wire:key="card-{id}"` en `.card`.
  También resuelto: `ErrorException: Trying to access array offset on null`
  en `HandleComponents.php:88` que ocurría al interactuar con filas afectadas.

---

## v1.16.3 — 2026-06-11

### Fixed (HOTFIX-USERS-006)

- Error 419 al ordenar, buscar y filtrar en `/iee/users/users` corregido definitivamente.
  La causa era un snapshot Livewire de `UserIndex` mayor a 16,383 bytes que PHP descartaba
  por falta de directorio temporal escribible. Corrección: componentes Livewire duplicados
  en vista móvil reemplazados con HTML estático; opciones perPage=50/100 eliminadas del
  selector; validación server-side de perPage en `updatedPerPage()`.
  Ver `docs/impl/HOTFIX-USERS-006-Correccion-Definitiva-419-Livewire.md`.

---

## v1.16.2 — 2026-06-11

### Fixed (HOTFIX-USERS-004)

- Diagnóstico de Error 419 en búsqueda/filtros/ordenamiento de usuarios completado.
  Causa: sesión de navegador expirada (SESSION_LIFETIME=120 min). El servidor procesa
  correctamente todas las peticiones Livewire con sesión válida (HTTP 200).
  Corrección en código: `->layout('layouts.app')` eliminado de `UserIndex::render()`;
  middleware `CheckForzarCambioPassword` corregido para permitir rutas `livewire/*`.

---

## v1.16.1 — 2026-06-11

### Fixed (HOTFIX-USERS-003)

- Error 500 en gestión de usuarios corregido.
  `UserIndex::mount(): void` declarado incorrectamente; eliminado `: void`.

---

## v1.16.0 — 2026-06-11

### Added (IMPL-USERS-002)

- Gestión de usuarios modernizada: búsqueda reactiva por nombre/apellido/email,
  filtros por rol y estado, y ordenamiento por todas las columnas.
  Flujo de creación y edición sin cambios.

---

## v1.15.3 — 2026-06-11

### Fixed (HOTFIX-DEP-001)

- Catálogo Dependencias restaurado: Error 500 por columna `name` inexistente en `users`.
  `DependenciasIndex::mount()` ahora usa `nombres` y `apellidos` correctamente.

---

## v1.15.2 — 2026-06-11

### Fixed (HOTFIX-INV-DASH-002)

- Error 500 en Dashboard Ejecutivo de Inventario corregido.
  MySQL `ONLY_FULL_GROUP_BY` rechazaba `GROUP BY CASE WHEN origen...` en DASH-005.
  Fix: `GROUP BY origen` en SQL + normalización PHP de NULL/vacío a "Sin origen".

---

## v1.15.1 — 2026-06-11

### Fixed (HOTFIX-INV-DASH-001)

- Menú lateral y Apps corregidos para abrir el Dashboard Ejecutivo de Inventario como página inicial del módulo.

---

## v1.15.0 — 2026-06-11

### Added (IMPL-INV-DASH-001)

- Dashboard Ejecutivo de Inventario IEE en página principal del módulo.
  Rector, Administrador, Coordinadores y Responsables acceden al tablero institucional
  con KPIs, gráficas de distribución, alertas operativas, accesos rápidos
  e indicadores de calidad de datos al ingresar a Inventario.

---

## v1.14.1 — 2026-06-11

### Fixed

- **[HOTFIX-RBAC-001]** Restaurado el acceso a todos los módulos tras restauración de datos.
  El rector y demás roles recibían 403 al ingresar a Usuarios e Inventario por pérdida de vínculos
  en `app_role`. Corregido con migración de recuperación RBAC. Sin regresiones funcionales.

---

## v1.14.0 — 2026-06-11

### Added

- **[IMPL-USERS-001]** Los administradores e institución ahora pueden gestionar contraseñas
  y estados de cuentas sin acceso directo a la base de datos.
  Restablecimiento de contraseña con confirmación visual, opción de forzar cambio al siguiente
  ingreso, bloqueo y desbloqueo de cuentas, y auditoría completa de todas las acciones.

---

## v1.13.1 — 2026-06-11

### Fixed

- **[IMPL-CORE-CLEANUP-001 Fase 2]** Completada migración de suite de pruebas al modelo activo.
  12 archivos de prueba de Fortify/Jetstream/User/Auth actualizados de `App\Models\User`
  a `Modules\User\Entities\User`. Factories propias creadas en `Modules\User\Database\Factories\`.
  `ProfileInformationTest` adaptado a campos reales (`nombres`, `apellidos`);
  `RegistrationTest` adaptado con campos IEE e insert de roles requeridos.
  10 tests pasan; 7 correctamente omitidos. 0 regresiones.

---

## v1.13.0 — 2026-06-11

### Fixed

- **[IMPL-CORE-CLEANUP-001]** Remediación crítica de Fortify y modelo User.
  Cuatro Fortify/Jetstream Actions (`UpdateUserPassword`, `ResetUserPassword`,
  `UpdateUserProfileInformation`, `DeleteUser`) corregidos para usar
  `Modules\User\Entities\User` (modelo activo) en lugar de `App\Models\User`.
  `UpdateUserProfileInformation` alineado con campos reales del schema
  (`nombres`, `apellidos`, `userID`, `email`). Binding de `LoginResponse` en
  `FortifyServiceProvider` corregido. `app/Models/User.php` neutralizado
  (dependencias Spatie y RolSistema eliminadas; archivo clasificado como LEGACY
  pendiente de eliminación). Funciones de cambio de contraseña, actualización
  de perfil, reset de contraseña y eliminación de cuenta — antes inoperativas
  con HTTP 500 — son ahora funcionales. Suite de pruebas ejecuta sin fatal errors
  (18 pass vs 1 antes de esta corrección).

---

## v1.12.1 — 2026-06-10

### Fixed

- **[IMPL-INFRA-001]** URL pública del producto migrada a `/iee`.
  Symlink `/iee` creado en servidor; `/Modular` coexiste durante transición.
  `APP_URL`, `ASSET_URL` y `SESSION_PATH` actualizados. La URL canónica del producto
  es ahora `http://bhagamapps.com/iee`.

---

## v1.12.0 — 2026-06-10

### Changed

- **[IMPL-CORE-BRANDING-001]** Activación de identidad institucional IEE.
  Primera versión con branding IEE desplegado. APP_NAME, APP_URL, ASSET_URL,
  SESSION_PATH, AdminLTE logo/título, footer y welcome reflejan identidad institucional.
  BhagamApps permanece como organización desarrolladora (referencia discreta en footer).
