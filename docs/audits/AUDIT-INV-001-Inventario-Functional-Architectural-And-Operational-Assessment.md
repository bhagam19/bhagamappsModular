# AUDIT-INV-001 — Inventario Functional, Architectural & Operational Assessment

**Estado:** COMPLETADO  
**Responsable:** Auditoría  
**Fecha:** 2026-06-09  
**Origen:** BASELINE-APPS-1.0  
**Alcance:** Solo lectura — sin modificaciones de código, datos ni migraciones

---

## 1. Contexto

Con la aprobación de `BASELINE-APPS-1.0`, el foco principal del proyecto se traslada al módulo Inventario. Este módulo concentra 1.420 bienes registrados distribuidos en 135 dependencias y 28 categorías. Esta auditoría establece la línea base oficial para la siguiente fase de desarrollo.

---

## 2. Mapa del módulo (estado real)

### 2.1 Tablas existentes

| Tabla | Filas | Columnas clave |
|---|---|---|
| `bienes` | 1.420 | id, nombre, cantidad, serie, origen, fecha_adquisicion, precio, categoria_id, dependencia_id, almacenamiento_id, estado_id, mantenimiento_id, observaciones, deleted_at |
| `detalles` | 1.412 | id, bien_id, car_especial, tamano, material, color, marca, otra |
| `categorias` | 28 | id, nombre |
| `dependencias` | 135 | id, nombre, ubicacion_id, user_id |
| `ubicaciones` | 4 | id, nombre |
| `estados` | 4 | id, nombre |
| `almacenamientos` | 2 | id, nombre |
| `mantenimientos` | 3 | id, nombre |
| `bienes_imagenes` | 0 | id, bien_id, ruta_imagen, descripcion |
| `historial_modificaciones_bienes` | 0 | id, bien_id, tipo_objeto, campo, valor_anterior, valor_nuevo, dependencia_id, estado, aprobado_por |
| `historial_dependencias_bienes` | 0 | id, bien_id, dependencia_anterior_id, dependencia_nueva_id, user_id, aprobado_por, fecha_modificacion |
| `historial_eliminaciones_bienes` | 0 | id, bien_id, dependencia_id, user_id, aprobado_por, estado, motivo |
| `mantenimientos_programados` | 0 | id, bien_id, user_id, tipo, titulo, descripcion, fecha_programada, fecha_realizada, estado |
| `notifications` | 0 | id, type, notifiable_type, notifiable_id, data, read_at |

### 2.2 Tablas ausentes (mencionadas en diseño original)

| Tabla esperada | Estado | Impacto |
|---|---|---|
| `detalles_bienes` | NO EXISTE (tabla real: `detalles`) | Inconsistencia de nomenclatura — funcional en código |
| `estados_bienes` | NO EXISTE (tabla real: `estados`) | Inconsistencia de nomenclatura |
| `origenes` | NO EXISTE (campo libre en `bienes.origen`) | Sin catálogo normalizado de orígenes |
| `responsables` | NO EXISTE | F-006 no implementado |
| `bienes_responsables` | NO EXISTE | F-006 bloqueado |
| `bienes_aprobacion_pendiente` | NO EXISTE | Workflow vía `historial_modificaciones_bienes` |
| `historial_ubicaciones_bienes` | NO EXISTE | F-005 no implementado |
| `historial_eliminaciones` | NO EXISTE (tabla real: `historial_eliminaciones_bienes`) | Inconsistencia de nomenclatura |

### 2.3 Catálogos operativos

| Catálogo | Registros | Descripción |
|---|---|---|
| `estados` | Nuevo, Bueno, Regular, Malo | 4 estados de bien |
| `almacenamientos` | En Uso, Almacenado | 2 estados de almacenamiento |
| `mantenimientos` | Al Día, En Mora, Dado de Baja | 3 estados de mantenimiento |
| `ubicaciones` | 4 registros | Sedes/instalaciones |

### 2.4 Distribución de bienes

| Estado | Bienes | % |
|---|---|---|
| Bueno | 1.080 | 76,1% |
| Nuevo | 158 | 11,1% |
| Regular | 96 | 6,8% |
| Malo | 86 | 6,1% |

| Almacenamiento | Bienes | % |
|---|---|---|
| En Uso | 1.316 | 92,7% |
| Almacenado | 104 | 7,3% |

**Top categorías:** Muebles (827), Equipos de Comunicación (170), Equipos de Cómputo (141), Equipos Audiovisuales (88).

---

## 3. Evaluación Funcional

### F-001 — CRUD Principal de Bienes

| Operación | Estado | Evidencia |
|---|---|---|
| Crear | ✅ Implementado | `BienesIndex::store()` con validación completa |
| Consultar | ✅ Implementado | `filtrarBienesQuery()` con eager loading |
| Editar (inline) | ✅ Implementado | `EditarCampoBien` con flujo de aprobación |
| Eliminar (soft) | ✅ Implementado | `solicitarEliminacion()` con flujo aprobador |
| Restaurar | ❌ Sin UI | `SoftDeletes` en modelo pero sin vista de restauración |
| Filtros | ✅ Implementado | Por nombre, categoría, dependencia, estado, user |
| Paginación | ✅ Implementado | `WithPagination`, configurable (10/25/50/100) |
| Ordenamiento | ✅ Implementado | `sortBy()` con dirección asc/desc |
| Duplicar | ✅ Implementado | `duplicar()` replica bien y detalle |

**Resultado F-001:** 8/9 operaciones implementadas. Falta UI de restauración.

---

### F-002 — Edición Inline

| Aspecto | Estado | Evidencia |
|---|---|---|
| EditarCampoBien | ✅ Implementado | Componente Livewire completo |
| Tipo inferido | ✅ Implementado | `inferirTipo()` — text/select/textarea/date/number |
| Opciones de select | ✅ Implementado | `cargarOpciones()` para todos los campos FK |
| Persistencia directa | ✅ Rol Administrador/Rector guardan directo |
| Flujo aprobación | ✅ Rol sin permiso → `historial_modificaciones_bienes` + notificación |
| Lógica estado_id | ✅ Cascada automática: malo→dado de baja, regular→en mora |
| Validación | ✅ Por tipo de campo |
| **Bug HmbIndex** | ⚠️ `$modificacion` usado antes del null check en `aprobarModificacion()` |

