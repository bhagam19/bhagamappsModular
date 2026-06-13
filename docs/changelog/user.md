# User — Changelog

Historial de cambios del módulo User.
Módulo: `Modules/User` — Rutas: `/user/*`

---

## v2.5.1 — 2026-06-12

### Fixed (IMPL-CORE-MENU-001 completion — RBAC Rector)

- **RoleSeeder**: Rector ya no recibe permisos de categorías `roles` ni `permisos`.
  `Permission::whereNotIn('categoria', ['roles', 'permisos'])` como fuente para el sync
  del Rector. Rector mantiene acceso a usuarios, bienes, inventario, apps y administración
  de contraseñas, pero no puede ver/crear/editar/eliminar Roles ni Permisos.
  Cierra V-008 y V-009 del IMPL-CORE-MENU-001.

---

## v2.5.0 — 2026-06-12

### Added (IMPL-RBAC-002 — Jerarquía Institucional y Protección del Administrador Principal)

- Columna `es_principal` (boolean, default false) en tabla `users`. Migración marca
  automáticamente al primer Administrador como principal.
- Método `isAdminPrincipal()` en entidad `User`.
- Trait `ProteccionAdminPrincipal`: centraliza protección con registro en
  `auditoria_passwords` y abort 403.
- Protección backend en 8 componentes Livewire: UserIndex, EditarNombresUser,
  EditarApellidosUser, EditarEmailUser, EditarUserIDUser, EditarRolUser,
  GestionPasswordUser, GestionEstadoUser.
- Protección visual en `user-index.blade.php`: Admin Principal con badges
  `Administrador Principal` y `Protegido`; campos de edición en modo solo-lectura.

---

## v2.4.4 — 2026-06-11

### Fixed (HOTFIX-USERS-007 — Integridad del listado de usuarios)

- **Causa raíz**: ausencia de `wire:key` en los elementos raíz de los `@forelse` que
  iteran usuarios. Livewire 3 aplicaba morfología posicional a las `<tr>` y `.card`.
  Al cambiar el ordenamiento, morph no podía identificar filas por usuario — creaba
  componentes hijo con snapshot JS nulo, causando filas visualmente faltantes y
  `ErrorException: Trying to access array offset on null` en `HandleComponents.php:88`
  al interactuar con esas filas.
- `user-index.blade.php`: añadido `wire:key="row-{{ $user->id }}"` en el `<tr>` de
  la tabla desktop y `wire:key="card-{{ $user->id }}"` en el `.card` del acordeón móvil.
  Morph ahora identifica cada fila por ID de usuario y las mueve/crea/elimina correctamente.
- Verificado: 116 usuarios, sin duplicados, todas las páginas cubiertas (USRINT-001→005).

---

## v2.4.3 — 2026-06-11

### Fixed (HOTFIX-USERS-006 — Corrección definitiva 419 en búsqueda/filtros/ordenamiento)

- **Causa raíz**: snapshot Livewire de `UserIndex` superaba el límite de 16,383 bytes
  del buffer POST de PHP. Con perPage=25 y 14 componentes hijos por usuario (7 desktop +
  7 móvil duplicados), el cuerpo POST alcanzaba ~17,900 bytes. PHP descartaba todo el cuerpo,
  el token CSRF quedaba ilegible, y Laravel retornaba HTTP 419.
- `user-index.blade.php`: reemplazados los 5 `@livewire('user.editar-*-user')` del cuerpo
  del acordeón móvil con display estático (HTML puro). Los hijos por usuario pasan de 14 a 9
  (máximo con todos los permisos activos). Con perPage=25: 225 hijos → cuerpo ~12,850 bytes
  → margen de 3,533 bytes bajo el umbral de 16,383.
- `user-index.blade.php`: eliminadas opciones perPage=50 y perPage=100 del selector.
  Con 9 hijos/usuario, perPage=50 generaría 450 hijos → cuerpo ~21 KB → también produciría 419.
- `UserIndex.php`: `updatingPerPage()` renombrado a `updatedPerPage()` con validación
  `in_array($this->perPage, [10, 25])` para bloquear valores inválidos vía URL directa.
- Funcionalidad preservada: búsqueda reactiva, filtros por rol y estado, ordenamiento
  por todas las columnas, edición inline desktop, gestión de contraseñas y estado en
  desktop y móvil. Documentado en HOTFIX-USERS-006-Correccion-Definitiva-419-Livewire.md.

---

## v2.4.2 — 2026-06-11

### Fixed (HOTFIX-USERS-004 — Error 419 en búsqueda/filtros/ordenamiento)

