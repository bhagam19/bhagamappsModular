# IMPLEMENTATION-BACKLOG-CORE-FOUNDATION

**Documento:** IMPL-CORE-001 — Backlog Ejecutable del Core Foundation
**Versión:** 1.0.0
**Estado:** Aprobado — Vigente
**Fecha:** 2026-06-14
**Autor:** Ingeniero Principal de Implementación
**Autoridad:** ARCH-001 · ADR-001 · ADR-002 · ADR-003 · ADR-004 · ADR-005
**Prerequisito:** CORE-FOUNDATION-IMPLEMENTATION-PLAN.md leído y validado

---

## Convenciones

```
EPIC   → Componente CORE completo
FEAT   → Feature dentro del EPIC
TASK   → Tarea técnica ejecutable
SUB    → Subtarea o paso concreto

Prioridad:
  P0 = Bloqueante (nadie puede continuar sin esto)
  P1 = Crítico (bloquea el siguiente EPIC)
  P2 = Importante (necesario para completitud del EPIC)
  P3 = Complementario (mejora calidad pero no bloquea)

Estimación en horas de trabajo real (no tiempo de reloj).
```

---

## EPIC-000 — Prerequisitos Técnicos

**Objetivo:** Resolver los bloqueantes antes de iniciar el CORE Foundation.
**Estimación total:** 3-5 horas
**Criterio de éxito:** `SincronizarRolesYPermisosCoreAction` puede ejecutarse sin errores de clase.

---

### FEAT-000-A — Instalar Spatie Permission

| Campo | Valor |
|-------|-------|
| Prioridad | P0 |
| Dependencias | Ninguna |
| Estimación | 1 hora |
| Criterio de aceptación | `vendor/spatie` existe; `config/permission.php` publicado; migration de Spatie en `database/migrations/` |

**TASK-000-A-1:** Instalar el paquete
```bash
composer require spatie/laravel-permission
```
- Verificar que no hay conflictos de versión con Laravel 11
- El paquete spatie/laravel-permission ^6 es compatible con Laravel 11

**TASK-000-A-2:** Publicar configuración y migrations
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```
- Confirmar que `config/permission.php` fue creado
- Confirmar que la migration de Spatie fue copiada a `database/migrations/`

**TASK-000-A-3:** Configurar `config/permission.php`
- Cambiar `model_has_permissions.user_type` si usa morphs polimórficos
- Verificar que `guard_name` está configurado como `web`
- Verificar que `teams` está en `false` (ADR-003: sin teams)

**SUB-000-A-3-1:** Editar `config/permission.php`:
```php
'models' => [
    'permission' => Spatie\Permission\Models\Permission::class,
    'role'       => Spatie\Permission\Models\Role::class,
],
'table_names' => [
    'roles'                 => 'roles',       // Atención: conflicto con tabla legacy
    'permissions'           => 'permissions', // Atención: conflicto con tabla legacy
    'model_has_permissions' => 'model_has_permissions',
    'model_has_roles'       => 'model_has_roles',
    'role_has_permissions'  => 'role_has_permissions',
],
'teams' => false,
```

**TASK-000-A-4:** Verificar conflicto de nombres de tablas
- La migration de Spatie crea `roles` y `permissions`
- Ya existen tablas `roles` y `permissions` (legacy custom)
- **Estrategia:** Renombrar tablas legacy ANTES de migrar Spatie (ver EPIC-001 FEAT-001-C)

---

### FEAT-000-B — Crear RolInstitucional enum

| Campo | Valor |
|-------|-------|
| Prioridad | P0 |
| Dependencias | Ninguna |
| Estimación | 30 minutos |
| Criterio de aceptación | `app/Auth/RolInstitucional.php` existe; `php artisan tinker` puede instanciar el enum |

**TASK-000-B-1:** Crear el archivo `app/Auth/RolInstitucional.php`
```php
<?php
namespace App\Auth;

