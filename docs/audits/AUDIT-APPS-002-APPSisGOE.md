# AUDIT-APPS-002 — Módulo Apps: Auditoría Arquitectónica para APPSisGOE

**Fecha:** 2026-06-14  
**Versión auditada:** BhagamApps v1.4.0  
**Auditor:** Claude Sonnet 4.6 (arquitectónico — sin modificaciones al código)  
**Propósito:** Base para definir el CORE de registro, visibilidad y gobernanza de módulos en APPSisGOE

---

## 1. Arquitectura Completa

### 1.1 Tablas de Base de Datos

#### Tabla: `apps`

Esquema construido por acumulación de migraciones:

| Columna | Tipo | Nullable | Default | Descripción |
|---------|------|----------|---------|-------------|
| `id` | bigint UNSIGNED AI PK | NO | — | Identificador autoincremental |
| `nombre` | varchar(255) | NO | — | Nombre visible de la aplicación |
| `slug` | varchar(255) UNIQUE | SÍ | NULL | Identificador técnico (kebab-case). Clave de referencia en middleware y visibilidad |
| `ruta` | varchar(255) | NO | — | URL de entrada al módulo (ej. `/inventario`) |
| `descripcion` | text | SÍ | NULL | Descripción institucional del módulo |
| `imagen` | varchar(255) | SÍ | NULL | Ruta a imagen legacy (PNG en vendor/adminlte) |
| `icono` | varchar(255) | SÍ | NULL | Clase FontAwesome (ej. `fas fa-boxes`) |
| `color` | varchar(20) | SÍ | NULL | Color hexadecimal para la tarjeta del dashboard |
| `orden` | int UNSIGNED | NO | 99 | Orden de aparición en el dashboard (0 = primero) |
| `habilitada` | tinyint(1) | NO | 1 | Toggle de visibilidad global del módulo |
| `user_id` | bigint UNSIGNED | SÍ | NULL | Propietario legacy (sin FK — campo heredado del sistema anterior) |
| `created_at` | timestamp | SÍ | NULL | — |
| `updated_at` | timestamp | SÍ | NULL | — |

**Índices:** PK(`id`), UNIQUE(`slug`)  
**Nota arquitectónica crítica:** `user_id` no tiene FK constraint. Es un campo residual del sistema anterior donde cada app tenía un "dueño". En el sistema actual no se usa.

---

#### Tabla: `app_role`

Pivot que determina acceso de roles a módulos.

| Columna | Tipo | Nullable | Default | Descripción |
|---------|------|----------|---------|-------------|
| `id` | bigint UNSIGNED AI PK | NO | — | — |
| `app_id` | bigint UNSIGNED | NO | — | FK → `apps.id` ON DELETE CASCADE |
| `role_id` | bigint UNSIGNED | NO | — | FK → `roles.id` ON DELETE CASCADE |
| `created_at` | timestamp | SÍ | NULL | — |
| `updated_at` | timestamp | SÍ | NULL | — |

**Índices:** PK(`id`), UNIQUE(`app_id`, `role_id`), FK(`app_id`), FK(`role_id`)  
**Semántica:** Si un rol está en `app_role` para una app, todos los usuarios con ese rol verán esa app en su dashboard.

---

#### Tabla: `app_user`

Pivot que permite asignación individual de módulos por usuario.

| Columna | Tipo | Nullable | Default | Descripción |
|---------|------|----------|---------|-------------|
| `id` | bigint UNSIGNED AI PK | NO | — | — |
| `user_id` | bigint UNSIGNED | NO | — | FK → `users.id` ON DELETE CASCADE |
| `app_id` | bigint UNSIGNED | NO | — | FK → `apps.id` ON DELETE CASCADE |
| `role_id` | bigint UNSIGNED | SÍ | NULL | FK → `roles.id` ON DELETE SET NULL (campo legacy, eliminado en migración posterior) |
| `activo` | tinyint(1) | NO | 1 | Toggle individual de acceso del usuario a esta app |
| `created_at` | timestamp | SÍ | NULL | — |
| `updated_at` | timestamp | SÍ | NULL | — |

