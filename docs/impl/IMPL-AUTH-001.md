# IMPL-AUTH-001 — Authorization Security Remediation

**Fecha:** 2026-06-08
**Estado:** Ejecutado
**Prioridad:** Crítica
**Relacionado con:** AUDIT-AUTH-001A, ADR-008, ADR-AUTHORIZATION-002

---

## 1. Hallazgos confirmados

| ID | Descripción | Evidencia |
|----|-------------|-----------|
| H-001 | `GET /apps` sin `permission:ver-apps` | `Route::resource('apps', ...)` en `Modules/Apps/routes/web.php:12` — solo `auth+verified`. `GET /apps/admin` tenía el permiso; `GET /apps` no. |
| H-003 | Métodos de escritura en Roles sin autorización | `RolesIndex.store()` y `delete()` sin `abort_if`. `EditarNombreRole.guardar()`, `EditarDescripcionRole.guardar()`, `EditarRolePermissions.save()` sin verificación. |
| H-004 | Métodos de escritura en Permissions sin autorización | `PermissionsIndex.store()` y `delete()` sin `abort_if`. `EditarNombrePermission.guardar()`, `EditarDescripcionPermission.guardar()`, `EditarCategoriaPermission.guardar()` sin verificación. |

---

## 2. Correcciones realizadas

### H-001 — `Modules/Apps/routes/web.php`

**Cambio:** `->middleware('permission:ver-apps')` agregado al `Route::resource`.

```php
// Antes
Route::resource('apps', AppController::class)->names('apps.apps');

// Después
Route::resource('apps', AppController::class)->names('apps.apps')
    ->middleware('permission:ver-apps');
```

La ruta `GET /apps/admin` mantiene `permission:ver-apps` (ajuste de consistencia
respecto al permiso: la ruta de acceso al panel usa `ver-apps`; las acciones de
escritura dentro del panel usan `administrar-apps` en los Livewire components).

---

### H-003 — Módulo User / Roles (5 archivos)

**`Modules/User/Livewire/Roles/RolesIndex.php`**

```php
// store() — agregado
abort_if(! auth()->user()->hasPermission('crear-roles'), 403);

// delete() — agregado
abort_if(! auth()->user()->hasPermission('eliminar-roles'), 403);
```

**`Modules/User/Livewire/Roles/EditarNombreRole.php`**

```php
// editar() — agregado
abort_if(! auth()->user()->hasPermission('editar-roles'), 403);

// guardar() — agregado + validación de unicidad
abort_if(! auth()->user()->hasPermission('editar-roles'), 403);
$this->validate(['nombre' => 'required|string|max:255|unique:roles,nombre,' . $this->role->id]);
```

**`Modules/User/Livewire/Roles/EditarDescripcionRole.php`**

```php
// editar() y guardar() — agregado en ambos
abort_if(! auth()->user()->hasPermission('editar-roles'), 403);
```

**`Modules/User/Livewire/Roles/EditarRolePermissions.php`**

```php
// save() — agregado
abort_if(! auth()->user()->hasPermission('asignar-permisos-a-roles'), 403);
```

---

### H-004 — Módulo User / Permissions (4 archivos)

**`Modules/User/Livewire/Permissions/PermissionsIndex.php`**

```php
// store() — agregado
abort_if(! auth()->user()->hasPermission('crear-permisos'), 403);

// delete() — agregado
abort_if(! auth()->user()->hasPermission('eliminar-permisos'), 403);
```

**`EditarNombrePermission.php`, `EditarDescripcionPermission.php`, `EditarCategoriaPermission.php`**

```php
// editar() y guardar() en los tres componentes — agregado
abort_if(! auth()->user()->hasPermission('editar-permisos'), 403);
```

---

## 3. Validación posterior

### Usuarios sin permisos

| Acción intentada | Permiso requerido | Resultado esperado |
|-----------------|------------------|--------------------|
| `GET /apps` (sin ver-apps) | `ver-apps` | 403 Forbidden |
| `GET /apps` (sin auth) | auth+verified | Redirige a login |
| `RolesIndex::store()` | `crear-roles` | abort(403) |
| `RolesIndex::delete()` | `eliminar-roles` | abort(403) |
| `EditarNombreRole::guardar()` | `editar-roles` | abort(403) |
| `EditarRolePermissions::save()` | `asignar-permisos-a-roles` | abort(403) |
| `PermissionsIndex::store()` | `crear-permisos` | abort(403) |
| `PermissionsIndex::delete()` | `eliminar-permisos` | abort(403) |
| `EditarNombrePermission::guardar()` | `editar-permisos` | abort(403) |

### Usuarios autorizados (sin regresión)

Los roles Administrador y Rector tienen asignados en `permission_role`:
- `ver-apps`, `crear-apps`, `editar-apps`, `eliminar-apps`, `administrar-apps` (ids: 28-32)
- `crear-roles`, `editar-roles`, `eliminar-roles`, `asignar-permisos-a-roles` (ids: 6-9)
- `crear-permisos`, `editar-permisos`, `eliminar-permisos` (ids: 11-13)

Estos usuarios continúan accediendo con normalidad. No se modificó ninguna asignación
de permisos en `permission_role`.

---

## 4. Impacto

### Compatibilidad

- Sin cambios en la base de datos.
- Sin cambios en migraciones.
- Sin cambios en modelos.
- Sin cambios en la arquitectura de tres capas (ADR-008).
- Sin cambios en `app_role`, `App::visiblesPara()`, `CheckAppAccess`.

### Riesgo de regresión

**Bajo.** Los cambios son puramente aditivos (`abort_if` al inicio de cada método).
Los usuarios con los permisos correctos no experimentan cambio alguno. Los usuarios
sin permisos reciben 403 donde antes recibían una respuesta indebida.

Un caso particular: `EditarNombreRole.guardar()` recibió una validación de unicidad
adicional (`unique:roles,nombre,{id}`). Esto previene colisiones al renombrar un rol.
No es un cambio de comportamiento para valores válidos.

### Módulos afectados

| Módulo | Archivos | Tipo de cambio |
|--------|----------|----------------|
| Apps | `routes/web.php` | Middleware agregado al resource route |
| User | 9 Livewire components | `abort_if` en métodos de escritura |

---

## 5. Hallazgos NO corregidos en esta implementación

| ID | Descripción | Razón |
|----|-------------|-------|
| H-002 | Flujos de aprobación con roles hardcoded en Inventario | Fuera del alcance de IMPL-AUTH-001. Requiere decisión de negocio sobre modelo de aprobación. |
| H-005 | `EditarSlugApp` no invalida caché | Fuera del alcance. |
| H-006 | `hasPermission()` sin caché | Fuera del alcance. |
| H-007 | `app_user.role_id` sin uso | Fuera del alcance (DP-001 de ADR-008). |
| H-008 | Menú sidebar desincronizado | Pendiente IMPL-013. |
| H-009 | Gates hardcoded | Pendiente IMPL-AUTH-002. |
| H-010 | Resource routes sin implementación | Bajo riesgo — no explotable. |
| H-011 | Patrones inconsistentes de rechazo | Fuera del alcance. |

---

## 6. Versionado

| Componente | Versión anterior | Versión nueva |
|------------|-----------------|---------------|
| Apps | v1.3.0 | v1.3.1 |
| User | v2.1.2 | v2.2.0 |
| BhagamApps (plataforma) | v1.6.0 | v1.6.1 |

Archivos actualizados:
- `CHANGELOG.md`
- `VERSIONING.md`
- `config/versiones.php`
- `docs/changelog/apps.md`
- `docs/changelog/user.md`
