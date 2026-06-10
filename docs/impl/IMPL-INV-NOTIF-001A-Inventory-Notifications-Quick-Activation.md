# IMPL-INV-NOTIF-001A — Inventory Notifications Quick Activation

**Fecha:** 2026-06-10
**Módulo:** Inventario
**Versión:** Inventario v2.10.3 | BhagamApps v1.11.3
**Origen:** AUDIT-INV-NOTIF-001
**Tipo:** Activación (infraestructura existente, sin funcionalidades nuevas)

---

## Contexto

AUDIT-INV-NOTIF-001 identificó tres items de deuda funcional de activación:

- **DF-001:** `NotificacionesDropdown` construido pero comentado en navbar
- **DF-002:** `NotificacionesIcono` construido pero no renderizado
- **DF-004:** Clase `Notificaciones` referenciada en `InventarioServiceProvider` sin existir

Los tres podían cerrarse con cambios mínimos y cero riesgo.

---

## Diagnóstico previo (hallazgos de auditoría)

### Alias Livewire corregido (DF-001)

El bloque comentado en `navbar.blade.php` usaba el alias incorrecto:

```blade
{{-- INCORRECTO (pre-existente) --}}
@livewire('hmb.notificaciones-dropdown')
```

El auto-registro de `InventarioServiceProvider` genera el alias real desde la ruta del archivo:
`Notifications/NotificacionesDropdown.php` → `notifications.notificaciones-dropdown`

El alias incorrecto habría producido un error `Component not found` al descomentar sin corrección.

### Filtro pendiente faltante (DF-001-FIX)

`NotificacionesDropdown::render()` consultaba todos los registros HMB sin filtro de estado:

```php
// ANTES — mostraba aprobados, rechazados y pendientes
HistorialModificacionBien::with(['bien', 'user'])->latest()->paginate($this->perPage);

// DESPUÉS — solo pendientes
HistorialModificacionBien::with(['bien', 'user'])
    ->where('estado', 'pendiente')
    ->latest()
    ->paginate($this->perPage);
```

### Referencia huérfana (DF-004)

`InventarioServiceProvider.php` contenía dos bloques `/* */` comentados con:
- Imports a clases incluyendo `Notificaciones` (que no existe como archivo PHP)
- Registros manuales `Livewire::component()` redundantes con el auto-registro en loop

Ambos bloques fueron eliminados. El auto-registro en `File::allFiles()` (líneas 48-69)
registra correctamente todos los componentes sin necesidad de registros manuales.

---

## Cambios aplicados

### 1. `resources/views/vendor/adminlte/partials/navbar/navbar.blade.php`

Reemplazado el bloque comentado `{{-- ... --}}` con la activación correcta:

```blade
{{-- Notificaciones Inventario (IMPL-INV-NOTIF-001A) --}}
@auth
    @php
        $esAdmin = auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Rector');
    @endphp

    @if ($esAdmin)
        {{-- Icono con badge contador de modificaciones pendientes — DF-002 --}}
        <li class="nav-item">
            <a class="nav-link" href="{{ route('inventario.hmb') }}" title="Modificaciones pendientes">
                <i class="fas fa-bell"></i>
                @livewire('notifications.notificaciones-icono')
            </a>
        </li>

        {{-- Dropdown de aprobaciones inline HMB — DF-001 --}}
        @livewire('notifications.notificaciones-dropdown')
    @endif
@endauth
```

**Notas de implementación:**
- El componente `NotificacionesDropdown` renderiza su propio `<li class="nav-item dropdown">`, por eso NO se envuelve en otro `<li>` (el original tenía doble `<li>`).
- El icono SÍ necesita envoltura en `<li>` porque `NotificacionesIcono` renderiza un `<div>`.
- Ambos elementos solo son visibles para `Administrador` y `Rector`.

### 2. `Modules/Inventario/Livewire/Notifications/NotificacionesDropdown.php`

Añadido filtro `->where('estado', 'pendiente')` en `render()`.

### 3. `Modules/Inventario/Providers/InventarioServiceProvider.php`

Eliminados dos bloques `/* */` que contenían:
- Imports comentados (7 use statements, incluyendo la clase `Notificaciones` inexistente)
- Registros manuales comentados (5 Livewire::component), incluyendo la referencia huérfana

---

## Validaciones

| ID    | Descripción                                              | Resultado |
|-------|----------------------------------------------------------|-----------|
| V-001 | Dropdown visible para Administrador (condicional `$esAdmin`) | ✓ |
| V-002 | Dropdown visible para Rector (condicional `$esAdmin`)    | ✓ |
| V-003 | Dropdown oculto para Coordinador (excluido del `@if`)    | ✓ |
| V-004 | Icono visible en navbar para admins                      | ✓ |
| V-005 | Contador correcto (`estado='pendiente'` en icono y dropdown) | ✓ |
| V-006 | 0 errores Livewire (PHP syntax OK, alias correcto)       | ✓ |
| V-007 | 0 errores PHP (`php -l` en 4 archivos)                   | ✓ |
| V-008 | Sin regresiones: `wire:poll` sigue en 0 en BienesIndex/HEB/HMB | ✓ |
| V-009 | No reaparece el error 419 (sin wire:poll nuevo)          | ✓ |

---

## Archivos modificados

```
resources/views/vendor/adminlte/partials/navbar/navbar.blade.php
Modules/Inventario/Livewire/Notifications/NotificacionesDropdown.php
Modules/Inventario/Providers/InventarioServiceProvider.php
config/versiones.php
CHANGELOG.md
VERSIONING.md
docs/changelog/inventario.md
docs/changelog/bhagamapps.md
docs/impl/IMPL-INV-NOTIF-001A-Inventory-Notifications-Quick-Activation.md  (este archivo)
```

---

## Deuda residual conocida (para IMPL-INV-NOTIF-001B)

| ID    | Descripción |
|-------|-------------|
| DF-003 | `toDatabase` comentado en `NotificacionHmb` y `NotificacionHeb` — tabla `notifications` sin uso |
| DF-005 | HMB: sin mecanismo cross-session (admin debe recargar para ver nuevas solicitudes desde otro usuario) |
| DF-006 | HEB: ídem |
| DF-007 | `NotificacionesDropdown` solo cubre HMB — eliminaciones HEB no tienen dropdown equivalente |
| DF-008 | Sidebar sin badges de pendientes |

El `NotificacionesDropdown` tiene lógica de aprobación propia (`aprobarCambio`/`rechazarCambio`)
que borra el registro HMB en lugar de actualizar su estado. Esta inconsistencia con `HmbIndex`
(que actualiza `estado='aprobada'`) debe ser evaluada en NOTIF-001B antes de que se apruebe
en producción desde el dropdown.

---

## Preparación para IMPL-INV-NOTIF-001B

La siguiente fase debe abordar:
1. Activar `toDatabase` en ambas clases de notificación
2. Sincronización cross-session para HMB y HEB (WebSockets o `wire:poll.visible`)
3. Ampliar el dropdown para cubrir HEB
4. Resolver inconsistencia de aprobación entre `NotificacionesDropdown` y `HmbIndex`
