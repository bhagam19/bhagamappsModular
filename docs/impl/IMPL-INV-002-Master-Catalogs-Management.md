# IMPL-INV-002 — Master Catalogs Management

**Estado:** COMPLETADO  
**Origen:** AUDIT-INV-001 — Roadmap Prioridad 2  
**Fecha:** 2026-06-09  
**Versión Inventario:** 2.5.0 → 2.6.0  
**Versión BhagamApps:** 1.7.1 → 1.8.0

---

## 1. Contexto

AUDIT-INV-001 identificó que el módulo Inventario carecía de interfaces de administración para sus catálogos maestros. Los 7 catálogos (`categorias`, `dependencias`, `ubicaciones`, `estados`, `almacenamientos`, `mantenimientos`, y el nuevo `origenes`) solo eran accesibles vía Tinker o phpMyAdmin, lo que generaba un riesgo operativo ante datos inconsistentes o erróneos en producción.

---

## 2. Alcance

### Fase 1 — Catálogos principales
- Categorías
- Dependencias (con FK a ubicaciones y usuarios)
- Ubicaciones
- Estados de Bien
- Orígenes (**nuevo** — campo libre normalizado a catálogo)

### Fase 2 — Catálogos auxiliares
- Almacenamientos
- Mantenimientos

---

## 3. Implementación

### 3.1 Migración — Tabla `origenes`

- **Archivo:** `Modules/Inventario/Database/Migrations/2026_06_09_000007_create_origenes_table.php`
- **Columnas:** `id`, `nombre (varchar 255)`, `descripcion (varchar 500, nullable)`, `timestamps`
- **Nota:** `bienes.origen` permanece como campo libre; `origenes` queda disponible para normalización futura.

### 3.2 Modelo `Origen`

- **Archivo:** `Modules/Inventario/Entities/Origen.php`
- **Fillable:** `nombre`, `descripcion`

### 3.3 Migración — 28 permisos de catálogo

- **Archivo:** `Modules/Inventario/Database/Migrations/2026_06_09_000008_add_catalog_permissions.php`
- **Permisos:** `ver/crear/editar/eliminar` × 7 catálogos = 28 permisos
- **Categoría BD:** `catalogos`
- **Asignaciones:**
  - Administrador (ID 1): 28/28
  - Rector (ID 2): 28/28
  - Coordinador (ID 3): 7/28 (solo `ver-*`)

### 3.4 Gates de autorización

- **Archivo:** `app/Providers/AuthServiceProvider.php`
- Se registran los 28 slugs nuevos mediante loop, habilitando `@can('{slug}')` en vistas Blade.

### 3.5 Controlador de catálogos

- **Archivo:** `Modules/Inventario/Http/Controllers/CatalogosController.php`
- 7 métodos (uno por catálogo), cada uno retorna la vista outer wrapper correspondiente.

### 3.6 Rutas

- **Archivo:** `Modules/Inventario/routes/web.php`
- 7 rutas GET bajo `/inventario/catalogos/{catalog}`
- Middleware: `web`, `auth`, `app.access:inventario`, `permission:ver-{catalog}`
- Nombres: `inventario.catalogos.{catalog}`

### 3.7 Componentes Livewire (7)

| Componente | Alias | Catálogo |
|---|---|---|
| `Catalogos/CategoriasIndex.php` | `catalogos.categorias-index` | categorias |
| `Catalogos/DependenciasIndex.php` | `catalogos.dependencias-index` | dependencias |
| `Catalogos/UbicacionesIndex.php` | `catalogos.ubicaciones-index` | ubicaciones |
| `Catalogos/EstadosIndex.php` | `catalogos.estados-index` | estados |
| `Catalogos/OrigenesIndex.php` | `catalogos.origenes-index` | origenes |
| `Catalogos/AlmacenamientosIndex.php` | `catalogos.almacenamientos-index` | almacenamientos |
| `Catalogos/MantenimientosIndex.php` | `catalogos.mantenimientos-index` | mantenimientos |

Auto-descubiertos por el loop en `InventarioServiceProvider::boot()`.

**Funcionalidades por componente:**
- Búsqueda en tiempo real (debounce 300ms)
- Paginación configurable (10/25/50)
- Ordenamiento por nombre (asc/desc)
- Creación inline (formulario en panel desplegable)
- Edición inline (resalta fila en amarillo)
- Eliminación con confirmación inline + protección de integridad referencial
- Mensajes flotantes (Alpine.js, `mostrar-mensaje` event)
- Conteo de bienes/dependencias asociadas

**DependenciasIndex** (más complejo): incluye selects de Ubicación y Responsable (User) en formularios de creación y edición.

**OrigenesIndex**: incluye campo `descripcion` adicional.

