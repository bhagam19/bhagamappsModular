# AUDIT-INV-003 — Inventory Functional Coverage Audit

**Fecha:** 2026-06-10
**Estado:** COMPLETADO
**Tipo:** Auditoría de Cobertura Funcional — Módulo Inventario
**Auditor:** Claude Code (claude-sonnet-4-6)
**Repositorio auditado:** `private/bhagamappsModular` únicamente

---

## 1. Validación Previa Obligatoria

```
pwd:    /home/adolfo/web/bhagamapps.com/private/bhagamappsModular
remote: https://github.com/bhagam19/bhagamappsModular.git (fetch/push)
branch: main
HEAD:   07ac094
```

Auditoría realizada **exclusivamente** sobre `Modules/Inventario/` dentro del repositorio oficial.
`public_html` y cualquier implementación legacy fueron ignorados completamente.

---

## 2. Inventario de Archivos del Módulo

| Categoría | Cantidad | Archivos clave |
|---|---|---|
| Entities (modelos) | 15 | Bien, BienResponsable, Origen, HistorialModificacionBien, HistorialEliminacionBien, MantenimientoProgramado, BienImagen, Detalle, + 7 catálogos |
| HTTP Controllers | 9 | BienController, ActaController, ActaPDFController, CatalogosController, HebController, HmbController, ResponsablesController, InventarioController (API), TestFiltroController |
| Livewire Components | 21 | Ver §3 completo |
| Migrations | 21 | 14 originales (2025-05-21) + 7 nuevas (2026-06-08/09) |
| Seeders | 16 | BienesSeeder + 15 catálogos/historiales |
| Routes | 2 | `web.php` (15 rutas named) + `api.php` (1 apiResource Sanctum) |
| Views | 28 | blade + livewire blade |
| Providers | 3 | InventarioServiceProvider, RouteServiceProvider, EventServiceProvider |

---

## 3. Componentes Livewire por Subsistema

### 3.1 Bienes
| Componente | Ruta Livewire | Estado |
|---|---|---|
| `BienesIndex` | `Livewire\Bienes\BienesIndex` | Implementado |
| `EditarCampoBien` | `Livewire\Bienes\EditarCampoBien` | Implementado |
| `EditarDetalleBien` | `Livewire\Bienes\EditarDetalleBien` | Implementado |
| `EditarDetalleBienModal` | `Livewire\Bienes\EditarDetalleBienModal` | Implementado |

### 3.2 Actas de Entrega
| Componente | Ruta Livewire | Estado |
|---|---|---|
| `ActaEntregaIndex` | `Livewire\Actas\ActaEntregaIndex` | Implementado |
| `ActaPDF` | `Livewire\Actas\ActaPDF` | Parcial — ver GAP-008 |
| `ActaPrinter` | `Livewire\Actas\ActaPrinter` | Implementado (helper) |

### 3.3 Catálogos Maestros
| Componente | Catálogo | Estado |
|---|---|---|
| `CategoriasIndex` | categorias | Implementado — CRUD completo |
| `DependenciasIndex` | dependencias | Implementado |
| `UbicacionesIndex` | ubicaciones | Implementado — ver GAP-004 |
| `EstadosIndex` | estados | Implementado |
| `OrigenesIndex` | origenes | Implementado — ver GAP-003 |
| `AlmacenamientosIndex` | almacenamientos | Implementado |
| `MantenimientosIndex` | mantenimientos | Implementado |

### 3.4 HEB (Historial Eliminaciones Bienes)
| Componente | Estado |
|---|---|
| `HebIndex` | Implementado |
| `NotificacionHeb` | Implementado |

### 3.5 HMB (Historial Modificaciones Bienes)
| Componente | Estado |
|---|---|
| `HmbIndex` | Implementado |
| `NotificacionHmb` | Implementado |

### 3.6 Responsables / Custodios
| Componente | Estado |
|---|---|
| `ResponsablesIndex` | Implementado |

### 3.7 Notificaciones
| Componente | Estado |
|---|---|
| `Notificaciones` | Parcial — ver GAP-001 |
| `NotificacionesDropdown` | Implementado |
| `NotificacionesIcono` | Implementado |

---

## 4. Cobertura Funcional por Área

### 4.1 Bienes — IMPLEMENTADO ✅