**Índices:** PK(`id`), UNIQUE(`user_id`, `app_id`), FK(`user_id`), FK(`app_id`)  
**Semántica:** Permite excepciones individuales respecto al acceso por rol. La migración `2026_06_09_000003_drop_app_user_role_id` eliminó el campo `role_id` de esta tabla (fue un diseño descartado).

---

#### Nota: Dependencia crítica en tabla `roles`

La tabla `roles` (módulo User) contiene la columna `app_id UNSIGNED BIGINT NULLABLE` con FK → `apps.id` ON DELETE SET NULL. Este diseño creó una dependencia circular: el módulo User no puede funcionar si la tabla `apps` no existe. En la práctica, esto significa que **Apps es una dependencia de arranque del sistema**, no un módulo opcional.

---

### 1.2 Modelos Eloquent

#### `Modules\Apps\Entities\App`

```
$fillable: nombre, slug, ruta, descripcion, imagen, icono, color, orden, habilitada
$casts:    habilitada → boolean, orden → integer
$table:    'apps'
```

**Relaciones:**
- `user()` — `belongsToMany(User, 'app_user', 'app_id', 'user_id')->withPivot('activo')->withTimestamps()`
- `roles()` — `belongsToMany(Role, 'app_role', 'app_id', 'role_id')->withTimestamps()`

**Método estático clave — `visiblesPara(User $user): Collection`:**

```php
public static function visiblesPara(User $user): Collection
{
    $version = (int) cache()->get('apps.cache_version', 0);
    $key = "apps.visibles.{$user->id}.v{$version}";

    return cache()->remember($key, 300, function () use ($user) {
        return static::where('habilitada', true)
            ->where(function ($query) use ($user) {
                $query->whereHas('roles', function ($q) use ($user) {
                    $q->where('roles.id', $user->role_id);
                })->orWhereHas('user', function ($q) use ($user) {
                    $q->where('users.id', $user->id)
                      ->where('app_user.activo', true);
                });
            })
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();
    });
}
```

**Lógica:** OR lógico entre acceso por rol (`app_role`) y acceso individual activo (`app_user.activo = true`). Resultado cacheado 300 segundos en caché versioned.

---

### 1.3 Servicios

No existe un AppService dedicado. La lógica de negocio está directamente en el modelo `App` (método `visiblesPara`) y en los componentes Livewire (`AppsIndex`, `EditarXxx`).

---

### 1.4 Middleware — `CheckAppAccess`

**Archivo:** `app/Http/Middleware/CheckAppAccess.php`  
**Alias:** `app.access` (registrado en `app/Http/Kernel.php`)

```php
public function handle(Request $request, Closure $next, string $slug): Response
{
    $user = $request->user();

    if (! $user) {
        abort(403, 'No tienes acceso a este módulo.');
    }

    if (! App::visiblesPara($user)->contains('slug', $slug)) {
        abort(403, 'No tienes acceso al módulo "' . $slug . '".');
    }

    return $next($request);
}
```

**Uso:** `middleware(['web', 'auth', 'app.access:{slug}'])`  
El parámetro `{slug}` es el slug de la app en la tabla `apps`.

---

### 1.5 Comandos Artisan

#### `apps:sync` — `Modules\Apps\Console\Commands\SyncApps`

**Propósito:** Registrar automáticamente en la tabla `apps` los módulos nWidart instalados que no tengan registro aún.

**Lógica:**
```
Para cada módulo en Module::all():
  slug = Str::slug(nombre_modulo)
  App::firstOrCreate(
    ['slug' => slug],
    ['nombre' => nombre, 'ruta' => '/' . slug, 
     'descripcion' => "Módulo {nombre}", 
     'habilitada' => false,   ← siempre deshabilitado al crear
     'orden' => 99]
  )
```

