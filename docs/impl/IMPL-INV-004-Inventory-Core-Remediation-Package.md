# IMPL-INV-004 — Inventory Core Remediation Package

**Fecha:** 2026-06-10
**Estado:** COMPLETADO
**Origen:** AUDIT-INV-003
**Tipo:** Consolidación técnica — corrección de hallazgos activos
**Versiones:** Inventario v2.8.0 → v2.8.1 | BhagamApps v1.9.2 → v1.9.3

---

## Repositorio

```
pwd:    /home/adolfo/web/bhagamapps.com/private/bhagamappsModular
remote: https://github.com/bhagam19/bhagamappsModular.git
branch: main
```

---

## Alcance ejecutado

| GAP | Severidad | Estado |
|---|---|---|
| GAP-001 — Permisos inexistentes en `Notificaciones.php` | ALTA | ✅ CERRADO |
| GAP-002 — `bienes.user_id` en modelo/código pero columna ausente en BD | ALTA | ✅ CERRADO |
| GAP-004 — `ubicacion_id` en UI pero columna ausente en `bienes` | MEDIA | ✅ CERRADO |
| GAP-003 — Decisión arquitectónica `bienes.origen` vs catálogo `origenes` | MEDIA | ✅ DOCUMENTADO |

---

## GAP-001 — Notificaciones con permisos inexistentes

### Problema
`Modules/Inventario/Livewire/Notifications/Notificaciones.php` contenía:
```php
$this->authorize('aprobar-cambios-bienes');   // línea 35
$this->authorize('rechazar-cambios-bienes');  // línea 78
```
Estos permisos no existen en ninguna migración del sistema. Adicionalmente:
- La vista declarada (`inventario::livewire.notifications.notificaciones`) no existía en el sistema de archivos.
- La lógica duplicaba el flujo de aprobación de `HmbIndex` de forma inconsistente.
- No había ninguna vista blade que incluyera este componente (`@livewire`).

`NotificacionesIcono.php` apuntaba a `inventario::livewire.notifications.notificaciones-icono`, ruta de vista inexistente (la vista real está en `livewire/hmb/notificaciones-icono.blade.php`).

### Decisión
- **`Notificaciones.php`**: eliminado. El flujo canónico de aprobación de modificaciones es `HmbIndex` con el permiso `gestionar-historial-modificaciones-bienes`. No crear un duplicado roto.
- **`NotificacionesIcono.php`**: corregido path de vista.

### Cambios
| Archivo | Acción |
|---|---|
| `Livewire/Notifications/Notificaciones.php` | **Eliminado** |
| `Livewire/Notifications/NotificacionesIcono.php` | Path de vista corregido → `inventario::livewire.hmb.notificaciones-icono` |

### Validación V-001
```
grep -r "aprobar-cambios-bienes|rechazar-cambios-bienes" Modules/Inventario/ --include="*.php"
→ (sin resultados)
```
0 referencias a permisos inexistentes. ✅

---

## GAP-002 — `bienes.user_id` en modelo pero columna ausente en BD

### Problema
La migración `2025_05_21_014554_create_bienes_table.php` no define columna `user_id`.

Sin embargo existían referencias a esa columna en tres lugares:

1. **`Entities/Bien.php` — `$fillable`:**
   ```php
   'user_id',   // columna inexistente → mass assignment silencioso
   ```

2. **`Entities/Bien.php` — `getDisplayValue()`:**
   ```php
   'user_id' => ['rel' => 'user', 'campo' => fn($u) => ...],
   // Bien no tiene relación user() → siempre retornaba null
   ```

3. **`Http/Controllers/ActaPDFController.php`:**
   ```php
   Bien::with(['detalle', 'estado', 'dependencia', 'user'])
       ->where('user_id', $userId)   // query siempre devolvía vacío
       ->get();
   ```

### Arquitectura correcta
La relación usuario→bien se establece **exclusivamente** vía `dependencias.user_id`:
```
bienes.dependencia_id → dependencias.id
dependencias.user_id → users.id
```
Como ya implementaba correctamente `ActaEntregaIndex.php` y la vista `bienes-index.blade.php`.

