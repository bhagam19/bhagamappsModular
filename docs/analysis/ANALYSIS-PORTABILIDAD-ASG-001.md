# ANALYSIS-PORTABILIDAD-ASG-001

# Matriz de Portabilidad APPSisGOE

## Estado

EJECUTADO

---

# Proyecto

APPSisGOE

---

# Fecha

2026-06-17

---

# Referencias

* AUDIT-MIGRACION-ASG-001
* ADR-MIGRACION-ASG-001
* PLAN-PORTABILIDAD-ASG-001

---

# Origen

```text
/home/adolfo/web/bhagamapps.com/private/bhagamappsModular
```

---

# Destino

```text
/home/adolfo/web/bhagamapps.com/public_html
```

---

# Leyenda de Categorías

| Categoría | Descripción |
|---|---|
| A | Reutilización directa — sin cambios |
| B | Adaptación — modificaciones menores |
| C | Reimplementación — incompatible, requiere reescritura en arquitectura destino |
| D | Descarte — no será migrado |

---

# Fase MP-01 — Clasificación General de Artefactos

## Resumen

| Categoría | Cantidad | % |
|---|---|---|
| A — Reutilización directa | 25 | 32% |
| B — Adaptación | 20 | 25% |
| C — Reimplementación | 9 | 11% |
| D — Descarte | 25 | 32% |
| **Total** | **79** | **100%** |

---

# Fase MP-02 — Portabilidad Documental

## Resultado: Categoría A (todos)

Todos los documentos son independientes de la arquitectura técnica.

Son portables íntegramente sin modificación.

| Documento origen | Carpeta destino en public_html | Categoría |
|---|---|---|
| `docs/ddom/DDOM-GESTION-001.md` | `docs/decisiones/` | A |
| `docs/ddom/DDOM-GESTION-002.md` | `docs/decisiones/` | A |
| `docs/ddom/DDOM-GESTION-003.md` | `docs/decisiones/` | A |
| `docs/ddom/DDOM-GESTION-004.md` | `docs/decisiones/` | A |
| `docs/ddom/DDOM-GESTION-005.md` | `docs/decisiones/` | A |
| `docs/ddom/DDOM-GESTION-005A.md` | `docs/decisiones/` | A |
| `docs/ddom/DDOM-GESTION-006.md` | `docs/decisiones/` | A |
| `docs/ddom/DDOM-GESTION-DATA-001.md` | `docs/decisiones/` | A |
| `docs/ddom/DDOM-GESTION-IND-001.md` | `docs/decisiones/` | A |
| `docs/ddom/DDOM-GESTION-MAP-001.md` | `docs/decisiones/` | A |
| `docs/ddom/DDOM-GESTION-META-001.md` | `docs/decisiones/` | A |
| `docs/ddom/DDOM-GESTION-OBJ-001.md` | `docs/decisiones/` | A |
| `docs/adr/ADR-GESTION-DATA-001.md` | `docs/decisiones/` | A |
| `docs/adr/ADR-GESTION-OPS-001.md` | `docs/decisiones/` | A |
| `docs/adr/ADR-GESTION-OPS-002.md` | `docs/decisiones/` | A |
| `docs/adr/ADR-MIGRACION-ASG-001.md` | `docs/decisiones/` | A |
| `docs/audits/AUDIT-GESTION-CORE-001.md` | `docs/audits/` | A |
| `docs/audits/AUDIT-GESTION-PLAN-001.md` | `docs/audits/` | A |
| `docs/audits/AUDIT-GESTION-PLAN-002.md` | `docs/audits/` | A |
| `docs/audits/AUDIT-GESTION-OPS-001.md` | `docs/audits/` | A |
| `docs/audits/AUDIT-GESTION-OPS-002.md` | `docs/audits/` | A |
| `docs/roadmap/ROADMAP-GESTION-001.md` | `docs/architecture/` | A |
| `docs/plan/PLAN-GESTION-CORE-001.md` | `docs/architecture/` | A |
| `docs/plan/PLAN-GESTION-OPS-001.md` | `docs/architecture/` | A |
| `docs/plan/PLAN-GESTION-DASH-001.md` | `docs/architecture/` | A |
| `docs/impl/IMPL-GESTION-CORE-001.md` | `docs/impl/` | A |
| `docs/impl/IMPL-GESTION-PLAN-001.md` | `docs/impl/` | A |
| `docs/impl/IMPL-GESTION-OPS-001.md` | `docs/impl/` | A |

