# CORE-FOUNDATION-IMPLEMENTATION-PLAN

**Documento:** IMPL-CORE-001 — Plan de Implementación del Core Foundation
**Versión:** 1.0.0
**Estado:** Aprobado — Vigente
**Fecha:** 2026-06-14
**Autor:** Ingeniero Principal de Implementación
**Autoridad:** ARCH-001 · ADR-001 · ADR-002 · ADR-003 · ADR-004 · ADR-005
**Prerequisito:** IMPLEMENTATION-READINESS-REPORT.md leído y validado

---

## Tabla de Contenidos

1. [Principios de Implementación](#1-principios-de-implementación)
2. [Orden de Implementación](#2-orden-de-implementación)
3. [CORE-000 — Prerequisitos Técnicos](#3-core-000--prerequisitos-técnicos)
4. [CORE-001 — Users](#4-core-001--users)
5. [CORE-002 — Authorization](#5-core-002--authorization)
6. [CORE-003 — Modules](#6-core-003--modules)
7. [CORE-004 — Audit](#7-core-004--audit)
8. [CORE-005 — Notifications](#8-core-005--notifications)
9. [CORE-006 — Security](#9-core-006--security)
10. [Validación Final del CORE Foundation](#10-validación-final-del-core-foundation)

---

## 1. Principios de Implementación

### Sin arqueología

Cada decisión de implementación se justifica en ARCH-001, ADRs o documentos de dominio. Si un elemento del sistema actual no puede justificarse, se evalúa su eliminación.

### Sistema en producción

El repositorio tiene un sistema en producción. Toda implementación debe mantener el sistema funcionando. No se hacen cambios destructivos sin migración de datos ni ruta de rollback.

### Transaccionalidad

Las migraciones de base de datos son transaccionales cuando el motor lo permite. Cada migración tiene `up()` y `down()` correctamente implementados.

### Un cambio a la vez

Las tareas se ejecutan en el orden definido. No se salta una tarea aunque parezca opcional. Las dependencias entre tareas son reales.

### Tests antes y después

Antes de cada componente: ejecutar la suite existente y registrar estado baseline. Después de cada componente: la suite debe pasar igual o mejor.

---

## 2. Orden de Implementación

```
CORE-000: Prerequisitos técnicos
    │
    ▼
CORE-001: Users
    │  (requiere CORE-000)
    ▼
CORE-002: Authorization
    │  (requiere CORE-001: tabla users, Spatie)
    ▼
CORE-003: Modules
    │  (requiere CORE-002: permisos, roles)
    ▼
CORE-004: Audit
    │  (requiere CORE-001: users FK)
    ▼
CORE-005: Notifications
    │  (requiere CORE-001: users FK)
    ▼
CORE-006: Security
    │  (requiere CORE-001: es_principal, forzar_cambio_password)
    ▼
VALIDACIÓN FINAL
```

**Regla de dependencia:** Un CORE-N no puede iniciar hasta que CORE-(N-1) esté completo y todos sus tests pasen.

**Excepción:** CORE-004 y CORE-005 pueden implementarse en paralelo si hay capacidad, ya que no tienen dependencia directa entre sí. Ambos dependen de CORE-001 y CORE-002.

---

## 3. CORE-000 — Prerequisitos Técnicos

**Objetivo:** Resolver los bloqueantes identificados en IMPLEMENTATION-READINESS-REPORT antes de comenzar la implementación del CORE.

**Duración estimada:** 2-4 horas.

### 3.1 Prerequisito-001 — Instalar Spatie Permission

**Dependencias:** Ninguna.

**Acción:**
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

**Validación:**
- `vendor/spatie/laravel-permission/` existe
- `config/permission.php` publicado
- `database/migrations/*_create_permission_tables.php` publicado

**Nota:** NO ejecutar `php artisan migrate` todavía. Las tablas de Spatie se integran en el plan de CORE-002.

---

### 3.2 Prerequisito-002 — Crear RolInstitucional enum

**Dependencias:** Decisión arquitectónica de cuáles son los 7 roles fijos (ya definidos en DOM-INV-001 §3 y RoleSeeder).

**Ruta:** `app/Auth/RolInstitucional.php`

**Contenido:**
```php
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

**Validación:**
- El enum existe en `app/Auth/RolInstitucional.php`
- `SincronizarRolesYPermisosCoreAction` puede importarlo sin error de clase
- `php artisan tinker` puede instanciar `App\Auth\RolInstitucional::Administrador`

---

### 3.3 Prerequisito-003 — Decisión Laravel 11 vs. 13

**Acción requerida:** Decisión de ingeniería — no técnica.

**Recomendación:** Continuar en Laravel 11.44.7 para CORE Foundation. Planificar upgrade a Laravel 13 en una ADR futura (ADR-006). Laravel 11 es LTS hasta agosto 2026 y todos los patrones de ARCH-001 (Livewire 3, Spatie, nwidart) son compatibles.

**Impacto de esta decisión:** La referencia a "Laravel 13" en ARCH-001 §1.1 se considera un objetivo de versión futura, no un bloqueante de implementación actual.

---

## 4. CORE-001 — Users

**Objetivo:** Formalizar la gestión de usuarios como componente del CORE, eliminando dependencias cruzadas y garantizando la estructura correcta de la tabla `users`.

**Autoridad:** ADR-001 CORE-1, ARCH-001 §4.1.

**Dependencias:** CORE-000 completado.

**Duración estimada:** 4-6 horas.

### 4.1 Migraciones

| Migración | Acción | Descripción |
|-----------|--------|-------------|
| `users` table | Verificar | Confirmar que `nombres`, `apellidos`, `userID`, `bloqueado`, `forzar_cambio_password`, `es_principal` existen y tienen el tipo correcto |
| `auditoria_passwords` table | Verificar | Confirmar existencia y estructura |
| Sin nuevas migraciones para users | — | La estructura actual es correcta según DAT-INV-001 §11.1 |

### 4.2 Modelo User

**Archivo:** `Modules/User/Entities/User.php`

**Acciones requeridas:**

1. **Eliminar imports ilegales (BLOCKER-004):**
   ```php
   // ELIMINAR estas líneas:
   use Modules\Inventario\Entities\Bien;
   use Modules\Inventario\Entities\Dependencia;
   ```

2. **Eliminar métodos que dependen de Inventario:**
   ```php
   // ELIMINAR:
   public function dependencias() { ... }
   public function bienesAsignados() { ... }
   public function bienes() { ... }
   ```

3. **Mantener:**
   - `isAdminPrincipal()` → usa `es_principal`
   - `bloqueado` cast
   - `forzar_cambio_password` cast
   - Relación con `role` (mientras se completa CORE-002)
   - `hasPermission()` (deprecar después de CORE-002)

4. **Agregar (preparación para Spatie):**
   ```php
   use Spatie\Permission\Traits\HasRoles;
   // Agregar HasRoles en la lista de traits
   ```

### 4.3 Modelo AuditoriaPassword

**Archivo:** `Modules/User/Entities/AuditoriaPassword.php`

**Acción:** Verificar que el modelo existe, tiene `$fillable` correcto y la tabla está migrada. No requiere cambios si ya funciona.

### 4.4 Servicios CORE-1

No se crean servicios nuevos en CORE-1. La autenticación es provista completamente por Fortify + Jetstream.

### 4.5 Tests requeridos

| Test | Tipo | Descripción |
|------|------|-------------|
| `UserModelTest` | Feature | User puede crearse, bloquearse, marcar forzar_cambio_password |
| `AdminPrincipalTest` | Feature | `isAdminPrincipal()` retorna true/false según `es_principal` |
| `CrossModuleDependencyTest` | Unit | User.php no tiene imports de `Modules\Inventario\*` |
| `AuditoriaPasswordTest` | Feature | AuditoriaPassword registra entradas inmutables |

### 4.6 Criterio de completitud

- [ ] `User.php` no tiene imports de `Modules\Inventario\*`
- [ ] `User.php` tiene `HasRoles` de Spatie (preparación para CORE-002)
- [ ] `auditoria_passwords` table migrada y accesible
- [ ] Tests de CORE-1 pasan
- [ ] Sistema en producción sigue funcionando (login, logout, perfil)

---

## 5. CORE-002 — Authorization

**Objetivo:** Reemplazar el sistema RBAC custom por Spatie Permission, implementar los 7 roles institucionales fijos, reducir gates a 3 críticos, y registrar el middleware `can:` como la única capa de autorización de rutas.

**Autoridad:** ADR-003, ARCH-001 §6.

**Dependencias:** CORE-001 completado, Spatie instalado (CORE-000).

**Duración estimada:** 8-12 horas.

### 5.1 Migraciones

| Migración | Tipo | Descripción |
|-----------|------|-------------|
| `create_permission_tables` | Nueva (Spatie) | Crea `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` con guard_name |
| `drop_legacy_roles_table` | Drop | Elimina la tabla `roles` custom (después de migrar datos) |
| `drop_legacy_permissions_tables` | Drop | Elimina `permissions`, `permission_role`, `permission_user` custom |
| `clean_roles_app_id_fk` | Alter | Eliminar FK circular `roles.app_id → apps` |

**Orden obligatorio:** Crear tablas Spatie PRIMERO, migrar datos, LUEGO eliminar tablas legacy.

### 5.2 Capacidad enum — Actualización

**Archivo:** `app/Auth/Capacidad.php`

**Acción:** El enum ya existe con la estructura correcta. Verificar:
- Que `permisosCore()` retorna todos los permisos que deben sembrarse
- Que los valores de los casos usen el formato `recurso:accion` consistentemente
- Que los permisos del módulo Inventario estén listados (o delegados al `module.json` del módulo)

### 5.3 RolInstitucional enum

**Archivo:** `app/Auth/RolInstitucional.php` (creado en CORE-000)

**Uso en `SincronizarRolesYPermisosCoreAction`:**
- 7 roles institucionales creados en Spatie
- Matriz rol×permiso aplicada desde `Capacidad::permisosCore()`

### 5.4 Seeder de CORE

**Archivo:** `database/seeders/CorePermissionSeeder.php` (nuevo)

```php
// Delega a SincronizarRolesYPermisosCoreAction
$action->execute();
```

**Idempotente:** Puede ejecutarse múltiples veces sin duplicar datos.

### 5.5 Gates — Reducción a 3

**Archivo:** `app/Providers/AuthServiceProvider.php`

**Acción:** Reemplazar los 60+ gates actuales por exactamente 3:

```php
// Gate 1: Bypass para AdminPrincipal (Gate::before)
Gate::before(function (User $user, string $ability) {
    if ($user->isAdminPrincipal()) {
        return true; // El admin principal puede todo
    }
});

// Gate 2: es_principal como operación crítica
Gate::define('es-admin-principal', fn(User $user) => $user->isAdminPrincipal());

// Gate 3: Verificar si el usuario tiene algún rol activo
Gate::define('tiene-acceso-sistema', fn(User $user) => !$user->bloqueado);
```

**El resto de la autorización** se resuelve mediante:
- Middleware `can:{capacidad}` en rutas (usando Spatie bajo el capó)
- `$this->authorize(Capacidad::X->value)` en Actions

**Nota:** Los gates adicionales del módulo Inventario (ver-bienes, etc.) se eliminan. Las rutas de Inventario usan `can:{slug}` directamente con Spatie.

### 5.6 Middleware de autorización

**Archivo:** `app/Http/Middleware/CheckPermission.php`

**Acción:** Deprecar. En su lugar, las rutas usan el middleware estándar `can:` de Laravel (compatible con Spatie). Si alguna ruta usa `->middleware('permission:slug')`, migrar a `->middleware('can:slug')`.

### 5.7 Servicios CORE-2

| Servicio | Ruta | Descripción |
|----------|------|-------------|
| `SincronizarRolesYPermisosCoreAction` | `app/Actions/Core/` | Ya existe. Completar y validar. |
| `CorePermissionSeeder` | `database/seeders/` | Nuevo seeder que ejecuta la Action |

### 5.8 Tests requeridos

| Test | Tipo | Descripción |
|------|------|-------------|
| `SpatieRolesExistTest` | Feature | Los 7 roles institucionales existen en BD |
| `CorePermissionsExistTest` | Feature | Todos los Capacidad::permisosCore() existen en BD |
| `RolMatrixTest` | Feature | Administrador tiene todos los permisos; Auxiliar tiene solo los de su categoría |
| `AdminPrincipalBypassTest` | Feature | Gate::before retorna true para AdminPrincipal en cualquier ability |
| `BlockedUserTest` | Feature | Usuario bloqueado no puede acceder a rutas protegidas |
| `SpatieCacheTest` | Unit | Invalidar PermissionRegistrar::forgetCachedPermissions() funciona |

### 5.9 Criterio de completitud

- [ ] Tablas Spatie (`permissions`, `roles`, `model_has_roles`, etc.) migradas
- [ ] Tablas legacy (`roles` custom, `permissions` custom) eliminadas
- [ ] 7 roles institucionales sembrados
- [ ] `Capacidad::permisosCore()` sembrados
- [ ] Gate::before implementado para AdminPrincipal
- [ ] Solo 3 gates en AuthServiceProvider
- [ ] Rutas usan `can:{slug}` o `authorize()` con Capacidad enum
- [ ] Tests de CORE-2 pasan
- [ ] Login, permisos y acceso al módulo Inventario siguen funcionando en producción

---

## 6. CORE-003 — Modules

**Objetivo:** Implementar el ciclo de vida de módulos de 6 estados, el formato `module.json` APPSisGOE, y `ModuleVisibilityService` como reemplazo de `App::visiblesPara()`.

**Autoridad:** ADR-002, ARCH-001 §5.

**Dependencias:** CORE-002 completado.

**Duración estimada:** 10-14 horas.

### 6.1 Migraciones

| Migración | Tipo | Descripción |
|-----------|------|-------------|
| `create_modules_table` | Nueva | Tabla `modules` con 6 estados |
| `create_module_role_pivot` | Nueva | `module_role`: qué roles tienen acceso a qué módulo |
| `create_module_user_pivot` | Nueva | `module_user`: acceso individual a módulo por usuario |
| `migrate_apps_to_modules` | Script | Migrar datos de `apps` a `modules` (sin eliminar `apps` todavía) |

**Esquema de `modules` table:**
```sql
CREATE TABLE modules (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key             VARCHAR(60) UNIQUE NOT NULL,      -- slug único: 'inventario', 'user'
    nombre          VARCHAR(100) NOT NULL,
    descripcion     TEXT NULL,
    version         VARCHAR(20) NOT NULL DEFAULT '1.0.0',
    min_core        VARCHAR(20) NOT NULL DEFAULT '1.0.0',
    status          ENUM('pendiente','instalando','activo','inactivo','error','desinstalando') NOT NULL DEFAULT 'pendiente',
    ruta            VARCHAR(200) NULL,
    icono           VARCHAR(100) NULL,
    color           VARCHAR(40) NULL,
    orden           TINYINT UNSIGNED NOT NULL DEFAULT 0,
    metadata        JSON NULL,
    hash            VARCHAR(64) NULL,
    installed_at    TIMESTAMP NULL,
    activated_at    TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL
);
```

### 6.2 Lifecycle States

```
pendiente     → instalando → activo
pendiente     → error (si falla)
activo        → inactivo   (desactivar)
inactivo      → activo     (reactivar)
activo        → desinstalando → (eliminado)
instalando    → error (si falla la instalación)
```

### 6.3 module.json — Formato APPSisGOE

**Formato requerido (por módulo):**
```json
{
    "key": "inventario",
    "name": "Inventario",
    "version": "1.0.0",
    "min_core": "1.0.0",
    "description": "Gestión del ciclo de vida de bienes muebles institucionales",
    "ruta": "/inventario",
    "icono": "fas fa-boxes",
    "color": "#2563eb",
    "orden": 1,
    "providers": ["Modules\\Inventario\\Providers\\InventarioServiceProvider"],
    "hash": ""
}
```

**Acción:** Actualizar `module.json` de todos los módulos activos al nuevo formato.

### 6.4 ModuleVisibilityService

**Archivo:** `app/Services/ModuleVisibilityService.php`

**Contrato:**
```php
class ModuleVisibilityService
{
    public function visiblesPara(User $user): Collection;
    public function invalidarCache(User $user): void;
    public function invalidarCacheGlobal(): void;
}
```

**Implementación:** Reemplaza `App::visiblesPara()`. Usa la tabla `modules` con `module_role` y `module_user`. Cache versioned con `cache()->increment('modules.cache_version')`.

### 6.5 Middleware `modulo.access`

**Archivo:** `app/Http/Middleware/ModuloAccess.php`

**Acción:** Crear este middleware que reemplaza `CheckAppAccess`. Usa `ModuleVisibilityService` en lugar de `App::visiblesPara()`.

**Registro en `bootstrap/app.php`:**
```php
$middleware->alias([
    'modulo.access' => \App\Http\Middleware\ModuloAccess::class,
]);
```

### 6.6 Actions del ciclo de vida

| Action | Ruta | Descripción |
|--------|------|-------------|
| `InstalarModuloAction` | `app/Actions/Core/` | pendiente → activo |
| `ActivarModuloAction` | `app/Actions/Core/` | inactivo → activo |
| `DesactivarModuloAction` | `app/Actions/Core/` | activo → inactivo |
| `DesinstalarModuloAction` | `app/Actions/Core/` | activo → desinstalando |
| `RegistrarModuloAction` | `app/Actions/Core/` | Registra un módulo en la tabla `modules` |
| `SincronizarModulosAction` | `app/Actions/Core/` | Lee module.json de todos los módulos y sincroniza con BD |

### 6.7 Servicios CORE-3

| Servicio/Clase | Ruta | Descripción |
|----------------|------|-------------|
| `ModuleVisibilityService` | `app/Services/` | Servicio principal de visibilidad |
| `ModuloAccess` middleware | `app/Http/Middleware/` | Reemplaza CheckAppAccess |
| `Module` model | `app/Models/` | Modelo Eloquent de la tabla `modules` |

### 6.8 Tests requeridos

| Test | Tipo | Descripción |
|------|------|-------------|
| `ModuleTableExistsTest` | Feature | Tabla `modules` existe con la estructura correcta |
| `ModuleLifecycleTest` | Feature | Transiciones de estado válidas: pendiente→activo→inactivo |
| `ModuleVisibilityTest` | Feature | ModuleVisibilityService retorna módulos correctos por rol |
| `ModuleVisibilityCacheTest` | Feature | Cache se invalida al incrementar modules.cache_version |
| `ModuloAccessMiddlewareTest` | Feature | Middleware bloquea acceso a módulo inactivo |
| `ModuleJsonFormatTest` | Unit | module.json de cada módulo tiene los campos requeridos |

### 6.9 Criterio de completitud

- [ ] Tabla `modules` migrada
- [ ] Pivots `module_role` y `module_user` migrados
- [ ] Datos de `apps` migrados a `modules`
- [ ] Todos los `module.json` actualizados al formato APPSisGOE
- [ ] `ModuleVisibilityService` implementado
- [ ] `ModuloAccess` middleware implementado y registrado
- [ ] 6 Actions del ciclo de vida implementadas
- [ ] Tests de CORE-3 pasan
- [ ] Visibilidad de módulos para usuarios funciona igual que antes (regresión cero)

---

## 7. CORE-004 — Audit

**Objetivo:** Formalizar `ActivityLogger` como servicio CORE accesible por todos los módulos. Garantizar que `activity_logs` y `auditoria_passwords` son inmutables.

**Autoridad:** ADR-001 CORE-4, ARCH-001 §9.

**Dependencias:** CORE-001 completado.

**Duración estimada:** 3-5 horas.

### 7.1 Migraciones

| Migración | Tipo | Descripción |
|-----------|------|-------------|
| `activity_logs` table | Verificar | Ya existe. Confirmar estructura e índices. |
| `auditoria_passwords` table | Verificar | Ya existe. Confirmar inmutabilidad. |
| Sin nuevas migraciones | — | La estructura actual es correcta |

### 7.2 Reorganización del servicio

**Situación actual:** `ActivityLogger` vive en `Modules/ActivityLog/Services/ActivityLogger.php`

**Acción requerida:**

Opción A (recomendada): Mover `ActivityLogger` a `app/Services/Core/ActivityLogger.php` y actualizar todos los imports. Esto cumple ARCH-001 §9 (ActivityLogger es CORE, no módulo).

Opción B (compatible): Mantener en `Modules/ActivityLog/` pero registrar el servicio en el ServiceProvider de la aplicación para que sea accesible como `ActivityLogger::log()` sin importar el namespace del módulo.

**Decisión:** Opción A, usando alias de compatibilidad temporal:
```php
// En AppServiceProvider
class_alias(\App\Services\Core\ActivityLogger::class, 
            \Modules\ActivityLog\Services\ActivityLogger::class);
```

### 7.3 Modelo ActivityLog

**Archivo:** `app/Models/Core/ActivityLog.php` (nuevo) o mantener en módulo con alias.

**Invariante:** El modelo NO debe tener métodos `update()` ni `delete()`. Solo `create()`.

### 7.4 Restricción de inmutabilidad

**Nivel aplicación:**
- `ActivityLog` model sin `$fillable` para `id`, `created_at`
- Sin método `destroy()` en el controlador/Livewire
- Sin ruta DELETE para `activity_logs`

**Nivel base de datos (recomendado en producción):**
- Revocar `UPDATE` y `DELETE` en la tabla `activity_logs` para el usuario de aplicación

### 7.5 Tests requeridos

| Test | Tipo | Descripción |
|------|------|-------------|
| `ActivityLoggerTest` | Feature | ActivityLogger::log() crea un registro con todos los campos |
| `ActivityLogImmutableTest` | Unit | ActivityLog model no tiene métodos de modificación |
| `ActivityLogIndexTest` | Feature | Los índices compuestos existen en la tabla |
| `AuditoriaPasswordImmutableTest` | Unit | AuditoriaPassword no permite actualización |

### 7.6 Criterio de completitud

- [ ] `ActivityLogger` accesible desde `app/Services/Core/` o con alias global
- [ ] Todos los módulos pueden usar `ActivityLogger::log()` sin importar namespace de Inventario
- [ ] `activity_logs` table existe con índices correctos
- [ ] `auditoria_passwords` table existe y es inmutable
- [ ] Tests de CORE-4 pasan

---

## 8. CORE-005 — Notifications

**Objetivo:** Mover el sistema de notificaciones fuera del módulo Inventario y establecerlo como servicio CORE disponible para todos los módulos.

**Autoridad:** ADR-001 CORE-5, ARCH-001 §10.

**Dependencias:** CORE-001 completado.

**Duración estimada:** 4-6 horas.

### 8.1 Migraciones

| Migración | Tipo | Descripción |
|-----------|------|-------------|
| `notifications` table | Verificar | Ya existe (Laravel standard). Verificar ubicación. Si está en Inventario migrations, moverla a `database/migrations/`. |

**Acción:** La migración `2025_05_21_020618_create_notifications_table.php` actualmente está en `Modules/Inventario/Database/Migrations/`. Moverla a `database/migrations/` o crear una migración de verificación en el directorio correcto.

### 8.2 Componentes Livewire CORE-5

**Situación actual:** `NotificacionesDropdown` en `Modules/Inventario/Livewire/Notifications/`

**Acción:** Crear versión CORE del componente:

| Componente | Ruta | Descripción |
|-----------|------|-------------|
| `NotificacionesDropdown` | `app/Livewire/Core/` | Componente CORE que muestra notificaciones del usuario autenticado |
| `NotificacionesIcono` | `app/Livewire/Core/` | Ícono con contador de notificaciones no leídas |

**La versión de Inventario** puede quedar temporalmente pero debe delegar al CORE o depreciarse.

### 8.3 Servicio de Notificaciones

**Archivo:** `app/Services/Core/NotificationService.php`

**Contrato:**
```php
class NotificationService
{
    public function enviar(User $user, string $tipo, array $datos): void;
    public function marcarLeida(string $notificationId): void;
    public function marcarTodasLeidas(User $user): void;
    public function noLeidas(User $user): Collection;
}
```

### 8.4 Tests requeridos

| Test | Tipo | Descripción |
|------|------|-------------|
| `NotificationsTableTest` | Feature | Tabla `notifications` existe y tiene la estructura correcta |
| `NotificationServiceTest` | Feature | NotificationService envía y recupera notificaciones |
| `NotificacionesDropdownTest` | Feature | Componente CORE carga notificaciones del usuario |

### 8.5 Criterio de completitud

- [ ] `notifications` table en migraciones del CORE (no en Inventario)
- [ ] `NotificacionesDropdown` y `NotificacionesIcono` en `app/Livewire/Core/`
- [ ] `NotificationService` implementado
- [ ] Módulo Inventario puede enviar notificaciones usando el servicio CORE
- [ ] Tests de CORE-5 pasan

---

## 9. CORE-006 — Security

**Objetivo:** Consolidar todos los mecanismos de seguridad transversales como componentes formales del CORE.

**Autoridad:** ADR-001 CORE-6, ARCH-001 §4.6.

**Dependencias:** CORE-001, CORE-002 completados.

**Duración estimada:** 2-4 horas.

### 9.1 Migraciones

No se requieren nuevas migraciones. `auditoria_passwords` ya existe (CORE-004).

### 9.2 Componentes de seguridad

| Componente | Estado actual | Acción |
|-----------|---------------|--------|
| `CheckForzarCambioPassword` | `Modules/User/Http/Middleware/` | Mantener en User module; ya registrado en `bootstrap/app.php`. Si se requiere mover al CORE, crear alias. |
| `ProteccionAdminPrincipal` trait | `Modules/User/Traits/` | Mover a `app/Traits/ProteccionAdminPrincipal.php`. Actualizar imports en todo el código. |
| `isAdminPrincipal()` en User | Usa `es_principal` ✓ | No requiere cambio. |
| `AuditoriaPassword` model | `Modules/User/Entities/` | Mover a `app/Models/Core/AuditoriaPassword.php`. Actualizar imports. |
| Gate::before para AdminPrincipal | No existe | Implementar en `AuthServiceProvider::boot()` (parte de CORE-002). |

### 9.3 Registro en bootstrap/app.php

Verificar que el archivo registra:
```php
$middleware->alias([
    'modulo.access'      => \App\Http\Middleware\ModuloAccess::class,
    'forzar.cambio.pass' => \Modules\User\Http\Middleware\CheckForzarCambioPassword::class,
]);

$middleware->appendToGroup('web', \Modules\User\Http\Middleware\CheckForzarCambioPassword::class);
```

El alias `permission` puede eliminarse o mantenerse como `can:` alias de compatibilidad.

### 9.4 Tests requeridos

| Test | Tipo | Descripción |
|------|------|-------------|
| `ForzarCambioPasswordTest` | Feature | Usuario con forzar_cambio_password=true es redirigido a perfil |
| `AdminPrincipalProtectionTest` | Feature | Intentar modificar AdminPrincipal genera registro en auditoria_passwords |
| `ProteccionAdminPrincipalTraitTest` | Unit | Trait verifica correctamente el campo es_principal |

### 9.5 Criterio de completitud

- [ ] `ProteccionAdminPrincipal` trait en `app/Traits/`
- [ ] `AuditoriaPassword` model en `app/Models/Core/` (o con alias)
- [ ] Gate::before implementado (parte de CORE-002)
- [ ] Tests de CORE-006 pasan
- [ ] Protección del AdminPrincipal funciona end-to-end

---

## 10. Validación Final del CORE Foundation

### 10.1 Checklist de entrega

**CORE-000:**
- [ ] `spatie/laravel-permission` instalado y configurado
- [ ] `RolInstitucional` enum creado
- [ ] Decisión Laravel 11 vs. 13 documentada

**CORE-001:**
- [ ] User model sin imports de Inventario
- [ ] `auditoria_passwords` accesible y funcional
- [ ] Tests de CORE-1 pasan (100%)

**CORE-002:**
- [ ] Tablas Spatie migradas
- [ ] Tablas legacy RBAC eliminadas
- [ ] 7 roles sembrados
- [ ] Todos los Capacidad::permisosCore() sembrados
- [ ] Gate::before implementado
- [ ] Solo 3 gates en AuthServiceProvider
- [ ] Tests de CORE-2 pasan (100%)

**CORE-003:**
- [ ] Tabla `modules` con 6 estados
- [ ] Datos migrados de `apps`
- [ ] `module.json` actualizados
- [ ] `ModuleVisibilityService` funcional
- [ ] `ModuloAccess` middleware registrado
- [ ] 6 Actions del lifecycle implementadas
- [ ] Tests de CORE-3 pasan (100%)

**CORE-004:**
- [ ] `ActivityLogger` accesible como servicio CORE
- [ ] `activity_logs` inmutable
- [ ] `auditoria_passwords` inmutable
- [ ] Tests de CORE-4 pasan (100%)

**CORE-005:**
- [ ] `notifications` table en migraciones CORE
- [ ] `NotificacionesDropdown` en CORE
- [ ] `NotificationService` implementado
- [ ] Tests de CORE-5 pasan (100%)

**CORE-006:**
- [ ] `ProteccionAdminPrincipal` en `app/Traits/`
- [ ] `AuditoriaPassword` en CORE
- [ ] Todos los mecanismos de seguridad funcionando
- [ ] Tests de CORE-6 pasan (100%)

### 10.2 Tests de regresión de producción

Antes de declarar CORE Foundation completo, ejecutar:

```bash
# Suite completa de tests
php artisan test

# Verificar módulo Inventario sigue funcionando
php artisan test --filter=Inventario

# Verificar autenticación
php artisan test --filter=Authentication
```

**Criterio:** 0 regresiones. Si hay regresiones, no se declara el CORE Foundation completo.

### 10.3 Siguiente paso

Una vez completado el CORE Foundation y validado con 0 regresiones, se puede autorizar:

```
IMPL-CORE-002
Implementación real del Core Foundation
```

El backlog detallado de tareas ejecutables se encuentra en:
```
docs/implementation/IMPLEMENTATION-BACKLOG-CORE-FOUNDATION.md
```

---

*Fin del documento CORE-FOUNDATION-IMPLEMENTATION-PLAN v1.0.0*
*Generado: 2026-06-14*
*Siguiente documento: IMPLEMENTATION-BACKLOG-CORE-FOUNDATION.md*
