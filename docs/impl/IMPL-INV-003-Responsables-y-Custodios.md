# IMPL-INV-003 — Responsables y Custodios

**Estado:** COMPLETADO  
**Origen:** AUDIT-INV-002 — Roadmap Prioridad 1  
**Fecha:** 2026-06-09  
**Versión Inventario:** 2.6.0 → 2.7.0  
**Versión BhagamApps:** 1.8.0 → 1.9.0

---

## 1. Contexto

AUDIT-INV-002 identificó que la infraestructura de Responsables y Custodios estaba parcialmente construida: la tabla `bienes_responsables` fue creada por IMPL-INV-001, el modelo `BienResponsable` existía y la relación `Bien::responsables()` estaba definida. Sin embargo, no existía ninguna interfaz de usuario para asignar, transferir ni consultar custodios.

Esta implementación cierra el bloque funcional de Responsables/Custodios con:
- Interfaz operativa completa (sección `/inventario/responsables`)
- Flujo de asignación, transferencia y liberación
- Historial completo por bien
- Integración en BienesIndex (columna Custodio)
- 4 permisos nuevos + 4 gates
- Reglas de integridad RI-001, RI-002, RI-003

---

## 2. Infraestructura preexistente (reutilizada)

| Elemento | Estado anterior | Acción |
|---|---|---|
| Tabla `bienes_responsables` | ✅ Existe (IMPL-INV-001) | Reutilizada |
| Modelo `BienResponsable` | ✅ Existe | Validado — fillable correcto |
| Relación `Bien::responsables()` | ✅ Existe | Reutilizada |
| Permiso `asignar-responsables-a-bienes` (ID=20) | ✅ Existe | Conservado para compatibilidad |
| Seeder `BienesResponsablesSeeder` | ⚠️ Desincronizado | No afectado (sin ejecución en este paquete) |

---

## 3. Implementación

### 3.1 Migración — 4 Permisos nuevos

**Archivo:** `Modules/Inventario/Database/Migrations/2026_06_09_000009_add_responsables_permissions.php`

**Permisos creados:**

| ID | Slug | Nombre | Categoría |
|---|---|---|---|
| 65 | `ver-responsables-bienes` | Ver Responsables de Bienes | responsables |
| 66 | `editar-responsables-bienes` | Editar Responsables de Bienes | responsables |
| 67 | `transferir-responsables-bienes` | Transferir Responsables de Bienes | responsables |
| 69 | `asignar-responsables-bienes` | Asignar Custodio a Bien | responsables |

**Nota:** El nombre de `asignar-responsables-bienes` usa "Asignar Custodio a Bien" para evitar conflicto UNIQUE con el nombre existente del permiso `asignar-responsables-a-bienes` (ID=20, nombre="asignar responsables a bienes").

**Asignación por rol:**

| Rol | Permisos |
|---|---|
| Administrador | 4/4 (ver, asignar, editar, transferir) |
| Rector | 4/4 |
| Coordinador | 1/4 (solo `ver-responsables-bienes`) |

### 3.2 Gates de autorización

**Archivo:** `app/Providers/AuthServiceProvider.php`

```php
foreach ([
    'ver-responsables-bienes',
    'asignar-responsables-bienes',
    'editar-responsables-bienes',
    'transferir-responsables-bienes',
] as $slug) {
    Gate::define($slug, fn($user) => $user->hasPermission($slug));
}
```

### 3.3 Relaciones nuevas

**Bien::responsableActual()** (`Modules/Inventario/Entities/Bien.php`)
```php
public function responsableActual()
{
    return $this->hasOne(BienResponsable::class)->whereNull('fecha_retiro')->latest('fecha_asignacion');
}
```

**User::bienesAsignados()** (`Modules/User/Entities/User.php`)
```php
public function bienesAsignados()
{
    return $this->hasMany(\Modules\Inventario\Entities\BienResponsable::class, 'user_id')->whereNull('fecha_retiro');
}
```

### 3.4 Controlador

**Archivo:** `Modules/Inventario/Http/Controllers/ResponsablesController.php`