**Resultado F-002:** Funcional con un bug de lógica en el aprobador.

---

### F-003 — Detalles de Bien

| Aspecto | Estado | Evidencia |
|---|---|---|
| CRUD via Livewire | ✅ Implementado | `EditarDetalleBien` con toggle inline |
| Creación si no existe | ✅ `Detalle::firstOrNew()` |
| Flujo aprobación | ✅ Envío a `historial_modificaciones_bienes` con tipo_objeto='detalle' |
| Integración con bien | ✅ eager-loaded en `filtrarBienesQuery()` |
| Cobertura | ⚠️ 1.412/1.420 bienes con detalle (8 sin detalle) |

**Resultado F-003:** Funcional. 8 bienes activos sin registro de detalle.

---

### F-004 — Historial de Modificaciones (HMB)

| Aspecto | Estado | Evidencia |
|---|---|---|
| Registro automático | ✅ Creado en `EditarCampoBien::actualizar()` y `EditarDetalleBien::actualizar()` |
| Tipo 'bien' | ✅ Modifica campo directo del bien |
| Tipo 'detalle' | ✅ Aplica JSON de cambios al detalle |
| Aprobación | ✅ `HmbIndex::aprobarModificacion()` |
| Rechazo | ✅ `HmbIndex::rechazarModificacion()` |
| Historial dependencias | ✅ Si campo='dependencia_id' → registra en `historial_dependencias_bienes` |
| Resolución FK en vista | ✅ Relaciones `valorAnteriorCategoria`, `valorNuevoCategoria`, etc. |
| Bug logic | ⚠️ Null check de `$modificacion` después de `$bien = Bien::find($modificacion->bien_id)` |

**Resultado F-004:** Funcional con bug de null check en aprobador.

---

### F-005 — Historial de Ubicaciones

| Aspecto | Estado |
|---|---|
| Tabla `historial_ubicaciones_bienes` | ❌ NO EXISTE |
| UI de cambio de ubicación | ❌ NO IMPLEMENTADO |
| Consulta histórica | ❌ NO IMPLEMENTADO |

**Resultado F-005:** NO IMPLEMENTADO.

---

### F-006 — Custodios y Responsables

| Aspecto | Estado |
|---|---|
| Tabla `bienes_responsables` | ❌ NO EXISTE |
| Tabla `responsables` | ❌ NO EXISTE |
| Modelo `BienResponsable` | ✅ Existe |
| Relación `Bien::responsables()` | ✅ Definida (apunta a tabla inexistente) |
| Permiso `asignar-responsables-a-bienes` | ✅ Existe y asignado a roles |
| UI de asignación | ❌ NO IMPLEMENTADO |
| Historial de custodios | ❌ NO IMPLEMENTADO |

**Resultado F-006:** MODELO Y PERMISOS sin tabla ni UI. Cualquier llamada a `bien->responsables` generará error SQL.

---

### F-007 — Imágenes

| Aspecto | Estado |
|---|---|
| Tabla `bienes_imagenes` | ✅ Existe (0 registros) |
| Modelo `BienImagen` | ✅ Existe |
| Relación `Bien::imagenes()` | ✅ Definida |
| Permiso `ver-imagenes-de-bienes` | ✅ Existe y asignado |
| Componente Livewire de imágenes | ❌ NO EXISTE |
| Ruta de carga/visualización | ❌ NO EXISTE |

**Resultado F-007:** TABLA Y MODELO sin UI. No hay forma de cargar ni ver imágenes.

---

### F-008 — Mantenimientos Programados

| Aspecto | Estado |
|---|---|
| Tabla `mantenimientos_programados` | ✅ Existe (0 registros) |
| Modelo `MantenimientoProgramado` | ✅ Existe |
| Relación `Bien::mantenimientosProgramados()` | ✅ Definida |
| UI de programación | ❌ NO EXISTE |
| UI de seguimiento | ❌ NO EXISTE |

**Resultado F-008:** TABLA Y MODELO sin UI. Funcionalidad no operativa.

---

### F-009 — Eliminación con Aprobación

| Aspecto | Estado | Evidencia |
|---|---|---|
| Solicitud de eliminación | ✅ `BienesIndex::solicitarEliminacion()` |
| Aprobación directa (Adm/Rector) | ✅ Soft delete inmediato + historial 'aprobado' |
| Flujo de aprobación | ✅ Otros roles → registro 'pendiente' + notificación |
| Rechazo | ✅ `HebIndex::rechazarEliminacion()` |
| Historial | ✅ `historial_eliminaciones_bienes` |
| **Ruta /inventario/heb** | ❌ INACCESSIBLE — permiso `gestionar-historial-eliminaciones-bienes` NO EXISTE |
| Acceso administrador al HEB | ❌ BLOQUEADO por middleware |

**Resultado F-009:** Flujo de solicitud funcional, pero la interfaz de administración del HEB es completamente inaccesible. Los administradores no pueden ver ni gestionar solicitudes de eliminación pendientes.

---

### F-010 — Actas

| Aspecto | Estado | Evidencia |
|---|---|---|
| `ActaEntregaIndex` Livewire | ✅ Implementado |
| Encabezado | ✅ Vista `livewire/actas/encabezado.blade.php` |
| Texto inicial | ✅ Vista `livewire/actas/texto-inicial.blade.php` |
| Tabla de bienes | ✅ Vista `livewire/actas/tabla-bienes.blade.php` |
| Firmas | ✅ Vista `livewire/actas/firmas.blade.php` (solo última página) |
| Footer | ✅ Vista `livewire/actas/footer.blade.php` |
| Paginación HTML | ✅ `ActaPrinter::renderActaPaginada()` con `chunk($itemsPorPagina)` |
| Selector de usuario | ✅ Administrador/Rector ven selector; otros ven su propio acta |
| `ActaController::index()` | ⚠️ Usa `Bien::all()` sin filtros ni paginación — stub |

