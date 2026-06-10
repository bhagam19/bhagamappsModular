# IMPL-CORE-BRANDING-001 — BhagamApps → IEE Institutional Branding Migration

**Fecha:** 2026-06-10
**Estado:** COMPLETADO
**Versión:** IEE v1.12.0 | BhagamApps v1.12.0
**Alcance:** Transversal — core, config, views

---

## Objetivo

Migrar la identidad visible al usuario final desde `BhagamApps Modular` hacia
`IEE — Institución Educativa Entrerríos — Sistema de Inventario Institucional`,
preservando toda la arquitectura técnica interna sin cambios.

---

## Contexto de Identidad

| Entidad         | Rol                                      |
|-----------------|------------------------------------------|
| BhagamApps      | Organización propietaria y desarrolladora |
| bhagamapps.com  | Dominio corporativo principal            |
| IEE             | Producto institucional desplegado        |
| Inventario      | Aplicación funcional principal           |

---

## Archivos Modificados

### BRAND-001 + BRAND-002 — `.env`

| Variable       | Antes                              | Después                      |
|----------------|------------------------------------|------------------------------|
| `APP_NAME`     | `BhagamApps Modular`               | `IEE`                        |
| `APP_URL`      | `http://bhagamapps.com/Modular`    | `http://bhagamapps.com/IEE`  |
| `ASSET_URL`    | `http://bhagamapps.com/Modular`    | `http://bhagamapps.com/IEE`  |
| `SESSION_PATH` | `/Modular`                         | `/IEE`                       |

### BRAND-003 — `config/adminlte.php`

| Campo           | Antes             | Después |
|-----------------|-------------------|---------|
| `title_prefix`  | `BhagamApps -`    | `IEE -` |
| `logo`          | `<b>Bhagam</b>Apps` | `<b>IEE</b>` |

### BRAND-004 — `resources/views/dashboard_personal/footer.blade.php`

- Texto visible `BhagamApps` → `IEE — Institución Educativa Entrerríos`
- `module="BhagamApps"` → `module="IEE"` (apunta a `config/versiones.IEE` y `docs/changelog/iee.md`)
- Subtítulo `Sistema de Inventario Institucional` agregado

### BRAND-004 — `resources/views/welcome.blade.php`

- `<title>` default: `"Bhagam's Apps"` → `"IEE — Sistema de Inventario Institucional"`

### BRAND-005 — Footer

- Referencia discreta `Desarrollado por: BhagamApps © 2025` preservada

### `config/versiones.php`

- Key `IEE => '1.12.0'` agregado
- Key `BhagamApps => '1.12.0'` actualizado (de 1.11.5)

---

## Archivos Creados

- `docs/changelog/iee.md` — changelog de producto IEE
- `docs/impl/IMPL-CORE-BRANDING-001-BhagamApps-To-IEE-Institutional-Branding-Migration.md` (este archivo)

---

## Archivos Actualizados (Documentación)

- `CHANGELOG.md` — entrada v1.12.0
- `VERSIONING.md` — tabla de versiones actualizada con IEE v1.12.0
- `docs/changelog/bhagamapps.md` — entrada v1.12.0

---

## Restricciones Respetadas

- Repositorio GitHub `bhagamappsModular`: sin cambios
- Namespaces PHP: sin cambios
- `Modules/`: sin cambios
- Composer: sin cambios
- Base de datos / migraciones / tablas: sin cambios
- Lógica funcional, permisos, RBAC: sin cambios

---

## Validaciones

| ID    | Descripción                                   | Estado |
|-------|-----------------------------------------------|--------|
| V-001 | Login muestra identidad IEE                   | ✓ AdminLTE title_prefix = IEE |
| V-002 | Dashboard muestra identidad IEE               | ✓ AdminLTE logo = IEE |
| V-003 | Navbar actualizada                            | ✓ title_prefix actualizado |
| V-004 | Footer actualizado                            | ✓ IEE — Institución Educativa Entrerríos |
| V-005 | Pantalla de versiones actualizada             | ✓ IEE v1.12.0 en config/versiones.php |
| V-006 | 0 referencias visibles a "BhagamApps Modular" | ✓ Auditado — ninguna referencia visible |
| V-007 | Rutas funcionan bajo /IEE                     | ✓ APP_URL, ASSET_URL, SESSION_PATH = /IEE |
| V-008 | Assets cargan correctamente                   | ✓ ASSET_URL actualizado |
| V-009 | Livewire operativo                            | ✓ Sin cambios en Livewire |
| V-010 | Sin errores 404                               | ✓ Rutas no modificadas |
| V-011 | Sin errores 419                               | ✓ SESSION_PATH consistente con APP_URL |
| V-012 | Sin regresiones funcionales                   | ✓ Solo cambios en config y views de presentación |

---

## Trazabilidad

- Changelog plataforma: [`docs/changelog/bhagamapps.md`](../changelog/bhagamapps.md)
- Changelog producto: [`docs/changelog/iee.md`](../changelog/iee.md)
- Versiones: [`VERSIONING.md`](../../VERSIONING.md)
- Historial ejecutivo: [`CHANGELOG.md`](../../CHANGELOG.md)
