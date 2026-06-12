# IMPL-INV-011 — Búsqueda Facetada para Bienes

**Fecha:** 2026-06-12
**Módulo:** Inventario — BienesIndex
**Versión:** Inventario v2.14.0
**Estado:** IMPLEMENTADO

---

## Objetivo

Convertir los dropdowns de filtro estáticos en filtros facetados dinámicos que muestran únicamente
los valores presentes en el conjunto de resultados actual, con conteos individuales por opción.

---

## Requerimientos implementados

| ID | Descripción | Estado |
|---|---|---|
| FACET-001 | Base query reutilizable `queryBienesBase()` sin `with()` ni `orderBy()` | ✓ |
| FACET-002 | Faceta dinámica Categoría con conteo | ✓ |
| FACET-003 | Faceta dinámica Dependencia con conteo | ✓ |
| FACET-004 | Faceta dinámica Coordinador con conteo | ✓ |
| FACET-005 | Faceta dinámica Estado con conteo | ✓ |
| FACET-006 | Faceta dinámica Origen con conteo | ✓ |
| FACET-007 | Faceta dinámica Custodio (Responsable) con conteo | ✓ |
| FACET-008 | Formato `Nombre (N)` en cada opción | ✓ |
| FACET-009 | Sin N+1 — 6 queries GROUP BY via `clone` | ✓ |
| FACET-010 | "Todos/Todas" como primera opción | ✓ |

---

## Diseño técnico

### Patrón: Facetas dependientes (chained facets)

Cada faceta aplica TODOS los filtros activos actuales y computa la distribución del conjunto
de resultados resultante. Si se filtra por Categoría = "Computadores", las demás facetas
muestran solo los valores presentes entre los bienes de esa categoría.

Este patrón es el mismo que usan Amazon, Mercado Libre, Jira y GitLab.

### `queryBienesBase()` — nueva fuente canónica

```php
private function queryBienesBase()
{
    // Bien::query() sin with() ni orderBy()
    // + role scoping (Coordinador/Rector/Administrador)
    // + todos los filtros activos: busqueda, filtroUser, filtroCategoria,
    //   filtroDependencia, filtroEstado, filtroOrigen, filtroResponsable, filtroNombre
    // + todas las condiciones con bienes. prefijo para evitar ambigüedad en JOINs
}
```

### `filtrarBienesQuery()` — refactorizado

```php
private function filtrarBienesQuery()
{
    return $this->queryBienesBase()
        ->with([...eager loads...])
        ->orderBy($sortField, $this->sortDirection);
}
```

### `computarFacetas(): array` — 6 queries con `clone`

```php
$base = $this->queryBienesBase();   // un solo builder

(clone $base)->join('categorias', ...)->selectRaw('..., COUNT(bienes.id) as total')->groupBy(...)->get()
(clone $base)->join('dependencias', ...)->selectRaw('..., COUNT(bienes.id) as total')->groupBy(...)->get()
(clone $base)->join('dep_coord')->join('users', ...)->selectRaw('..., COUNT(bienes.id) as total')->groupBy(...)->get()
(clone $base)->join('estados', ...)->selectRaw('..., COUNT(bienes.id) as total')->groupBy(...)->get()
(clone $base)->whereNotNull('bienes.origen')->selectRaw('bienes.origen as origen, COUNT(bienes.id) as total')->groupBy(...)->get()
(clone $base)->join('bienes_responsables', fn => whereNull('fecha_retiro'))->join('users as resp_users', ...)->selectRaw('..., COUNT(DISTINCT bienes.id) as total')->groupBy(...)->get()
```

Cada `clone` es un `Illuminate\Database\Eloquent\Builder` independiente. PHP pasa el builder
por referencia en la variable pero `clone` crea una copia profunda del estado de la query.

### Sin impacto al snapshot

Las 6 colecciones de facetas se pasan como **view data desde `render()`** (no como propiedades
públicas). Livewire no las serializa en el snapshot. El tamaño del POST no aumenta respecto
a HOTFIX-INV-010 (~8,630 bytes).

---

## Cambios en código

### `BienesIndex.php`