**Subtotal MP-02: 28 documentos — Categoría A**

---

# Fase MP-03 — Portabilidad de Datos (tablas existentes)

## Comparación de schemas

### gestiones

| Campo | bhagamappsModular | public_html | Decisión |
|---|---|---|---|
| `id` | bigint PK | bigint PK | Compatible |
| `codigo` | string(10) UNIQUE | **AUSENTE** | No migrar campo |
| `nombre` | string(100) | string (sin límite) | Compatible |
| `slug` | **AUSENTE** | string UNIQUE | No requiere acción |
| `descripcion` | text nullable | text nullable | Compatible |
| `orden` | tinyint | smallint | Compatible |
| `activo` | boolean | **AUSENTE** | No migrar campo |
| `estado` | **AUSENTE** | string default 'activo' | No requiere acción |
| `timestamps` | sí | sí | Compatible |
| `deleted_at` | sí (SoftDeletes) | **AUSENTE** | No migrar |

**Decisión: Categoría D** — Schema incompatible. La tabla existe en destino con modelo diferente. No se migra migración ni modelo.

---

### procesos

| Campo | bhagamappsModular | public_html | Decisión |
|---|---|---|---|
| `gestion_id` | FK → gestiones | FK → gestiones | Compatible |
| `codigo` | string(10) UNIQUE | **AUSENTE** | No migrar campo |
| `nombre` | string(150) | string | Compatible |
| `slug` | **AUSENTE** | string unique(gestion_id,slug) | No requiere acción |
| `descripcion` | text nullable | text nullable | Compatible |
| `orden` | tinyint | smallint | Compatible |
| `activo` | boolean | **AUSENTE** | No migrar campo |
| `estado` | **AUSENTE** | string | No requiere acción |
| `deleted_at` | sí | **AUSENTE** | No migrar |

**Decisión: Categoría D**

---

### componentes

| Campo | bhagamappsModular | public_html | Decisión |
|---|---|---|---|
| `proceso_id` | FK → procesos | FK → procesos | Compatible |
| `codigo` | string(15) UNIQUE | **AUSENTE** | No migrar campo |
| `nombre` | string(200) | string | Compatible |
| `slug` | **AUSENTE** | string unique(proceso_id,slug) | No requiere acción |
| `descripcion` | text nullable | text nullable | Compatible |
| `orden` | tinyint | smallint | Compatible |
| `activo` | boolean | **AUSENTE** | No migrar campo |
| `estado` | **AUSENTE** | string | No requiere acción |
| `deleted_at` | sí | **AUSENTE** | No migrar |

**Decisión: Categoría D**

---

### objetivos

| Campo | bhagamappsModular | public_html | Decisión |
|---|---|---|---|
| `proceso_id` | FK → procesos | FK → procesos | Compatible |
| `codigo` | string(20) UNIQUE | **AUSENTE** | No migrar campo |
| `nombre` | string(250) | string(500) | Compatible |
| `slug` | **AUSENTE** | string unique(proceso_id,slug) | No requiere acción |
| `descripcion` | text nullable | **AUSENTE** | No migrar campo |
| `activo` | boolean | **AUSENTE** | No migrar campo |
| `orden` | **AUSENTE** | smallint | No requiere acción |
| `estado` | **AUSENTE** | string | No requiere acción |
| `deleted_at` | sí | **AUSENTE** | No migrar |

**Decisión: Categoría D**

---

### metas

