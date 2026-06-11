# AUDIT-CORE-DEADCODE-001 — Legacy Architecture & Dead Code Assessment

| Campo               | Valor                                                                    |
|---------------------|--------------------------------------------------------------------------|
| **ID**              | AUDIT-CORE-DEADCODE-001                                                  |
| **Tipo**            | Dead Code & Legacy Architecture Assessment                               |
| **Fecha**           | 2026-06-11                                                               |
| **Auditor**         | Claude Code (claude-sonnet-4-6)                                          |
| **Origen**          | Derivado de AUDIT-IEE-001 (SHA: `0fc747e`) — DT-001                     |
| **Versión auditada**| IEE v1.12.1 / BhagamApps v1.12.1 / Inventario v2.10.5                   |
| **Repositorio**     | https://github.com/bhagam19/bhagamappsModular.git                        |

---

## Objetivo

Clasificar completamente el código del directorio `app/` y los artefactos residuales en
`Modules/` y `resources/views/` para determinar qué está activo, qué está muerto, qué es
riesgoso y qué decisión arquitectónica debe tomarse antes de continuar con nuevas funcionalidades.

---

## DC-001 — Arquitectura Activa

### Rutas y controladores que sirven peticiones reales bajo `/iee`

| Ruta (prefijo `/iee`) | Controlador / Handler                             | Namespace         |
|-----------------------|---------------------------------------------------|-------------------|
| `GET /`               | `HomeController@index` → `ppal.index`            | `App\Http\Controllers\Ppal` |
| `GET /inventario/*`   | `BienController`, `ActaController`, `HmbController`, `HebController`, `CatalogosController`, `ResponsablesController`, `UbicacionesHistorialController`, `MantenimientosProgramadosController`, `ActaPDFController` | `Modules\Inventario\Http\Controllers` |
| `GET /apps/*`         | `AppController`, `AppAdminController`            | `Modules\Apps\Http\Controllers` |
| `GET /user/*`         | Livewire/Jetstream (rutas User/Módulo)           | `Modules\User` + Jetstream |
| `GET /login`, `POST /login` | Fortify → `auth.login` view             | `Laravel\Fortify` |
| `GET /register`       | Fortify → `auth.register` view                  | `Laravel\Fortify` |
| `GET /forgot-password`, etc. | Fortify password reset                 | `Laravel\Fortify` |
| `GET /ppal`           | `HomeController@index`                           | `App\Http\Controllers\Ppal` |

### Componentes activos en `app/`

| Archivo                                        | Rol                                     |
|------------------------------------------------|-----------------------------------------|
| `app/Providers/AppServiceProvider.php`         | Livewire URL fix para subfolder `/iee`  |
| `app/Providers/AuthServiceProvider.php`        | Definición de Gates custom              |
| `app/Providers/FortifyServiceProvider.php`     | Registro de acciones Fortify            |
| `app/Providers/JetstreamServiceProvider.php`   | Registro de DeleteUser Jetstream        |
| `app/Providers/RouteServiceProvider.php`       | Registro de grupos de rutas             |
| `app/Providers/EventServiceProvider.php`       | Listener `Registered` → email verif.   |
| `app/Http/Middleware/CheckPermission.php`      | Middleware `permission:` — activo       |
| `app/Http/Middleware/CheckAppAccess.php`       | Middleware `app.access:` — activo      |
| `app/Http/Middleware/Authenticate.php`         | Redirect to login — activo              |
| `app/Http/Middleware/VerifyCsrfToken.php`      | CSRF — activo                           |
| `app/Http/Controllers/Ppal/HomeController.php` | Sirve el dashboard principal            |
| `app/Http/Controllers/Auth/LoginResponse.php`  | Redirige post-login a `/`              |
| `app/Actions/Fortify/CreateNewUser.php`        | Registro de nuevos usuarios ✅          |
| `app/View/Components/ChangelogModal.php`       | Footer changelog — activo               |
| `app/Console/Kernel.php`                       | Scheduler (vacío pero activo)           |
| `app/Exceptions/Handler.php`                   | Exception handler estándar              |

### Módulos activos en `Modules/`

| Módulo             | Versión  | Rutas activas    | Estado     |
|--------------------|----------|------------------|------------|
| `Modules/Inventario` | v2.10.5 | 32 rutas web + 5 API | ACTIVO ✅ |
| `Modules/User`       | v2.2.1  | 6 rutas web + RBAC | ACTIVO ✅ |
| `Modules/Apps`       | v1.5.0  | 8 rutas web + 5 API | ACTIVO ✅ |
| `Modules/CrudGenerator` | v1.1.0 | 7 rutas web + 5 API | ACTIVO ✅ |

---

## DC-002 — Arquitectura Paralela en `app/`

### Mapa completo de clasificación

#### GRUPO A — Arquitectura paralela de Inventario (MUERTA + RIESGOSA)

Esta arquitectura fue iniciada como segunda generación del módulo Inventario usando el
stack `app/` (Actions, DTOs, ReadServices, Models, Capacidad). Nunca se conectó a rutas.
**Ningún archivo de este grupo está registrado en ningún `routes/*.php`.**

