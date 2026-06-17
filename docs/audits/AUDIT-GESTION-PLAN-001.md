# AUDIT-GESTION-PLAN-001

# Auditoría Integral del Modelo de Planeación Institucional

## Estado

EJECUTADA

---

# Tipo

Auditoría de Arquitectura, Modelo de Datos y Consistencia Funcional

---

# Proyecto

APPSisGOE

---

# Objetivo

Auditar integralmente la consistencia, completitud, trazabilidad y viabilidad de implementación del Modelo de Planeación Institucional definido para APPSisGOE antes de iniciar la implementación de la Fase 2.

La auditoría deberá validar la coherencia entre:

* Estructura Institucional
* Planeación Estratégica
* Modelo Operativo
* Modelo de Datos
* Integración con módulos operativos

---

# Alcance

## Documentos a Auditar

### CORE Institucional

* DDOM-GESTION-001
* DDOM-GESTION-002

---

### Planeación

* DDOM-GESTION-003
* DDOM-GESTION-OBJ-001
* DDOM-GESTION-META-001
* DDOM-GESTION-IND-001

---

### Modelo de Datos

* DDOM-GESTION-DATA-001
* ADR-GESTION-DATA-001

---

### Modelo Operativo

* ADR-GESTION-PLAN-001
* ADR-GESTION-OPS-001

---

### Roadmap

* ROADMAP-GESTION-001
* PLAN-GESTION-PLAN-001
* PLAN-GESTION-CORE-001

---

# Validaciones Obligatorias

## AUD-001

### Cobertura de Procesos

Verificar:

```text
19 procesos oficiales
```

con:

```text
19 objetivos base
```

Resultado esperado:

```text
100%
```

---

## AUD-002

### Cobertura de Objetivos

Verificar:

```text
19 objetivos
```

con:

```text
42 metas
```

Resultado esperado:

Todos los objetivos poseen al menos una meta.

---

## AUD-003

### Cobertura de Metas

Verificar:

```text
42 metas
```

con:

```text
25 indicadores
```

Resultado esperado:

Todas las metas poseen al menos un indicador asociado.

---

## AUD-004

### Reutilización de Indicadores

Verificar que los indicadores definidos puedan ser reutilizados entre múltiples metas.

Resultado esperado:

Cumplimiento de:

```text
Meta ↔ Indicador
N:M
```

---

## AUD-005

### Coherencia Arquitectónica

Validar:

```text
Proceso
 ↓
Objetivo
 ↓
Meta
 ↔
Indicador
```

según ADR-GESTION-PLAN-001.

---

## AUD-006

### Coherencia Operativa

Validar:

```text
Meta
 ↓
Actividad
 ↓
Tarea
 ↓
Responsable
```

según ADR-GESTION-OPS-001.

---

## AUD-007

### Integración con Componentes

Validar:

```text
Actividad
 ↓
Componente
```

como mecanismo de conexión entre planeación y estructura institucional.

---

## AUD-008

### Integración con Módulos

Verificar compatibilidad con:

```text
Inventario
Comunidad Educativa
Académico
Convivencia
Talento Humano
Financiero
```

---

## AUD-009

### Integración con Dashboard

Validar que los indicadores definidos permitan futura construcción de:

```text
Dashboard Institucional
```

---

## AUD-010

### Integración con Gantt

Validar que el modelo operativo permita futura construcción de:

```text
Gantt Institucional
```

basado en:

```text
Actividades
Tareas
```

---

# Ejecución

## Fecha de Ejecución

2026-06-17

---

## Documentos Verificados

| Documento | Estado | Verificado |
|---|---|---|
| DDOM-GESTION-001 | APROBADO | SI |
| DDOM-GESTION-002 | APROBADO | SI |
| DDOM-GESTION-003 | APROBADO | SI |
| DDOM-GESTION-OBJ-001 | APROBADO | SI |
| DDOM-GESTION-META-001 | APROBADO | SI |
| DDOM-GESTION-IND-001 | APROBADO | SI |
| DDOM-GESTION-DATA-001 | APROBADO | SI |
| ADR-GESTION-DATA-001 | APROBADO | SI |
| ADR-GESTION-PLAN-001 | NO EXISTE | NO |
| ADR-GESTION-OPS-001 | APROBADO | SI |
| ROADMAP-GESTION-001 | APROBADO | SI |
| PLAN-GESTION-CORE-001 | APROBADO | SI |
| PLAN-GESTION-PLAN-001 | NO EXISTE | NO |

