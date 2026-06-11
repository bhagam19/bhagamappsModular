# IEE — Changelog de Producto

Registra cambios de identidad y configuración del producto institucional **IEE**
(Institución Educativa Entrerríos — Sistema de Inventario Institucional).

La plataforma técnica subyacente se documenta en [`docs/changelog/bhagamapps.md`](bhagamapps.md).

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