| Archivo                                                         | Clasificación | LOC | Riesgo     |
|-----------------------------------------------------------------|---------------|-----|------------|
| `app/Models/Inventario/Bien.php`                               | MUERTO        | 71  | MEDIO      |
| `app/Models/Inventario/BienResponsable.php`                    | MUERTO        | 54  | MEDIO      |
| `app/Actions/Inventario/AsignarResponsableBienAction.php`      | MUERTO        | 31  | BAJO       |
| `app/Actions/Inventario/TransferirResponsableBienAction.php`   | MUERTO        | 39  | BAJO       |
| `app/DTOs/Inventario/AsignarResponsableData.php`               | MUERTO        | 12  | BAJO       |
| `app/DTOs/Inventario/TransferirResponsableData.php`            | MUERTO        | 13  | BAJO       |
| `app/ReadServices/Inventario/BienResponsableReadService.php`   | MUERTO        | 43  | BAJO       |
| `app/Http/Controllers/Inventario/BienResponsableController.php`| MUERTO        | 97  | BAJO       |
| `app/Http/Requests/Inventario/StoreResponsableBienRequest.php` | MUERTO        | 42  | BAJO       |
| `app/Http/Requests/Inventario/TransferirResponsableRequest.php`| MUERTO        | 45  | BAJO       |
| `app/Auth/Capacidad.php`                                       | MUERTO        | 136 | ALTO       |
| `app/Actions/Core/SincronizarRolesYPermisosCoreAction.php`     | MUERTO        | 140 | CRÍTICO    |

**Total Grupo A: 12 archivos, ~723 LOC, sin ruta activa alguna.**

**Notas críticas del Grupo A**:
- `app/Auth/Capacidad.php` define permisos con formato `recurso:accion` destinados a Spatie.
  Ninguno de estos slugs está definido en `AuthServiceProvider` (el sistema activo usa
  guiones: `ver-bienes`, `ver-responsables-bienes`).
- `app/Actions/Core/SincronizarRolesYPermisosCoreAction.php` importa
  `Spatie\Permission\Models\*` y `RolInstitucional` — este último **no existe en ningún
  archivo del repositorio**. Es invocable cero veces sin instalar Spatie.
- `app/Models/Inventario/Bien.php` castea `App\Enums\Inventario\EstadoBien` y
  `App\Enums\Inventario\EstadoMantenimiento` — **ninguno de estos enums existe**.
  Cargar este modelo provoca `Class not found` fatal.
- `app/Models/Inventario/BienResponsable.php` referencia `App\Models\User` →
  cascada de `Trait not found` Spatie.

#### GRUPO B — app/Models/User.php (RIESGOSO — CRÍTICO)

| Archivo               | Clasificación | LOC | Riesgo       |
|-----------------------|---------------|-----|--------------|
| `app/Models/User.php` | RIESGOSO      | 43  | **CRÍTICO**  |

Este es el riesgo más severo del repositorio. Análisis detallado en **DC-004**.

#### GRUPO C — Módulo Grupos legacy (LEGACY — SIN RUTAS)

| Archivo                                        | Clasificación | LOC | Riesgo |
|------------------------------------------------|---------------|-----|--------|
| `app/Models/Grupo.php`                         | LEGACY        | 29  | BAJO   |
| `app/Livewire/Grupo/CrearGrupo.php`            | LEGACY        | 44  | BAJO   |
| `app/Livewire/Grupo/EditarNombreGrupo.php`     | LEGACY        | 37  | BAJO   |
| `app/Http/Controllers/Ppal/GrupoController.php`| LEGACY        | 83  | BAJO   |

`GrupoController` referencia `App\Models\Grupo` y rutas `admin.grupos.*` que no existen
en `routes/ppal.php`. Los Livewire components no están registrados en ninguna vista activa.

#### GRUPO D — Fortify Actions con User model incorrecto (ACTIVO + RIESGOSO)

Este grupo es distinto: los archivos **son activos** (registrados en `FortifyServiceProvider`
y `JetstreamServiceProvider`), pero importan `App\Models\User` — el modelo con Spatie roto.

| Archivo                                                    | Clasificación     | Riesgo       |
|------------------------------------------------------------|-------------------|--------------|
| `app/Actions/Fortify/UpdateUserProfileInformation.php`    | ACTIVO + RIESGOSO | **CRÍTICO**  |
| `app/Actions/Fortify/UpdateUserPassword.php`              | ACTIVO + RIESGOSO | **CRÍTICO**  |
| `app/Actions/Fortify/ResetUserPassword.php`               | ACTIVO + RIESGOSO | **CRÍTICO**  |
| `app/Actions/Jetstream/DeleteUser.php`                    | ACTIVO + RIESGOSO | **CRÍTICO**  |

**Impacto operativo concreto**: Cuando un usuario autenticado intenta:
- `PUT /user/password` → `UpdateUserPassword::update(App\Models\User $user, …)` → PHP intenta
  cargar `App\Models\User` → autoloader resuelve `use Spatie\Permission\Traits\HasRoles` →
  **Fatal Error: Trait not found → HTTP 500**
- `PUT /user/profile-information` → mismo camino → **500**
- Reset de contraseña por email → `ResetUserPassword::reset(App\Models\User $user, …)` → **500**
- Eliminación de cuenta → `DeleteUser::delete(App\Models\User $user)` → **500**