**Capacidades activas:**
- Listado paginado con filtros multi-campo (nombre, usuario, categoría, dependencia, estado)
- Creación con formulario inline, selección desde lista existente o ingreso libre de nombre/origen
- Detalle asociado al bien (car_especial, marca, color, tamaño, material, otra)
- Edición inline campo por campo con lógica de workflow por rol
- Modal de edición de detalle completo
- Duplicación de bien (con detalle)
- Eliminación con workflow de aprobación (solicitud pendiente para Coordinador, directa para Admin/Rector)
- Bienes destacados (top 5 por cantidad)
- Columnas configurables por usuario
- Indicador visual de campos con modificación pendiente
- Ordenación por cualquier columna
- Filtros enlazados al query string para URLs persistentes
- Notificación a Admin/Rector al crear solicitud de modificación o eliminación

**Lógica de estado automática** (en `EditarCampoBien`):
- Estado `malo` → mantenimiento: "dado de baja", almacenamiento: "almacenado"
- Estado `regular` → mantenimiento: "en mora", almacenamiento: "en uso"
- Estado `bueno/nuevo` → mantenimiento: "al día", almacenamiento: "en uso"

**SoftDeletes:** activo en `Bien` con `deleted_at`.

### 4.2 Actas de Entrega — IMPLEMENTADO ✅ (con observación)

**Capacidades activas:**
- Acta HTML multi-página con paginación por ítems configurable
- Encabezado, texto inicial, tabla de bienes, firmas, footer por sección
- Filtro por usuario (Admin/Rector: selector; Coordinador: forzado al propio)
- Descarga PDF vía ruta `/actas/{userId}/pdf` usando `SnappyPdf`

**Observación:** `ActaPDF` (Livewire component) usa `DomPDF` y su método `render()` apunta a `acta-entrega-index` en lugar de `actaPDF` — ver GAP-008.

### 4.3 HMB — Historial de Modificaciones — IMPLEMENTADO ✅

**Capacidades activas:**
- Cola de modificaciones pendientes paginada
- Aprobación: aplica `valor_nuevo` al campo del bien o detalle, registra en historial
- Caso especial `dependencia_id`: también crea entrada en `historial_dependencias_bienes`
- Rechazo: marca estado `rechazada`
- Soporte para `tipo_objeto`: `'bien'` o `'detalle'` (detalle se deserializa como JSON)
- Relaciones polimórficas para valor_anterior/nuevo de categoría, dependencia, estado

### 4.4 HEB — Historial de Eliminaciones — IMPLEMENTADO ✅

**Capacidades activas:**
- Cola de solicitudes de eliminación pendientes paginada
- Aprobación: aplica `softDelete()` al bien
- Rechazo: marca estado `rechazado`
- Ambas operaciones dentro de transacción DB
- Verificación anti-duplicados: bloquea segunda solicitud pendiente sobre el mismo bien
- Verificación de pertenencia a dependencia antes de crear solicitud

### 4.5 Catálogos Maestros — IMPLEMENTADO ✅ (7 catálogos)

Todos los catálogos siguientes tienen CRUD completo vía Livewire (crear/editar/eliminar inline sin páginas separadas):

| Catálogo | Protección al eliminar | Descripción |
|---|---|---|
| Categorías | Sí — bloquea si hay bienes | `withCount('bienes')` |
| Dependencias | No verificada | — |
| Ubicaciones | No verificada | Desconectada de bienes (GAP-004) |
| Estados | No verificada | — |
| Orígenes | No verificada | Desconectada de bienes (GAP-003) |
| Almacenamientos | No verificada | — |
| Mantenimientos | No verificada | — |

### 4.6 Responsables / Custodios — IMPLEMENTADO ✅

**Capacidades activas:**
- Listado de bienes con custodio actual y dependencia
- Asignar custodio (crea `BienResponsable`, cierra anterior)
- Transferir custodio (cierra anterior con `fecha_retiro = fecha_asignacion_nuevo`)
- Liberar custodio (marca `fecha_retiro = today`)
- Ver historial completo de custodios por bien (toggle inline)
- Búsqueda por nombre de bien, filtro por dependencia y por responsable
- Formulario compartido asignar/transferir con validación

**Tabla:** `bienes_responsables` (migración 2026-06-09-000005)

### 4.7 Notificaciones — PARCIALMENTE IMPLEMENTADO ⚠️

**Activo:**
- `NotificacionesDropdown`: dropdown de notificaciones no leídas
- `NotificacionesIcono`: icono con contador
- `NotificacionHeb`: notificación enviada a Admin/Rector al crear solicitud HEB
- `NotificacionHmb`: notificación enviada a Admin/Rector al crear solicitud HMB

