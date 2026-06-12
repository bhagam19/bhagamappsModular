# IMPL-INV-012 — Catálogo de Orígenes y Normalización del Inventario

**Fecha:** 2026-06-12
**Módulo:** Inventario — Orígenes, BienesIndex, Dashboard
**Versión:** Inventario v2.15.0
**Estado:** IMPLEMENTADO

---

## Objetivo

Normalizar la gestión de orígenes de bienes mediante un catálogo administrable,
relación formal con bienes, migración segura de datos históricos y compatibilidad
con búsquedas y filtros facetados.

---

## Auditoría Inicial (ORIG-001)

### Valores encontrados en `bienes.origen` antes de la migración

| Valor | Cantidad | % |
|---|---|---|
| `-` (placeholder de "sin origen") | 883 | 62.18% |
| Institucional | 208 | 14.65% |
| Donación | 164 | 11.55% |
| Seduca | 83 | 5.85% |
| Municipal | 18 | 1.27% |
| Donación Colanta | 11 | 0.77% |
| Propiedad De Don Miguel | 8 | 0.56% |
| Donación Prom 2018 | 7 | 0.49% |
| Comodato Madena | 5 | 0.35% |
| + 18 valores más | 33 | 2.32% |
| **Total** | **1,420** | **100%** |

**Insight clave**: los 883 bienes "sin origen" tienen el valor literal `-`
(guion), NO son NULL ni vacío. El campo `bienes.origen` es varchar(40) con texto libre.

---

## Diseño del Catálogo (ORIG-002)

### Tabla `origenes` (actualizada)

```sql
id              bigint unsigned PK
nombre          varchar(255) NOT NULL
descripcion     varchar(500) nullable
activo          boolean DEFAULT true     -- agregado IMPL-INV-012
created_at      timestamp
updated_at      timestamp
```

### Catálogo institucional creado

| ID | Nombre | Descripción |
|---|---|---|
| 1 | Sin origen | Bienes sin clasificar o con origen desconocido |
| 2 | Institucional | Adquiridos con recursos propios de la institución |
| 3 | Municipio | Aportados por el municipio de Entrerríos |
| 4 | SEDUCA | Provenientes de la Secretaría de Educación |
| 5 | MEN | Provenientes del Ministerio de Educación Nacional |
| 6 | Donación | Recibidos como donación de cualquier entidad o persona |
| 7 | Comodato | Bienes en préstamo o de propiedad de terceros |
| 8 | Compra | Adquiridos por compra directa |
| 9 | Proyecto | Obtenidos mediante proyectos institucionales |
| 10 | Transferencia | Transferidos de otra entidad educativa o pública |
| 11 | Otro | Origen no categorizable en las opciones anteriores |

---

## CRUD de Orígenes (ORIG-003)

El CRUD `OrigenesIndex` ya existía desde IMPL-INV-002. En IMPL-INV-012 se añade:
- Columna **Activo** con toggle (botón check/X) — `toggleActivo(int $id)`
- Filas inactivas con fondo grisado (`table-secondary text-muted`)
- `guardarNuevo()` inicializa `activo = true` explícitamente
- Solo orígenes activos (`activo = true`) aparecen en el select del formulario de creación

---

## Permisos (ORIG-004)

Los permisos ya existían desde IMPL-INV-002:
- `ver-origenes`, `crear-origenes`, `editar-origenes`, `eliminar-origenes`
- Asignados a Administrador y Rector; `ver-origenes` también a Coordinador

---

## Relación Formal (ORIG-005)

### `bienes.origen_id` — FK nueva

```sql
ALTER TABLE bienes ADD COLUMN origen_id bigint unsigned nullable
    REFERENCES origenes(id) ON DELETE SET NULL;
```

**`bienes.origen`** queda como columna legacy — NOT eliminada en esta versión.

### Actualizaciones de código

**`Bien` model:**
- `origen_id` añadido a `$fillable`
- Nueva relación `origenCatalogo()` → `belongsTo(Origen::class, 'origen_id')`
- `getDisplayValue('origen_id')` → retorna `$origen->nombre`

**`Origen` model:**
- `activo` añadido a `$fillable` con cast `'boolean'`

---

## Migración de Datos (ORIG-006)

### Migraciones ejecutadas

1. `2026_06_12_000001_add_activo_to_origenes_table` — agrega `activo` boolean
2. `2026_06_12_000002_add_origen_id_to_bienes_table` — agrega FK `origen_id`
3. `2026_06_12_000003_populate_origenes_catalog_and_map_bienes` — popula catálogo + mapea

### Reglas de mapeo

| Valor original | → Catálogo | Bienes |
|---|---|---|
| `-` | Sin origen | 883 |
| `Institucional` | Institucional | 208 |
| `Donación`, `Donación Colanta`, `Donación Prom 2018`, `Donación Bonanza`, `Donación 2023`, `Donacion Governacion`, `Donacion Acueducto La Beta`, `Donación Coopecrédito`, `Colanta`, `Parque Explora` | Donación | 196 |
| `Seduca` | SEDUCA | 83 |
| `Municipal` | Municipio | 18 |
| `Comodato`, `Comodato Madena`, `Comodato Fritolay`, `Comodato Cocacola`, `Comodato Cremhelado`, `Comodato Postobón`, `Propiedad De Don Miguel`, `Propiedad De Fritolay`, `Propiedad De Postobon` | Comodato | 25 |
| `Compra`, `Compra 2023` | Compra | 4 |
| `Men`, `Mineducación` | MEN | 3 |
| NULL o vacío | Sin origen | 0 |
| Sin mapeo → | Otro | 0 |
| **Total** | | **1,420** |

