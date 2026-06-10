# Inventario — Changelog

Historial de cambios del módulo Inventario.
Módulo: `Modules/Inventario` — Rutas: `/inventario/*`

---

## v2.9.0 — 2026-06-10

### Added (IMPL-INV-005 — Historial de Ubicaciones)

- **[RF-001/RF-002/RF-003/RF-004/RF-005]** Trazabilidad física completa de bienes:
  tabla `historial_ubicaciones_bienes` (bien_id, ubicacion_origen_id, ubicacion_destino_id,
  user_id, fecha_movimiento, observaciones), entidad `HistorialUbicacionBien`.

- **[RF-001]** Relaciones `ubicacionActual()` y `historialUbicaciones()` añadidas a `Bien`.
  Relación `movimientos()` añadida a `Ubicacion`.
  Columna `ubicacion_actual` disponible (oculta por defecto) en `BienesIndex` — eager loaded
  sin N+1 via `with('ubicacionActual.ubicacionDestino')`.

- **[RF-002]** Componente Livewire `HistorialUbicacionesBien` con formulario inline de cambio
  de ubicación (RI-001: registra origen, destino, fecha, usuario; RI-002: destino validado por FK;
  RI-003: origen derivado del último historial). Ruta `GET /inventario/ubicaciones/historial`.

- **[RF-003/RF-004]** Historial inline expandible por bien: muestra origen, destino, fecha
  movimiento, usuario responsable. Primera asignación muestra origen como "Primera asignación".

- **[RF-005]** Responde: ¿Dónde está? (ubicacionActual), ¿Dónde estuvo? (historial),
  ¿Cuándo fue movido? (fecha_movimiento), ¿Quién lo hizo? (user).

- **Permisos RBAC:** `ver-historial-ubicaciones-bienes` (Admin + Rector + Coordinador ver),
  `cambiar-ubicacion-bienes` (Admin + Rector únicamente).
  Gates registrados en `AuthServiceProvider`.

- **Sidebar:** Entrada "Historial Ubicaciones" añadida al submenú Inventario.

- **Componente standalone:** `CambiarUbicacionBien` para uso independiente embebido.

---

## v2.8.2 — 2026-06-10

### Fixed (IMPL-INV-004 suplemento — validaciones residuales)

- **[V-005]** Corregido SQL bug en `BienesIndex.filtrarBienesQuery()`: columnas virtuales `user_id`
  y `detalle` estaban en `$ordenBase` y podían generar `ORDER BY bienes.user_id` / `ORDER BY bienes.detalle`
  que no existen en BD. Añadido guard de allowlist `$columnasSortables`; sort de columnas no listadas
  cae a `id`.

- **[V-003]** Eliminada propiedad de formulario `public $user_id` de `BienesIndex` (nunca usada
  en `store()`; herencia de diseño anterior sin columna BD correspondiente).

- **[V-003/V-004]** Eliminados casos `user_id` y `ubicacion_id` de `EditarCampoBien.inferirTabla()`
  y `cargarOpciones()`: código muerto, la vista intercepta ambas columnas antes de invocar el componente.
  Eliminado import `Ubicacion` que quedaba sin uso.

- **[V-004]** Eliminado bloque `@if ($column === 'ubicacion_id')` de `bienes-index.blade.php`:
  código muerto, `ubicacion_id` nunca puede estar en `$visibleColumns`.

- **[GAP-001 cierre final]** Eliminadas definiciones de Gates `aprobar-cambios-bienes` y
  `rechazar-cambios-bienes` de `AuthServiceProvider.php`: referenciaban permiso `aprobar-pendientes-bienes`
  no seeded en ninguna migración. `Notificaciones.php` (el único llamador) fue eliminado en v2.8.1.

---

## v2.8.1 — 2026-06-10

### Fixed (IMPL-INV-004 — Inventory Core Remediation Package)

- **[GAP-001]** Eliminado `Livewire/Notifications/Notificaciones.php`: componente completamente roto
  (vista inexistente, permisos `aprobar-cambios-bienes`/`rechazar-cambios-bienes` no registrados
  en ninguna migración, funcionalidad duplicada por `HmbIndex`). 0 `AuthorizationException` posibles.

- **[GAP-001]** Corregido `NotificacionesIcono.php`: path de vista corregido de
  `livewire.notifications.notificaciones-icono` → `livewire.hmb.notificaciones-icono`.