enum RolInstitucional: string
{
    case Administrador = 'administrador';
    case Rector        = 'rector';
    case Coordinador   = 'coordinador';
    case Auxiliar      = 'auxiliar';
    case Docente       = 'docente';
    case Estudiante    = 'estudiante';
    case Invitado      = 'invitado';
}
```

**TASK-000-B-2:** Verificar importación en `SincronizarRolesYPermisosCoreAction`
- El import `use App\Auth\RolInstitucional;` debe resolver sin error
- Ejecutar `php artisan tinker --execute="echo App\Auth\RolInstitucional::Administrador->value;"`

---

### FEAT-000-C — Decisión de versión Laravel

| Campo | Valor |
|-------|-------|
| Prioridad | P1 |
| Dependencias | Ninguna |
| Estimación | 30 minutos (decisión) |
| Criterio de aceptación | Decisión documentada en CLAUDE.md o ADR |

**TASK-000-C-1:** Documentar decisión de versión
- Opción A: Continuar en Laravel 11.44.7 (recomendado)
- Opción B: Upgrade a Laravel 13 antes de continuar
- Si Opción A: actualizar referencia en ARCH-001 o crear ADR-006 que documente el plan de upgrade

---

## EPIC-001 — CORE-1: Users

**Objetivo:** Formalizar la gestión de usuarios eliminando dependencias ilegales y preparando para Spatie.
**Estimación total:** 4-6 horas
**Criterio de éxito:** User model sin imports de Inventario; HasRoles de Spatie incluido; tests pasan.

---

### FEAT-001-A — Limpiar User model

| Campo | Valor |
|-------|-------|
| Prioridad | P0 |
| Dependencias | EPIC-000 completado |
| Estimación | 2 horas |
| Criterio de aceptación | `User.php` no tiene `use Modules\Inventario\*`; sistema sigue funcionando |

**TASK-001-A-1:** Auditar qué código usa los métodos ilegales del User model
```bash
grep -rn "->dependencias()\|->bienesAsignados()\|->bienes()" \
  app/ Modules/ resources/ \
  --include="*.php" --include="*.blade.php"
