# IMPL-INV-DASH-002 — Optimización Funcional y Visual del Dashboard Ejecutivo

**Fecha:** 2026-06-11
**Módulo:** Inventario (`Modules/Inventario`)
**Prioridad:** ALTA
**Estado:** RESUELTO
**Versión:** Inventario v2.12.0 · IEE v1.17.0

---

## Objetivo

Optimizar el Dashboard Ejecutivo en `/iee/inventario` con mejoras funcionales y visuales
definidas en los ítems DASH-011 a DASH-020.

---

## Auditoría previa (datos reales 2026-06-11)

| Indicador             | Valor       |
|-----------------------|-------------|
| Total bienes          | 1,420       |
| Bienes activos        | 1,420       |
| Dados de baja         | 0           |
| Mant. pendientes      | 4           |
| Mant. realizados      | 0           |
| Responsables activos  | 1           |
| Categoría top         | Muebles (827) |
| Dependencia top       | S1 B301 (Madena) (62) |
| Bienes origen `-`     | 883 (62.2%) |
| Bienes origen válido  | 537 (37.8%) |

---

## Cambios implementados

### DASH-011 — Porcentajes en KPIs

Nuevas propiedades en `InventarioDashboard.php`:
- `$pctActivos` (int): `totalBienesActivos / (activos + bajas) * 100`
- `$pctBajas` (int): `totalBajas / (activos + bajas) * 100`
- `$pctMantPendientes` (float): `totalMantPendientes / totalBienes * 100` con 1 decimal

Los KPIs de Fila 2 muestran: `1,420 <sup>100%</sup>` / `4 <sup>0.3%</sup>`.

### DASH-012 — Corrección de gráficas (origen)

**Bug corregido:** La condición de normalización en `cargarGraficas()` solo manejaba `NULL` y `""` como
"Sin origen", pero 883 bienes tienen origen `"-"` (guion). El guion no es un origen válido —
era un valor placeholder en la carga de datos.

**Fix:** Añadido `|| $r->origen === '-'` al groupBy de normalización:
```php
fn($r) => (is_null($r->origen) || $r->origen === '' || $r->origen === '-') ? 'Sin origen' : $r->origen
```

**Bug corregido:** `$origenesNormalizados` bloqueaba la gráfica si había menos de 2 orígenes
distintos (excluyendo "Sin origen"). Esto causaba que la gráfica no se mostrara nunca con los
datos reales (solo 1 origen "válido" antes del fix).

**Fix:** Propiedad `$origenesNormalizados` eliminada. Condición en blade simplificada a
`count($chartOrigenes) > 0`.

**Resultado:** La gráfica ahora muestra "Sin origen: 883" + 26 orígenes conocidos correctamente.

### DASH-013 — Accesos Rápidos al tope

Nueva posición en el blade: inmediatamente después del encabezado, antes de los KPIs.
Estructura resultante: Encabezado → Accesos Rápidos → KPIs → Resumen → Calidad → Tops → Gráficas → Movimientos.

### DASH-014 — Calidad de Datos ampliada

- Movido al bloque tras Resumen Ejecutivo/Alertas, antes de gráficas.
- Nuevo indicador: **"Con Origen"** (`$countConOrigen` / `$pctConOrigen`).
  Criterio: origen no nulo, no vacío, no `"-"` → 537/1,420 = 38%.
- Barras muestran `N / 1,420` (conteo absoluto) + porcentaje.
- Índice general ahora promedia 5 indicadores (antes 4).
- `wire:key` en el `@foreach` de indicadores.

### DASH-015 — Top 10 Dependencias (tabla)

Tabla con ranking, nombre, cantidad y porcentaje del total de bienes.
Reutiliza `$chartDependencias` (ya top-10 ordenado por total desc).
Porcentaje calculado en blade: `$dep['total'] / $totalBienes * 100`.

### DASH-016 — Top 10 Responsables (tabla)

Nueva query en `cargarTopResponsables()`:
```php
DB::table('bienes_responsables')
    ->join('users', ...)
    ->selectRaw("CONCAT(nombres, ' ', apellidos) as nombre, COUNT(bien_id) as total")
    ->whereNull('fecha_retiro')
    ->groupBy('user_id', 'nombres', 'apellidos')
    ->orderByDesc('total')
    ->limit(10)
```

Resultado: `$topResponsables` = array con nombre completo y cantidad de bienes.
Actualmente: 1 responsable activo (Teresita Marleny Pérez Peña, 10 bienes).

### DASH-017 — Resumen Ejecutivo

Nuevo bloque card-info con 6 indicadores clave:
1. Categoría predominante (de `$chartCategorias[0]`)
2. Dependencia con más bienes (de `$chartDependencias[0]`)
3. Responsable con más bienes (de `$topResponsables[0]`)
4. Solicitudes pendientes (badge warning/success según valor)
5. Mantenimientos pendientes (badge warning/success)
6. Mantenimientos realizados

