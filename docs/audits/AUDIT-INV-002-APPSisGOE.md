# AUDIT-INV-002 — Módulo Inventario: Auditoría Funcional para APPSisGOE

**Fecha:** 2026-06-14  
**Versión auditada:** Inventario v2.4.0  
**Auditor:** Claude Sonnet 4.6 (arquitectónico — sin modificaciones al código)  
**Propósito:** Base para el diseño del módulo Inventario Institucional en APPSisGOE

---

## 1. Mapa Funcional

El módulo Inventario soporta los siguientes procesos de negocio:

| # | Proceso | Descripción |
|---|---------|-------------|
| 1 | **Registro de bienes** | Alta de bienes institucionales con datos completos y detalles técnicos |
| 2 | **Modificación controlada (HMB)** | Flujo de aprobación para cambios en campos de bienes existentes |
| 3 | **Baja de bienes (HEB)** | Flujo de aprobación para eliminación lógica (soft delete) de bienes |
| 4 | **Gestión de custodios** | Asignación y retiro de responsables de bienes |
| 5 | **Seguimiento de ubicaciones** | Registro del historial de movimientos físicos entre ubicaciones |
| 6 | **Mantenimientos programados** | Programación y seguimiento de mantenimientos preventivos y correctivos |
| 7 | **Actas de entrega** | Generación de actas de entrega por responsable (imprimible / PDF) |
| 8 | **Catálogos maestros** | Gestión de categorías, dependencias, ubicaciones, estados, orígenes, almacenamientos, mantenimientos |
| 9 | **Dashboard ejecutivo** | KPIs, alertas, calidad de datos, distribución por categoría/dependencia |
| 10 | **Notificaciones en tiempo real** | Alertas sobre solicitudes pendientes de HMB y HEB |

---

## 2. Modelo de Datos

### Resumen de tablas (16 tablas)

```
bienes                         → entidad central
detalles                       → especificaciones técnicas (1:1 con bienes)
bienes_imagenes                → galería fotográfica
bienes_responsables            → custodios actuales e históricos
historial_modificaciones_bienes → flujo HMB
historial_eliminaciones_bienes  → flujo HEB
historial_dependencias_bienes   → registro de traslados entre dependencias
historial_ubicaciones_bienes    → registro de cambios de ubicación física
mantenimientos_programados      → agenda de mantenimientos
categorias                     → catálogo maestro
dependencias                   → unidades administrativas con responsable
ubicaciones                    → espacios físicos
estados                        → condición del bien (Nuevo/Bueno/Regular/Malo)
almacenamientos                → tipo de almacenamiento
mantenimientos                 → tipo de mantenimiento
origenes                       → procedencia del bien (catálogo normalizado)
notifications                  → tabla Laravel de notificaciones del sistema
```

---

### Detalle de tablas

#### Tabla: `bienes`

| Columna | Tipo | Nullable | Default | Descripción |
|---------|------|----------|---------|-------------|
| `id` | bigint PK | NO | — | — |
| `nombre` | varchar(100) | SÍ | NULL | Nombre del bien (ej. "Portátil HP") |
| `cantidad` | int | SÍ | NULL | Cantidad de unidades |
| `serie` | varchar(40) | SÍ | NULL | Número de serie |
| `origen` | varchar(40) | SÍ | NULL | Campo legacy — texto libre (reemplazado por `origen_id`) |
| `origen_id` | bigint FK | SÍ | NULL | FK → `origenes.id` SET NULL |
| `fecha_adquisicion` | date | SÍ | NULL | Fecha de compra o adquisición |
| `precio` | decimal(12,2) | SÍ | NULL | Valor del bien en pesos colombianos |
| `categoria_id` | bigint FK | SÍ | NULL | FK → `categorias.id` SET NULL |
| `dependencia_id` | bigint FK | SÍ | NULL | FK → `dependencias.id` SET NULL |
| `almacenamiento_id` | bigint FK | SÍ | NULL | FK → `almacenamientos.id` SET NULL |
| `estado_id` | bigint FK | SÍ | NULL | FK → `estados.id` SET NULL |
| `mantenimiento_id` | bigint FK | SÍ | NULL | FK → `mantenimientos.id` SET NULL |
| `observaciones` | varchar(200) | SÍ | NULL | Notas libres |
| `deleted_at` | timestamp | SÍ | NULL | Soft delete |
| `created_at` | timestamp | SÍ | NULL | — |
| `updated_at` | timestamp | SÍ | NULL | — |

**Nota:** `origen` (varchar) es un campo legacy que coexiste con `origen_id` (FK a catálogo). La migración `populate_origenes_catalog_and_map_bienes` normalizó los datos al catálogo.

---

#### Tabla: `detalles`