```

**TASK-001-A-2:** Por cada uso encontrado, crear alternativa sin dependencia
- Si un Livewire component de Inventario usa `auth()->user()->bienesAsignados()`:
  reemplazar por `BienResponsable::where('user_id', auth()->id())->whereNull('fecha_retiro')->get()`
  dentro del propio componente de Inventario

**TASK-001-A-3:** Eliminar imports ilegales de `User.php`:
```php
// Eliminar:
use Modules\Inventario\Entities\Bien;
use Modules\Inventario\Entities\Dependencia;
```

**TASK-001-A-4:** Eliminar métodos que usan esos imports:
- `dependencias()` — eliminado
- `bienesAsignados()` — eliminado
- `bienes()` — eliminado
- `apps()` — evaluar si aún se necesita con CORE-003

**TASK-001-A-5:** Ejecutar suite de tests y verificar 0 regresiones
```bash
php artisan test --filter=Feature
```

---

### FEAT-001-B — Preparar User model para Spatie

| Campo | Valor |
|-------|-------|
| Prioridad | P1 |
| Dependencias | FEAT-000-A (Spatie instalado) |
| Estimación | 1 hora |
| Criterio de aceptación | User model tiene `HasRoles` trait; `php artisan test` no muestra errores |

**TASK-001-B-1:** Agregar HasRoles trait al User model:
```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    // ... resto de traits
}
```

**TASK-001-B-2:** Verificar que el trait no entra en conflicto con el método `hasPermission()` custom
- Si hay conflicto de métodos, renombrar el método custom a `hasPermissionLegacy()` temporalmente
- El método Spatie se llama `hasPermissionTo()` — no hay conflicto directo

---

### FEAT-001-C — Renombrar tablas legacy RBAC

| Campo | Valor |
|-------|-------|
| Prioridad | P0 |
| Dependencias | EPIC-000 completado |
| Estimación | 1 hora |
| Criterio de aceptación | Tablas legacy renombradas; Spatie puede crear sus tablas sin conflicto |

**TASK-001-C-1:** Crear migration para renombrar tablas legacy:
```php
Schema::rename('roles', 'roles_legacy');
Schema::rename('permissions', 'permissions_legacy');
Schema::rename('permission_role', 'permission_role_legacy');
Schema::rename('permission_user', 'permission_user_legacy');
```

**TASK-001-C-2:** Actualizar todos los modelos legacy para apuntar a las tablas renombradas:
- `Role` model: `protected $table = 'roles_legacy';`
- `Permission` model: `protected $table = 'permissions_legacy';`

**TASK-001-C-3:** Verificar que el sistema sigue funcionando con tablas renombradas

**TASK-001-C-4:** Ejecutar migration de Spatie (ahora sin conflicto):
```bash
php artisan migrate
```

---

### FEAT-001-D — Tests CORE-1

| Campo | Valor |
|-------|-------|
| Prioridad | P2 |
| Dependencias | FEAT-001-A, FEAT-001-B |
| Estimación | 1.5 horas |
| Criterio de aceptación | 4 tests nuevos pasan |

**TASK-001-D-1:** Crear `tests/Feature/Core/UserModelTest.php`
**TASK-001-D-2:** Crear `tests/Feature/Core/AdminPrincipalTest.php`
**TASK-001-D-3:** Crear `tests/Unit/Core/CrossModuleDependencyTest.php`
**TASK-001-D-4:** Crear `tests/Feature/Core/AuditoriaPasswordTest.php`

---

## EPIC-002 — CORE-2: Authorization

**Objetivo:** Reemplazar RBAC custom por Spatie. Reducir gates. Establecer autorización formal.
**Estimación total:** 8-12 horas
**Criterio de éxito:** 7 roles Spatie sembrados; 3 gates en AuthServiceProvider; módulo Inventario funciona con `can:`.

---

### FEAT-002-A — Completar SincronizarRolesYPermisosCoreAction

| Campo | Valor |
|-------|-------|
| Prioridad | P0 |
| Dependencias | EPIC-001 completado; Spatie instalado y migrado |
| Estimación | 2 horas |
| Criterio de aceptación | Action ejecuta sin error; 7 roles y todos los permisos de core existen en BD |

**TASK-002-A-1:** Revisar y completar `SincronizarRolesYPermisosCoreAction`
- Verificar que `Capacidad::permisosCore()` retorna todos los permisos necesarios
- Verificar la matriz rol×permiso (qué permisos tiene cada rol)
- Agregar permisos de Inventario al catálogo del Capacidad enum (si no están)

**TASK-002-A-2:** Crear `database/seeders/CorePermissionSeeder.php`:
```php
class CorePermissionSeeder extends Seeder {
    public function run(): void {
        (new SincronizarRolesYPermisosCoreAction())->execute();
    }
}
```

**TASK-002-A-3:** Ejecutar seeder y verificar:
```bash
php artisan db:seed --class=CorePermissionSeeder
```

---

### FEAT-002-B — Rediseñar AuthServiceProvider (3 gates)

| Campo | Valor |
|-------|-------|
| Prioridad | P1 |
| Dependencias | FEAT-002-A |
| Estimación | 3 horas |
| Criterio de aceptación | `AuthServiceProvider` tiene solo 3 gates; todas las rutas de Inventario funcionan con `can:` |

**TASK-002-B-1:** Auditar qué gates actualmente se usan en rutas y componentes:
```bash
grep -rn "can:\|@can\|Gate::allows\|->authorize\|middleware('permission" \
  app/ Modules/ resources/ \
  --include="*.php" --include="*.blade.php" | grep -v vendor
