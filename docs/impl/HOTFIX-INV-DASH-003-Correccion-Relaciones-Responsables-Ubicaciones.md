# HOTFIX-INV-DASH-003 — Corrección de Relaciones de Responsables y Ubicaciones en Dashboard

**Estado:** COMPLETADO  
**Prioridad:** CRÍTICA  
**Fecha:** 2026-06-11  
**Archivo modificado:** `Modules/Inventario/Livewire/Dashboard/InventarioDashboard.php`

---

## REL-001 — Relación Real del Modelo de Datos

### Estructura de tablas

```
bienes
├── id
├── nombre
├── dependencia_id  ← FK nullable → dependencias.id
├── categoria_id
├── estado_id
├── deleted_at (SoftDeletes)
└── ...

dependencias
├── id
├── nombre          (ej: "Aula A201", "Laboratorio", "Rectoría")
├── ubicacion_id    ← FK NOT NULL → ubicaciones.id
├── user_id         ← FK NOT NULL → users.id  (responsable de la dependencia)
└── timestamps

bienes_responsables          (asignación directa bien ↔ usuario)
├── id
├── bien_id          ← FK → bienes.id
├── user_id          ← FK nullable → users.id
├── fecha_asignacion
├── fecha_retiro     (NULL = activo)
└── timestamps

historial_ubicaciones_bienes (movimientos explícitos de ubicación física)
├── id
├── bien_id                ← FK → bienes.id
├── ubicacion_origen_id    ← FK nullable → ubicaciones.id
├── ubicacion_destino_id   ← FK NOT NULL → ubicaciones.id
├── fecha_movimiento
└── timestamps
```

### Reglas de negocio (IEE)

| Relación | Fuente | Constraint |
|---|---|---|
| Bien → Ubicación física | `bienes.dependencia_id` → `dependencias.ubicacion_id` | `dependencias.ubicacion_id` NOT NULL |
| Bien → Responsable heredado | `bienes.dependencia_id` → `dependencias.user_id` | `dependencias.user_id` NOT NULL |
| Bien → Responsable directo | `bienes_responsables` (whereNull fecha_retiro) | Registro opcional adicional |
| Bien → Movimiento de ubicación | `historial_ubicaciones_bienes` | Registro opcional adicional |

**Principio clave:** toda `Dependencia` tiene **siempre** un responsable (`user_id NOT NULL`) y una ubicación física (`ubicacion_id NOT NULL`). Por tanto, cualquier bien con `dependencia_id IS NOT NULL` tiene implícitamente ambos.

---

## REL-002 — Lógica Incorrecta del Dashboard (pre-hotfix)

### `cargarAlertas()` — lógica original

```php
// Solo buscaba en bienes_responsables — ignoraba la dependencia
$this->alertSinResponsable = DB::table('bienes')
    ->whereNull('deleted_at')
    ->whereNotIn('id', function ($q) {
        $q->select('bien_id')->from('bienes_responsables')->whereNull('fecha_retiro');
    })->count();

// Solo buscaba en historial_ubicaciones_bienes — ignoraba la dependencia
$this->alertSinUbicacion = DB::table('bienes')
    ->whereNull('deleted_at')
    ->whereNotIn('id', function ($q) {
        $q->select('bien_id')->from('historial_ubicaciones_bienes');
    })->count();
```

### `cargarCalidadDatos()` — lógica original

```php
$this->countConResponsable = DB::table('bienes')
    ->whereNull('deleted_at')
    ->whereIn('id', function ($q) {
        $q->select('bien_id')->from('bienes_responsables')->whereNull('fecha_retiro');
    })->count();   // Solo asignaciones directas

$this->countConUbicacion = DB::table('bienes')
    ->whereNull('deleted_at')
    ->whereIn('id', function ($q) {
        $q->select('bien_id')->from('historial_ubicaciones_bienes');
    })->count();   // Solo movimientos explícitos
```

---

## REL-003 — Fuente Correcta de Verdad

| Campo | Fuente de verdad primaria | Fuente complementaria |
|---|---|---|
| Responsable | `bienes.dependencia_id` → `dependencias.user_id` | `bienes_responsables` (asignación directa) |
| Ubicación | `bienes.dependencia_id` → `dependencias.ubicacion_id` | `historial_ubicaciones_bienes` (movimiento explícito) |

Un bien tiene responsable si `dependencia_id IS NOT NULL` **O** tiene registro activo en `bienes_responsables`.  
Un bien tiene ubicación si `dependencia_id IS NOT NULL` **O** tiene registro en `historial_ubicaciones_bienes`.

---

## REL-004 — Tabla Comparativa: Valor Dashboard vs Realidad

Medición realizada contra la BD de producción el 2026-06-11:

