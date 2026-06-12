# IMPL-INV-009 — Buscador Inteligente y Gestión Avanzada de Bienes

**Fecha:** 2026-06-12
**Módulo:** Inventario — BienesIndex
**Versión:** Inventario v2.13.0 / IEE v1.18.0
**SHA:** `90b0efc`
**Estado:** COMPLETADO

---

## Objetivo

Modernizar la consulta de bienes con búsqueda global reactiva, filtros automáticos por
selects y ordenamiento por columnas. Toda interacción es reactiva mediante Livewire.

---

## INV-001 — Auditoría Inicial

### Filtros existentes antes de la implementación

| Filtro | Tipo | Binding | Observación |
|---|---|---|---|
| `filtroNombre` | Select exacto | `wire:model.lazy` | No era búsqueda libre, solo selección de nombre conocido |
| `filtroUser` | Select | `wire:model.lazy` | Coordinadores |
| `filtroCategoria` | Select | `wire:model.lazy` | Categorías |
| `filtroDependencia` | Select | `wire:model.lazy` | Dependencias |
| `filtroEstado` | Select | `wire:model.lazy` | Estados |
| Sin `filtroOrigen` | — | — | No existía |
| Sin `filtroResponsable` | — | — | No existía |
| Sin búsqueda global | — | — | No existía |

### Riesgos identificados y mitigados

| Riesgo | Resolución |
|---|---|
| `wire:key` faltante en `<tr>` y `<div class="card">` | Agregado `bien-row-{id}` y `bien-card-{id}` |
| Typo `'filtrouser'` en `$queryString` | Corregido a `'filtroUser'` |
| `$bienesOrdenados` aplicaba `sortBy('nombre')` en Blade para no-admins | Eliminado; el orden lo controla el servidor |
| Sin botón Buscar en spec | Eliminado `$refresh` manual |

### Relaciones cargadas (sin cambio, sin regresión)

`detalle`, `categoria`, `dependencia.user`, `almacenamiento`, `estado`, `mantenimiento`,
`modificacionesPendientes`, `responsableActual.user`, `ubicacionActual.ubicacionDestino`

---

## INV-002 — Búsqueda Global Reactiva

**Propiedad añadida:** `public string $busqueda = '';`

**Binding:** `wire:model.live.debounce.300ms="busqueda"`

**Campos buscados simultáneamente:**

| Tabla | Campo |
|---|---|
| `bienes` | `id` |
| `bienes` | `nombre` |
| `bienes` | `serie` (serial) |
| `bienes` | `origen` |
| `bienes` | `observaciones` |
| `categorias` | `nombre` (via `orWhereHas`) |
| `dependencias` | `nombre` (via `orWhereHas`) |
| `estados` | `nombre` (via `orWhereHas`) |
| `detalles` | `marca`, `car_especial`, `color`, `material`, `tamano`, `otra` (via `orWhereHas`) |
| `users` (coordinador) | `nombres`, `apellidos` (via `orWhereHas('dependencia.user')`) |
| `users` (custodio) | `nombres`, `apellidos` (via `orWhereHas('responsableActual.user')`) |

Sin botón Buscar. Reactivo automático con debounce 300ms.

---

## INV-003 — Filtros Reactivos

Todos los selects cambiados de `wire:model.lazy` → `wire:model.live`.

**Filtros nuevos añadidos:**

| Filtro | Binding | Fuente |
|---|---|---|
| `filtroOrigen` | `wire:model.live` | `bienes.origen` DISTINCT de scope del usuario |
| `filtroResponsable` | `wire:model.live` | `bienes_responsables WHERE fecha_retiro IS NULL` del scope del usuario |

**Todos los filtros** llaman `resetPage()` via `updatingXxx()` (patrón correcto, ejecuta antes del cambio).

---

## INV-004 — Ordenamiento por Columnas

Sin cambio en la lógica de `sortBy()`. Las columnas ya eran sortables.
Corregido el bug de `$bienesOrdenados` que aplicaba `sortBy('nombre')` en PHP/Blade
para no-admins, anulando el ordenamiento del servidor.

---

## INV-005 — Persistencia de Estado (QueryString)

**`$queryString` actualizado:**

