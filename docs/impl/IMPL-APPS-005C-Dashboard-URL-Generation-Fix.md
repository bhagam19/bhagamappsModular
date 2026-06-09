# IMPL-APPS-005C — Dashboard URL Generation Fix for Subdirectory Installations

**Fecha:** 2026-06-08
**Estado:** Ejecutado
**Prioridad:** Alta
**Relacionado con:** AUDIT-APPS-005B, AUDIT-APPS-005A

---

## 1. Hallazgo confirmado

**Diagnóstico:** AUDIT-APPS-005B confirmó que el Dashboard (`apps::index.blade.php`) usa
`href="{{ $app->ruta }}"` directamente sin pasar el path por el helper `url()`.

**Impacto:** En `APP_URL = https://bhagamapps.com/Modular`, el path `/inventario/bienes`
se resuelve por el navegador como:

```
https://bhagamapps.com/inventario/bienes   ← INCORRECTO (404)
```

En lugar de:

```
https://bhagamapps.com/Modular/inventario/bienes   ← CORRECTO
```

**Archivos afectados:** `Modules/Apps/resources/views/index.blade.php`, líneas 13 y 46.

---

## 2. Causa raíz

Un `href` con path absoluto (barra inicial) es resuelto por el navegador desde la raíz del
dominio, ignorando cualquier subdirectorio de despliegue. El helper `url()` de Laravel
construye la URL completa a partir de `APP_URL`, incluyendo el prefijo `/Modular`.

**Componentes inspeccionados:**

| Componente | Mecanismo | Estado |
|---|---|---|
| Dashboard `apps::index.blade.php` L13, L46 | `href="{{ $app->ruta }}"` directo | Roto — corregido |
| Sidebar `left-sidebar.blade.php` L33 | `href="{{ url($appNav->ruta) }}"` | Correcto |
| AdminLTE menú estático (`config/adminlte.php`) | `HrefFilter` → `url()` / `route()` | Correcto |

---

## 3. Corrección aplicada

**Archivo:** `Modules/Apps/resources/views/index.blade.php`

```php
// Antes — línea 13 (escritorio)
<a href="{{ $app->ruta }}" class="text-decoration-none d-block">

// Después — línea 13 (escritorio)
<a href="{{ url($app->ruta) }}" class="text-decoration-none d-block">

// Antes — línea 46 (móvil)
<a href="{{ $app->ruta }}" class="d-flex align-items-center text-decoration-none w-100 h-100">

// Después — línea 46 (móvil)
<a href="{{ url($app->ruta) }}" class="d-flex align-items-center text-decoration-none w-100 h-100">
```

Sin cambios en modelo, migraciones, permisos, rutas ni configuración.

---

## 4. Validación posterior

URLs generadas por `url()` para los módulos visibles del usuario Rector:

```
url('/user')               → https://bhagamapps.com/Modular/user         ✓
url('/inventario/bienes')  → https://bhagamapps.com/Modular/inventario/bienes ✓
url('/apps/admin')         → https://bhagamapps.com/Modular/apps/admin    ✓
```

Todas las rutas incluyen el prefijo `/Modular`. No quedan ocurrencias de
`href="{{ $app->ruta }}"` sin envolver (grep retornó exit code 1 — sin matches).

---

## 5. Impacto

- Sin cambios en arquitectura, RBAC, middleware, ni caché.
- Riesgo de regresión: **Nulo**. El cambio es un drop-in: `url(path)` con path que ya
  tiene barra inicial produce la misma URL en instalaciones raíz y la URL correcta en
  instalaciones de subdirectorio.

---

## 6. Versionado

| Componente | Versión anterior | Versión nueva |
|---|---|---|
| Apps | v1.4.1 | v1.4.2 |
| BhagamApps | v1.6.3 | v1.6.4 |

Archivos actualizados:
- `CHANGELOG.md` — entrada v1.6.4 (consolidada con IMPL-H-005)
- `VERSIONING.md`
- `config/versiones.php`
- `docs/changelog/apps.md` — entrada v1.4.2
- `docs/changelog/bhagamapps.md` — entrada v1.6.4