| Columna | Tipo | Nullable | Descripción |
|---------|------|----------|-------------|
| `id` | bigint PK | NO | — |
| `bien_id` | bigint FK | NO | FK → `bienes.id` CASCADE |
| `car_especial` | varchar(255) | SÍ | Características especiales del bien |
| `tamano` | varchar(255) | SÍ | Dimensiones o talla |
| `material` | varchar(255) | SÍ | Material de fabricación |
| `color` | varchar(255) | SÍ | Color |
| `marca` | varchar(255) | SÍ | Marca comercial |
| `otra` | varchar(255) | SÍ | Información adicional libre |

Relación 1:1 con `bienes`. Se crea solo si hay datos técnicos relevantes.

---

#### Tabla: `bienes_imagenes`

| Columna | Tipo | Nullable | Descripción |
|---------|------|----------|-------------|
| `id` | bigint PK | NO | — |
| `bien_id` | bigint FK | NO | FK → `bienes.id` CASCADE |
| `ruta_imagen` | varchar | NO | Ruta en storage (ej. `storage/bienes/img.jpg`) |
| `descripcion` | varchar | SÍ | Descripción de la imagen |

---

#### Tabla: `bienes_responsables`

| Columna | Tipo | Nullable | Descripción |
|---------|------|----------|-------------|
| `id` | bigint PK | NO | — |
| `bien_id` | bigint FK | NO | FK → `bienes.id` CASCADE |
| `user_id` | bigint FK | SÍ | FK → `users.id` SET NULL |
| `observaciones` | varchar(255) | SÍ | Notas de la asignación |
| `fecha_asignacion` | date | SÍ | Inicio de custodia |
| `fecha_retiro` | date | SÍ | Fin de custodia (NULL = activo) |

**Responsable actual:** `whereNull('fecha_retiro')->latest('fecha_asignacion')` → 1 registro.

---

#### Tabla: `historial_modificaciones_bienes`

| Columna | Tipo | Nullable | Descripción |
|---------|------|----------|-------------|
| `id` | bigint PK | NO | — |
| `bien_id` | bigint FK | NO | FK → `bienes.id` CASCADE |
| `tipo_objeto` | varchar | NO | `'bien'` o `'detalle'` |
| `campo` | varchar | NO | Nombre del campo modificado (ej. `'estado_id'`) |
| `valor_anterior` | text | SÍ | Valor antes del cambio |
| `valor_nuevo` | text | SÍ | Valor propuesto (JSON para `'detalle'`) |
| `dependencia_id` | bigint FK | NO | FK → `dependencias.id` CASCADE |
| `estado` | enum | NO | `'pendiente'`, `'aprobada'`, `'rechazada'` |
| `aprobado_por` | bigint FK | SÍ | FK → `users.id` SET NULL |

---

#### Tabla: `historial_eliminaciones_bienes`

| Columna | Tipo | Nullable | Descripción |
|---------|------|----------|-------------|
| `id` | bigint PK | NO | — |
| `bien_id` | bigint FK | NO | FK → `bienes.id` CASCADE |
| `dependencia_id` | bigint FK | SÍ | FK → `dependencias.id` SET NULL |
| `user_id` | bigint FK | NO | FK → `users.id` (quien solicita) |
| `aprobado_por` | bigint FK | SÍ | FK → `users.id` (quien aprueba) |
| `estado` | enum | NO | `'pendiente'`, `'aprobado'`, `'rechazado'` |
| `motivo` | varchar | NO | Razón de la baja |

---

#### Tabla: `historial_dependencias_bienes`

| Columna | Tipo | Nullable | Descripción |
|---------|------|----------|-------------|
| `id` | bigint PK | NO | — |
| `bien_id` | bigint FK | NO | FK → `bienes.id` CASCADE |
| `dependencia_anterior_id` | bigint FK | SÍ | FK → `dependencias.id` SET NULL |
| `dependencia_nueva_id` | bigint FK | NO | FK → `dependencias.id` CASCADE |
| `user_id` | bigint FK | SÍ | FK → `users.id` SET NULL (quien trasladó) |
| `aprobado_por` | bigint FK | SÍ | FK → `users.id` SET NULL |
| `fecha_modificacion` | timestamp | NO | CURRENT_TIMESTAMP |

Se crea automáticamente cuando se aprueba una modificación de `dependencia_id` en HMB.

---

#### Tabla: `historial_ubicaciones_bienes`

| Columna | Tipo | Nullable | Descripción |
|---------|------|----------|-------------|
| `id` | bigint PK | NO | — |
| `bien_id` | bigint FK | NO | FK → `bienes.id` CASCADE |
| `ubicacion_origen_id` | bigint FK | SÍ | FK → `ubicaciones.id` SET NULL |
| `ubicacion_destino_id` | bigint FK | NO | FK → `ubicaciones.id` CASCADE |
| `user_id` | bigint FK | SÍ | FK → `users.id` SET NULL |
| `fecha_movimiento` | timestamp | NO | CURRENT_TIMESTAMP |
| `observaciones` | varchar(500) | SÍ | Notas del movimiento |