---

# Resultados por Validación

## AUD-001 — Cobertura de Procesos

### Verificación

19 procesos Guía 34 contra 19 objetivos base definidos en DDOM-GESTION-OBJ-001.

| Gestión | Procesos | Objetivos |
|---|---|---|
| GD | 6 (GD-01 a GD-06) | 6 |
| GA | 4 (GA-01 a GA-04) | 4 |
| GAF | 5 (GAF-01 a GAF-05) | 5 |
| GC | 4 (GC-01 a GC-04) | 4 |
| Total | 19 | 19 |

### Resultado

```text
PASS
```

Cobertura: 100%

---

## AUD-002 — Cobertura de Objetivos

### Verificación

Conteo real de códigos META en DDOM-GESTION-META-001:

| Gestión | Metas contadas |
|---|---|
| GD | 12 |
| GA | 9 |
| GAF | 11 |
| GC | 8 |
| Total real | 40 |
| Total declarado | 42 |

Todos los 19 objetivos poseen al menos una meta. Cobertura de objetivos: 100%.

### Observación

DDOM-GESTION-META-001 declara en el Resumen 42 metas. El conteo real de códigos META es 40. Delta de 2 metas sin identificar. Ver Hallazgo H-M-01.

### Resultado

```text
PASS con observación
```

---

## AUD-003 — Cobertura de Metas

### Verificación

Los 25 indicadores del catálogo tienen cobertura temática sobre todas las áreas de metas:

| Gestión | Metas | Indicadores aplicables |
|---|---|---|
| GD (12 metas) | IND-DIR-001 a IND-DIR-005 | Cobertura temática |
| GA (9 metas) | IND-ACA-001 a IND-ACA-007 | Cobertura temática |
| GAF (11 metas) | IND-ADM-001 a IND-ADM-007 | Cobertura temática |
| GC (8 metas) | IND-COM-001 a IND-COM-006 | Cobertura temática |

### Hallazgo crítico

La cobertura existe temáticamente pero no está formalizada. No existe ningún documento que mapee explícitamente qué indicador mide cuál meta específica. Esta vinculación es imprescindible para el seeder meta_indicador.

### Resultado

```text
PASS CONDICIONAL
```

La cobertura temática es adecuada. La asociación formal Meta a Indicador está sin documentar. Ver Hallazgos H-C-01, H-C-02, H-C-03.

---

## AUD-004 — Reutilización de Indicadores

### Verificación

ADR-GESTION-DATA-001 establece explícitamente:

```text
tabla: meta_indicador
campos: id, meta_id, indicador_id, created_at, updated_at
cardinalidad: N:M
```

La arquitectura admite que un mismo indicador mida simultáneamente múltiples metas.

### Hallazgo

La cardinalidad Meta N:M Indicador está documentada en ADR-GESTION-DATA-001 pero ausente en las Cardinalidades Oficiales de DDOM-GESTION-DATA-001. Ver Hallazgo H-A-03.

### Resultado

```text
PASS CONDICIONAL
```

Arquitectura correcta en ADR. DDOM desincronizado.

---

## AUD-005 — Coherencia Arquitectónica

### Hallazgo inmediato

ADR-GESTION-PLAN-001 NO EXISTE en docs/adr/. La validación no puede ejecutarse sobre el documento referenciado. Se valida con los documentos existentes.

### Verificación sobre documentos existentes

```text
Proceso
 ↓ proceso_id en objetivos
Objetivo
 ↓ sin FK directa
Meta
 ↔ via meta_indicador N:M
Indicador
```