```

**TASK-002-B-2:** Por cada gate que desaparece, verificar que la ruta usa `can:{slug}` con el valor del Capacidad enum correspondiente

**TASK-002-B-3:** Reescribir `AuthServiceProvider::boot()` con 3 gates

**TASK-002-B-4:** Agregar `Gate::before()` para AdminPrincipal

**TASK-002-B-5:** Ejecutar suite completa de tests

---

### FEAT-002-C — Migración de datos RBAC

| Campo | Valor |
|-------|-------|
| Prioridad | P1 |
| Dependencias | FEAT-001-C (tablas renombradas); FEAT-002-A (Spatie sembrado) |
| Estimación | 2 horas |
| Criterio de aceptación | Todos los usuarios tienen sus roles Spatie asignados correctamente |

**TASK-002-C-1:** Crear script de migración de datos:
```php
// Migrar roles legacy a Spatie
$usersLegacy = DB::table('users')->select('id', 'role_id')->get();
foreach ($usersLegacy as $user) {
    $roleLegacy = DB::table('roles_legacy')->find($user->role_id);
    if ($roleLegacy) {
        $spatiRole = Role::findByName($roleLegacy->nombre);
        User::find($user->id)?->assignRole($spatiRole);
    }
}
```

**TASK-002-C-2:** Ejecutar en ambiente de desarrollo y verificar

**TASK-002-C-3:** Ejecutar en producción con backup previo

**TASK-002-C-4:** Eliminar tablas legacy (después de validar):
```bash
Schema::drop('roles_legacy');
Schema::drop('permissions_legacy');
Schema::drop('permission_role_legacy');
Schema::drop('permission_user_legacy');
```

---

### FEAT-002-D — Actualizar middleware de autorización

| Campo | Valor |
|-------|-------|
| Prioridad | P2 |
| Dependencias | FEAT-002-A |
| Estimación | 1 hora |
| Criterio de aceptación | Rutas de todos los módulos usan `can:` o `->middleware('can:slug')` |

**TASK-002-D-1:** Reemplazar `->middleware('permission:slug')` por `->middleware('can:slug')` en todas las rutas
**TASK-002-D-2:** Verificar que `CheckPermission` middleware ya no es necesario
**TASK-002-D-3:** Deprecar `CheckPermission` (o eliminar si no se usa)

---

### FEAT-002-E — Tests CORE-2

| Campo | Valor |
|-------|-------|
| Prioridad | P2 |
| Dependencias | FEAT-002-A, FEAT-002-B |
| Estimación | 2 horas |
| Criterio de aceptación | 6 tests pasan |

**TASK-002-E-1:** `tests/Feature/Core/SpatieRolesExistTest.php`
**TASK-002-E-2:** `tests/Feature/Core/CorePermissionsExistTest.php`
**TASK-002-E-3:** `tests/Feature/Core/RolMatrixTest.php`
**TASK-002-E-4:** `tests/Feature/Core/AdminPrincipalBypassTest.php`
**TASK-002-E-5:** `tests/Feature/Core/BlockedUserTest.php`
**TASK-002-E-6:** `tests/Unit/Core/SpatieCacheTest.php`

---

## EPIC-003 — CORE-3: Modules

**Objetivo:** Implementar el ciclo de vida formal de módulos APPSisGOE.
**Estimación total:** 10-14 horas
**Criterio de éxito:** Tabla `modules` con 6 estados; `ModuleVisibilityService` funcional; datos migrados de `apps`.

---

### FEAT-003-A — Tabla modules y pivots

| Campo | Valor |
|-------|-------|
| Prioridad | P0 |
| Dependencias | EPIC-002 completado |
| Estimación | 2 horas |
| Criterio de aceptación | Tablas `modules`, `module_role`, `module_user` migradas |

**TASK-003-A-1:** Crear migration `create_modules_table`
**TASK-003-A-2:** Crear migration `create_module_role_pivot`
**TASK-003-A-3:** Crear migration `create_module_user_pivot`
**TASK-003-A-4:** Ejecutar `php artisan migrate`

---

### FEAT-003-B — Module model y lifecycle Actions

| Campo | Valor |
|-------|-------|
| Prioridad | P1 |
| Dependencias | FEAT-003-A |
| Estimación | 3 horas |
| Criterio de aceptación | `Module` model existe; 6 Actions implementadas |

**TASK-003-B-1:** Crear `app/Models/Module.php` con ENUM states y relaciones
**TASK-003-B-2:** Crear `app/Actions/Core/RegistrarModuloAction.php`
**TASK-003-B-3:** Crear `app/Actions/Core/InstalarModuloAction.php`
**TASK-003-B-4:** Crear `app/Actions/Core/ActivarModuloAction.php`
**TASK-003-B-5:** Crear `app/Actions/Core/DesactivarModuloAction.php`
**TASK-003-B-6:** Crear `app/Actions/Core/DesinstalarModuloAction.php`
**TASK-003-B-7:** Crear `app/Actions/Core/SincronizarModulosAction.php`

---

### FEAT-003-C — ModuleVisibilityService

| Campo | Valor |
|-------|-------|
| Prioridad | P1 |
| Dependencias | FEAT-003-A |
| Estimación | 2 horas |
| Criterio de aceptación | `ModuleVisibilityService::visiblesPara($user)` retorna mismos módulos que `App::visiblesPara()` |

**TASK-003-C-1:** Crear `app/Services/ModuleVisibilityService.php`
**TASK-003-C-2:** Implementar cache versioned
**TASK-003-C-3:** Registrar en `AppServiceProvider` como singleton

---

### FEAT-003-D — Middleware modulo.access

| Campo | Valor |
|-------|-------|
| Prioridad | P1 |
| Dependencias | FEAT-003-C |
| Estimación | 1 hora |
| Criterio de aceptación | Middleware registrado; rutas de módulos lo usan |

**TASK-003-D-1:** Crear `app/Http/Middleware/ModuloAccess.php`
**TASK-003-D-2:** Registrar en `bootstrap/app.php` como alias `modulo.access`
**TASK-003-D-3:** Actualizar rutas de módulos de `app.access:slug` a `modulo.access:key`

---

### FEAT-003-E — Actualizar module.json y migrar datos de apps

| Campo | Valor |
|-------|-------|
| Prioridad | P2 |
| Dependencias | FEAT-003-A |
| Estimación | 2 horas |
| Criterio de aceptación | Todos los `module.json` tienen formato APPSisGOE; datos de `apps` en `modules` |

**TASK-003-E-1:** Actualizar `module.json` de Inventario
**TASK-003-E-2:** Actualizar `module.json` de User
**TASK-003-E-3:** Actualizar `module.json` de ActivityLog
**TASK-003-E-4:** Actualizar `module.json` de Apps
**TASK-003-E-5:** Actualizar `module.json` de AdminSistema
**TASK-003-E-6:** Crear y ejecutar `SincronizarModulosAction` para poblar tabla `modules`

---

### FEAT-003-F — Tests CORE-3

| Campo | Valor |
|-------|-------|
| Prioridad | P2 |
| Dependencias | FEAT-003-A, FEAT-003-C, FEAT-003-D |
| Estimación | 2 horas |
| Criterio de aceptación | 6 tests pasan |

**TASK-003-F-1:** `tests/Feature/Core/ModuleTableExistsTest.php`
**TASK-003-F-2:** `tests/Feature/Core/ModuleLifecycleTest.php`
**TASK-003-F-3:** `tests/Feature/Core/ModuleVisibilityTest.php`
**TASK-003-F-4:** `tests/Feature/Core/ModuleVisibilityCacheTest.php`
**TASK-003-F-5:** `tests/Feature/Core/ModuloAccessMiddlewareTest.php`
**TASK-003-F-6:** `tests/Unit/Core/ModuleJsonFormatTest.php`

---

## EPIC-004 — CORE-4: Audit

**Objetivo:** Formalizar ActivityLogger como servicio CORE.
**Estimación total:** 3-5 horas
**Criterio de éxito:** `ActivityLogger` accesible globalmente; inmutabilidad garantizada.

---

### FEAT-004-A — Mover ActivityLogger al CORE

| Campo | Valor |
|-------|-------|
| Prioridad | P1 |
| Dependencias | EPIC-001 completado |
| Estimación | 2 horas |
| Criterio de aceptación | `App\Services\Core\ActivityLogger::log()` funciona; todos los módulos lo usan |

**TASK-004-A-1:** Copiar `ActivityLogger.php` a `app/Services/Core/ActivityLogger.php`
**TASK-004-A-2:** Actualizar namespace a `App\Services\Core`
**TASK-004-A-3:** En `AppServiceProvider`, crear alias de compatibilidad:
```php
class_alias(\App\Services\Core\ActivityLogger::class, 
            \Modules\ActivityLog\Services\ActivityLogger::class);