`CreateNewUser.php` es el **único** Fortify action correcto: usa `Modules\User\Entities\User`. ✅

#### GRUPO E — Vistas paralelas de Inventario y Grupos (MUERTAS)

| Archivo / Directorio                                        | Clasificación | LOC  |
|-------------------------------------------------------------|---------------|------|
| `resources/views/inventario/bienes/index.blade.php`        | MUERTO        | 221  |
| `resources/views/inventario/bienes/show.blade.php`         | MUERTO        | 538  |
| `resources/views/inventario/responsables/asignar.blade.php`| MUERTO        | 68   |
| `resources/views/inventario/responsables/transferir.blade.php` | MUERTO    | 90   |
| `resources/views/inventario/responsables/historial.blade.php` | MUERTO     | 112  |
| `resources/views/inventario/responsables/por-usuario.blade.php` | MUERTO   | 77   |
| `resources/views/livewire/grupo/crear-grupo.blade.php`     | LEGACY        | 32   |
| `resources/views/livewire/grupo/editar-nombre-grupo.blade.php` | LEGACY    | 15   |

Todas las vistas en `resources/views/inventario/` usan `<x-admin-layout>` — un componente
**que no existe** en el repositorio. Si alguna ruta las invocara, produciría un error de
componente Blade no encontrado. Son artefactos del stack paralelo (Grupo A).

---

## DC-003 — Dependencias

### Mapa composer.json vs. uso real

| Paquete                          | En composer.json | Usado en código activo | Usado en código muerto | Estado      |
|----------------------------------|:----------------:|:---------------------:|:---------------------:|-------------|
| `laravel/framework`              | ✅               | ✅                    | —                     | ACTIVO ✅   |
| `laravel/jetstream`              | ✅               | ✅ (auth, profile)    | —                     | ACTIVO ✅   |
| `laravel/sanctum`                | ✅               | ✅ (API tokens)       | —                     | ACTIVO ✅   |
| `laravel/tinker`                 | ✅               | debug                 | —                     | ACTIVO ✅   |
| `livewire/livewire`              | ✅               | ✅                    | —                     | ACTIVO ✅   |
| `nwidart/laravel-modules`        | ✅               | ✅                    | —                     | ACTIVO ✅   |
| `jeroennoten/laravel-adminlte`   | ✅               | ✅                    | —                     | ACTIVO ✅   |
| `barryvdh/laravel-dompdf`        | ✅               | ✅ (ActaPDF Livewire) | —                     | ACTIVO ✅   |
| `barryvdh/laravel-snappy`        | ✅               | ✅ (ActaPDFController)| —                     | ACTIVO ✅   |
| `league/csv`                     | ✅               | ✅ (Seeders)          | —                     | ACTIVO ✅   |
| `spatie/laravel-permission`      | ❌               | ❌                    | ✅ (Grupos A+B)       | **FALTANTE / CRÍTICO** |

### Dependencias instaladas pero no usadas en producción

Ninguna dependencia instalada en `require` está completamente sin uso en la ruta activa.
`barryvdh/laravel-snappy` y `barryvdh/laravel-dompdf` coexisten porque se usan en dos
rutas distintas para generación de PDF (duplicidad de propósito, no de código muerto).

### Dependencias usadas en código muerto pero no instaladas

| Paquete                    | Archivo(s) que lo importan                                           |
|----------------------------|----------------------------------------------------------------------|
| `spatie/laravel-permission` | `app/Models/User.php`, `app/Actions/Core/SincronizarRolesYPermisosCoreAction.php` |

**Diagnóstico**: `spatie/laravel-permission` nunca fue agregado a `composer.json`.
Los archivos que lo referencian son restos de una migración arquitectónica planificada
pero no ejecutada. La eliminación de estos archivos resuelve la inconsistencia sin
necesidad de instalar Spatie.

### Enum ausentes referenciados desde código muerto

| Enum                                  | Referenciado desde              |
|---------------------------------------|---------------------------------|
| `App\Enums\Inventario\EstadoBien`     | `app/Models/Inventario/Bien.php` |
| `App\Enums\Inventario\EstadoMantenimiento` | `app/Models/Inventario/Bien.php` |
| `App\Auth\RolInstitucional`           | `app/Actions/Core/SincronizarRolesYPermisosCoreAction.php` |

Estos enums nunca fueron creados. Cargar cualquiera de sus referencias provoca
`Class not found` fatal.

---

## DC-004 — User Model: Diagnóstico Completo

### Dos modelos User coexisten en el repositorio

| Modelo                             | Tabla BD  | Auth provider | Usado en producción |
|------------------------------------|-----------|:-------------:|:-------------------:|
| `Modules\User\Entities\User`       | `users`   | ✅ (`config/auth.php`) | ✅ SÍ    |
| `App\Models\User`                  | `users`   | ❌            | ❌ NO               |

`config/auth.php` registra explícitamente:
```php
'model' => Modules\User\Entities\User::class,
```

`Modules\User\Entities\User` es el **único modelo de usuario real** del sistema.

### Características de cada modelo