| Campo | bhagamappsModular | public_html | Decisión |
|---|---|---|---|
| `objetivo_id` | FK → objetivos | FK → objetivos (restrictOnDelete) | Compatible |
| `componente_id` | FK → componentes | FK → componentes (cascadeOnDelete) | Compatible |
| `codigo` | string(25) UNIQUE | **AUSENTE** | No migrar campo |
| `nombre` | string(300) | string(500) | Compatible |
| `slug` | **AUSENTE** | string unique(componente_id,anio,slug) | No requiere acción |
| `descripcion` | text nullable | text nullable | Compatible |
| `unidad` | string(50) nullable | **AUSENTE** | No migrar campo |
| `valor_objetivo` | decimal(10,2) nullable | **AUSENTE** | No migrar campo |
| `anio` | **AUSENTE** | smallint unsigned | No requiere acción |
| `activo` | boolean | **AUSENTE** | No migrar campo |
| `estado` | **AUSENTE** | string(50) | No requiere acción |
| `deleted_at` | sí | **AUSENTE** | No migrar |

**Decisión: Categoría D**

---

### indicadores

**Diferencia estructural crítica:**

```text
bhagamappsModular: indicadores.componente_id → componentes (relación polimórfica vía meta_indicador pivot)
public_html:       indicadores.meta_id → metas (relación directa 1:N)
```

| Campo | bhagamappsModular | public_html | Decisión |
|---|---|---|---|
| `componente_id` | FK → componentes | **AUSENTE** | No migrar |
| `meta_id` | **AUSENTE** (en pivot) | FK → metas | No requiere acción |
| `codigo` | string(20) UNIQUE | **AUSENTE** | No migrar |
| `nombre` | string(200) | string | Compatible |
| `descripcion` | text nullable | text nullable | Compatible |
| `formula` | text nullable | **AUSENTE** | No migrar |
| `unidad` | string(50) | `unidad_medida` string(100) | Semánticamente compatible |
| `frecuencia` | string(20) | **AUSENTE** | No migrar |
| `tipo` | string(20) | `tipo` string(50) + enum TipoIndicador | Adaptable |
| `fuente_dato` | string(100) | **AUSENTE** | No migrar |
| `linea_base` | **AUSENTE** | decimal(10,2) nullable | No requiere acción |
| `meta_esperada` | **AUSENTE** | decimal(10,2) nullable | No requiere acción |
| `activo` | boolean | **AUSENTE** | No migrar |
| `estado` | **AUSENTE** | string(50) | No requiere acción |
| `deleted_at` | sí | **AUSENTE** | No migrar |

**Decisión: Categoría D**

---

### meta_indicador (pivot)

No existe en public_html porque `indicadores` tiene FK directa a `metas`.

El modelo de relación es fundamentalmente diferente:

```text
bhagamappsModular: Meta ↔ Indicador (N:M vía pivot meta_indicador)
public_html:       Meta → Indicador (1:N vía meta_id directo)
```

**Decisión: Categoría D** — El pivot no tiene equivalente en destino. La relación ya está resuelta de forma diferente en public_html.

---

## Resumen MP-03

| Migración | Categoría |
|---|---|
| M-01 create_gestiones_table | D |
| M-02 create_procesos_table | D |
| M-03 create_componentes_table | D |
| M-04 create_objetivos_table | D |
| M-05 create_metas_table | D |
| M-06 create_indicadores_table | D |
| M-07 create_meta_indicador_table | D |

**Subtotal MP-03: 7 migraciones — Categoría D**

---

# Fase MP-04 — Portabilidad Operativa (tablas nuevas)

### actividades

La tabla `actividades` NO existe en public_html.

Las FKs referencian `metas` y `componentes`, que SÍ existen.

La estructura es autónoma y compatible con el schema de destino.

**Decisión: Categoría A** — Migración portable directamente. FK a `metas.id` y `componentes.id` son válidas.

---

### tareas

La tabla `tareas` NO existe en public_html.

