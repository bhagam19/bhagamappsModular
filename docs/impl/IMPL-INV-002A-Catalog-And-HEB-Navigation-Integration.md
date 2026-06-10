# IMPL-INV-002A — Catalog & HEB Navigation Integration

**Estado:** IMPLEMENTADO — IMPL-INV-002 CERRADO DEFINITIVAMENTE  
**Fecha:** 2026-06-09  
**Origen:** AUDIT-INV-NAV-001  
**Versión:** Inventario v2.8.0 | BhagamApps v1.9.2

---

## Contexto

AUDIT-INV-NAV-001 determinó que 8 módulos de Inventario tenían infraestructura completa
(rutas, permisos, gates) pero carecían de entrada en el menú lateral. Los usuarios no podían
acceder a ellos sin URL directa. Adicionalmente, HEB no tenía `Gate::define()` definido,
lo que impedía el uso de `@can()` en vistas.

---

## Cambios implementados

### 1. `config/adminlte.php` — 8 entradas añadidas al submenú Inventario

Añadidas tras la entrada "Responsables":

| Entrada | Ruta | Permiso `can` |
|---|---|---|
| Categorías | `inventario.catalogos.categorias` | `ver-categorias` |
| Dependencias | `inventario.catalogos.dependencias` | `ver-dependencias` |
| Ubicaciones | `inventario.catalogos.ubicaciones` | `ver-ubicaciones` |
| Estados | `inventario.catalogos.estados` | `ver-estados` |
| Orígenes | `inventario.catalogos.origenes` | `ver-origenes` |
| Almacenamientos | `inventario.catalogos.almacenamientos` | `ver-almacenamientos` |
| Mantenimientos | `inventario.catalogos.mantenimientos` | `ver-mantenimientos` |
| Historial Eliminaciones | `inventario.heb` | `gestionar-historial-eliminaciones-bienes` |

Todas las entradas siguen el estándar establecido: `text`, `icon`, `route`, `active`, `can`.

### 2. `app/Providers/AuthServiceProvider.php` — Gate HEB añadido

```php
Gate::define('gestionar-historial-eliminaciones-bienes',
    fn($user) => $user->hasPermission('gestionar-historial-eliminaciones-bienes'));
```

Corrige D-002 de AUDIT-INV-NAV-001. Ubicado antes del marcador `[crud-generator-gates]`.

---

## Estructura final del menú Inventario

```
Inventario
├── Bienes               (ver-bienes)
├── Actas de Entrega     (ver-actas-de-entrega)
├── Responsables         (ver-responsables-bienes)
├── Categorías           (ver-categorias)
├── Dependencias         (ver-dependencias)
├── Ubicaciones          (ver-ubicaciones)
├── Estados              (ver-estados)
├── Orígenes             (ver-origenes)
├── Almacenamientos      (ver-almacenamientos)
├── Mantenimientos       (ver-mantenimientos)
└── Historial Eliminaciones (gestionar-historial-eliminaciones-bienes)
```

---

## Validaciones

| Validación | Criterio | Mecanismo | Estado |
|---|---|---|---|
| V-001 | Categorías visible para autorizados | `can: ver-categorias` en adminlte.php | ✅ |
| V-002 | Dependencias visible para autorizados | `can: ver-dependencias` | ✅ |
| V-003 | Ubicaciones visible para autorizados | `can: ver-ubicaciones` | ✅ |
| V-004 | Estados visible para autorizados | `can: ver-estados` | ✅ |
| V-005 | Orígenes visible para autorizados | `can: ver-origenes` | ✅ |
| V-006 | Almacenamientos visible para autorizados | `can: ver-almacenamientos` | ✅ |
| V-007 | Mantenimientos visible para autorizados | `can: ver-mantenimientos` | ✅ |
| V-008 | HEB visible para autorizados | `can: gestionar-historial-eliminaciones-bienes` | ✅ |
| V-009 | Gate HEB funcionando | `Gate::define(...)` en AuthServiceProvider línea 142 | ✅ |
| V-010 | Usuarios sin permisos no visualizan | AdminLTE filtra por `can` automáticamente | ✅ |

---

## Archivos modificados

| Archivo | Tipo de cambio |
|---|---|
| `config/adminlte.php` | +8 entradas en submenú Inventario |
| `app/Providers/AuthServiceProvider.php` | +1 Gate::define HEB |
| `config/versiones.php` | v2.8.0 / v1.9.2 |
| `CHANGELOG.md` | v1.9.2 |
| `VERSIONING.md` | v2.8.0 / v1.9.2 |
| `docs/changelog/inventario.md` | v2.8.0 |
| `docs/changelog/bhagamapps.md` | v1.9.2 |

---

## Cierre formal

**IMPL-INV-002 — Catalog Management: CERRADO DEFINITIVAMENTE**

Todos los catálogos maestros (Categorías, Dependencias, Ubicaciones, Estados, Orígenes,
Almacenamientos, Mantenimientos) están completamente implementados y accesibles desde la
navegación institucional con control de permisos.