**Resultado F-010:** Funcional vía Livewire. `ActaController` es un stub sin implementar.

---

### F-011 — Sistema de Búsqueda

| Aspecto | Estado |
|---|---|
| Filtro por nombre | ✅ Dropdown con lista completa de nombres únicos |
| Filtro por origen | ✅ Dropdown con lista completa de orígenes únicos |
| Filtro por categoría | ✅ |
| Filtro por dependencia | ✅ |
| Filtro por estado | ✅ |
| Filtro por usuario responsable | ✅ |
| Búsqueda parcial (texto libre) | ❌ Sin campo de texto libre |
| Búsqueda multi-campo simultáneo | ✅ Filtros combinables |

**Resultado F-011:** Búsqueda funcional mediante filtros dropdown. No hay campo de texto libre para búsqueda parcial.

---

### F-012 — Sistema de Filtros

| Aspecto | Estado |
|---|---|
| Filtros dinámicos | ✅ Se actualizan según bienes resultantes |
| `actualizarOpcionesFiltros()` | ✅ Recalcula opciones disponibles |
| Persistencia via URL | ✅ `$queryString` configurado |
| Integración con paginación | ✅ `resetPage()` en cada `updated*` |
| Limpiar filtros | ⚠️ Bug: `limpiarFiltros()` resetea `filtroUser` (U mayúscula) pero la propiedad en `$queryString` es `filtrouser` (minúscula) |

**Resultado F-012:** Funcional con un bug menor en `limpiarFiltros()`.

---

### F-013 — Tabla Dinámica

| Aspecto | Estado |
|---|---|
| `availableColumns` | ✅ 17 columnas definidas |
| `visibleColumns` | ✅ Configurable por usuario |
| `toggleColumn()` | ✅ Respeta `ordenBase` |
| Columnas `user_id` / `ubicacion_id` | ⚠️ Definidas en `availableColumns` pero NO existen en tabla `bienes` |
| Responsive | A verificar visualmente |

**Resultado F-013:** Funcional con dos columnas fantasma (`user_id`, `ubicacion_id`).

---

## 4. Evaluación de CRUDs de Catálogos

### Matriz de CRUDs de Catálogos

| Catálogo | Tabla | Registros | CRUD UI | Permisos | Búsqueda | Estado |
|---|---|---|---|---|---|---|
| Categorías | `categorias` | 28 | ❌ | ❌ | ❌ | SIN UI |
| Dependencias | `dependencias` | 135 | ❌ | ❌ | ❌ | SIN UI |
| Ubicaciones | `ubicaciones` | 4 | ❌ | ❌ | ❌ | SIN UI |
| Estados | `estados` | 4 | ❌ | ❌ | ❌ | SIN UI |
| Almacenamientos | `almacenamientos` | 2 | ❌ | ❌ | ❌ | SIN UI |
| Mantenimientos | `mantenimientos` | 3 | ❌ | ❌ | ❌ | SIN UI |
| Orígenes | campo libre | — | ❌ | ❌ | ❌ | SIN TABLA |
| Responsables | INEXISTENTE | — | ❌ | ❌ | ❌ | SIN TABLA NI UI |

**Todos los catálogos son gestionados exclusivamente vía seeders/tinker.** No existe ninguna interfaz administrativa para crear, editar o eliminar categorías, dependencias, ubicaciones, estados, almacenamientos ni mantenimientos.

---

## 5. Evaluación Arquitectónica

### A-001 — Modelos Eloquent

| Modelo | Tabla | Relaciones | Fillable | Casts | Scopes | Observación |
|---|---|---|---|---|---|---|
| `Bien` | `bienes` | ✅ 9 relaciones | ⚠️ `ubicacion_id`, `user_id` no existen en BD | ✅ precio:decimal:2 | ❌ Sin scopes | fillable desincronizado |
| `Detalle` | `detalles` | ✅ belongsTo Bien | ✅ | — | — | OK |
| `Categoria` | `categorias` | — | ✅ | — | — | Sin relaciones inversas |
| `Dependencia` | `dependencias` | — | ✅ | — | — | Sin relaciones inversas explícitas |
| `Estado` | `estados` | — | ✅ | — | — | OK |
| `Almacenamiento` | `almacenamientos` | — | ✅ | — | — | OK |
| `Mantenimiento` | `mantenimientos` | — | ✅ | — | — | OK |
| `Ubicacion` | `ubicaciones` | — | ✅ | — | — | OK |
| `BienResponsable` | `bienes_responsables` | ✅ bien, user | ✅ | — | — | **TABLA NO EXISTE** |
| `BienImagen` | `bienes_imagenes` | ✅ bien | ✅ | — | — | Sin UI |
| `HistorialModificacionBien` | `historial_modificaciones_bienes` | ✅ múltiples | ✅ | — | — | OK |
| `HistorialEliminacionBien` | `historial_eliminaciones_bienes` | ✅ bien (withTrashed) | ✅ | — | — | OK |
| `HistorialDependenciaBien` | `historial_dependencias_bienes` | ✅ | ✅ | — | — | OK |
| `MantenimientoProgramado` | `mantenimientos_programados` | ✅ bien | ✅ | — | — | Sin UI |

---

### A-002 — Migraciones

| Migración | Estado | Observaciones |
|---|---|---|
| `create_almacenamientos_table` | ✅ | FK nullable |
| `create_categorias_table` | ✅ | OK |
| `create_estados_table` | ✅ | OK |
| `create_mantenimientos_table` | ✅ | OK |
| `create_ubicaciones_table` | ✅ | OK |
| `create_dependencias_table` | ✅ | FK a ubicaciones y users |
| `create_bienes_table` | ⚠️ | Sin `ubicacion_id` ni `user_id` (referenciados en model) |
| `create_detalles_table` | ✅ | OK |
| `create_historial_modificaciones_bienes_table` | ✅ | OK |
| `create_historial_dependencias_bienes_table` | ✅ | OK |
| `create_historial_eliminaciones_bienes_table` | ✅ | OK |
| `create_notifications_table` | ✅ | Standard Laravel |
| `create_bienes_imagenes_table` | ✅ | OK |
| `create_mantenimientos_programados_table` | ✅ | OK |
| `change_precio_float_to_decimal_in_bienes` | ✅ | Correctivo aplicado |

