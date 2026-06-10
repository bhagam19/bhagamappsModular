# AUDIT-INV-NAV-001 — Inventario Navigation Audit

**Fecha:** 2026-06-09  
**Repositorio:** private/bhagamappsModular  
**Módulo:** Modules/Inventario  
**Propósito:** Verificar integración completa de todos los CRUDs de Inventario al menú lateral

---

## Veredicto general

**2 COMPLETOS — 8 PARCIALES — 0 NO INTEGRADOS**

El sidebar de Inventario está severamente incompleto: 7 catálogos y HEB tienen ruta, permiso y gate correctos, pero no aparecen en el menú lateral. Los usuarios no pueden acceder a ellos sin URL directa.

---

## Matriz completa

| Módulo | Ruta | Permiso | Gate | Sidebar | Estado |
|---|---|---|---|---|---|
| **Bienes** | ✅ `inventario.bienes.index` | ✅ `ver-bienes` | ✅ Línea 34 ASP | ✅ adminlte.php:372 | **COMPLETO** |
| **Categorías** | ✅ `inventario.catalogos.categorias` | ✅ `ver-categorias` | ✅ Loop línea 120 ASP | ❌ Ausente | **PARCIAL** |
| **Dependencias** | ✅ `inventario.catalogos.dependencias` | ✅ `ver-dependencias` | ✅ Loop línea 121 ASP | ❌ Ausente | **PARCIAL** |
| **Ubicaciones** | ✅ `inventario.catalogos.ubicaciones` | ✅ `ver-ubicaciones` | ✅ Loop línea 122 ASP | ❌ Ausente | **PARCIAL** |
| **Estados** | ✅ `inventario.catalogos.estados` | ✅ `ver-estados` | ✅ Loop línea 123 ASP | ❌ Ausente | **PARCIAL** |
| **Orígenes** | ✅ `inventario.catalogos.origenes` | ✅ `ver-origenes` | ✅ Loop línea 124 ASP | ❌ Ausente | **PARCIAL** |
| **Almacenamientos** | ✅ `inventario.catalogos.almacenamientos` | ✅ `ver-almacenamientos` | ✅ Loop línea 125 ASP | ❌ Ausente | **PARCIAL** |
| **Mantenimientos** | ✅ `inventario.catalogos.mantenimientos` | ✅ `ver-mantenimientos` | ✅ Loop línea 126 ASP | ❌ Ausente | **PARCIAL** |
| **Responsables** | ✅ `inventario.responsables.index` | ✅ `ver-responsables-bienes` | ✅ Loop línea 133 ASP | ✅ adminlte.php:388 | **COMPLETO** |
| **HEB** | ✅ `inventario.heb` | ✅ `gestionar-historial-eliminaciones-bienes` | ❌ Sin Gate::define | ❌ Ausente | **PARCIAL** |

> ASP = `app/Providers/AuthServiceProvider.php`

---

## Detalle por módulo

### 1. Bienes — COMPLETO

| Check | Evidencia |
|---|---|
| Ruta | `web.php:16` — `GET /bienes` → `BienController` — middleware `permission:ver-bienes` |
| Permiso | CSV seeder `Modules/User/Database/Seeders/data/permissions.csv:15` |
| Gate | `AuthServiceProvider.php:34` — `Gate::define('ver-bienes', ...)` |
| Sidebar | `adminlte.php:372-378` — `'can' => 'ver-bienes'` |

---

### 2. Categorías — PARCIAL

| Check | Evidencia |
|---|---|
| Ruta | `web.php:43-45` — `GET /catalogos/categorias` → `CatalogosController` — middleware `permission:ver-categorias` |
| Permiso | Migración `2026_06_09_000008_add_catalog_permissions.php` |
| Gate | `AuthServiceProvider.php:120` — loop foreach, `Gate::define('ver-categorias', ...)` |
| Sidebar | ❌ **Sin entrada en adminlte.php** |

---

### 3. Dependencias — PARCIAL

| Check | Evidencia |
|---|---|
| Ruta | `web.php:47-49` — `GET /catalogos/dependencias` → `CatalogosController` — middleware `permission:ver-dependencias` |
| Permiso | Migración `2026_06_09_000008_add_catalog_permissions.php` |
| Gate | `AuthServiceProvider.php:121` — loop foreach, `Gate::define('ver-dependencias', ...)` |
| Sidebar | ❌ **Sin entrada en adminlte.php** |

---

### 4. Ubicaciones — PARCIAL

| Check | Evidencia |
|---|---|
| Ruta | `web.php:51-53` — `GET /catalogos/ubicaciones` → `CatalogosController` — middleware `permission:ver-ubicaciones` |
| Permiso | Migración `2026_06_09_000008_add_catalog_permissions.php` |
| Gate | `AuthServiceProvider.php:122` — loop foreach + legacy línea 42 |
| Sidebar | ❌ **Sin entrada en adminlte.php** |

---

### 5. Estados — PARCIAL