El modelo de datos no implementa FK entre objetivos y metas. La cadena narrativa Proceso a Objetivo a Meta no tiene correspondencia estructural directa en DDOM-GESTION-DATA-001. La relación es indirecta: un proceso tiene componentes, los componentes tienen metas.

### Resultado

```text
PARCIAL
```

Coherencia arquitectónica general válida. FK Objetivo a Meta ausente en modelo de datos. ADR-GESTION-PLAN-001 pendiente de creación. Ver Hallazgos H-B-01, H-M-03.

---

## AUD-006 — Coherencia Operativa

### Verificación

| Relación | Implementación en DDOM-GESTION-DATA-001 | Estado |
|---|---|---|
| Meta a Actividad | actividades.meta_id | PASS |
| Actividad a Tarea | tareas.actividad_id | PASS |
| Tarea a Responsable | NO EXISTE tabla tarea_responsables | FALLA |

ADR-GESTION-OPS-001 establece: cada tarea deberá tener al menos un responsable. DDOM-GESTION-DATA-001 solo define actividad_responsables.

### Resultado

```text
PARCIAL
```

Meta a Actividad a Tarea correcta. Tarea a Responsable incumplida en modelo de datos. Ver Hallazgo H-A-01.

---

## AUD-007 — Integración con Componentes

### Verificación

ADR-GESTION-OPS-001 establece: las actividades deberán asociarse a uno o varios componentes.

DDOM-GESTION-DATA-001 define en tabla actividades:

```text
componente_id (FK simple)
```

Cardinalidad declarada:

```text
Componente 1:N Actividad
```

Una FK simple implementa 1:N, no N:M. Una actividad solo puede pertenecer a un componente.

### Resultado

```text
PARCIAL
```

Integración existe pero cardinalidad insuficiente respecto al ADR. Ver Hallazgo H-A-02.

---

## AUD-008 — Integración con Módulos

### Verificación

| Módulo | Indicadores vinculados | Estado |
|---|---|---|
| Inventario | IND-ADM-002, IND-ADM-003, IND-ADM-004 | PASS |
| Comunidad Educativa | IND-DIR-003, IND-COM-001 a IND-COM-004 | PASS |
| Académico | IND-ACA-001 a IND-ACA-007, IND-ADM-001 | PASS |
| Convivencia | IND-COM-006 | PASS |
| Talento Humano | IND-ADM-006 | PASS |
| Financiero | IND-ADM-007 | PASS |

Arquitectura de integración: metricas_operativas + indicador_metrica (N:M) definida en DDOM-GESTION-DATA-001. Regla de integración en DDOM-GESTION-002: Módulo a Métrica Operativa a Componente.

### Resultado

```text
PASS
```

Todos los módulos tienen cobertura de indicadores. Arquitectura de métricas operativas correcta.

---

## AUD-009 — Integración con Dashboard

### Verificación

| Capacidad | Soporte en modelo |
|---|---|
| valor_actual / valor_objetivo | SI |
| Series temporales (frecuencia) | SI |
| Histórico (seguimientos_indicadores) | SI |
| Agrupación por gestión | SI |
| Posición en ROADMAP (Fase 8) | Correcta |

### Resultado

```text
PASS
```

El modelo soporta la construcción futura del Dashboard Institucional.

---

## AUD-010 — Integración con Gantt

### Verificación

ADR-GESTION-OPS-001: el Gantt Institucional se construirá sobre Actividades y Tareas.

| Campo requerido | En actividades | En tareas |
|---|---|---|
| fecha_inicio | SI | SI |
| fecha_fin | SI | SI |
| porcentaje_avance | SI | SI |
| estado | SI | SI |
| responsable | actividad_responsables | Sin tarea_responsables |
| duración | calculable | calculable |

### Resultado

```text
PASS CONDICIONAL
```

El modelo soporta el Gantt básico. La asignación de responsable por tarea requiere ajuste de modelo de datos. Ver Hallazgo H-A-01.

---

# Hallazgos

## Críticos

### H-C-01 — Mapeo Meta a Componente no definido