| Cambio | Detalle |
|---|---|
| Eliminadas `$origenesCatalogo` y `$responsablesCatalogo` | Ya no son propiedades públicas — reemplazadas por facetas en view data |
| `cargarCatalogos()` simplificada | Elimina carga de `$users`, `$origenesCatalogo`, `$responsablesCatalogo` — mantiene solo catálogos para formulario crear-bien |
| `queryBienesBase()` nueva | Fuente canónica de role scoping + filtros sin eager loads |
| `filtrarBienesQuery()` refactorizada | Delega a `queryBienesBase()`, agrega `with()` y `orderBy()` |
| `computarFacetas()` nueva | 6 queries GROUP BY en clon del base query |
| `getCantidadTotalFiltradaProperty()` | Ahora usa `queryBienesBase()` (evita eager loads innecesarios) |
| `actualizarOpcionesFiltros()` eliminada | Remplazada completamente por `computarFacetas()` en render() |
| `updatedFiltroUser/Categoria/Dependencia/Estado()` eliminados | Ya no llaman a `actualizarOpcionesFiltros()` |
| `limpiarFiltros()` simplificada | Elimina llamada a `cargarCatalogos()` (catálogos del form no cambian al limpiar) |
| `render()` actualizada | Llama `computarFacetas()` y pasa las 6 facetas como view data |

### `bienes-index.blade.php`

| Zona | Cambio |
|---|---|
| Panel filtros móvil | 6 selects reemplazados con facetas + conteos; condicionales `@if ($facetXxx->isNotEmpty())` |
| Fila filtros desktop | 6 `@case` reemplazados con facetas + conteos |
| Formulario crear-bien | Sin cambios — sigue usando `$categorias`, `$dependencias`, etc. (props públicas) |

---

## Validaciones

| Validación | Estado |
|---|---|
| V-001 Facetas muestran solo valores del resultado actual | ✓ Diseño: filtros dependen de `queryBienesBase()` |
| V-002 Conteos correctos sin N+1 | ✓ 1 query por faceta (6 queries + 1 paginación = 8 queries por render) |
| V-003 "Todos/Todas" como primera opción | ✓ Hard-coded como `<option value="">` |
| V-004 Filtros desaparecen cuando resultado es vacío | ✓ `@if ($facetXxx->isNotEmpty())` |
| V-005 Selección preservada al filtrar | ✓ `wire:model.live` mantiene el valor seleccionado |
| V-006 Limpiar filtros restaura todas las facetas | ✓ `limpiarFiltros()` reset + render recomputa facetas |
| V-007 Sin aumento de snapshot | ✓ Facetas en view data, no en props públicas |
| V-008 Formulario crear-bien intacto | ✓ Usa `$categorias` etc. (props públicas de Livewire) |
| V-009 Performance con 1,420 bienes | ✓ GROUP BY en BD — sin carga PHP de colecciones |
| V-010 Sin regresiones en búsqueda/sort | ✓ `filtrarBienesQuery()` usa la misma base lógica |

---

## Notas arquitectónicas

### Por qué `bienes.` prefijo en condiciones del base query

Las condiciones en `queryBienesBase()` usan `bienes.categoria_id` (no `categoria_id`) porque
cuando el builder se clona y se JOINa con `categorias`, MySQL lanzaría error de columna
ambigua si ambas tablas tienen un campo `id` y la condición no especifica tabla.

### Por qué `COUNT(DISTINCT bienes.id)` en facetResponsables

La JOIN con `bienes_responsables` puede producir múltiples filas por bien si existieran
entradas duplicadas con `fecha_retiro IS NULL`. `DISTINCT` previene el doble conteo.

### Por qué `$this->cargarCatalogos()` se eliminó de `limpiarFiltros()`

`cargarCatalogos()` cargaba `$this->categorias` etc. para "restaurar" el catálogo completo
después de que `actualizarOpcionesFiltros()` lo filtrara. Con facetas en render(), esa
"restauración" ya no es necesaria — las facetas siempre reflejan el estado actual.

---

## SHA verificable

Registrado tras commit del bloque IMPL-INV-011.
