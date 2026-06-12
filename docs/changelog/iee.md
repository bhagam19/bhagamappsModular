# IEE — Changelog de Producto

Registra cambios de identidad y configuración del producto institucional **IEE**
(Institución Educativa Entrerríos — Sistema de Inventario Institucional).

La plataforma técnica subyacente se documenta en [`docs/changelog/bhagamapps.md`](bhagamapps.md).

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