---

#### Tabla: `mantenimientos_programados`

| Columna | Tipo | Nullable | Descripción |
|---------|------|----------|-------------|
| `id` | bigint PK | NO | — |
| `bien_id` | bigint FK | NO | FK → `bienes.id` CASCADE |
| `user_id` | bigint FK | SÍ | FK → `users.id` SET NULL (quien programó) |
| `tipo` | varchar | NO | `'preventivo'`, `'correctivo'`, etc. |
| `titulo` | varchar | NO | Nombre de la tarea de mantenimiento |
| `descripcion` | text | SÍ | Descripción detallada |
| `fecha_programada` | date | NO | Cuándo se planificó |
| `fecha_realizada` | date | SÍ | Cuándo se ejecutó (NULL = pendiente) |
| `estado` | enum | NO | `'pendiente'`, `'realizado'`, `'cancelado'` |

---

#### Tablas de catálogos maestros

| Tabla | Columnas | Valores sembrados |
|-------|----------|------------------|
| `categorias` | `id`, `nombre(60)` | CSV (múltiples categorías institucionales) |
| `ubicaciones` | `id`, `nombre(20)` | CSV (espacios físicos del plantel) |
| `almacenamientos` | `id`, `nombre(20)` | CSV (tipos de almacenamiento) |
| `estados` | `id`, `nombre(20)` | Nuevo, Bueno, Regular, Malo |
| `mantenimientos` | `id`, `nombre(20)` | Tipos de mantenimiento |
| `origenes` | `id`, `nombre`, `descripcion`, `activo` | CSV (fuentes de adquisición) |

#### Tabla: `dependencias`

| Columna | Tipo | Nullable | Descripción |
|---------|------|----------|-------------|
| `id` | bigint PK | NO | — |
| `nombre` | varchar(50) | NO | Nombre de la unidad administrativa |
| `ubicacion_id` | bigint FK | NO | FK → `ubicaciones.id` (NO ACTION) |
| `user_id` | bigint FK | NO | FK → `users.id` (coordinador responsable) |

---

## 3. Entidades de Negocio

### 3.1 Bienes

Entidad central. Representa cualquier bien mueble institucional. Soporta:
- Datos de identificación (nombre, serie, precio, fecha de adquisición)
- Clasificación (categoría, almacenamiento, estado físico)
- Ubicación administrativa (dependencia) y física (historial de ubicaciones)
- Detalles técnicos (marca, color, material, medidas — tabla `detalles`)
- Custodio actual (relación `responsableActual` via `bienes_responsables`)
- Imágenes (tabla `bienes_imagenes`)
- Mantenimientos programados
- Soft delete + historial de eliminaciones

El modelo `Bien` tiene el método `getDisplayValue($key)` que resuelve el valor humano de cualquier campo (incluyendo FK a relaciones), usado por el componente `EditarCampoBien` para mostrar el valor anterior antes de proponer un cambio.

### 3.2 Categorías y Subcategorías

`categorias` es un catálogo plano (sin jerarquía). Los "grupos institucionales" (Mobiliario, TIC, Audiovisual, etc.) se derivan en el dashboard agrupando `categoria_id` en arrays de IDs — no existe una tabla de grupos.

### 3.3 Ubicaciones y Dependencias

- `ubicaciones` = espacios físicos (salones, oficinas, bodegas)
- `dependencias` = unidades administrativas. Cada dependencia tiene una `ubicacion_id` (dónde está) y un `user_id` (coordinador responsable de la dependencia)
- El historial de movimientos físicos se registra en `historial_ubicaciones_bienes`

### 3.4 Responsables (Custodios)

`bienes_responsables` registra la cadena de custodia:
- Un bien puede tener múltiples responsables a lo largo del tiempo
- El responsable actual = el registro con `fecha_retiro IS NULL`
- Al asignar nuevo responsable, se debe cerrar el registro anterior (fecha_retiro)

### 3.5 Imágenes

`bienes_imagenes` soporta galería multi-imagen por bien. La ruta almacena la ruta relativa en Laravel Storage.

### 3.6 Mantenimientos

Dos conceptos distintos:
- `mantenimientos` = catálogo de tipos (preventivo, correctivo, etc.) — FK en bien
- `mantenimientos_programados` = agenda real por bien, con fechas y estado

---

## 4. HMB — Historial de Modificaciones de Bienes

### 4.1 Objetivo

Implementar un flujo de aprobación para cambios en datos de bienes. Los usuarios con permisos de edición básica no pueden modificar directamente un bien — solo proponen cambios que quedan pendientes de aprobación por Administrador o Rector.

