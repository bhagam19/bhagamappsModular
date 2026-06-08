# CrudGenerator — Changelog

Historial de cambios del módulo CrudGenerator.
Módulo: `Modules/CrudGenerator` — Herramienta interna de desarrollo.

---

## v1.1.0 — 2025-06-23

### Added

- Servicios para generar CRUD automáticamente con:
  - Rutas web y API.
  - Componentes Livewire dinámicos.
  - Vistas Blade con edición inline.
  - Ítems de menú en AdminLTE.
  - Permisos con guardias.
- Integración con `EditarCampoGenerico` para edición en línea.
- Estructura de servicios orientados a generación modular.

### Changed

- Refactor del generador base para dividir responsabilidades y facilitar mantenimiento.

---

## v1.0.0 — 2025-06-23

### Added

- Implementado módulo `CrudGenerator` para generación automática de CRUDs.
- Comandos Artisan para crear y limpiar CRUDs generados.
- Gestión automática de permisos y configuración de AuthServiceProvider.
- Inclusión dinámica de CRUDs generados en el menú AdminLTE.
- Rutas web y API configuradas para los CRUDs generados.
- Componentes Livewire integrados para manejo dinámico de datos.
- Archivos `stub` editables para vistas, rutas, permisos y menú.
- Migraciones, seeders y configuración inicial del módulo.

> Versión inicial publicada junto con BhagamApps v1.2.0.