| Característica               | `Modules\User\Entities\User`    | `App\Models\User`          |
|------------------------------|---------------------------------|----------------------------|
| Campos fillable              | `nombres`, `apellidos`, `userID`, `email`, `password`, `role_id` | `name`, `email`, `password`, `rol_sistema` |
| RBAC                         | `hasPermission($slug)` custom   | `HasRoles` Spatie (roto)   |
| Relaciones                   | `role()`, `permissions()`, `dependencias()`, `bienesAsignados()` | `bienesAsignados()` paralela |
| Traits                       | `HasApiTokens`, `HasProfilePhoto`, `Notifiable`, `TwoFactorAuthenticatable` | `HasFactory`, `Notifiable`, `HasRoles` (roto) |
| Estado operativo             | **FUNCIONAL**                   | **FATAL al cargar**        |

### `App\Models\User` — Análisis de riesgo

```php
// app/Models/User.php — línea 15
use Spatie\Permission\Traits\HasRoles;  // ← Trait no encontrado
```

- `spatie/laravel-permission` NO está en `composer.json`.
- PHP lanza `Fatal error: Trait "Spatie\Permission\Traits\HasRoles" not found`
  en el momento en que cualquier contexto de ejecución requiere cargar esta clase.
- Los Fortify Actions `UpdateUserPassword`, `UpdateUserProfileInformation`,
  `ResetUserPassword`, y el Jetstream action `DeleteUser`, todos tienen
  `use App\Models\User` y type-hint `User $user`. Están registrados en los
  service providers y se invocan en peticiones HTTP reales.

### Cadena de fallo en producción

```
Usuario intenta actualizar contraseña
→ PUT /user/password
→ Fortify llama UpdateUserPassword::update(App\Models\User $user, array $input)
→ PHP autoloader resuelve App\Models\User
→ PHP parsea app/Models/User.php
→ PHP intenta cargar Spatie\Permission\Traits\HasRoles
→ FATAL ERROR: Trait not found
→ HTTP 500 para el usuario
```

**Este defecto es silencioso en condiciones normales** porque el flujo de login
(Fortify) carga `Modules\User\Entities\User` — modelo correcto. El fatal error
solo ocurre cuando el usuario ya autenticado intenta operaciones de perfil/contraseña.

---

## DC-005 — RBAC: Mapa de Arquitectura Completo

### Sistema RBAC activo (único operativo en producción)

```
Authenticatable: Modules\User\Entities\User
    │
    ├── role_id → Modules\User\Entities\Role
    │                   │
    │                   └── permissions() → Modules\User\Entities\Permission
    │                           (tabla: permission_role)
    │
    └── permissions() directos → Modules\User\Entities\Permission
            (tabla: permission_user)
```

**Verificación de permiso en runtime**:
```
User::hasPermission($slug)
    ├── $this->permissions()->where('slug', $slug)->exists()  // permiso directo
    └── $this->role->permissions()->where('slug', $slug)->exists()  // permiso por rol
```

**Formato de slug activo**: guiones (`ver-bienes`, `gestionar-historial-modificaciones-bienes`)

**Gates (AuthServiceProvider)**: `Gate::define($slug, fn($user) => $user->hasPermission($slug))`
— todos los gates referencian el sistema custom. ✅

**Middleware activos**:
- `permission:ver-bienes` → `CheckPermission::handle()` → `User::hasPermission('ver-bienes')`
- `app.access:inventario` → `CheckAppAccess::handle()` → `App::visiblesPara($user)`

### Sistema RBAC paralelo (MUERTO — nunca conectado)

```
App\Auth\Capacidad (enum backed string)
    │   formato: 'recurso:accion' (colon, diferente al activo)
    │
    └── App\Actions\Core\SincronizarRolesYPermisosCoreAction
            │
            ├── Spatie\Permission\Models\Permission (NO INSTALADO)
            ├── Spatie\Permission\Models\Role (NO INSTALADO)
            └── App\Auth\RolInstitucional (NO EXISTE)
```

**Incompatibilidades con el sistema activo**:

| Aspecto            | Sistema activo                          | Sistema paralelo (muerto)          |
|--------------------|-----------------------------------------|------------------------------------|
| Paquete base       | Custom (`Modules\User`)                 | Spatie (no instalado)              |
| Formato slug       | `ver-bienes` (guión)                    | `inventario_bienes:ver` (colon)    |
| Definición de gates | `AuthServiceProvider` (guión slugs)    | `Gate::authorize(Capacidad::…)`    |
| User model         | `Modules\User\Entities\User`            | `App\Models\User`                  |
| Tables             | `roles`, `permissions`, `permission_role` | Tablas Spatie (no existen)       |

### Gates definidos vs. capacidades del enum (incompatibilidad confirmada)

| Gate en AuthServiceProvider   | Capacidad enum equivalente               | ¿Coinciden? |
|-------------------------------|------------------------------------------|:-----------:|
| `ver-bienes`                  | `InventarioBienesVer` = `inventario_bienes:ver` | ❌    |
| `ver-responsables-bienes`     | `InventarioResponsablesVer` = `inventario_responsables:ver` | ❌ |
| `gestionar-historial-modificaciones-bienes` | (sin equivalente en Capacidad) | — |

Los gates del sistema activo y los valores del enum Capacidad son **completamente
diferentes en formato y nombre**. No existe ningún punto de compatibilidad.

---

## DC-006 — Código Muerto: Inventario Completo

### PHP Files