**Problema:** `Notificaciones.php` (`Livewire\Notifications\Notificaciones`) usa `$this->authorize('aprobar-cambios-bienes')` y `'rechazar-cambios-bienes'` — permisos que **no existen en ninguna migración**. Este componente provocará una excepción de autorización si se llama. Ver GAP-001.

---

## 5. Esquema de Base de Datos

### 5.1 Tablas activas (21 migraciones)

| Tabla | Creada | Propósito |
|---|---|---|
| `almacenamientos` | 2025-05-21 | Catálogo de tipos de almacenamiento |
| `categorias` | 2025-05-21 | Catálogo de categorías de bienes |
| `estados` | 2025-05-21 | Catálogo de estados de bienes |
| `mantenimientos` | 2025-05-21 | Catálogo de tipos de mantenimiento |
| `ubicaciones` | 2025-05-21 | Catálogo de ubicaciones |
| `dependencias` | 2025-05-21 | Dependencias/oficinas (contiene `user_id`) |
| `bienes` | 2025-05-21 | Registro principal de bienes (SoftDeletes) |
| `detalles` | 2025-05-21 | Detalle extendido de bien (1:1) |
| `historial_modificaciones_bienes` | 2025-05-21 | Cola de modificaciones pendientes/aprobadas |
| `historial_dependencias_bienes` | 2025-05-21 | Log de traslados de dependencia |
| `historial_eliminaciones_bienes` | 2025-05-21 | Cola de eliminaciones pendientes/aprobadas |
| `notifications` | 2025-05-21 | Notificaciones Laravel (tabla estándar) |
| `bienes_imagenes` | 2025-05-21 | Imágenes asociadas a bienes (sin UI activa) |
| `mantenimientos_programados` | 2025-05-21 | Mantenimientos calendarizados (sin UI activa) |
| `bienes` precio decimal | 2026-06-08 | Altera precio de float a decimal(12,2) |
| `permission: gestionar-heb` | 2026-06-09 | Permiso HEB + asignación Admin/Rector |
| `bienes_responsables` | 2026-06-09 | Historial de custodios por bien |
| `assign inventario to coordinador` | 2026-06-09 | Asigna app inventario a rol Coordinador |
| `origenes` | 2026-06-09 | Catálogo de orígenes (tabla independiente) |
| `permissions: catálogos` | 2026-06-09 | 28 permisos CRUD catálogos + roles |
| `permissions: responsables` | 2026-06-09 | 4 permisos custodios + roles |

### 5.2 Columnas ausentes / inconsistencias

| Campo | Modelo | Migración | Impacto |
|---|---|---|---|
| `user_id` | En `Bien.$fillable` | **Ausente** en `bienes` | `ActaPDFController` query falla silenciosamente |
| `ubicacion_id` | En `BienesIndex.availableColumns` | **Ausente** en `bienes` | Columna nunca muestra datos |
| `titulo`, `tipo`, `fecha_realizada` | Ausentes en `MantenimientoProgramado.$fillable` | Presentes en migración | Campos no asignables |

---

## 6. Registro de Permisos

### 6.1 Permisos registrados en migraciones

| Permiso (slug) | Categoría | Roles asignados |
|---|---|---|
| `gestionar-historial-modificaciones-bienes` | aprobaciones | Admin, Rector |
| `gestionar-historial-eliminaciones-bienes` | aprobaciones | Admin, Rector |
| `ver-categorias` + CRUD | catalogos | Admin, Rector (todos); Coordinador (ver) |
| `ver-dependencias` + CRUD | catalogos | Admin, Rector (todos); Coordinador (ver) |
| `ver-ubicaciones` + CRUD | catalogos | Admin, Rector (todos); Coordinador (ver) |
| `ver-estados` + CRUD | catalogos | Admin, Rector (todos); Coordinador (ver) |
| `ver-origenes` + CRUD | catalogos | Admin, Rector (todos); Coordinador (ver) |
| `ver-almacenamientos` + CRUD | catalogos | Admin, Rector (todos); Coordinador (ver) |
| `ver-mantenimientos` + CRUD | catalogos | Admin, Rector (todos); Coordinador (ver) |
| `ver-responsables-bienes` | responsables | Admin, Rector, Coordinador |
| `asignar-responsables-bienes` | responsables | Admin, Rector |
| `editar-responsables-bienes` | responsables | Admin, Rector |
| `transferir-responsables-bienes` | responsables | Admin, Rector |

**Permisos inferidos en código (no en migraciones):** `ver-bienes`, `crear-bienes`, `ver-actas-de-entrega`

### 6.2 Permisos referenciados pero NO registrados

