# IMPL-INV-007 — Technical Debt Cleanup

**Estado:** COMPLETADO  
**Versión:** Inventario v2.10.1 | BhagamApps v1.11.1  
**Fecha:** 2026-06-10  
**Origen:** AUDIT-INV-005  
**Tipo:** Limpieza de deuda técnica — sin nuevas funcionalidades

---

## Objetivo

Cerrar DT-001, DT-003 y DT-005 identificados en AUDIT-INV-005.
Dejar DT-002 (normalización de orígenes) como deuda planificada con recomendación arquitectónica.

---

## DT-001 — Gates Huérfanos — CERRADO ✓

### Diagnóstico

4 Gates definidos en `AuthServiceProvider` sin permiso en BD y sin referencias externas:

| Gate eliminado | Motivo | Impacto |
|---|---|---|
| `ver-categorias-bienes` | Slug incorrecto — BD usa `ver-categorias` | Ninguno — dead code |
| `ver-historial-modificaciones` | Slug incorrecto — BD usa `gestionar-historial-modificaciones-bienes` | Ninguno — dead code |
| `ver-historial-ubicaciones` | Slug incorrecto — BD usa `ver-historial-ubicaciones-bienes` | Ninguno — dead code |
| `ver-responsables` | Slug incorrecto — BD usa `ver-responsables-bienes` | Ninguno — dead code |

Adicionalmente, 3 Gates definidos individualmente que ya estaban cubiertos por el foreach de
IMPL-INV-002 (`ver-ubicaciones`, `ver-dependencias`, `ver-estados`) fueron consolidados.
El comment encabezado del bloque fue actualizado para claridad.

### Cambio aplicado

**Archivo:** `app/Providers/AuthServiceProvider.php`

Antes (líneas 32–72): 11 líneas con definiciones redundantes/huérfanas.
Después: 4 líneas netas (2 gates funcionales únicos + 1 gate HMB).

### Validación

```
ver-categorias-bienes: REMOVED OK
ver-historial-modificaciones: REMOVED OK
ver-historial-ubicaciones: REMOVED OK
ver-responsables: REMOVED OK
ALL 19 functional gates OK
```

---

## DT-003 — HMB sin Sidebar — CERRADO ✓

### Diagnóstico

El subsistema HMB (Historial de Modificaciones de Bienes) estaba completamente operativo:
- Ruta: `GET /inventario/hmb` → `inventario.hmb`
- Middleware: `permission:gestionar-historial-modificaciones-bienes`
- Livewire: `HmbIndex` registrado automáticamente
- Permiso en BD asignado a Administrador y Rector

Pero no tenía entrada en el sidebar de Inventario — solo accesible por URL directa.

### Cambio aplicado

**Archivo:** `config/adminlte.php`

Añadida entrada entre "Historial Eliminaciones" e "Historial Ubicaciones":

```php
[
    'text'   => 'Historial Modificaciones',
    'icon'   => 'fas fa-history text-info',
    'route'  => 'inventario.hmb',
    'active' => ['inventario/hmb*'],
    'can'    => 'gestionar-historial-modificaciones-bienes',
],
```

### Validación

```
Sidebar inventario: 14/14 subsistemas con acceso navegable ✓
route inventario.hmb: GET|HEAD inventario/hmb → HmbController ✓
can: gestionar-historial-modificaciones-bienes → en BD, asignado a Admin + Rector ✓
active pattern: ['inventario/hmb*'] → no colisiona con otros patterns ✓
```

---

## DT-005 — Migración Duplicada — CERRADO ✓

### Diagnóstico

`database/migrations/2026_06_09_000001_create_bienes_responsables_table.php` aparecía como
Pending en `migrate:status`. Análisis:

| Atributo | Valor |
|---|---|
| Estado en BD migrations table | NUNCA ejecutada (Pending) |
| Tabla creada por | Módulo: `2026_06_09_000005_create_bienes_responsables_table.php` |
| Guard en el archivo | `if (Schema::hasTable('bienes_responsables')) { return; }` |
| Riesgo de ejecución | Ninguno (idempotente) |
| Riesgo de eliminación | Ninguno (tabla ya existe, ningún código referencia el archivo) |

### Cambio aplicado

Eliminado: `database/migrations/2026_06_09_000001_create_bienes_responsables_table.php`

La fuente canónica para `bienes_responsables` es la migración del módulo Inventario.

### Validación