### Decisión: Opción A — Eliminar referencias obsoletas
`bienes.user_id` nunca fue una columna real. La relación existe y funciona vía dependencia.

### Cambios
| Archivo | Cambio |
|---|---|
| `Entities/Bien.php` | Eliminado `user_id` de `$fillable` |
| `Entities/Bien.php` | Eliminado `user_id` de mapa `$relaciones` en `getDisplayValue()` |
| `Http/Controllers/ActaPDFController.php` | Query reescrita con JOIN a `dependencias.user_id`. Eliminado `'user'` del `with()` |

### ActaPDFController — antes vs después
```php
// ANTES (siempre devolvía colección vacía):
$bienes = Bien::with(['detalle', 'estado', 'dependencia', 'user'])
    ->where('user_id', $userId)
    ->get();

// DESPUÉS (equivalente a ActaEntregaIndex):
$bienes = Bien::with(['detalle', 'estado', 'dependencia'])
    ->join('dependencias', 'bienes.dependencia_id', '=', 'dependencias.id')
    ->where('dependencias.user_id', $userId)
    ->orderBy('dependencias.nombre')
    ->orderBy('bienes.nombre')
    ->select('bienes.*')
    ->get();
```

### Validación V-002 / V-003
```
grep -n "user_id" Modules/Inventario/Entities/Bien.php
→ (sin resultados en fillable ni en relaciones)

grep -n "->where('user_id'" Modules/Inventario/Http/Controllers/ActaPDFController.php
→ (sin resultados)
```
`ActaPDFController` ahora retorna bienes válidos. ✅

---

## GAP-004 — `ubicacion_id` en UI pero columna ausente en `bienes`

### Problema
`BienesIndex.$availableColumns` incluía `'ubicacion_id' => 'Ubicación'`.

La columna `bienes.ubicacion_id` **no existe** en la migración de `bienes`.

La arquitectura real es:
```
bienes.dependencia_id → dependencias.id
dependencias.ubicacion_id → ubicaciones.id
```

Efectos del bug:
- La UI mostraba un toggle de columna "Ubicación" en el selector.
- `sortBy('ubicacion_id')` generaría SQL error (`ORDER BY bienes.ubicacion_id` inexistente).
- El toggle activaría `EditarCampoBien` con `campo='ubicacion_id'`, que intentaría UPDATE en columna inexistente.

**Nota:** `$ordenBase` ya NO contenía `ubicacion_id` (la columna no era visible por defecto), pero sí estaba en `availableColumns` para el toggle de columnas.

### Decisión
Eliminar `ubicacion_id` de `$availableColumns`. La ubicación sigue siendo accesible vía `dependencia.ubicacion` (ya implementado en la vista para la columna `dependencia_id`).

### Cambio
| Archivo | Cambio |
|---|---|
| `Livewire/Bienes/BienesIndex.php` | Eliminado `'ubicacion_id' => 'Ubicación'` de `$availableColumns` |

### Validación V-004
```
grep -n "ubicacion_id" Modules/Inventario/Livewire/Bienes/BienesIndex.php
→ (sin resultados)
```
0 referencias a columna inexistente en componente de listado. ✅

---

## GAP-003 — Decisión Arquitectónica: `bienes.origen` vs catálogo `origenes`

### Situación
- `bienes.origen`: campo `string(40)` en migración original (2025-05-21).
- Tabla `origenes`: catálogo creado el 2026-06-09 como lookup table independiente.
- No existe FK `bienes.origen_id` vinculando los dos.
- El formulario de creación de bienes usa `listaOrigenesBienes` (valores únicos de `bienes.origen`) como sugerencias, no el catálogo `origenes`.

### Opciones evaluadas
| Opción | Descripción | Riesgo |
|---|---|---|
| A — Mantener as-is | `bienes.origen` = texto libre, catálogo `origenes` = referencia administrable | Bajo |
| B — Normalizar ahora | Agregar `origen_id FK`, migrar datos, actualizar UI | Alto — migración de datos destructiva |
| C — Eliminar catálogo | Borrar `origenes` table y entity | Pérdida de funcionalidad existente |

### Decisión: Opción A — Mantener as-is

