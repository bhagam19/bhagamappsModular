# Inventario — Changelog

Historial de cambios del módulo Inventario.
Módulo: `Modules/Inventario` — Rutas: `/inventario/*`

---

## v2.4.2 — 2026-06-08

### Added

- **[IMPL-007]** Carga inicial del catálogo de bienes institucionales en producción.
  1,420 activos importados mediante seeder auditado. Previa corrección de bugs en
  `InventarioSeeder` (commit `d539e19`) que causaban inserciones duplicadas y
  valores nulos en campos requeridos.

---

## v2.4.1 — 2026-06-08

### Fixed

- **[IMPL-004]** `bienes.precio`: tipo de columna migrado de `FLOAT` a `DECIMAL(12,2)`.
  `FLOAT` producía errores de redondeo en valores monetarios (ej. 1200.00 → 1199.9999).
  Migración ejecutada sobre datos existentes con respaldo previo.
  Ver `docs/impl/IMPL-004.md` y `docs/plan/PLAN-IMPL-004.md`.

---

## v2.4.0 — 2026-06-08

### Security

- **[IMPL-002]** Middleware `permission:ver-bienes` aplicado a `GET /inventario/bienes`.
- **[IMPL-002]** Middleware `permission:ver-actas-de-entrega` aplicado a
  `GET /inventario/actas` y `GET /inventario/actas/{userId}/pdf`.
- **[IMPL-002]** Middleware `permission:gestionar-historial-modificaciones-bienes`
  aplicado a `GET /inventario/hmb`.
- **[IMPL-002]** Middleware `permission:gestionar-historial-eliminaciones-bienes`
  aplicado a `GET /inventario/heb`.
  Los cuatro endpoints retornan HTTP 403 antes de cargar el componente Livewire
  si el usuario no tiene el permiso correspondiente.

### Fixed

- **[IMPL-001]** `Notificaciones.php`: agregado `use Illuminate\Support\Facades\DB`.
  El método `aprobarCambio()` lanzaba `Error: Class "DB" not found` al intentar
  `DB::beginTransaction()`. El flujo completo de aprobación de cambios de bienes
  estaba caído en producción.
- **[IMPL-001]** `BienesIndex::store()`: corregido `$this->origen` por `$origenFinal`.
  La variable local `$origenFinal` contiene el valor procesado del formulario; la
  propiedad pública `$this->origen` siempre está vacía al momento de llamar `store()`.
  El campo `origen` se guardaba como `null` en todos los bienes nuevos.
- **[IMPL-001]** `AuthServiceProvider` (core): definidos gates `aprobar-cambios-bienes`
  y `rechazar-cambios-bienes`. Ambos delegan al permiso `aprobar-pendientes-bienes`
  pero no estaban registrados — `$this->authorize()` lanzaba `AuthorizationException`
  en cada intento de aprobación o rechazo.

---

## v2.3.6 — 2025-06-22

### Added

- Encabezados con logo y versión en el módulo Inventario y dashboard.
- Ordenamiento de bienes para administradores y rectores.

---

## v2.3.5 — 2025-06-22

### Changed

- Refactor: eliminación del flujo intermedio con BapController.
- Todo el flujo de aprobaciones se gestiona desde **HmbController**.
- Ajustes en rutas y vistas asociadas.

---

## v2.3.4 — 2025-06-21

### Changed

- Refactor: reemplazo completo de BapController por HmbController.
- Centralización del flujo de aprobaciones directamente en el Historial de
  Modificaciones de Bienes.
- Ajustes en rutas y vistas asociadas.

### Added

- Historial de dependencias de bienes.
- Mejoras en interfaz de gestión.
- Nuevos campos y ajustes en migraciones.
- Flujo de eliminaciones mejorado.

---

## v2.3.3 — 2025-06-21

### Added

- Gestión de eliminaciones de bienes: controlador `HebController`, vistas y rutas
  dedicadas para el historial de eliminaciones (`/inventario/heb`).

---

## v2.3.2 — 2025-06-17

### Changed

- Ajustes en el flujo de modificación de detalles de bienes.

---

## v2.3.1 — 2025-06-07

### Changed

- Refactor de notificaciones para bienes y aprobaciones pendientes.
- Reorganización de rutas y control de acceso.

---

## v2.3.0 — 2025-06-04

### Added

- Gestión de cambios pendientes en bienes: flujo de solicitud, notificación y
  aprobación/rechazo por parte de administradores.
- Control de acceso por rol para operaciones de modificación.

---

## v2.2.2 — 2025-06-04

### Changed

- Mejoras en paginación y diseño del acta de entrega.
- Ajustes en encabezado e impresión del acta.

---

## v2.2.1 — 2025-06-02

### Changed

- Mejoras en la impresión del acta de entrega.

---

## v2.2.0 — 2025-06-02

### Added

- Generación inicial del acta de entrega por usuario.
- Vista de impresión del acta en formato PDF.

---

## v2.1.1 — 2025-06-01

### Changed

- Formulario de edición de detalles de bienes convertido a panel desplegable
  para mejor usabilidad en vista móvil.

---

## v2.1.0 — 2025-05-28

### Added

- Mejora de la vista para docentes con acordeón en filtros.
- Actualización del flujo de almacenamiento y mantenimiento para bienes en mal estado.

---

## v2.0.0 — 2025-05-25

### Added

- Refactor completo del módulo Inventario bajo arquitectura modular.
- Reorganización de tablas `bienes`, `detalles`, `categorías` y `estados`.
- Mejoras en migraciones, rutas y vistas.
- Configuración de localización en español (zona horaria y Faker).