### 4.2 Flujo

```
Usuario propone cambio en EditarCampoBien / EditarDetalleBien
    │
    ▼
Se crea registro en historial_modificaciones_bienes
    estado = 'pendiente'
    campo = nombre del campo modificado
    valor_anterior = valor actual del bien
    valor_nuevo = valor propuesto
    tipo_objeto = 'bien' | 'detalle'
    │
    ▼
Notificación enviada a Administrador y Rector
(NotificacionHmb vía Laravel Notifications)
    │
    ▼
Administrador/Rector accede a HmbIndex
    │
    ├──── Aprobar → aplica valor_nuevo al bien.campo
    │              → si campo = 'dependencia_id': crea registro en historial_dependencias_bienes
    │              → estado = 'aprobada', aprobado_por = auth()->id()
    │              → ActivityLogger registra la acción
    │
    └──── Rechazar → estado = 'rechazada', aprobado_por = auth()->id()
                    → bien no se modifica
```

### 4.3 Estados

```
[pendiente] ──aprueba──► [aprobada] (bien modificado)
[pendiente] ──rechaza──► [rechazada] (bien sin cambios)
```

### 4.4 Tablas involucradas

- `historial_modificaciones_bienes` — registro de la propuesta
- `bienes` — se modifica solo si se aprueba
- `detalles` — se modifica si `tipo_objeto = 'detalle'`
- `historial_dependencias_bienes` — registro secundario si campo = `dependencia_id`
- `notifications` — notificación al aprobador

### 4.5 Máquina de estados

```
┌────────────┐
│  PROPUESTO │
│            │
│  campo:X   │
│  valor_new │
└─────┬──────┘
      │ Crea en DB
      ▼
┌────────────┐
│  PENDIENTE │◄──── Visible en HmbIndex
└─────┬──────┘
      │
      ├────────── Admin/Rector APRUEBA ──────►┌────────────┐
      │                                        │  APROBADA  │
      │                                        │            │
      │                                        │ bien.campo │
      │                                        │ = nuevo    │
      │                                        └────────────┘
      │
      └────────── Admin/Rector RECHAZA ──────►┌────────────┐
                                               │  RECHAZADA │
                                               │            │
                                               │ bien no    │
                                               │ cambia     │
                                               └────────────┘
```

### 4.6 Casos de uso principales

1. Auxiliar edita estado de un bien → propuesta pendiente → coordinador aprueba
2. Coordinador propone cambio de dependencia → coordinador aprueba → se registra en historial de dependencias
3. Múltiples propuestas pendientes para el mismo bien → se muestra badge de alerta en `BienesIndex`
4. Bien con `tieneModificacionesPendientes()` bloquea visualmente nuevas ediciones del mismo campo

---

## 5. HEB — Historial de Eliminaciones de Bienes

### 5.1 Objetivo

Implementar un flujo de baja controlada de bienes. La eliminación nunca es inmediata — pasa por un workflow de aprobación (excepto para Administrador/Rector que pueden eliminar directamente).

### 5.2 Flujo

```
Usuario abre modal de eliminación en BienesIndex
    │ Selecciona motivo (predefinido o libre)
    ▼
¿Tiene rol Administrador o Rector?
    │
    ├── SÍ → HistorialEliminacionBien(estado='aprobado', aprobado_por=user)
    │         $bien->delete() [soft delete]
    │         ActivityLogger registra eliminación
    │
    └── NO → Verifica que usuario pertenece a la dependencia del bien
              Verifica que no existe solicitud pendiente para este bien
              Crea HistorialEliminacionBien(estado='pendiente')
              Envía NotificacionHeb a todos los Administradores y Rectores
```

### 5.3 Estados

```
[pendiente] ──aprueba──► [aprobado] → bien.deleted_at = now()
[pendiente] ──rechaza──► [rechazado] → bien permanece activo
```

### 5.4 Aprobación en HEB

HEB no tiene componente Livewire propio — usa un controlador tradicional (`HebController`) que lista eliminaciones. La aprobación real se hace directamente en `BienesIndex::solicitarEliminacion()` para el camino del Admin.

**Brecha detectada:** No existe un componente o ruta para que el Administrador revise y apruebe/rechace solicitudes pendientes de HEB una vez creadas por otros roles. El HebController solo lista — no tiene acciones de aprobar/rechazar.

### 5.5 Máquina de estados

```
┌──────────────────┐
│ SOLICITUD CREADA │
│ (por rol básico) │
└────────┬─────────┘
         │ Notificación enviada
         ▼
┌──────────────┐
│  PENDIENTE   │◄──── Visible en HEB (lista)
└──────┬───────┘
       │
       ├──── Admin aprueba ──►┌────────────┐
       │                       │  APROBADO  │
       │                       │ deleted_at │
       │                       │ = now()    │
       │                       └────────────┘
       │
       └──── Admin rechaza ──►┌────────────┐
                               │  RECHAZADO │
                               │ bien       │
                               │ permanece  │
                               └────────────┘
```