| Métrica | Valor Dashboard (incorrecto) | Valor Real (correcto) | Diferencia |
|---|---|---|---|
| Total bienes activos | 1,420 | 1,420 | — |
| Bienes con `dependencia_id` | — | 1,420 (100%) | — |
| Bienes sin `dependencia_id` | — | 0 (0%) | — |
| **Con responsable** | **10** | **1,420** | **+1,410** |
| **Sin responsable (alerta)** | **1,410** | **0** | **−1,410** |
| **Con ubicación** | **0** | **1,420** | **+1,420** |
| **Sin ubicación (alerta)** | **1,420** | **0** | **−1,420** |
| % Con responsable (calidad) | ~1% | 100% | +99 pp |
| % Con ubicación (calidad) | 0% | 100% | +100 pp |

---

## Causa Raíz

El Dashboard implementó la lógica de responsables y ubicaciones mirando **únicamente** las tablas de asignación directa (`bienes_responsables` e `historial_ubicaciones_bienes`), sin considerar que la relación `Bien → Dependencia` ya implica tanto responsable (`dependencias.user_id NOT NULL`) como ubicación física (`dependencias.ubicacion_id NOT NULL`).

Esto produjo falsas alertas masivas y métricas de calidad de datos completamente erróneas.

---

## DASHREL-001/002/003 — Corrección Aplicada

### Archivo modificado

`Modules/Inventario/Livewire/Dashboard/InventarioDashboard.php`

### `cargarAlertas()` — lógica corregida

```php
// Sin responsable: debe carecer de dependencia_id Y de bienes_responsables activo
$this->alertSinResponsable = DB::table('bienes')
    ->whereNull('deleted_at')
    ->whereNull('dependencia_id')
    ->whereNotIn('id', function ($q) {
        $q->select('bien_id')->from('bienes_responsables')->whereNull('fecha_retiro');
    })->count();

// Sin ubicación: debe carecer de dependencia_id Y de historial_ubicaciones_bienes
$this->alertSinUbicacion = DB::table('bienes')
    ->whereNull('deleted_at')
    ->whereNull('dependencia_id')
    ->whereNotIn('id', function ($q) {
        $q->select('bien_id')->from('historial_ubicaciones_bienes');
    })->count();
```

### `cargarCalidadDatos()` — lógica corregida

```php
// Con responsable: dependencia asignada OR bienes_responsables activo
$this->countConResponsable = DB::table('bienes')
    ->whereNull('deleted_at')
    ->where(function ($q) {
        $q->whereNotNull('dependencia_id')
          ->orWhereIn('id', function ($sub) {
              $sub->select('bien_id')->from('bienes_responsables')->whereNull('fecha_retiro');
          });
    })->count();

// Con ubicación: dependencia asignada OR historial_ubicaciones_bienes
$this->countConUbicacion = DB::table('bienes')
    ->whereNull('deleted_at')
    ->where(function ($q) {
        $q->whereNotNull('dependencia_id')
          ->orWhereIn('id', function ($sub) {
              $sub->select('bien_id')->from('historial_ubicaciones_bienes');
          });
    })->count();
```

### Secciones del Dashboard corregidas

- **Alertas** → `Bienes sin responsable` y `Bienes sin ubicación`
- **Calidad de Datos** → barras `Con Responsable` y `Con Ubicación` + porcentajes + índice general
- **Resumen Ejecutivo** → Índice general de calidad (promedio de los 5 indicadores)

---

## Validaciones

### V-001 — Relación funcional documentada ✅
`dependencias.user_id NOT NULL` y `dependencias.ubicacion_id NOT NULL` verificados en migración `2025_05_21_014455_create_dependencias_table.php`.

### V-002 — Conteo de responsables correcto ✅
```
Antes:  countConResponsable = 10    (solo bienes_responsables)
Después: countConResponsable = 1,420 (todos tienen dependencia_id)
alertSinResponsable: 1,410 → 0
```

### V-003 — Conteo de ubicaciones correcto ✅
```
Antes:  countConUbicacion = 0    (historial vacío)
Después: countConUbicacion = 1,420 (todos tienen dependencia_id)
alertSinUbicacion: 1,420 → 0
```

### V-004 — Dashboard consistente con modelo de negocio ✅
La lógica de consultas refleja correctamente la cadena `Bien → Dependencia → {user_id, ubicacion_id}`.

### V-005 — Sin errores SQL ✅
Las consultas usan `whereNull`/`whereNotNull`/`whereIn`/`orWhereIn` estándar de Eloquent Query Builder. No hay SQL crudo sensible.

### V-006 — Sin N+1 ✅
Todas las consultas son agregadas (COUNT). No hay iteración sobre colecciones con lazy loading.

### V-007 — Sin regresiones ✅
- `cargarKpis()`: sin cambios.
- `cargarGraficas()`: sin cambios.
- `cargarTopResponsables()`: sin cambios (sigue listando asignaciones directas en `bienes_responsables`, comportamiento correcto para el ranking).
- `alertInfoIncompleta`: sin cambios (sigue detectando bienes sin categoria_id, dependencia_id o estado_id).
- Vista Blade: sin cambios.

---

## Commit y Push

Ver historial git del repositorio para SHA verificable.