**Comportamiento:**
- Solo CREA registros, nunca actualiza ni elimina.
- Los módulos recién descubiertos se crean con `habilitada = false` — deben activarse manualmente.
- No gestiona `app_role` — los vínculos de acceso por rol deben configurarse por separado.
- Es idempotente: ejecutar múltiples veces no genera duplicados (firstOrCreate por slug).

---

### 1.6 Seeders — Aplicaciones Registradas

#### `AppSeeder` — Catálogo canónico (12 aplicaciones)

| Orden | Nombre | Slug | Ruta | Habilitada | Color | Icono |
|-------|--------|------|------|------------|-------|-------|
| 0 | Administración del Sistema | `admin-sistema` | `/admin/backups` | ✅ | `#6c757d` | `fas fa-server` |
| 1 | Usuarios | `user` | `/user` | ✅ | `#3a3f8c` | `fas fa-users` |
| 2 | Inventario | `inventario` | `/inventario` | ✅ | `#28a745` | `fas fa-boxes` |
| 3 | Aplicaciones | `apps` | `/apps/admin` | ✅ | `#6610f2` | `fas fa-th-large` |
| 4 | Biblioteca | `biblioteca` | `/biblioteca` | ❌ | `#fd7e14` | `fas fa-book` |
| 10 | SINAI vs SIMAT | `sinai-vs-simat` | `/SvS` | ❌ | `#17a2b8` | `fas fa-balance-scale` |
| 11 | Planeador | `planeador` | `/planeador` | ❌ | `#20c997` | `fas fa-calendar-alt` |
| 12 | EduInclusiva | `edu-inclusiva` | `/eduInclusiva` | ❌ | `#e83e8c` | `fas fa-universal-access` |
| 13 | CTE | `cte` | `/cte` | ❌ | `#6c757d` | `fas fa-chalkboard-teacher` |
| 14 | Creador de Exámenes | `creador-examenes` | `/creadorExamenes` | ❌ | `#007bff` | `fas fa-file-alt` |
| 15 | Préstamo Tabletas | `prestamo-tabletas` | `/prestamoTabletas` | ❌ | `#343a40` | `fas fa-tablet-alt` |
| 16 | Evaluar para Avanzar | `evaluar-para-avanzar` | `/evaluarParaAvanzar` | ❌ | `#ffc107` | `fas fa-chart-line` |

**Nota:** Los 9 módulos deshabilitados constituyen el roadmap implícito del sistema.

#### `AppsPermissionSeeder` — Permiso legacy

Siembra el permiso `administrar-apps` y lo asigna a Administrador y Rector.
(Los permisos CRUD completos se siembran vía migración `2026_06_09_000001_add_apps_crud_permissions`.)

#### Matriz de acceso por rol (`assign_app_roles_rbac_recovery`)

| Rol | Módulos accesibles |
|-----|-------------------|
| Administrador | user, inventario, apps |
| Rector | user, inventario, apps |
| Coordinador | user, inventario |
| Auxiliar | inventario |
| Docente | inventario |

---

## 2. Flujo de Descubrimiento de Aplicaciones

Existen dos mecanismos de registro de apps:

### 2.1 Registro Manual (seeder)

```
AppSeeder::run()
    → App::updateOrCreate(['slug' => slug], [...datos...])
    → Registro en tabla 'apps' con todos los metadatos visuales
    → habilitada puede ser true o false
    → NO asigna app_role (eso es responsabilidad de migraciones/seeders separados)
```

### 2.2 Descubrimiento Automático (artisan)

```
php artisan apps:sync
    → Module::all() — lista módulos nWidart instalados
    → Por cada módulo:
        slug = Str::slug(nombreModulo)
        App::firstOrCreate(['slug' => slug], {habilitada: false, orden: 99, ...})
    → Solo crea registros nuevos, no actualiza existentes
    → Módulos nuevos quedan deshabilitados hasta configuración manual
```

### 2.3 Creación desde UI (Livewire)

```
AppsIndex::store()
    → Requiere permiso 'crear-apps'
    → Crea App con habilitada=false siempre
    → NO asigna roles ni usuarios
    → Invalida caché: cache()->increment('apps.cache_version')
```