FK referencia `actividades`, que se creará en la misma portabilidad.

Columna `responsable_tipo` restringida a `usuario|rol` por ADR-MIGRACION-ASG-001 B-002.

**Decisión: Categoría B** — La migración requiere adaptación mínima: eliminar el valor `dependencia` del comentario/validación de `responsable_tipo` para reflejar la decisión ADR.

---

## Resumen MP-04

| Migración | Categoría | Nota |
|---|---|---|
| M-08 create_actividades_table | A | Sin cambios |
| M-09 create_tareas_table | B | Ajuste de comentario responsable_tipo |

---

# Fase MP-05 — Portabilidad de Código

## Modelos

### Gestion, Proceso, Componente, Objetivo, Meta, Indicador

Ya existen en public_html con implementación propia y compatible.

No se migran. No se reemplazan.

**Decisión: Categoría D** — Los modelos del destino son la implementación oficial.

---

### Actividad

No existe en public_html. Debe ser creado.

Dependencias del modelo:

| Dependencia | Estado en destino | Acción |
|---|---|---|
| `App\Models\Meta` | EXISTE | Compatible |
| `App\Models\Componente` | EXISTE | Compatible |
| `SoftDeletes` | No lo usa public_html | Mantener — no rompe |
| Namespace `App\Models` | Igual en destino | Sin cambio |

Además, se debe añadir la relación `actividades()` al modelo `Meta` existente en public_html:

```php
public function actividades(): HasMany {
    return $this->hasMany(Actividad::class)->orderBy('codigo');
}
```

**Decisión: Categoría A** — Crear `app/Models/Actividad.php` directamente. Añadir relación a `Meta`.

---

### Tarea

No existe en public_html. Debe ser creado con adaptaciones.

Dependencias del modelo:

| Dependencia | bhagamappsModular | Estado en destino | Acción |
|---|---|---|---|
| `Modules\User\Entities\User` | namespace módulo | `App\Models\User` | Cambiar namespace |
| `Modules\User\Entities\Role` | namespace módulo | `Spatie\Permission\Models\Role` | Cambiar namespace |
| `Modules\Inventario\Entities\Dependencia` | módulo Inventario | NO EXISTE | Eliminar |
| `responsable()` método | usuario\|rol\|dependencia | usuario\|rol únicamente | Simplificar match |
| `getNombreResponsableAttribute()` | 3 ramas | 2 ramas | Simplificar |

**Decisión: Categoría B** — Crear `app/Models/Tarea.php` con namespaces corregidos y Dependencia eliminada.

---

## Resumen modelos

| Modelo | Categoría | Acción |
|---|---|---|
| Gestion | D | Ya existe en destino |
| Proceso | D | Ya existe en destino |
| Componente | D | Ya existe en destino |
| Objetivo | D | Ya existe en destino |
| Meta | D + B | Existe; añadir relación `actividades()` |
| Indicador | D | Ya existe en destino |
| Actividad | A | Crear sin cambios + añadir en Meta |
| Tarea | B | Crear con namespaces adaptados |

---

## Controladores

### GestionInstitucionalController

Contiene únicamente `arbol()` — vista de árbol visual Gestión→Proceso→Componente.

public_html ya tiene `GestionController` con CRUD completo para gestiones.

La vista de árbol visual (solo lectura) no existe en destino.

El controlador es simple y portable.

