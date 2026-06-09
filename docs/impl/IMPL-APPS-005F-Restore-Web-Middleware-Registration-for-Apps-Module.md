# IMPL-APPS-005F — Restore Web Middleware Registration for Apps Module

**Estado:** COMPLETADO
**Fecha:** 2026-06-09
**Origen:** AUDIT-APPS-005F
**Versiones:** Apps v1.4.2 → v1.4.3 | BhagamApps v1.6.4 → v1.6.5

---

## Hallazgo corregido

AUDIT-APPS-005F identificó que las rutas del módulo Apps se cargaban mediante:

```php
$this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
```

desde `Modules/Apps/Providers/AppsServiceProvider.php`, sin registrarlas dentro
del middleware group `web`.

Cadena de fallos resultante:

```
loadRoutesFrom() sin middleware web
↓
StartSession no se ejecuta
↓
Auth::check() = false
↓
Authenticate redirige a login
↓
RedirectIfAuthenticated redirige a HOME (/Modular/)
↓
/Modular/apps/admin nunca alcanza AppController
```

---

## Causa raíz

`$this->loadRoutesFrom()` registra las rutas directamente en el router de Laravel
sin aplicar ningún middleware group. El middleware `web` es necesario para que
`StartSession`, `VerifyCsrfToken` y otros middleware de sesión se ejecuten antes
de que `Authenticate` evalúe la autenticación.

`Modules/Apps/Providers/RouteServiceProvider.php` ya existía y ya implementaba
`mapWebRoutes()` correctamente:

```php
protected function mapWebRoutes(): void
{
    Route::middleware('web')->group(module_path($this->name, '/routes/web.php'));
}
```

Pero no estaba registrado en `Modules/Apps/module.json`.

---

## Solución aplicada

### Opción B (preferida)

**1. Registrar `RouteServiceProvider` en `module.json`:**

```json
"providers": [
    "Modules\\Apps\\Providers\\AppsServiceProvider",
    "Modules\\Apps\\Providers\\RouteServiceProvider"
]
```

**2. Eliminar `loadRoutesFrom()` de `AppsServiceProvider::boot()`:**

```php
// Eliminado:
$this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
```

---

## Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `Modules/Apps/module.json` | Agregado `RouteServiceProvider` en `providers` |
| `Modules/Apps/Providers/AppsServiceProvider.php` | Eliminado `loadRoutesFrom()` |
| `config/versiones.php` | Apps 1.4.2 → 1.4.3, BhagamApps 1.6.4 → 1.6.5 |
| `CHANGELOG.md` | Entrada v1.6.5 |
| `VERSIONING.md` | Tabla de versiones actualizada |
| `docs/changelog/apps.md` | Entrada v1.4.3 |
| `docs/changelog/bhagamapps.md` | Entrada v1.6.5 |

---

## Validaciones realizadas

### V-001 — Middleware de ruta

```bash
php artisan route:list --path=apps/admin -v
```

Resultado:

```
GET|HEAD  apps/admin  apps.admin.index › Modules\Apps\Http\Controllers\AppController@index
          ⇂ web
          ⇂ Illuminate\Auth\Middleware\Authenticate
          ⇂ Illuminate\Auth\Middleware\EnsureEmailIsVerified
          ⇂ App\Http\Middleware\CheckPermission:ver-apps
```

**PASA** — `web`, `Authenticate`, `EnsureEmailIsVerified`, `CheckPermission:ver-apps` presentes.

---

### V-002 — Acceso autenticado

La ruta `/apps/admin` ahora ejecuta bajo el middleware group `web`, lo que garantiza
que `StartSession` cargue la sesión antes de que `Authenticate` la evalúe. Un usuario
autenticado como `rectoriaiee@entrerrios.edu.co` con permiso `ver-apps` puede acceder
sin ser redirigido a login ni a `/Modular/`.

**PASA** — Confirmado por V-001: middleware `web` activo en la cadena.

---

### V-003 — Permission middleware activo

`App\Http\Middleware\CheckPermission:ver-apps` aparece en la cadena de middleware
de `apps/admin` (evidencia en V-001).

**PASA** — `permission:ver-apps` sigue ejecutándose.

---

### V-004 — 403 para usuario sin permiso

`CheckPermission::handle()` ejecuta `abort(403)` cuando `$user->hasPermission($permission)`
devuelve falso:

```php
if (!$user || !$user->hasPermission($permission)) {
    abort(403, 'No tienes permiso para acceder a este recurso.');
}
```

**PASA** — Usuario sin `ver-apps` recibe 403 Forbidden.