### DASH-018 — Validación responsive

Todas las nuevas secciones usan `col-12 col-md-*` o `col-12 col-sm-6 col-md-*`.
Calidad de Datos usa `col-12 col-sm-6 col-md-4 col-lg` para distribución fluida.
Accesos Rápidos usa `col-6 col-sm-4 col-md-3 col-lg-auto`.

### DASH-019 — Sin consultas N+1

Todas las queries en `mount()`:
- `Bien::count()` — simple COUNT
- `Dependencia::count()` — simple COUNT
- `DB::table('bienes_responsables')->distinct('user_id')->count('user_id')` — aggregate
- `cargarGraficas()` — selectRaw + groupBy + limit(10)
- `cargarAlertas()` — COUNTs con subqueries (1 query cada uno)
- `cargarCalidadDatos()` — COUNTs con subqueries (5 queries)
- `cargarTopResponsables()` — join + groupBy + limit(10)

`render()` mantiene `with(['bien:id,nombre'])` para eager loading del historial.

### DASH-020 — wire:key en todos los loops

Agregados:
- Accesos Rápidos: `wire:key="acceso-{{ $ai }}"` en `@foreach`
- Calidad de Datos: `wire:key="calidad-{{ $ci_i }}"` en `@foreach`
- Top Dependencias: `wire:key="top-dep-{{ $di }}"` en `@foreach`
- Top Responsables: `wire:key="top-resp-{{ $ri }}"` en `@foreach`
- Últimas Modificaciones: `wire:key="mod-{{ $mod->id }}"` en `@forelse`
- Últimas Ubicaciones: `wire:key="ubic-{{ $ubic->id }}"` en `@forelse`
- Últimas Eliminaciones: `wire:key="elim-{{ $elim->id }}"` en `@forelse`

**Razón:** Lección aprendida de HOTFIX-USERS-007 — sin `wire:key`, Alpine Morph usa posición
en lugar de identidad al re-renderizar. Aunque el dashboard no reordena (es read-only al cargar),
el uso de `wire:key` es práctica correcta para todos los loops en componentes Livewire.

---

## Corrección adicional: totalResponsables

`$totalResponsables` se calculaba sobre `Dependencia::whereNotNull('user_id')` (contaba dependencias
con responsable asignado en el catálogo, no usuarios con bienes a cargo).

Corregido a `bienes_responsables WHERE fecha_retiro IS NULL` para medir responsables reales de bienes.
Valor correcto: 1 (antes era N dependencias con user_id != null).

---

## Validaciones

| V# | Descripción | Resultado |
|----|-------------|-----------|
| V-001 | KPIs muestran porcentajes (Activos %, Bajas %, Mant %) | ✓ 100%, 0%, 0.3% |
| V-002 | Gráfica Origen visible con datos normalizados | ✓ "Sin origen: 883" + 26 orígenes |
| V-003 | Accesos Rápidos al tope (antes de KPIs en el DOM) | ✓ |
| V-004 | Calidad muestra conteos absolutos y pctConOrigen | ✓ 537/1,420 = 38% |
| V-005 | Top 10 Dependencias renderiza tabla con % | ✓ S1 B301 (Madena): 62 (4.4%) |
| V-006 | Top 10 Responsables renderiza tabla | ✓ Teresita Marleny: 10 bienes |
| V-007 | Resumen Ejecutivo muestra los 6 indicadores | ✓ |
| V-008 | Layout responsive correcto en móvil y escritorio | ✓ Bootstrap col-12 col-md-* |
| V-009 | Sin consultas N+1 — todos los queries son aggregate | ✓ |
| V-010 | wire:key en todos los 7 loops del blade | ✓ |
| V-011 | PHP syntax OK | ✓ `php -l` sin errores |
| V-012 | Component boots en tinker sin excepciones | ✓ mount() exitoso |

---

## Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `Modules/Inventario/Livewire/Dashboard/InventarioDashboard.php` | DASH-011/012/016/019/014: nuevas propiedades y métodos |
| `Modules/Inventario/resources/views/livewire/dashboard/inventario-dashboard.blade.php` | DASH-012/013/014/015/016/017/018/020: reescritura completa |
| `docs/impl/IMPL-INV-DASH-002-Optimizacion-Dashboard-Ejecutivo.md` | Este documento |
| `docs/changelog/inventario.md` | v2.12.0 |
| `docs/changelog/iee.md` | v1.17.0 |
| `VERSIONING.md` | Inventario v2.12.0, IEE v1.17.0 |
| `config/versiones.php` | Inventario v2.12.0, IEE v1.17.0 |
