# IMPL-RBAC-002 — Jerarquía Institucional y Protección del Administrador Principal

**Fecha:** 2026-06-12
**Módulo:** User
**Versión:** 2.5.0
**SHA base:** 9c52dc5

---

## Objetivo

Proteger al Administrador Principal (primer usuario con rol Administrador, identificado por `es_principal=true`) para que ningún otro usuario —incluidos otros Administradores— pueda modificar sus datos, cambiar su contraseña, bloquear su cuenta o eliminarlo.

---

## Entregables

### RBAC-001 — Columna `es_principal`

**Migración:** `2026_06_12_000011_add_es_principal_to_users_table.php`

- Agrega `boolean es_principal` (default `false`) a la tabla `users`.
- La migración marca automáticamente como principal al primer usuario con rol `Administrador` ordenado por `id`.
- Resultado en producción: usuario id=54 (Adolfo León Ruiz Hernández) marcado como `es_principal=true`.

### RBAC-002 — Método `isAdminPrincipal()` en entidad User

**Archivo:** `Modules/User/Entities/User.php`

```php
public function isAdminPrincipal(): bool
{
    return (bool) $this->es_principal;
}
```

`es_principal` añadido a `$fillable` y `$casts`.

### RBAC-003 — Trait `ProteccionAdminPrincipal`

**Archivo:** `Modules/User/Traits/ProteccionAdminPrincipal.php`

Centraliza la lógica de protección: registra el intento en `auditoria_passwords` y aborta con 403. Así no se duplica la lógica en cada componente.

```php
protected function verificarNoEsAdminPrincipal(User $target, string $accion): void
```

### RBAC-004 — Protección backend en componentes Livewire

Trait aplicado con llamada a `verificarNoEsAdminPrincipal()` en los siguientes componentes:

| Componente | Métodos protegidos | Acción registrada |
|---|---|---|
| `UserIndex` | `delete()` | `intento_eliminar_admin_principal` |
| `EditarNombresUser` | `editar()`, `guardar()` | `intento_editar_admin_principal` |
| `EditarApellidosUser` | `editar()`, `guardar()` | `intento_editar_admin_principal` |
| `EditarEmailUser` | `editar()`, `guardar()` | `intento_editar_admin_principal` |
| `EditarUserIDUser` | `editar()`, `guardar()` | `intento_editar_admin_principal` |
| `EditarRolUser` | `editar()`, `guardar()` | `intento_editar_admin_principal` |
| `GestionPasswordUser` | `mount()`, `restablecer()` | `intento_restablecer_password_admin_principal` |
| `GestionEstadoUser` | `bloquear()`, `desbloquear()` | `intento_bloquear/desbloquear_admin_principal` |

### RBAC-005 — Jerarquía de roles (V-001, V-002)

La jerarquía de acceso ya existía a través del sistema RBAC de permisos:

- **Administrador**: acceso completo (salvo Admin Principal).
- **Rectoría**: acceso limitado por permisos asignados a su rol.
- **Coordinación / Auxiliar**: sin acceso al módulo de usuarios.

### RBAC-006 — Protección visual en `user-index.blade.php`

Para la fila del Administrador Principal:

- **Campos editables** (nombres, apellidos, userID, rol, email): se muestran como texto estático (no se cargan los componentes Livewire de edición).
- **Columna Acciones**: en lugar de botones (gestion-password, gestion-estado, eliminar), se muestran:
  - `<span class="badge badge-dark">Administrador Principal</span>`
  - `<span class="badge badge-warning">Protegido</span>`
- **Vista móvil**: badge `Administrador Principal` junto al nombre; badge `Protegido` en lugar de botones de acción.

---

## Validaciones

- **V-001**: Rectoría no puede modificar al Admin Principal → protegido por `verificarNoEsAdminPrincipal` en todos los componentes.
- **V-002**: Rectoría puede editar usuarios normales → sin cambios en su flujo normal.
- **V-003**: Administrador no puede eliminar al Admin Principal → `delete()` en `UserIndex` protegido.
- **V-004**: Administrador no puede cambiar la contraseña del Admin Principal → `GestionPasswordUser::mount()` y `restablecer()` protegidos.
- **V-005**: Administrador no puede bloquear al Admin Principal → `GestionEstadoUser::bloquear()` protegido.
- **V-006**: Intentos bloqueados se registran en `auditoria_passwords` con acción específica.

---

## Deuda técnica

- DT-RBAC-001: No existe mecanismo para transferir la condición de Admin Principal a otro usuario (operación de administración de sistema pendiente para una versión futura).
- DT-RBAC-002: Tests automatizados pendientes para este módulo (ver DT-004 heredada de IMPL-INV-007).