### 5.6 Casos de uso

1. Auxiliar registra bien duplicado → solicitud de baja → Admin aprueba → bien archivado
2. Admin detecta bien fantasma → elimina directamente desde BienesIndex
3. Motivos predefinidos garantizan estandarización del proceso de bajas

---

## 6. Dashboard — Métricas

El componente `InventarioDashboard` carga todas las métricas en `mount()` mediante 8 métodos privados.

### 6.1 KPIs principales (DASH-001)

| Métrica | Fuente | Fórmula |
|---------|--------|---------|
| Total de Bienes | `bienes` | `Bien::count()` (excluye soft-deleted) |
| Dependencias | `dependencias` | `Dependencia::count()` |
| Responsables activos | `bienes_responsables` | `COUNT DISTINCT user_id WHERE fecha_retiro IS NULL` |
| Categorías | `categorias` | `Categoria::count()` |
| Bienes Activos | `bienes` | = Total Bienes (todos los no eliminados son activos) |
| Dados de Baja | `bienes` | `Bien::onlyTrashed()->count()` |
| Mantenimientos Pendientes | `mantenimientos_programados` | `WHERE estado = 'pendiente'` |
| Mantenimientos Realizados | `mantenimientos_programados` | `WHERE estado = 'realizado'` |
| Bienes en Mantenimiento | `mantenimientos_programados` | `COUNT DISTINCT bien_id WHERE estado = 'pendiente'` |

### 6.2 Estado Ejecutivo (DASH-026)

| Estado | Fórmula | Utilidad |
|--------|---------|---------|
| Bienes Activos % | `totalBienes / (totalBienes + totalBajas) * 100` | Tasa de supervivencia del inventario |
| En Mantenimiento % | `totalBienesEnMant / totalBienes * 100` | Carga de mantenimiento actual |
| Dados de Baja % | `totalBajas / (totalBienes + totalBajas) * 100` | Tasa de rotación por baja |
| Solicitudes Pendientes | `COUNT(HMB.pendiente) + COUNT(HEB.pendiente)` | Carga administrativa pendiente |

### 6.3 Gráficas (DASH-002/003/005)

| Gráfica | Consulta | Tipo visual |
|---------|----------|-------------|
| Bienes por Categoría | `GROUP BY categoria_id ORDER BY COUNT DESC` | Doughnut Chart.js |
| Bienes por Dependencia Top 10 | `GROUP BY dependencia_id LIMIT 10 ORDER BY COUNT DESC` + JOIN user | Horizontal Bar |
| Condición física | `GROUP BY estado_id ORDER BY COUNT DESC` | Doughnut |
| Origen de bienes | `JOIN origenes GROUP BY origen_id` | Doughnut |

### 6.4 Alertas (DASH-007)

| Alerta | Condición | Criticidad |
|--------|-----------|-----------|
| Mantenimientos vencidos | `estado='pendiente' AND fecha_programada < today` | Alta |
| Bienes sin responsable | `dependencia_id IS NULL AND NOT IN bienes_responsables activo` | Media |
| Bienes sin ubicación | `dependencia_id IS NULL AND NOT IN historial_ubicaciones` | Media |
| Info incompleta | `categoria_id IS NULL OR dependencia_id IS NULL OR estado_id IS NULL` | Baja |
| Solicitudes pendientes | `HMB.pendiente + HEB.pendiente` | Media |

### 6.5 Calidad de Datos (DASH-014)

Índice general = promedio de 5 métricas de completitud:

| Métrica | Fórmula | Descripción |
|---------|---------|-------------|
| Con Responsable % | `(dependencia_id NOT NULL OR en bienes_responsables) / total` | Bienes con custodio |
| Con Ubicación % | `(dependencia_id NOT NULL OR en historial_ubicaciones) / total` | Bienes ubicados |
| Con Categoría % | `categoria_id NOT NULL / total` | Bienes clasificados |
| Con Estado % | `estado_id NOT NULL / total` | Bienes con condición física |
| Con Origen % | `origen_id NOT NULL AND origen != 'Sin origen' / total` | Bienes con procedencia |

### 6.6 Bienes Estratégicos (DASH-022/023)

Clasificación por keyword en `nombre` del bien. Grupos:
Portátiles, Video Beam, Computadores, Cámaras, Impresoras, Televisores, Tablets, Switch/Red, UPS, Servidores.

Resultado: conteo + % del inventario total para cada categoría TIC.

### 6.7 Grupos Institucionales (DASH-028)