**No existe migración para `bienes_responsables`.**

---

### A-003 — Livewire

| Componente | Tipo | Estado | Notas |
|---|---|---|---|
| `BienesIndex` | Principal | ✅ Operativo | N+1 en `camposPendientes()` (ver R-001) |
| `EditarCampoBien` | Hijo inline | ✅ Operativo | |
| `EditarDetalleBien` | Hijo inline | ✅ Operativo | |
| `EditarDetalleBienModal` | Modal | ⚠️ Existe pero sin uso registrado | Posible componente alternativo no integrado |
| `ActaEntregaIndex` | Actas | ✅ Operativo | |
| `ActaPrinter` | Servicio | ✅ Operativo | Renderizado HTML paginado |
| `ActaPDF` | PDF | ⚠️ Existe, a verificar integración | |
| `HmbIndex` | HMB | ✅ Operativo | Bug null check en `aprobarModificacion()` |
| `HebIndex` | HEB | ⚠️ Componente OK | Ruta bloqueada por permiso inexistente |
| `NotificacionHmb` | Notification | ✅ | Canal DB |
| `NotificacionHeb` | Notification | ✅ | Canal DB |
| `Notificaciones` | Dropdown | ✅ | |
| `NotificacionesDropdown` | UI | ✅ | |
| `NotificacionesIcono` | UI | ✅ | |

---

### A-004 — Autorización

| Permiso | Slug | Existe en BD | Asignado a roles | Usado en código |
|---|---|---|---|---|
| ver-bienes | `ver-bienes` | ✅ | Adm, Rect, Aux, Doc, Coord | ✅ |
| crear-bienes | `crear-bienes` | ✅ | Adm, Rect, Aux, Doc, Coord | ✅ |
| editar-bienes | `editar-bienes` | ✅ | Adm, Rect, Aux, Doc, Coord | ✅ |
| eliminar-bienes | `eliminar-bienes` | ✅ | Adm, Rect, Aux, Doc | ✅ |
| aprobar-bienes | `aprobar-bienes` | ✅ | Adm, Rect, Aux, Doc | ✅ |
| ver-historial-bienes | `ver-historial-bienes` | ✅ | Adm, Rect, Aux, Doc | ✅ |
| asignar-responsables-a-bienes | `asignar-responsables-a-bienes` | ✅ | Adm, Rect, Aux, Doc | ⚠️ Sin UI |
| ver-imagenes-de-bienes | `ver-imagenes-de-bienes` | ✅ | Adm, Rect, Aux, Doc | ⚠️ Sin UI |
| gestionar-historial-modificaciones-bienes | `gestionar-historial-modificaciones-bienes` | ✅ | Adm, Rect | ✅ (ruta /hmb) |
| **gestionar-historial-eliminaciones-bienes** | `gestionar-historial-eliminaciones-bienes` | ❌ **NO EXISTE** | — | ❌ (ruta /heb bloqueada) |
| aprobar-pendientes-bienes | `aprobar-pendientes-bienes` | ✅ | Adm, Rect | ⚠️ Sin UI asociada |
| editar-aprobaciones-pendientes-bienes | `editar-aprobaciones-pendientes-bienes` | ✅ | Adm, Rect | ⚠️ Sin UI asociada |
| eliminar-aprobaciones-pendientes-bienes | `eliminar-aprobaciones-pendientes-bienes` | ✅ | Adm, Rect | ⚠️ Sin UI asociada |
| ver-actas-de-entrega | `ver-actas-de-entrega` | ✅ | Adm, Rect | ✅ |

**Observación:** Coordinador tiene `ver-bienes`, `crear-bienes`, `editar-bienes` pero no tiene eliminación ni aprobación. Sus ediciones generan solicitudes pendientes que solo Administrador y Rector pueden aprobar.

---

### A-005 — Integración con Apps

| Aspecto | Estado |
|---|---|
| `app.access:inventario` | ✅ Aplicado a todas las rutas web |
| App `inventario` en catálogo | ✅ slug=inventario, ruta=/inventario/bienes, habilitada=true |
| Asignada a roles | Administrador, Rector |
| Coordinador con acceso | ❌ No asignado en app_role (puede ver-bienes pero no acceder al módulo) |

**Observación:** Coordinador tiene permisos `ver-bienes`, `crear-bienes`, `editar-bienes` pero la app `inventario` no está asignada a su rol en `app_role`, por lo que el middleware `app.access:inventario` le bloqueará el acceso al módulo completo.

---

### A-006 — BienesIndex: Auditoría Específica

| Aspecto | Estado | Detalle |
|---|---|---|
| `availableColumns` | ⚠️ | 17 columnas, 2 no existen en BD (`user_id`, `ubicacion_id`) |
| `visibleColumns` | ✅ | Inicializado con `ordenBase` |
| `ordenBase` | ⚠️ | Incluye `user_id` (no existe en BD) |
| Filtros | ✅ | 5 filtros + combinables |
| Ordenamiento | ✅ | `sortBy()` funcional |
| Paginación | ✅ | WithPagination + configurable |
| Componentes hijos | ✅ | EditarCampoBien, EditarDetalleBien embebidos |
| Eventos | ✅ | `bienActualizado`, `bienCreado` |
| Edición inline | ✅ | Funcional |
| N+1 en render | ⚠️ | `camposPendientes()` bypasea eager load — ver R-001 |
| `cargarCatalogos()` | ⚠️ | Carga todos los bienes para filtrar IDs únicos |

---

## 6. Evaluación de Integridad

### I-001 — Huérfanos

| Relación | Huérfanos | Estado |
|---|---|---|
| bienes → categorias | 0 | ✅ |
| bienes → dependencias | 0 | ✅ |
| bienes → estados | 0 | ✅ |
| bienes → almacenamientos | 0 | ✅ |
| detalles → bienes | 0 | ✅ |
| bienes_imagenes → bienes | 0 | ✅ |
| historial_modificaciones_bienes → bienes | 0 | ✅ |
| historial_dependencias_bienes → bienes | 0 | ✅ |
| mantenimientos_programados → bienes | 0 | ✅ |

