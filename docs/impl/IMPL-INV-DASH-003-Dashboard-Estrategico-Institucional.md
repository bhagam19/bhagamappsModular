# IMPL-INV-DASH-003 — Dashboard Estratégico Institucional

**Fecha:** 2026-06-11
**Módulo:** Inventario (`Modules/Inventario`)
**Prioridad:** ALTA
**Estado:** IMPLEMENTADO
**Versión:** Inventario v2.13.0 · IEE v1.18.0

---

## Objetivo

Convertir el Dashboard Ejecutivo de Inventario en una herramienta de análisis y toma de
decisiones para Rectoría y Administración, implementando las tareas DASH-021 a DASH-030.

---

## Contexto previo

Construido sobre IMPL-INV-DASH-001, IMPL-INV-DASH-002 y HOTFIX-INV-DASH-003.
El dashboard ya era funcional con KPIs, alertas, calidad de datos y gráficas básicas.

---

## Hallazgos — DASH-021: Auditoría de Gráficas Actuales

### Datos reales al 2026-06-11

| Indicador                    | Valor                          |
|------------------------------|-------------------------------|
| Total bienes activos         | 1,420                         |
| Dados de baja                | 0                             |
| Bienes en mantenimiento      | 4 (bienes distintos)          |
| Órdenes mant. pendientes     | 4                             |
| Categoría top                | Muebles (827 · 58.2%)         |
| Dependencia top              | S1 B301 Madena (62 bienes)    |
| Responsable top              | Teresita Marleny Pérez Peña (10 · 0.7%) |
| Con origen registrado        | 537 / 1,420 (37.8%)           |
| Condición predominante       | Bueno (1,079 · 76%)           |

### Validación de gráficas existentes

| Gráfica               | Estado  | Observación |
|-----------------------|---------|-------------|
| Bienes por Categoría  | ✅ OK   | Doughnut correcto, porcentajes coinciden |
| Bienes por Dependencia| ✅ OK   | Barra horizontal, top 10 correcto |
| Estado del Inventario | ✅→✅   | Convertido a tablero ejecutivo (DASH-026) |
| Origen de Bienes      | ✅ OK   | Normaliza NULL/"-" a "Sin origen" correctamente |
| Top Responsables      | ✅ OK   | Datos correctos, mejorado con % (DASH-025) |

---

## Implementación

### Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `Modules/Inventario/Livewire/Dashboard/InventarioDashboard.php` | Nuevas propiedades y métodos |
| `Modules/Inventario/resources/views/livewire/dashboard/inventario-dashboard.blade.php` | Nuevas secciones y mejoras visuales |

---

## DASH-022/023 — Bienes Estratégicos

**Criterio de clasificación:** palabra clave en el campo `nombre` del bien (LOWER LIKE).

| Tipo             | Keywords usadas                                    |
|------------------|----------------------------------------------------|
| Portátiles       | portátil, laptop, notebook                         |
| Video Beam       | video beam, videobeam, proyector                   |
| Computadores     | computador                                         |
| Cámaras          | cámara, camara                                     |
| Impresoras       | impresora, multifuncional                          |
| Televisores      | televisor, television, televisión                  |
| Tablets          | tablet                                             |
| Switch / Red     | switch, router                                     |
| UPS              | ups                                                |
| Servidores       | servidor                                           |

**Resultado (2026-06-11):**

| Bien Estratégico | Total | % del inventario |
|-----------------|-------|-----------------|
| Portátiles      | 83    | 5.8%            |
| Video Beam      | 50    | 3.5%            |
| Computadores    | 21    | 1.5%            |
| Cámaras         | 15    | 1.1%            |
| Impresoras      | 14    | 1.0%            |
| Televisores     | 12    | 0.8%            |
| Tablets         | 16    | 1.1%            |
| Switch / Red    | 10    | 0.7%            |
| UPS             | 7     | 0.5%            |
| Servidores      | 0     | —               |

---

## DASH-024 — Top Dependencias Relevantes

