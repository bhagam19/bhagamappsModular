# IMPL-AUTH-002 — Authorization Hardcoded Role Elimination

**Fecha:** 2026-06-08
**Estado:** Ejecutado
**Prioridad:** Media
**Relacionado con:** AUDIT-AUTH-001A, PLAN-AUTH-001, ADR-008, ADR-AUTHORIZATION-002, IMPL-AUTH-001

---

## 1. Hallazgos confirmados

### H-009 — Gates hardcoded en AuthServiceProvider (4 instancias)

| Gate | Patrón anterior | Riesgo |
|------|----------------|--------|
| `usuarios.user` | `in_array($user->role->nombre, ['Administrador', 'Rector', 'Coordinador'])` | Alto acoplamiento |
| `admin.grupos` | `in_array($user->role->nombre, ['Administrador', 'Rector', 'Coordinador'])` | Alto acoplamiento |
| `admin.evaldoc` | `in_array($user->role->nombre, ['Administrador', 'Rector', 'Coordinador'])` | Alto acoplamiento |
| `admin.biblioteca` | `in_array($user->role->nombre, ['Administrador', 'Rector', 'Coordinador'])` | Alto acoplamiento |

Todos los gates eran consumidos exclusivamente en `config/adminlte.php` como
visibilidad de secciones del menú lateral. Ninguno protegía rutas o métodos
Livewire directamente.

### Hallazgos adicionales verificados (fuera de alcance)

- `Modules/Inventario/Livewire/Bienes/BienesIndex.php`: múltiples `hasRole()` en
  lógica de consulta y aprobación (H-002 — requiere decisión de negocio).
- `Modules/Inventario/Livewire/Bienes/EditarDetalleBien.php`: `hasRole()` e
  `in_array(role->nombre)` en lógica de aprobación (H-002).
- `Modules/Inventario/Livewire/Bienes/EditarCampoBien.php`: misma naturaleza (H-002).

Estos hallazgos se mantienen excluidos por la misma razón que en IMPL-AUTH-001:
pertenecen a lógica de negocio de flujos de aprobación con restricciones semánticas
propias, no a la capa de autorización de acceso. Requieren decisión de PMO sobre
modelo de aprobación.

---

## 2. Permisos creados

| ID | Slug | Nombre | Categoría |
|----|------|--------|-----------|
| 33 | `ver-grupos` | ver grupos | grupos |
| 34 | `ver-evaluacion-docente` | ver evaluación docente | evaluacion-docente |
| 35 | `ver-biblioteca` | ver biblioteca | biblioteca |

El permiso `ver-usuarios` (id=1) ya existía. Se extendió su asignación al rol
Coordinador (role_id=3) para mantener equivalencia funcional con el gate anterior.

---

## 3. Asignaciones a roles

| Permiso | Administrador (1) | Rector (2) | Coordinador (3) |
|---------|:-----------------:|:----------:|:---------------:|
| ver-usuarios (id=1) | ✓ ya existía | ✓ ya existía | ✓ **agregado** |
| ver-grupos (id=33) | ✓ nuevo | ✓ nuevo | ✓ nuevo |
| ver-evaluacion-docente (id=34) | ✓ nuevo | ✓ nuevo | ✓ nuevo |
| ver-biblioteca (id=35) | ✓ nuevo | ✓ nuevo | ✓ nuevo |

---

## 4. Correcciones en código

### `app/Providers/AuthServiceProvider.php`

Eliminado import `use Modules\User\Entities\User` (quedó huérfano al remover
todos los `instanceof User` checks).

```php
// Antes — usuarios.user
Gate::define('usuarios.user', function ($user) {
    return $user instanceof User
        && in_array($user->role->nombre, ['Administrador', 'Rector', 'Coordinador']);
});

// Después
Gate::define('usuarios.user', function ($user) {
    return $user->hasPermission('ver-usuarios');
});
```

