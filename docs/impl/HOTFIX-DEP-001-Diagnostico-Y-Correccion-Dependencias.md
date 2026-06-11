# HOTFIX-DEP-001 — Diagnóstico y Corrección: Error 500 en Catálogo Dependencias

**Fecha:** 2026-06-11
**Módulo:** Inventario
**Componente:** `Modules/Inventario/Livewire/Catalogos/DependenciasIndex.php`
**Versiones resultantes:** Inventario v2.11.3 | IEE v1.15.3 | BhagamApps v1.15.3

---

## Síntoma

`http://bhagamapps.com/iee/inventario/catalogos/dependencias` devolvía HTTP 500.
La página no cargaba; sin mensaje visible al usuario.

---

## Fase 1 — Diagnóstico

### Log de error (`storage/logs/laravel.log`)

```
[2026-06-11 17:54:00] production.ERROR: SQLSTATE[42S22]:
  Column not found: 1054 Unknown column 'name' in 'SELECT'
  SQL: select `name`, `id` from `users` order by `name` asc
  File: Modules/Inventario/Livewire/Catalogos/DependenciasIndex.php:40
  View: Modules/Inventario/resources/views/catalogos/dependencias.blade.php
```

### Causa raíz

`DependenciasIndex::mount()` línea 40 ejecutaba:

```php
$this->usuarios = User::orderBy('name')->pluck('name', 'id')->toArray();
```

La tabla `users` en este proyecto **no tiene columna `name`**; usa `nombres` y `apellidos`
(confirmado desde `Modules/User/Entities/User.php` fillable).

MySQL rechazó la consulta en el nivel de SELECT con error `Unknown column 'name'`.

### Auditoría adicional

- `dependencias-index.blade.php`: usa `$usuarios` como array `id => string_nombre` en
  `<select>` y en `{{ $usuarios[$item->user_id] }}` — compatible con la corrección.
- Relaciones `Dependencia`, query de listado, paginación, resto de la vista: sin problemas.
- `DependenciasSeeder.php`: correcto (reescrito previamente en sesión). Sin relación con el error.

---

## Fase 2 — Corrección

**Archivo:** `Modules/Inventario/Livewire/Catalogos/DependenciasIndex.php` línea 40

**Antes:**
```php
$this->usuarios = User::orderBy('name')->pluck('name', 'id')->toArray();
```

**Después:**
```php
$this->usuarios = User::orderBy('nombres')
    ->get(['id', 'nombres', 'apellidos'])
    ->mapWithKeys(fn($u) => [$u->id => trim($u->nombres . ' ' . $u->apellidos)])
    ->toArray();
```

Estrategia: recuperar `id`, `nombres`, `apellidos` con Eloquent (columnas reales),
construir el string de nombre completo en PHP con `mapWithKeys`.

---

## Validaciones (V-001 a V-006)

| # | Validación | Resultado |
|---|-----------|-----------|
| V-001 | Query ejecuta sin excepción | ✅ `php artisan tinker` — sin error |
| V-002 | Retorna registros correctos | ✅ 116 usuarios, formato `id => "Nombre Apellido"` |
| V-003 | Orden correcto | ✅ orden ascendente por `nombres` |
| V-004 | Una sola línea modificada | ✅ solo `mount()` línea 40 |
| V-005 | Vista no requiere cambios | ✅ `$usuarios` sigue siendo `array<int, string>` |
| V-006 | Sin regresiones en otros CRUDs | ✅ corrección aislada al componente |

---

## Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `Modules/Inventario/Livewire/Catalogos/DependenciasIndex.php` | Línea 40: `pluck('name', 'id')` → `get()->mapWithKeys()` con `nombres`/`apellidos` |

---

## Commit

Ver git log para SHA verificable.