DDOM-GESTION-META-001 define 40 metas usando identificadores de proceso (META-GD01-001, META-GA04-003, etc.). El modelo de datos DDOM-GESTION-DATA-001 exige componente_id en la tabla metas.

Ejemplo: bajo GD-01 existen 4 componentes (Misión/Visión/Principios, Metas Institucionales, Conocimiento y Apropiación, Política de Inclusión). No existe ningún documento que decida a cuál de estos 4 componentes pertenece META-GD01-001 ni META-GD01-002.

Impacto: el MetasSeeder de IMPL-GESTION-PLAN-001 no puede ejecutarse sin esta decisión.

Bloquea: IMPL-GESTION-PLAN-001

---

### H-C-02 — Mapeo Indicador a Componente no definido

DDOM-GESTION-IND-001 clasifica indicadores por gestión (IND-DIR, IND-ACA, IND-ADM, IND-COM). El modelo de datos exige componente_id en la tabla indicadores. Bajo la Gestión Directiva existen 29 componentes. No existe ningún documento que asigne IND-DIR-001 a un componente específico.

Impacto: el IndicadoresSeeder de IMPL-GESTION-PLAN-001 no puede ejecutarse sin esta decisión.

Bloquea: IMPL-GESTION-PLAN-001

---

### H-C-03 — Mapeo Meta a Indicador no definido

ADR-GESTION-DATA-001 establece la tabla meta_indicador (N:M). No existe ningún documento que defina cuáles indicadores miden cuáles metas específicas (ejemplo: IND-DIR-001 mide META-GD01-001).

Impacto: el MetaIndicadorSeeder no puede ejecutarse. El Dashboard y los seguimientos no tendrán datos de medición.

Bloquea: IMPL-GESTION-PLAN-001

---

## Altos

### H-A-01 — Tarea a Responsable sin implementación en modelo de datos

ADR-GESTION-OPS-001 establece que cada tarea deberá tener al menos un responsable. DDOM-GESTION-DATA-001 define actividad_responsables pero NO define tarea_responsables.

Impacto: la Fase 3 (IMPL-GESTION-OPS-001) implementará tareas sin responsables. El ADR quedará incumplido. Se requerirá refactorización posterior.

---

### H-A-02 — Cardinalidad Actividad a Componente inconsistente

ADR-GESTION-OPS-001: las actividades deberán asociarse a uno o varios componentes. DDOM-GESTION-DATA-001 define componente_id simple (1:N). Si una actividad requiere fortalecer simultáneamente dos componentes, el modelo actual no lo permite.

Impacto: limitación funcional. Requiere cambio de FK simple a tabla pivote actividad_componentes si se respeta el ADR.

---

### H-A-03 — meta_indicador ausente en Cardinalidades Oficiales de DDOM-GESTION-DATA-001

ADR-GESTION-DATA-001 formaliza la relación Meta N:M Indicador via tabla meta_indicador. Las Cardinalidades Oficiales de DDOM-GESTION-DATA-001 no incluyen esta cardinalidad. Solo aparece Componente 1:N Indicador.

Impacto: un implementador que siga solo DDOM-GESTION-DATA-001 puede omitir meta_indicador y romper la trazabilidad Meta a Indicador.

---

## Medios

### H-M-01 — Discrepancia numérica en DDOM-GESTION-META-001

El Resumen de DDOM-GESTION-META-001 declara 42 metas. El conteo real de códigos META en el documento es 40. Delta de 2 metas no identificadas.

Conteo: GD(12) + GA(9) + GAF(11) + GC(8) = 40.

---

### H-M-02 — Indicadores del catálogo incompletos estructuralmente

DDOM-GESTION-IND-001 define la Estructura del Indicador con 8 campos: Código, Nombre, Descripción, Fórmula, Unidad, Frecuencia, Tipo, Fuente de Datos.

Estado real en el catálogo:

| Campo | Completitud |
|---|---|
| Nombre | 25 de 25 |
| Unidad | 25 de 25 |
| Frecuencia | 25 de 25 |
| Fuente | 25 de 25 |
| Descripción | 1 de 25 |
| Fórmula | 0 de 25 |
| Tipo | 0 de 25 |