**Integridad referencial: LIMPIA**

### I-002 — Duplicados e inconsistencias en series

| Valor de serie | Bienes | Tipo |
|---|---|---|
| `'0'` | 1.055 | Placeholder sin serial (74,3% del total) |
| `'NULL'` (string) | 304 | Placeholder — debería ser NULL real |
| `'Sin serial'` | 4 | Placeholder textual |
| Valores únicos reales | 57 | Seriales genuinos |
| NULL real | 2 | Sin serie (correcto) |

**Observación:** 1.363 bienes (96%) usan placeholders en lugar de `NULL` real para indicar ausencia de número de serie. El campo `serie` no tiene restricción `UNIQUE` en BD.

### I-003 — Datos inconsistentes

| Aspecto | Estado |
|---|---|
| bienes sin detalle | 8 (0,6%) |
| bienes sin precio | 0 |
| bienes sin cantidad | 0 |
| bienes soft-deleted | 0 (ninguno eliminado aún) |

---

## 7. Evaluación de Rendimiento

### R-001 — N+1 en `BienesIndex::render()`

**Localización:** `BienesIndex.php:576`

```php
$camposPendientes = $bienes->mapWithKeys(fn($bien) => [
    $bien->id => $bien->camposPendientes()
]);
```

`camposPendientes()` llama:

```php
return $this->modificacionesPendientes()
    ->where('estado', 'pendiente')
    ->pluck('campo')
    ->unique()
    ->toArray();
```

Este método encadena una cláusula `where()` sobre la relación **ignorando el eager load** `with(['modificacionesPendientes'])` ya realizado en `filtrarBienesQuery()`. Con `perPage=10`, esto genera **10 consultas adicionales** por render. Con `perPage=25` o 50, el impacto aumenta proporcionalmente.

**Impacto con 1.420 bienes y paginación default 10:** aceptable. Con paginación 100: 100 queries extra por render.

### R-002 — `cargarCatalogos()` ineficiente

```php
$bienes = Bien::whereIn('dependencia_id', $dependenciasIds)->get(); // carga TODOS los bienes
$this->categorias = Categoria::whereIn('id', $bienes->pluck('categoria_id')->unique())->...
```

Carga la colección completa de bienes en memoria para extraer IDs únicos. Con 1.420 bienes, el impacto es bajo hoy pero escala cuadráticamente.

### R-003 — Escalabilidad estimada

| Escenario | Bienes | Impacto estimado |
|---|---|---|
| Actual | 1.420 | Aceptable |
| Corto plazo | ~3.000 | Aceptable con N+1 moderado |
| Mediano plazo | ~10.000 | N+1 impacto notable; `cargarCatalogos()` crítico |
| Largo plazo | 50.000+ | Rediseño requerido sin correcciones |

---

## 8. Preguntas de Auditoría

### P-001 — ¿El CRUD de bienes está completo y operativo?

**Sí, parcialmente.** Crear, consultar, editar inline y eliminar (con aprobación) funcionan. Falta UI de restauración de soft-deleted. El flujo de aprobación de eliminaciones está bloqueado por permiso faltante.

### P-002 — ¿Funcionalidades parcialmente implementadas?

- F-009: Eliminación con aprobación — solicitud funciona, gestión de solicitudes inaccesible.
- F-013: Tabla dinámica — funcional pero con columnas fantasma.
- F-012: Filtros — funcional pero con bug en `limpiarFiltros()`.
- Actas: `ActaController` es stub.

### P-003 — ¿Funcionalidades solo a nivel de BD/modelo?

- F-007 Imágenes: tabla + modelo, sin UI.
- F-008 Mantenimientos programados: tabla + modelo, sin UI.
- F-006 Responsables: modelo, sin tabla ni UI.

### P-004 — ¿Riesgos de pérdida de trazabilidad?

- **Alto:** Historial de ubicaciones (F-005) no implementado. No hay registro de cambios de sede.
- **Alto:** Historial de eliminaciones inaccesible para administradores vía UI.
- **Bajo:** `historial_modificaciones_bienes` y `historial_dependencias_bienes` operativos aunque vacíos.

### P-005 — ¿Riesgos de integridad referencial?

- **Crítico:** `BienResponsable` apunta a tabla inexistente — cualquier llamada a `bien->responsables` falla.
- **Bajo:** `ubicacion_id` y `user_id` en fillable sin columna en BD — Eloquent los ignorará silenciosamente en `create/fill`.

### P-006 — ¿Riesgos de autorización?

- **Crítico:** Ruta `/inventario/heb` inaccessible para todos los roles.
- **Medio:** Coordinador con permisos de bienes pero sin acceso al módulo via `app.access`.
- **Bajo:** Permisos `aprobar-pendientes-bienes`, `editar-aprobaciones-pendientes-bienes`, `eliminar-aprobaciones-pendientes-bienes` asignados a roles sin UI asociada.

### P-007 — ¿Riesgos de rendimiento?

- **Medio:** N+1 en `camposPendientes()` — 10 queries extra por render con perPage default.
- **Bajo:** `cargarCatalogos()` carga colección completa — aceptable en volumen actual.

### P-008 — ¿Porcentaje del roadmap original implementado?

| Área | Estimado |
|---|---|
| CRUD principal bienes | 85% |
| Edición inline | 95% |
| Detalles | 90% |
| Historial modificaciones | 80% |
| Historial ubicaciones | 0% |
| Custodios/Responsables | 5% (solo modelo) |
| Imágenes | 10% (tabla+modelo) |
| Mantenimientos programados | 10% (tabla+modelo) |
| Eliminación con aprobación | 60% (flujo OK, UI admin bloqueada) |
| Actas | 80% |
| Búsqueda/Filtros | 75% |
| CRUDs de catálogos | 0% |
| **Total estimado** | **~45%** |

### P-009 — Matriz de entidades