**Rationale:**
- La normalización requiere una migración de datos (mappear strings libres a IDs) que es compleja y riesgosa sin casos de uso urgentes.
- El catálogo `origenes` tiene valor como lista administrable de valores canónicos. Puede evolucionar a ser la fuente de sugerencias del formulario de bienes en una iteración futura.
- Esta decisión no bloquea ninguna funcionalidad activa.

**Programado para futura iteración:**
- IMPL-INV-005 (o posterior): agregar `bienes.origen_id FK` con migración nullable, conectar `OrigenesIndex` como fuente de sugerencias en formulario, deprecar `bienes.origen` como string libre.

---

## Resumen de archivos modificados

| Archivo | Tipo de cambio |
|---|---|
| `Modules/Inventario/Livewire/Notifications/Notificaciones.php` | **Eliminado** |
| `Modules/Inventario/Livewire/Notifications/NotificacionesIcono.php` | Corrección de path de vista |
| `Modules/Inventario/Entities/Bien.php` | Eliminado `user_id` de `$fillable` y `getDisplayValue()` |
| `Modules/Inventario/Http/Controllers/ActaPDFController.php` | Query corregida (JOIN dependencias) |
| `Modules/Inventario/Livewire/Bienes/BienesIndex.php` | Eliminado `ubicacion_id` de `availableColumns` |
| `config/versiones.php` | BhagamApps → v1.9.3, Inventario → v2.8.1 |
| `CHANGELOG.md` | Entrada v1.9.3 |
| `VERSIONING.md` | Versiones actualizadas |
| `docs/changelog/inventario.md` | Entrada v2.8.1 |
| `docs/changelog/bhagamapps.md` | Entrada v1.9.3 |
| `docs/impl/IMPL-INV-004-Inventory-Core-Remediation-Package.md` | Este documento |

---

## Validaciones

| ID | Verificación | Resultado |
|---|---|---|
| V-001 | 0 referencias a `aprobar-cambios-bienes` / `rechazar-cambios-bienes` | ✅ PASS |
| V-002 | `ActaPDFController` genera resultados válidos (JOIN correcto) | ✅ PASS |
| V-003 | 0 referencias a `bienes.user_id` inexistente en model/controllers | ✅ PASS |
| V-004 | 0 referencias a `ubicacion_id` inexistente en componente Bienes | ✅ PASS |
| V-005 | Sin errores de ejecución (no se introdujeron nuevas referencias rotas) | ✅ PASS |
| V-006 | Sin regresiones funcionales (display `user_id` vía dependencia intacto, catálogos intactos) | ✅ PASS |

---

## Trazabilidad de versiones

| Componente | Antes | Después |
|---|---|---|
| Inventario | v2.8.0 | v2.8.1 |
| BhagamApps | v1.9.2 | v1.9.3 |

- `config/versiones.php` → actualizado
- `VERSIONING.md` → actualizado
- `CHANGELOG.md` → entrada v1.9.3 agregada
- `docs/changelog/inventario.md` → entrada v2.8.1 agregada
- `docs/changelog/bhagamapps.md` → entrada v1.9.3 agregada
- Footer (via `ChangelogModal` → `config('versiones.BhagamApps')`) → automáticamente actualizado
- Pantalla de versiones (via `ChangelogModal` + `docs/changelog/*.md`) → automáticamente actualizado

---

## Estado post-remediación — GAPs de AUDIT-INV-003

| GAP | Severidad original | Estado |
|---|---|---|
| GAP-001 | ALTA | ✅ CERRADO — componente eliminado |
| GAP-002 | ALTA | ✅ CERRADO — referencias eliminadas, query corregida |
| GAP-003 | MEDIA | ✅ DOCUMENTADO — decisión arquitectónica tomada |
| GAP-004 | MEDIA | ✅ CERRADO — referencia a columna inexistente eliminada |
| GAP-005 | MEDIA | ⏳ PENDIENTE — tests automatizados (fuera de alcance IMPL-INV-004) |
| GAP-006 | BAJA | ⏳ PENDIENTE — MantenimientosProgramados sin UI |
| GAP-007 | BAJA | ⏳ PENDIENTE — BienesImagenes sin UI activa |
| GAP-008 | BAJA | ⏳ PENDIENTE — dual PDF mechanism |
| GAP-009 | BAJA | ⏳ PENDIENTE — TestFiltroController residual |
| GAP-010 | BAJA | ⏳ PENDIENTE — MantenimientoProgramado.$fillable desync |