- **[GAP-002]** Eliminado `user_id` de `Bien.$fillable` y de `Bien.getDisplayValue()`:
  la columna `bienes.user_id` nunca existió en base de datos. La relación usuario→bien
  es exclusivamente vía `dependencias.user_id`.

- **[GAP-002]** Corregido `ActaPDFController::show()`: query reescrita usando JOIN a
  `dependencias.user_id` en lugar de `where('user_id', $userId)` (que siempre devolvía vacío).
  Eliminado `'user'` del `with()` (relación inexistente en `Bien`).

- **[GAP-004]** Eliminado `ubicacion_id` de `BienesIndex.$availableColumns`:
  la columna `bienes.ubicacion_id` no existe en base de datos. La ubicación se accede
  a través de `dependencia.ubicacion_id` (columna real en `dependencias`).

### Decision (GAP-003 — `bienes.origen` vs catálogo `origenes`)

- **Decisión arquitectónica:** mantener `bienes.origen` como campo de texto libre.
  El catálogo `origenes` sirve como referencia administrable, no como FK.
  La normalización completa (agregar `origen_id FK` a `bienes` con migración de datos)
  se programa para una iteración futura como IMPL-INV-005 o posterior.

---

## v2.8.0 — 2026-06-09

### Added (IMPL-INV-002A — Catalog & HEB Navigation Integration)

- **[IMPL-INV-002A]** Entradas de navegación añadidas al sidebar de Inventario:
  Categorías, Dependencias, Ubicaciones, Estados, Orígenes, Almacenamientos, Mantenimientos.
  Cada entrada protegida por `can: ver-{catalogo}` via AdminLTE.

- **[IMPL-INV-002A]** Entrada "Historial Eliminaciones" (HEB) añadida al sidebar de Inventario.
  Protegida por `can: gestionar-historial-eliminaciones-bienes`.

- **[IMPL-INV-002A]** `Gate::define('gestionar-historial-eliminaciones-bienes', ...)` añadido
  a `AuthServiceProvider` — D-002 de AUDIT-INV-NAV-001 corregido.

- **IMPL-INV-002 declarado CERRADO DEFINITIVAMENTE.**

---

## v2.7.1 — 2026-06-09

### Fixed

- **[IMPL-INV-003A]** Integración de navegación: entrada "Responsables" añadida al submenú de Inventario
  en `config/adminlte.php`. Protegida con `can: 'ver-responsables-bienes'`.
  Activa en rutas `inventario/responsables*`. Cierre formal de IMPL-INV-003.

---

## v2.7.0 — 2026-06-09

### Added

- **[IMPL-INV-003]** Gestión completa de Responsables y Custodios de bienes.
  Nueva sección `/inventario/responsables` protegida por `permission:ver-responsables-bienes`.

- **[IMPL-INV-003]** Asignación de custodio: formulario inline para seleccionar usuario, fecha y observaciones.
  Reglas RI-001/RI-003 aplicadas — un bien solo puede tener un custodio vigente.

- **[IMPL-INV-003]** Transferencia de custodio: cierra responsable anterior (registra `fecha_retiro = fecha_asignacion_nuevo`),
  registra el nuevo responsable. Regla RI-002 garantizada.

- **[IMPL-INV-003]** Liberación de custodio: acción para desvincular responsable vigente sin asignar reemplazo.

- **[IMPL-INV-003]** Historial inline por bien: expande la fila del bien para mostrar el historial completo de custodios
  con estado Vigente / Retirado.

- **[IMPL-INV-003]** Filtros en ResponsablesIndex: por nombre de bien, por dependencia, por responsable vigente.

- **[IMPL-INV-003]** Consulta por responsable (RF-005): filtro por usuario muestra todos los bienes asignados.

- **[IMPL-INV-003]** 4 nuevos permisos (`ver/asignar/editar/transferir-responsables-bienes`) + 4 gates en AuthServiceProvider.
  Administrador y Rector: 4/4. Coordinador: solo `ver-responsables-bienes`.

- **[IMPL-INV-003]** Relación `Bien::responsableActual()` (hasOne con `whereNull('fecha_retiro')`).
  Relación `User::bienesAsignados()` inversa.

- **[IMPL-INV-003]** Columna "Custodio" disponible en BienesIndex (toggle, no visible por defecto).
  Eager loading de `responsableActual.user` añadido a `filtrarBienesQuery()`.

