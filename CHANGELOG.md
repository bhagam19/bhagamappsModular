# 📒 CHANGELOG

Registro de cambios del proyecto BhagamApps.

## [CrudGenerator-v1.1.0] - 2025-06-23
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

## [User-v2.0.0] - 2025-06-23
### Changed
- Refactor: Renombrado el módulo `Users` a `User` con nueva estructura modular.
- Ajustes en assets y configuración del frontend para adaptarse a la nueva estructura.

## [BhagamApps-v1.2.0] y [CrudGenerator-v1.0.0] - 2025-06-23
### Added
- Implementado módulo `CrudGenerator` para generación automática de CRUDs.
- Comandos Artisan para crear y limpiar CRUDs generados.
- Gestión automática de permisos y configuración de AuthServiceProvider.
- Inclusión dinámica de CRUDs generados en el menú AdminLTE.
- Rutas web y API configuradas para los CRUDs generados.
- Componentes Livewire integrados para manejo dinámico de datos.
- Archivos `stub` editables para vistas, rutas, permisos y menú.
- Migraciones, seeders y configuración inicial del módulo.

## [Inventario-v2.3.6] - 2025-06-22
### Added
- Encabezados con logo y versión en el módulo Inventario y dashboard.
- Ordenamiento de bienes para administradores y rectores.

## [Inventario-v2.3.5] - 2025-06-22
### Changed
- Refactor: eliminación del flujo intermedio con BapController.
- Todo el flujo de aprobaciones se gestiona desde **HmbController**.
- Ajustes en rutas y vistas asociadas.

## [Inventario-v2.3.4] - 2025-06-21
### Changed
- Refactor: reemplazo completo de BapController por HmbController.
- Ajustes en rutas y vistas asociadas.
- Centralización del flujo de aprobaciones directamente en el Historial de Modificaciones de Bienes.

### Added
- Añadido historial de dependencias de bienes.
- Mejoras en interfaz de gestión.
- Nuevos campos y ajustes en migraciones.
- Flujo de eliminaciones mejorado.

## [Inventario-v2.3.2] - 2025-06-17
### Changed
- Ajustes en el flujo de modificación de detalles de bienes.

## [Users-v1.1.1] - 2025-06-08
### Changed
- Actualización de nombres de permisos para mejorar consistencia.

## [App-v1.0.0] - 2025-06-07
### Added
- Creación inicial del módulo App como módulo central para la plataforma.

## [Inventario-v2.3.1] - 2025-06-07
### Changed
- Refactor de notificaciones para bienes y aprobaciones pendientes.
- Reorganización de rutas y control de acceso.

## [Users-v1.1.0] - 2025-06-07
### Changed
- Actualización de seeders de roles y permisos.
- Reasignación de permisos a roles (coordinadores, docentes, auxiliares).
- Ajustes en rutas de administración de usuarios.

## [BhagamApps-v1.2.0] - 2025-06-07
### Changed
- Reorganización del sistema de navegación y rutas generales.
- Limpieza de vistas antiguas y actualización de vistas administrativas.
- Refactor del sistema de notificaciones.

## [Inventario-v2.1.0] - 2025-05-28
### Added
- Mejora de la vista para docentes con acordeón en filtros.
- Actualización del flujo de almacenamiento y mantenimiento para bienes en mal estado.

## [Inventario-v2.0.0] - 2025-05-25
### Added
- Refactor completo del módulo Inventario bajo arquitectura modular.
- Reorganización de tablas `bienes`, `detalles`, `categorías` y `estados`.
- Mejoras en migraciones, rutas y vistas.
- Configuración de localización en español (zona horaria y Faker).


## [Users-v1.0.0] - 2025-05-22
### Added
- Creación inicial del módulo Users.
- Gestión de usuarios, roles y permisos.

## [BhagamApps-v1.1.0] - 2025-05-22
### Added
- Migración a estructura modular.
- Creación inicial de los módulos `Users` e `Inventario`.

## [BhagamApps-v1.0.0] - 2025-05-20
### Added
- Creación inicial de BhagamApps con Laravel 11.
- Integración con Jetstream y Livewire.
- Estructura modular inicial.
- Preparación del módulo Users (usuarios, roles y permisos).
