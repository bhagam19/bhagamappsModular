# AUDIT-APPS-005E — Trazar redirección: /apps/admin → /Modular

**Fecha:** 2026-06-08
**Auditor:** Claude (Sonnet 4.6)
**Alcance:** Flujo completo de request a `/apps/admin` — middleware, controller, view, Livewire, response
**Tipo:** Solo diagnóstico — sin modificación de código
**Relacionado con:** AUDIT-APPS-005A, AUDIT-APPS-005B, IMPL-APPS-005C

---

## 1. Síntesis ejecutiva

La redirección `/Modular/apps/admin → /Modular` **no ocurre dentro de un único request HTTP**.
No existe ningún `redirect()` explícito en el flujo de `/apps/admin`.

La secuencia observada es el resultado de **dos bugs simultáneos** que provocan un ciclo:

| Bug | Efecto |
|-----|--------|
| `App::visiblesPara()` genera SQL inválido | HTTP 500 en TODA página con layout AdminLTE |
| `RedirectIfAuthenticated` redirige a `url('/')` | Si el usuario (aún autenticado) accede a `/login`, va a `/Modular` |

**Estado actual:** ambos bugs están corregidos. La redirección ya no es reproducible.

---

## 2. Infraestructura de la instalación

El dominio `bhagamapps.com` tiene **dos Laravel separados**:

| URL | Directorio | App |
|-----|-----------|-----|
| `https://bhagamapps.com/` | `public_html/public/index.php` | App raíz (original) |
| `https://bhagamapps.com/Modular/` | `public_html/public/Modular/` (symlink → `private/bhagamappsModular/public/`) | BhagamAppsModular |

El `.htaccess` de `public_html/public/`:
```apache
RewriteCond %{REQUEST_URI} ^/(Modular|portal-ieplac)(/|$)
RewriteRule ^ - [L]   # pasa directamente al subdirectorio symlinkeado
```

Requests a `/apps/admin` (sin `/Modular/`) llegan a la **app raíz**, NO a BhagamAppsModular.

---

## 3. Flujo del request `GET /Modular/apps/admin`

### 3.1 — Stack de middleware (en orden)

```
InvokeDeferredCallbacks
TrustProxies
HandleCors
PreventRequestsDuringMaintenance
ValidatePostSize
TrimStrings
ConvertEmptyStringsToNull
DisableBackButtonCacheMiddleware  ← Livewire (global)
StartSession
ShareErrorsFromSession
VerifyCsrfToken
Authenticate          ← auth:sanctum / auth
AuthenticateSession
SubstituteBindings
EnsureEmailIsVerified ← verified
CheckPermission       ← permission:ver-apps
```

### 3.2 — Análisis por etapa

| Etapa | Código | HTTP emitido | Tipo |
|-------|--------|-------------|------|
| `Authenticate` — sin sesión | redirige a `route('login')` | **302** → `/Modular/login` | redirect |
| `Authenticate` — con sesión | pasa | — | — |
| `EnsureEmailIsVerified` — no verificado | redirige a verificación | 302 | redirect |
| `CheckPermission('ver-apps')` — sin permiso | `abort(403)` | **403** | error page, NO redirect |
| `CheckPermission('ver-apps')` — con permiso | pasa | — | — |
| `AppController::index()` | `return view('apps::admin.index')` | — | sin redirect |
| Render view `apps::admin.index` | extiende AdminLTE layout | — | — |
| Sidebar: `App::visiblesPara()` | **ver §4** | — | — |
| `AppsIndex::render()` | query normal (`App::with(...)`) | — | sin redirect |

### 3.3 — Resultado del request (estado histórico con el bug)

`GET /Modular/apps/admin` → **HTTP 500**

La redirección a `/Modular` **NO ocurre durante este request**.

---

## 4. Bug identificado: `App::visiblesPara()` — SQL inválido

### 4.1 — Código original (commit `cce5677` — IMPL-013)

**Archivo:** `Modules/Apps/Entities/App.php`

```php
->orWhereHas('user', function ($q) use ($user) {
    $q->where('users.id', $user->id)
      ->wherePivot('activo', true);   // ← BUG
});
```

`wherePivot()` es un método de `BelongsToMany`, no de un query builder plano.
Cuando se llama dentro de `whereHas()`, el `$q` es un `Builder` estándar.
Laravel no reconoce `wherePivot` → genera SQL inválido:

```sql
-- SQL generado (incorrecto)
... and `users`.`id` = 54 and `pivot` = activo
```

### 4.2 — Dos errores distintos (evidencia del log)

| Período | Error MySQL | Causa adicional |
|---------|------------|----------------|
| 17:26:51 → 19:35:37 | `Table 'app_role' doesn't exist` | Migraciones aún no ejecutadas |
| 19:40:26 → 19:40:49 | `Unknown column 'pivot' in 'WHERE'` | Migraciones ejecutadas, `app_role` existe |

Ambos errores usan el **mismo SQL inválido**. La primera causa desaparece al ejecutar las migraciones; la segunda persiste hasta que se corrige el código.

### 4.3 — Puntos de llamada

`App::visiblesPara()` es llamado desde **dos lugares**:

| Archivo | Línea | Contexto |
|---------|-------|---------|
| `app/Http/Controllers/Ppal/HomeController.php` | 12 | Ruta `GET /` (dashboard principal) |
| `resources/views/vendor/adminlte/partials/sidebar/left-sidebar.blade.php` | 27 | Bloque `@php` en toda página con AdminLTE |

**Efecto:** TODA página que usa el layout AdminLTE lanzaba `QueryException` → `ViewException` → HTTP 500.
Incluye: `/Modular/`, `/Modular/apps/admin`, `/Modular/user`, `/Modular/inventario/bienes`.

### 4.4 — Corrección

**Archivo:** `Modules/Apps/Entities/App.php`
**Commit:** `03ebae5`

```php
// Antes
->wherePivot('activo', true)

// Después
->where('app_user.activo', true)
```

SQL generado (correcto):
```sql
... and `users`.`id` = 54 and `app_user`.`activo` = 1
```

---

## 5. Mecanismo de la redirección observada

### 5.1 — Flujo que produce `/Modular`

Con el bug activo, todas las páginas AdminLTE lanzaban HTTP 500.
El usuario (con sesión válida) intentaba acceder a `/apps/admin`, veía la página de error,
y navegaba manualmente a `/Modular/login`.

**`GET /Modular/login` con sesión activa:**

```
Middleware aplicado:
  auth (web)        → usuario YA autenticado, pasa
  guest             → RedirectIfAuthenticated::handle()
                      → Auth::guard()->check() == true
                      → return redirect(RouteServiceProvider::HOME)
                      → redirect(url('/'))
                      → url('/') = https://bhagamapps.com/Modular/
```

**HTTP 302 → `https://bhagamapps.com/Modular/`** ← OBSERVADO

**Archivos involucrados:**

| Componente | Archivo | Línea |
|---|---|---|
| `RouteServiceProvider::HOME` | `app/Providers/RouteServiceProvider.php` | 20 |
| `RedirectIfAuthenticated` | `app/Http/Middleware/RedirectIfAuthenticated.php` | 24 |
| `url('/')` | `APP_URL=https://bhagamapps.com/Modular` | `.env:3` |

### 5.2 — `CustomLoginResponse` — código muerto

**Archivo:** `app/Http/Controllers/Auth/LoginResponse.php`

```php
public function toResponse($request)
{
    return redirect()->intended('/');  // fallback → url('/') = /Modular/
}
```

**Archivo:** `app/Providers/FortifyServiceProvider.php`

```php
$this->app->singleton(LoginResponse::class, CustomLoginResponse::class);
```

**Problema:** Sin `use` imports, PHP resuelve `LoginResponse::class` como
`App\Providers\LoginResponse` (clase inexistente). El binding se registra bajo la
clave `App\Providers\LoginResponse`, NO bajo `Laravel\Fortify\Contracts\LoginResponse`.
Fortify nunca usa este binding. `CustomLoginResponse` es **código muerto**.

Fortify usa su `LoginResponse` por defecto: `redirect()->intended(config('fortify.home'))` = `/dashboard`.
`url('/dashboard')` = `https://bhagamapps.com/Modular/dashboard` (ruta registrada por Fortify,
usa layout Jetstream `<x-app-layout>` — NO tiene sidebar AdminLTE, funciona aunque visiblesPara fallara).

---

## 6. Flujo completo confirmado