| Check | Evidencia |
|---|---|
| Ruta | `web.php:55-57` — `GET /catalogos/estados` → `CatalogosController` — middleware `permission:ver-estados` |
| Permiso | Migración `2026_06_09_000008_add_catalog_permissions.php` |
| Gate | `AuthServiceProvider.php:123` — loop foreach + legacy línea 54 |
| Sidebar | ❌ **Sin entrada en adminlte.php** |

---

### 6. Orígenes — PARCIAL

| Check | Evidencia |
|---|---|
| Ruta | `web.php:59-61` — `GET /catalogos/origenes` → `CatalogosController` — middleware `permission:ver-origenes` |
| Permiso | Migración `2026_06_09_000008_add_catalog_permissions.php` |
| Gate | `AuthServiceProvider.php:124` — loop foreach |
| Sidebar | ❌ **Sin entrada en adminlte.php** |

---

### 7. Almacenamientos — PARCIAL

| Check | Evidencia |
|---|---|
| Ruta | `web.php:63-65` — `GET /catalogos/almacenamientos` → `CatalogosController` — middleware `permission:ver-almacenamientos` |
| Permiso | Migración `2026_06_09_000008_add_catalog_permissions.php` |
| Gate | `AuthServiceProvider.php:125` — loop foreach |
| Sidebar | ❌ **Sin entrada en adminlte.php** |

---

### 8. Mantenimientos — PARCIAL

| Check | Evidencia |
|---|---|
| Ruta | `web.php:67-69` — `GET /catalogos/mantenimientos` → `CatalogosController` — middleware `permission:ver-mantenimientos` |
| Permiso | Migración `2026_06_09_000008_add_catalog_permissions.php` |
| Gate | `AuthServiceProvider.php:126` — loop foreach + legacy línea 74 |
| Sidebar | ❌ **Sin entrada en adminlte.php** |

---

### 9. Responsables — COMPLETO

| Check | Evidencia |
|---|---|
| Ruta | `web.php:72-74` — `GET /responsables` → `ResponsablesController` — middleware `permission:ver-responsables-bienes` |
| Permiso | Migración `2026_06_09_000009_add_responsables_permissions.php` |
| Gate | `AuthServiceProvider.php:133-138` — loop foreach responsables |
| Sidebar | `adminlte.php:388-394` — `'can' => 'ver-responsables-bienes'` — IMPL-INV-003A |

---

### 10. HEB (Historial Eliminaciones Bienes) — PARCIAL

| Check | Evidencia |
|---|---|
| Ruta | `web.php:38-40` — `GET /heb` → `HebController` — middleware `permission:gestionar-historial-eliminaciones-bienes` |
| Permiso | Migración `2026_06_09_000004_add_heb_permission_and_assign_roles.php` |
| Gate | ❌ **`Gate::define('gestionar-historial-eliminaciones-bienes', ...)` no encontrado en AuthServiceProvider** |
| Sidebar | ❌ **Sin entrada en adminlte.php** |

> **Nota crítica HEB:** El middleware de ruta (`permission:`) usa Spatie directamente y funciona. Sin embargo, no existe Gate::define() correspondiente, lo que impide el uso de `@can('gestionar-historial-eliminaciones-bienes')` en vistas.

---

## Hallazgo adicional: HMB (fuera del scope original, incluido por completitud)

| Check | Evidencia |
|---|---|
| Ruta | `web.php:34-36` — `GET /hmb` → `HmbController` — middleware `permission:gestionar-historial-modificaciones-bienes` |
| Permiso | CSV seeder `permissions.csv:22` |
| Gate | `AuthServiceProvider.php:58-60` — `Gate::define(...)` presente |
| Sidebar | ❌ **Sin entrada en adminlte.php** |

---

## Deficiencias identificadas

### D-001 — Catálogos sin sidebar (7 módulos)
Categorías, Dependencias, Ubicaciones, Estados, Orígenes, Almacenamientos y Mantenimientos tienen infraestructura completa pero son inaccesibles desde el menú.

**Patrón de corrección:** agregar submenu "Catálogos" con entrada por módulo bajo `'can' => 'ver-{modulo}'`.

### D-002 — HEB sin Gate::define
La ruta usa middleware Spatie (`permission:...`) que funciona de forma independiente, pero no existe `Gate::define('gestionar-historial-eliminaciones-bienes', ...)` en `AuthServiceProvider.php`. Las directivas `@can(...)` en vistas para este permiso funcionarían via Spatie auto-gate, pero la inconsistencia con el patrón del proyecto es un riesgo.

### D-003 — HEB sin sidebar
HEB no tiene entrada en el menú lateral.

### D-004 — HMB sin sidebar (fuera del scope auditado)
HMB tiene gate definido pero tampoco aparece en el sidebar.

---

## Pendientes para cierre de navegación

| ID | Acción | Módulos afectados |
|---|---|---|
| P-001 | Agregar entradas de catálogos al sidebar (agrupados o individuales) | Categorías, Dependencias, Ubicaciones, Estados, Orígenes, Almacenamientos, Mantenimientos |
| P-002 | Agregar `Gate::define` para HEB en AuthServiceProvider | HEB |
| P-003 | Agregar entrada HEB al sidebar | HEB |
| P-004 | Evaluar agregar entrada HMB al sidebar | HMB |