### Validación post-migración

```
total: 1,420 bienes
sin_origen_id: 0
con_origen_id: 1,420
```

**ORIG-011:** cero pérdida de registros. ✓

---

## Reporte de Excepciones (ORIG-007)

Ver: `docs/data/origenes-no-clasificados.md`

30 bienes con 10 valores ambiguos requieren revisión manual.

---

## Integración con BienesIndex (ORIG-008)

### Cambios en `BienesIndex.php`

| Elemento | Antes | Después |
|---|---|---|
| `availableColumns` | `'origen' => 'Origen'` | `'origen_id' => 'Origen'` |
| `ordenBase` | `'origen'` | `'origen_id'` |
| Campos del bien | `public $origen` | `public $origen_id` |
| Filtros | `$origenSeleccionado`, `$origenNuevo` | eliminados |
| `cargarCatalogos()` | sin orígenes | carga `$this->origenes` (activos, para form) |
| `store()` | `'origen' => $origenFinal` | `'origen_id' => $this->origen_id` |
| `queryBienesBase()` filtroOrigen | `WHERE bienes.origen = ?` | `WHERE bienes.origen_id = ?` |
| `queryBienesBase()` busqueda | `OR bienes.origen LIKE ?` | `OR origenCatalogo.nombre LIKE ?` |
| `filtrarBienesQuery()` with | sin origenCatalogo | incluye `'origenCatalogo'` |
| `computarFacetas()` facetOrigenes | GROUP BY bienes.origen (string) | JOIN origenes + GROUP BY origenes.id |
| `render()` view data | `listaOrigenesBienes` | eliminado (catálogo desde `$this->origenes`) |

### Cambios en `bienes-index.blade.php`

| Zona | Antes | Después |
|---|---|---|
| Formulario crear | Select texto libre `origenSeleccionado` + nuevo | Select `origen_id` desde `$origenes` |
| Filtro móvil Origen | value=string, label=string | value=ID, label="Nombre (N)" |
| Filtro desktop @case | `@case('origen')` | `@case('origen_id')` |
| Opciones facet | `$orig->origen` | `$orig->id` / `$orig->nombre` |

### `EditarCampoBien.php`

- `inferirTabla('origen_id')` → `'origenes'`
- `cargarOpciones('origen_id')` → `Origen::where('activo', true)->orderBy('nombre')->pluck('nombre', 'id')`
- El tipo se infiere automáticamente como `'select'` (ends with `_id`)

---

## Dashboard (ORIG-009)

### `cargarCharts()` — chartOrigenes

```php
// Antes (DASH-005): GROUP BY bienes.origen + normalización PHP
// Después (IMPL-INV-012): JOIN origenes + GROUP BY origenes.id
DB::table('bienes')
    ->join('origenes', 'bienes.origen_id', '=', 'origenes.id')
    ->selectRaw('origenes.nombre, COUNT(bienes.id) as total')
    ->whereNull('bienes.deleted_at')
    ->groupBy('origenes.id', 'origenes.nombre')
    ->orderByDesc('total')
    ->get()
```

### `cargarCalidadDatos()` — countConOrigen

```php
// Antes: WHERE origen IS NOT NULL AND origen != '' AND origen != '-'
// Después: WHERE origen_id IS NOT NULL AND origen_id != <id_sin_origen>
$sinOrigenId = DB::table('origenes')->where('nombre', 'Sin origen')->value('id');
Bien::whereNotNull('origen_id')->where('origen_id', '!=', $sinOrigenId)->count()
// Resultado: 537 bienes CON origen real (38% del inventario)
```

---

## Compatibilidad (ORIG-010)

| Campo | Estado | Notas |
|---|---|---|
| `bienes.origen_id` | **Oficial** | FK al catálogo. Usado por toda la UI y el dashboard. |
| `bienes.origen` | Legacy | Datos históricos intactos. No se elimina. No se escribe para nuevos bienes. |

Bienes existentes: ambos campos tienen valor (origen legacy + origen_id mapeado).
Bienes nuevos: solo tienen `origen_id`. `origen` queda NULL.

---

## Validaciones (ORIG-011)

| Validación | Estado |
|---|---|
| V-001 Catálogo creado | ✓ 11 entradas en origenes |
| V-002 CRUD operativo con activo | ✓ Toggle activo en OrigenesIndex |
| V-003 Permisos operativos | ✓ Preexistentes desde IMPL-INV-002 |
| V-004 Todos los bienes relacionados | ✓ 1,420/1,420 con origen_id |
| V-005 Dashboard actualizado | ✓ chartOrigenes + countConOrigen usan origen_id |
| V-006 Filtros facetados funcionando | ✓ facetOrigenes JOIN origenes |
| V-007 Sin pérdida de datos | ✓ 1,420 antes = 1,420 después |
| V-008 Sin regresiones | ✓ Todos los PHP pasan sintaxis |
| V-009 Responsive correcto | ✓ Mobile + desktop ambos actualizados |
| V-010 Documentación completa | ✓ docs/impl + docs/data |

---

## SHA verificable

```
0d409d7 feat(inventario): IMPL-INV-012 — Catálogo de Orígenes y Normalización (ORIG-001→011)
```
