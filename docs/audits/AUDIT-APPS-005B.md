# AUDIT-APPS-005B — URL Generation in Dashboard and Sidebar

**Fecha:** 2026-06-08
**Auditor:** Claude (Sonnet 4.6)
**Alcance:** Dashboard, Sidebar, campo `apps.ruta`, generación de URLs
**Tipo:** Solo diagnóstico — sin modificación de código
**Relacionado con:** AUDIT-APPS-005A, IMPL-ADR-009, IMPL-013

---

## 1. Problema reportado

Los enlaces generados en Dashboard y Sidebar apuntan a URLs sin el prefijo
del subdirectorio de despliegue `/Modular`:

```
Observado:   /inventario/bienes
             /apps/admin
             /users/users

Esperado:    /Modular/inventario/bienes
             /Modular/apps/admin
             /Modular/users/users
```

---

## 2. Verificaciones realizadas

### 2.1 — APP_URL y helpers de Laravel

```
APP_URL = https://bhagamapps.com/Modular

url('/inventario/bienes') → https://bhagamapps.com/Modular/inventario/bienes  ✓
url('/apps/admin')        → https://bhagamapps.com/Modular/apps/admin          ✓
route('apps.admin.index') → https://bhagamapps.com/Modular/apps/admin          ✓
```

`APP_URL` está correctamente configurado. Los helpers `url()` y `route()` generan
URLs con el prefijo `/Modular`. **El problema no está en la configuración.**

---

### 2.2 — Campo `apps.ruta` en BD

| id | nombre | ruta |
|----|--------|------|
| 16 | Usuarios | `/user` |
| 15 | Inventario | `/inventario/bienes` |
| 13 | Aplicaciones | `/apps/admin` |
| 17 | Biblioteca | `/biblioteca` |

Todos los valores tienen barra inicial (`/`). Son paths absolutos válidos para
pasar a `url()`. **El problema no está en los datos.**

---

### 2.3 — Dashboard (`apps::index.blade.php`) — BUG CONFIRMADO

**Archivo:** `Modules/Apps/resources/views/index.blade.php`

```php
// Línea 13 — versión escritorio
<a href="{{ $app->ruta }}" ...>

// Línea 46 — versión móvil
<a href="{{ $app->ruta }}" ...>
```

`$app->ruta` se usa **directamente como valor de `href`** sin pasar por el helper
`url()`. Con `$app->ruta = '/inventario/bienes'`:

```html
<!-- HTML generado -->
<a href="/inventario/bienes">

<!-- El navegador resuelve como -->
https://bhagamapps.com/inventario/bienes   ← FALTA /Modular
```

**Causa:** `href` con path absoluto (barra inicial) se resuelve desde la raíz del
dominio, ignorando el subdirectorio `/Modular`.

---

### 2.4 — Sidebar MIS MÓDULOS (`left-sidebar.blade.php`) — SIN BUG

**Archivo:** `resources/views/vendor/adminlte/partials/sidebar/left-sidebar.blade.php`

```php
// Línea 33
<a href="{{ url($appNav->ruta) }}" ...>
```

Usa el helper `url()` → genera `https://bhagamapps.com/Modular/inventario/bienes`.
**Correcto.**

---

### 2.5 — Menú estático de `config/adminlte.php` — SIN BUG

AdminLTE procesa los ítems con clave `'route'` a través de
`vendor/jeroennoten/laravel-adminlte/src/Menu/Filters/HrefFilter.php`:

```php
// HrefFilter.php:36-38
if (! empty($item['url'])) {
    return url($item['url']);
} elseif (! empty($item['route'])) {
    return $this->makeHrefFromRouteAttr($item['route']); // usa route()
}
```

Todos los ítems estáticos del menú usan `route()` o `url()` internamente.
**Correcto.**

---

## 3. Causa raíz

| Componente | Mecanismo | Estado |
|---|---|---|
| Dashboard `apps::index.blade.php` L13, L46 | `href="{{ $app->ruta }}"` directo | **ROTO** |
| Sidebar MIS MÓDULOS L33 | `href="{{ url($appNav->ruta) }}"` | Correcto |
| Sidebar menú estático (AdminLTE HrefFilter) | `route()` / `url()` | Correcto |

**La causa raíz es única y acotada:** el dashboard usa `$app->ruta` directamente
como `href` sin envolverlo en `url()`. Esto produce URLs que apuntan a la raíz del
dominio (`https://bhagamapps.com/ruta`) en lugar del subdirectorio de despliegue
(`https://bhagamapps.com/Modular/ruta`).

---

## 4. Archivos afectados

| Archivo | Líneas | Descripción |
|---------|--------|-------------|
| `Modules/Apps/resources/views/index.blade.php` | 13, 46 | `href="{{ $app->ruta }}"` sin `url()` |

Un único archivo, dos líneas idénticas (versión escritorio y móvil).

---

## 5. Corrección requerida

Reemplazar en ambas líneas:

```php
// Antes
<a href="{{ $app->ruta }}" ...>

// Después
<a href="{{ url($app->ruta) }}" ...>
```

No se requieren cambios en `apps.ruta`, en la configuración de `APP_URL`,
ni en el sidebar.

---

## 6. Hallazgo adicional — registros duplicados en `apps`

La tabla `apps` contiene dos conjuntos de registros:

- **IDs 13–24:** Registros actuales con `nombre`, `slug`, `icono`, `ruta` completos.
- **IDs 1–12:** Registros legacy con `nombre = NULL` y `slug = NULL`.

`App::visiblesPara()` puede devolver registros de ambos grupos si están habilitados
y asignados al rol del usuario. Los registros legacy con `nombre = NULL` producen
advertencias de PHP (`str_pad(null)` deprecation) y se renderizan sin nombre en el
dashboard. Este hallazgo es independiente del problema de URLs y requiere análisis
separado.

---

## 7. Estado

| Hallazgo | Severidad | Estado |
|----------|-----------|--------|
| `href="{{ $app->ruta }}"` sin `url()` en dashboard | Media | Abierto — pendiente IMPL |
| Registros legacy sin `nombre`/`slug` en `apps` | Baja | Abierto — pendiente análisis |

---

## 8. Referencias

- `APP_URL`: `.env` línea 3
- Vista dashboard: `Modules/Apps/resources/views/index.blade.php`
- Sidebar publicado: `resources/views/vendor/adminlte/partials/sidebar/left-sidebar.blade.php`
- HrefFilter: `vendor/jeroennoten/laravel-adminlte/src/Menu/Filters/HrefFilter.php`