Agrupación de `categoria_id` en grupos semánticos:
- Mobiliario (IDs 1, 20)
- Equipos Tecnológicos (IDs 5, 6)
- Equipos Audiovisuales (ID 7)
- Equipos Administrativos (ID 9)
- Material Didáctico (IDs 3, 12, 13, 19)
- Instrumentos Musicales (ID 4)
- Herramientas (ID 26)

**Problema:** Los IDs de categorías están hardcodeados en el componente — frágil ante cambios en el seeder.

---

## 7. Catálogo de Componentes Livewire (22 componentes)

### 7.1 Grupo: Dashboard

**`InventarioDashboard`** (`Livewire/Dashboard/`)
- Propósito: Dashboard ejecutivo completo con KPIs, gráficas, alertas y calidad de datos
- Propiedades: 30+ propiedades públicas (totalBienes, pctActivos, chartCategorias, alertas, etc.)
- Métodos clave: cargarKpis(), cargarGraficas(), cargarAlertas(), cargarCalidadDatos(), cargarTopResponsables(), cargarBienesEstrategicos(), cargarGruposInstitucionales(), cargarEstadoEjecutivo()
- Carga: solo en `mount()` — sin polling, sin refresh automático
- Complejidad: **Alta** — 8 queries agrupadas en mount, vistas con Chart.js (Alpine.js x-data)

---

### 7.2 Grupo: Bienes

**`BienesIndex`** (`Livewire/Bienes/`)
- Propósito: Listado principal de bienes con CRUD, filtros facetados, búsqueda global, soft delete
- Propiedades: 30+ (filtros, paginación, ordenamiento, columnas visibles, catálogos, formulario de alta)
- Métodos clave: store(), duplicar(), solicitarEliminacion(), abrirModalEliminacion(), computarFacetas(), queryBienesBase(), filtrarBienesQuery()
- Query string: 10 parámetros persistidos en URL
- Permisos: `ver-bienes` (mount), `crear-bienes` (store), gestión de eliminación por rol
- Complejidad: **Muy Alta** — componente más complejo del módulo (668 líneas)

**`EditarCampoBien`** (`Livewire/Bienes/`)
- Propósito: Edición inline de un campo del bien con flujo de aprobación HMB
- Propiedades: bien (model binding), campo, valor, editando
- Lógica: Si Admin/Rector → modifica directamente. Otros → crea propuesta en HMB + notificación
- Complejidad: **Alta** — maneja múltiples tipos de campo (FK, texto, fecha, decimal)

**`EditarDetalleBien`** (`Livewire/Bienes/`)
- Propósito: Edición de los 6 campos del detalle técnico con flujo HMB
- Propiedades: bien, detalle[], camposDetalle[]
- Lógica: Crea propuesta HMB con `tipo_objeto = 'detalle'` y `valor_nuevo` = JSON
- Complejidad: **Media**

**`EditarDetalleBienModal`** (`Livewire/Bienes/`)
- Propósito: Modal alternativo para edición de detalles técnicos (admin directo, sin HMB)
- Escucha evento: `cargarDetalle($bienId)`
- Complejidad: **Baja**

---

### 7.3 Grupo: Actas

**`ActaEntregaIndex`** (`Livewire/Actas/`)
- Propósito: Generación de actas de entrega por responsable/coordinador
- Propiedades: userId, user, bienes, contenidoActa, itemsPorPagina
- Delega generación a: `ActaPrinter::renderActaPaginada()`
- Permiso: `ver-actas-de-entrega`
- Complejidad: **Media**

**`ActaPDF`** (`Livewire/Actas/`)
- Propósito: Generación y descarga de acta en formato PDF via DomPDF
- Genera: `acta_entrega_{id}.pdf`
- Complejidad: **Baja**

---

### 7.4 Grupo: HMB

**`HmbIndex`** (`Livewire/Hmb/`)
- Propósito: Listado y gestión de solicitudes de modificación de bienes (aprobación/rechazo)
- Propiedades: perPage, sortField, sortDirection
- Métodos: aprobarModificacion($id), rechazarModificacion($id)
- Escucha: `modificacionActualizada` → `$refresh`
- Permiso: `gestionar-historial-modificaciones-bienes`
- Transaccional: `DB::beginTransaction()` en aprobarModificacion()
- Complejidad: **Alta**

**`NotificacionHmb`** (`Livewire/Hmb/`)
- Propósito: Clase de notificación Laravel para alertas de HMB a administradores
- Complejidad: **Baja**

---

### 7.5 Grupo: Notificaciones

**`NotificacionesDropdown`** (`Livewire/Notifications/`)
- Propósito: Dropdown de notificaciones en el navbar con contador
- Complejidad: **Baja**

**`NotificacionesIcono`** (`Livewire/Notifications/`)
- Propósito: Ícono del campana en navbar con badge de conteo no leídas
- Complejidad: **Baja**

