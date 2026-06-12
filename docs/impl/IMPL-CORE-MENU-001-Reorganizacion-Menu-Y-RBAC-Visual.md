# IMPL-CORE-MENU-001 — Reorganización Definitiva del Menú y RBAC Visual

**Fecha:** 2026-06-12
**Módulo:** Core — `config/adminlte.php`, `app/Providers/AuthServiceProvider.php`
**Versión:** BhagamApps v1.20.0 · IEE v1.21.0
**Estado:** IMPLEMENTADO

---

## Objetivo

Alinear la navegación lateral con la arquitectura funcional de IEE: contenedor Mis Módulos,
RBAC visual granular por permiso, orden alfabético en Inventario, eliminación de módulos
placeholder sin implementar.

---

## Auditoría Inicial (MENU-001)

### Estado del menú antes de la implementación

| Elemento | Estado | Problema |
|---|---|---|
| Sin header "Mis Módulos" | Ausente | No hay contenedor raíz |
| Gestión de Accesos | `can: usuarios.user` | Roles y Permisos sin gate individual |
| `active` de Usuarios/Roles/Permisos | `admin/user*`, `admin/roles*`, `admin/permissions*` | Rutas reales son `users/users*`, `users/roles*`, `users/permissions*` |
| Inventario | Sin gate padre | Orden: Dashboard, Bienes, Actas, Responsables, Categorías... (no alfabético) |
| "Mantenimientos" duplicado | 2 ítems con mismo texto | catalog (fa-tools) y programados (fa-wrench) indistinguibles |
| Grupos | `can: admin.grupos`, rutas vacías `''` | Módulo no implementado |
| Evaluación Docente | `can: admin.evaldoc`, rutas vacías `''` | Módulo no implementado |
| Biblioteca | `can: admin.biblioteca`, rutas vacías `''` | Módulo no implementado |

### Gates faltantes detectados

Los slugs `ver-roles` (id=56) y `ver-permisos` (id=61) existían en la tabla `permissions`
pero no tenían `Gate::define()` en `AuthServiceProvider`. Las rutas del módulo User ya usaban
`middleware('permission:ver-roles')` y `middleware('permission:ver-permisos')`, pero el
menú no podía evaluarlos.

---

## Cambios Implementados

### AuthServiceProvider (MENU-006)

Añadidos al final de `boot()`:

```php
Gate::define('ver-roles', fn($user) => $user->hasPermission('ver-roles'));
Gate::define('ver-permisos', fn($user) => $user->hasPermission('ver-permisos'));
```

### config/adminlte.php — Menú completo (MENU-002 al MENU-009)

#### Estructura resultante

```
Inicio
[HEADER] MIS MÓDULOS
  Gestión de Acceso          (can: usuarios.user)
    → Usuarios               (hereda gate del padre)
    → Roles                  (can: ver-roles)
    → Permisos               (can: ver-permisos)
  Aplicaciones               (can: ver-apps)
  Inventario
    → Dashboard              (sin gate — accesible por todos los que acceden al módulo)
    → Bienes                 (can: ver-bienes)
    → Actas de Entrega       (can: ver-actas-de-entrega)
    → Almacenamientos        (can: ver-almacenamientos)
    → Categorías             (can: ver-categorias)
    → Dependencias           (can: ver-dependencias)
    → Estados                (can: ver-estados)
    → Historial Eliminaciones(can: gestionar-historial-eliminaciones-bienes)
    → Historial Modificaciones(can: gestionar-historial-modificaciones-bienes)
    → Historial Ubicaciones  (can: ver-historial-ubicaciones-bienes)
    → Mantenimientos         (can: ver-mantenimientos)              [catálogo]
    → Mantenimientos Programados (can: ver-mantenimientos-programados)
    → Orígenes               (can: ver-origenes)
    → Responsables           (can: ver-responsables-bienes)
    → Ubicaciones            (can: ver-ubicaciones)
```

#### Cambios específicos vs. estado anterior

| Cambio | Antes | Después |
|---|---|---|
| Header raíz | Ausente | `['header' => 'MIS MÓDULOS']` |
| Nombre sección acceso | "Gestión de Accesos" | "Gestión de Acceso" |
| Gate individual Roles | Ninguno | `can: ver-roles` |
| Gate individual Permisos | Ninguno | `can: ver-permisos` |
| active Usuarios | `admin/user*` | `users/users*` |
| active Roles | `admin/roles*` | `users/roles*` |
| active Permisos | `admin/permissions*` | `users/permissions*` |
| Inventario active padre | `['inventario']` | `['inventario*']` |
| Orden Inventario | Aleatorio post-Bienes | Alfabético post-Dashboard/Bienes |
| "Mantenimientos" duplicado | 2 ítems igual texto | Segundo renombrado "Mantenimientos Programados" |
| Grupos | Presente (rutas vacías) | **Eliminado** |
| Evaluación Docente | Presente (rutas vacías) | **Eliminado** |
| Biblioteca | Presente (rutas vacías) | **Eliminado** |

---

## Verificación por Roles (MENU-010)

| Gate / Rol | Administrador | Rectoría | Coordinación | Auxiliar |
|---|---|---|---|---|
| `usuarios.user` (Gestión de Acceso) | SI | SI | NO | NO |
| `ver-roles` (Roles) | SI | SI | NO | NO |
| `ver-permisos` (Permisos) | SI | SI | NO | NO |
| `ver-bienes` (Bienes) | SI | SI | SI | SI |

---

## Validaciones (MENU-001 → MENU-010)

| ID | Validación | Estado |
|---|---|---|
| V-001 | Mis Módulos es el contenedor principal | ✓ `['header' => 'MIS MÓDULOS']` |
| V-002 | Gestión de Acceso agrupada correctamente | ✓ con RBAC individual |
| V-003 | Inventario agrupado correctamente | ✓ Dashboard, Bienes, luego alfabético |
| V-004 | CRUDs ordenados alfabéticamente | ✓ Actas→Almac→Cat→Dep→Est→Hist*→Mant→MantProg→Orí→Resp→Ubic |
| V-005 | Apps ocultas/placeholder eliminadas | ✓ Grupos, Eval. Docente, Biblioteca eliminados |
| V-006 | RBAC visual Roles/Permisos | ✓ Gates ver-roles, ver-permisos operativos |
| V-007 | App::visiblesPara() respetado | ✓ Aplicaciones sigue con `can: ver-apps` |
| V-008 | Sin enlaces rotos | ✓ Todas las rutas verificadas con `route:list` |
| V-009 | Sin errores 403 inesperados | ✓ active patterns corregidos a `users/*` |
| V-010 | Sin regresiones | ✓ Sintaxis PHP verificada en ambos archivos |

---

## Deuda Técnica Residual

El nodo padre "Inventario" no tiene gate propio — aparece en el menú para cualquier usuario
autenticado aunque no tenga acceso al módulo (el middleware `app.access:inventario` lo bloquea
en ruta, pero el ítem es visible en sidebar). Para ocultarlo a nivel de menú se requeriría
un Gate basado en `App::visiblesPara()` que actualmente no está registrado como Gate de Laravel.
Registrado como DT-MENU-001 para evaluación futura.

---

## SHA verificable

```
0b2cada feat(core): IMPL-CORE-MENU-001 — Reorganización Menú y RBAC Visual (MENU-001→010)
```