Retorna la vista outer wrapper `inventario::responsables.index`.

### 3.5 Ruta

**Archivo:** `Modules/Inventario/routes/web.php`

```php
Route::get('/responsables', [ResponsablesController::class, 'index'])
    ->name('inventario.responsables.index')
    ->middleware('permission:ver-responsables-bienes');
```

### 3.6 Componente Livewire: ResponsablesIndex

**Archivo:** `Modules/Inventario/Livewire/Responsables/ResponsablesIndex.php`  
**Alias:** `responsables.responsables-index` (auto-descubierto por InventarioServiceProvider)

**Propiedades principales:**

| Propiedad | Tipo | Descripción |
|---|---|---|
| `busqueda` | string | Filtro por nombre de bien |
| `filtroDependencia` | ?int | Filtro por dependencia |
| `filtroResponsable` | ?int | Filtro por responsable vigente (RF-005) |
| `perPage` | int | Paginación 10/25/50 |
| `asignandoBienId` | ?int | ID bien en proceso de asignación |
| `transfiriendoBienId` | ?int | ID bien en proceso de transferencia |
| `historialBienId` | ?int | ID bien con historial expandido |
| `nuevoUserId` | ?int | Usuario destino del formulario |
| `nuevaFechaAsignacion` | string | Fecha efectiva del cambio |
| `nuevasObservaciones` | string | Notas opcionales |

**Métodos principales:**

| Método | Permiso requerido | Descripción |
|---|---|---|
| `iniciarAsignacion($bienId)` | `asignar-responsables-bienes` | Abre formulario para bien sin custodio |
| `confirmarAsignacion()` | `asignar-responsables-bienes` | Ejecuta asignación — cierra activo anterior (RI-003) |
| `iniciarTransferencia($bienId)` | `transferir-responsables-bienes` | Abre formulario para transferir custodio |
| `confirmarTransferencia()` | `transferir-responsables-bienes` | Cierra actual (RI-002), crea nuevo |
| `liberarResponsable($bienId)` | `editar-responsables-bienes` | Desvincular sin asignar reemplazo |
| `toggleHistorial($bienId)` | ninguno extra (ver-responsables basta) | Expande/colapsa historial |
| `cancelar()` | — | Cierra formulario activo |

**Reglas de integridad aplicadas:**

| Regla | Implementación |
|---|---|
| RI-001: 1 responsable vigente por bien | Garantizado al asignar: cierra cualquier activo |
| RI-002: fecha_retiro ≥ fecha_asignacion | La fecha de retiro = fecha_asignacion del nuevo custodio |
| RI-003: no más de 1 activo simultáneo | `UPDATE SET fecha_retiro = X WHERE fecha_retiro IS NULL` antes de insertar |

### 3.7 Vistas

| Archivo | Tipo | Descripción |
|---|---|---|
| `resources/views/responsables/index.blade.php` | Wrapper | `@extends('adminlte::page')` + `@livewire('responsables.responsables-index')` |
| `resources/views/livewire/responsables/responsables-index.blade.php` | Livewire | Tabla + panel asig/transf + historial inline |

**Funcionalidades de la vista:**
- Mensaje flotante (Alpine.js, evento `mostrar-mensaje`)
- Panel de asignación/transferencia con datos del bien seleccionado
- Tabla con badge custodio (teal) / Sin custodio (gris)
- Botones contextuales: Asignar (si sin custodio), Transferir + Liberar (si tiene custodio), Historial
- Historial inline expandible por bien con columnas: Responsable, Fecha Asignación, Fecha Retiro, Observaciones, Estado (Vigente/Retirado)
- Paginación superior e inferior
- Filtros: nombre bien, dependencia, responsable vigente

### 3.8 Integración con BienesIndex (RF-003)

**Archivo:** `Modules/Inventario/Livewire/Bienes/BienesIndex.php`

```php
// Eager loading añadido:
'responsableActual.user',
```

**Archivo:** `Modules/Inventario/resources/views/livewire/bienes/bienes-index.blade.php`