```
migrate:status | grep 2026_06_09_000001:
  2026_06_09_000001_add_apps_crud_permissions ... [6] Ran
  (el duplicado ya no aparece como Pending)
```

---

## DT-002 — Normalización de Orígenes — EXCLUIDO DEL ALCANCE

### Estado

Deuda técnica planificada para iteración futura. **No modificado en esta implementación.**

### Recomendación Arquitectónica

**Situación actual:**
- `bienes.origen`: columna `string` con 1.420 valores heterogéneos ("Institucional",
  "Comodato Cremhelado", "Donación Colanta", "Municipal", etc.)
- Tabla `origenes`: 0 registros — CRUD operativo pero vacío
- No existe FK `bienes.origen_id`

**Riesgo de normalización:**
Los valores de `bienes.origen` no están normalizados: hay variaciones ortográficas,
mayúsculas inconsistentes, nombres parciales. Una migración automática requiere
validación manual o lógica de deduplicación.

**Plan recomendado (3 fases):**

1. **Fase de extracción (script):**
   ```sql
   INSERT INTO origenes (nom_origen) 
   SELECT DISTINCT origen FROM bienes WHERE origen IS NOT NULL AND origen != '-'
   ORDER BY origen;
   ```
   Revisar manualmente los ~15–20 valores únicos y consolidar duplicados semánticos
   ("Comodato Cremhelado" y "Comodato Cremhelado S.A." → un solo registro).

2. **Fase de migración de datos:**
   ```sql
   ALTER TABLE bienes ADD COLUMN origen_id BIGINT UNSIGNED NULL;
   ALTER TABLE bienes ADD CONSTRAINT fk_bienes_origen FOREIGN KEY (origen_id) REFERENCES origenes(id);
   UPDATE bienes b JOIN origenes o ON o.nom_origen = b.origen SET b.origen_id = o.id;
   ```

3. **Fase de depreciación:**
   - Mantener `bienes.origen` temporalmente como fallback de solo lectura
   - Actualizar `Bien::$fillable` para usar `origen_id` en lugar de `origen`
   - Actualizar la relación `Bien::origen()` → `belongsTo(Origen::class, 'origen_id')`
   - Quitar `origen` de formularios de edición

**Precondición obligatoria:** validación humana de los valores únicos antes de ejecutar
cualquier migración de datos. Los 1.420 registros no deben modificarse sin supervisión.

---

## Archivos Modificados

| Archivo | Tipo de cambio |
|---|---|
| `app/Providers/AuthServiceProvider.php` | Eliminados 4 gates huérfanos + 3 redundantes |
| `config/adminlte.php` | Añadida entrada HMB al sidebar |
| `database/migrations/2026_06_09_000001_create_bienes_responsables_table.php` | **ELIMINADO** |
| `config/versiones.php` | Inventario 2.10.0→2.10.1, BhagamApps 1.11.0→1.11.1 |
| `CHANGELOG.md` | v1.11.1 |
| `VERSIONING.md` | Tabla versiones actuales |
| `docs/changelog/inventario.md` | v2.10.1 |
| `docs/changelog/bhagamapps.md` | v1.11.1 |
| `docs/impl/IMPL-INV-007-Technical-Debt-Cleanup.md` | Este documento |

---

## Validaciones

| Código | Descripción | Resultado |
|---|---|---|
| V-001 | 0 gates huérfanos | ✓ 4 eliminados, 0 restantes |
| V-002 | HMB accesible desde sidebar | ✓ Entrada añadida con can/route/active correctos |
| V-003 | RBAC funcionando | ✓ 19 functional gates confirmados |
| V-004 | Sin regresiones | ✓ Todos los gates funcionales operativos |
| V-005 | Migraciones consistentes | ✓ migrate:status sin duplicados Pending |
| V-006 | Tests pasan | N/A — sin tests automatizados (DT-004) |
| V-007 | Sidebar Inventario consistente | ✓ 14/14 subsistemas con entrada navegable |

---

## Estado de deuda post IMPL-INV-007

| DT | Estado |
|---|---|
| DT-001 Gates huérfanos | **CERRADO** |
| DT-002 Normalización orígenes | Planificado — recomendación arquitectónica en este doc |
| DT-003 HMB sin sidebar | **CERRADO** |
| DT-004 Tests automatizados | Pendiente — deuda de madurez |
| DT-005 Migración duplicada | **CERRADO** |
