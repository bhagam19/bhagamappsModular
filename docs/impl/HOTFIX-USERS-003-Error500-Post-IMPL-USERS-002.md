# HOTFIX-USERS-003 — Error 500 Post-IMPL-USERS-002

**Fecha:** 2026-06-11
**Módulo:** User
**Componente:** `Modules/User/Livewire/User/UserIndex.php`
**SHA introducidor:** `7328608` (IMPL-USERS-002)
**Versiones resultantes:** User v2.4.1 | IEE v1.16.1 | BhagamApps v1.16.1

---

## Síntoma

`/iee/users/users` devuelve HTTP 500 inmediatamente tras el despliegue de IMPL-USERS-002 (SHA 7328608).

---

## Fase 1 — Diagnóstico

### Log de error (`storage/logs/laravel.log`)

```
[2026-06-11 18:14:47] production.ERROR: A void function must not return a value
File: Modules/User/Livewire/User/UserIndex.php:48
Exception: Symfony\Component\ErrorHandler\Error\FatalError
```

### Causa raíz

En IMPL-USERS-002, el método `mount()` fue declarado con tipo de retorno `: void`:

```php
// INCORRECTO — causó el FatalError
public function mount(): void
{
    if (!auth()->user()->hasPermission('ver-usuarios')) {
        return redirect()->route('ppal.index');   // <-- retorna RedirectResponse
    }
    ...
}
```

`void` prohíbe en PHP cualquier `return` con valor. `redirect()` retorna
un objeto `RedirectResponse`, lo que dispara `A void function must not return a value`
como `FatalError` antes de que el componente pueda inicializarse.

El código original de la función (antes de IMPL-USERS-002) **no tenía `: void`**.
IMPL-USERS-002 añadió la anotación por consistencia con otros métodos, sin notar
que `mount()` devuelve un redirect condicional.

---

## Fase 2 — Corrección

**Archivo:** `Modules/User/Livewire/User/UserIndex.php` línea 45

**Antes (IMPL-USERS-002):**
```php
public function mount(): void
```

**Después:**
```php
public function mount()
```

Un único carácter de cambio: eliminar `: void`. El cuerpo del método no cambia.

---

## Validaciones

| # | Validación | Resultado |
|---|-----------|-----------|
| V-001 | PHP lint sin errores | ✅ `php -l` — No syntax errors |
| V-002 | Query sin filtros: 116 usuarios | ✅ Tinker |
| V-003 | Roles cargados en mount: 7 roles | ✅ Tinker |
| V-004 | Búsqueda reactiva conservada | ✅ Propiedad `$busqueda` intacta |
| V-005 | Filtros por rol y estado conservados | ✅ Propiedades intactas |
| V-006 | Ordenamiento conservado | ✅ `sortBy()` intacto |
| V-007 | Sin regresiones en resto del módulo | ✅ Solo `mount()` modificado |

---

## Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `Modules/User/Livewire/User/UserIndex.php` | Línea 45: eliminado `: void` del signature de `mount()` |

---

## Commit

Ver git log para SHA verificable.