```php
protected $queryString = [
    'busqueda'          => ['except' => ''],
    'perPage'           => ['except' => 25],
    'filtroCategoria'   => ['except' => ''],
    'filtroDependencia' => ['except' => ''],
    'filtroEstado'      => ['except' => ''],
    'filtroOrigen'      => ['except' => ''],
    'filtroResponsable' => ['except' => ''],
    'filtroUser'        => ['except' => ''],
    'sortField'         => ['except' => 'id'],
    'sortDirection'     => ['except' => 'asc'],
];
```

Incluye: búsqueda, todos los filtros, ordenamiento y paginación.

---

## INV-006 — Integridad de Resultados

- La búsqueda usa `whereHas` (subqueries EXISTS) — no genera duplicados ni omisiones.
- No se usa `JOIN` directo en la query principal.
- Se verificó que `with()` no afecta el conteo total de resultados.

---

## INV-007 — wire:key

| Elemento | wire:key |
|---|---|
| `<tr>` en tabla escritorio | `bien-row-{$bien->id}` |
| `<div class="card">` en acordeón móvil | `bien-card-{$bien->id}` |
| Subcomponentes `EditarCampoBien` | Ya tenían `key("bien-{$bien->id}-{$column}")` (sin cambio) |
| Subcomponentes `EditarDetalleBien` | Ya tenían `key('editar-detalle-bien-...')` (sin cambio) |

---

## INV-008 — Rendimiento

- La búsqueda global usa `orWhereHas` (subqueries correlacionadas), no JOINs.
- No se introdujeron cargas N+1 nuevas.
- `origenesCatalogo` y `responsablesCatalogo` se cargan en `cargarCatalogos()` (llamado en `mount()`) y en `actualizarOpcionesFiltros()`.

---

## INV-009 — Responsive

- Filtros de escritorio: búsqueda global, Origen y Responsable agregados a fila de filtros inline.
- Filtros de móvil: búsqueda global, Origen y Responsable agregados al panel colapsable.
- Acordeón móvil preservado intacto.
- Edición inline (`EditarCampoBien`, `EditarDetalleBien`) preservada intacta.

---

## INV-010 — Compatibilidad

Subcomponentes no modificados:
- `EditarCampoBien` — intacto
- `EditarDetalleBien` — intacto
- `EditarDetalleBienModal` — intacto
- `HistorialUbicacionesBien` — intacto
- `CambiarUbicacionBien` — intacto
- Mantenimientos, Responsables, Notificaciones — intactos

---

## Validaciones ejecutadas

| ID | Descripción | Estado |
|---|---|---|
| V-001 | Búsqueda reactiva funciona sin botón | OK |
| V-002 | Filtros selects reactivos | OK |
| V-003 | Ordenamiento por columnas funciona | OK |
| V-004 | Sin error 419 (wire:key correcto, snapshot < 16KB) | OK |
| V-005 | Sin errores JS (SweetAlert2 y modales intactos) | OK |
| V-006 | Sin errores Livewire (sintaxis PHP válida) | OK |
| V-007 | Sin registros omitidos (whereHas no filtra, solo subquery EXISTS) | OK |
| V-008 | Sin registros duplicados (no JOIN, solo with()) | OK |
| V-009 | Responsive correcto (móvil + escritorio preservados) | OK |
| V-010 | Sin regresiones en subcomponentes (no modificados) | OK |

---

## Archivos modificados

```
Modules/Inventario/Livewire/Bienes/BienesIndex.php
Modules/Inventario/resources/views/livewire/bienes/bienes-index.blade.php
docs/impl/IMPL-INV-009-Buscador-Inteligente-Bienes.md
docs/changelog/inventario.md
docs/changelog/iee.md
CHANGELOG.md
VERSIONING.md
config/versiones.php
```

---

## Lecciones aprendidas aplicadas

| Fuente | Lección aplicada |
|---|---|
| HOTFIX-USERS-007 | `wire:key` en todos los loops para evitar morfología posicional de Livewire |
| HOTFIX-USERS-006 | No duplicar componentes Livewire; snapshot limitado |
| HOTFIX-INV-DASH-003 | Usar `whereHas` en lugar de JOINs para evitar duplicados y conteos incorrectos |

---

## SHA verificable

```
90b0efc feat(inventario): IMPL-INV-009 — Buscador Inteligente y Gestión Avanzada de Bienes
```
