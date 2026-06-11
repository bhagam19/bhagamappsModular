# IMPL-USERS-001 — Password Administration & Recovery

**Estado:** COMPLETADO  
**Fecha:** 2026-06-11  
**Versiones:** IEE v1.14.0 | BhagamApps v1.14.0 | User v2.3.0  
**Rama:** main  
**Autorizado por:** PMO  

---

## Origen

Derivado de AUDIT-IEE-001 e IMPL-CORE-CLEANUP-001.  
Prerequisitos cumplidos: Fortify operativo, Jetstream operativo, Password Reset operativo, User Model estabilizado, tests funcionales.

---

## Alcance implementado

### USR-001 — Restablecimiento Administrativo

- Livewire `GestionPasswordUser` permite a Administrador y Rector restablecer contraseñas de cualquier usuario.
- Incluye generador de contraseña aleatoria (icono `fa-random`).
- Modal Bootstrap por usuario integrado en `UserIndex`.
- Se muestra contraseña generada en texto (solo al administrador, temporalmente visible).
- No se almacenan contraseñas en texto plano en ningún momento.

### USR-002 — Cambio Obligatorio

- Campo `forzar_cambio_password` (boolean, default false) añadido a tabla `users`.
- Checkbox opcional en el modal de restablecimiento.
- Middleware `CheckForzarCambioPassword` en el grupo `web` intercepta todas las rutas protegidas y redirige a `/user/profile`.
- Al cambiar la contraseña desde el perfil (`UpdateUserPassword` action), el flag se limpia automáticamente.

### USR-003 — Gestión de Estado

- Campo `bloqueado` (boolean, default false) añadido a tabla `users`.
- Livewire `GestionEstadoUser` permite bloquear/desbloquear usuarios.
- Fortify `authenticateUsing` niega autenticación si `bloqueado = true`.
- Acciones visibles condicionalmente según permisos.

### USR-004 — Auditoría

- Tabla `auditoria_passwords` creada con: `usuario_afectado_id`, `administrador_id`, `accion` (enum), `fecha_hora`.
- Acciones auditadas: `password_reset`, `password_forced`, `user_blocked`, `user_unblocked`.
- Registro automático en cada operación.

### USR-005 — Interfaz

- Componentes `GestionPasswordUser` y `GestionEstadoUser` integrados en la columna Acciones de `UserIndex`.
- Visibles solo para usuarios con los permisos correspondientes.
- Coherente con AdminLTE + Livewire existente.

---

## Seguridad

| Regla | Implementación |
|-------|----------------|
| SEC-001 No texto plano | Solo `Hash::make()` al persistir |
| SEC-002 Hash::make() | `Hash::make()` en `GestionPasswordUser` |
| SEC-003 Permisos antes de acción | `abort_unless()` en `mount()` y métodos de escritura |
| SEC-004 Sin gestión no autorizada | Verificación por permiso en cada componente |

---

## RBAC

### Permisos creados

| Slug | Categoría | Descripción |
|------|-----------|-------------|
| `ver-administracion-passwords` | administracion-passwords | Ver panel de administración |
| `restablecer-passwords` | administracion-passwords | Restablecer contraseña de usuarios |
| `bloquear-usuarios` | administracion-passwords | Bloquear cuenta de usuario |
| `desbloquear-usuarios` | administracion-passwords | Desbloquear cuenta de usuario |

### Asignación por rol

| Rol | Permisos |
|-----|----------|
| Administrador | todos (4) |
| Rector | todos (4) |
| Coordinador | ninguno |

---

## Migraciones

| Archivo | Descripción |
|---------|-------------|
| `2026_06_11_100000_add_password_management_columns_to_users_table.php` | Columnas `bloqueado`, `forzar_cambio_password` en `users` |
| `2026_06_11_100001_create_auditoria_passwords_table.php` | Tabla `auditoria_passwords` |
| `2026_06_11_100002_add_password_permissions.php` | 4 permisos + asignación a Administrador y Rector |

---

## Archivos creados / modificados

### Creados

```
Modules/User/Database/Migrations/2026_06_11_100000_*.php
Modules/User/Database/Migrations/2026_06_11_100001_*.php
Modules/User/Database/Migrations/2026_06_11_100002_*.php
Modules/User/Entities/AuditoriaPassword.php
Modules/User/Http/Middleware/CheckForzarCambioPassword.php
Modules/User/Livewire/Password/GestionPasswordUser.php
Modules/User/Livewire/Password/GestionEstadoUser.php
Modules/User/Resources/views/livewire/password/gestion-password-user.blade.php
Modules/User/Resources/views/livewire/password/gestion-estado-user.blade.php
tests/Feature/User/PasswordAdminTest.php
docs/impl/IMPL-USERS-001-Password-Administration-And-Recovery.md
```

### Modificados

```
Modules/User/Entities/User.php            — fillable + casts
Modules/User/Providers/UserServiceProvider.php — registro Livewire
Modules/User/Resources/views/livewire/user/user-index.blade.php — botones
Modules/User/Database/Seeders/data/permissions.csv — 4 nuevos permisos
Modules/User/Database/Seeders/data/permission_role.csv — asignaciones
app/Actions/Fortify/UpdateUserPassword.php — limpia forzar_cambio_password
app/Providers/FortifyServiceProvider.php   — authenticateUsing bloqueado
app/Http/Kernel.php                        — alias forzar.cambio.pass (legacy)
bootstrap/app.php                          — grupo web + alias middleware
config/versiones.php                       — versiones actualizadas
CHANGELOG.md                               — entrada v1.14.0
VERSIONING.md                              — tabla actualizada
docs/changelog/iee.md                      — entrada v1.14.0
docs/changelog/bhagamapps.md              — entrada v1.14.0
docs/changelog/user.md                     — entrada v2.3.0
```

---

## Tests ejecutados

| ID | Descripción | Resultado |
|----|-------------|-----------|
| V-001 | Administrador puede restablecer contraseña | ✓ PASS |
| V-002 | Rector puede restablecer contraseña | ✓ PASS |
| V-003 | Coordinador NO puede restablecer contraseña | ✓ PASS |
| V-004 | Usuario bloqueado no puede autenticarse | ✓ PASS |
| V-005 | Usuario desbloqueado puede autenticarse | ✓ PASS |
| V-006 | Forzar cambio de contraseña redirige | ✓ PASS |
| V-007 | Auditoría registrada para las 4 acciones | ✓ PASS |
| V-008 | Sin regresiones en login | ✓ PASS |

**Tests previos sin regresión:** AuthenticationTest (3), BrowserSessionsTest (1), DeleteAccountTest (2), PasswordResetTest (4), UpdatePasswordTest (3), ProfileInformationTest (2) — 15 tests, todos PASS.
