# IMPL-INV-NOTIF-001B — Inventory Notifications Consistency & Persistence

**Versión:** Inventario v2.10.4 / BhagamApps v1.11.4
**Fecha:** 2026-06-10
**Origen:** AUDIT-INV-NOTIF-001, IMPL-INV-NOTIF-001A
**Estado:** COMPLETADO

---

## Resumen ejecutivo

Completar la consistencia funcional del sistema de notificaciones de Inventario, preservando
la trazabilidad histórica y verificando la infraestructura de persistencia existente.

---

## Diagnóstico pre-implementación

### Divergencias encontradas

| ID  | Archivo | Línea | Bug |
|-----|---------|-------|-----|
| D-1 | `NotificacionesDropdown.php` | 111 | `$cambio->delete()` en aprobarCambio — destruye evidencia |
| D-2 | `NotificacionesDropdown.php` | 38-41 | Null-check de `$cambio` después de usar `$cambio->bien_id` |
| D-3 | `NotificacionesDropdown.php` | 66-75 | Crea nuevos registros en lugar de actualizar `estado='aprobada'` |
| D-4 | `NotificacionesDropdown.php` | 69 | Clave `campo_modificado` inexistente (columna real: `campo`) |
| D-5 | `NotificacionesDropdown.php` | — | Falta creación `HistorialDependenciaBien` para `dependencia_id` |
| D-6 | `NotificacionesDropdown.php` | 135 | `$cambio->delete()` en rechazarCambio — destruye evidencia |
| D-7 | `NotificacionesIcono.php` | — | Sin listener `cambioActualizado` — contador no se refresca |
| D-8 | `NotificacionHmb.php` | 91-101 | `toDatabase()` comentado; referencia `user->name` incorrecto |
| D-9 | `NotificacionHeb.php` | 49-59 | `toDatabase()` comentado; referencia campos inexistentes en HEB |

### Clase canónica de referencia

`HmbIndex::aprobarModificacion()` y `HmbIndex::rechazarModificacion()` — implementación
oficial con trazabilidad correcta. Todo el dropdown debía replicar esta lógica.

---

## Implementación

### NOTIF-001 — aprobarCambio corregido

**Archivo:** `Modules/Inventario/Livewire/Notifications/NotificacionesDropdown.php`

Lógica reescrita para ser idéntica a `HmbIndex::aprobarModificacion()`:

1. `find($id)` → null-check inmediato antes de cualquier uso.
2. Tipo `bien`: actualiza `$bien->$campo` + `$cambio->estado='aprobada'` + `$cambio->aprobado_por` + `save()`.
3. Si `$campo === 'dependencia_id'`: crea `HistorialDependenciaBien` con los IDs anterior/nuevo.
4. Tipo `detalle`: itera campos JSON, actualiza `$detalle`, luego `$cambio->estado='aprobada'` + `save()`.
5. `$cambio->delete()` eliminado completamente — el registro HMB se conserva con estado definitivo.
6. Import `HistorialDependenciaBien` añadido.

### NOTIF-002 — rechazarCambio corregido

**Archivo:** `Modules/Inventario/Livewire/Notifications/NotificacionesDropdown.php`

Reemplazado:
```php
$cambio->delete();
```
Por:
```php
$cambio->estado = 'rechazada';
$cambio->aprobado_por = auth()->id();
$cambio->save();
```

### NOTIF-003 — NotificacionHmb: canal database activado

**Archivo:** `Modules/Inventario/Livewire/Hmb/NotificacionHmb.php`

- `via()` actualizado de `['mail']` a `['mail', 'database']`.
- `toDatabase()` implementado y activado:
  - Usuario obtenido vía `$this->modificacion->dependencia?->user` (no `user_id` directo —
    columna inexistente en `historial_modificaciones_bienes`).
  - Nombre construido con `nombres`/`apellidos` (campos reales del modelo `User`).
  - Payload: `bien_id`, `tipo_objeto`, `campo`, `valor_anterior`, `valor_nuevo`, `solicitado_por`.