```
**TASK-004-A-4:** Verificar que todos los módulos que usan ActivityLogger siguen funcionando

---

### FEAT-004-B — Garantizar inmutabilidad

| Campo | Valor |
|-------|-------|
| Prioridad | P2 |
| Dependencias | FEAT-004-A |
| Estimación | 1 hora |
| Criterio de aceptación | No existe ruta DELETE ni método destroy() para `activity_logs` |

**TASK-004-B-1:** Auditar rutas:
```bash
grep -rn "activity_log\|activity-log" routes/ Modules/*/Routes/
```
**TASK-004-B-2:** Verificar que el controller `ActivityLogController` no tiene métodos destructivos
**TASK-004-B-3:** Crear `app/Models/Core/ActivityLog.php` con `$guarded = ['id']` y sin `delete()`

---

### FEAT-004-C — Tests CORE-4

| Campo | Valor |
|-------|-------|
| Prioridad | P2 |
| Dependencias | FEAT-004-A |
| Estimación | 1.5 horas |
| Criterio de aceptación | 4 tests pasan |

**TASK-004-C-1:** `tests/Feature/Core/ActivityLoggerTest.php`
**TASK-004-C-2:** `tests/Unit/Core/ActivityLogImmutableTest.php`
**TASK-004-C-3:** `tests/Feature/Core/ActivityLogIndexTest.php`
**TASK-004-C-4:** `tests/Unit/Core/AuditoriaPasswordImmutableTest.php`

---

## EPIC-005 — CORE-5: Notifications

**Objetivo:** Extraer notificaciones de Inventario al CORE.
**Estimación total:** 4-6 horas
**Criterio de éxito:** `NotificacionesDropdown` en CORE; Inventario envía notificaciones via CORE.

---

### FEAT-005-A — Mover notifications table al CORE

| Campo | Valor |
|-------|-------|
| Prioridad | P1 |
| Dependencias | EPIC-001 completado |
| Estimación | 1 hora |
| Criterio de aceptación | Migration de notifications no está en Inventario sino en `database/migrations/` |

**TASK-005-A-1:** Verificar si la tabla `notifications` ya existe en BD
**TASK-005-A-2:** Si existe (ya migrada): crear migration de verificación en `database/migrations/`
**TASK-005-A-3:** Si no existe: mover el archivo de migration de Inventario a `database/migrations/`

---

### FEAT-005-B — Crear NotificationService CORE

| Campo | Valor |
|-------|-------|
| Prioridad | P1 |
| Dependencias | FEAT-005-A |
| Estimación | 2 horas |
| Criterio de aceptación | `NotificationService::enviar()` crea notificaciones; `noLeidas()` las retorna |

**TASK-005-B-1:** Crear `app/Services/Core/NotificationService.php`
**TASK-005-B-2:** Implementar `enviar()`, `marcarLeida()`, `marcarTodasLeidas()`, `noLeidas()`
**TASK-005-B-3:** Registrar en `AppServiceProvider`

---

### FEAT-005-C — Mover NotificacionesDropdown al CORE

| Campo | Valor |
|-------|-------|
| Prioridad | P2 |
| Dependencias | FEAT-005-B |
| Estimación | 2 horas |
| Criterio de aceptación | Componente CORE funciona; módulo Inventario sigue mostrando notificaciones |

**TASK-005-C-1:** Crear `app/Livewire/Core/NotificacionesDropdown.php` que usa `NotificationService`
**TASK-005-C-2:** Crear `app/Livewire/Core/NotificacionesIcono.php`
**TASK-005-C-3:** Crear vistas en `resources/views/core/notifications/`
**TASK-005-C-4:** Actualizar layouts para usar el componente CORE en lugar del de Inventario

---

### FEAT-005-D — Tests CORE-5

| Campo | Valor |
|-------|-------|
| Prioridad | P2 |
| Dependencias | FEAT-005-A, FEAT-005-B |
| Estimación | 1 hora |
| Criterio de aceptación | 3 tests pasan |

**TASK-005-D-1:** `tests/Feature/Core/NotificationsTableTest.php`
**TASK-005-D-2:** `tests/Feature/Core/NotificationServiceTest.php`
**TASK-005-D-3:** `tests/Feature/Core/NotificacionesDropdownTest.php`

---

## EPIC-006 — CORE-6: Security

**Objetivo:** Consolidar mecanismos de seguridad transversales en el CORE.
**Estimación total:** 2-4 horas
**Criterio de éxito:** `ProteccionAdminPrincipal` en `app/Traits/`; `AuditoriaPassword` en CORE; Gate::before implementado.

---

### FEAT-006-A — Mover ProteccionAdminPrincipal al CORE

| Campo | Valor |
|-------|-------|
| Prioridad | P2 |
| Dependencias | EPIC-001 completado |
| Estimación | 1 hora |
| Criterio de aceptación | Trait en `app/Traits/`; alias en `Modules/User/Traits/` para compatibilidad |

**TASK-006-A-1:** Copiar `ProteccionAdminPrincipal.php` a `app/Traits/ProteccionAdminPrincipal.php`
**TASK-006-A-2:** Actualizar namespace a `App\Traits`
**TASK-006-A-3:** En `Modules/User/Traits/ProteccionAdminPrincipal.php` agregar:
```php
class_alias(\App\Traits\ProteccionAdminPrincipal::class, 
            \Modules\User\Traits\ProteccionAdminPrincipal::class);
```
**TASK-006-A-4:** Actualizar imports en todos los Livewire que usan el trait

---

### FEAT-006-B — Mover AuditoriaPassword al CORE

| Campo | Valor |
|-------|-------|
| Prioridad | P2 |
| Dependencias | EPIC-001 completado |
| Estimación | 1 hora |
| Criterio de aceptación | Model en `app/Models/Core/`; funcionalidad intacta |

**TASK-006-B-1:** Copiar `AuditoriaPassword.php` a `app/Models/Core/AuditoriaPassword.php`
**TASK-006-B-2:** Actualizar namespace a `App\Models\Core`
**TASK-006-B-3:** Crear alias de compatibilidad en `AppServiceProvider`
**TASK-006-B-4:** Verificar que `ProteccionAdminPrincipal` usa el modelo correcto

---

### FEAT-006-C — Verificar Gate::before (ya parte de EPIC-002)

| Campo | Valor |
|-------|-------|
| Prioridad | P1 |
| Dependencias | FEAT-002-B |
| Estimación | Incluido en EPIC-002 |
| Criterio de aceptación | `Gate::before()` implementado; AdminPrincipal tiene acceso total |

**TASK-006-C-1:** Confirmar que `Gate::before()` está en `AuthServiceProvider::boot()` (ejecutado en EPIC-002)

---

### FEAT-006-D — Tests CORE-6

| Campo | Valor |
|-------|-------|
| Prioridad | P2 |
| Dependencias | FEAT-006-A, FEAT-006-B |
| Estimación | 1 hora |
| Criterio de aceptación | 3 tests pasan |

**TASK-006-D-1:** `tests/Feature/Core/ForzarCambioPasswordTest.php`
**TASK-006-D-2:** `tests/Feature/Core/AdminPrincipalProtectionTest.php`
**TASK-006-D-3:** `tests/Unit/Core/ProteccionAdminPrincipalTraitTest.php`

---

## EPIC-007 — Validación Final

**Objetivo:** Verificar que el CORE Foundation completo funciona sin regresiones.
**Estimación total:** 2-3 horas
**Criterio de éxito:** 0 regresiones; todos los tests pasan; sistema en producción funcional.

---

### FEAT-007-A — Suite completa de tests

| Campo | Valor |
|-------|-------|
| Prioridad | P0 |
| Dependencias | EPIC-000 a EPIC-006 completados |
| Estimación | 1 hora |
| Criterio de aceptación | `php artisan test` pasa al 100% |

**TASK-007-A-1:**
```bash
php artisan test --coverage
```
**TASK-007-A-2:** Documentar cobertura de tests en este backlog
**TASK-007-A-3:** Cualquier test roto: registrar como bug y corregir antes de declarar completitud

---

### FEAT-007-B — Verificación de producción

| Campo | Valor |
|-------|-------|
| Prioridad | P0 |
| Dependencias | FEAT-007-A |
| Estimación | 1 hora |
| Criterio de aceptación | Login, módulo Inventario, RBAC visual, backups: todos funcionales |

**TASK-007-B-1:** Login con usuario Administrador → Verificar acceso
**TASK-007-B-2:** Login con usuario Coordinador → Verificar que solo ve módulos asignados
**TASK-007-B-3:** Módulo Inventario → Verificar CRUD de bienes, HMB, HEB
**TASK-007-B-4:** ActivityLog → Verificar que los logs se crean al editar bienes
**TASK-007-B-5:** Dashboard Inventario → Verificar carga correcta

---

### FEAT-007-C — Documentar resultado

| Campo | Valor |
|-------|-------|
| Prioridad | P3 |
| Dependencias | FEAT-007-B |
| Estimación | 30 minutos |
| Criterio de aceptación | Release note creado en `docs/releases/` |

**TASK-007-C-1:** Crear `docs/releases/RELEASE-CORE-FOUNDATION-1.0.0.md`
**TASK-007-C-2:** Actualizar `VERSIONING.md` con versión del CORE

---

## Resumen de estimaciones

| EPIC | Componente | Estimación mínima | Estimación máxima |
|------|-----------|-------------------|-------------------|
| EPIC-000 | Prerequisitos | 2h | 4h |
| EPIC-001 | CORE-1: Users | 4h | 6h |
| EPIC-002 | CORE-2: Authorization | 8h | 12h |
| EPIC-003 | CORE-3: Modules | 10h | 14h |
| EPIC-004 | CORE-4: Audit | 3h | 5h |
| EPIC-005 | CORE-5: Notifications | 4h | 6h |
| EPIC-006 | CORE-6: Security | 2h | 4h |
| EPIC-007 | Validación Final | 2h | 3h |
| **TOTAL** | | **35h** | **54h** |

**Paralelización posible:**
- EPIC-004 y EPIC-005 pueden iniciarse después de EPIC-001 (no dependen de EPIC-002 ni EPIC-003)
- EPIC-006 puede ejecutarse en paralelo con EPIC-003

**Con paralelización:**
- Ruta crítica: EPIC-000 → EPIC-001 → EPIC-002 → EPIC-003 → EPIC-007
- En ruta crítica: ~26h mínimas → ~39h máximas

---

## Criterio de Autorización para IMPL-CORE-002

El siguiente documento de implementación real (IMPL-CORE-002) puede autorizarse cuando:

```
[x] Todos los tests de EPIC-000 a EPIC-006 pasan
[x] 0 regresiones en tests de Inventario, User, Auth
[x] Sistema en producción funciona (checklist FEAT-007-B completo)
[x] Release note CORE Foundation 1.0.0 creado
```

---

*Fin del documento IMPLEMENTATION-BACKLOG-CORE-FOUNDATION v1.0.0*
*Generado: 2026-06-14*
*Autorización requerida: equipo APPSisGOE*