| Permiso | Dónde se usa | Riesgo |
|---|---|---|
| `aprobar-cambios-bienes` | `Notificaciones.php:35` | AuthorizationException en runtime |
| `rechazar-cambios-bienes` | `Notificaciones.php:78` | AuthorizationException en runtime |

---

## 7. Hallazgos — Brechas y Problemas

### GAP-001 — Permisos `aprobar/rechazar-cambios-bienes` no registrados
**Severidad:** ALTA  
**Archivo:** `Livewire/Notifications/Notificaciones.php` líneas 35, 78  
**Descripción:** `$this->authorize('aprobar-cambios-bienes')` y `$this->authorize('rechazar-cambios-bienes')` usan permisos inexistentes en la base de datos. El componente `Notificaciones` lanzará `AuthorizationException` cuando cualquier usuario intente aprobar o rechazar cambios desde la vista de notificaciones.  
**Nota:** El flujo de aprobación funcional está en `HmbIndex` (`gestionar-historial-modificaciones-bienes`), que sí tiene su permiso registrado. `Notificaciones.php` es un componente paralelo posiblemente obsoleto.

### GAP-002 — `bienes.user_id` en modelo pero columna ausente en tabla
**Severidad:** ALTA  
**Archivo:** `Entities/Bien.php` (fillable), `Http/Controllers/ActaPDFController.php:18`  
**Descripción:** `$fillable` del modelo `Bien` incluye `user_id`, pero la migración `bienes` no tiene esa columna. `ActaPDFController` hace `where('user_id', $userId)` que siempre devuelve vacío; la relación real usuario→bien es a través de `dependencias.user_id`.

### GAP-003 — Catálogo `origenes` desconectado del campo `bienes.origen`
**Severidad:** MEDIA  
**Archivo:** `Database/Migrations/2026_06_09_000007_create_origenes_table.php`, `Entities/Origen.php`  
**Descripción:** La tabla `origenes` existe y tiene CRUD completo vía `OrigenesIndex`, pero `bienes.origen` sigue siendo un campo `string` libre. No hay FK `origen_id` en `bienes` ni relación `origen()` en el modelo `Bien`. El catálogo es inerte desde la perspectiva del registro de bienes.

### GAP-004 — `bienes.ubicacion_id` ausente en migración pero presente en UI
**Severidad:** MEDIA  
**Archivo:** `Livewire/Bienes/BienesIndex.php` (availableColumns)  
**Descripción:** `ubicacion_id` aparece en `availableColumns` del listado de bienes, pero la migración de `bienes` no tiene esa columna. `Ubicaciones` tiene catálogo CRUD completo pero no está referenciado por ningún bien.

### GAP-005 — Cobertura de tests: cero
**Severidad:** MEDIA  
**Archivos:** `tests/Feature/.gitkeep`, `tests/Unit/.gitkeep`  
**Descripción:** Ambos directorios de tests del módulo Inventario están vacíos. No hay ningún test automatizado (Feature ni Unit) para ninguno de los subsistemas auditados.

### GAP-006 — `MantenimientosProgramados` sin interfaz de usuario
**Severidad:** BAJA  
**Archivos:** `Entities/MantenimientoProgramado.php`, migración `create_mantenimientos_programados_table.php`  
**Descripción:** Tabla y entidad creadas. Relación `mantenimientosProgramados()` presente en `Bien`. Sin embargo, no existe ningún Livewire component, ruta ni vista para gestionar mantenimientos programados. El subsistema está scaffolded pero no expuesto.

### GAP-007 — `BienesImagenes` sin interfaz activa
**Severidad:** BAJA  
**Archivos:** `Entities/BienImagen.php`, migración `create_bienes_imagenes_table.php`  
**Descripción:** Tabla y entidad creadas. Relación `imagenes()` presente en `Bien`. AUDIT-INV-004A documentó una evaluación de readiness. Sin UI activa para carga o gestión de imágenes.

### GAP-008 — Mecanismo dual de generación de PDF
**Severidad:** BAJA  
**Archivos:** `Http/Controllers/ActaPDFController.php` (usa SnappyPdf), `Livewire/Actas/ActaPDF.php` (usa DomPDF)  
**Descripción:** Existen dos rutas de generación PDF. `ActaPDFController` está activo en la ruta `/actas/{userId}/pdf`. `ActaPDF` Livewire es un componente adicional que además apunta erróneamente a la vista `acta-entrega-index` en su método `render()` en lugar de `actaPDF`.

