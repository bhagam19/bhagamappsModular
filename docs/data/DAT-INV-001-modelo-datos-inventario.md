# DAT-INV-001 — Modelo de Datos del Dominio Inventario

**Documento:** DAT-INV-001
**Versión:** 1.0.0
**Estado:** Aprobado — Vigente
**Fecha:** 2026-06-14
**Naturaleza:** Modelo de datos oficial. Transforma DOM-INV-001 en estructuras de persistencia.
**Autoridad:** Fuente Oficial del Modelo de Datos del Dominio Inventario. Base obligatoria para migraciones, modelos, repositorios, servicios y la implementación del módulo Inventario en APPSisGOE.
**Documentos base:** DOM-INV-001-dominio-inventario.md · ADR-004-dominio-inventario.md · ARCH-001-arquitectura-ejecutable-appsisgoe.md

---

## Tabla de Contenidos

1. [Introducción](#1-introducción)
2. [Principios de Diseño](#2-principios-de-diseño)
3. [Mapa de Entidades](#3-mapa-de-entidades)
4. [Entidades Principales](#4-entidades-principales)
5. [Relaciones](#5-relaciones)
6. [Restricciones de Integridad](#6-restricciones-de-integridad)
7. [Historiales e Inmutabilidad](#7-historiales-e-inmutabilidad)
8. [Persistencia de Eventos](#8-persistencia-de-eventos)
9. [Estados](#9-estados)
10. [Índices y Rendimiento](#10-índices-y-rendimiento)
11. [Datos Compartidos con el CORE](#11-datos-compartidos-con-el-core)
12. [Riesgos del Modelo](#12-riesgos-del-modelo)
13. [Matriz de Trazabilidad](#13-matriz-de-trazabilidad)
14. [Validación contra DOM-INV-001](#14-validación-contra-dom-inv-001)

---

## 1. Introducción

### 1.1 Propósito

Este documento define las estructuras de persistencia del Dominio Inventario de APPSisGOE. Su misión es transformar las entidades, agregados, reglas de negocio y procesos definidos en DOM-INV-001 en un modelo de datos preciso, sin pérdida de semántica y sin agregar estructuras que no tengan justificación en el dominio.

Toda decisión de diseño incluida aquí es rastreable a una regla de negocio (RN), un agregado, un proceso o un evento de DOM-INV-001. Toda estructura que no pueda justificarse de esta forma no existe en este modelo.

### 1.2 Relación con DOM-INV-001

DOM-INV-001 define **qué** gestiona el dominio y bajo qué reglas. DAT-INV-001 define **cómo** persiste esa gestión. La relación es directa:

```
DOM-INV-001                      DAT-INV-001
───────────────────────────────────────────────────────────
Agregado: Bien               →   Entidad: bienes
Agregado: Custodia           →   Entidad: custodias
Agregado: SolicitudMod.      →   Entidad: solicitudes_modificacion
Agregado: SolicitudBaja      →   Entidad: solicitudes_baja
Agregado: MantenimientoProg. →   Entidad: mantenimientos_programados
Evento: CustodioAsignado     →   INSERT en custodias
Evento: BienTrasladado       →   INSERT en historial_dependencias
RN-034: Slug estable         →   Campo slug UNIQUE en catálogos
RN-036: Inmutabilidad        →   Sin updated_at en historiales
```

### 1.3 Alcance

Este modelo define las estructuras de persistencia exclusivamente del Dominio Inventario. No define estructuras del CORE de APPSisGOE. Las tablas del CORE (users, roles, permissions, modules, notifications, activity_logs) son externas a este modelo y se referencian mediante claves foráneas hacia `users.id`.

El modelo comprende **18 entidades** organizadas en cuatro grupos:

- **Entidad raíz y extensiones:** bienes, detalles_bienes, imagenes_bienes
- **Catálogos maestros:** categorias, subcategorias, estados, origenes, almacenamientos, tipos_mantenimiento, ubicaciones, dependencias
- **Entidades operacionales:** custodias, solicitudes_modificacion, solicitudes_baja, mantenimientos_programados
- **Historiales (inmutables):** historial_ubicaciones, historial_dependencias
- **Documentación:** actas_entrega

---

## 2. Principios de Diseño

Todos los principios de esta sección son vinculantes. Ninguna decisión de implementación puede contradirlos.

---

### 2.1 Identificadores primarios — Enteros autoincrement

**Regla:** Toda tabla usa `BIGINT UNSIGNED AUTO_INCREMENT` como clave primaria en APPSisGOE v1.

**Origen:** ARCH-001 §7.1.

Los identificadores primarios son internos al sistema. No se usan como identificadores de dominio en el código de negocio.

---

### 2.2 Identificadores de dominio — Slugs

**Regla:** Toda entidad de catálogo que sea referenciada en lógica de negocio o en código (no solo como FK entre tablas) debe tener un campo `slug VARCHAR UNIQUE`.

**Formato:** kebab-case en minúsculas, sin caracteres especiales. Ejemplos: `mobiliario`, `equipos-tecnologicos`, `estado-bueno`, `compra-directa`.

**Origen:** ARCH-001 §7.2, RN-034, ADR-004 §5.3.

**Catálogos con slug obligatorio:**
`categorias`, `subcategorias`, `estados`, `origenes`, `almacenamientos`, `tipos_mantenimiento`, `ubicaciones`, `dependencias`.

**Por qué:** RN-034 define que los grupos semánticos institucionales se identifican por código estable, no por identificador interno. Si el slug de la categoría "Mobiliario" es `mobiliario`, ese identificador nunca cambia aunque se renombre la categoría o se reimporte el catálogo.

---

### 2.3 SoftDeletes — Solo en bienes

**Regla:** Solo la entidad `bienes` usa SoftDeletes (campo `deleted_at`). Los catálogos y otras entidades no usan SoftDeletes; se controlan mediante campo `activo` si son desactivables.

**Origen:** ARCH-001 §7.3, RN-003, ADR-004 §5.3.

**Por qué solo bienes:** RN-003 define que un bien dado de baja conserva toda su información histórica. La baja de un bien es un evento patrimonial irreversible que requiere SoftDeletes para cumplir la trazabilidad. Los catálogos tienen semántica diferente: pueden desactivarse pero no "darse de baja" en el sentido patrimonial.

---

### 2.4 Auditoría de tabla — campos temporales

**Regla:** Toda tabla del dominio lleva `created_at TIMESTAMP NULL`.

**Regla:** Las tablas que permiten actualizaciones llevan `updated_at TIMESTAMP NULL`.

**Regla:** Las tablas de historial (inmutables) **no** llevan `updated_at`. Solo `created_at`.

**Origen:** ARCH-001 §7.4.

Tablas **sin** `updated_at` (solo INSERT, nunca UPDATE en sus registros de historial):
`custodias`, `historial_ubicaciones`, `historial_dependencias`, `actas_entrega`.

**Nota sobre solicitudes:** `solicitudes_modificacion` y `solicitudes_baja` sí tienen `updated_at` porque su campo `estado` cambia de `pendiente` a su estado final. Esto no viola la inmutabilidad de los datos del registro (el campo, los valores, el solicitante) — solo cambia el estado y el aprobador.

---

### 2.5 Inmutabilidad de historiales

**Regla:** Las tablas de historial (`historial_ubicaciones`, `historial_dependencias`, `custodias`) admiten exclusivamente operaciones INSERT. Ningún UPDATE ni DELETE está permitido sobre estos registros.

**Origen:** RN-024, RN-028, RN-036, ADR-004 §5.6.

La inmutabilidad se garantiza en dos niveles:
1. **Aplicación:** las Actions y Repositorios del módulo no implementan métodos de actualización sobre historiales.
2. **Base de datos:** los permisos del usuario de aplicación en producción deben excluir UPDATE/DELETE sobre estas tablas.

---

### 2.6 Grupos semánticos de categorías

**Regla:** La entidad `categorias` incluye el campo `grupo_institucional` que identifica el grupo semántico de la categoría. Los valores posibles son: `mobiliario`, `tic`, `audiovisual`, `administrativo`, `didactico`, `musical`, `herramientas`, `deportivo`, `otro`.

**Origen:** RN-034, ADR-004 §5.3 (corrección de BhagamApps: grupos por slug, no por ID).

Este campo reemplaza la práctica prohibida de hardcodear IDs de categorías en el código para agrupar el dashboard. El dashboard consulta `categorias WHERE grupo_institucional = 'tic'` en lugar de `categorias WHERE id IN (5, 6)`.

---

### 2.7 Integridad referencial en cascada

**Regla general:** Los registros de historial usan `ON DELETE SET NULL` o `ON DELETE RESTRICT` hacia entidades que pueden desaparecer. Nunca `ON DELETE CASCADE` en historiales, porque el historial debe sobrevivir a la eliminación del referente.

**Excepción:** `detalles_bienes` e `imagenes_bienes` usan `ON DELETE CASCADE` desde `bienes`, porque son extensiones del bien sin valor histórico independiente.

**Origen:** RN-018 (los historiales de un bien dado de baja siguen siendo accesibles).

---

### 2.8 Restricción de modificabilidad de solicitudes

**Regla:** Una vez que una solicitud (de modificación o de baja) alcanza estado `aprobada`/`rechazada` (o `aprobado`/`rechazado`), ningún campo del registro puede volver a modificarse, incluido el estado.

**Implementación:** Esta restricción se garantiza a nivel de aplicación (Actions y Repositorios rechazan la operación si el estado es terminal). Se refuerza con la restricción de integridad RI-007 y RI-008.

---

## 3. Mapa de Entidades

```
╔══════════════════════════════════════════════════════════════════════════════════╗
║                     MODELO DE DATOS — DOMINIO INVENTARIO                         ║
╠══════════════════════════════════════════════════════════════════════════════════╣
║                                                                                  ║
║  CATÁLOGOS MAESTROS (slug único, gestionados por Aprobadores/Coordinadores)      ║
║                                                                                  ║
║  ┌─────────────┐  ┌──────────────┐  ┌──────────┐  ┌──────────────┐            ║
║  │  categorias  │  │ subcategorias │  │  estados │  │   origenes   │            ║
║  │  slug        │  │  slug        │  │  slug    │  │   slug       │            ║
║  │  grupo_inst. │  │  categoria_id│  │  nombre  │  │   nombre     │            ║
║  └──────┬───────┘  └──────┬───────┘  └──────────┘  └─────────────┘            ║
║         │                 │                                                      ║
║  ┌──────────────────┐ ┌──────────────────────┐  ┌──────────────┐              ║
║  │  almacenamientos  │ │  tipos_mantenimiento  │  │  ubicaciones │              ║
║  │  slug             │ │  slug · clasificacion │  │  slug        │              ║
║  └──────────────────┘ └──────────────────────┘  └──────┬───────┘              ║
║                                                         │                       ║
║                                              ┌─────────────────┐               ║
║                                              │  dependencias   │               ║
║                                              │  slug           │               ║
║                                              │  ubicacion_id──►│               ║
║                                              │  coordinador_id (CORE users)    ║
║                                              └────────┬────────┘               ║
║                                                       │                        ║
║  ┌──────────────────────────────────────────────────────────────────────────┐  ║
║  │                            bienes                                         │  ║
║  │  (entidad raíz · SoftDeletes · audit timestamps)                          │  ║
║  │  placa (UNIQUE) · serie · nombre · cantidad · precio · fecha_adquisicion  │  ║
║  │  categoria_id · subcategoria_id · dependencia_id · origen_id              │  ║
║  │  estado_id · almacenamiento_id · observaciones · deleted_at               │  ║
║  └────┬───────┬───────┬──────────┬────────────┬─────────────┬───────────────┘  ║
║       │       │       │          │            │             │                   ║
║       ▼       ▼       ▼          ▼            ▼             ▼                   ║
║  ┌─────────┐ ┌───────┐ ┌─────────────┐ ┌──────────┐ ┌───────────────┐        ║
║  │detalles │ │imáge- │ │  custodias  │ │solicitu- │ │solicitudes_   │        ║
║  │_bienes  │ │nes_   │ │(cadena cust)│ │des_modif.│ │baja (HEB)     │        ║
║  │(1:1)    │ │bienes │ │fecha_fin    │ │campo     │ │motivo         │        ║
║  │         │ │(1:N)  │ │IS NULL=actv │ │val_ant   │ │estado         │        ║
║  └─────────┘ └───────┘ └─────────────┘ │val_nuevo │ └───────────────┘        ║
║                                         │estado    │                           ║
║                                         └──────────┘                           ║
║                                              │ aprobación de campo dependencia  ║
║       ▼                                      ▼                                  ║
║  ┌──────────────────────┐       ┌─────────────────────────┐                   ║
║  │  historial_          │       │  historial_dependencias  │                   ║
║  │  ubicaciones         │       │  (solo INSERT, inmutable) │                  ║
║  │  (solo INSERT)       │       └─────────────────────────┘                   ║
║  └──────────────────────┘                                                      ║
║                                                                                  ║
║       ▼                              ▼                                           ║
║  ┌──────────────────────────────────────┐  ┌──────────────────────────────┐   ║
║  │  mantenimientos_programados           │  │  actas_entrega               │   ║
║  │  tipo_mantenimiento_id               │  │  custodio_id (CORE users)    │   ║
║  │  estado: pendiente/realizado/cancel. │  │  bienes_contados             │   ║
║  └──────────────────────────────────────┘  └──────────────────────────────┘   ║
║                                                                                  ║
║  ── ► FK al CORE (users.id)    ══ Entidades del dominio Inventario             ║
╚══════════════════════════════════════════════════════════════════════════════════╝
```

---

## 4. Entidades Principales

---

### 4.1 — bienes

**Propósito:** Entidad raíz del dominio. Representa un bien mueble institucional durante toda su vida en el inventario. Es el aggregado raíz en el sentido de DDD: todas las operaciones del dominio tienen a `bienes` como punto de entrada.

**Justificación de negocio:** Agregado "Bien" en DOM-INV-001 §5.1. Capacidad 1 (Gestión de Bienes). Proceso 1 (Registrar Bien). RN-001, RN-002, RN-038.

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT. Identificador interno del sistema.
- `placa` — VARCHAR(60) UNIQUE NOT NULL. Código de identificación institucional asignado por la IEE. Identificador patrimonial formal. **RN-038:** obligatorio y único.
- `serie` — VARCHAR(80) NULL. Número de serie del fabricante. Opcional (no todo bien tiene serie). Índice para búsquedas.
- `nombre` — VARCHAR(200) NOT NULL. Nombre descriptivo del bien (ej. "Portátil HP EliteBook 840").
- `descripcion` — TEXT NULL. Descripción libre complementaria del bien.
- `cantidad` — SMALLINT UNSIGNED NOT NULL DEFAULT 1. Número de unidades. La mayoría de los bienes son unidades individuales; algunos son lotes.
- `precio` — DECIMAL(14, 2) NULL. Valor de adquisición en pesos colombianos. Nullable: algunos bienes ingresados por donación no tienen precio de referencia.
- `fecha_adquisicion` — DATE NULL. Fecha en que el bien ingresó al patrimonio de la IEE. **RN-040:** si se registra, no puede ser posterior a `created_at`.
- `categoria_id` — BIGINT UNSIGNED NOT NULL. FK → `categorias.id` ON DELETE RESTRICT. **RN-001:** toda bien requiere categoría.
- `subcategoria_id` — BIGINT UNSIGNED NULL. FK → `subcategorias.id` ON DELETE SET NULL. Clasificación secundaria opcional. **RI-012:** la subcategoría debe pertenecer a la categoría del bien.
- `dependencia_id` — BIGINT UNSIGNED NULL. FK → `dependencias.id` ON DELETE SET NULL. Unidad administrativa responsable del bien. **RN-002:** un bien activo sin dependencia_id es una irregularidad (alerta del dashboard).
- `origen_id` — BIGINT UNSIGNED NULL. FK → `origenes.id` ON DELETE SET NULL. Procedencia del bien.
- `estado_id` — BIGINT UNSIGNED NOT NULL. FK → `estados.id` ON DELETE RESTRICT. Condición física del bien. **RN-039:** obligatorio.
- `almacenamiento_id` — BIGINT UNSIGNED NULL. FK → `almacenamientos.id` ON DELETE SET NULL. Tipo de almacenamiento.
- `observaciones` — VARCHAR(500) NULL. Notas libres sobre el bien.
- `deleted_at` — TIMESTAMP NULL. Campo de SoftDelete. Si tiene valor, el bien está dado de baja. **RN-003, RN-018.**
- `created_at` — TIMESTAMP NULL. Momento de registro del bien (BienRegistrado).
- `updated_at` — TIMESTAMP NULL. Última actualización de atributos directos.

**Claves y restricciones:**
- PK: `id`
- UNIQUE: `placa`
- FK: `categoria_id → categorias.id` RESTRICT
- FK: `subcategoria_id → subcategorias.id` SET NULL
- FK: `dependencia_id → dependencias.id` SET NULL
- FK: `origen_id → origenes.id` SET NULL
- FK: `estado_id → estados.id` RESTRICT
- FK: `almacenamiento_id → almacenamientos.id` SET NULL

**Campos explícitamente excluidos:**
- ~~`origen` varchar~~ — Campo legacy de BhagamApps. **Prohibido.** Solo existe `origen_id` FK. (ADR-004 §5.3)
- ~~`mantenimiento_id`~~ — FK directa al catálogo de tipos de mantenimiento en el bien. **Prohibido.** Los mantenimientos se gestionan en `mantenimientos_programados`. (ADR-004 §5.3)

---

### 4.2 — detalles_bienes

**Propósito:** Especificaciones técnicas opcionales del bien. Extensión 1:1 de `bienes` que permite registrar características físicas y técnicas sin sobrecargar la entidad principal.

**Justificación de negocio:** DOM-INV-001 §2 (Detalle técnico). ADR-004 §5.3. El detalle técnico es opcional: no todos los bienes requieren marca/color/material. El campo `tipo_objeto = 'detalle'` en las solicitudes de modificación muestra que los detalles participan del workflow HMB.

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `bien_id` — BIGINT UNSIGNED NOT NULL UNIQUE. FK → `bienes.id` ON DELETE CASCADE. UNIQUE garantiza la relación 1:1.
- `marca` — VARCHAR(100) NULL. Fabricante del bien (ej. "HP", "Yamaha", "Pelikan").
- `color` — VARCHAR(60) NULL. Color principal del bien.
- `material` — VARCHAR(100) NULL. Material de fabricación principal.
- `tamano` — VARCHAR(100) NULL. Dimensiones, medidas o talla del bien.
- `caracteristicas_especiales` — TEXT NULL. Características técnicas relevantes no cubiertas por los demás campos.
- `otras_especificaciones` — TEXT NULL. Campo libre para especificaciones adicionales.
- `created_at` — TIMESTAMP NULL.
- `updated_at` — TIMESTAMP NULL.

**Claves:**
- PK: `id`
- UNIQUE: `bien_id` (garantiza 1:1 con bienes)
- FK: `bien_id → bienes.id` CASCADE

---

### 4.3 — imagenes_bienes

**Propósito:** Galería fotográfica del bien. Permite adjuntar múltiples imágenes para documentar el estado físico, la marca, el número de serie y otras características visuales del bien.

**Justificación de negocio:** DOM-INV-001 §7.1 (Registrar Bien: "El funcionario reúne la información del bien... fotografiar los bienes para el registro visual"). Capacidad 1 (Gestión de Bienes).

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `bien_id` — BIGINT UNSIGNED NOT NULL. FK → `bienes.id` ON DELETE CASCADE.
- `ruta` — VARCHAR(500) NOT NULL. Ruta relativa en el sistema de almacenamiento de archivos (ej. `bienes/2024/img-00123.jpg`).
- `descripcion` — VARCHAR(200) NULL. Descripción del contenido de la imagen.
- `orden` — TINYINT UNSIGNED NOT NULL DEFAULT 1. Orden de presentación en la galería. La imagen con `orden = 1` es la imagen principal.
- `created_at` — TIMESTAMP NULL.
- `updated_at` — TIMESTAMP NULL.

**Claves:**
- PK: `id`
- FK: `bien_id → bienes.id` CASCADE
- INDEX: `(bien_id, orden)`

---

### 4.4 — categorias

**Propósito:** Catálogo maestro de clasificación primaria de los bienes. Permite agrupar el inventario por tipo institucional y provee los grupos semánticos para el dashboard.

**Justificación de negocio:** RN-001 (todo bien requiere categoría), RN-033 (catálogos gestionados por autoridades), RN-034 (grupos por código estable). Capacidad 8 (Gestión de Catálogos). Indicadores Operativos §10 (distribución por categoría, grupos institucionales).

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `slug` — VARCHAR(80) UNIQUE NOT NULL. Identificador semántico estable. Ejemplos: `mobiliario`, `portables`, `instrumentos-musicales`. Nunca cambia aunque se renombre la categoría. **RN-034.**
- `nombre` — VARCHAR(100) NOT NULL. Nombre visible de la categoría (ej. "Mobiliario", "Equipos TIC").
- `grupo_institucional` — VARCHAR(60) NULL. Agrupa categorías en conjuntos semánticos para el dashboard y reportes. Valores predefinidos: `mobiliario`, `tic`, `audiovisual`, `administrativo`, `didactico`, `musical`, `herramientas`, `deportivo`, `otro`. Un slug de categoría puede pertenecer a un solo grupo. **RN-034: reemplaza los IDs hardcodeados de BhagamApps.**
- `descripcion` — TEXT NULL. Descripción de qué bienes pertenecen a esta categoría.
- `activo` — TINYINT(1) NOT NULL DEFAULT 1. Si es 0, la categoría no aparece en formularios de alta de bienes. Los bienes existentes con esta categoría no se ven afectados. **RN-035:** una categoría no puede desactivarse si tiene bienes activos asociados.
- `created_at` — TIMESTAMP NULL.
- `updated_at` — TIMESTAMP NULL.

**Claves:**
- PK: `id`
- UNIQUE: `slug`
- INDEX: `grupo_institucional`

---

### 4.5 — subcategorias

**Propósito:** Clasificación secundaria de los bienes dentro de una categoría. Permite mayor granularidad en la descripción sin reemplazar la categoría.

**Justificación de negocio:** DOM-INV-001 §2 (Subcategoría: "Clasificación secundaria dentro de una categoría. Permite mayor granularidad"). Glosario Oficial.

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `slug` — VARCHAR(80) UNIQUE NOT NULL. Identificador semántico estable. Ejemplos: `portatiles`, `computadores-escritorio`, `video-beam`.
- `nombre` — VARCHAR(100) NOT NULL. Nombre visible.
- `categoria_id` — BIGINT UNSIGNED NOT NULL. FK → `categorias.id` ON DELETE RESTRICT. La subcategoría pertenece a exactamente una categoría. **RI-012:** un bien con subcategoría debe tener la misma categoría que la subcategoría.
- `descripcion` — TEXT NULL.
- `activo` — TINYINT(1) NOT NULL DEFAULT 1.
- `created_at` — TIMESTAMP NULL.
- `updated_at` — TIMESTAMP NULL.

**Claves:**
- PK: `id`
- UNIQUE: `slug`
- FK: `categoria_id → categorias.id` RESTRICT
- INDEX: `categoria_id`

---

### 4.6 — estados

**Propósito:** Catálogo de condiciones físicas de los bienes. Define los valores posibles para describir el estado de conservación.

**Justificación de negocio:** DOM-INV-001 §2 (Condición física: "Nuevo / Bueno / Regular / Malo"). RN-039. Indicadores Operativos (distribución por condición física).

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `slug` — VARCHAR(40) UNIQUE NOT NULL. Valores estándar: `nuevo`, `bueno`, `regular`, `malo`.
- `nombre` — VARCHAR(60) NOT NULL. Nombre visible: "Nuevo", "Bueno", "Regular", "Malo".
- `descripcion` — TEXT NULL. Criterios para asignar este estado (ej. "Bueno: en uso, sin deterioro significativo").
- `orden` — TINYINT UNSIGNED NOT NULL DEFAULT 1. Orden de presentación (de mejor a peor condición).
- `created_at` — TIMESTAMP NULL.
- `updated_at` — TIMESTAMP NULL.

**Claves:**
- PK: `id`
- UNIQUE: `slug`

**Nota:** Los estados estándar del dominio son exactamente cuatro. La tabla existe como catálogo para permitir que la institución actualice las descripciones o el orden de presentación sin tocar código. Los slugs `nuevo`, `bueno`, `regular`, `malo` son invariantes del dominio.

---

### 4.7 — origenes

**Propósito:** Catálogo de procedencias de los bienes. Normaliza cómo llegaron los bienes al patrimonio institucional.

**Justificación de negocio:** DOM-INV-001 §2 (Origen: "Compra directa, Donación, Transferencia de otra entidad, Comodato, Fabricación propia"). Capacidad 8.

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `slug` — VARCHAR(80) UNIQUE NOT NULL. Ejemplos: `compra-directa`, `donacion`, `transferencia`, `comodato`, `fabricacion-propia`.
- `nombre` — VARCHAR(100) NOT NULL.
- `descripcion` — TEXT NULL.
- `activo` — TINYINT(1) NOT NULL DEFAULT 1.
- `created_at` — TIMESTAMP NULL.
- `updated_at` — TIMESTAMP NULL.

**Claves:**
- PK: `id`
- UNIQUE: `slug`

---

### 4.8 — almacenamientos

**Propósito:** Catálogo de tipos de almacenamiento físico de los bienes. Describe cómo se guarda o protege el bien.

**Justificación de negocio:** DOM-INV-001 §11 (Catálogos del Dominio Inventario: "Tipos de almacenamiento"). Capacidad 8.

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `slug` — VARCHAR(80) UNIQUE NOT NULL.
- `nombre` — VARCHAR(80) NOT NULL.
- `descripcion` — TEXT NULL.
- `created_at` — TIMESTAMP NULL.
- `updated_at` — TIMESTAMP NULL.

**Claves:**
- PK: `id`
- UNIQUE: `slug`

---

### 4.9 — tipos_mantenimiento

**Propósito:** Catálogo de tipos de actividad de mantenimiento. Clasifica los mantenimientos según su propósito (preventivo o correctivo) y el tipo de tarea (revisión técnica, limpieza, calibración, etc.).

**Justificación de negocio:** DOM-INV-001 §2 (Mantenimiento: "preventivo o correctivo"), §4 Capacidad 8 (catálogos incluyen "Tipos de mantenimiento"), §11 (Catálogos del Dominio Inventario).

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `slug` — VARCHAR(80) UNIQUE NOT NULL. Ejemplos: `revision-tecnica`, `limpieza`, `calibracion`, `reparacion-menor`.
- `nombre` — VARCHAR(100) NOT NULL.
- `clasificacion` — ENUM('preventivo', 'correctivo') NOT NULL. Meta-clasificación del tipo de mantenimiento. Preventivo = planificado para evitar daños. Correctivo = respuesta a daño ya ocurrido.
- `descripcion` — TEXT NULL.
- `activo` — TINYINT(1) NOT NULL DEFAULT 1.
- `created_at` — TIMESTAMP NULL.
- `updated_at` — TIMESTAMP NULL.

**Claves:**
- PK: `id`
- UNIQUE: `slug`
- INDEX: `clasificacion`

---

### 4.10 — ubicaciones

**Propósito:** Catálogo de espacios físicos del plantel educativo donde pueden encontrarse los bienes.

**Justificación de negocio:** DOM-INV-001 §2 (Ubicación: "Espacio físico específico dentro del plantel"). Capacidad 5 (Seguimiento de Ubicaciones). Proceso 4 (Cambiar Ubicación Física). RN-027.

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `slug` — VARCHAR(80) UNIQUE NOT NULL. Ejemplos: `aula-101`, `sala-informatica-2`, `bodega-principal`, `sala-profesores`.
- `nombre` — VARCHAR(100) NOT NULL.
- `descripcion` — VARCHAR(300) NULL.
- `activo` — TINYINT(1) NOT NULL DEFAULT 1.
- `created_at` — TIMESTAMP NULL.
- `updated_at` — TIMESTAMP NULL.

**Claves:**
- PK: `id`
- UNIQUE: `slug`

---

### 4.11 — dependencias

**Propósito:** Unidades administrativas de la institución bajo cuya responsabilidad se encuentran bienes. Es el vínculo entre el organigrama institucional y el inventario.

**Justificación de negocio:** DOM-INV-001 §2 (Dependencia: "Unidad administrativa de la institución"). Capacidad 5 (Traslados). Proceso 3. RN-025, RN-026, RN-019.

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `slug` — VARCHAR(80) UNIQUE NOT NULL. Ejemplos: `rectoria`, `coordinacion-academica`, `biblioteca`, `sala-sistemas-1`.
- `nombre` — VARCHAR(100) NOT NULL.
- `ubicacion_id` — BIGINT UNSIGNED NOT NULL. FK → `ubicaciones.id` ON DELETE RESTRICT. Ubicación física principal de la dependencia.
- `coordinador_id` — BIGINT UNSIGNED NOT NULL. FK → **`users.id`** (CORE) ON DELETE RESTRICT. Funcionario responsable de la dependencia. Referencia al CORE.
- `descripcion` — VARCHAR(300) NULL.
- `activo` — TINYINT(1) NOT NULL DEFAULT 1.
- `created_at` — TIMESTAMP NULL.
- `updated_at` — TIMESTAMP NULL.

**Claves:**
- PK: `id`
- UNIQUE: `slug`
- FK: `ubicacion_id → ubicaciones.id` RESTRICT
- FK: `coordinador_id → users.id` RESTRICT (CORE)
- INDEX: `coordinador_id`

**Nota:** `coordinador_id` referencia `users.id` del CORE. No se replica la información del usuario: solo se guarda la referencia. Esta es una dependencia legítima del módulo hacia el CORE-1 (ADR-004 §6, consecuencias negativas aceptadas).

---

### 4.12 — custodias

**Propósito:** Registro completo de la cadena de custodia de cada bien. Cada fila representa un período en que un funcionario tuvo responsabilidad formal sobre el bien. La custodia activa tiene `fecha_fin IS NULL`. La cadena histórica completa es la colección de todas las filas de un bien.

**Justificación de negocio:** Agregado "Custodia" en DOM-INV-001 §5.2. RN-021, RN-022, RN-023, RN-024. Proceso 5 (Cambiar Custodio). Eventos CustodioAsignado, CustodioLiberado.

**Nombre elegido:** `custodias` (no `bienes_responsables` como en BhagamApps). Justificación: el término del dominio es "Custodia" (DOM-INV-001 §2 Glosario). El nombre debe reflejar el dominio, no la herencia tecnológica.

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `bien_id` — BIGINT UNSIGNED NOT NULL. FK → `bienes.id` ON DELETE RESTRICT. Un bien dado de baja conserva su historial de custodias.
- `custodio_id` — BIGINT UNSIGNED NULL. FK → **`users.id`** (CORE) ON DELETE SET NULL. Nullable porque si el usuario es eliminado del sistema, la custodia histórica no debe perderse (el campo queda NULL pero el registro persiste).
- `fecha_inicio` — DATE NOT NULL. Cuándo comenzó esta custodia.
- `fecha_fin` — DATE NULL. Cuándo terminó esta custodia. NULL = custodia actualmente vigente.
- `observaciones` — TEXT NULL. Notas de la asignación.
- `registrado_por_id` — BIGINT UNSIGNED NULL. FK → **`users.id`** (CORE) ON DELETE SET NULL. Quién ejecutó el cambio de custodia.
- `created_at` — TIMESTAMP NULL. Momento exacto del INSERT (sin `updated_at` — inmutable).

**Claves:**
- PK: `id`
- FK: `bien_id → bienes.id` RESTRICT
- FK: `custodio_id → users.id` SET NULL (CORE)
- FK: `registrado_por_id → users.id` SET NULL (CORE)
- INDEX: `(bien_id, fecha_fin)` — para encontrar la custodia activa: `WHERE bien_id = ? AND fecha_fin IS NULL`
- INDEX: `custodio_id` — para listar todos los bienes de un custodio

**Restricción crítica (RI-003):** No puede existir más de una fila con `fecha_fin IS NULL` para el mismo `bien_id`. Esta restricción se implementa con una combinación de:
- Índice único parcial: `UNIQUE KEY uq_custodia_activa (bien_id, fecha_fin)` no funciona directamente para NULL en MySQL. Se implementa mediante índice funcional o constraint de aplicación.
- Garantía de aplicación: `AsignarCustodioAction` cierra la custodia anterior antes de abrir la nueva, en una transacción atómica. **RN-023.**

---

### 4.13 — solicitudes_modificacion

**Propósito:** Registro del workflow HMB. Cada fila representa la intención documentada de cambiar el valor de un atributo de un bien. Guarda el campo, el valor antes y el valor propuesto, el estado de la solicitud, el solicitante y el aprobador.

**Justificación de negocio:** Agregado "Solicitud de Modificación" en DOM-INV-001 §5.3. RN-007 a RN-014. Proceso 2 (Modificar Campo). Eventos ModificacionPropuesta, ModificacionAprobada, ModificacionRechazada.

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `bien_id` — BIGINT UNSIGNED NOT NULL. FK → `bienes.id` ON DELETE RESTRICT. El bien cuyo atributo se desea modificar.
- `tipo_objeto` — ENUM('bien', 'detalle') NOT NULL DEFAULT 'bien'. Si el campo a modificar es de `bienes` o de `detalles_bienes`.
- `campo` — VARCHAR(100) NOT NULL. Nombre del campo que se quiere modificar (ej. `estado_id`, `dependencia_id`, `marca`).
- `valor_anterior` — TEXT NULL. Snapshot del valor actual del campo en el momento de la solicitud. Puede ser NULL si el campo no tenía valor.
- `valor_nuevo` — TEXT NOT NULL. Valor propuesto. Para campos de tipo FK, se almacena el ID. Para tipo 'detalle' con múltiples campos, se almacena JSON.
- `estado` — ENUM('pendiente', 'aprobada', 'rechazada') NOT NULL DEFAULT 'pendiente'.
- `solicitante_id` — BIGINT UNSIGNED NOT NULL. FK → **`users.id`** (CORE) ON DELETE RESTRICT. Quien propuso el cambio.
- `aprobador_id` — BIGINT UNSIGNED NULL. FK → **`users.id`** (CORE) ON DELETE SET NULL. Quien resolvió la solicitud. NULL si estado = 'pendiente'.
- `fecha_resolucion` — TIMESTAMP NULL. Cuándo fue resuelta. NULL si pendiente.
- `created_at` — TIMESTAMP NULL. Momento de la propuesta (ModificacionPropuesta).
- `updated_at` — TIMESTAMP NULL. Último cambio de estado.

**Claves:**
- PK: `id`
- FK: `bien_id → bienes.id` RESTRICT
- FK: `solicitante_id → users.id` RESTRICT (CORE)
- FK: `aprobador_id → users.id` SET NULL (CORE)
- INDEX: `(bien_id, campo, estado)` — para verificar RI-005: no puede haber más de una solicitud pendiente por campo del mismo bien
- INDEX: `estado` — para filtrar pendientes en HmbIndex

**Inmutabilidad parcial:** Los campos `bien_id`, `tipo_objeto`, `campo`, `valor_anterior`, `valor_nuevo` y `solicitante_id` no se modifican nunca después del INSERT. Solo `estado`, `aprobador_id` y `fecha_resolucion` cambian en la transición hacia estado terminal. Una vez en estado terminal, ningún campo cambia. **RI-007.**

---

### 4.14 — solicitudes_baja

**Propósito:** Registro del workflow HEB. Cada fila representa la intención documentada de retirar un bien del inventario activo. Guarda el motivo, el estado, el solicitante y el aprobador.

**Justificación de negocio:** Agregado "Solicitud de Baja" en DOM-INV-001 §5.4. RN-015 a RN-022. Proceso 8 (Solicitar Baja), Proceso 9 (Aprobar/Rechazar Baja). Eventos BajaSolicitada, BajaAprobada, BajaRechazada.

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `bien_id` — BIGINT UNSIGNED NOT NULL. FK → `bienes.id` ON DELETE RESTRICT.
- `motivo` — TEXT NOT NULL. Justificación de la baja. **RN-017:** obligatorio.
- `estado` — ENUM('pendiente', 'aprobado', 'rechazado') NOT NULL DEFAULT 'pendiente'.
- `solicitante_id` — BIGINT UNSIGNED NOT NULL. FK → **`users.id`** (CORE) ON DELETE RESTRICT.
- `aprobador_id` — BIGINT UNSIGNED NULL. FK → **`users.id`** (CORE) ON DELETE SET NULL. NULL si estado = 'pendiente'.
- `dependencia_id_momento` — BIGINT UNSIGNED NULL. FK → `dependencias.id` ON DELETE SET NULL. Dependencia del bien en el momento de la solicitud. Registro histórico para auditoría.
- `fecha_resolucion` — TIMESTAMP NULL. Cuándo fue resuelta.
- `created_at` — TIMESTAMP NULL.
- `updated_at` — TIMESTAMP NULL.

**Claves:**
- PK: `id`
- FK: `bien_id → bienes.id` RESTRICT
- FK: `solicitante_id → users.id` RESTRICT (CORE)
- FK: `aprobador_id → users.id` SET NULL (CORE)
- FK: `dependencia_id_momento → dependencias.id` SET NULL
- INDEX: `(bien_id, estado)` — para verificar RI-006: no puede haber más de una solicitud pendiente por bien
- INDEX: `estado`

**Inmutabilidad parcial:** Los campos `bien_id`, `motivo`, `solicitante_id` y `dependencia_id_momento` son inmutables después del INSERT. Solo `estado`, `aprobador_id` y `fecha_resolucion` cambian. **RI-008.**

---

### 4.15 — historial_ubicaciones

**Propósito:** Registro inmutable de cada cambio de ubicación física de un bien. Permite reconstruir el recorrido físico del bien dentro del plantel a lo largo del tiempo.

**Justificación de negocio:** DOM-INV-001 §2 (Historial: "Historial de ubicaciones"), RN-027, RN-028, RN-036. Proceso 4. Evento UbicacionCambiada.

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `bien_id` — BIGINT UNSIGNED NOT NULL. FK → `bienes.id` ON DELETE RESTRICT.
- `ubicacion_origen_id` — BIGINT UNSIGNED NULL. FK → `ubicaciones.id` ON DELETE SET NULL. NULL si es la primera ubicación registrada del bien.
- `ubicacion_destino_id` — BIGINT UNSIGNED NOT NULL. FK → `ubicaciones.id` ON DELETE RESTRICT.
- `registrado_por_id` — BIGINT UNSIGNED NULL. FK → **`users.id`** (CORE) ON DELETE SET NULL.
- `fecha_movimiento` — TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP. Momento del cambio de ubicación.
- `observaciones` — VARCHAR(500) NULL.
- `created_at` — TIMESTAMP NULL. (Sin `updated_at` — inmutable)

**Claves:**
- PK: `id`
- FK: `bien_id → bienes.id` RESTRICT
- FK: `ubicacion_origen_id → ubicaciones.id` SET NULL
- FK: `ubicacion_destino_id → ubicaciones.id` RESTRICT
- FK: `registrado_por_id → users.id` SET NULL (CORE)
- INDEX: `bien_id` — consultas de historial por bien
- INDEX: `fecha_movimiento` — consultas cronológicas

---

### 4.16 — historial_dependencias

**Propósito:** Registro inmutable de cada traslado de un bien entre dependencias administrativas. Se crea automáticamente cuando se aprueba una solicitud de modificación cuyo campo es `dependencia_id`.

**Justificación de negocio:** DOM-INV-001 §2 (Historial: "Historial de dependencias / traslados"), RN-025, RN-026, RN-028, RN-036. Proceso 3 (Trasladar Bien). Evento BienTrasladado.

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `bien_id` — BIGINT UNSIGNED NOT NULL. FK → `bienes.id` ON DELETE RESTRICT.
- `dependencia_origen_id` — BIGINT UNSIGNED NULL. FK → `dependencias.id` ON DELETE SET NULL. NULL si es la primera asignación de dependencia.
- `dependencia_destino_id` — BIGINT UNSIGNED NOT NULL. FK → `dependencias.id` ON DELETE RESTRICT.
- `solicitud_modificacion_id` — BIGINT UNSIGNED NULL. FK → `solicitudes_modificacion.id` ON DELETE SET NULL. Referencia a la solicitud HMB que originó el traslado. NULL si fue una modificación directa por un Aprobador.
- `registrado_por_id` — BIGINT UNSIGNED NULL. FK → **`users.id`** (CORE) ON DELETE SET NULL. Quien ejecutó el traslado (el Aprobador).
- `fecha_traslado` — TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP.
- `created_at` — TIMESTAMP NULL. (Sin `updated_at` — inmutable)

**Claves:**
- PK: `id`
- FK: `bien_id → bienes.id` RESTRICT
- FK: `dependencia_origen_id → dependencias.id` SET NULL
- FK: `dependencia_destino_id → dependencias.id` RESTRICT
- FK: `solicitud_modificacion_id → solicitudes_modificacion.id` SET NULL
- FK: `registrado_por_id → users.id` SET NULL (CORE)
- INDEX: `bien_id`

---

### 4.17 — mantenimientos_programados

**Propósito:** Agenda de mantenimientos del inventario. Cada fila representa tanto la planificación de un mantenimiento como (cuando se ejecuta) el registro de su ejecución. La misma entidad soporta el ciclo de vida completo: Pendiente → Realizado | Cancelado.

**Justificación de negocio:** Agregado "Mantenimiento Programado" en DOM-INV-001 §5.5. RN-029, RN-030, RN-031, RN-032. Procesos 6 y 7. Eventos MantenimientoProgramado, MantenimientoRealizado, MantenimientoCancelado. Indicadores de Control (mantenimientos vencidos).

**Por qué una sola entidad:** DOM-INV-001 §7.7 describe el registro de ejecución como una transición de estado del mismo registro ("El sistema actualiza el estado del mantenimiento a Realizado"). No existe un agregado separado "MantenimientoEjecutado" en el dominio. La ejecución es una transición de estado, no una entidad nueva.

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `bien_id` — BIGINT UNSIGNED NOT NULL. FK → `bienes.id` ON DELETE RESTRICT.
- `tipo_mantenimiento_id` — BIGINT UNSIGNED NOT NULL. FK → `tipos_mantenimiento.id` ON DELETE RESTRICT.
- `titulo` — VARCHAR(200) NOT NULL. Nombre descriptivo de la tarea (ej. "Limpieza anual de ventiladores").
- `descripcion` — TEXT NULL. Descripción detallada de lo que implica el mantenimiento.
- `fecha_programada` — DATE NOT NULL. Cuándo estaba planificado. **RN-030:** si `fecha_programada < hoy` y `estado = 'pendiente'`, el mantenimiento está vencido.
- `fecha_realizada` — DATE NULL. Fecha real de ejecución. NULL si pendiente o cancelado. **RN-031.**
- `estado` — ENUM('pendiente', 'realizado', 'cancelado') NOT NULL DEFAULT 'pendiente'.
- `registrado_por_id` — BIGINT UNSIGNED NULL. FK → **`users.id`** (CORE) ON DELETE SET NULL. Quien planificó el mantenimiento.
- `realizado_por_id` — BIGINT UNSIGNED NULL. FK → **`users.id`** (CORE) ON DELETE SET NULL. Quien ejecutó el mantenimiento. NULL si pendiente o cancelado.
- `observaciones` — TEXT NULL. Resultado del mantenimiento, hallazgos, recomendaciones.
- `created_at` — TIMESTAMP NULL.
- `updated_at` — TIMESTAMP NULL.

**Claves:**
- PK: `id`
- FK: `bien_id → bienes.id` RESTRICT
- FK: `tipo_mantenimiento_id → tipos_mantenimiento.id` RESTRICT
- FK: `registrado_por_id → users.id` SET NULL (CORE)
- FK: `realizado_por_id → users.id` SET NULL (CORE)
- INDEX: `(bien_id, estado)` — para listar mantenimientos por bien
- INDEX: `(estado, fecha_programada)` — para detectar vencidos: `WHERE estado = 'pendiente' AND fecha_programada < CURDATE()`

---

### 4.18 — actas_entrega

**Propósito:** Registro de generación de actas de entrega. Cada fila acredita que, en un momento específico, un acta fue generada para un custodio con un número determinado de bienes. Permite auditar cuándo y por quién fue solicitada un acta.

**Justificación de negocio:** Capacidad 7 (Generación de Actas de Entrega). Proceso 11. RN-037 (el acta refleja el estado en el momento de su generación). Evento ActaGenerada.

**Atributos:**

- `id` — Clave primaria BIGINT UNSIGNED AUTO_INCREMENT.
- `custodio_id` — BIGINT UNSIGNED NOT NULL. FK → **`users.id`** (CORE) ON DELETE RESTRICT. Funcionario para quien se generó el acta.
- `generada_por_id` — BIGINT UNSIGNED NOT NULL. FK → **`users.id`** (CORE) ON DELETE RESTRICT. Quien solicitó la generación.
- `bienes_contados` — SMALLINT UNSIGNED NOT NULL. Número de bienes incluidos en el acta en el momento de su generación.
- `ruta_documento` — VARCHAR(500) NULL. Ruta en el sistema de almacenamiento si el PDF fue guardado. NULL si solo fue generado para impresión directa.
- `created_at` — TIMESTAMP NULL. Momento exacto de generación. (Sin `updated_at` — inmutable, el acta no se regenera; se genera una nueva.)

**Claves:**
- PK: `id`
- FK: `custodio_id → users.id` RESTRICT (CORE)
- FK: `generada_por_id → users.id` RESTRICT (CORE)
- INDEX: `custodio_id` — para listar actas históricas de un custodio

---

## 5. Relaciones

Catálogo completo de relaciones entre entidades del dominio.

---

**REL-001 — bienes 1:0..1 detalles_bienes**
Un bien puede tener exactamente un detalle técnico, o ninguno.
Obligatoriedad: Opcional. No todo bien requiere especificaciones técnicas.
RN asociada: DOM-INV-001 §2 (Detalle técnico: "opcional").
Implementación: `detalles_bienes.bien_id` UNIQUE NOT NULL.

**REL-002 — bienes 1:N imagenes_bienes**
Un bien puede tener cero o más imágenes.
Obligatoriedad: Opcional.
RN asociada: Capacidad 1 (registrar bien con fotografías).

**REL-003 — bienes N:1 categorias**
Un bien pertenece a exactamente una categoría.
Obligatoriedad: Obligatorio. `bienes.categoria_id` NOT NULL.
RN asociada: RN-001.

**REL-004 — bienes N:0..1 subcategorias**
Un bien puede pertenecer a una subcategoría (o a ninguna).
Obligatoriedad: Opcional.
Restricción: La subcategoría debe pertenecer a la misma categoría del bien (RI-012).
RN asociada: DOM-INV-001 §2 (Subcategoría: "opcional").

**REL-005 — bienes N:0..1 dependencias**
Un bien activo debe estar asignado a una dependencia. Un bien sin dependencia es una irregularidad (alerta del dashboard).
Obligatoriedad: Obligatorio en la práctica, no nulo en BD para bienes bien gestionados.
RN asociada: RN-002, Indicadores de Control (bienes sin dependencia).

**REL-006 — bienes N:1 estados**
Un bien tiene siempre una condición física.
Obligatoriedad: Obligatorio. `bienes.estado_id` NOT NULL.
RN asociada: RN-039.

**REL-007 — bienes N:0..1 origenes**
Un bien puede tener registrada su procedencia.
Obligatoriedad: Opcional.
RN asociada: RN-037 (calidad de datos: bienes con origen registrado).

**REL-008 — bienes N:0..1 almacenamientos**
Un bien puede tener registrado su tipo de almacenamiento.
Obligatoriedad: Opcional.

**REL-009 — bienes 1:N custodias**
Un bien tiene múltiples registros de custodia a lo largo del tiempo. Exactamente uno tiene `fecha_fin IS NULL` (la custodia activa).
Obligatoriedad: Todo bien activo debe tener al menos una custodia activa (RI-001).
RN asociada: RN-002, RN-021, RN-022.

**REL-010 — bienes 1:N solicitudes_modificacion**
Un bien puede tener múltiples solicitudes de modificación (en distintos momentos y sobre distintos campos).
Obligatoriedad: Las solicitudes son opcionales; se crean cuando los usuarios proponen cambios.
RN asociada: RN-007, RN-010 (máximo una pendiente por campo).

**REL-011 — bienes 1:N solicitudes_baja**
Un bien puede tener múltiples solicitudes de baja a lo largo del tiempo (si fueron rechazadas y se volvió a intentar).
Obligatoriedad: Opcional.
Restricción: Solo una pendiente en cualquier momento dado (RI-006).
RN asociada: RN-016, RN-018.

**REL-012 — bienes 1:N historial_ubicaciones**
Un bien puede tener múltiples registros de cambio de ubicación física.
Obligatoriedad: Opcional (el bien puede no haber cambiado de ubicación).
RN asociada: RN-027, RN-028.

**REL-013 — bienes 1:N historial_dependencias**
Un bien puede tener múltiples registros de traslado entre dependencias.
Obligatoriedad: Opcional.
RN asociada: RN-025, RN-026.

**REL-014 — bienes 1:N mantenimientos_programados**
Un bien puede tener múltiples mantenimientos programados a lo largo del tiempo.
Obligatoriedad: Opcional.
RN asociada: RN-029.

**REL-015 — categorias 1:N subcategorias**
Una categoría puede tener múltiples subcategorías. Una subcategoría pertenece a exactamente una categoría.
Obligatoriedad: La subcategoría tiene padre obligatorio.
RN asociada: RI-012.

**REL-016 — ubicaciones 1:N dependencias**
Una ubicación puede ser la sede de múltiples dependencias. Cada dependencia tiene una ubicación.
Obligatoriedad: Obligatorio. `dependencias.ubicacion_id` NOT NULL.

**REL-017 — custodias N:1 users (CORE)**
Cada registro de custodia está asociado a un custodio (usuario del CORE).
Obligatoriedad: Nullable para resguardo histórico si el usuario es eliminado.
RN asociada: RN-021.

**REL-018 — solicitudes_modificacion N:1 solicitudes_modificacion→historial_dependencias**
Cuando se aprueba una solicitud de modificación de `dependencia_id`, se crea un registro en `historial_dependencias` que referencia esa solicitud.
RN asociada: RN-014 (registro especial en traslados).

**REL-019 — mantenimientos_programados N:1 tipos_mantenimiento**
Cada mantenimiento tiene un tipo del catálogo.
Obligatoriedad: Obligatorio. `tipo_mantenimiento_id` NOT NULL.
RN asociada: RN-029.

**REL-020 — dependencias N:1 users (CORE)**
Cada dependencia tiene un coordinador responsable.
Obligatoriedad: Obligatorio. `coordinador_id` NOT NULL.
Justificación: DOM-INV-001 §2 (Dependencia: "tiene un coordinador responsable").

---

## 6. Restricciones de Integridad

Catálogo completo de restricciones de integridad del modelo. Toda RI es rastreable a una RN del dominio.

---

**RI-001 — Custodia activa obligatoria en bien activo**
Todo bien con `deleted_at IS NULL` debe tener exactamente un registro en `custodias` con `bien_id = id AND fecha_fin IS NULL`.
Origen: RN-002.
Implementación: Garantizada por `AsignarCustodioAction` en Proceso 1 (asigna primer custodio al registrar) y Proceso 5 (asigna nuevo antes de cerrar anterior).

**RI-002 — Bien dado de baja sin custodia activa**
Un bien con `deleted_at IS NOT NULL` no puede tener custodias con `fecha_fin IS NULL`.
Origen: RN-004.
Implementación: `DarDeBajaAction` cierra la custodia activa antes de ejecutar el soft delete.

**RI-003 — Máximo una custodia activa por bien**
No puede existir más de un registro en `custodias` con el mismo `bien_id` y `fecha_fin IS NULL`.
Origen: RN-021.
Implementación: Constraint de aplicación + índice único funcional en `(bien_id)` WHERE `fecha_fin IS NULL`.

**RI-004 — Fechas coherentes en custodia**
Si `custodias.fecha_fin IS NOT NULL`, entonces `fecha_fin >= fecha_inicio`.
Origen: Coherencia temporal del dominio.
Implementación: Validación en `CerrarCustodiaAction`.

**RI-005 — Máximo una solicitud de modificación pendiente por campo del mismo bien**
No puede existir más de un registro en `solicitudes_modificacion` con el mismo `(bien_id, campo)` y `estado = 'pendiente'`.
Origen: RN-010.
Implementación: Verificación previa en `ProponerModificacionAction` + índice único parcial `(bien_id, campo)` WHERE `estado = 'pendiente'`.

**RI-006 — Máximo una solicitud de baja pendiente por bien**
No puede existir más de un registro en `solicitudes_baja` con el mismo `bien_id` y `estado = 'pendiente'`.
Origen: RN-018.
Implementación: Verificación previa en `SolicitarBajaAction` + índice único parcial.

**RI-007 — Estado terminal de solicitud de modificación es inmutable**
Un registro en `solicitudes_modificacion` con `estado IN ('aprobada', 'rechazada')` no puede cambiar a ningún otro estado.
Origen: RN-013, §2.8.
Implementación: `AprobarModificacionAction` y `RechazarModificacionAction` verifican que `estado = 'pendiente'` antes de proceder. Cualquier otra operación de actualización sobre una solicitud resuelta es rechazada.

**RI-008 — Estado terminal de solicitud de baja es inmutable**
Un registro en `solicitudes_baja` con `estado IN ('aprobado', 'rechazado')` no puede cambiar.
Origen: RN-022, §2.8.
Implementación: Similar a RI-007.

**RI-009 — Aprobador obligatorio en estado terminal**
Si `solicitudes_modificacion.estado IN ('aprobada', 'rechazada')`, entonces `aprobador_id IS NOT NULL`.
Si `solicitudes_baja.estado IN ('aprobado', 'rechazado')`, entonces `aprobador_id IS NOT NULL`.
Origen: DOM-INV-001 §5.3 y §5.4 (invariantes de los agregados).
Implementación: Validación antes de cambiar el estado en las Actions.

**RI-010 — No modificar bien dado de baja**
Un bien con `deleted_at IS NOT NULL` no puede ser el destino de: nuevas custodias, nuevas solicitudes de modificación, nuevas solicitudes de baja, nuevos mantenimientos programados.
Origen: RN-004, RN-005, RN-006.
Implementación: Verificación al inicio de todas las Actions que operan sobre bienes: `abort_if($bien->trashed(), ...)`.

**RI-011 — Coherencia de subcategoría con categoría**
Si `bienes.subcategoria_id IS NOT NULL`, entonces `subcategorias.categoria_id = bienes.categoria_id`.
Origen: RN-001, DOM-INV-001 §2 (Subcategoría: "dentro de una categoría").
Implementación: Validación en `RegistrarBienAction` y en toda Action que modifique `subcategoria_id` o `categoria_id`.

**RI-012 — Categoría no eliminable con bienes activos**
No se puede eliminar ni desactivar una `categoria` si existen `bienes` activos con `categoria_id = id`.
Origen: RN-035.
Implementación: Verificación en `DesactivarCategoriaAction`.

**RI-013 — Placa única incluyendo bienes dados de baja**
`bienes.placa` es UNIQUE en todos los registros, incluyendo los soft-deleted.
Origen: RN-038 (la placa es un identificador patrimonial institucional permanente).
Implementación: Constraint UNIQUE en la columna. Las consultas que excluyen soft-deleted deben usar UNIQUE sobre todos los registros.

**RI-014 — Historiales no actualizables**
Las tablas `custodias`, `historial_ubicaciones` y `historial_dependencias` no admiten UPDATE ni DELETE.
Origen: RN-024, RN-028, RN-036.
Implementación: Los repositorios de estas entidades solo implementan métodos de INSERT y SELECT. Los permisos de base de datos en producción excluyen UPDATE/DELETE sobre estas tablas.

**RI-015 — Inmutabilidad de datos de solicitudes**
Los campos `bien_id`, `campo`, `valor_anterior`, `valor_nuevo`, `solicitante_id` de `solicitudes_modificacion` nunca se modifican después del INSERT.
Los campos `bien_id`, `motivo`, `solicitante_id` de `solicitudes_baja` nunca se modifican.
Origen: RN-009, RN-013, RN-017, RN-022.
Implementación: Los repositorios no exponen métodos de actualización para estos campos.

**RI-016 — historial_dependencias creado solo por aprobación de HMB sobre campo dependencia_id**
Los registros en `historial_dependencias` solo se crean cuando `AprobarModificacionAction` procesa una solicitud con `campo = 'dependencia_id'` (o modificación directa de dependencia por Aprobador).
Origen: RN-014 (traslado automático en aprobación de cambio de dependencia).
Implementación: Única ruta de creación en `AprobarModificacionAction`.

**RI-017 — Mantenimiento solo sobre bien activo**
No se puede crear un `mantenimiento_programado` para un bien con `deleted_at IS NOT NULL`.
Origen: RN-006.
Implementación: Verificación en `ProgramarMantenimientoAction`.

**RI-018 — fecha_adquisicion no puede ser futura**
`bienes.fecha_adquisicion`, si se registra, debe ser `<= created_at` del bien.
Origen: RN-040.
Implementación: Validación en `RegistrarBienAction`.

---

## 7. Historiales e Inmutabilidad

Los historiales son el corazón de la trazabilidad patrimonial. Este capítulo define la estrategia oficial para cada uno.

---

### 7.1 Historial de modificaciones — solicitudes_modificacion

**Qué registra:** Toda propuesta de cambio sobre los atributos de un bien: el campo a modificar, el valor antes del cambio, el valor propuesto, el estado de la solicitud, el solicitante y el resolutor.

**Cuándo se registra:** Al crear una solicitud de modificación (RN-007, RN-008). En el mismo INSERT se captura el valor actual del campo como `valor_anterior` (snapshot inmutable).

**Quién lo registra:** El sistema al procesar `ProponerModificacionAction` (para roles básicos) o `ModificarCampoDirectoAction` (para Aprobadores). En el caso de modificación directa por Aprobadores, igualmente se crea un registro con estado `aprobada` directamente.

**Qué NO se registra:** El motivo del rechazo (extensión futura si se requiere). Las modificaciones del CORE (cambios de rol, permisos) que no son bienes del inventario.

**Estrategia de inmutabilidad:** Los campos de datos (`campo`, `valor_anterior`, `valor_nuevo`, `bien_id`, `solicitante_id`) son inmutables desde el INSERT. Solo cambian los campos de estado (`estado`, `aprobador_id`, `fecha_resolucion`) y solo una vez, hacia un estado terminal.

**Relación con `historial_dependencias`:** Cuando el campo modificado es `dependencia_id` y la solicitud es aprobada, la aprobación crea adicionalmente un registro en `historial_dependencias`. Ambos registros son atómicos (en la misma transacción).

---

### 7.2 Historial de ubicaciones — historial_ubicaciones

**Qué registra:** Cada cambio de ubicación física de un bien: la ubicación de origen, la de destino, quién lo registró, cuándo.

**Cuándo se registra:** Al ejecutar el Proceso 4 (Cambiar Ubicación Física). El registro se crea en el momento en que el funcionario notifica el movimiento.

**Quién lo registra:** El sistema al procesar `CambiarUbicacionAction`. El campo `registrado_por_id` identifica al funcionario que reportó el movimiento.

**Por qué es inmutable:** El historial de ubicaciones físicas es un registro de hechos pasados. Un bien estuvo en un lugar en un período dado. Ese hecho no puede "deshacerse" modificando el registro.

**Primera ubicación:** Si un bien no tiene registros previos en `historial_ubicaciones`, `ubicacion_origen_id` es NULL en el primer registro.

---

### 7.3 Historial de custodias — custodias

**Qué registra:** Cada período de custodia de un bien. La tabla `custodias` es simultáneamente el estado actual (fila con `fecha_fin IS NULL`) y el historial completo (todas las filas).

**Cuándo se registra:** Al asignar un custodio al bien por primera vez (Proceso 1) y al cambiar de custodio (Proceso 5). En cada cambio se crea UN NUEVO registro (el nuevo custodio) y se actualiza la `fecha_fin` del registro anterior (el custodio que sale). **Esta es la única actualización permitida en `custodias`:** cerrar un registro activo.

**Nota sobre `updated_at`:** `custodias` no tiene `updated_at`. La actualización de `fecha_fin` en el cierre de la custodia es la única modificación permitida. Sin embargo, como principio de diseño limpio, se puede implementar con una columna `cerrada_en TIMESTAMP NULL` que se establece al cerrar, en lugar de modificar el `fecha_fin` registrado. En este modelo v1, se usa `fecha_fin` como campo mutable exclusivamente para este propósito.

**Transaccionalidad obligatoria:** El cierre de la custodia anterior y la apertura de la nueva deben ocurrir en la misma transacción de base de datos. **RN-023, RI-003.**

---

### 7.4 Historial de bajas — solicitudes_baja

**Qué registra:** Toda solicitud formal de dar de baja un bien: el motivo, el solicitante, el estado, el resolutor, la dependencia del bien en el momento de la solicitud.

**Cuándo se registra:** Al crear una solicitud de baja (rol básico) o al ejecutar una baja directa (Aprobador). En el caso de la baja directa, se crea un registro con estado `aprobado` directamente.

**Por qué se conservan las solicitudes rechazadas:** Una solicitud de baja rechazada es evidencia de que hubo una intención de dar de baja el bien y que la autoridad competente la rechazó. Esto es relevante para la auditoría patrimonial.

**Relación con el bien:** Cuando `solicitudes_baja.estado = 'aprobado'`, el bien correspondiente tiene `deleted_at IS NOT NULL`. Ambos registros son atómicos (en la misma transacción). La integridad referencial no se establece mediante FK directa entre estas dos condiciones, sino mediante la atomicidad de `AprobarBajaAction`.

---

## 8. Persistencia de Eventos

Mapeo completo de los eventos de dominio (DOM-INV-001 §9) a sus entidades de persistencia.

---

**BienRegistrado**
↓ Entidades afectadas: bienes (INSERT), custodias (INSERT — primer custodio)
↓ Opcionalmente: detalles_bienes (INSERT si hay datos técnicos), imagenes_bienes (INSERT si hay imágenes)
↓ Persistencia: Transacción que incluye INSERT en bienes + INSERT en custodias

**BienModificado** (modificación directa por Aprobador)
↓ Entidades afectadas: bienes (UPDATE de un campo), solicitudes_modificacion (INSERT con estado='aprobada')
↓ Si campo = dependencia_id: historial_dependencias (INSERT)
↓ Persistencia: Transacción atómica

**ModificacionPropuesta**
↓ Entidades afectadas: solicitudes_modificacion (INSERT con estado='pendiente')
↓ Persistencia: INSERT en solicitudes_modificacion con captura de valor_anterior

**ModificacionAprobada**
↓ Entidades afectadas: solicitudes_modificacion (UPDATE estado→'aprobada', aprobador_id, fecha_resolucion), bienes (UPDATE campo)
↓ Si campo = dependencia_id: historial_dependencias (INSERT)
↓ Persistencia: Transacción atómica

**ModificacionRechazada**
↓ Entidades afectadas: solicitudes_modificacion (UPDATE estado→'rechazada', aprobador_id, fecha_resolucion)
↓ bienes: sin cambios
↓ Persistencia: UPDATE en solicitudes_modificacion

**CustodioAsignado**
↓ Entidades afectadas: custodias (INSERT nuevo registro con fecha_fin=NULL)
↓ Persistencia: INSERT en custodias (parte de la transacción de CambiarCustodio)

**CustodioLiberado**
↓ Entidades afectadas: custodias (UPDATE fecha_fin en el registro activo anterior)
↓ Persistencia: UPDATE en custodias (parte de la transacción de CambiarCustodio, junto con CustodioAsignado)

**BienTrasladado** (traslado entre dependencias)
↓ Entidades afectadas: historial_dependencias (INSERT), bienes (campo dependencia_id ya actualizado por ModificacionAprobada)
↓ Persistencia: INSERT en historial_dependencias dentro de la transacción de AprobarModificacionAction

**UbicacionCambiada**
↓ Entidades afectadas: historial_ubicaciones (INSERT)
↓ Persistencia: INSERT en historial_ubicaciones

**MantenimientoProgramado**
↓ Entidades afectadas: mantenimientos_programados (INSERT con estado='pendiente')
↓ Persistencia: INSERT en mantenimientos_programados

**MantenimientoRealizado**
↓ Entidades afectadas: mantenimientos_programados (UPDATE estado→'realizado', fecha_realizada, observaciones, realizado_por_id)
↓ Persistencia: UPDATE en mantenimientos_programados

**MantenimientoCancelado**
↓ Entidades afectadas: mantenimientos_programados (UPDATE estado→'cancelado')
↓ Persistencia: UPDATE en mantenimientos_programados

**BajaSolicitada** (por rol básico)
↓ Entidades afectadas: solicitudes_baja (INSERT con estado='pendiente')
↓ Persistencia: INSERT en solicitudes_baja

**BajaAprobada** (directa por Aprobador)
↓ Entidades afectadas: bienes (UPDATE deleted_at=now()), custodias (UPDATE fecha_fin en activa), solicitudes_baja (INSERT con estado='aprobado' directamente)
↓ Persistencia: Transacción atómica

**BajaAprobada** (desde solicitud pendiente)
↓ Entidades afectadas: solicitudes_baja (UPDATE estado→'aprobado', aprobador_id), bienes (UPDATE deleted_at=now()), custodias (UPDATE fecha_fin en activa)
↓ Persistencia: Transacción atómica

**BajaRechazada**
↓ Entidades afectadas: solicitudes_baja (UPDATE estado→'rechazado', aprobador_id, fecha_resolucion)
↓ bienes: sin cambios
↓ Persistencia: UPDATE en solicitudes_baja

**ActaGenerada**
↓ Entidades afectadas: actas_entrega (INSERT con custodio_id, bienes_contados, ruta si se guarda PDF)
↓ Persistencia: INSERT en actas_entrega

---

## 9. Estados

### 9.1 Estado del Bien

La condición de activo/baja se persiste en `bienes.deleted_at`.

| Estado | Condición en BD | Transición origen | Transición destino |
|--------|----------------|------------------|-------------------|
| Activo | `deleted_at IS NULL` | Alta (Proceso 1) | Dado de baja |
| Dado de baja | `deleted_at IS NOT NULL` | BajaAprobada | — (terminal) |

**Transiciones prohibidas:**
- Dado de baja → Activo: No existe proceso de reactivación en APPSisGOE v1.
- Un bien puede ser consultado estando dado de baja, pero no puede recibir ninguna operación de modificación.

---

### 9.2 Estado de Solicitudes de Modificación (HMB)

Persiste en `solicitudes_modificacion.estado`.

| Estado | Descripción | Transiciones válidas |
|--------|-------------|---------------------|
| pendiente | Solicitud creada, esperando resolución | → aprobada, → rechazada |
| aprobada | Aprobada, cambio aplicado al bien | — (terminal) |
| rechazada | Rechazada, bien sin cambios | — (terminal) |

**Transiciones prohibidas:**
- aprobada → pendiente
- rechazada → pendiente
- aprobada → rechazada
- rechazada → aprobada

**Precondición para aprobada:** `aprobador_id IS NOT NULL` y bien `deleted_at IS NULL`.
**Precondición para rechazada:** `aprobador_id IS NOT NULL`.

---

### 9.3 Estado de Solicitudes de Baja (HEB)

Persiste en `solicitudes_baja.estado`.

| Estado | Descripción | Transiciones válidas |
|--------|-------------|---------------------|
| pendiente | Solicitud creada, bien sigue activo | → aprobado, → rechazado |
| aprobado | Aprobado, bien dado de baja | — (terminal) |
| rechazado | Rechazado, bien continúa activo | — (terminal) |

**Transiciones prohibidas:** Mismas restricciones que HMB (ningún estado terminal puede retroceder).

**Condición de atomicidad:** La transición pendiente → aprobado incluye en la misma transacción: `solicitudes_baja.estado = 'aprobado'` + `bienes.deleted_at = now()` + `custodias.fecha_fin = today()` (cierre de custodia activa).

---

### 9.4 Estado de Mantenimientos Programados

Persiste en `mantenimientos_programados.estado`.

| Estado | Descripción | Transiciones válidas |
|--------|-------------|---------------------|
| pendiente | Planificado, pendiente de ejecución | → realizado, → cancelado |
| realizado | Ejecutado, fecha_realizada IS NOT NULL | — (terminal) |
| cancelado | No se ejecutará | — (terminal) |

**Condición de vencimiento:** `estado = 'pendiente' AND fecha_programada < CURDATE()`. Este no es un estado en BD, sino una condición calculada en el dashboard. **RN-030.**

**Transiciones prohibidas:**
- realizado → pendiente
- cancelado → pendiente
- Un mantenimiento no puede eliminarse. **RN-032.**

---

## 10. Índices y Rendimiento

### 10.1 Índices obligatorios

Índices requeridos para las consultas críticas del módulo:

**bienes:**
- `UNIQUE (placa)` — búsqueda por placa institucional. RI-013.
- `INDEX (serie)` — búsqueda por número de serie.
- `INDEX (categoria_id)` — filtro por categoría en BienesIndex y dashboard.
- `INDEX (dependencia_id)` — filtro por dependencia en BienesIndex y dashboard.
- `INDEX (estado_id)` — filtro por condición física.
- `INDEX (deleted_at)` — separación de activos vs. dados de baja en todas las consultas.
- `INDEX (nombre)` — búsqueda textual (preferiblemente FULLTEXT si el motor lo soporta).

**custodias:**
- `INDEX (bien_id, fecha_fin)` — consulta de custodia activa: `WHERE bien_id = ? AND fecha_fin IS NULL`.
- `INDEX (custodio_id)` — listar todos los bienes de un custodio.

**solicitudes_modificacion:**
- `INDEX (bien_id, campo, estado)` — verificar RI-005 y cargar modificaciones pendientes por bien.
- `INDEX (estado)` — filtrar pendientes en HmbIndex.
- `INDEX (solicitante_id)` — historial de solicitudes por usuario.

**solicitudes_baja:**
- `INDEX (bien_id, estado)` — verificar RI-006.
- `INDEX (estado)` — filtrar pendientes en HebIndex.

**mantenimientos_programados:**
- `INDEX (bien_id)` — listar mantenimientos de un bien.
- `INDEX (estado, fecha_programada)` — detectar vencidos: `WHERE estado = 'pendiente' AND fecha_programada < CURDATE()`.

**historial_ubicaciones:**
- `INDEX (bien_id)` — historial de ubicaciones de un bien.

**historial_dependencias:**
- `INDEX (bien_id)` — historial de traslados de un bien.

**categorias:**
- `UNIQUE (slug)`
- `INDEX (grupo_institucional)` — agrupación del dashboard por grupo semántico.

Todos los catálogos maestros: `UNIQUE (slug)`.

---

### 10.2 Búsquedas frecuentes identificadas

- **BienesIndex con filtros facetados:** filtro simultáneo por categoría, dependencia, estado, origen, con paginación y búsqueda textual por nombre/serie/placa. Requiere todos los índices de `bienes` más eager loading de relaciones.
- **Custodia activa de un bien:** `WHERE bien_id = X AND fecha_fin IS NULL`. Crítico para visualización del bien y generación de actas.
- **HmbIndex — pendientes:** `WHERE estado = 'pendiente' ORDER BY created_at ASC`. Paginado.
- **HebIndex — pendientes:** `WHERE estado = 'pendiente' ORDER BY created_at ASC`. Paginado.
- **Dashboard — mantenimientos vencidos:** `WHERE estado = 'pendiente' AND fecha_programada < CURDATE()`.
- **Dashboard — bienes por grupo institucional:** `JOIN categorias WHERE grupo_institucional = 'tic'`.
- **Dashboard — bienes sin custodio:** `LEFT JOIN custodias WHERE custodias.fecha_fin IS NULL AND custodias.id IS NULL`.
- **Generación de acta:** `INNER JOIN custodias WHERE custodio_id = X AND fecha_fin IS NULL` para obtener todos los bienes del custodio.

---

### 10.3 Consultas críticas con impacto en diseño

**Calidad de datos (índice de completitud):** El dashboard calcula porcentajes de campos completos. Estas son consultas de agregación sobre la tabla `bienes` completa. Para rendimiento en inventarios grandes, considerar una vista materializada o tabla de caché de métricas actualizada mediante eventos.

**Bienes estratégicos TIC:** El filtro por palabras clave en `bienes.nombre` es una búsqueda textual libre. FULLTEXT index sobre `nombre` es preferible a LIKE '%portátil%' para tablas medianas/grandes.

---

## 11. Datos Compartidos con el CORE

El Dominio Inventario es un consumidor de servicios del CORE. No replica datos del CORE; los referencia.

---

### 11.1 CORE Users (CORE-1)

**Qué referencia el dominio:** `users.id` en los siguientes campos:
- `custodias.custodio_id` — identidad del custodio
- `custodias.registrado_por_id` — quién asignó la custodia
- `dependencias.coordinador_id` — coordinador responsable de la dependencia
- `solicitudes_modificacion.solicitante_id` / `aprobador_id`
- `solicitudes_baja.solicitante_id` / `aprobador_id`
- `historial_ubicaciones.registrado_por_id`
- `historial_dependencias.registrado_por_id`
- `mantenimientos_programados.registrado_por_id` / `realizado_por_id`
- `actas_entrega.custodio_id` / `generada_por_id`

**Qué NO se replica:** El Dominio Inventario no almacena nombre, cédula, correo ni rol del usuario. Si se necesita mostrar el nombre del custodio en una vista, se obtiene mediante JOIN con `users`. Los roles y permisos son gestionados exclusivamente por el CORE-2.

**Impacto de eliminación de usuario:** Todos los FK hacia `users.id` en historiales e información operacional usan `ON DELETE SET NULL`. Esto garantiza que si un usuario es eliminado del sistema, el historial patrimonial del dominio permanece intacto (el `custodio_id` queda NULL, pero el registro de custodia existe). Los FK con `ON DELETE RESTRICT` (solicitante_id en solicitudes) previenen eliminar un usuario que tiene solicitudes activas.

---

### 11.2 CORE Audit (CORE-4)

**Qué usa el dominio:** `ActivityLogger::log()` — el servicio de registro transversal de actividad. El Dominio Inventario llama a este servicio en todas sus Actions significativas, pero no posee la tabla `activity_logs`.

**Qué aporta el dominio:** Sus propios historiales de dominio (`custodias`, `historial_ubicaciones`, `historial_dependencias`, `solicitudes_modificacion`, `solicitudes_baja`) son historiales de dominio específicos, complementarios al log transversal del CORE. Ambos coexisten:
- `activity_logs` (CORE): responde quién hizo qué acción en el sistema.
- Historiales de dominio: responden qué le ha ocurrido a un bien específico, con semántica patrimonial.

---

### 11.3 CORE Notifications (CORE-5)

**Qué usa el dominio:** La tabla `notifications` del CORE y el mecanismo `Notification::send()`. El Dominio Inventario emite notificaciones en los siguientes eventos:
- `ModificacionPropuesta` → notifica a Aprobadores
- `BajaSolicitada` → notifica a Aprobadores
- `MantenimientoVencido` → notifica a Coordinadores y Aprobadores (si se implementa listener de mantenimientos vencidos)

**Qué NO posee el dominio:** La tabla `notifications` pertenece al CORE. El componente visual (dropdown de notificaciones) pertenece al CORE. El Dominio Inventario solo genera los eventos.

---

### 11.4 CORE Modules (CORE-3)

**Qué usa el dominio:** El middleware `modulo.access:inventario` es gestionado por el CORE-3. El Dominio Inventario declara sus metadatos en `module.json` pero la lógica de visibilidad y acceso pertenece al CORE.

**Qué NO posee el dominio:** No tiene FK hacia la tabla `modules` del CORE. La gobernanza de módulos es responsabilidad del CORE. (ARCH-RULE-018)

---

## 12. Riesgos del Modelo

---

**RDM-001 — Custodia activa sin constraint de base de datos**
La restricción RI-003 (máximo una custodia activa por bien) no puede implementarse directamente como UNIQUE KEY en MySQL porque `UNIQUE (bien_id, fecha_fin)` trata múltiples NULLs como distintos. El constraint es garantizado solo por la aplicación.
Impacto: Alto. Si existe un bug en `AsignarCustodioAction`, dos custodias podrían quedar activas.
Mitigación: Test de invariante en suite CI (`SELECT bien_id, COUNT(*) FROM custodias WHERE fecha_fin IS NULL GROUP BY bien_id HAVING COUNT(*) > 1 → debe retornar 0 filas`). Índice funcional si el motor lo soporta (MySQL 8.0+: índice generado sobre IF(fecha_fin IS NULL, bien_id, NULL)).

**RDM-002 — Crecimiento de historial_ubicaciones y custodias**
Los historiales son inmutables y acumulativos. En una institución con muchos bienes y traslados frecuentes, estas tablas pueden crecer significativamente.
Impacto: Medio (rendimiento a largo plazo).
Mitigación: Los índices sobre `bien_id` garantizan que las consultas por bien sean eficientes independientemente del volumen total. Particionamiento por año si el volumen supera los 10M de registros.

**RDM-003 — Referencia de custodio_id a usuario eliminado**
Si un usuario es eliminado del CORE y tenía custodias activas, el campo `custodio_id` queda NULL. El bien queda sin custodio (RI-001 violada).
Impacto: Medio.
Mitigación: `ON DELETE RESTRICT` en `custodias.custodio_id` para custodias activas. Antes de eliminar un usuario, se debe verificar que no tiene custodias activas.

**RDM-004 — Solicitudes pendientes en bien dado de baja**
Si se ejecuta una baja directa mientras existe una solicitud de modificación pendiente para el mismo bien, la solicitud queda "huérfana" (el bien está dado de baja pero la solicitud sigue en pendiente).
Impacto: Bajo (no afecta integridad patrimonial, solo genera ruido en HmbIndex).
Mitigación: `DarDeBajaAction` cancela automáticamente (o ignora) las solicitudes pendientes del bien al ejecutar la baja. Alternativa: dejarlas en pendiente y que el HmbIndex las muestre como inoperables.

**RDM-005 — Campo valor_anterior captura snapshot incorrecto**
Si `valor_anterior` en `solicitudes_modificacion` se captura incorrectamente (diferente del valor real del campo en ese momento), el historial de modificaciones no refleja la realidad.
Impacto: Alto (trazabilidad incorrecta).
Mitigación: El valor se captura dentro de la transacción, inmediatamente antes del INSERT, leyendo directamente del registro del bien. No se acepta el valor enviado por el cliente; se lee desde la base de datos.

**RDM-006 — grupo_institucional hardcodeado como ENUM en aplicación**
Si los valores de `grupo_institucional` son constantes en el código pero no en la BD, un nuevo grupo institucional requiere despliegue.
Impacto: Bajo.
Mitigación: El campo `grupo_institucional` en `categorias` es VARCHAR, no ENUM. Los valores son gestionados por la institución. El código usa constantes named (equivalente al enum `Capacidad` del CORE) para referenciar los valores, pero no los restringe a nivel de BD, permitiendo extensión sin migración de columna.

---

## 13. Matriz de Trazabilidad

Toda entidad del modelo es trazable a un elemento del dominio (DOM-INV-001).

| Entidad | Agregado DOM | Proceso DOM | Evento DOM | Reglas de negocio |
|---------|-------------|-------------|-----------|------------------|
| bienes | Bien | P1, P2, P3, P4, P5 | BienRegistrado, BienModificado | RN-001, RN-002, RN-003, RN-005, RN-038, RN-039, RN-040 |
| detalles_bienes | Bien (extensión) | P1 | BienRegistrado | DOM §2 (Detalle técnico) |
| imagenes_bienes | Bien (extensión) | P1 | BienRegistrado | Capacidad 1 |
| categorias | — (catálogo) | — | — | RN-001, RN-033, RN-034, RN-035 |
| subcategorias | — (catálogo) | — | — | DOM §2 (Subcategoría) |
| estados | — (catálogo) | — | — | RN-039 |
| origenes | — (catálogo) | — | — | DOM §2 (Origen) |
| almacenamientos | — (catálogo) | — | — | Capacidad 8 |
| tipos_mantenimiento | — (catálogo) | — | — | RN-029, Capacidad 8 |
| ubicaciones | — (catálogo) | P4 | UbicacionCambiada | RN-027 |
| dependencias | — (catálogo) | P3 | BienTrasladado | RN-025, DOM §2 (Dependencia) |
| custodias | Custodia | P1, P5 | CustodioAsignado, CustodioLiberado | RN-021, RN-022, RN-023, RN-024 |
| solicitudes_modificacion | SolicitudModificacion | P2 | ModificacionPropuesta, ModificacionAprobada, ModificacionRechazada | RN-007 a RN-014 |
| solicitudes_baja | SolicitudBaja | P8, P9 | BajaSolicitada, BajaAprobada, BajaRechazada | RN-015 a RN-022 |
| historial_ubicaciones | — (historial) | P4 | UbicacionCambiada | RN-027, RN-028, RN-036 |
| historial_dependencias | — (historial) | P3 | BienTrasladado | RN-025, RN-026, RN-036 |
| mantenimientos_programados | MantenimientoProg. | P6, P7 | MantenimientoProgramado, MantenimientoRealizado, MantenimientoCancelado | RN-029, RN-030, RN-031, RN-032 |
| actas_entrega | — (documento) | P11 | ActaGenerada | RN-037, Capacidad 7 |

---

## 14. Validación contra DOM-INV-001

Verificación explícita de cobertura del modelo de datos respecto a todos los elementos definidos en DOM-INV-001.

---

### 14.1 Cobertura de Actores

| Actor DOM-INV-001 | Cobertura en modelo |
|------------------|---------------------|
| Auxiliar de Inventario | Representado por `users.id` en `solicitante_id` de solicitudes y `registrado_por_id` en historiales. Sus restricciones (solo propone, no aprueba) se implementan en la capa de autorización del CORE-2, no en el modelo de datos. |
| Coordinador de Dependencia | Representado en `dependencias.coordinador_id`. Sus solicitudes usan los mismos campos que el Auxiliar. |
| Aprobador | Representado en `aprobador_id` de solicitudes y `registrado_por_id` en acciones directas. |
| Custodio | Representado en `custodias.custodio_id`. |
| Auditor Institucional | El auditor consume el modelo en modo lectura. No requiere entidades adicionales. |
| Solicitante de Baja | Representado en `solicitudes_baja.solicitante_id`. |

**Cobertura: 100%**

---

### 14.2 Cobertura de Capacidades

| Capacidad DOM-INV-001 | Entidades que la soportan |
|----------------------|--------------------------|
| C1: Gestión de Bienes | bienes, detalles_bienes, imagenes_bienes, categorias, subcategorias, estados, origenes, almacenamientos |
| C2: Control de Modificaciones (HMB) | solicitudes_modificacion, historial_dependencias |
| C3: Control de Bajas (HEB) | solicitudes_baja |
| C4: Gestión de Custodios | custodias |
| C5: Seguimiento de Ubicaciones | historial_ubicaciones, historial_dependencias, ubicaciones, dependencias |
| C6: Gestión de Mantenimientos | mantenimientos_programados, tipos_mantenimiento |
| C7: Generación de Actas | actas_entrega, custodias (consulta de bienes activos del custodio) |
| C8: Gestión de Catálogos | categorias, subcategorias, estados, origenes, almacenamientos, tipos_mantenimiento, ubicaciones, dependencias |
| C9: Indicadores de Gestión | Todas las entidades (consultas de agregación). Campo grupo_institucional en categorias para grupos semánticos. |
| C10: Trazabilidad | custodias, solicitudes_modificacion, solicitudes_baja, historial_ubicaciones, historial_dependencias |

**Cobertura: 100%**

---

### 14.3 Cobertura de Agregados

| Agregado DOM-INV-001 | Entidad principal | Cobertura |
|---------------------|------------------|-----------|
| Bien | bienes | Completa. Incluye SoftDeletes, placa única, todos los atributos del glosario. |
| Custodia | custodias | Completa. Cadena temporal con fecha_fin IS NULL para activa, atomicidad garantizada. |
| Solicitud de Modificación | solicitudes_modificacion | Completa. tipo_objeto para bien/detalle, valor_anterior inmutable, estados terminales. |
| Solicitud de Baja | solicitudes_baja | Completa. Motivo obligatorio, dependencia_id_momento para auditoría, estados terminales. |
| Mantenimiento Programado | mantenimientos_programados | Completa. Tipo via FK a catálogo, estado triestado, fechas programada y realizada. |

**Cobertura: 100%**

---

### 14.4 Cobertura de Procesos

| Proceso DOM-INV-001 | Entidades afectadas | Persistencia |
|---------------------|--------------------|-----------  |
| P1: Registrar Bien | bienes, custodias, detalles_bienes?, imagenes_bienes? | INSERT transaccional |
| P2: Modificar Campo (HMB) | solicitudes_modificacion, bienes | INSERT + UPDATE transaccional |
| P3: Trasladar Bien | solicitudes_modificacion, historial_dependencias, bienes | Derivado de P2 con campo=dependencia_id |
| P4: Cambiar Ubicación | historial_ubicaciones | INSERT |
| P5: Cambiar Custodio | custodias | UPDATE (cierre) + INSERT (apertura), transaccional |
| P6: Programar Mantenimiento | mantenimientos_programados | INSERT |
| P7: Registrar Mantenimiento Realizado | mantenimientos_programados | UPDATE estado + fecha_realizada |
| P8: Solicitar Baja | solicitudes_baja | INSERT |
| P9: Aprobar/Rechazar Baja | solicitudes_baja, bienes, custodias | UPDATE transaccional |
| P10: Consultar Historial | todas (solo lectura) | SELECT |
| P11: Generar Acta | actas_entrega | INSERT + SELECT custodias |

**Cobertura: 100%**

---

### 14.5 Cobertura de Eventos

| Evento DOM-INV-001 | Persistencia | Cobertura |
|-------------------|-------------|-----------|
| BienRegistrado | bienes INSERT | Completa |
| BienModificado | bienes UPDATE + solicitudes_modificacion INSERT | Completa |
| ModificacionPropuesta | solicitudes_modificacion INSERT | Completa |
| ModificacionAprobada | solicitudes_modificacion UPDATE + bienes UPDATE | Completa |
| ModificacionRechazada | solicitudes_modificacion UPDATE | Completa |
| CustodioAsignado | custodias INSERT | Completa |
| CustodioLiberado | custodias UPDATE (fecha_fin) | Completa |
| BienTrasladado | historial_dependencias INSERT | Completa |
| UbicacionCambiada | historial_ubicaciones INSERT | Completa |
| MantenimientoProgramado | mantenimientos_programados INSERT | Completa |
| MantenimientoRealizado | mantenimientos_programados UPDATE | Completa |
| MantenimientoCancelado | mantenimientos_programados UPDATE | Completa |
| BajaSolicitada | solicitudes_baja INSERT | Completa |
| BajaAprobada | solicitudes_baja UPDATE + bienes UPDATE + custodias UPDATE | Completa |
| BajaRechazada | solicitudes_baja UPDATE | Completa |
| ActaGenerada | actas_entrega INSERT | Completa |

**Cobertura: 16/16 eventos — 100%**

---

### 14.6 Cobertura de Reglas de Negocio

| RN | Cobertura en modelo |
|----|---------------------|
| RN-001 | bienes.categoria_id NOT NULL + FK RESTRICT |
| RN-002 | RI-001: custodia activa obligatoria |
| RN-003 | bienes.deleted_at (SoftDeletes) |
| RN-004 | RI-002, RI-010: bien baja sin custodia activa |
| RN-005 | RI-010: bien baja no recibe solicitudes_modificacion |
| RN-006 | RI-017: bien baja no recibe mantenimientos |
| RN-007 | solicitudes_modificacion: roles básicos crean con estado='pendiente' |
| RN-008 | solicitudes_modificacion: Aprobadores crean con estado='aprobada' directamente |
| RN-009 | solicitudes_modificacion: campo, valor_anterior, valor_nuevo NOT NULL |
| RN-010 | RI-005: único pendiente por campo |
| RN-011 | ModificacionAprobada: transacción bien UPDATE + solicitud UPDATE |
| RN-012 | bien sin cambios en rechazo |
| RN-013 | solicitudes_modificacion: no DELETE, registros permanentes |
| RN-014 | historial_dependencias creado en aprobación de campo dependencia_id |
| RN-015 | solicitudes_baja: roles básicos, estado='pendiente' |
| RN-016 | RI-006: único pendiente por bien |
| RN-017 | solicitudes_baja.motivo NOT NULL |
| RN-018 | solicitudes_baja: no DELETE, registros permanentes |
| RN-019 | Verificación de dependencia en SolicitarBajaAction (capa de aplicación) |
| RN-020 | BajaAprobada: bien sin cambios en rechazo |
| RN-021 | RI-003: única custodia activa (fecha_fin IS NULL) por bien |
| RN-022 | custodias: atomicidad en cambio |
| RN-023 | Transacción en CambiarCustodioAction |
| RN-024 | RI-014: custodias solo INSERT (excepto cierre de fecha_fin) |
| RN-025 | historial_dependencias: registro obligatorio en traslado |
| RN-026 | historial_dependencias vinculado a aprobación HMB de dependencia |
| RN-027 | historial_ubicaciones: registro en cada movimiento |
| RN-028 | RI-014: historiales solo INSERT |
| RN-029 | mantenimientos_programados.tipo_mantenimiento_id NOT NULL |
| RN-030 | Condición: estado='pendiente' AND fecha_programada < CURDATE() |
| RN-031 | mantenimientos_programados.fecha_realizada al ejecutar |
| RN-032 | mantenimientos_programados: no DELETE |
| RN-033 | Restricción de roles en capa de autorización (CORE-2) |
| RN-034 | categorias.slug UNIQUE + categorias.grupo_institucional |
| RN-035 | RI-012: categoría no eliminable con bienes activos |
| RN-036 | Tablas historial sin updated_at, solo INSERT |
| RN-037 | actas_entrega: registro con bienes_contados y fecha generación |
| RN-038 | bienes.placa NOT NULL UNIQUE |
| RN-039 | bienes.estado_id NOT NULL |
| RN-040 | Validación en RegistrarBienAction: fecha_adquisicion <= created_at |

**Cobertura: 40/40 reglas de negocio — 100%**

---

*Fin del documento DAT-INV-001 — Modelo de Datos del Dominio Inventario v1.0.0*
*Vigente desde: 2026-06-14*
*Base para: Migraciones · Modelos · Repositorios · Servicios · APP-INV-001 (Arquitectura de Aplicación)*