| Elemento | Adaptación |
|---|---|
| Namespace | `App\Http\Controllers\Ppal\` → `App\Http\Controllers\Gestion\` |
| `use App\Models\Gestion` | Compatible — existe en destino |
| Método `arbol()` | Sin cambios |

**Decisión: Categoría B** — Portable con cambio de namespace.

---

### PlaneacionController

Carga `Gestion::with(['procesos.objetivos.metas.indicadores'])`.

En public_html:

```text
Meta::indicadores() → hasMany(Indicador) — directo, sin pivot
```

La cadena de eager loading `metas.indicadores` funciona con `hasMany`.

| Elemento | Adaptación |
|---|---|
| Namespace | `App\Http\Controllers\Ppal\` → `App\Http\Controllers\Planeacion\` |
| `use App\Models\Gestion` | Compatible |
| Eager loading chain | Compatible con el modelo hasMany de public_html |

**Decisión: Categoría B** — Portable con cambio de namespace. Sin cambios funcionales.

---

### OperacionController

Controlador nuevo sin equivalente en destino.

Dependencias:

| Dependencia | bhagamappsModular | Destino | Acción |
|---|---|---|---|
| `App\Models\{Actividad,Meta,Tarea}` | namespace app | igual en destino | Sin cambio |
| `Modules\User\Entities\User` | módulo User | `App\Models\User` | Cambiar |
| `Modules\User\Entities\Role` | módulo User | `Spatie\Permission\Models\Role` | Cambiar |
| `Modules\Inventario\Entities\Dependencia` | módulo Inventario | NO EXISTE | Eliminar |
| `$dependencias` variable en `index()` | colección Dependencia | Eliminar | Eliminar |
| Validación `responsable_tipo in:usuario,rol,dependencia` | 3 valores | 2 valores | Simplificar |

**Decisión: Categoría B** — Portable con 4 cambios puntuales.

---

## Resumen controladores

| Controlador | Categoría | Acción |
|---|---|---|
| GestionInstitucionalController | B | Namespace → Gestion\ |
| PlaneacionController | B | Namespace → Planeacion\ |
| OperacionController | B | Namespaces + eliminar Dependencia |

---

## Rutas

| Elemento | bhagamappsModular | public_html | Acción |
|---|---|---|---|
| Middleware | `auth:sanctum, jetstream.auth_session, verified` | `auth` | Cambiar |
| Prefijo | ninguno | `admin/` | Añadir prefix |
| Ruta gestion | `GET /gestion-institucional` | `GET /admin/gestion-institucional/arbol` | Ajustar |
| Ruta planeacion | `GET /planeacion` | `GET /admin/planeacion-institucional/arbol` | Ajustar |
| Ruta operacion (5 rutas) | `/operacion/*` | `/admin/operacion/*` | Añadir bajo admin |
| Nombres | `gestion.arbol`, `planeacion.index`, `operacion.*` | Mantener o adaptar | Revisar colisión |

**Decisión: 7 rutas — Categoría B**

---

## Vistas

### gestion/arbol.blade.php

| Elemento | bhagamappsModular | public_html | Acción |
|---|---|---|---|
| Layout | `<x-app-layout>` | `<x-admin-layout>` | Cambiar tag |
| Datos | `$gestiones` (Gestion con procesos.componentes) | Igual | Sin cambio |
| Frontend | AlpineJS, Tailwind | Compatible — público usa mismo stack | Sin cambio |

**Decisión: Categoría B** — Cambio de layout únicamente.

---

### planeacion/index.blade.php

| Elemento | bhagamappsModular | public_html | Acción |
|---|---|---|---|
| Layout | `<x-app-layout>` | `<x-admin-layout>` | Cambiar tag |
| Datos | `$gestiones` con eager loading | Compatible | Sin cambio |
| Frontend | AlpineJS, Tailwind | Compatible | Sin cambio |

**Decisión: Categoría B** — Cambio de layout únicamente.

---

### operacion/index.blade.php

| Elemento | bhagamappsModular | public_html | Acción |
|---|---|---|---|
| Layout | `<x-app-layout>` | `<x-admin-layout>` | Cambiar tag |
| `$dependencias` variable | pasada desde controller | Eliminada | Eliminar del selector |
| Selector responsable_tipo | usuario \| rol \| dependencia | usuario \| rol | Eliminar opción dependencia |
| `$usuarios`, `$roles` | `Modules\User\Entities\{User,Role}` | `App\Models\User`, Spatie Role | Transparente (controller lo resuelve) |
| Frontend AlpineJS | CDN | Compatible | Sin cambio |
| Barras de avance | Tailwind | Compatible | Sin cambio |

**Decisión: Categoría B** — Layout + eliminación de dependencia del selector.

---

## Resumen vistas

| Vista | Categoría | Cambios |
|---|---|---|
| gestion/arbol.blade.php | B | Layout |
| planeacion/index.blade.php | B | Layout |
| operacion/index.blade.php | B | Layout + selector responsable_tipo |

---

# Fase MP-06 — Seguridad (RBAC)

## Comparación de sistemas RBAC

| Dimensión | bhagamappsModular | public_html |
|---|---|---|
| Sistema | Custom slug-based | Spatie Permission |
| Clase Permission | `Modules\User\Entities\Permission` | `Spatie\Permission\Models\Permission` |
| Clase Role | `Modules\User\Entities\Role` | `Spatie\Permission\Models\Role` |
| Campo lookup | `slug` | `name` + `guard_name` |
| Asignación | `$rol->permissions()->syncWithoutDetaching($ids)` | `$rol->givePermissionTo($name)` |
| User trait | `hasPermission(string $slug)` | `HasRoles` (Spatie) |

## Permisos a migrar

21 permisos en 3 categorías. Los slugs se convierten en `name` de Spatie.

### Adaptación de migraciones de permisos

**Patrón origen (bhagamappsModular):**

```php
Permission::firstOrCreate(['slug' => 'ver-gestiones'], [...]);
Role::where('nombre', 'Administrador')->get()
    ->each(fn($rol) => $rol->permissions()->syncWithoutDetaching($ids));
```

**Patrón destino (Spatie):**

```php
Permission::firstOrCreate(['name' => 'ver-gestiones', 'guard_name' => 'web']);
$rol = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
$rol->givePermissionTo(['ver-gestiones', ...]);
```

**Decisión M-10, M-11, M-12: Categoría C** — Las migraciones deben ser reescritas completamente con el patrón Spatie. El contenido (slugs/nombres de permisos) se conserva; la mecánica cambia.

## Middleware

| Ruta | bhagamappsModular | public_html |
|---|---|---|
| Verificación de acceso | Sin `permission:` en rutas APPSisGOE | Sin `permission:` en rutas gestion/planeacion actuales |
| Auth principal | `auth:sanctum` | `auth` |
| Sesión Jetstream | `jetstream.auth_session` | No aplica |
| Módulo | `app.access:...` | `modulo.access:...` |

Las rutas APPSisGOE en destino deben añadirse bajo el grupo existente:

```php
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // Rutas existentes de gestion e planeacion
    // Añadir: rutas de operacion
});
```

**Decisión: Categoría B** — Ajuste de grupo middleware.

---

## Resumen MP-06

| Artefacto | Categoría | Acción |
|---|---|---|
| M-10 add_gestion_permissions | C | Reescribir con Spatie |
| M-11 add_planeacion_permissions | C | Reescribir con Spatie |
| M-12 add_operacion_permissions | C | Reescribir con Spatie |
| Middleware de rutas | B | `auth:sanctum` → `auth` |

---

# Fase MP-07 — Plan de Ejecución

## Orden de implementación

Las fases deben ejecutarse en el orden indicado por dependencias.

```text
PASO 1 — Documental (sin riesgo, sin dependencias)
PASO 2 — Tablas nuevas (actividades, tareas)
PASO 3 — Modelos nuevos (Actividad, Tarea) + actualizar Meta
PASO 4 — Permisos (Spatie)
PASO 5 — Controladores
PASO 6 — Rutas
PASO 7 — Vistas
PASO 8 — QA funcional
```

## Cronograma detallado

| Paso | Artefactos | Dependencia previa | Riesgo | Esfuerzo |
|---|---|---|---|---|
| 1 | 28 documentos | Ninguna | Ninguno | BAJO |
| 2 | M-08, M-09 | Paso 1 opcional | Bajo (tablas nuevas) | BAJO |
| 3 | Actividad.php, Tarea.php, Meta.php (relación) | Paso 2 | Bajo | BAJO |
| 4 | 3 migraciones permisos Spatie | Paso 3 | Medio (Spatie API) | MEDIO |
| 5 | 3 controladores | Pasos 3–4 | Medio | MEDIO |
| 6 | 7 rutas | Paso 5 | Bajo | BAJO |
| 7 | 3 vistas | Pasos 5–6 | Bajo | BAJO |
| 8 | QA funcional `/admin/operacion` | Paso 7 | — | MEDIO |

## Riesgos identificados

| ID | Riesgo | Probabilidad | Impacto | Mitigación |
|---|---|---|---|---|
| R-01 | `Meta::actividades()` requiere que actividades table exista | ALTA | ALTO | Ejecutar Paso 2 antes de Paso 3 |
| R-02 | Spatie `givePermissionTo` falla si permiso no existe | MEDIA | MEDIO | Usar `firstOrCreate` antes de asignar |
| R-03 | Colisión de nombre de ruta con rutas existentes | MEDIA | BAJO | Verificar `php artisan route:list` antes del Paso 6 |
| R-04 | `<x-admin-layout>` en public_html puede requerir slots distintos | BAJA | BAJO | Verificar slots del componente antes del Paso 7 |
| R-05 | `Role::firstOrCreate('Administrador')` — nombre exacto del rol en Spatie | MEDIA | MEDIO | Verificar con `Role::where('name', 'Administrador')->exists()` antes del Paso 4 |

---

# Resumen Ejecutivo de Portabilidad

## Tabla maestra de clasificación

| # | Artefacto | Tipo | Categoría | Acción requerida |
|---|---|---|---|---|
| 1 | 28 docs (DDOM, ADR, AUDIT, PLAN, IMPL, ROADMAP) | Doc | A | Copiar a docs/ destino |
| 2 | M-08 create_actividades_table | Migración | A | Copiar sin cambios |
| 3 | M-09 create_tareas_table | Migración | B | Ajustar comentario responsable_tipo |
| 4 | M-10 add_gestion_permissions | Migración | C | Reescribir con Spatie |
| 5 | M-11 add_planeacion_permissions | Migración | C | Reescribir con Spatie |
| 6 | M-12 add_operacion_permissions | Migración | C | Reescribir con Spatie |
| 7 | M-01–M-07 (gestiones→meta_indicador) | Migración | D | No migrar |
| 8 | Actividad.php | Modelo | A | Crear en destino |
| 9 | Tarea.php | Modelo | B | Crear con namespaces Spatie |
| 10 | Gestion/Proceso/Componente/Objetivo/Meta/Indicador.php | Modelo | D | Ya existen |
| 11 | Meta.php (relación actividades) | Modelo | B | Añadir hasMany Actividad |
| 12 | GestionInstitucionalController | Controlador | B | Namespace Gestion\ |
| 13 | PlaneacionController | Controlador | B | Namespace Planeacion\ |
| 14 | OperacionController | Controlador | B | Namespace + eliminar Dependencia |
| 15 | 7 rutas APPSisGOE | Ruta | B | prefix admin + middleware auth |
| 16 | gestion/arbol.blade.php | Vista | B | x-admin-layout |
| 17 | planeacion/index.blade.php | Vista | B | x-admin-layout |
| 18 | operacion/index.blade.php | Vista | B | x-admin-layout + quitar dependencia |
| 19 | 7 seeders institucionales | Seeder | D | Incompatibles con schema slug-based |

## Conteo final

| Categoría | Artefactos |
|---|---|
| A — Reutilización directa | 30 |
| B — Adaptación | 20 |
| C — Reimplementación | 3 |
| D — Descarte | 26 |
| **Total** | **79** |

---

# Estado

```text
ANALYSIS-PORTABILIDAD-ASG-001 — COMPLETO
```

```text
Hoja de ruta lista para iniciar IMPL-PORTABILIDAD-ASG-001.
```
