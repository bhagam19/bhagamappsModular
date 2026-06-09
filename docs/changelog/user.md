# User — Changelog

Historial de cambios del módulo User.
Módulo: `Modules/User` — Rutas: `/user/*`

> **Nota:** El módulo se llamaba `Users` hasta v1.1.1. En v2.0.0 fue renombrado
> a `User` con nueva estructura modular. Las entradas anteriores a v2.0.0 se
> conservan bajo el nombre histórico `Users` para fidelidad con el historial.

---

## v2.2.1 — 2026-06-08

### Added

- **[IMPL-AUTH-002]** Nuevos permisos de acceso a secciones administrativas:
  `ver-grupos` (id=33), `ver-evaluacion-docente` (id=34), `ver-biblioteca` (id=35).
  Asignados a Administrador, Rector y Coordinador.
- **[IMPL-AUTH-002]** Permiso `ver-usuarios` asignado a Coordinador (omisión
  corregida — el gate `usuarios.user` ya concedía acceso a Coordinador pero el
  seeder no lo reflejaba).

---

## v2.2.0 — 2026-06-08

### Security

- **[IMPL-AUTH-001 / H-003]** `RolesIndex.store()` ahora requiere `crear-roles`.
  Antes: cualquier usuario con `ver-roles` podía crear roles vía Livewire wire:call.
- **[IMPL-AUTH-001 / H-003]** `RolesIndex.delete()` ahora requiere `eliminar-roles`.
  Antes: cualquier usuario con `ver-roles` podía eliminar roles vía Livewire wire:call.
- **[IMPL-AUTH-001 / H-003]** `EditarNombreRole.editar()` y `guardar()` requieren
  `editar-roles`. Incluida validación de unicidad en `guardar()`.
- **[IMPL-AUTH-001 / H-003]** `EditarDescripcionRole.editar()` y `guardar()` requieren
  `editar-roles`.
- **[IMPL-AUTH-001 / H-003]** `EditarRolePermissions.save()` requiere
  `asignar-permisos-a-roles`. Antes: cualquier usuario podía sincronizar permisos de
  un rol invocando el método directamente.
- **[IMPL-AUTH-001 / H-004]** `PermissionsIndex.store()` requiere `crear-permisos`.
- **[IMPL-AUTH-001 / H-004]** `PermissionsIndex.delete()` requiere `eliminar-permisos`.
- **[IMPL-AUTH-001 / H-004]** `EditarNombrePermission.editar()` y `guardar()` requieren
  `editar-permisos`.
- **[IMPL-AUTH-001 / H-004]** `EditarDescripcionPermission.editar()` y `guardar()`
  requieren `editar-permisos`.
- **[IMPL-AUTH-001 / H-004]** `EditarCategoriaPermission.editar()` y `guardar()`
  requieren `editar-permisos`.

---

## v2.1.2 — 2026-06-08

### Fixed

- **[IMPL-012]** Corregidos 9 slugs de permisos incorrectos en vistas Blade del módulo User.
  `crear-users` → `crear-usuarios`; `editar-user`/`editar-users` → `editar-usuarios`;
  `eliminar-users` → `eliminar-usuarios`. Afectaba: formulario de creación, edición
  inline desktop y móvil, y botones de eliminación — todos invisibles a pesar de que
  el usuario tuviera los permisos correctos.
- **[IMPL-012]** Corregido bug crítico en `EditarRolUser.mount()`: `role_id` se
  inicializaba con el nombre del rol (string) en lugar de la FK integer. Causaba
  corrupción de datos al guardar (`role_id = 'Administrador'` en columna INT).
- **[IMPL-012]** Roles cargados dinámicamente desde base de datos en
  `editar-rol-user.blade.php`. Elimina dependencia de lista hardcodeada de 7 roles.

---

## v2.1.1 — 2026-06-08

### Fixed

- **[IMPL-003]** `permission_role`: eliminados 76 registros duplicados causados por doble
  ejecución del seeder `Permission_RoleSeeder`. La tabla pasó de 156 a 80 registros.
  Se conservó el registro con `id` más bajo para cada par `(role_id, permission_id)`.

### Security

- **[IMPL-003]** Agregado constraint `UNIQUE(role_id, permission_id)` en `permission_role`.
  Previene que futuros seeders o inserciones directas generen duplicados silenciosos.

---

## v2.1.0 — 2026-06-08

### Security

- **[IMPL-001]** `EditarApellidosUser`, `EditarEmailUser`, `EditarRolUser`,
  `EditarUserIDUser`: agregada verificación del permiso `editar-usuarios` en
  los métodos `editar()` y `guardar()`. Cualquier usuario autenticado podía
  modificar datos de otros usuarios, incluido el cambio de rol.
- **[IMPL-002]** Middleware `permission:ver-usuarios` aplicado al resource `users`.
- **[IMPL-002]** Middleware `permission:ver-roles` aplicado al resource `roles`.
- **[IMPL-002]** Middleware `permission:ver-permisos` aplicado al resource `permissions`.

### Fixed

- **[IMPL-001]** `UserIndex.php`: corregidos slugs de permisos.
  El componente usaba `ver-users`, `crear-users`, `eliminar-users` pero la BD
  registra `ver-usuarios`, `crear-usuarios`, `eliminar-usuarios`. Los usuarios
  con permisos correctamente asignados aparecían sin acceso a la sección.
- **[IMPL-001]** `EditarNombresUser.php`: corregido slug `editar-user` → `editar-usuarios`.

---

## v2.0.0 — 2025-06-23

### Changed

- Refactor: renombrado el módulo `Users` a `User` con nueva estructura modular.
- Ajustes en assets y configuración del frontend para adaptarse a la nueva estructura.

---

## Users v1.1.1 — 2025-06-08

### Changed

- Actualización de nombres de permisos para mejorar consistencia.

---

## Users v1.1.0 — 2025-06-07

### Changed

- Actualización de seeders de roles y permisos.
- Reasignación de permisos a roles (coordinadores, docentes, auxiliares).
- Ajustes en rutas de administración de usuarios.

---

## Users v1.0.0 — 2025-05-22

### Added

- Creación inicial del módulo Users.
- Gestión de usuarios, roles y permisos.