---

## v2.6.0 — 2026-06-09

### Added

- **[IMPL-INV-002 / Fase 1]** CRUD administrativo completo para los catálogos maestros del módulo.
  Nuevas secciones con búsqueda, paginación, ordenamiento, edición inline y protección de integridad referencial antes de eliminar.
  Catalogs implementados: **Categorías**, **Dependencias**, **Ubicaciones**, **Estados de Bien**, **Orígenes**.

- **[IMPL-INV-002 / Fase 2]** CRUD administrativo para los catálogos auxiliares: **Almacenamientos**, **Mantenimientos**.

- **[IMPL-INV-002]** Nuevas rutas GET bajo `/inventario/catalogos/{catalog}` protegidas por `app.access:inventario` + permiso individual `ver-{catalog}`.
  Rutas: `/catalogos/categorias`, `/catalogos/dependencias`, `/catalogos/ubicaciones`, `/catalogos/estados`, `/catalogos/origenes`, `/catalogos/almacenamientos`, `/catalogos/mantenimientos`.

- **[IMPL-INV-002]** 28 nuevos permisos en categoría `catalogos` (ver/crear/editar/eliminar × 7 catálogos).
  Asignados: Administrador y Rector reciben los 28; Coordinador recibe solo los 7 `ver-*`.
  Migración: `2026_06_09_000008_add_catalog_permissions`.

- **[IMPL-INV-002]** Tabla `origenes` creada con columnas `nombre` y `descripcion`. Origen de bien era texto libre en `bienes.origen`.
  El catálogo queda disponible para normalización futura.
  Migración: `2026_06_09_000007_create_origenes_table`.
  Modelo: `Modules/Inventario/Entities/Origen.php`.

- **[IMPL-INV-002]** 7 Componentes Livewire bajo `Modules/Inventario/Livewire/Catalogos/` (auto-descubiertos por `InventarioServiceProvider`).
  Alias: `catalogos.{catalog}-index`.

- **[IMPL-INV-002]** Gates de autorización registrados en `AuthServiceProvider` para los 28 slugs nuevos,
  habilitando uso de `@can('editar-categorias')` etc. en vistas Blade.

---

## v2.5.0 — 2026-06-09

### Fixed

- **[IMPL-INV-001 / H-CRIT-001]** Creado permiso `gestionar-historial-eliminaciones-bienes`
  y asignado a Administrador y Rector. La ruta `GET /inventario/heb` exigía este permiso
  mediante middleware pero no existía en BD — todos los roles obtenían HTTP 403.
  Migración: `2026_06_09_000004_add_heb_permission_and_assign_roles`.

- **[IMPL-INV-001 / H-CRIT-002]** Creada tabla `bienes_responsables` (Escenario A —
  funcionalidad vigente). Modelo `BienResponsable`, relación `Bien::responsables()`,
  permiso `asignar-responsables-a-bienes` y seeder `BienesResponsablesSeeder` existían
  sin tabla de respaldo. Cualquier acceso a la relación generaba error SQL fatal.
  Migración: `2026_06_09_000005_create_bienes_responsables_table`.
  Columnas: `bien_id`, `user_id`, `observaciones`, `fecha_asignacion`, `fecha_retiro`.

- **[IMPL-INV-001 / H-ALTO-001]** App `inventario` asignada al rol Coordinador en
  `app_role`. El rol tenía permisos `ver-bienes`, `crear-bienes`, `editar-bienes` pero el
  middleware `app.access:inventario` bloqueaba el acceso antes de evaluar permisos.
  Migración: `2026_06_09_000006_assign_inventario_app_to_coordinador`.

- **[IMPL-INV-001 / H-ALTO-002]** Corregido orden de null check en
  `HmbIndex::aprobarModificacion()`. La variable `$modificacion` era accedida en
  `$bien = Bien::find($modificacion->bien_id)` antes del guard `if (!$modificacion)`.
  Corregido: null check movido al inicio del método, antes de cualquier uso.
  También corregido typo en dispatch del catch: `modificacionActualizad` → `modificacionActualizada`.

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
  Ver `docs/impl/IMPL-004-Migración de FLOAT a DECIMAL(12,2) en bienes.precio.md` y `docs/plan/PLAN-IMPL-004.md`.

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