**Diagrama de descubrimiento:**

```
Código fuente              apps:sync               Base de datos
(Módulos nWidart)     ─────────────────────►    tabla 'apps'
                       Module::all()              (habilitada=false)
                       Str::slug(nombre)               │
                       App::firstOrCreate              │ Admin activa
                                                       ▼
                                                (habilitada=true)
                                                       │
                                               assign app_role
                                               (migración o UI)
```

---

## 3. Flujo de Visibilidad

### 3.1 Mecanismo central: `App::visiblesPara($user)`

```
App::visiblesPara($user)
    │
    ├─ Consulta caché versioned: "apps.visibles.{userId}.v{version}"
    │   ├─ HIT: retorna colección cacheada (TTL 300s)
    │   └─ MISS: ejecuta query
    │       └─ SELECT * FROM apps
    │          WHERE habilitada = true
    │          AND (
    │              EXISTS (SELECT 1 FROM app_role
    │                      WHERE app_role.app_id = apps.id
    │                      AND app_role.role_id = users.role_id)
    │              OR
    │              EXISTS (SELECT 1 FROM app_user
    │                      WHERE app_user.app_id = apps.id
    │                      AND app_user.user_id = users.id
    │                      AND app_user.activo = true)
    │          )
    │          ORDER BY orden ASC, nombre ASC
```

### 3.2 Precedencia entre `app_role` y `app_user`

**No hay precedencia — es OR lógico.** Si el usuario tiene acceso por cualquiera de las dos vías, ve la app.  
`app_user` no es un "override" sino un canal adicional de acceso.

### 3.3 Invalidación de caché

```
cache()->increment('apps.cache_version')
```

Incrementar la versión global invalida las entradas de TODOS los usuarios simultáneamente, sin necesidad de conocer sus IDs. Se llama en:
- `AppsIndex::toggleHabilitada()`
- `AppsIndex::guardarRoles()`
- `AppsIndex::guardarUsuarios()`
- `AppsIndex::store()`
- `AppsIndex::delete()`
- Todos los `EditarXxxApp::guardar()`

### 3.4 HomeController — Dashboard principal

```php
class HomeController extends Controller
{
    public function index()
    {
        $apps = App::visiblesPara(auth()->user());
        return view('ppal.index', compact('apps'));
    }
}
```

Carga única llamada a `visiblesPara` — sin lógica adicional.

### 3.5 Middleware CheckAppAccess — Protección de rutas

```
Request → Middleware app.access:{slug}
    │
    ├─ $user = request()->user()
    │   └─ null → abort(403)
    │
    └─ App::visiblesPara($user)->contains('slug', $slug)
        ├─ true  → $next($request)  ← acceso concedido
        └─ false → abort(403, "No tienes acceso al módulo '{slug}'.")
```

**Módulos protegidos por `app.access`:**

| Módulo | Ruta | Slug verificado |
|--------|------|----------------|
| Inventario | `/inventario/*` | `inventario` |
| User | `/users/*` | `user` |
| AdminSistema | `/admin/*` | `admin-sistema` |
| ActivityLog | `/admin/*` | `admin-sistema` |

---

## 4. Flujo de Sincronización

`apps:sync` es minimalista — solo crea, nunca actualiza ni elimina:

```
1. Module::all() → lista todos los módulos nWidart activos en el filesystem
2. Para cada módulo:
   a. Genera slug con Str::slug(nombre)
   b. Llama App::firstOrCreate(['slug' => slug], defaults)
   c. Si wasRecentlyCreated: reporta "+ Creada"
   d. Si ya existía: reporta "~ Existente"
3. Retorna conteo de nuevas y existentes
```

**Lo que NO hace `apps:sync`:**
- No detecta módulos eliminados del filesystem
- No deshabilita apps huérfanas
- No actualiza datos de apps existentes
- No configura `app_role`
- No maneja versiones ni compatibilidades