```php
// Antes — admin.grupos
Gate::define('admin.grupos', function ($user) {
    return $user instanceof User
        && in_array($user->role->nombre, ['Administrador', 'Rector', 'Coordinador']);
});

// Después
Gate::define('admin.grupos', function ($user) {
    return $user->hasPermission('ver-grupos');
});
```

```php
// admin.evaldoc y admin.biblioteca — mismo patrón
Gate::define('admin.evaldoc', function ($user) {
    return $user->hasPermission('ver-evaluacion-docente');
});

Gate::define('admin.biblioteca', function ($user) {
    return $user->hasPermission('ver-biblioteca');
});
```

---

## 5. Validación posterior

### Comportamiento equivalente (sin regresión)

| Escenario | Antes | Después |
|-----------|-------|---------|
| Administrador ve "Gestión de Accesos" en menú | Sí (`in_array`) | Sí (`ver-usuarios` asignado) |
| Rector ve "Gestión de Accesos" en menú | Sí | Sí |
| Coordinador ve "Gestión de Accesos" en menú | Sí | Sí (`ver-usuarios` añadido a Coordinador) |
| Administrador ve "Grupos" en menú | Sí | Sí (`ver-grupos` asignado) |
| Coordinador ve "Grupos" en menú | Sí | Sí |
| Docente/Estudiante NO ven secciones admin | No | No |
| Remoción del nombre "Administrador" no rompe gate | No (atado a string) | Sí (atado a permiso) |

### Sin cambios en comportamiento funcional

Los módulos Grupos, Evaluación Docente y Biblioteca tienen rutas vacías en la
configuración del menú (`'route' => ''`) — son stubs no implementados. El cambio
de gate no afecta la funcionalidad porque no existe funcionalidad aún.

---

## 6. Archivos modificados

| Archivo | Tipo de cambio |
|---------|---------------|
| `app/Providers/AuthServiceProvider.php` | 4 gates reemplazados, import eliminado |
| `database/migrations/2026_06_09_000002_add_auth002_permissions.php` | Nueva migración |
| `Modules/User/Database/Seeders/data/permissions.csv` | 3 nuevas filas (ids 33–35) |
| `Modules/User/Database/Seeders/data/permission_role.csv` | 10 nuevas asignaciones (ids 186–195) |

---

## 7. Impacto

### Compatibilidad

- Sin cambios en modelos.
- Sin cambios en middleware.
- Sin cambios en la arquitectura de tres capas (ADR-008).
- Sin cambios en `app_role`, `App::visiblesPara()`, `CheckAppAccess`.
- El nombre de los gates (`usuarios.user`, `admin.grupos`, etc.) no cambió —
  los consumidores en `adminlte.php` no requieren modificación.

### Riesgo de regresión

**Bajo.** El cambio es una sustitución 1:1 de la condición dentro de cada gate.
Los gates expuestos al exterior mantienen el mismo nombre. La única diferencia
observable es que ahora el acceso depende del permiso efectivo del usuario en
lugar de su nombre de rol.

---

## 8. Hallazgos NO corregidos en esta implementación

| ID | Descripción | Razón |
|----|-------------|-------|
| H-002 | Flujos de aprobación con roles hardcoded en Inventario | Requiere decisión de negocio. |
| H-005 | `EditarSlugApp` no invalida caché | Pendiente implementación separada. |
| H-006 | `hasPermission()` sin caché | Fuera del alcance. |
| H-008 | Menú sidebar desincronizado de `App::visiblesPara()` | Pendiente IMPL-013. |

---

## 9. Versionado

| Componente | Versión anterior | Versión nueva |
|------------|-----------------|---------------|
| BhagamApps (plataforma) | v1.6.1 | v1.6.2 |
| User | v2.2.0 | v2.2.1 |

Archivos actualizados:
- `CHANGELOG.md`
- `VERSIONING.md`
- `config/versiones.php`
- `docs/changelog/bhagamapps.md`
- `docs/changelog/user.md`