| Entidad | CRUD | Permisos | Búsqueda | Filtros | Estado |
|---|---|---|---|---|---|
| bienes | C✅ R✅ U✅ D✅ | ✅ | ✅ filtros | ✅ | OPERATIVO |
| detalles | C✅ R✅ U✅ D— | ✅ | — | — | OPERATIVO |
| categorias | C❌ R✅ U❌ D❌ | ❌ | ❌ | — | SOLO LECTURA |
| dependencias | C❌ R✅ U❌ D❌ | ❌ | ❌ | — | SOLO LECTURA |
| ubicaciones | C❌ R✅ U❌ D❌ | ❌ | ❌ | — | SOLO LECTURA |
| estados | C❌ R✅ U❌ D❌ | ❌ | ❌ | — | SOLO LECTURA |
| almacenamientos | C❌ R✅ U❌ D❌ | ❌ | ❌ | — | SOLO LECTURA |
| mantenimientos | C❌ R✅ U❌ D❌ | ❌ | ❌ | — | SOLO LECTURA |
| bienes_imagenes | C❌ R❌ U❌ D❌ | ✅ perms | ❌ | — | SIN UI |
| bienes_responsables | — | ✅ perms | ❌ | — | SIN TABLA |
| mantenimientos_programados | C❌ R❌ U❌ D❌ | ❌ | ❌ | — | SIN UI |
| historial_mod_bienes | R✅ Aprobación✅ | ✅ | — | — | OPERATIVO |
| historial_elim_bienes | R⚠️ Aprobación⚠️ | ❌ (falta perm) | — | — | UI BLOQUEADA |

### P-010 — Funcionalidades sin interfaz operativa

1. Gestión del catálogo de eliminaciones (HEB) — UI bloqueada
2. Carga y visualización de imágenes
3. Asignación de custodios/responsables
4. Programación y seguimiento de mantenimientos
5. Historial de ubicaciones/sedes
6. Gestión de todos los catálogos maestros (categorías, dependencias, ubicaciones, estados, almacenamientos, mantenimientos)
7. Restauración de bienes soft-deleted
8. Búsqueda de texto libre (parcial)

### P-011 — Modelos y migraciones sin UI

| Entidad | Modelo | Migración | UI |
|---|---|---|---|
| `BienImagen` | ✅ | ✅ | ❌ |
| `MantenimientoProgramado` | ✅ | ✅ | ❌ |
| `BienResponsable` | ✅ | ❌ | ❌ |

### P-012 — Principales bloques de deuda técnica

1. **Catálogos sin administración:** 6 catálogos manejados solo por seeder.
2. **HEB inaccessible:** permiso faltante bloquea flujo completo de eliminaciones.
3. **F-005, F-006, F-007, F-008:** funcionalidades con modelo/tabla sin UI.
4. **Serie como campo libre:** 96% de bienes usan placeholders en lugar de NULL.
5. **N+1 queries** en render de BienesIndex.
6. **Coordinador sin acceso al módulo** pese a tener permisos.

---

## 9. Hallazgos

### CRÍTICOS

#### H-CRIT-001 — Ruta /inventario/heb permanentemente inaccessible

**Descripción:** La ruta `GET /inventario/heb` exige el middleware `permission:gestionar-historial-eliminaciones-bienes`. Este permiso no existe en la tabla `permissions`.

**Evidencia:**
```sql
SELECT * FROM permissions WHERE slug = 'gestionar-historial-eliminaciones-bienes';
-- 0 rows
```

**Impacto:** Ningún rol puede acceder al panel de gestión de solicitudes de eliminación. Los administradores no pueden aprobar ni rechazar eliminaciones pendientes. Las solicitudes enviadas por coordinadores permanecen en estado `pendiente` indefinidamente sin que nadie pueda gestionarlas.

**Riesgo:** Bloqueo total del flujo de eliminación con aprobación.

**Recomendación:** Crear el permiso `gestionar-historial-eliminaciones-bienes` y asignarlo a Administrador y Rector.

---

#### H-CRIT-002 — Tabla bienes_responsables inexistente

**Descripción:** El modelo `BienResponsable`, la relación `Bien::responsables()` y el permiso `asignar-responsables-a-bienes` existen y están configurados, pero la tabla `bienes_responsables` no fue creada.

**Evidencia:**
```
TABLE bienes_responsables: NO EXISTE
Permiso asignar-responsables-a-bienes: EXISTS id:20 — asignado a Adm, Rect, Aux, Doc
```

**Impacto:** Cualquier llamada a `$bien->responsables` genera `SQLSTATE[42S02]: Table not found`. El permiso asignado no tiene funcionalidad correspondiente.

**Riesgo:** Error fatal en cualquier código que use la relación de responsables.

**Recomendación:** Crear migración `create_bienes_responsables_table` e implementar UI.

---

### ALTOS

#### H-HIGH-001 — Columnas fantasma en modelo y BienesIndex

**Descripción:** El modelo `Bien::$fillable` incluye `ubicacion_id` y `user_id`. `BienesIndex::$availableColumns` y `$ordenBase` incluyen los mismos campos. Ninguno existe en la tabla `bienes`.

**Evidencia:**
```
En fillable pero NO en BD: ubicacion_id, user_id
BienesIndex availableColumns — user_id: NO EN BD | ubicacion_id: NO EN BD
```

**Impacto:** `user_id` en `$ordenBase` se incluye en `visibleColumns` inicial → la columna "user" aparece en la tabla pero no puede mostrar ni filtrar datos reales. Esto podría causar errores de ordenamiento si se ordena por `user_id`.

**Riesgo:** Datos incorrectos en UI; error SQL si se ordena por campo inexistente.

**Recomendación:** Remover `ubicacion_id` y `user_id` del fillable, availableColumns y ordenBase. `user` se accede correctamente via `dependencia.user`.

---

#### H-HIGH-002 — Coordinador sin acceso al módulo Inventario

**Descripción:** El rol Coordinador tiene permisos `ver-bienes`, `crear-bienes` y `editar-bienes` pero la app `inventario` no está asignada en `app_role` para ese rol. El middleware `app.access:inventario` lo bloqueará en la primera capa.