---

## 5. Dependencias

### 5.1 Módulos que usan el middleware `app.access`

| Módulo | Archivo de rutas | Slug |
|--------|-----------------|------|
| Inventario | `Modules/Inventario/routes/web.php:15` | `inventario` |
| User | `Modules/User/Routes/web.php:12` | `user` |
| AdminSistema | `Modules/AdminSistema/Routes/web.php:8` | `admin-sistema` |
| ActivityLog | `Modules/ActivityLog/Routes/web.php:6` | `admin-sistema` |

### 5.2 Módulos con seeders que tocan `app_role`

| Módulo | Archivo | Acción |
|--------|---------|--------|
| User | `Modules/User/Database/Seeders/AppRoleSeeder.php` | Siembra vínculos app_role |
| Inventario | `Modules/Inventario/Database/Migrations/2026_06_09_000006_assign_inventario_app_to_coordinador.php` | Asigna inventario a Coordinador |
| Apps | `Modules/Apps/database/migrations/2026_06_11_200000_assign_app_roles_rbac_recovery.php` | Recuperación de la matriz completa de acceso |

### 5.3 Dependencias estructurales

- `roles.app_id` FK → `apps.id` (en módulo User) — relación circular que convierte a Apps en dependencia de boot
- `app/Http/Controllers/Ppal/HomeController.php` importa `Modules\Apps\Entities\App` directamente
- `BackupExportSeeders` y `BackupRestoreFromZip` exportan/importan la tabla `apps`
- `InstitutionalRestoreSeeder` restaura el estado completo incluyendo `apps`, `app_role`, `app_user`

### 5.4 Módulo User como co-propietario de RBAC de Apps

El módulo User contiene los modelos `Role` y `Permission` que Apps necesita. Apps depende de User, pero User también tiene FK hacia Apps. Esta dependencia circular es el indicador más claro de que Apps no es un módulo ordinario: es infraestructura compartida.

---

## 6. Evaluación Estratégica

### 6.1 ¿Es Apps un módulo de negocio?

**No.**

Un módulo de negocio encapsula un dominio funcional de la institución: Inventario gestiona bienes, User gestiona personas. Apps no gestiona ningún concepto del dominio educativo. No tiene entidades de negocio propias — una "app" no existe en el mundo real de la institución, solo existe en el software.

### 6.2 ¿Es Apps un servicio compartido?

**Sí, parcialmente — pero con alcance insuficiente para el rol que desempeña.**

Apps provee:
- El catálogo de módulos disponibles (`apps` table)
- El mecanismo de visibilidad por rol y por usuario
- El middleware de acceso a rutas
- La invalidación de caché de visibilidad

Estos son servicios transversales que todos los módulos consumen. Sin Apps, ningún módulo es accesible (el middleware `app.access` bloquea todo). Sin Apps, el dashboard está vacío. Sin Apps, los seeders de roles no pueden vincular apps a roles.

### 6.3 ¿Debe Apps convertirse en parte del CORE de APPSisGOE?

**Sí — y APPSisGOE ya tiene la mayor parte de esta lógica implementada de forma más sofisticada.**

APPSisGOE tiene un sistema de módulos con 6 estados (Pendiente, Instalando, Activo, Inactivo, Error, Desinstalado), manifiestos con hash/versión, Actions con DTOs y dry-run. Lo que le falta es exactamente lo que Apps tiene: visibilidad por rol y por usuario, middleware de acceso, metadatos visuales, y UI de gestión de acceso.

La migración correcta no es "mover Apps a APPSisGOE" sino "completar el CORE de APPSisGOE con los mecanismos de visibilidad institucional que BhagamAppsModular ya resolvió".

---

## 7. Diseño Objetivo para APPSisGOE

### 7.1 Registro de Módulos

APPSisGOE ya tiene tabla `modules` con manifiestos. Agregar campos visuales:

