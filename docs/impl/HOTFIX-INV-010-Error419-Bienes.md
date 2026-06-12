# HOTFIX-INV-010 — Error 419 en Búsqueda Reactiva de Bienes

**Fecha:** 2026-06-12
**Módulo:** Inventario — BienesIndex
**Severidad:** CRÍTICA
**Estado:** RESUELTO
**SHA commit:** (ver al final)

---

## Síntoma

Tras IMPL-INV-009 (SHA 90b0efc), la ruta `/iee/inventario/bienes` presentaba
`This page has expired / 419` al usar búsqueda reactiva, filtros u ordenamiento.

---

## Causa raíz

Idéntica a HOTFIX-USERS-006: el cuerpo POST del request Livewire supera **16,383 bytes**,
el límite efectivo del `upload_tmp_dir` del pool PHP-FPM (`/home/adolfo/tmp` pertenece a
`root:root`, el worker corre como `adolfo` y no puede crear archivos temporales allí).
Cuando el POST supera ese umbral, PHP descarta todo el cuerpo, el token CSRF queda
ilegible y Laravel retorna HTTP 419.

---

## Diagnóstico cuantitativo

### Hijos Livewire por bien (con permiso `editar-bienes`)

| Zona | Componentes por bien |
|---|---|
| Desktop (tabla, columnas dinámicas) | 13 (`editar-campo-bien` × 12 + `editar-detalle-bien` × 1) |
| Móvil (cuerpo del acordeón) | 12 (`editar-campo-bien` × 11 + `editar-detalle-bien` × 1) |
| **Total por bien** | **25** |

### Estimación del cuerpo POST (antes del fix)

| Fuente | Bytes |
|---|---|
| 250 hijos (25 × 10 bienes) × 51 bytes | 12,750 |
| `$listaNombresBienes` en snapshot (260 nombres únicos) | 3,397 |
| Otras props reactivas | ~2,000 |
| **Total estimado** | **~18,147** |
| **Límite PHP-FPM** | **16,383** |
| **Exceso** | **~1,764 bytes** |

El umbral exacto:
```
POST body ≤ 16,383 bytes → PHP lee completo → CSRF OK → HTTP 200 ✓
POST body ≥ 16,384 bytes → PHP descarta todo → CSRF null → HTTP 419 ✗
```

### Raíz de cada problema

**Problema 1 — Hijos Livewire en móvil:**
El cuerpo del acordeón móvil renderizaba los mismos `editar-campo-bien` y
`editar-detalle-bien` que la tabla de escritorio. Aunque solo un layout es
visible por CSS, Livewire registra TODOS los hijos en el snapshot del padre
independientemente de la visibilidad. Patrón idéntico al resuelto en HOTFIX-USERS-006.

**Problema 2 — `$listaNombresBienes` como propiedad pública:**
Era un array de 260 strings (nombres únicos de bienes) almacenado como `public $listaNombresBienes = []`.
Al ser pública, Livewire la serializa completa en el snapshot en cada request.
Con 260 nombres promedio de 13 chars = 3,397 bytes de JSON que se incluían
innecesariamente en cada POST, aunque el formulario de creación estuviera oculto.

---

## Corrección aplicada

### Cambio 1 — `BienesIndex.php`

**Eliminadas `$listaNombresBienes` y `$listaOrigenesBienes` del snapshot:**

Antes:
```php
public $listaNombresBienes = [];
public $listaOrigenesBienes = [];
// cargadas en mount() siempre
```

Después:
```php
// Declaraciones eliminadas (no son propiedades públicas)
// Computadas en render() solo cuando $mostrarFormulario === true:
$listaNombresBienes = [];
$listaOrigenesBienes = [];
if ($this->mostrarFormulario) {
    $listaNombresBienes = Bien::pluck('nombre')->...->toArray();
    $listaOrigenesBienes = Bien::pluck('origen')->...->toArray();
}
```

Los datos se pasan como view data desde `render()`, nunca van al snapshot.
Cuando el formulario está oculto (estado por defecto), ni siquiera se consultan.

### Cambio 2 — `bienes-index.blade.php`

**Cuerpo del acordeón móvil — HTML estático:**

Antes: 12 instancias `@livewire('bienes.editar-campo-bien/editar-detalle-bien', ...)` por bien.

Después: HTML estático con `$bien->getDisplayValue($key)` y `$bien->detalle->$attr`.
Las modificaciones pendientes se muestran con indicador visual (ícono hourglass).
La edición inline se preserva en la tabla de **escritorio** (sin cambios).

---

## Resultado

### Estimación POST después del fix

| Fuente | Bytes |
|---|---|
| 130 hijos (13 desktop × 10 bienes) × 51 bytes | 6,630 |
| `$listaNombresBienes` en snapshot | 0 (eliminado) |
| Otras props reactivas | ~2,000 |
| **Total estimado** | **~8,630** |
| **Margen bajo el límite** | **7,753 bytes (47%)** |

### Funcionalidad preservada

| Validación | Estado |
|---|---|
| V-001 Búsqueda reactiva funciona | ✓ |
| V-002 Filtros reactivos funcionan | ✓ |
| V-003 Ordenamiento funciona | ✓ |
| V-004 Sin 419 | ✓ |
| V-005 Sin registros omitidos | ✓ |
| V-006 Sin duplicados | ✓ |
| V-007 Sin errores Livewire | ✓ |
| V-008 Sin regresiones funcionales (edición desktop intacta) | ✓ |

### Lección aprendida

Dos tipos de datos nunca deben ir como `public $prop` en componentes Livewire con
paginación si se quiere evitar el 419:

1. **Arrays de strings grandes** (catálogos de nombres, listas de selección) — computar
   en `render()` como view data, especialmente si solo son necesarios condicionalmente.

2. **Componentes Livewire hijos duplicados desktop/móvil** — el layout CSS no reduce
   el tamaño del snapshot. Solo un conjunto debe usar hijos Livewire reactivos; el otro
   usa HTML estático.

---

## wire:key — Auditoría

| Elemento | wire:key | Estado |
|---|---|---|
| `<tr>` escritorio | `bien-row-{id}` | ✓ Presente (desde IMPL-INV-009) |
| `<div class="card">` móvil | `bien-card-{id}` | ✓ Presente (desde IMPL-INV-009) |
| Subcomponentes desktop | `bien-{id}-{campo}` | ✓ Presentes |
| `editar-detalle-bien` desktop | `editar-detalle-bien-escritorio-{id}` | ✓ Presente |
| Modal `editar-detalle-bien-modal` | (singleton) | ✓ Sin loop, no requiere wire:key |

---

## SHA verificable

```
(ver git log --oneline -1)
```
