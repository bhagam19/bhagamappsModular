# IMPL-013 — Apps Module Access Enforcement

**Fecha:** 2026-06-08
**Estado:** Completado
**Relacionado con:** AUDIT-APPS-003, ADR-008, IMPL-APPS-002, AUDIT-APPS-002

---

## Resumen ejecutivo

Se completó la transición del módulo Apps desde un sistema de visibilidad de aplicaciones
hacia el sistema oficial de control de acceso a módulos de BhagamAppsModular.

La brecha arquitectónica identificada en AUDIT-APPS-003 — donde Apps controlaba visibilidad
pero no acceso efectivo — quedó cerrada mediante seis hallazgos implementados:

1. **H-001/H-002** — Middleware `CheckAppAccess` + enforcement en rutas de Inventario y User
2. **H-003** — Invalidación de caché al cambiar rol de usuario
3. **H-004** — Menú lateral dinámico desde `App::visiblesPara()`
4. **H-005** — Gestión funcional de `app_user` (asignación directa usuario → app)
5. **H-006** — Corrección de referencia de controlador en `Modules/Apps/routes/api.php`

---

## Archivos creados

| Archivo | Descripción |
|---|---|
| `app/Http/Middleware/CheckAppAccess.php` | Middleware que valida acceso a módulo via `App::visiblesPara()` |

---

## Archivos modificados

| Archivo | Cambio |
|---|---|
| `app/Http/Kernel.php` | Registro del alias `app.access → CheckAppAccess` |
| `bootstrap/app.php` | Registro del alias `app.access → CheckAppAccess` (Laravel 11) |
| `Modules/Inventario/routes/web.php` | Middleware `app.access:inventario` en grupo principal |
| `Modules/User/Routes/web.php` | Middleware `app.access:user` en grupo principal |
| `Modules/User/Livewire/User/EditarRolUser.php` | `cache()->increment('apps.cache_version')` en `guardar()` |
| `resources/views/vendor/adminlte/partials/sidebar/left-sidebar.blade.php` | Sección "MIS MÓDULOS" dinámica desde `App::visiblesPara()` |
| `Modules/Apps/Livewire/Apps/AppsIndex.php` | Métodos `abrirModalUsuarios`, `guardarUsuarios`, `cerrarModalUsuarios` |
| `Modules/Apps/resources/views/livewire/apps/apps-index.blade.php` | Modal de gestión de usuarios directos + botón en columna Acciones |
| `Modules/Apps/routes/api.php` | Corregida referencia `AppsController` → `AppController` |

---

## Detalle técnico por hallazgo

### H-001 — Middleware `CheckAppAccess`

**Archivo:** `app/Http/Middleware/CheckAppAccess.php`

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

El middleware usa `App::visiblesPara()` que ya incluye caché de 300s. No genera queries
adicionales para usuarios cuya colección de apps ya esté cacheada.

Registrado con alias `app.access` en:
- `app/Http/Kernel.php` (compatibilidad con módulos nWidart)
- `bootstrap/app.php` (Laravel 11 pipeline)

### H-002 — Enforcement en rutas de módulos

| Módulo | Slug | Middleware aplicado en |
|---|---|---|
| Inventario | `inventario` | Grupo `Route::middleware(['web','auth','app.access:inventario'])` |
| User | `user` | Grupo `Route::middleware(['web','auth','app.access:user'])` |

**Nota:** El módulo Apps no requiere `app.access` propio — su acceso está controlado por
`permission:administrar-apps`. Aplicar `app.access:apps` crearía un catch-22 donde el
admin perdería acceso al panel de administración de Apps si el módulo no está asignado.

### H-003 — Invalidación de caché por cambio de rol

**Archivo:** `Modules/User/Livewire/User/EditarRolUser.php`

```php
public function guardar()
{
    // ...
    $this->user->save();
    $this->editando = false;

    // Invalidar caché — el nuevo rol puede tener distinta visibilidad
    cache()->increment('apps.cache_version');
}
```

Usa el mismo mecanismo (`apps.cache_version`) empleado en `AppsIndex`. El cambio de rol
fuerza recálculo de `visiblesPara()` en la siguiente request del usuario afectado.

### H-004 — Menú lateral dinámico

**Archivo:** `resources/views/vendor/adminlte/partials/sidebar/left-sidebar.blade.php`

Se agrega una sección "MIS MÓDULOS" al final del `ul` de navegación, después de los
ítems estáticos existentes. Usa `App::visiblesPara(auth()->user())` directamente (con
beneficio del caché). Los ítems estáticos del menú (Inventario, Gestión de Accesos, etc.)
permanecen intactos — no se eliminaron ni modificaron.

La sección es visualmente separada con `<li class="nav-header">MIS MÓDULOS</li>`.
El ítem activo se determina con `request()->is(ltrim($appNav->ruta, '/') . '*')`.

### H-005 — Gestión de `app_user`

**Componente:** `Modules/Apps/Livewire/Apps/AppsIndex.php`

Nuevos métodos añadidos siguiendo el mismo patrón que la gestión de roles:

- `abrirModalUsuarios(int $appId)` — carga usuarios asignados directamente
- `guardarUsuarios()` — sincroniza con `activo = true` para todos los seleccionados
- `cerrarModalUsuarios()` — cierra y limpia estado del modal

```php
$syncData = collect($this->usuariosSeleccionados)
    ->mapWithKeys(fn ($id) => [$id => ['activo' => true]])
    ->toArray();
$app->user()->sync($syncData);
```

El uso de `sync()` con datos de pivot garantiza que:
- Usuarios seleccionados tienen `activo = true`
- Usuarios deseleccionados son eliminados del pivot
- No se crean duplicados (tabla tiene `UNIQUE(user_id, app_id)`)

**Vista:** `apps-index.blade.php` — nuevo botón en columna Acciones con badge de conteo
y modal con lista de usuarios ordenados por apellidos.

### H-006 — Corrección API Apps

**Archivo:** `Modules/Apps/routes/api.php`

```php
// Antes (clase inexistente)
use Modules\Apps\Http\Controllers\AppsController;
Route::apiResource('apps', AppsController::class)->names('apps');

// Después (clase correcta)
use Modules\Apps\Http\Controllers\AppController;
Route::apiResource('apps', AppController::class)->names('apps');
```

---

## Validación de casos de uso

| Caso | Comportamiento esperado | Implementado por |
|---|---|---|
| Usuario sin acceso al módulo escribe URL directa | 403 `No tienes acceso al módulo` | H-001 + H-002 |
| Usuario con acceso accede por URL | Pasa al módulo | H-001 + H-002 |
| Admin cambia rol → usuario pierde acceso a módulo | Visible inmediatamente tras la request | H-003 |
| Asignación directa vía `app_user` | App visible aunque rol no la tenga | H-005 |
| Dashboard y menú lateral | Misma fuente: `App::visiblesPara()` | H-004 |

---

## Módulos afectados

| Módulo/Componente | Tipo de cambio |
|---|---|
| Apps | Feature (H-005), bugfix (H-006) |
| User | Enhancement (H-003) |
| Inventario | Enforcement (H-002) |
| Core (middleware) | Feature (H-001) |
| Core (layout sidebar) | Feature (H-004) |

---

## Estado de Decisiones Pendientes de ADR-008

| DP | Estado post-IMPL-013 |
|---|---|
| DP-001 (semántica `app_user.role_id`) | Sin cambios — fuera de alcance |
| DP-002 (contrato middleware) | Resuelto: slug como parámetro, 403 ante acceso denegado |