---

### 7.6 Grupo: Responsables

**`ResponsablesIndex`** (`Livewire/Responsables/`)
- Propósito: Gestión de custodios de bienes (asignación, retiro, historial)
- Permiso: `ver-responsables-bienes`
- Complejidad: **Media**

---

### 7.7 Grupo: Ubicaciones

**`CambiarUbicacionBien`** (`Livewire/Ubicaciones/`)
- Propósito: Formulario para registrar cambio de ubicación física de un bien
- Crea registro en `historial_ubicaciones_bienes`
- Complejidad: **Baja**

**`HistorialUbicacionesBien`** (`Livewire/Ubicaciones/`)
- Propósito: Listado del historial de ubicaciones de un bien específico
- Complejidad: **Baja**

---

### 7.8 Grupo: Catálogos (7 componentes CRUD)

Todos siguen el mismo patrón: lista con búsqueda + paginación + inline create/edit/delete.

| Componente | Tabla | Complejidad |
|------------|-------|-------------|
| `AlmacenamientosIndex` | `almacenamientos` | Baja |
| `CategoriasIndex` | `categorias` | Baja |
| `DependenciasIndex` | `dependencias` | Media (tiene user_id, ubicacion_id) |
| `EstadosIndex` | `estados` | Baja |
| `MantenimientosIndex` | `mantenimientos` | Baja |
| `OrigenesIndex` | `origenes` | Baja (tiene campo activo) |
| `UbicacionesIndex` | `ubicaciones` | Baja |

### 7.9 Grupo: Mantenimientos

**`MantenimientosProgramadosIndex`** (`Livewire/Mantenimientos/`)
- Propósito: Agenda de mantenimientos programados con filtros por estado y bien
- Permiso: `ver-mantenimientos-programados`
- Complejidad: **Media**

---

## 8. Rutas del Módulo

Todo bajo `middleware(['web', 'auth', 'app.access:inventario'])` y prefijo `/inventario`:

| Método | URI | Handler | Permiso adicional |
|--------|-----|---------|-------------------|
| GET | `/inventario` | InventarioController::dashboard | — |
| GET | `/inventario/bienes` | BienController::index | `ver-bienes` |
| GET/POST/PUT/DELETE | `/inventario/bienes/*` | BienController (resource) | — |
| GET | `/inventario/actas` | ActaController::index | `ver-actas-de-entrega` |
| GET | `/inventario/actas/{userId}/pdf` | ActaPDFController::show | `ver-actas-de-entrega` |
| GET | `/inventario/hmb` | HmbController::index | `gestionar-historial-modificaciones-bienes` |
| GET | `/inventario/heb` | HebController::index | `gestionar-historial-eliminaciones-bienes` |
| GET | `/inventario/catalogos/categorias` | CatalogosController::categorias | `ver-categorias` |
| GET | `/inventario/catalogos/dependencias` | CatalogosController::dependencias | `ver-dependencias` |
| GET | `/inventario/catalogos/ubicaciones` | CatalogosController::ubicaciones | `ver-ubicaciones` |
| GET | `/inventario/catalogos/estados` | CatalogosController::estados | `ver-estados` |
| GET | `/inventario/catalogos/origenes` | CatalogosController::origenes | `ver-origenes` |
| GET | `/inventario/catalogos/almacenamientos` | CatalogosController::almacenamientos | `ver-almacenamientos` |
| GET | `/inventario/catalogos/mantenimientos` | CatalogosController::mantenimientos | `ver-mantenimientos` |
| GET | `/inventario/responsables` | ResponsablesController::index | `ver-responsables-bienes` |
| GET | `/inventario/ubicaciones/historial` | UbicacionesHistorialController::index | `ver-historial-ubicaciones-bienes` |
| GET | `/inventario/mantenimientos/programados` | MantenimientosProgramadosController::index | `ver-mantenimientos-programados` |

---

## 9. Modelo Conceptual del Dominio

### 9.1 Mapa de Entidades

