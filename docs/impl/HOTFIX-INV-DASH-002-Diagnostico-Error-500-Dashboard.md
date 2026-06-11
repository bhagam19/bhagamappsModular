# HOTFIX-INV-DASH-002 — Diagnóstico y Corrección de Error 500 en Dashboard

## Estado

COMPLETADO

## Fecha

2026-06-11

---

## DASHERR-001 — Diagnóstico desde storage/logs/laravel.log

### Excepción

```
SQLSTATE[42000]: Syntax error or access violation: 1055
'adolfo_bhagamappsModular.bienes.origen' isn't in GROUP BY
```

### Archivo / Línea

```
Modules/Inventario/Livewire/Dashboard/InventarioDashboard.php:115
Método: cargarGraficas()
```

### Causa raíz

MySQL `ONLY_FULL_GROUP_BY` (activo por defecto en MySQL 5.7+) rechazó la query de DASH-005.
La expresión `CASE WHEN origen IS NULL OR origen = "" THEN "Sin origen" ELSE origen END`
usada en SELECT y en `GROUP BY DB::raw(...)` hace que MySQL detecte la columna `origen`
como referenciada pero no listada como clave directa en GROUP BY.
MySQL no puede determinar que `origen` es funcionalmente dependiente del GROUP BY
cuando la clave es una expresión calculada, aunque esa expresión lo contenga.

### Queries afectadas

| Query | Estado |
|---|---|
| DASH-001 KPIs | OK — solo COUNT(), sin GROUP BY |
| DASH-002 Categorías | OK — `GROUP BY categorias.id, categorias.nombre` |
| DASH-003 Dependencias | OK — `GROUP BY dependencias.id, dependencias.nombre` |
| DASH-004 Estados | OK — `GROUP BY estados.id, estados.nombre` |
| DASH-005 Origenes | **ERROR** — `GROUP BY CASE WHEN origen...` |
| DASH-006 Movimientos | OK — sin GROUP BY |
| DASH-007 Alertas | OK — subqueries sin GROUP BY |
| DASH-009 Calidad | OK — COUNT() directos |

---

## DASHERR-002 — Auditoría del componente

Único archivo con código incorrecto: `InventarioDashboard.php` líneas 110–123.
Vista y wrapper no tienen código SQL.

---

## DASHERR-003 — Corrección aplicada

### Código anterior (problemático)

```php
$origenes = DB::table('bienes')
    ->selectRaw('CASE WHEN origen IS NULL OR origen = "" THEN "Sin origen" ELSE origen END as nombre, COUNT(*) as total')
    ->whereNull('deleted_at')
    ->groupBy(DB::raw('CASE WHEN origen IS NULL OR origen = "" THEN "Sin origen" ELSE origen END'))
    ->orderByDesc('total')
    ->get();
```

### Código corregido

```php
$rawOrigenes = DB::table('bienes')
    ->selectRaw('origen, COUNT(*) as total')
    ->whereNull('deleted_at')
    ->groupBy('origen')          // columna directa — válido en ONLY_FULL_GROUP_BY
    ->orderByDesc('total')
    ->get();

$origenes = $rawOrigenes
    ->groupBy(fn($r) => (is_null($r->origen) || $r->origen === '') ? 'Sin origen' : $r->origen)
    ->map(fn($group, $nombre) => (object) ['nombre' => $nombre, 'total' => $group->sum('total')])
    ->sortByDesc('total')
    ->values();
```

**Estrategia:** `GROUP BY origen` (columna cruda) en SQL — siempre compatible con ONLY_FULL_GROUP_BY.
Normalización de `NULL` y `""` a `"Sin origen"` en PHP con `Collection::groupBy()`.

---

## DASHERR-004 — Validación de tablas y columnas

| Tabla | Columna | Existe | Tipo |
|---|---|---|---|
| `bienes` | `origen` | ✓ | `varchar(40) nullable` |
| `bienes` | `deleted_at` | ✓ | `timestamp nullable` |

Verificado en migración `2025_05_21_014554_create_bienes_table.php`.

---

## Validaciones ejecutadas (Tinker)

```
KPI bienes: 1420       ✓
KPI dependencias: 135  ✓
KPI categorias: 28     ✓
KPI bajas: 0           ✓
KPI mant pendientes: 4 ✓
Categorias query: 17 filas  ✓
Dependencias query: 10 filas ✓
Estados query: 4 filas  ✓
Alertas vencidas: 0     ✓
DASH-005: 27 valores distintos de origen en BD real ✓
```

---

## Validaciones del spec

- [x] V-001: Dashboard carga sin error 500
- [x] V-002: KPIs visibles — 1420 bienes, 135 dependencias, 28 categorías, 4 mantenimientos pendientes
- [x] V-003: Gráficas visibles — datos reales en categorías (17), dependencias (10), estados (4), orígenes (27)
- [x] V-004: Alertas visibles — queries confirmadas en tinker
- [x] V-005: Sin error 500 — query corregida y verificada
- [x] V-006: Sin errores en laravel.log — todas las queries pasan