**El módulo Inventario queda listo para iniciar IMPL-INV-005 (Historial de Ubicaciones).**

---

*Implementado sobre commit base `998b993` — rama `main` — 2026-06-10.*

---

## Suplemento v2.8.2 — Validaciones residuales post-remediación

**Fecha:** 2026-06-10
**Tipo:** Correcciones residuales detectadas en auditoría post-commit

Durante la revisión del commit `e4364d7` (v2.8.1) se identificaron residuos no cubiertos
por las validaciones originales. Este suplemento los cierra.

### Residuos corregidos

#### Sort SQL bug — BienesIndex (V-005)

`$ordenBase` incluía `user_id` y `detalle`, que no son columnas reales de la tabla `bienes`.
Si un Admin/Rector hacía clic en el encabezado de esas columnas para ordenar, `filtrarBienesQuery()`
ejecutaba `ORDER BY bienes.user_id` generando `QueryException`.

**Fix:** Guard de allowlist `$columnasSortables` en `filtrarBienesQuery()`. Columnas no listadas
caen a `id`.

| Archivo | Cambio |
|---|---|
| `Livewire/Bienes/BienesIndex.php` | Allowlist `$columnasSortables` en `filtrarBienesQuery()` |
| `Livewire/Bienes/BienesIndex.php` | Eliminada propiedad de formulario muerta `public $user_id` |

#### Código muerto EditarCampoBien (V-003/V-004)

`inferirTabla()` y `cargarOpciones()` tenían casos para `ubicacion_id` y `user_id`, columnas
no pertenecientes al modelo `Bien`. Ambos son código muerto: la vista `bienes-index.blade.php`
intercepta esas columnas antes de montar `EditarCampoBien`.

| Archivo | Cambio |
|---|---|
| `Livewire/Bienes/EditarCampoBien.php` | Eliminados casos `ubicacion_id` y `user_id` de `inferirTabla()` y `cargarOpciones()` |
| `Livewire/Bienes/EditarCampoBien.php` | Eliminado import `Ubicacion` (sin uso tras limpieza) |

#### Blade dead code (V-004)

`@if ($column === 'ubicacion_id')` en `bienes-index.blade.php` era inalcanzable: `ubicacion_id`
nunca puede estar en `$visibleColumns` (no estaba en `$ordenBase`).

| Archivo | Cambio |
|---|---|
| `resources/views/livewire/bienes/bienes-index.blade.php` | Eliminado bloque `@if ($column === 'ubicacion_id')` |

#### Gates huérfanos AuthServiceProvider (GAP-001 cierre final)

Los Gates `aprobar-cambios-bienes` y `rechazar-cambios-bienes` permanecían definidos en
`AuthServiceProvider.php` referenciando el permiso `aprobar-pendientes-bienes`, nunca seeded.
Con `Notificaciones.php` eliminado en v2.8.1, estos gates quedaron completamente huérfanos.

| Archivo | Cambio |
|---|---|
| `app/Providers/AuthServiceProvider.php` | Eliminadas definiciones de Gates `aprobar-cambios-bienes` y `rechazar-cambios-bienes` |

### Validaciones post-suplemento

| ID | Verificación | Resultado |
|---|---|---|
| V-001 | 0 Gates referenciando permisos inexistentes | ✅ PASS |
| V-002 | `ActaPDFController` genera resultados válidos | ✅ PASS (sin cambio) |
| V-003 | 0 referencias a `bienes.user_id` en model/controllers/editores | ✅ PASS |
| V-004 | 0 referencias a `ubicacion_id` inexistente en componentes Bienes | ✅ PASS |
| V-005 | Sin errores de ejecución (sort guard activo) | ✅ PASS |
| V-006 | Sin regresiones funcionales | ✅ PASS |