### GAP-009 — Artefacto de desarrollo `TestFiltroController` sin remover
**Severidad:** BAJA  
**Archivos:** `Http/Controllers/TestFiltroController.php`, `resources/views/livewire/bienes/test-filtro.blade.php`  
**Descripción:** Controller y vista de prueba de filtros presentes en producción. Sin ruta activa encontrada, pero el archivo existe en el repositorio.

### GAP-010 — `MantenimientoProgramado.$fillable` desincronizado con migración
**Severidad:** BAJA  
**Archivo:** `Entities/MantenimientoProgramado.php`  
**Descripción:** El fillable declara `['bien_id', 'fecha_programada', 'descripcion', 'estado']` pero la migración tiene columnas adicionales: `user_id`, `tipo`, `titulo`, `fecha_realizada`. Los campos `titulo` y `tipo` son requeridos (`not null`) en la migración pero ausentes del fillable.

---

## 8. Matriz de Cobertura Funcional

| Área | Rutas | Livewire | Migración | Permisos | Tests | Estado |
|---|:---:|:---:|:---:|:---:|:---:|---|
| Bienes CRUD | ✅ | ✅ | ✅ | ⚠️ parcial* | ❌ | **Funcional** |
| Workflow modificaciones | ✅ | ✅ | ✅ | ✅ | ❌ | **Funcional** |
| Workflow eliminaciones | ✅ | ✅ | ✅ | ✅ | ❌ | **Funcional** |
| Actas de entrega (HTML) | ✅ | ✅ | — | ✅ | ❌ | **Funcional** |
| Actas de entrega (PDF) | ✅ | ⚠️ bug | — | ✅ | ❌ | **Parcial** |
| Catálogo Categorías | ✅ | ✅ | ✅ | ✅ | ❌ | **Funcional** |
| Catálogo Dependencias | ✅ | ✅ | ✅ | ✅ | ❌ | **Funcional** |
| Catálogo Ubicaciones | ✅ | ✅ | ✅ | ✅ | ❌ | **Funcional** (desconectado de bienes) |
| Catálogo Estados | ✅ | ✅ | ✅ | ✅ | ❌ | **Funcional** |
| Catálogo Orígenes | ✅ | ✅ | ✅ | ✅ | ❌ | **Funcional** (desconectado de bienes) |
| Catálogo Almacenamientos | ✅ | ✅ | ✅ | ✅ | ❌ | **Funcional** |
| Catálogo Mantenimientos | ✅ | ✅ | ✅ | ✅ | ❌ | **Funcional** |
| Responsables / Custodios | ✅ | ✅ | ✅ | ✅ | ❌ | **Funcional** |
| Notificaciones dropdown | — | ✅ | ✅ | — | ❌ | **Funcional** |
| Notificaciones aprobación | — | ❌ bug | — | ❌ ausentes | ❌ | **No funcional** |
| Imágenes de bienes | ❌ | ❌ | ✅ | ❌ | ❌ | **Scaffolded** |
| Mantenimientos programados | ❌ | ❌ | ✅ | ❌ | ❌ | **Scaffolded** |

*`ver-bienes`, `crear-bienes` no tienen migración explícita; se asignan en código o manualmente.

---

## 9. Resumen Ejecutivo

El módulo **Inventario** de `bhagamappsModular` tiene una cobertura funcional **alta en sus subsistemas core**: bienes (CRUD + workflow), historial de modificaciones, historial de eliminaciones, actas de entrega y gestión de responsables/custodios están **operativos**. Los 7 catálogos maestros tienen CRUD completo con protección de permisos.

**Puntos críticos a resolver antes de considerar el módulo production-stable:**

1. **GAP-001 (ALTA):** Registrar o eliminar los permisos `aprobar-cambios-bienes` y `rechazar-cambios-bienes`; o eliminar el componente `Notificaciones.php` si es obsoleto.
2. **GAP-002 (ALTA):** Eliminar `user_id` del `$fillable` de `Bien` y corregir `ActaPDFController` para usar la relación `dependencia.user_id`.
3. **GAP-003 (MEDIA):** Decidir si `bienes.origen` migra a FK sobre `origenes` o si el catálogo cumple otra función.
4. **GAP-004 (MEDIA):** Agregar `ubicacion_id` FK a la migración de `bienes` o remover la columna de `availableColumns`.
5. **GAP-005 (MEDIA):** Implementar tests Feature mínimos para los flujos críticos de aprobación.

**Subsistemas scaffolded pendientes de UI:** imágenes de bienes, mantenimientos programados.

---

*Evidencia verificada contra codebase en commit `07ac094` del repositorio `bhagam19/bhagamappsModular` (rama `main`, 2026-06-10).*