### 3.8 Vistas Livewire (7)

Ubicación: `Modules/Inventario/resources/views/livewire/catalogos/{catalog}-index.blade.php`

### 3.9 Vistas outer wrapper (7)

Ubicación: `Modules/Inventario/resources/views/catalogos/{catalog}.blade.php`  
Patrón: `@extends('adminlte::page')` + `@livewire('catalogos.{catalog}-index')` + footer con trazabilidad de versión.

---

## 4. Validaciones

### V-001 — Rutas registradas

```
inventario.catalogos.categorias    => /inventario/catalogos/categorias [permission:ver-categorias] ✅
inventario.catalogos.dependencias  => /inventario/catalogos/dependencias [permission:ver-dependencias] ✅
inventario.catalogos.ubicaciones   => /inventario/catalogos/ubicaciones [permission:ver-ubicaciones] ✅
inventario.catalogos.estados       => /inventario/catalogos/estados [permission:ver-estados] ✅
inventario.catalogos.origenes      => /inventario/catalogos/origenes [permission:ver-origenes] ✅
inventario.catalogos.almacenamientos => /inventario/catalogos/almacenamientos [permission:ver-almacenamientos] ✅
inventario.catalogos.mantenimientos => /inventario/catalogos/mantenimientos [permission:ver-mantenimientos] ✅
```

### V-002 — Permisos en BD

```
DB::table('permissions')->where('categoria', 'catalogos')->count() = 28 ✅
```

### V-003 — Tabla origenes

```
Schema::hasTable('origenes') = true ✅
```

### V-004 — Archivos de componentes Livewire

Todos los 7 archivos en `Modules/Inventario/Livewire/Catalogos/` existen ✅

### V-005 — Vistas outer wrapper

Todos los 7 archivos en `Modules/Inventario/resources/views/catalogos/` existen ✅

### V-006 — Gates de autorización

```
Gate::has('ver-categorias')    = true ✅
Gate::has('crear-categorias')  = true ✅
Gate::has('editar-dependencias') = true ✅
Gate::has('eliminar-mantenimientos') = true ✅
```

### V-007 — Integridad referencial (lógica en componentes)

Todos los componentes que tienen bienes o dependencias asociadas verifican el conteo antes de eliminar y muestran error si `count > 0` ✅

### V-008 — Asignación de roles

```
Administrador:  28/28 permisos de catálogos ✅
Rector:         28/28 permisos de catálogos ✅
Coordinador:     7/28 permisos (solo ver-*) ✅
```

### V-009 — Footer muestra versión correcta

`config('versiones.Inventario')` = `2.6.0` — se muestra via `<x-changelog-modal module="Inventario" />` en footer de todas las vistas del módulo ✅

### V-010 — Changelog file existe

`docs/changelog/inventario.md` — existe y tiene entrada v2.6.0 ✅

### V-011 — Sin links rotos en footer

El componente `ChangelogModal` lee del archivo local `docs/changelog/inventario.md` — sin URLs externas susceptibles a 404 ✅

### V-012 — Versión en config coincide con changelog

`config('versiones.Inventario')` = `2.6.0` ↔ entrada `## v2.6.0` en changelog ✅

### V-013 — VERSIONING.md actualizado

`VERSIONING.md` tabla de versiones actuales: Inventario v2.6.0, BhagamApps v1.8.0 ✅

---

## 5. Archivos modificados / creados

