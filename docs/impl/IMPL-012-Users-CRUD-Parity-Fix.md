# IMPL-012 — Users CRUD Parity Fix

**Estado:** COMPLETADA
**Fecha:** 2026-06-08
**Origen:** AUDIT-USERS-001
**Responsable:** Implementación

---

# Contexto

AUDIT-USERS-001 identificó que el módulo Users tenía toda la infraestructura CRUD
funcional, pero defectos menores impedían su correcta utilización. Los principales
problemas eran slugs de permisos incorrectos en vistas Blade y un bug crítico de
corrupción de datos en la inicialización de `EditarRolUser`.

---

# Objetivo

Restablecer la paridad funcional CRUD entre los módulos Users y Roles mediante la
corrección de las inconsistencias detectadas.

---

# Hallazgos Confirmados

## H-01 — Slugs incorrectos en user-index.blade.php

Archivo: `Modules/User/Resources/views/livewire/user/user-index.blade.php`

| Slug incorrecto | Slug correcto | Línea original | Ocurrencias |
|---|---|---|---|
| `crear-users` | `crear-usuarios` | 40 | 1 |
| `editar-user` | `editar-usuarios` | 143 | 1 |
| `editar-users` | `editar-usuarios` | 179, 187, 195, 203, 211 | 5 |
| `eliminar-users` | `eliminar-usuarios` | 219, 249 | 2 |

> **Nota:** La búsqueda global adicional (R-02) detectó `editar-users` — variante
> no identificada en la auditoría original pero del mismo tipo. Corregida en esta IMPL.

## H-02 — Slugs incorrectos en vistas inline móviles

Slug incorrecto `editar-user` corregido a `editar-usuarios` en:

| Archivo |
|---|
| `Modules/User/Resources/views/livewire/user/editar-nombres-user.blade.php` |
| `Modules/User/Resources/views/livewire/user/editar-apellidos-user.blade.php` |
| `Modules/User/Resources/views/livewire/user/editar-email-user.blade.php` |
| `Modules/User/Resources/views/livewire/user/editar-userID-user.blade.php` |
| `Modules/User/Resources/views/livewire/user/editar-rol-user.blade.php` |

## H-03 — Bug crítico en EditarRolUser.php

Archivo: `Modules/User/Livewire/User/EditarRolUser.php`

**Antes:**
```php
$this->role_id = $user->role->nombre ?? 'Sin rol';
```

**Después:**
```php
$this->role_id = $user->role_id;
```

**Impacto del bug:** `mount()` asignaba el nombre del rol (string) a `$role_id`,
que luego en `guardar()` era escrito a la columna `role_id` (FK integer). Esto
causaba corrupción de datos silenciosa o error de integridad referencial al guardar.

## H-04 — Roles dinámicos en editar-rol-user.blade.php

Archivo: `Modules/User/Resources/views/livewire/user/editar-rol-user.blade.php`

Reemplazadas 7 opciones hardcodeadas (IDs 1-7 con nombres fijos) por carga
dinámica desde base de datos.

**Antes:**
```blade
<option value="1">Administrador</option>
<option value="2">Rector</option>
...
<option value="7">Invitado</option>
```

**Después:**
```blade
@foreach ($roles as $role)
    <option value="{{ $role->id }}">{{ $role->nombre }}</option>
@endforeach
```

Los roles se pasan desde `EditarRolUser.php` vía `render()`:
```php
return view('user::livewire.user.editar-rol-user', [
    'roles' => Role::all(),
]);
```

---

# Búsqueda Global Adicional

Antes de finalizar se ejecutó búsqueda global en todo el módulo User para las
variantes `crear-users`, `editar-user`, `editar-users`, `eliminar-users`.

**Resultado:** Sin ocurrencias residuales. Cero slugs incorrectos en el módulo.

---

# Archivos Modificados

| Archivo | Tipo de cambio |
|---|---|
| `Modules/User/Livewire/User/EditarRolUser.php` | H-03 bug fix + H-04 roles dinámicos |
| `Modules/User/Resources/views/livewire/user/user-index.blade.php` | H-01 slugs |
| `Modules/User/Resources/views/livewire/user/editar-rol-user.blade.php` | H-02 slug + H-04 roles |
| `Modules/User/Resources/views/livewire/user/editar-nombres-user.blade.php` | H-02 slug |
| `Modules/User/Resources/views/livewire/user/editar-apellidos-user.blade.php` | H-02 slug |
| `Modules/User/Resources/views/livewire/user/editar-email-user.blade.php` | H-02 slug |
| `Modules/User/Resources/views/livewire/user/editar-userID-user.blade.php` | H-02 slug |

---

# Criterios de Éxito

✅ Formulario de creación visible para usuarios con `crear-usuarios`.
✅ Mensaje de ayuda "Doble click" visible para usuarios con `editar-usuarios`.
✅ Edición inline (desktop: doble click) habilitada para usuarios con `editar-usuarios`.
✅ Botones "Editar" móvil visibles para usuarios con `editar-usuarios`.
✅ Botones "Eliminar" visibles en desktop y móvil para usuarios con `eliminar-usuarios`.
✅ Rol actual se muestra correctamente (FK integer, no string).
✅ Cambio de rol se guarda correctamente sin corrupción de FK.
✅ Roles cargados dinámicamente desde base de datos.
✅ Cero slugs incorrectos en el módulo User.

---

# Exclusiones Aplicadas

No se modificaron:

- H-05: Permisos faltantes en RolesIndex.
- H-06: Permisos faltantes en componentes de edición de Roles.
- H-07: Paginación de RolesIndex.
- Ninguna funcionalidad nueva.
- Ningún otro módulo.

---

# Trazabilidad

```text
AUDIT-USERS-001
        ↓
IMPL-012
        ↓
Users CRUD Parity Fix
```