Tabla mejorada con columna **Responsable** (obtenida de `dependencias.user_id → users`).
Se muestra cantidad, porcentaje y responsable asociado.

---

## DASH-025 — Top Responsables Relevantes

Tabla mejorada con columna **%** (porcentaje sobre el total de bienes activos).

---

## DASH-026 — Estado del Inventario: Tablero Ejecutivo

Reemplaza la gráfica de torta de condición física por un tablero ejecutivo de ciclo de vida:

| Indicador            | Fuente de datos                                    |
|----------------------|----------------------------------------------------|
| Bienes Activos       | `bienes.deleted_at IS NULL`                        |
| En Mantenimiento     | `mantenimientos_programados` distinct `bien_id`    |
| Dados de Baja        | `bienes.deleted_at IS NOT NULL` (soft deletes)     |
| Solicitudes Pendientes | HEB + HMB en estado pendiente                   |

Se agrega debajo una barra de **condición física** (Nuevo/Bueno/Regular/Malo) como
información complementaria, sin necesidad de gráfica Chart.js.

---

## DASH-027 — Indicadores de Gestión

Sección "Indicadores de Gestión" reemplaza y amplía el antiguo "Resumen Ejecutivo":

1. Categoría predominante
2. Dependencia con más bienes
3. Responsable con más bienes (con %)
4. **Tipo de bien predominante** (condición física más frecuente = Bueno con 76%)
5. Solicitudes pendientes
6. Bienes en mantenimiento (con conteo de órdenes)

---

## DASH-028 — Indicadores Institucionales

Grupos institucionales basados en `categorias.id`:

| Grupo                   | IDs de categorías       | Bienes | % |
|-------------------------|-------------------------|--------|---|
| Mobiliario              | 1 (Muebles), 20 (Enseres) | 849 | 59.8% |
| Equipos Tecnológicos    | 5 (Cómputo), 6 (Comunicación) | 311 | 21.9% |
| Equipos Audiovisuales   | 7 (Audiovisuales)       | 88     | 6.2% |
| Equipos Administrativos | 9 (Oficina)             | 32     | 2.3% |
| Material Didáctico      | 3, 12, 13, 19           | ~40    | ~2.8% |
| Instrumentos Musicales  | 4                       | 41     | ~2.9% |
| Herramientas            | 26                      | 49     | ~3.5% |
| Otros                   | Resto de categorías     | var.   | var. |

---

## DASH-029 — Ranking Tecnológico

Sección "Inventario Tecnológico" con barra de progreso por tipo de bien TIC,
total TIC y porcentaje sobre el inventario total.

---

## DASH-030 — Revisión Visual

- Todas las cards usan `h-100` para alineación vertical.
- Tablas con `text-truncate` y `title` para nombres largos.
- Progress bars de 5-10px de altura para legibilidad.
- Sección de concentración del inventario (indicador adicional).
- Sin scroll innecesario en ninguna sección.

---

## Consultas y N+1

- **No hay N+1**: todas las consultas son agregaciones DB directas o se usan `with()` en
  los historiales del `render()`.
- `cargarBienesEstrategicos()` ejecuta 1 query por tipo estratégico (10 queries totales).
- `cargarGruposInstitucionales()` ejecuta 1 query por grupo (7 queries totales).
- Resto de métodos: 1 query cada uno (sin cambio vs versión anterior).

---

## Validaciones (V-001 a V-008)

| ID    | Descripción                        | Estado |
|-------|-------------------------------------|--------|
| V-001 | Todas las gráficas cargan           | ✅     |
| V-002 | Datos correctos (verificados DB)    | ✅     |
| V-003 | Porcentajes correctos               | ✅     |
| V-004 | Dashboard más útil (nuevas secciones) | ✅   |
| V-005 | Sin errores JS (wire:ignore + canvas IDs únicos) | ✅ |
| V-006 | Sin errores SQL (queries validados) | ✅     |
| V-007 | Sin N+1 (todas agregaciones directas) | ✅   |
| V-008 | Responsive (Bootstrap 4, col-md-*, col-xl-*) | ✅ |