| Grupo | Archivos | LOC total |
|-------|----------|-----------|
| Arquitectura paralela Inventario (Grupo A) | 12 | ~723 |
| `app/Models/User.php` (Grupo B) | 1 | 43 |
| Módulo Grupos legacy (Grupo C) | 4 | 193 |
| `Modules/Inventario/Http/Controllers/TestFiltroController.php` | 1 | ~25 |
| **Total PHP dead code** | **18** | **~984** |

### Blade Views

| Grupo | Archivos | LOC total |
|-------|----------|-----------|
| Vistas paralelas Inventario (Grupo E) | 6 | 1,106 |
| Vistas legacy Grupos | 2 | 47 |
| **Total Blade dead code** | **8** | **~1,153** |

### Total dead code: 26 archivos, ~2,137 LOC

### Código activo que referencia código muerto (dependencias cruzadas)

| Archivo activo                         | Referencia a código muerto                          |
|----------------------------------------|-----------------------------------------------------|
| `app/Actions/Fortify/UpdateUserProfileInformation.php` | `App\Models\User` (Grupo B)    |
| `app/Actions/Fortify/UpdateUserPassword.php`           | `App\Models\User` (Grupo B)    |
| `app/Actions/Fortify/ResetUserPassword.php`            | `App\Models\User` (Grupo B)    |
| `app/Actions/Jetstream/DeleteUser.php`                 | `App\Models\User` (Grupo B)    |

Estas cuatro referencias cruzadas son el **origen de todos los riesgos operativos críticos**.

---

## DC-007 — Riesgo Operativo

### Clasificación por severidad

---

#### RIESGO-001 — Fatal Error en operaciones de perfil/contraseña [CRÍTICO]

| Campo       | Valor                                                          |
|-------------|----------------------------------------------------------------|
| Severidad   | **CRÍTICO**                                                    |
| Tipo        | Fatal PHP Error → HTTP 500                                     |
| Trigger     | `PUT /user/password`, `PUT /user/profile-information`, `POST /forgot-password`, eliminación de usuario |
| Causa raíz  | `App\Models\User` → `Spatie\Permission\Traits\HasRoles` no instalado |
| Archivos    | `app/Actions/Fortify/UpdateUserPassword.php`, `UpdateUserProfileInformation.php`, `ResetUserPassword.php`, `app/Actions/Jetstream/DeleteUser.php` |
| Impacto     | Cualquier usuario que intente actualizar su contraseña o perfil recibe 500. Reset de contraseña por email inoperativo. |
| Estado      | **ACTIVO EN PRODUCCIÓN** — silencioso mientras nadie use estas funciones |

---

#### RIESGO-002 — Trait Spatie no instalado referenciado desde autoload [CRÍTICO]

| Campo       | Valor                                                          |
|-------------|----------------------------------------------------------------|
| Severidad   | **CRÍTICO**                                                    |
| Tipo        | Fatal PHP Error                                                |
| Trigger     | Cualquier carga de `App\Models\User`                          |
| Causa raíz  | `use Spatie\Permission\Traits\HasRoles` con paquete ausente   |
| Archivos    | `app/Models/User.php`                                          |
| Impacto     | Bloquea la suite de tests completa. Puede interrumpir producción si el namespace `App\Models\User` es resuelto inesperadamente (discovery de Livewire, opcache warm-up, etc.) |

---

#### RIESGO-003 — Enums inexistentes en modelos paralelos [ALTO]

| Campo       | Valor                                                          |
|-------------|----------------------------------------------------------------|
| Severidad   | ALTO                                                           |
| Tipo        | Class not found                                                |
| Trigger     | Carga de `App\Models\Inventario\Bien`                         |
| Causa raíz  | Referencia a `App\Enums\Inventario\EstadoBien` y `EstadoMantenimiento` que no existen |
| Archivos    | `app/Models/Inventario/Bien.php`                              |
| Impacto     | Cualquier ruta que instancie este modelo falla con 500. No activo hoy porque no hay rutas registradas. |

---

#### RIESGO-004 — RolInstitucional no existe [ALTO]

| Campo       | Valor                                                          |
|-------------|----------------------------------------------------------------|
| Severidad   | ALTO                                                           |
| Tipo        | Class not found                                                |
| Trigger     | Ejecución de `SincronizarRolesYPermisosCoreAction::execute()`  |
| Causa raíz  | `use App\Auth\RolInstitucional` — enum nunca creado            |
| Archivos    | `app/Actions/Core/SincronizarRolesYPermisosCoreAction.php`    |
| Impacto     | Seeder o comando que llame a esta Action falla inmediatamente  |

---

#### RIESGO-005 — Componente Blade `<x-admin-layout>` inexistente [MEDIO]

| Campo       | Valor                                                          |
|-------------|----------------------------------------------------------------|
| Severidad   | MEDIO                                                          |
| Tipo        | Blade component not found                                      |
| Trigger     | Renderizar cualquier vista en `resources/views/inventario/`   |
| Causa raíz  | Las vistas usan `<x-admin-layout>` que no existe              |
| Archivos    | 6 vistas en `resources/views/inventario/`                     |
| Impacto     | Actualmente sin impacto (sin rutas). Si se registran accidentalmente → 500. |

---

#### RIESGO-006 — Gates definidos con slug incorrecto en controlador paralelo [BAJO]