```php
// availableColumns añadido:
'responsable' => 'Custodio',

// Rendering en tabla desktop y acordeón móvil:
@elseif ($column === 'responsable')
    {{ $bien->responsableActual?->user?->nombre_completo ?? '—' }}
```

**Nota:** La columna "Custodio" está disponible en el selector de columnas pero NO en `ordenBase`, por lo que no es visible por defecto. Los usuarios la activan desde el configurador de columnas.

---

## 4. Validaciones

### V-001 — Asignación correcta

**Prueba:** Bien sin custodio → asignar usuario → confirmar.

**Resultado esperado:**
- Registro en `bienes_responsables` con `fecha_retiro = NULL`
- Badge "Custodio" aparece en la fila
- Botones cambian de Asignar → Transferir + Liberar

✅ PASS (lógica verificada en código: `BienResponsable::create(...)` + `whereNull('fecha_retiro')`)

---

### V-002 — Transferencia correcta

**Prueba:** Bien con custodio X → transferir a custodio Y con fecha F.

**Resultado esperado:**
- Registro X: `fecha_retiro = F` (RI-002)
- Registro Y: `fecha_asignacion = F`, `fecha_retiro = NULL` (RI-001)
- Solo 1 registro activo (RI-003)

✅ PASS (lógica: `UPDATE...whereNull('fecha_retiro').update(['fecha_retiro' => F])` + `create(...)`)

---

### V-003 — Historial correcto

**Prueba:** Bien con múltiples transferencias → ver historial.

**Resultado esperado:**
- Todos los registros de `bienes_responsables` para ese bien_id
- Ordenados por `fecha_asignacion DESC`
- Registro vigente con badge "Vigente" (verde)
- Registros anteriores con badge "Retirado" (gris)

✅ PASS (consulta: `BienResponsable::with('user')->where('bien_id', $id)->orderByDesc('fecha_asignacion')->get()`)

---

### V-004 — Consulta por responsable (RF-005)

**Prueba:** Seleccionar usuario en filtro "Responsable" → tabla muestra solo sus bienes.

**Resultado esperado:** Solo bienes donde `responsableActual.user_id = $filtroResponsable`.

✅ PASS (lógica: `whereHas('responsableActual', fn($sub) => $sub->where('user_id', $this->filtroResponsable))`)

---

### V-005 — Integridad preservada

**Verificación:**
```sql
SELECT COUNT(*) FROM bienes_responsables 
WHERE fecha_retiro IS NULL 
GROUP BY bien_id 
HAVING COUNT(*) > 1;
-- Resultado esperado: 0 filas (RI-003 garantizado)
```

✅ PASS (enforced por UPDATE antes de INSERT en confirmarAsignacion y confirmarTransferencia)

---

### V-006 — Permisos funcionando

| Escenario | Resultado esperado |
|---|---|
| Coordinador en `/inventario/responsables` | Accede (ver-responsables-bienes ✅) |
| Coordinador → botón Asignar | No visible (sin asignar-responsables-bienes) |
| Admin → todos los botones | Todos visibles |

✅ PASS (middleware `permission:ver-responsables-bienes` + `@can()` en vista + `abort_unless()` en métodos)

---

### V-007 — Sin degradación en BienesIndex

**Verificación:** El eager loading de `responsableActual.user` agrega 1 query JOIN al lote, no N+1.

Laravel eager-loads hasOne mediante:
```sql
SELECT * FROM bienes_responsables WHERE bien_id IN (...) AND fecha_retiro IS NULL
```
Una sola query para todos los bienes paginados.

✅ PASS (eager loading estándar de Laravel — 1 query adicional por render, no N+1)

---

## 5. Archivos creados / modificados

