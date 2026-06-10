# IMPL-INV-008 — Eliminación de Polling Innecesario en Livewire

**Fecha:** 2026-06-10
**Módulo:** Inventario
**Versión:** Inventario v2.10.2 | BhagamApps v1.11.2
**Origen:** AUDIT-LIVEWIRE-419-001
**Tipo:** Fix (corrección de error 419 PAGE EXPIRED en background tabs)

---

## Contexto

AUDIT-LIVEWIRE-419-001 identificó que tres componentes Livewire del módulo Inventario
(`BienesIndex`, `HebIndex`, `HmbIndex`) usaban `wire:poll` para refrescar datos
periódicamente. Esta directiva causaba errores `419 PAGE EXPIRED` cuando el usuario
dejaba la vista abierta en una pestaña en segundo plano:

1. El navegador throttlea JS timers en tabs inactivos.
2. La sesión expira (`SESSION_LIFETIME=120`).
3. Al retomar la tab, el poll dispara con el CSRF token desactualizado.
4. Laravel rechaza la petición con 419.

`BienesIndex` ya contaba con actualización event-based funcional (`bienActualizado → recargarBien`),
haciendo el polling doblemente innecesario. `HEB` y `HMB` no requieren actualizaciones en
tiempo real por su naturaleza de flujos de aprobación manual.

---

## Cambios realizados

### 1. `bienes-index.blade.php` — eliminados 2 × `wire:poll.30s`

| Ubicación | Cambio |
|-----------|--------|
| Desktop div (antes línea 524) | `wire:poll.30s` eliminado |
| Mobile div (antes línea 835)  | `wire:poll.30s` eliminado |

### 2. `heb-index.blade.php` — eliminados 2 × `wire:poll.10s`

| Ubicación | Cambio |
|-----------|--------|
| Desktop div (antes línea 61) | `wire:poll.10s` eliminado |
| Mobile div (antes línea 129) | `wire:poll.10s` eliminado |

### 3. `hmb-index.blade.php` — eliminados 2 × `wire:poll.10s`

| Ubicación | Cambio |
|-----------|--------|
| Desktop div (antes línea 64) | `wire:poll.10s` eliminado |
| Mobile div (antes línea 210) | `wire:poll.10s` eliminado |

### 4. `BienesIndex.php` — eliminado listener muerto `bienCreado`

```php
// Antes
protected $listeners = [
    'bienActualizado' => 'recargarBien',
    'bienCreado'      => '$refresh',
];

// Después
protected $listeners = [
    'bienActualizado' => 'recargarBien',
];
```

`bienCreado` nunca fue despachado por ningún componente del módulo. Era código muerto
desde su introducción.

---

## Validaciones

| ID    | Descripción                                          | Resultado |
|-------|------------------------------------------------------|-----------|
| V-001 | `grep wire:poll` en 3 vistas afectadas               | 0 matches ✓ |
| V-002 | Listener `bienActualizado → recargarBien` intacto    | 1 match ✓  |
| V-003 | `php artisan view:clear && config:clear` sin errores | ✓          |
| V-004 | Origen 419 (wire:poll = 0) confirmado eliminado      | ✓          |

---

## Archivos modificados

```
Modules/Inventario/resources/views/livewire/bienes/bienes-index.blade.php
Modules/Inventario/resources/views/livewire/heb/heb-index.blade.php
Modules/Inventario/resources/views/livewire/hmb/hmb-index.blade.php
Modules/Inventario/Livewire/Bienes/BienesIndex.php
config/versiones.php
CHANGELOG.md
VERSIONING.md
docs/changelog/inventario.md
docs/changelog/bhagamapps.md
docs/impl/IMPL-INV-008-Eliminate-Unnecessary-Livewire-Polling.md  (este archivo)
```

---

## Nota arquitectónica

Si en el futuro se necesita actualización periódica de alguna de estas vistas, el patrón
correcto es `wire:poll.visible` (solo refresca cuando el elemento está en el viewport,
respetando el ciclo de vida del tab), no `wire:poll` sin modificador. Preferiblemente,
usar eventos Livewire (`dispatch` / `$listeners`) para actualizaciones event-driven.
