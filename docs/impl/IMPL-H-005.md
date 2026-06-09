# IMPL-H-005 — Cache Invalidation on App Slug Change

**Fecha:** 2026-06-08
**Estado:** Ejecutado
**Prioridad:** Baja
**Relacionado con:** AUDIT-APPS-004, H-005

---

## 1. Hallazgo confirmado

**H-005** — `EditarSlugApp.guardar()` no llamaba a `cache()->increment('apps.cache_version')`
tras persistir el cambio de slug.

**Evidencia:**

```php
// Estado previo (guardar() en EditarSlugApp)
$this->app->slug = $this->slug ?: null;
$this->app->save();
// ← cache()->increment ausente
$this->editando = false;
```

`App::visiblesPara()` cachea el objeto `App` completo (colección) con TTL 300 s.
Sin invalidación, el slug anterior permanecía en caché hasta el vencimiento natural,
causando que Dashboard y Sidebar mostraran el slug desactualizado.

---

## 2. Hallazgos adicionales detectados

Durante la verificación del alcance se identificaron dos componentes adicionales con
la misma omisión:

| Componente | Campo | Impacto de staleness |
|---|---|---|
| `EditarDescripcionApp` | `descripcion` | Texto descriptivo desactualizado |
| `EditarRutaApp` | `ruta` | URL del enlace de módulo desactualizada |

Ninguno de los tres estaba en H-005 original (solo `EditarSlugApp` fue auditado).
Los tres fueron corregidos en esta implementación dado que el fix es idéntico y el
riesgo de corrección es nulo.

**Cuadro completo de cobertura pre/post implementación:**

| Componente | Antes | Después |
|---|:---:|:---:|
| `EditarColorApp` | ✓ | ✓ |
| `EditarDescripcionApp` | ✗ | ✓ |
| `EditarIconoApp` | ✓ | ✓ |
| `EditarNombreApp` | ✓ | ✓ |
| `EditarOrdenApp` | ✓ | ✓ |
| `EditarRutaApp` | ✗ | ✓ |
| `EditarSlugApp` | ✗ | ✓ |

---

## 3. Corrección aplicada

Patrón oficial (tomado de `EditarColorApp`, `EditarNombreApp`, et al.):

```php
$this->app->save();
cache()->increment('apps.cache_version');
$this->editando = false;
```

Aplicado a los 3 archivos:

- `Modules/Apps/Livewire/Apps/EditarSlugApp.php`
- `Modules/Apps/Livewire/Apps/EditarDescripcionApp.php`
- `Modules/Apps/Livewire/Apps/EditarRutaApp.php`

El cambio es aditivo: una línea insertada entre `save()` y `$this->editando = false`.

---

## 4. Validación posterior

```
cache_version antes del increment:  1
cache_version después del increment: 2
Incremento:                          1 ✓

App::visiblesPara(Rector) tras invalidación: 8 apps ✓
```

La invalidación fuerza a `visiblesPara()` a reconstruir la colección en la próxima
llamada. Dashboard y Sidebar cargan datos frescos del modelo.

---

## 5. Impacto

- Sin cambios en arquitectura.
- Sin cambios en `App::visiblesPara()`, `CheckAppAccess`, middleware, gates.
- Sin cambios en `app_role`, `app_user`.
- Riesgo de regresión: **Nulo**. La línea añadida se ejecuta únicamente en la rama
  de éxito, después de que el save haya persistido correctamente.

---

## 6. Versionado

| Componente | Versión anterior | Versión nueva |
|---|---|---|
| Apps | v1.4.0 | v1.4.1 |

Archivos actualizados:
- `CHANGELOG.md` — entrada v1.6.4
- `VERSIONING.md`
- `config/versiones.php`
- `docs/changelog/apps.md`