La ausencia de Fórmulas impide calcular valores automáticos. El campo tipo del modelo de datos (simple/compuesto) no puede asignarse sin esta información.

---

### H-M-03 — FK Objetivo a Meta ausente en modelo de datos

La cadena narrativa aprobada es Proceso a Objetivo a Meta a Indicador. En DDOM-GESTION-DATA-001:

```text
objetivos.proceso_id  (Proceso → Objetivo)
metas.componente_id   (Componente → Meta, no Objetivo → Meta)
```

No existe objetivo_id en metas. La relación Objetivo a Meta es semántica pero no estructural.

---

## Bajos

### H-B-01 — ADR-GESTION-PLAN-001 referenciado pero inexistente

AUDIT-GESTION-PLAN-001 (AUD-005) valida la coherencia arquitectónica según ADR-GESTION-PLAN-001. El archivo docs/adr/ADR-GESTION-PLAN-001.md no existe en el repositorio.

---

### H-B-02 — PLAN-GESTION-PLAN-001 referenciado pero inexistente

AUDIT-GESTION-PLAN-001 incluye PLAN-GESTION-PLAN-001 en su alcance. El archivo no existe. Es un entregable planificado para Fase 2 aún no generado.

---

### H-B-03 — Formato inconsistente en DDOM-GESTION-META-001

GD-01, GD-02, GD-03 incluyen sección Objetivo explícita antes de las metas. GD-04, GD-05, GD-06 van directamente a Metas Base sin sección Objetivo. Inconsistencia de formato menor.

---

# Tabla Resumen de Hallazgos

| ID | Tipo | Descripción corta | Bloquea IMPL-GESTION-PLAN-001 |
|---|---|---|---|
| H-C-01 | CRITICO | Meta sin mapeo a Componente | SI |
| H-C-02 | CRITICO | Indicador sin mapeo a Componente | SI |
| H-C-03 | CRITICO | Meta a Indicador sin asociación definida | SI |
| H-A-01 | ALTO | Tarea a Responsable sin modelo de datos | No (Fase 3) |
| H-A-02 | ALTO | Actividad a Componente cardinalidad 1:N vs N:M | No (Fase 3) |
| H-A-03 | ALTO | meta_indicador ausente en DDOM-DATA-001 | Indirecto |
| H-M-01 | MEDIO | Discrepancia: 42 declaradas vs 40 reales | No |
| H-M-02 | MEDIO | Indicadores sin Fórmula, Tipo, Descripción | No |
| H-M-03 | MEDIO | FK Objetivo a Meta ausente en modelo de datos | No |
| H-B-01 | BAJO | ADR-GESTION-PLAN-001 no existe | No |
| H-B-02 | BAJO | PLAN-GESTION-PLAN-001 no existe | No |
| H-B-03 | BAJO | Formato inconsistente DDOM-META-001 | No |

---

# Tabla de Resultados AUD

| Validación | Resultado |
|---|---|
| AUD-001 Cobertura Procesos | PASS |
| AUD-002 Cobertura Objetivos | PASS con observación |
| AUD-003 Cobertura Metas | PASS CONDICIONAL |
| AUD-004 Reutilización Indicadores | PASS CONDICIONAL |
| AUD-005 Coherencia Arquitectónica | PARCIAL |
| AUD-006 Coherencia Operativa | PARCIAL |
| AUD-007 Integración Componentes | PARCIAL |
| AUD-008 Integración Módulos | PASS |
| AUD-009 Dashboard | PASS |
| AUD-010 Gantt | PASS CONDICIONAL |

---

# Riesgos

## RIE-001 — Seeder incoherente con modelo de datos

Nivel: CRITICO

Si IMPL-GESTION-PLAN-001 se ejecuta sin resolver H-C-01 y H-C-02, el MetasSeeder y el IndicadoresSeeder requerirán decisiones ad-hoc durante la implementación. El resultado será un modelo de datos con asignaciones de componentes sin respaldo documental, introduciendo deuda arquitectónica inmediata.

---