- **Causa raíz diagnosticada**: el 419 es sesión expirada, no un fallo de CSRF en código.
  Livewire muestra "This page has expired" cuando la sesión del navegador expira o fue
  corrompida. El servidor retorna HTTP 200 para todas las peticiones con sesión válida.
- `UserIndex::render()`: eliminado `->layout('layouts.app')`. Para componentes anidados
  `->layout()` es un no-op (solo aplica en full-page components via `__invoke()`).
- `CheckForzarCambioPassword`: corregido `'livewire/'` → `'livewire'` en la lista de rutas
  permitidas. La entrada anterior generaba patrón `'livewire//*'` que nunca coincidía con
  `livewire/update`, bloqueando peticiones Livewire de usuarios con `forzar_cambio_password`.

---

## v2.4.1 — 2026-06-11

### Fixed (HOTFIX-USERS-003 — FatalError en mount())

- `UserIndex::mount()`: eliminado `: void` del signature.
  IMPL-USERS-002 añadió esta anotación de tipo pero `mount()` tiene `return redirect()`
  condicional — PHP lanza `A void function must not return a value` como FatalError.

---

## v2.4.0 — 2026-06-11

### Added (IMPL-USERS-002 — Búsqueda, Filtros y Ordenamiento de Usuarios)

- Búsqueda reactiva (`wire:model.live.debounce.300ms`) en nombres, apellidos y email. Sin botón.
- Filtro por rol: select reactivo que filtra por `role_id`. Opciones dinámicas desde BD.
- Filtro por estado: Todos / Activos / Bloqueados sobre columna `bloqueado`.
- Ordenamiento por columnas: ID, Nombres, Apellidos, No. Documento, Rol, Email, Estado, Creación.
  Toggle asc/desc con ícono visual en cada encabezado.
- Filtros persistentes durante paginación, ordenamiento y navegación Livewire.
- Columna `Estado` añadida a la tabla (badge Activo/Bloqueado) — visible por defecto.
- Columna `Creación` disponible en toggle de columnas.
- Vista móvil (acordeón): badge de estado y rol en cabecera de cada card.
- `$rolesDisponibles` cargado en `mount()` — elimina query dentro de Blade.

---

## v2.3.0 — 2026-06-11

### Added

- **[IMPL-USERS-001]** Administración institucional de contraseñas y estados de usuario.
  - `GestionPasswordUser` Livewire: restablece contraseña (Hash::make), genera contraseña
    aleatoria, opción de forzar cambio al siguiente ingreso.
  - `GestionEstadoUser` Livewire: bloquea/desbloquea cuentas de usuario.
  - `CheckForzarCambioPassword` middleware en grupo web: intercepta solicitudes y redirige
    a `/user/profile` cuando el flag `forzar_cambio_password` está activo.
  - `UpdateUserPassword` limpia `forzar_cambio_password` tras cambio exitoso.
  - `FortifyServiceProvider::authenticateUsing` impide autenticación de usuarios bloqueados.
  - Tabla `auditoria_passwords` con acciones: `password_reset`, `password_forced`,
    `user_blocked`, `user_unblocked` — `usuario_afectado_id`, `administrador_id`, `fecha_hora`.
  - Columnas `bloqueado` (bool, default false) y `forzar_cambio_password` (bool, default false)
    en tabla `users`.
  - 4 nuevos permisos: `ver-administracion-passwords`, `restablecer-passwords`,
    `bloquear-usuarios`, `desbloquear-usuarios`. Asignados a Administrador y Rector.
  - Componentes integrados en `UserIndex` (desktop + móvil), visibles según permisos.
  - Suite de pruebas `PasswordAdminTest` (8 tests, V-001 → V-008): todos PASS.
  - 15 tests previos sin regresiones.

> **Nota:** El módulo se llamaba `Users` hasta v1.1.1. En v2.0.0 fue renombrado
> a `User` con nueva estructura modular. Las entradas anteriores a v2.0.0 se
> conservan bajo el nombre histórico `Users` para fidelidad con el historial.

---

## v2.2.2 — 2026-06-11

### Added

- **[IMPL-CORE-CLEANUP-001 Fase 2]** Factories propias para el módulo User.
  - `Modules/User/Database/Factories/UserFactory` — genera usuarios con campos reales del schema
    (`nombres`, `apellidos`, `userID`, `email`, `role_id`), `Hash::make('password')` compatible
    con `BCRYPT_ROUNDS=4` de phpunit.xml, método `withPersonalTeam()` para compatibilidad con
    pruebas Jetstream cuando Teams está desactivado.
  - `Modules/User/Database/Factories/RoleFactory` — crea roles sin dependencia de App
    (`app_id` nullable tras migración v2026_06_08).
  - `Modules\User\Entities\User::newFactory()` y `Role::newFactory()` apuntan a las factories
    del módulo.

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