```
ESCENARIO A (más frecuente):

  1. GET /Modular/apps/admin [sesión válida]
     ├── Middleware: auth ✓ | verified ✓ | permission:ver-apps ✓
     ├── AppController::index() → return view('apps::admin.index')
     ├── Layout AdminLTE renderiza
     │   └── sidebar/left-sidebar.blade.php:27
     │       └── App::visiblesPara() → QueryException
     └── HTTP 500 [url permanece /Modular/apps/admin]

  2. [usuario navega manualmente a /Modular/login]

  3. GET /Modular/login [sesión STILL válida]
     └── RedirectIfAuthenticated → auth()->check() == true
         └── redirect(url(RouteServiceProvider::HOME))
             └── url('/') = https://bhagamapps.com/Modular/
         └── HTTP 302 → /Modular  ← OBSERVADO

ESCENARIO B (con pérdida de sesión):

  1. GET /Modular/apps/admin [sin sesión]
     └── Authenticate → redirect()->guest(route('login'))
         └── session['url.intended'] = /Modular/apps/admin
         └── HTTP 302 → /Modular/login

  2. POST /Modular/login → Fortify default LoginResponse
     └── redirect()->intended('/dashboard')
         └── url.intended = /Modular/apps/admin → redirige allí
     └── HTTP 302 → /Modular/apps/admin

  3. GET /Modular/apps/admin [con sesión nueva]
     └── MISMO HTTP 500 (escenario A, paso 1)
     [ciclo continúa]
```

---

## 7. Resumen: código HTTP final y origen

| Paso | URL | Código HTTP | Origen |
|------|-----|------------|--------|
| GET /Modular/apps/admin (con sesión, bug activo) | `/Modular/apps/admin` | **500** | `QueryException` en sidebar, `left-sidebar.blade.php:27` |
| GET /Modular/login (con sesión activa) | `/Modular/login` | **302 → /Modular/** | `RedirectIfAuthenticated::handle()`, `RouteServiceProvider::HOME='/'` |
| GET /Modular/ | `/Modular/` | **500** | `HomeController::index()` también llama `visiblesPara()` |

**La redirección a `/Modular` proviene de:**
- **Archivo:** `app/Http/Middleware/RedirectIfAuthenticated.php`
- **Método:** `handle()`
- **Línea:** 24 (`return redirect(RouteServiceProvider::HOME)`)
- **Código HTTP:** 302

---

## 8. Estado actual (post-correcciones)

| Componente | Antes | Ahora |
|---|---|---|
| `App::visiblesPara()` | `->wherePivot('activo', true)` → SQL inválido | `->where('app_user.activo', true)` ✓ |
| `app_role` tabla | Inexistente durante período de error | Existe ✓ |
| Dashboard URLs | `href="{{ $app->ruta }}"` → URL raíz del dominio | `href="{{ url($app->ruta) }}"` ✓ |
| `CustomLoginResponse` | Código muerto (binding incorrecto) | Sigue siendo código muerto — pendiente |
| `FortifyServiceProvider` binding | Broken (sin `use` imports) | Sigue broken — pendiente |
| `RouteServiceProvider::HOME` | `'/'` → redirige a `/Modular/` | Sin cambio — comportamiento correcto para login redirect |

---

## 9. Hallazgos adicionales para seguimiento

| ID | Hallazgo | Severidad | Estado |
|----|---------|-----------|--------|
| H-E01 | `CustomLoginResponse` registrado bajo clave incorrecta — código muerto | Baja | Abierto |
| H-E02 | `FortifyServiceProvider::register()` sin `use` imports → binding inválido | Baja | Abierto |
| H-E03 | `RouteServiceProvider::HOME = '/'` — authenticated users accessing `/login` aterrizan en `/Modular/` | Informativo | Sin acción requerida (comportamiento correcto) |

---

## 10. Referencias

- `App::visiblesPara()`: `Modules/Apps/Entities/App.php:55-74`
- Sidebar: `resources/views/vendor/adminlte/partials/sidebar/left-sidebar.blade.php:27`
- `HomeController`: `app/Http/Controllers/Ppal/HomeController.php:12`
- `RedirectIfAuthenticated`: `app/Http/Middleware/RedirectIfAuthenticated.php:24`
- `RouteServiceProvider::HOME`: `app/Providers/RouteServiceProvider.php:20`
- `CustomLoginResponse`: `app/Http/Controllers/Auth/LoginResponse.php`
- `FortifyServiceProvider`: `app/Providers/FortifyServiceProvider.php:23`
- Log: `storage/logs/laravel.log` — entradas 2026-06-08 17:26 → 19:40
- Commits: `cce5677` (bug), `03ebae5` (fix), `9c14b6e` (dashboard URL fix)