```sql
ALTER TABLE modules ADD COLUMN icono VARCHAR(255) NULL;
ALTER TABLE modules ADD COLUMN color VARCHAR(20) NULL;
ALTER TABLE modules ADD COLUMN orden INT UNSIGNED DEFAULT 99;
ALTER TABLE modules ADD COLUMN ruta_entrada VARCHAR(255) NULL;
```

El seeder/installer de cada módulo declara sus metadatos visuales en el manifiesto (`module.json`).

### 7.2 Versionado

APPSisGOE ya resuelve esto con manifiestos + hash de integridad. Mantener.

### 7.3 Compatibilidades y Dependencias entre Módulos

Declarar en `module.json`:
```json
{
  "key": "inventario",
  "requires": ["comunidad"],
  "conflicts": [],
  "version": "1.1.0",
  "min_core": "1.6.0"
}
```

El ModuleInstallAction valida `requires` antes de activar. Si un módulo requerido no está Activo, la instalación falla con mensaje claro.

### 7.4 Visibilidad Institucional (brecha principal)

Crear tablas `module_role` y `module_user`:

```sql
CREATE TABLE module_role (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,  -- FK → modules.id CASCADE
    role_id   BIGINT UNSIGNED NOT NULL,  -- FK → roles.id CASCADE
    UNIQUE (module_id, role_id)
);

CREATE TABLE module_user (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,  -- FK → modules.id CASCADE
    user_id   BIGINT UNSIGNED NOT NULL,  -- FK → users.id CASCADE
    activo    TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE (module_id, user_id)
);
```

Implementar `Module::visiblesPara(User $user)` con caché versioned (mismo patrón que `App::visiblesPara`).

### 7.5 Activación y Desactivación Controlada

APPSisGOE ya tiene `ModuleActivateAction` y `ModuleDeactivateAction`. Agregar:
- Verificación de dependientes inversos antes de desactivar (si módulo B requiere A, no desactivar A mientras B esté Activo)
- Propagación de invalidación de caché de visibilidad al cambiar estado

### 7.6 Middleware de Acceso

Crear `ModuloAccessMiddleware` en el CORE:

```php
class ModuloAccessMiddleware
{
    public function handle(Request $request, Closure $next, string $key): Response
    {
        $user = $request->user();
        if (!$user) abort(403);
        
        if (!Module::visiblesPara($user)->contains('key', $key)) {
            abort(403, "No tienes acceso al módulo '{$key}'.");
        }
        
        return $next($request);
    }
}
```

Cada módulo declara en sus rutas: `middleware('modulo.access:inventario')`

### 7.7 Gobernanza

| Acción | Capacidad requerida | Roles por defecto |
|--------|--------------------|--------------------|
| Ver catálogo de módulos | `modulos:ver` | Administrador, Rector |
| Instalar módulo | `modulos:instalar` | Administrador |
| Activar/desactivar módulo | `modulos:activar` | Administrador, Rector |
| Asignar módulo a rol | `modulos:asignar_rol` | Administrador, Rector, Coordinador |
| Asignar módulo a usuario | `modulos:asignar_usuario` | Administrador, Rector, Coordinador |
| Desinstalar módulo | `modulos:desinstalar` | Administrador |

### 7.8 Diagrama del Modelo Propuesto para APPSisGOE

