# AUDIT-APPS-001 — Estado del Módulo Apps

**Fecha:** 2026-06-08
**Auditor:** Claude (Sonnet 4.6) — IMPL-APPS-001
**Módulo auditado:** `Modules/Apps`
**Estado:** Completado

---

## 1. Inventario de archivos

| Archivo | Descripción |
|---|---|
| `Entities/App.php` | Modelo Eloquent — tabla `apps` |
| `Http/Controllers/AppController.php` | Controlador de recurso |
| `Providers/AppsServiceProvider.php` | ServiceProvider mínimo |
| `Providers/EventServiceProvider.php` | Vacío (scaffolding) |
| `Providers/RouteServiceProvider.php` | Vacío (scaffolding) |
| `database/migrations/2014_10_12_100001_create_apps_table.php` | Crea tabla `apps` |
| `database/migrations/2025_05_18_0045512_create_app_user_table.php` | Crea pivot `app_user` |
| `database/seeders/AppSeeder.php` | Seed de 12 aplicaciones |
| `database/seeders/AppsDatabaseSeeder.php` | Orquestador de seeders |
| `routes/web.php` | Rutas resource para apps |
| `resources/views/index.blade.php` | Vista parcial de listado (desktop + móvil) |
| `resources/views/components/layouts/master.blade.php` | Layout genérico vacío |
| `module.json` | Metadata nWidart |

---

## 2. Estado del modelo `App.php`

```php
// Campos en $fillable
'nombre', 'ruta', 'imagen', 'user_id', 'habilitada'

// Relación existente
public function user() → belongsToMany(User, 'app_user', 'app_id', 'user_id')
```

**Problemas detectados:**
- `user_id` en la tabla `apps` es redundante con la pivot `app_user`
- El campo `imagen` usa rutas de imágenes de AdminLTE (no íconos vectoriales)
- **Faltan**: `slug`, `descripcion`, `icono`, `color`, `orden`
- No existe scope `visiblesPara($user)`
- No existe relación `roles()` con la tabla de roles

---

## 3. Estado del controlador `AppController.php`

```php
public function index()
{
    $apps = App::where('user_id', auth()->id())->get(); // ← BUG
    return view('apps::index', compact('apps'));
}
```

**Bug crítico:** Usa `user_id` de la tabla `apps` como filtro de usuario, ignorando por completo el pivot `app_user`. La vista de administración nunca retornó datos correctos.

---

## 4. Estado de las migraciones

### `apps` (2014_10_12_100001)
| Columna | Tipo | Notas |
|---|---|---|
| id | bigIncrements | |
| nombre | string | |
| ruta | string | |
| imagen | string nullable | Ruta imagen |
| user_id | unsignedBigInteger nullable | Redundante — no FK formal |
| habilitada | boolean default true | |
| timestamps | | |

**Falta:** slug, descripcion, icono, color, orden

### `app_user` (2025_05_18)
| Columna | Tipo | Notas |
|---|---|---|
| id | bigIncrements | |
| user_id | FK → users | CASCADE |
| app_id | FK → apps | CASCADE |
| role_id | FK → roles nullable | SET NULL |
| activo | boolean default true | |
| timestamps | | |
| UNIQUE(user_id, app_id) | | |

**Observación:** El `role_id` en `app_user` mezcla dos conceptos (asignación por usuario vs asignación por rol). Esto debe separarse en una tabla `app_role` dedicada.

---

## 5. Estado del seeder `AppSeeder.php`

- Registra 12 aplicaciones con `user_id = 1` (hardcoded al admin)
- 4 habilitadas, 8 deshabilitadas
- No rellena `slug`, `descripcion`, `icono`, `color`, `orden` (columnas pendientes)
- La imagen apunta a `vendor/adminlte/dist/img/Apps/{imagen}`

---

## 6. Estado de la vista `index.blade.php`

- Vista parcial incluida desde `ppal.index`
- Renderiza correctamente desktop y móvil
- Usa `$app->habilitada` para mostrar/deshabilitar iconos
- **No usa Livewire** — renderizado estático
- Estilos inline en exceso (refactorizable)

---

## 7. Estado de rutas

```php
Route::resource('apps', AppController::class)->names('apps.apps');
// middleware: auth, verified
```

Registra las 7 rutas estándar de resource. Solo se usa `index` en producción.

---

## 8. Estado del ServiceProvider

```php
public function boot()
{
    $this->loadViewsFrom(__DIR__ . '/../resources/views', 'apps');
    $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
}
```

**Ausente:**
- `loadMigrationsFrom` — migraciones no se cargan automáticamente
- Registro de componentes Livewire
- Registro de comandos Artisan

---

## 9. Integración con el core

### `HomeController.php`
```php
$apps = auth()->user()->apps()->wherePivot('activo', true)->where('habilitada', true)->get();
```
Correcto: consulta `app_user` correctamente.

### `User.php`
```php
public function apps()
{
    return $this->belongsToMany(App::class, 'app_user', 'user_id', 'app_id');
}
```
Correcto: relación inversa funcional.

### `Role.php`
```php
protected $fillable = ['nombre', 'descripcion', 'app_id'];
```
`app_id` en roles = "este rol pertenece a una app" (BelongsTo implícito, no implementado). No debe confundirse con la futura pivot `app_role`.

---

## 10. Componentes Livewire

**Ninguno existe en el módulo Apps.** El dashboard muestra apps sin interactividad Livewire.

---

## 11. Comandos Artisan

**Ninguno existe en el módulo Apps.** El comando `apps:sync` no está implementado.

---

## 12. Resumen de hallazgos

| Hallazgo | Severidad | Acción recomendada |
|---|---|---|
| Bug en AppController::index() | Alta | Corregir con relación correcta |
| Faltan columnas en tabla apps | Media | Migración additive |
| No existe app_role pivot | Media | Nueva migración |
| No existe App::visiblesPara() | Alta | Implementar en modelo |
| ServiceProvider no carga migraciones | Media | Agregar loadMigrationsFrom |
| No hay Livewire en Apps | Baja | Implementar AppsIndex |
| No existe apps:sync command | Baja | Implementar comando |
| Seeder hardcodea user_id=1 | Baja | Refactorizar seeder |

---

## 13. Conclusión

El módulo Apps tiene una base funcional (pivot `app_user`, vista, seeder) pero incompleta para los objetivos del sistema. Las brechas son: autorización por rol, campos de metadatos, panel de administración y sincronización automática con módulos nWidart. La implementación propuesta en ADR-APPS-001 es aditiva y no rompe la funcionalidad existente.