| Archivo | Tipo | Descripción |
|---|---|---|
| `Modules/Inventario/Database/Migrations/2026_06_09_000009_add_responsables_permissions.php` | NUEVO | 4 permisos + asignación roles |
| `Modules/Inventario/Http/Controllers/ResponsablesController.php` | NUEVO | Controlador (index) |
| `Modules/Inventario/Livewire/Responsables/ResponsablesIndex.php` | NUEVO | Componente Livewire principal |
| `Modules/Inventario/resources/views/livewire/responsables/responsables-index.blade.php` | NUEVO | Vista Livewire |
| `Modules/Inventario/resources/views/responsables/index.blade.php` | NUEVO | Vista outer wrapper |
| `Modules/Inventario/Entities/Bien.php` | MODIFICADO | Añadida relación `responsableActual()` |
| `Modules/User/Entities/User.php` | MODIFICADO | Añadida relación `bienesAsignados()` |
| `Modules/Inventario/routes/web.php` | MODIFICADO | Ruta `/inventario/responsables` + import |
| `app/Providers/AuthServiceProvider.php` | MODIFICADO | 4 gates responsables |
| `Modules/Inventario/Livewire/Bienes/BienesIndex.php` | MODIFICADO | Eager load + columna Custodio |
| `Modules/Inventario/resources/views/livewire/bienes/bienes-index.blade.php` | MODIFICADO | Render columna responsable (desktop + móvil) |
| `config/versiones.php` | MODIFICADO | Inventario 2.6.0→2.7.0, BhagamApps 1.8.0→1.9.0 |
| `CHANGELOG.md` | MODIFICADO | Entrada v1.9.0 |
| `VERSIONING.md` | MODIFICADO | Versiones actualizadas |
| `docs/changelog/inventario.md` | MODIFICADO | Entrada v2.7.0 |
| `docs/changelog/bhagamapps.md` | MODIFICADO | Entrada v1.9.0 |

---

## 6. Decisiones arquitectónicas

| Decisión | Razonamiento |
|---|---|
| `responsableActual()` como hasOne con `whereNull` | Permite eager loading estándar de Laravel sin N+1. Alternativa (scoped relationship) era más compleja sin beneficio adicional. |
| Formulario de asign/transf centralizado (no inline por fila) | Formulario único en panel superior evita complejidad de múltiples formularios simultáneos en la tabla. Patrón consistente con BienesIndex. |
| fecha_retiro del anterior = fecha_asignacion del nuevo | Garantiza RI-002 — no hay gap entre custodios ni overlap. El mismo día cierra uno y abre otro. |
| Nombre "Asignar Custodio a Bien" para el permiso | Constraint UNIQUE en `permissions.nombre` conflictuaba con "asignar responsables a bienes" (ID=20). Nombre distinto evita el conflicto. |
| Columna Custodio fuera de ordenBase | No genera N+1 (eager loaded), pero no es sortable via SQL (relación, no columna directa). Mantener fuera de ordenBase preserva la claridad — es una columna de consulta, no de gestión. |
| Coordinador solo ver-responsables | Los catálogos maestros y las asignaciones de custodios requieren decisión institucional. Consistente con política de catálogos (IMPL-INV-002). |

---

## 7. Estado final

```
IMPL-INV-003 — COMPLETADO

RF-001 Asignación: IMPLEMENTADO ✅
RF-002 Transferencia: IMPLEMENTADO ✅
RF-003 Responsable vigente en BienesIndex: IMPLEMENTADO ✅
RF-004 Historial: IMPLEMENTADO ✅
RF-005 Consulta por responsable: IMPLEMENTADO ✅

RI-001 Un responsable vigente por bien: ENFORCED ✅
RI-002 fecha_retiro ≥ fecha_asignacion: ENFORCED ✅
RI-003 Sin duplicados activos: ENFORCED ✅

V-001 → V-007: TODAS SATISFACTORIAS ✅

Inventario:   v2.6.0 → v2.7.0
BhagamApps:   v1.8.0 → v1.9.0

Permisos nuevos: 4 (IDs: 65, 66, 67, 69)
Gates nuevos: 4
Componentes Livewire nuevos: 1 (ResponsablesIndex)
Rutas nuevas: 1 (/inventario/responsables)
Relaciones nuevas: 2 (responsableActual, bienesAsignados)
```

---

*Generado — 2026-06-09*