| Campo       | Valor                                                          |
|-------------|----------------------------------------------------------------|
| Severidad   | BAJO (actualmente sin impacto)                                 |
| Tipo        | 403 Forbidden en todas las rutas del controlador               |
| Trigger     | Si `BienResponsableController` se registra en routes          |
| Causa raíz  | `Gate::authorize('inventario_responsables:ver')` — gate no definido |
| Archivos    | `app/Http/Controllers/Inventario/BienResponsableController.php` |

---

## Mapa Arquitectónico Final

```
┌────────────────────────────────────────────────────────────────┐
│                    ARQUITECTURA ACTIVA                         │
├────────────────────────────────────────────────────────────────┤
│  Auth stack        Laravel Fortify + Jetstream (views adminlte)│
│  RBAC              Modules\User (Role/Permission custom)       │
│  Inventario        Modules\Inventario\Entities\* (Livewire)   │
│  Users             Modules\User\Entities\User                  │
│  Apps              Modules\Apps\Entities\App                   │
│  CrudGenerator     Modules\CrudGenerator                       │
│  Middleware        CheckPermission, CheckAppAccess             │
│  Gates             AuthServiceProvider (slug: guiones)        │
└────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────┐
│            ARQUITECTURA ACTIVA CON DEFECTO CRÍTICO             │
├────────────────────────────────────────────────────────────────┤
│  Fortify Actions (UpdateUserPassword, ResetUserPassword,       │
│  UpdateUserProfileInformation, DeleteUser)                     │
│  → Activos y registrados                                       │
│  → Importan App\Models\User → FATAL ERROR al ejecutarse       │
└────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────┐
│              ARQUITECTURA PARALELA / MUERTA                    │
├────────────────────────────────────────────────────────────────┤
│  App\Models\User (HasRoles Spatie roto)                        │
│  App\Models\Inventario\{Bien, BienResponsable}                 │
│  App\Auth\Capacidad (Spatie colon-slugs)                       │
│  App\Actions\Inventario\{Asignar, Transferir}                  │
│  App\Actions\Core\SincronizarRoles (Spatie, RolInstitucional)  │
│  App\DTOs\Inventario\{Asignar, Transferir}Data                 │
│  App\ReadServices\Inventario\BienResponsableReadService        │
│  App\Http\Controllers\Inventario\BienResponsableController     │
│  App\Http\Requests\Inventario\{Store, Transferir}Request       │
│  resources/views/inventario/{bienes,responsables}/*            │
└────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────┐
│                 ARQUITECTURA LEGACY (Grupos)                   │
├────────────────────────────────────────────────────────────────┤
│  App\Models\Grupo                                              │
│  App\Livewire\Grupo\{CrearGrupo, EditarNombreGrupo}           │
│  App\Http\Controllers\Ppal\GrupoController                     │
│  resources/views/livewire/grupo/*                              │
│  database/migrations/2025_05_12_232010_create_grupos_table     │
└────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────┐
│                   CÓDIGO HUÉRFANO PUNTUAL                      │
├────────────────────────────────────────────────────────────────┤
│  Modules/Inventario/Http/Controllers/TestFiltroController.php  │
│  Modules/Inventario/resources/views/livewire/bienes/test-filtro│
└────────────────────────────────────────────────────────────────┘
```

---

## Lista de Riesgos Clasificada

| ID | Descripción | Severidad | Estado en prod. | Acción recomendada |
|----|-------------|-----------|-----------------|-------------------|
| R-001 | Fatal error en update/reset password, profile, deleteUser | CRÍTICO | LATENTE | Corregir inmediatamente |
| R-002 | `app/Models/User.php` → Spatie trait no instalado | CRÍTICO | ACTIVO (bloquea tests) | Eliminar archivo |
| R-003 | Enums inexistentes en `app/Models/Inventario/Bien.php` | ALTO | Inactivo (sin rutas) | Eliminar archivo |
| R-004 | `RolInstitucional` no existe en `SincronizarRolesYPermisosCoreAction` | ALTO | Inactivo | Eliminar archivo |
| R-005 | `<x-admin-layout>` no existe en vistas paralelas | MEDIO | Inactivo | Eliminar vistas |
| R-006 | Gates con slug incorrecto en `BienResponsableController` | BAJO | Inactivo (sin rutas) | Eliminar controlador |

---

## Recomendaciones por Hallazgo

### Para cada hallazgo importante