```
┌─────────────────────────────────────────────────────────────────────┐
│                      DOMINIO INVENTARIO                             │
│                                                                     │
│  CATÁLOGOS MAESTROS              BIEN (entidad central)             │
│  ┌──────────────┐                ┌─────────────────────────────┐   │
│  │ categorias   │◄───────────────│ nombre, serie, precio       │   │
│  │ estados      │◄───────────────│ fecha_adquisicion, cantidad │   │
│  │ almacenam.   │◄───────────────│ observaciones               │   │
│  │ mantenimi.   │◄───────────────│                             │   │
│  │ origenes     │◄───────────────│                             │   │
│  └──────────────┘                └──────────┬──────────────────┘   │
│                                             │                       │
│  UNIDADES ADMIN                             │ FK                    │
│  ┌──────────────┐                           │                       │
│  │ ubicaciones  │                           ▼                       │
│  │   ▲          │         ┌─────────────────────────┐              │
│  │ dependencias │◄────────│   dependencia_id         │              │
│  │  (user_id)   │         └─────────────────────────┘              │
│  └──────────────┘                                                   │
│                                                                     │
│  EXTENSIONES DEL BIEN                                               │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │ detalles (1:1)      bienes_imagenes (1:N)                    │  │
│  │ bienes_responsables (1:N)  mantenimientos_programados (1:N)  │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  HISTORIAL (auditoría inmutable)                                    │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │ historial_modificaciones_bienes  (workflow HMB)              │  │
│  │ historial_eliminaciones_bienes   (workflow HEB)              │  │
│  │ historial_dependencias_bienes    (traslados admin)           │  │
│  │ historial_ubicaciones_bienes     (movimientos físicos)       │  │
│  └──────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
```

### 9.2 Mapa de Procesos

```
REGISTRO          OPERACIÓN         CONTROL           SALIDA
    │                 │                 │                │
    ▼                 ▼                 ▼                ▼
 Crear Bien      Editar Campo       Aprobar HMB      Dashboard
    │               (HMB)              │             KPIs/Alertas
    │                 │            Aprobar HEB           │
    │             Cambiar            │                   │
    │            Ubicación        Aprobar Mant.      Acta Entrega
    │                 │            Programado            │
    │             Asignar              │                 │
    │           Responsable        Activity Log      Historial
    │                 │                               Ubicaciones
    └─────────────────┘
          Bien Activo
    ┌─────────────────────────────────────────────────────────┐
    │                    Soft Delete (HEB)                    │
    │                    Bien Archivado                       │
    └─────────────────────────────────────────────────────────┘
```

---

## 10. Recomendación para APPSisGOE

### 10.1 Conservar

**Los workflows HMB y HEB** son un hallazgo de alto valor. El patrón de "propuesta → aprobación → aplicación" para modificaciones de activos institucionales es un requerimiento de auditoría interna en IEE (Instituciones de Educación Estatal). APPSisGOE debe preservar exactamente esta arquitectura.

**La distinción bien/detalle** permite separar metadatos operativos (estado, ubicación, precio) de especificaciones técnicas (marca, color, material). Conservar como tablas separadas.

**El modelo de custodios** (`bienes_responsables` con `fecha_retiro`) implementa correctamente una cadena de custodia temporal. Conservar el patrón.

**Las métricas del dashboard** son funcionalmente correctas y cubren todos los indicadores relevantes para gestión de activos en una IEE.

### 10.2 Mejorar

**Calidad del catálogo de categorías:** los IDs están hardcodeados en `cargarGruposInstitucionales()`. APPSisGOE debe usar slugs o enums para identificar grupos, no IDs de BD.

**HEB incompleto:** No existe UI para que Admin apruebe/rechace solicitudes pendientes de HEB creadas por roles básicos. Debe implementarse un componente equivalente a HmbIndex para HEB.

**Campo `origen` legacy:** Coexiste `origen` (varchar, legacy) con `origen_id` (FK). APPSisGOE debe eliminar el campo legacy y usar solo el catálogo normalizado.

**Rendimiento de BienesIndex:** El componente ejecuta múltiples queries en cada `render()` (facetas + bienes + camposPendientes). Agregar eager loading optimizado o memoización.

**Mantenimiento programado sin tipo:** La columna `tipo` en `mantenimientos_programados` es un varchar libre. Normalizar a enum o FK a catálogo.

### 10.3 Eliminar

**`origen` (campo varchar en `bienes`):** Ya fue reemplazado por `origen_id`. Una vez migrados todos los datos, eliminar la columna y el campo del fillable.

**`mantenimiento_id` en `bienes`:** FK al catálogo de tipos de mantenimiento en el bien directamente, que no tiene semántica clara. El bien puede tener múltiples `mantenimientos_programados`. La FK directa en `bienes` es redundante.

**`ActaPrinter` como clase estática:** Es un helper con lógica de presentación. Mover a un servicio inyectable o a un Blade component parametrizado.

### 10.4 Convertir en servicio compartido

**ActivityLogger:** Ya es un servicio del módulo `ActivityLog` (`Modules\ActivityLog\Services\ActivityLogger`). APPSisGOE debe preservar este patrón pero moverlo al CORE como servicio transversal.

**Notificaciones:** El sistema de notificaciones Laravel (`notifications` table + `Notification::send()`) es compartido. No duplicar — usar el mismo mecanismo en todos los módulos APPSisGOE.

**Dashboard de calidad de datos:** El concepto de "índice de completitud de datos" es reutilizable. En APPSisGOE podría ser un componente genérico parametrizable que muestre la completitud de cualquier entidad.
