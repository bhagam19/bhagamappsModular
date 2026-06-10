# IMPL-INV-005 — Historial de Ubicaciones de Bienes

**Estado:** CERRADO  
**Fecha:** 2026-06-10  
**Versión:** Inventario v2.9.0 | BhagamApps v1.10.0  
**Rama:** `main`

---

## Objetivo

Implementar el ciclo completo de gestión y trazabilidad de ubicaciones de bienes:
Bien → Ubicación actual → Cambio de ubicación → Historial de movimientos → Consulta histórica.

---

## Decisión Arquitectónica

La ubicación actual de un bien **no** se almacena como columna directa en `bienes` (la columna
`bienes.ubicacion_id` fue eliminada en IMPL-INV-004 por ser inconsistente). En su lugar, la
ubicación actual se deriva del registro más reciente en `historial_ubicaciones_bienes`, usando la
relación `ubicacionActual()` (`hasOne → latest('fecha_movimiento')`). Este patrón es idéntico al
usado para `responsableActual()` en `BienResponsable`.

---

## Infraestructura Creada

### Migraciones

| Archivo | Descripción |
|---------|-------------|
| `2026_06_10_000001_create_historial_ubicaciones_bienes_table.php` | Tabla principal de historial |
| `2026_06_10_000002_add_ubicaciones_historial_permissions.php` | 2 permisos RBAC + asignación a roles |

### Tabla `historial_ubicaciones_bienes`

```
id                    bigint PK
bien_id               FK → bienes (cascade)
ubicacion_origen_id   FK → ubicaciones (nullable, nullOnDelete)
ubicacion_destino_id  FK → ubicaciones (cascade)
user_id               FK → users (nullable, nullOnDelete)
fecha_movimiento      timestamp (default current)
observaciones         varchar(500) nullable
created_at / updated_at
```

### Entidades

- `Modules/Inventario/Entities/HistorialUbicacionBien.php`

### Relaciones añadidas

**`Bien`:**
```php
ubicacionActual()     → hasOne(HistorialUbicacionBien)->latest('fecha_movimiento')
historialUbicaciones() → hasMany(HistorialUbicacionBien)
```

**`Ubicacion`:**
```php
movimientos() → hasMany(HistorialUbicacionBien, 'ubicacion_destino_id')
```

---

## Componentes Livewire

| Clase | Alias | Descripción |
|-------|-------|-------------|
| `Livewire/Ubicaciones/HistorialUbicacionesBien` | `ubicaciones.historial-ubicaciones-bien` | Página principal: lista bienes + ubicación actual + cambiar + historial inline |
| `Livewire/Ubicaciones/CambiarUbicacionBien` | `ubicaciones.cambiar-ubicacion-bien` | Componente standalone para cambio de ubicación |

Registro: automático via `InventarioServiceProvider` (escaneo `File::allFiles`).

---

## Rutas

```
GET /inventario/ubicaciones/historial
    → UbicacionesHistorialController::index()
    → name: inventario.ubicaciones.historial
    → middleware: permission:ver-historial-ubicaciones-bienes
```

---

## Permisos RBAC

| Slug | Roles |
|------|-------|
| `ver-historial-ubicaciones-bienes` | Administrador, Rector, Coordinador |
| `cambiar-ubicacion-bienes` | Administrador, Rector |

Gates registrados en `AuthServiceProvider` (IMPL-INV-005 block).

---

## Integración BienesIndex (RF-001)

- Columna `'ubicacion_actual' => 'Ubicación Actual'` añadida a `availableColumns` (oculta por defecto).
- Eager load `'ubicacionActual.ubicacionDestino'` añadido a `filtrarBienesQuery()`.
- Vista maneja `$column === 'ubicacion_actual'` en tabla desktop y móvil.
- **Sin N+1 queries**: eager loading de Eloquent (2 queries adicionales totales).

---

## Reglas de Integridad Implementadas

| Regla | Implementación |
|-------|----------------|
| RI-001: registrar origen, destino, fecha, usuario | Campos obligatorios en `HistorialUbicacionBien::create()` |
| RI-002: no mover a ubicación inexistente | `'nuevaUbicacionId' => 'required|exists:ubicaciones,id'` |
| RI-003: consistencia con ubicación actual | Origen derivado de `bien->ubicacionActual?->ubicacion_destino_id` |

---

## Validaciones

| ID | Validación | Estado |
|----|-----------|--------|
| V-001 | Cambio de ubicación correcto | ✅ Formulario con validación Livewire + FK |
| V-002 | Historial registrado correctamente | ✅ `HistorialUbicacionBien::create()` con todos los campos |
| V-003 | Consulta histórica correcta | ✅ `toggleHistorial()` + eager load with/user/destino/origen |
| V-004 | Permisos funcionando | ✅ `abort_unless` + `@can` en vistas |
| V-005 | Sin N+1 queries | ✅ `with('ubicacionActual.ubicacionDestino')` en todas las queries |
| V-006 | Sin regresiones en BienesIndex | ✅ Columna nueva oculta por defecto; eager load no rompe nada existente |
| V-007 | Sidebar funcionando | ✅ Entrada "Historial Ubicaciones" + `can: 'ver-historial-ubicaciones-bienes'` |

---

## Archivos Creados

```
Modules/Inventario/Database/Migrations/2026_06_10_000001_create_historial_ubicaciones_bienes_table.php
Modules/Inventario/Database/Migrations/2026_06_10_000002_add_ubicaciones_historial_permissions.php
Modules/Inventario/Entities/HistorialUbicacionBien.php
Modules/Inventario/Livewire/Ubicaciones/HistorialUbicacionesBien.php
Modules/Inventario/Livewire/Ubicaciones/CambiarUbicacionBien.php
Modules/Inventario/Http/Controllers/UbicacionesHistorialController.php
Modules/Inventario/resources/views/ubicaciones/historial.blade.php
Modules/Inventario/resources/views/livewire/ubicaciones/historial-ubicaciones-bien.blade.php
Modules/Inventario/resources/views/livewire/ubicaciones/cambiar-ubicacion-bien.blade.php
docs/impl/IMPL-INV-005-Historial-de-Ubicaciones.md
```

## Archivos Modificados

```
Modules/Inventario/Entities/Bien.php                   — ubicacionActual(), historialUbicaciones()
Modules/Inventario/Entities/Ubicacion.php               — movimientos()
Modules/Inventario/Livewire/Bienes/BienesIndex.php      — availableColumns + with()
Modules/Inventario/resources/views/livewire/bienes/bienes-index.blade.php — ubicacion_actual column
Modules/Inventario/routes/web.php                       — ruta historial ubicaciones
app/Providers/AuthServiceProvider.php                   — Gates IMPL-INV-005
config/adminlte.php                                     — sidebar entry
config/versiones.php                                    — v2.9.0 / v1.10.0
CHANGELOG.md                                            — v1.10.0
VERSIONING.md                                           — versiones actualizadas
docs/changelog/inventario.md                            — v2.9.0
docs/changelog/bhagamapps.md                            — v1.10.0
```