| Hallazgo | Recomendación | Justificación |
|----------|---------------|---------------|
| `app/Models/User.php` | **D — Eliminar** | Clase fatal, reemplazada por `Modules\User\Entities\User`. Ningún uso legítimo. |
| Fortify Actions con User incorrecto | **C — Refactorizar** | Cambiar `use App\Models\User` por `use Modules\User\Entities\User` en los 4 archivos. Cero riesgo de regresión. |
| `app/Models/Inventario/Bien.php` | **D — Eliminar** | Modelo duplicado, enums faltantes, sin rutas. |
| `app/Models/Inventario/BienResponsable.php` | **D — Eliminar** | Idem. Referencia a `App\Models\User` roto. |
| `app/Auth/Capacidad.php` | **D — Eliminar** | RBAC custom activo no usa Capacidad. Si en el futuro se migra a Spatie, se recrea desde cero. |
| `app/Actions/Core/SincronizarRolesYPermisosCoreAction.php` | **D — Eliminar** | Depende de Spatie + RolInstitucional. Ninguno existe. Sin caller conocido. |
| `app/Actions/Inventario/*`, `app/DTOs/*`, `app/ReadServices/*`, `app/Http/Controllers/Inventario/*`, `app/Http/Requests/Inventario/*` | **D — Eliminar** | Sin ruta, sin caller. Dead code puro. La funcionalidad real existe en `Modules\Inventario`. |
| `resources/views/inventario/*` | **D — Eliminar** | Vistas muertas con componente inexistente. |
| Módulo Grupos (Models, Livewire, Controller, Views) | **D — Eliminar** | Sin módulo activo. Si Grupos se retoma, se construye desde cero con el stack correcto. |
| `TestFiltroController.php` + `test-filtro.blade.php` | **D — Eliminar** | Artefacto de debug. Sin ruta registrada. |
| `database/migrations/2025_05_12_232010_create_grupos_table.php` | **A — Mantener** | La tabla `grupos` puede existir en BD. Eliminar la migración sin cleanup previo crearía inconsistencia. Evaluar junto con el módulo Grupos. |

---

## Plan de Remediación Recomendado

### Fase 1 — Crítico (antes del próximo deploy)

1. **Corregir los 4 Fortify/Jetstream Actions** (RIESGO-001):
   ```
   app/Actions/Fortify/UpdateUserPassword.php
   app/Actions/Fortify/UpdateUserProfileInformation.php
   app/Actions/Fortify/ResetUserPassword.php
   app/Actions/Jetstream/DeleteUser.php
   ```
   Cambiar `use App\Models\User` → `use Modules\User\Entities\User` en cada uno.
   Ajustar los campos de `forceFill` a los existentes en el modelo real (`nombres`,
   `apellidos`, `email`, `password`).

### Fase 2 — Dead Code Cleanup (una sesión dedicada)

2. **Eliminar** todos los archivos del Grupo A (12 PHP, ~723 LOC):
   `app/Auth/Capacidad.php`, `app/Actions/Core/`, `app/Actions/Inventario/`,
   `app/DTOs/Inventario/`, `app/ReadServices/Inventario/`,
   `app/Http/Controllers/Inventario/`, `app/Http/Requests/Inventario/`,
   `app/Models/Inventario/`.

3. **Eliminar** `app/Models/User.php` (Grupo B, 43 LOC).

4. **Eliminar** Grupo C: `app/Models/Grupo.php`, `app/Livewire/Grupo/`,
   `app/Http/Controllers/Ppal/GrupoController.php`,
   `resources/views/livewire/grupo/`.

5. **Eliminar** Grupo E: `resources/views/inventario/`.

6. **Eliminar** `TestFiltroController.php` y `test-filtro.blade.php`.

### Resultado esperado tras remediación

- 0 riesgos críticos activos
- 0 archivos con imports a clases inexistentes
- Test suite ejecuta sin fatal errors (los 14 tests estructurales pasan limpio)
- ~2,180 LOC menos de código muerto en el repositorio
- Arquitectura del codebase: una sola capa coherente (`Modules/` para negocio, `app/` solo para infraestructura transversal)

---

## Resumen Ejecutivo

El repositorio contiene **dos arquitecturas paralelas**:

1. **Arquitectura activa** (`Modules/` + servicios transversales de `app/`): funciona en
   producción. RBAC custom (`Modules\User`), Inventario full (`Modules\Inventario`), AdminLTE.

2. **Arquitectura paralela muerta** (`app/Models/Inventario`, `app/Auth/Capacidad`, etc.):
   nunca fue conectada a rutas. Planificaba usar Spatie y un stack app/ canónico. Abandonada.

El defecto **más severo no es la existencia de código muerto**, sino que **4 archivos
activos y registrados** (`UpdateUserPassword`, `UpdateUserProfileInformation`,
`ResetUserPassword`, `DeleteUser`) referencian el modelo `App\Models\User` que produce
fatal error. Esto hace inoperativas las funciones de gestión de contraseña y perfil de todos
los usuarios del sistema.

**Hallazgo sobre `App\Models\User`**: Este modelo es la única pieza del código muerto que
tiene dependencias inversas desde el código activo. No es simplemente "código muerto" —
es una trampa: código activo que depende de código muerto que depende de un paquete no instalado.

---

## Suplemento — Hallazgos adicionales post-commit

### DC-001S — Configuración de providers: dualidad `config/app.php` / `bootstrap/providers.php`

En Laravel 11, los providers pueden registrarse en **dos lugares distintos**. Este repositorio usa ambos simultáneamente:

| Provider                          | `config/app.php` | `bootstrap/providers.php` | Estado    |
|-----------------------------------|:----------------:|:------------------------:|-----------|
| `App\Providers\AppServiceProvider`     | ✅              | ✅                       | **DOBLE REGISTRO** |
| `App\Providers\AuthServiceProvider`    | ✅              | ❌                       | ACTIVO    |
| `App\Providers\EventServiceProvider`   | ✅              | ❌                       | ACTIVO    |
| `App\Providers\RouteServiceProvider`   | ✅              | ❌                       | ACTIVO    |
| `App\Providers\BroadcastServiceProvider` | ❌ (comentado) | ❌                      | INACTIVO  |
| `App\Providers\FortifyServiceProvider` | ✅             | ✅                       | **DOBLE REGISTRO** |
| `App\Providers\JetstreamServiceProvider` | ✅           | ✅                       | **DOBLE REGISTRO** |

