# IEE — Changelog de Producto

Registra cambios de identidad y configuración del producto institucional **IEE**
(Institución Educativa Entrerríos — Sistema de Inventario Institucional).

La plataforma técnica subyacente se documenta en [`docs/changelog/bhagamapps.md`](bhagamapps.md).

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