```
┌─────────────────────────────────────────────────────────────────┐
│                         CORE APPSisGOE                          │
│                                                                 │
│  ┌──────────┐     ┌─────────────┐     ┌─────────────────────┐  │
│  │ modules  │────►│ module_role │◄────│ roles               │  │
│  │          │     └─────────────┘     └─────────────────────┘  │
│  │ -key     │                                    ▲              │
│  │ -name    │     ┌─────────────┐               │              │
│  │ -version │────►│ module_user │◄──────────────┤              │
│  │ -status  │     │ -activo     │     ┌──────────┴──────────┐  │
│  │ -orden   │     └─────────────┘     │ users               │  │
│  │ -icono   │                         └─────────────────────┘  │
│  │ -color   │                                                   │
│  │ -ruta_   │     Module::visiblesPara($user)                  │
│  │  entrada │     → OR(module_role, module_user)               │
│  └──────────┘     → caché versioned 300s                       │
│                                                                 │
│  Middleware: 'modulo.access:{key}'                              │
│  → Module::visiblesPara($user)->contains('key', $key)          │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 8. Comparación BhagamAppsModular vs APPSisGOE

### 8.1 Lo que APPSisGOE YA SUPERA a BhagamAppsModular

| Dimensión | BhagamAppsModular | APPSisGOE |
|-----------|-------------------|-----------|
| Estados del módulo | 2 (habilitada/deshabilitada) | 6 (Pendiente/Instalando/Activo/Inactivo/Error/Desinstalado) |
| Manifiestos | No (datos en seeder) | Sí (module.json con hash, versión, compatibilidad) |
| Ciclo de vida | No existe | ModuleInstallAction, ActivateAction, DeactivateAction |
| Arquitectura | Fat model (lógica en App::visiblesPara) | Clean Architecture (Actions, DTOs, ReadServices) |
| Sistema de permisos | RBAC casero | Spatie Permission (estándar de industria) |
| Carga condicional | Siempre carga todos | ModuleServiceProvider solo carga módulos Activos |
| Progreso de ops | No | Sí (progress reporting en tiempo real) |
| Dry-run | No | Sí (ModuleInstallAction puede simular sin ejecutar) |

### 8.2 Brechas en APPSisGOE que BhagamAppsModular resuelve

| Brecha en APPSisGOE | Solución en BhagamAppsModular | Complejidad de implementar |
|--------------------|-------------------------------|---------------------------|
| No hay `module_role` | `app_role` M:M con UNIQUE | Baja |
| No hay `module_user` | `app_user` M:M con `activo` | Baja |
| No hay `Module::visiblesPara()` | `App::visiblesPara($user)` con caché versioned | Media |
| No hay middleware de acceso por visibilidad | `CheckAppAccess` / alias `app.access:{slug}` | Baja |
| No hay dashboard de módulos por usuario | HomeController + ppal.index | Media |
| No hay metadatos visuales en `modules` | `icono`, `color`, `orden`, `ruta_entrada` | Baja |
| No hay UI para gestionar `module_role/user` | `AppsIndex` Livewire con modales | Alta |

### 8.3 Conclusión de comparación

APPSisGOE tiene la arquitectura más sólida, pero le falta el modelo de **visibilidad institucional** (qué módulos ve cada usuario según su rol). BhagamAppsModular resolvió este problema elegantemente con 3 tablas + 1 método estático + 1 middleware. APPSisGOE debe incorporar exactamente esa solución, adaptada a su arquitectura limpia.

---

## 9. Recomendación Final

**El módulo Apps de BhagamAppsModular NO debe migrarse como módulo separado a APPSisGOE. Su lógica debe absorberse en el CORE.**

### Plan de implementación recomendado

**Nivel 1 — Datos (1-2 sesiones):**
- Agregar campos `icono`, `color`, `orden`, `ruta_entrada` al modelo `Module` existente
- Crear tabla `module_role`
- Crear tabla `module_user`

**Nivel 2 — Lógica (1 sesión):**
- Implementar `Module::visiblesPara(User $user)` con caché versioned
- Crear `ModuloAccessMiddleware` con alias `modulo.access`
- Agregar Capacidades: `ModulosVisibilidadAdministrar`, `ModulosRolAsignar`, `ModulosUsuarioAsignar`

**Nivel 3 — UI (2-3 sesiones):**
- Panel de gestión de visibilidad por módulo (qué roles/usuarios tienen acceso)
- Dashboard institucional que muestre módulos según `Module::visiblesPara($user)`
- Componentes Livewire para toggle de acceso por rol y por usuario

**Nivel 4 — Gobernanza (1 sesión):**
- Validación de dependencias antes de desactivar
- Propagación de invalidación de caché en cambios de estado de módulos
- Log de cambios de visibilidad en ActivityLog