## RIE-002 — ADR incumplido en Fase 3

Nivel: ALTO

Si H-A-01 y H-A-02 no se resuelven antes de IMPL-GESTION-OPS-001, la implementación de Fase 3 entregará un modelo de datos que no cumple ADR-GESTION-OPS-001. La refactorización posterior requeriría migraciones sobre tablas ya pobladas con datos reales.

---

## RIE-003 — Dashboard sin Fórmulas

Nivel: MEDIO

Los 25 indicadores base carecen de Fórmula. Sin fórmulas, el motor de cálculo automático del Dashboard (Fase 8) no puede construirse. Deberán definirse antes de o durante IMPL-GESTION-PLAN-001.

---

## RIE-004 — Desincronización documental acumulativa

Nivel: MEDIO

La desincronización entre ADR-GESTION-DATA-001 (que define meta_indicador) y DDOM-GESTION-DATA-001 (que no la lista en cardinalidades) establece un patrón que puede repetirse. Los DDOM son la referencia primaria para implementadores; si están incompletos se generarán implementaciones incorrectas.

---

# Recomendaciones

## REC-001

Obligatoria. Previa a IMPL-GESTION-PLAN-001.

Crear DDOM-GESTION-MAP-001 que defina:

* Mapeo de cada meta base a su componente específico.
* Mapeo de cada indicador base a su componente específico.
* Tabla de asociaciones Meta a Indicador base para seed de meta_indicador.

Este documento es prerequisito bloqueante para IMPL-GESTION-PLAN-001.

---

## REC-002

Obligatoria. Previa a IMPL-GESTION-PLAN-001.

Corregir discrepancia numérica en DDOM-GESTION-META-001: identificar las 2 metas faltantes o corregir el Resumen a 40.

---

## REC-003

Obligatoria. Previa a IMPL-GESTION-OPS-001.

Resolver H-A-01 mediante una de las siguientes opciones:

Opción A: Crear tarea_responsables en DDOM-GESTION-DATA-001 y en migración de Fase 3.

Opción B: Actualizar ADR-GESTION-OPS-001 declarando que responsables se gestionan únicamente a nivel de actividad.

---

## REC-004

Recomendada.

Completar el catálogo DDOM-GESTION-IND-001 con Fórmulas y Tipo para los 25 indicadores. Puede hacerse en un suplemento DDOM-GESTION-IND-002 sin modificar el original.

---

## REC-005

Recomendada.

Crear ADR-GESTION-PLAN-001 para formalizar la arquitectura de Planeación y resolver H-M-03 decidiendo si se requiere objetivo_id en metas.

---

## REC-006

Recomendada.

Actualizar Cardinalidades Oficiales de DDOM-GESTION-DATA-001 para incluir Meta N:M Indicador (tabla meta_indicador), sincronizando con ADR-GESTION-DATA-001.

---

# Prerrequisitos para IMPL-GESTION-PLAN-001

Los siguientes documentos deben existir y estar aprobados antes de iniciar IMPL-GESTION-PLAN-001:

```text
DDOM-GESTION-MAP-001
```

Resolverá: H-C-01, H-C-02, H-C-03.

---

# Veredicto

```text
APROBADO CON AJUSTES
```

## Fundamento

La arquitectura institucional base es sólida y consistente. Los documentos CORE (DDOM-001, 002, 003) son coherentes entre sí. El modelo de datos general es implementable.

Sin embargo, existen 3 hallazgos críticos que impiden ejecutar IMPL-GESTION-PLAN-001 directamente.

Los hallazgos críticos no representan fallas arquitectónicas. Representan decisiones de datos pendientes que deben formalizarse antes de la implementación.

## Condición para inicio de IMPL-GESTION-PLAN-001

Resolución documentada de H-C-01, H-C-02 y H-C-03 mediante DDOM-GESTION-MAP-001 aprobado por PMO.

---

# Estado de la Auditoría

EJECUTADA

VEREDICTO: APROBADO CON AJUSTES

IMPL-GESTION-PLAN-001: EN ESPERA DE DDOM-GESTION-MAP-001