**Tres providers están registrados en ambas fuentes.** Laravel deduplica los providers en runtime, pero la duplicidad es un riesgo de mantenimiento: cambiar un provider en una fuente sin actualizar la otra puede producir comportamiento inesperado.

**`BroadcastServiceProvider`**: El archivo `app/Providers/BroadcastServiceProvider.php` existe en el filesystem pero el provider está comentado en `config/app.php`. El archivo es `HUÉRFANO` — activo para el autoloader pero sin efecto en runtime.

### DC-001S2 — `app/Http/Kernel.php`: código legacy en Laravel 11

| Archivo               | Clasificación | Riesgo |
|-----------------------|---------------|--------|
| `app/Http/Kernel.php` | LEGACY        | BAJO   |

En Laravel 11, `bootstrap/app.php` usa `Application::configure()->withMiddleware()` como fuente de verdad para la configuración del HTTP kernel. El archivo `app/Http/Kernel.php` que extiende `Illuminate\Foundation\Http\Kernel` es el mecanismo de Laravel 10 y anteriores.

**Duplicidad confirmada**: los aliases `permission` y `app.access` están definidos en **ambos** lugares:

```php
// app/Http/Kernel.php (legacy)
protected $middlewareAliases = [
    'permission' => \App\Http\Middleware\CheckPermission::class,
    'app.access'  => \App\Http\Middleware\CheckAppAccess::class,
    ...
];

// bootstrap/app.php (activo L11)
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'permission' => \App\Http\Middleware\CheckPermission::class,
        'app.access'  => \App\Http\Middleware\CheckAppAccess::class,
    ]);
})
```

La fuente operativa real en Laravel 11 es `bootstrap/app.php`. `app/Http/Kernel.php` es código legacy que puede causar confusión pero no rompe la aplicación actualmente.

### DC-007S — Riesgos adicionales

#### RIESGO-007 — Doble registro de providers [BAJO]

| Campo       | Valor                                                          |
|-------------|----------------------------------------------------------------|
| Severidad   | BAJO                                                           |
| Tipo        | Riesgo de mantenimiento / inconsistencia de configuración      |
| Causa raíz  | `AppServiceProvider`, `FortifyServiceProvider`, `JetstreamServiceProvider` en `config/app.php` Y `bootstrap/providers.php` |
| Impacto     | Laravel deduplica en runtime; no produce errores actualmente. Riesgo futuro si se modifica uno sin el otro. |
| Recomendación | **C — Refactorizar**: eliminar de `config/app.php` y usar solo `bootstrap/providers.php` (convención L11). |

#### RIESGO-008 — `app/Http/Kernel.php` legacy [BAJO]

| Campo       | Valor                                                          |
|-------------|----------------------------------------------------------------|
| Severidad   | BAJO                                                           |
| Tipo        | Riesgo de mantenimiento / código legacy                        |
| Causa raíz  | Laravel 10 Kernel coexiste con configuración Laravel 11 en `bootstrap/app.php` |
| Impacto     | Sin impacto operativo actual; confusión para mantenedores sobre cuál es la fuente de verdad. |
| Recomendación | **D — Eliminar** `app/Http/Kernel.php` una vez migrados todos los middleware al flujo L11. |

### Lista de Riesgos Actualizada (completa)

| ID | Descripción | Severidad | Estado en prod. | Acción recomendada |
|----|-------------|-----------|-----------------|-------------------|
| R-001 | Fatal error en update/reset password, profile, deleteUser | CRÍTICO | LATENTE | Corregir inmediatamente |
| R-002 | `app/Models/User.php` → Spatie trait no instalado | CRÍTICO | ACTIVO (bloquea tests) | Eliminar archivo |
| R-003 | Enums inexistentes en `app/Models/Inventario/Bien.php` | ALTO | Inactivo (sin rutas) | Eliminar archivo |
| R-004 | `RolInstitucional` no existe en `SincronizarRolesYPermisosCoreAction` | ALTO | Inactivo | Eliminar archivo |
| R-005 | `<x-admin-layout>` no existe en vistas paralelas | MEDIO | Inactivo | Eliminar vistas |
| R-006 | Gates con slug incorrecto en `BienResponsableController` | BAJO | Inactivo (sin rutas) | Eliminar controlador |
| R-007 | Doble registro de providers en config/app.php y bootstrap/providers.php | BAJO | Mitigado (deduplicación) | Refactorizar |
| R-008 | `app/Http/Kernel.php` legacy en proyecto Laravel 11 | BAJO | Sin impacto | Eliminar |

---

*Documento generado como parte de AUDIT-CORE-DEADCODE-001.*
*Fecha: 2026-06-11. Auditor: Claude Code (claude-sonnet-4-6).*
*Basado en AUDIT-IEE-001 SHA: `0fc747e`.*
*Suplemento post-commit añadido en misma sesión con hallazgos de bootstrap/app.php.*