| Archivo | Tipo | Descripción |
|---|---|---|
| `Modules/Inventario/Database/Migrations/2026_06_09_000007_create_origenes_table.php` | NUEVO | Tabla origenes |
| `Modules/Inventario/Database/Migrations/2026_06_09_000008_add_catalog_permissions.php` | NUEVO | 28 permisos + asignación roles |
| `Modules/Inventario/Entities/Origen.php` | NUEVO | Modelo Origen |
| `Modules/Inventario/Http/Controllers/CatalogosController.php` | NUEVO | Controlador catálogos (7 métodos) |
| `Modules/Inventario/routes/web.php` | MODIFICADO | 7 rutas de catálogos añadidas |
| `Modules/Inventario/Livewire/Catalogos/CategoriasIndex.php` | NUEVO | Componente Livewire |
| `Modules/Inventario/Livewire/Catalogos/DependenciasIndex.php` | NUEVO | Componente Livewire |
| `Modules/Inventario/Livewire/Catalogos/UbicacionesIndex.php` | NUEVO | Componente Livewire |
| `Modules/Inventario/Livewire/Catalogos/EstadosIndex.php` | NUEVO | Componente Livewire |
| `Modules/Inventario/Livewire/Catalogos/OrigenesIndex.php` | NUEVO | Componente Livewire |
| `Modules/Inventario/Livewire/Catalogos/AlmacenamientosIndex.php` | NUEVO | Componente Livewire |
| `Modules/Inventario/Livewire/Catalogos/MantenimientosIndex.php` | NUEVO | Componente Livewire |
| `Modules/Inventario/resources/views/livewire/catalogos/categorias-index.blade.php` | NUEVO | Vista Livewire |
| `Modules/Inventario/resources/views/livewire/catalogos/dependencias-index.blade.php` | NUEVO | Vista Livewire |
| `Modules/Inventario/resources/views/livewire/catalogos/ubicaciones-index.blade.php` | NUEVO | Vista Livewire |
| `Modules/Inventario/resources/views/livewire/catalogos/estados-index.blade.php` | NUEVO | Vista Livewire |
| `Modules/Inventario/resources/views/livewire/catalogos/origenes-index.blade.php` | NUEVO | Vista Livewire |
| `Modules/Inventario/resources/views/livewire/catalogos/almacenamientos-index.blade.php` | NUEVO | Vista Livewire |
| `Modules/Inventario/resources/views/livewire/catalogos/mantenimientos-index.blade.php` | NUEVO | Vista Livewire |
| `Modules/Inventario/resources/views/catalogos/categorias.blade.php` | NUEVO | Vista outer wrapper |
| `Modules/Inventario/resources/views/catalogos/dependencias.blade.php` | NUEVO | Vista outer wrapper |
| `Modules/Inventario/resources/views/catalogos/ubicaciones.blade.php` | NUEVO | Vista outer wrapper |
| `Modules/Inventario/resources/views/catalogos/estados.blade.php` | NUEVO | Vista outer wrapper |
| `Modules/Inventario/resources/views/catalogos/origenes.blade.php` | NUEVO | Vista outer wrapper |
| `Modules/Inventario/resources/views/catalogos/almacenamientos.blade.php` | NUEVO | Vista outer wrapper |
| `Modules/Inventario/resources/views/catalogos/mantenimientos.blade.php` | NUEVO | Vista outer wrapper |
| `app/Providers/AuthServiceProvider.php` | MODIFICADO | 28 gates de catálogos registrados |
| `config/versiones.php` | MODIFICADO | Inventario 2.5.0→2.6.0, BhagamApps 1.7.1→1.8.0 |
| `CHANGELOG.md` | MODIFICADO | Entrada v1.8.0 |
| `VERSIONING.md` | MODIFICADO | Versiones actualizadas |
| `docs/changelog/inventario.md` | MODIFICADO | Entrada v2.6.0 |
| `docs/changelog/bhagamapps.md` | MODIFICADO | Entrada v1.8.0 |

---

## 6. Decisiones arquitectónicas

| Decisión | Razonamiento |
|---|---|
| Origenes como catálogo independiente (sin FK a bienes) | La migración de `bienes.origen` de texto libre a FK requiere análisis de datos y está fuera del alcance de este paquete. El catálogo queda listo para vinculación futura. |
| Coordinador solo con ver-* en catálogos | Los catálogos son datos maestros de alta sensibilidad. Solo Administrador y Rector pueden modificarlos. |
| Gates registrados con loop en AuthServiceProvider | 28 gates individuales serían código repetitivo. El loop sobre el array de slugs mantiene la intención sin ruido. |
| Integridad referencial vía conteo en Livewire | Se optó por conteo en el componente en lugar de FK ON DELETE RESTRICT para dar mensaje de error útil al usuario antes de intentar la eliminación. |
| DependenciasIndex carga ubicaciones y usuarios en mount() | Son datos estables (pocas ubicaciones, usuarios conocidos). Cargar en mount() evita N+1 en render(). |

---

## 7. Estado final

```
IMPL-INV-002 — COMPLETADO

Fase 1 — Catálogos principales: COMPLETADO ✅
  Categorías:   CRUD + search + sort + pagination + integrity ✅
  Dependencias: CRUD + search + sort + pagination + integrity + FK selects ✅
  Ubicaciones:  CRUD + search + sort + pagination + integrity ✅
  Estados:      CRUD + search + sort + pagination + integrity ✅
  Orígenes:     CRUD + search + sort + pagination (nuevo catálogo) ✅

Fase 2 — Catálogos auxiliares: COMPLETADO ✅
  Almacenamientos: CRUD + search + sort + pagination + integrity ✅
  Mantenimientos:  CRUD + search + sort + pagination + integrity ✅

V-001 → V-013: TODAS SATISFACTORIAS ✅

Inventario:   v2.5.0 → v2.6.0
BhagamApps:   v1.7.1 → v1.8.0
```

---

*Generado — 2026-06-09*