- Tabla `notifications` confirmada ejecutada (migración `2025_05_21_020618`).

### NOTIF-003 — NotificacionHeb: NO activada (deuda técnica documentada)

**Archivo:** `Modules/Inventario/Livewire/Heb/NotificacionHeb.php`

El `toDatabase()` comentado referencia:
- `$this->solicitud->campo` — no existe en `HistorialEliminacionBien`
- `$this->solicitud->valor_anterior` — no existe en `HistorialEliminacionBien`
- `$this->solicitud->valor_nuevo` — no existe en `HistorialEliminacionBien`

Campos reales de `HistorialEliminacionBien`: `bien_id`, `dependencia_id`, `user_id`,
`aprobado_por`, `estado`, `motivo`.

**Acción requerida para activar:** Redefinir el payload usando campos existentes:
`bien_id`, `dependencia_id`, `user_id`, `estado`, `motivo`. No se improvisa; queda
como deuda técnica pendiente.

### NOTIF-004 — NotificacionesIcono con listener reactivo

**Archivo:** `Modules/Inventario/Livewire/Notifications/NotificacionesIcono.php`

Añadido:
```php
use Livewire\Attributes\On;

#[On('cambioActualizado')]
public function actualizarContador()
{
    $this->total = HistorialModificacionBien::where('estado', 'pendiente')->count();
}
```

El dropdown despacha `cambioActualizado` al aprobar o rechazar. El ícono escucha y
refresca `$total` sin wire:poll.

---

## Validaciones

| Código | Descripción | Estado |
|--------|-------------|--------|
| V-001  | Aprobación desde dropdown conserva historial | ✓ PASADA — `delete()` eliminado, `estado='aprobada'` |
| V-002  | Estado actualizado correctamente | ✓ PASADA — `estado` + `aprobado_por` guardados |
| V-003  | Rechazo consistente con HMB | ✓ PASADA — mismo patrón `estado='rechazada'` |
| V-004  | 0 eliminaciones indebidas | ✓ PASADA — `delete()` eliminado en aprobar y rechazar |
| V-005  | Dropdown operativo | ✓ PASADA — render filtra `estado='pendiente'` |
| V-006  | Icono operativo | ✓ PASADA — listener `cambioActualizado` activo |
| V-007  | Contador operativo | ✓ PASADA — se refresca en respuesta a eventos |
| V-008  | 0 errores PHP | ✓ PASADA — `php -l` limpio en los 3 archivos |
| V-009  | 0 errores Livewire | ✓ PASADA — Atributo `#[On]` Livewire 3 nativo |
| V-010  | Sin regresiones funcionales | ✓ PASADA — lógica de negocio idéntica a HmbIndex |

---

## Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `Modules/Inventario/Livewire/Notifications/NotificacionesDropdown.php` | NOTIF-001 + NOTIF-002 |
| `Modules/Inventario/Livewire/Notifications/NotificacionesIcono.php` | NOTIF-004 |
| `Modules/Inventario/Livewire/Hmb/NotificacionHmb.php` | NOTIF-003 (HMB activado) |
| `config/versiones.php` | BhagamApps 1.11.4, Inventario 2.10.4 |
| `CHANGELOG.md` | v1.11.4 |
| `VERSIONING.md` | Tabla de versiones actualizada |
| `docs/changelog/inventario.md` | v2.10.4 |
| `docs/changelog/bhagamapps.md` | v1.11.4 |
| `docs/impl/IMPL-INV-NOTIF-001B-*.md` | Este documento |

---

## Deuda técnica registrada

| DT | Descripción | Prioridad |
|----|-------------|-----------|
| DT-HEB-001 | `NotificacionHeb::toDatabase()` requiere redefinir payload con campos reales de `HistorialEliminacionBien` antes de activar canal `database` | Media |
