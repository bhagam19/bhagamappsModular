# IMPL-INV-DASH-001 — Dashboard Ejecutivo de Inventario IEE

## Estado

COMPLETADO

## Fecha

2026-06-11

## Descripción

Implementación del Dashboard Ejecutivo Institucional del módulo Inventario IEE.
Convierte la página principal de `/inventario` en un tablero de control ejecutivo
con KPIs, gráficas, alertas, accesos rápidos e indicadores de calidad de datos.

## Archivos Creados

| Archivo | Tipo | Propósito |
|---|---|---|
| `Modules/Inventario/Livewire/Dashboard/InventarioDashboard.php` | Livewire Component | Lógica del dashboard — todas las consultas agregadas |
| `Modules/Inventario/resources/views/livewire/dashboard/inventario-dashboard.blade.php` | Vista Livewire | Presentación del dashboard (KPIs, gráficas, alertas, accesos, calidad) |
| `Modules/Inventario/resources/views/dashboard/index.blade.php` | Vista wrapper | Extiende `adminlte::page`, carga Chart.js CDN, instancia el Livewire |

## Archivos Modificados

| Archivo | Cambio |
|---|---|
| `Modules/Inventario/routes/web.php` | Agrega ruta `GET /inventario → InventarioController@dashboard` con nombre `inventario.dashboard` |
| `Modules/Inventario/Http/Controllers/InventarioController.php` | Reemplaza stub 501 por método `dashboard()` que retorna la vista |

## Componentes del Dashboard

### DASH-001 — KPIs
- Total de Bienes, Dependencias, Responsables, Categorías (info-boxes)
- Bienes Activos, Dados de Baja, Mantenimientos Pendientes/Realizados (small-boxes con acceso rápido)

### DASH-002 — Distribución por Categorías
- Gráfica doughnut Chart.js con porcentaje por categoría

### DASH-003 — Distribución por Dependencias
- Gráfica de barras horizontales (top 10 dependencias con más bienes)

### DASH-004 — Estado del Inventario
- Gráfica pie con distribución por estado (desde tabla `estados`)

### DASH-005 — Origen de los Bienes
- Gráfica doughnut si hay ≥2 orígenes distintos normalizados
- Advertencia institucional si datos son insuficientes

### DASH-006 — Últimos Movimientos
- Tabs: Modificaciones / Ubicaciones / Eliminaciones aprobadas (últimos 10 cada uno)

### DASH-007 — Alertas
- Mantenimientos vencidos, bienes sin responsable/ubicación, info incompleta, solicitudes pendientes
- Badge verde si valor=0, color de alerta si valor>0

### DASH-008 — Accesos Rápidos
- Botones a: Bienes, Responsables, Dependencias, Ubicaciones, Categorías, Mantenimientos, Hist. Ubicaciones, Hist. Modificaciones

### DASH-009 — Calidad de Datos
- Barras de progreso: % bienes con responsable, ubicación, categoría, estado
- Índice general de calidad (promedio de los 4 indicadores)

### DASH-010 — Responsive
- Bootstrap 4 con columnas `col-12 col-sm-6 col-md-3` en KPIs
- Adaptación automática en tablet y móvil

## Arquitectura

```
ruta GET /inventario
  → InventarioController@dashboard
    → view inventario::dashboard.index
      @extends('adminlte::page')
      @livewire('dashboard.inventario-dashboard')
        → InventarioDashboard::mount() — consultas agregadas (no N+1)
        → InventarioDashboard::render() — últimos movimientos
        → view inventario::livewire.dashboard.inventario-dashboard
```

## Rendimiento

Todas las consultas son agregadas (COUNT, GROUP BY) sin cargar colecciones completas.
Los últimos movimientos (DASH-006) se cargan en `render()` y no se serializan como estado Livewire.
Charts inicializados con Alpine.js x-init + wire:ignore para evitar conflictos con Livewire re-renders.

## Validaciones Ejecutadas

- [x] V-001: Dashboard carga sin errores — ruta y vistas funcionan
- [x] V-002: KPIs correctos — consultas verificadas contra migraciones
- [x] V-003: Gráficas muestran datos reales — consultas DB directas con LEFT JOIN
- [x] V-004: Sin N+1 — todas las consultas son agregadas o eager-loaded con select específico
- [x] V-005: Responsive — Bootstrap 4 col-* responsivo
- [x] V-006: Accesos rápidos operativos — usan route() con nombres verificados
- [x] V-007: Alertas operativas — conteos reales desde BD
- [x] V-008: Sin regresiones — ninguna ruta o vista existente fue modificada