**Evidencia:**
```
app_role Coordinador: (ninguna app asignada)
Permisos Coordinador: ver-bienes, crear-bienes, editar-bienes
```

**Impacto:** Los coordinadores no pueden acceder al módulo Inventario pese a tener permisos declarados.

**Riesgo:** Funcionalidad inoperativa para el rol más orientado a la gestión de bienes por dependencia.

**Recomendación:** Asignar la app `inventario` al rol Coordinador en `app_role`.

---

#### H-HIGH-003 — Bug en HmbIndex::aprobarModificacion()

**Descripción:** La variable `$modificacion` es accedida en la línea `$bien = Bien::find($modificacion->bien_id)` antes del null check `if (!$modificacion)`.

**Evidencia:** `HmbIndex.php:70-72`

```php
$bien = Bien::with('dependencia')->find($modificacion->bien_id); // acceso prematuro
$user = $bien->dependencia->user_id;

if (!$modificacion) { // null check tardío e inútil
```

**Impacto:** Si `$modificacion` es null (aunque improbable en el flujo normal), la aplicación lanza `TypeError` antes de ejecutar el guard.

**Riesgo:** Error fatal no controlado en caso de concurrencia o doble clic.

**Recomendación:** Mover el null check antes de cualquier acceso a `$modificacion`.

---

#### H-HIGH-004 — Permisos sin UI asociada

**Descripción:** Tres permisos de gestión de aprobaciones existen y están asignados a Administrador y Rector, pero no hay ninguna ruta ni interfaz que los consuma:
- `aprobar-pendientes-bienes`
- `editar-aprobaciones-pendientes-bienes`
- `eliminar-aprobaciones-pendientes-bienes`

**Evidencia:** Ninguna ruta en `web.php` ni componente Livewire usa estos permisos.

**Impacto:** Los permisos no tienen funcionalidad. Las acciones de aprobación/edición/eliminación de pendientes están parcialmente en `HmbIndex` y `HebIndex` pero sin verificación de estos permisos específicos.

**Riesgo:** Autorización incoherente — el código usa `hasRole()` en lugar de `hasPermission()` para las acciones de aprobación.

---

### MEDIOS

#### H-MED-001 — Ausencia total de CRUD para catálogos

**Descripción:** Los 6 catálogos del módulo (categorías, dependencias, ubicaciones, estados, almacenamientos, mantenimientos) son administrables solo mediante seeders o tinker directo.

**Impacto:** Agregar una nueva categoría, dependencia o sede requiere acceso directo a la base de datos.

**Riesgo:** Dependencia de desarrollador para cambios operativos rutinarios.

---

#### H-MED-002 — Serie como campo libre con placeholders masivos

**Descripción:** 1.363 bienes (96%) usan placeholders en el campo `serie`: '0' (1.055), 'NULL' literal (304), 'Sin serial' (4). Solo 57 bienes tienen seriales genuinos.

**Impacto:** El campo serie no puede usarse como identificador único. Búsqueda por serie es no fiable.

**Riesgo:** Imposibilidad de detectar duplicados reales por número de serie.

---

#### H-MED-003 — 8 bienes sin detalle

**Descripción:** 8 bienes activos no tienen registro en la tabla `detalles`.

**Impacto:** La vista de detalle para estos bienes mostraría campos vacíos sin control.

---

#### H-MED-004 — F-005, F-007, F-008 no implementados

- Historial de ubicaciones: sin tabla ni UI.
- Imágenes: tabla + modelo sin UI.
- Mantenimientos programados: tabla + modelo sin UI.

**Impacto:** Funcionalidades documentadas como parte del diseño sin implementación visible para el usuario.

---

#### H-MED-005 — N+1 queries en BienesIndex render

**Descripción:** `camposPendientes()` ejecuta una query individual por bien en cada render, ignorando el eager load ya realizado.

**Impacto:** Con perPage=10: 10 queries extra. Con perPage=100: 100 queries extra. Con múltiples usuarios concurrentes: multiplicador de N+1.

---

#### H-MED-006 — ActaController es un stub

**Descripción:** `ActaController::index()` usa `Bien::all()` sin filtros, paginación ni permisos. La funcionalidad real está en el Livewire `ActaEntregaIndex`.

**Impacto:** La ruta `inventario/actas` puede cargar sin problemas en producción pero la vista `actas/index` no es la interfaz operativa.

---

### BAJOS

#### H-LOW-001 — Bug en limpiarFiltros()

**Descripción:** `limpiarFiltros()` resetea `filtroUser` (U mayúscula) pero en `$queryString` la clave es `filtrouser` (minúscula). PHP distingue mayúsculas en propiedades de objeto.

**Impacto:** El filtro por usuario no se limpia correctamente.

#### H-LOW-002 — Artefactos de prueba en producción

**Descripción:** `TestFiltroController.php` y `test-filtro.blade.php` están en el módulo de producción.

**Impacto:** Posible confusión para mantenimiento. Superficie innecesaria.

#### H-LOW-003 — EditarDetalleBienModal sin integración

**Descripción:** `EditarDetalleBienModal.php` existe pero no está referenciado en ninguna vista operativa.

**Impacto:** Código muerto o alternativa modal no integrada.

#### H-LOW-004 — Actas resource sin implementar

**Descripción:** Las rutas `actas.create`, `actas.show`, `actas.update`, `actas.edit`, `actas.destroy` están declaradas pero `ActaController` solo implementa `index()`.

**Impacto:** Rutas declaradas sin handler real. Retornarán error si se accede.

---

## 10. Matriz de Riesgos

