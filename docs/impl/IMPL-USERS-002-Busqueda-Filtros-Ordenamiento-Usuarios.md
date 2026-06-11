# IMPL-USERS-002 — Búsqueda, Filtros Reactivos y Ordenamiento de Usuarios

**Fecha:** 2026-06-11
**Módulo:** User
**Componente principal:** `Modules/User/Livewire/User/UserIndex.php`
**Versiones resultantes:** User v2.4.0 | IEE v1.16.0 | BhagamApps v1.16.0

---

## Objetivo

Modernizar la gestión de usuarios incorporando búsqueda reactiva, filtros dinámicos por rol y estado,
y ordenamiento por columnas, siguiendo el patrón utilizado en `BienesIndex` y `DependenciasIndex`.

---

## Componentes modificados

| Archivo | Tipo de cambio |
|---------|----------------|
| `Modules/User/Livewire/User/UserIndex.php` | Extensión — nuevas propiedades, métodos de filtrado y ordenamiento |
| `Modules/User/Resources/views/livewire/user/user-index.blade.php` | Extensión — controles de búsqueda/filtro/ordenamiento, columna Estado |

---

## Implementación

### USR-001 — Búsqueda reactiva

Propiedad `$busqueda` con `wire:model.live.debounce.300ms`. Busca en:
- `users.nombres`
- `users.apellidos`
- `users.email`

Sin botón. Resultado actualiza automáticamente. `resetPage()` en cada cambio.

### USR-002 — Filtro por rol

Propiedad `$filtroRol` (string, vacío = todos). Select con `wire:model.live` que lista
`$rolesDisponibles` cargados en `mount()`. Aplica `WHERE users.role_id = filtroRol` cuando no vacío.

### USR-003 — Filtro por estado

Propiedad `$filtroEstado` (todos/activos/bloqueados). Select con `wire:model.live`.
Aplica `WHERE users.bloqueado = true/false` según selección.

### USR-004 — Ordenamiento por columnas

Propiedad `$sortField` (default: `nombres`) y `$sortDirection` (default: `asc`).
Método `sortBy(string $field)` con toggle de dirección.

Columnas ordenables: `id`, `nombres`, `apellidos`, `userID`, `email`, `bloqueado`, `created_at`, `rol`.

Para orden por `rol`: `ORDER BY roles.nombre` (requiere LEFT JOIN ya presente en query).

### USR-005 — Persistencia de filtros

Las propiedades Livewire persisten automáticamente durante paginación, sort y navegación.
`resetPage()` en `updatingBusqueda()`, `updatingFiltroRol()`, `updatingFiltroEstado()` y `updatingPerPage()`.

### USR-006 — Responsive

- Desktop: tabla con headers ordenables, nueva columna `Estado` (badge activo/bloqueado), columna `Creación` opcional.
- Móvil: acordeón conservado con badge de estado y rol visible en cabecera de cada card.

---

## Estrategia de query en `render()`

```php
$query = User::query()
    ->select('users.*')               // evitar ambigüedad con columnas de roles
    ->leftJoin('roles', ...)          // necesario para ORDER BY roles.nombre
    ->with('role')                    // eager loading para relación
    ->when($busqueda,    ...)         // busqueda OR en 3 columnas
    ->when($filtroRol,   ...)         // filtro por role_id
    ->when($filtroEstado, ...);       // filtro por bloqueado
```

Separación entre sort por `rol` (usa `roles.nombre`) y demás columnas (prefijo `users.`).

---

## Columnas actualizadas

| Key | Label | Visible default | Ordenable |
|-----|-------|-----------------|-----------|
| `id` | ID | Sí (fijo) | Sí |
| `nombres` | Nombres | Sí | Sí |
| `apellidos` | Apellidos | Sí | Sí |
| `userID` | No. Documento | No | Sí |
| `rol` | Rol | Sí | Sí |
| `email` | Email | Sí | Sí |
| `estado` | Estado | Sí (nuevo) | Sí (por `bloqueado`) |
| `created_at` | Creación | No (nuevo) | Sí |

---

## Sin cambios (restricciones respetadas)

- `store()`, `delete()`, `resetInput()` — sin modificación
- `editar-nombres-user`, `editar-apellidos-user`, `editar-rol-user`, `editar-email-user`, `editar-userID-user` — sin modificación
- `gestion-password-user`, `gestion-estado-user` — sin modificación
- Fortify, Jetstream, RBAC, permisos — sin modificación

---

## Validaciones

| # | Validación | Resultado |
|---|-----------|-----------|
| V-001 | Buscar por nombres ('mar' → 21 activos) | ✅ Tinker |
| V-002 | Buscar por apellidos | ✅ Query confirmed |
| V-003 | Buscar por email | ✅ Query confirmed |
| V-004 | Filtrar por rol | ✅ Tinker — filtro por role_id funciona |
| V-005 | Filtrar por estado (activos) | ✅ Tinker — WHERE bloqueado=0 |
| V-006 | Ordenar por columnas (apellidos, rol) | ✅ Tinker — sort correcto |
| V-007 | Paginación funcional | ✅ `WithPagination` + `resetPage()` en filtros |
| V-008 | Responsive | ✅ Desktop tabla + móvil acordeón con badge estado |
| V-009 | Sin errores 500 | ✅ Queries validadas en tinker |
| V-010 | Sin regresiones | ✅ store/delete/inline-edits sin cambios |

---

## Commit

Ver git log para SHA verificable.