| ID | Hallazgo | Riesgo | Funcional | Arquitectura | Datos | Seguridad | Rendimiento |
|---|---|---|---|---|---|---|---|
| H-CRIT-001 | Ruta HEB inaccesible | CRÍTICO | ❌ | — | — | ⚠️ | — |
| H-CRIT-002 | bienes_responsables sin tabla | CRÍTICO | ❌ | ❌ | — | — | — |
| H-HIGH-001 | Columnas fantasma | ALTO | ⚠️ | ❌ | ⚠️ | — | — |
| H-HIGH-002 | Coordinador sin acceso módulo | ALTO | ❌ | ⚠️ | — | ⚠️ | — |
| H-HIGH-003 | Bug null check HmbIndex | ALTO | ⚠️ | ❌ | — | — | — |
| H-HIGH-004 | Permisos sin UI | ALTO | ⚠️ | ⚠️ | — | ⚠️ | — |
| H-MED-001 | Sin CRUD catálogos | MEDIO | ❌ | — | ⚠️ | — | — |
| H-MED-002 | Serie con placeholders | MEDIO | ⚠️ | — | ❌ | — | — |
| H-MED-003 | 8 bienes sin detalle | BAJO | — | — | ⚠️ | — | — |
| H-MED-004 | F-005/007/008 sin UI | MEDIO | ❌ | ⚠️ | — | — | — |
| H-MED-005 | N+1 en render | MEDIO | — | ⚠️ | — | — | ❌ |
| H-MED-006 | ActaController stub | BAJO | ⚠️ | ⚠️ | — | — | — |
| H-LOW-001 | Bug limpiarFiltros | BAJO | ⚠️ | — | — | — | — |
| H-LOW-002 | Artefactos test | BAJO | — | ⚠️ | — | — | — |
| H-LOW-003 | EditarDetalleBienModal huérfano | BAJO | — | ⚠️ | — | — | — |
| H-LOW-004 | Rutas actas sin implementar | BAJO | ⚠️ | ⚠️ | — | — | — |

---

## 11. Clasificación Global

```
╔══════════════════════════════════════════════════╗
║                                                  ║
║  ESTABLE CON DEUDA TÉCNICA                       ║
║                                                  ║
║  Núcleo CRUD: OPERATIVO                          ║
║  Flujos de aprobación: PARCIALMENTE BLOQUEADOS   ║
║  Cobertura funcional: ~45% del diseño original   ║
║  Integridad referencial: LIMPIA                  ║
║  Deuda técnica: SIGNIFICATIVA                    ║
║                                                  ║
╚══════════════════════════════════════════════════╝
```

El módulo es **operativo para las funciones centrales** (CRUD de bienes, edición inline, historial de modificaciones, actas) pero tiene **2 hallazgos críticos** que bloquean funcionalidades completas y una cobertura del 45% del diseño original.

---

## 12. Roadmap Propuesto

### Prioridad 1 — Correcciones críticas (bloqueantes)

| ID | Tarea | Estimado |
|---|---|---|
| IMPL-INV-001 | Crear permiso `gestionar-historial-eliminaciones-bienes` y asignarlo a Adm/Rector | XS |
| IMPL-INV-002 | Crear migración `create_bienes_responsables_table` | S |
| IMPL-INV-003 | Asignar app `inventario` al rol Coordinador en `app_role` | XS |
| IMPL-INV-004 | Corregir null check en `HmbIndex::aprobarModificacion()` | XS |

### Prioridad 2 — Correcciones funcionales

| ID | Tarea | Estimado |
|---|---|---|
| IMPL-INV-005 | Remover `user_id` y `ubicacion_id` del fillable, availableColumns y ordenBase | XS |
| IMPL-INV-006 | Corregir bug `limpiarFiltros()` — `filtroUser` vs `filtrouser` | XS |
| IMPL-INV-007 | Corregir N+1: usar `$bien->modificacionesPendientes` en lugar de `->modificacionesPendientes()` en `camposPendientes()` | S |
| IMPL-INV-008 | Implementar UI de restauración de bienes soft-deleted | M |
| IMPL-INV-009 | Implementar búsqueda por texto libre (parcial) | M |

### Prioridad 3 — Funcionalidades faltantes

| ID | Tarea | Estimado |
|---|---|---|
| IMPL-INV-010 | CRUD de catálogos: categorías, dependencias, ubicaciones, estados | L |
| IMPL-INV-011 | CRUD de catálogos: almacenamientos, mantenimientos | S |
| IMPL-INV-012 | F-007 Imágenes: componente Livewire de carga y visualización | L |
| IMPL-INV-013 | F-006 Responsables/Custodios: migración + UI + historial | XL |
| IMPL-INV-014 | F-008 Mantenimientos programados: UI completa | L |
| IMPL-INV-015 | F-005 Historial de ubicaciones: migración + UI | M |
| IMPL-INV-016 | Normalización del campo serie (campaña de limpieza de placeholders) | M |

### Prioridad 4 — Optimización

| ID | Tarea | Estimado |
|---|---|---|
| IMPL-INV-017 | Optimizar `cargarCatalogos()` con queries directas sin cargar bienes | S |
| IMPL-INV-018 | Eliminar artefactos de prueba (`TestFiltroController`, `test-filtro.blade.php`) | XS |
| IMPL-INV-019 | Implementar `ActaController` completamente o eliminar rutas resource no usadas | S |
| IMPL-INV-020 | Revisar e integrar `EditarDetalleBienModal` o eliminarlo | S |

---

## 13. Evidencias

| Aspecto | Comando | Resultado |
|---|---|---|
| Tablas existentes | `DESCRIBE <tabla>` vía tinker | 14 tablas auditadas |
| Bienes | `SELECT COUNT(*) FROM bienes` | 1.420 |
| H-CRIT-001 | `SELECT * FROM permissions WHERE slug = 'gestionar-historial-eliminaciones-bienes'` | 0 rows |
| H-CRIT-002 | `\Schema::hasTable('bienes_responsables')` | false |
| H-HIGH-001 | Comparación fillable vs DESCRIBE bienes | ubicacion_id, user_id ausentes |
| H-HIGH-002 | `app_role` WHERE role_id = Coordinador | 0 rows |
| Integridad referencias | 8 queries de orphan check | 0 huérfanos |
| Duplicados serie | GROUP BY serie HAVING cnt > 1 | '0': 1.055, 'NULL': 304, 'Sin serial': 4 |
| Permisos | `SELECT * FROM permissions` + `permission_role` | 35 permisos, 13 de inventario |
| Rutas | `php artisan route:list --verbose` | 22 rutas inventario |

---

*Generado automáticamente — 2026-06-09*
