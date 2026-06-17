# ROADMAP-GESTION-001

# Roadmap de Implementación del CORE Institucional

## Estado

APROBADO

---

# Propósito

Definir la hoja de ruta oficial para la implementación progresiva del CORE Institucional de APPSisGOE basado en:

* Guía 34 del MEN
* DDOM-GESTION-001 a DDOM-GESTION-006
* DDOM-GESTION-DATA-001
* ADR-GESTION-DATA-001

---

# Visión

Transformar APPSisGOE desde una plataforma modular de aplicaciones hacia un Sistema Institucional de Gestión capaz de:

* Administrar la estructura institucional.
* Gestionar procesos de mejoramiento continuo.
* Integrar métricas operativas.
* Generar indicadores institucionales.
* Planificar actividades institucionales.
* Realizar seguimiento institucional.
* Consolidar información para la toma de decisiones.

---

# Estado Actual

## Arquitectura

```text
95%
```

---

## Documentación

```text
95%
```

---

## Modelo de Datos

```text
90%
```

---

## Implementación CORE Institucional

```text
0%
```

---

# Fase 1

# Infraestructura Institucional Base

## Objetivo

Implementar la estructura organizacional oficial.

---

## Entidades

* gestiones
* procesos
* componentes

---

## Entregables

### IMPL-GESTION-CORE-001

Migraciones.

---

### IMPL-GESTION-CORE-002

Modelos Eloquent.

---

### IMPL-GESTION-CORE-003

Seeders Guía 34.

---

## Resultado Esperado

La estructura institucional queda completamente cargada.

---

# Fase 2

# Planeación Institucional

## Objetivo

Implementar la capa estratégica.

---

## Entidades

* objetivos
* metas
* indicadores
* meta_indicador

---

## Entregables

### IMPL-GESTION-PLAN-001

Migraciones.

---

### IMPL-GESTION-PLAN-002

CRUD institucional.

---

### IMPL-GESTION-PLAN-003

Gestión de objetivos.

---

## Resultado Esperado

Cada proceso puede administrar objetivos y metas.

---

# Fase 3

# Operación Institucional

## Objetivo

Implementar la ejecución institucional.

---

## Entidades

* actividades
* tareas
* responsables
* evidencias

---

## Entregables

### IMPL-GESTION-OPS-001

Migraciones.

---

### IMPL-GESTION-OPS-002

CRUD operativo.

---

### IMPL-GESTION-OPS-003

Gestión de responsables.

---

## Resultado Esperado

Las metas pueden ejecutarse mediante actividades y tareas.

---

# Fase 4

# Seguimiento Institucional

## Objetivo

Implementar el monitoreo institucional.

---

## Entidades

* seguimientos_indicadores
* seguimientos_metas
* seguimientos_actividades
* seguimientos_tareas

---

## Entregables

### IMPL-GESTION-TRACK-001

Seguimientos.

---

### IMPL-GESTION-TRACK-002

Motor de cálculo.

---

## Resultado Esperado

Seguimiento permanente de la gestión.

---

# Fase 5

# Integración de Métricas Operativas

## Objetivo

Conectar módulos al CORE.

---

## Entidades

* metricas_operativas
* indicador_metrica

---

## Entregables

### IMPL-GESTION-METRICS-001

Motor de métricas.

---

### IMPL-GESTION-METRICS-002

Integración Inventario.

---

### IMPL-GESTION-METRICS-003

Integración Comunidad Educativa.

---

## Resultado Esperado

Los módulos alimentan indicadores institucionales.

---

# Fase 6

# Vista Institucional Jerárquica

## Objetivo

Implementar navegación institucional.

---

## Funcionalidades

```text
Gestión
 ↓
Proceso
 ↓
Componente
 ↓
Meta
 ↓
Actividad
 ↓
Tarea
```

---

## Entregables

### IMPL-GESTION-UI-001

Árbol Institucional.

---

### IMPL-GESTION-UI-002

Panel de detalle.

---

## Resultado Esperado

Navegación completa por la estructura institucional.

---

# Fase 7

# Gantt Institucional

## Objetivo

Visualizar la ejecución institucional.

---

## Funcionalidades

* Cronograma
* Actividades
* Tareas
* Responsables
* Avances

---

## Entregables

### IMPL-GESTION-GANTT-001

Motor Gantt.

---

### IMPL-GESTION-GANTT-002

Vista anual.

---

## Resultado Esperado

Seguimiento visual de la ejecución institucional.

---

# Fase 8

# Dashboard Institucional

## Objetivo

Consolidar información para la toma de decisiones.

---

## Indicadores

* Avance por gestión.
* Avance por proceso.
* Avance por componente.
* Indicadores críticos.
* Actividades vencidas.
* Cumplimiento institucional.

---

## Entregables

### IMPL-GESTION-DASH-001

Dashboard Ejecutivo.

---

### IMPL-GESTION-DASH-002

Dashboard Operativo.

---

## Resultado Esperado

Centro de control institucional.

---

# Fase 9

# Planeación Institucional Completa

## Objetivo

Consolidar el ciclo institucional.

---

## Funcionalidades

* Autoevaluación.
* PMI.
* Planes de mejoramiento.
* Seguimiento institucional.

---

## Resultado Esperado

Cierre del ciclo de mejoramiento continuo.

---

# Prioridades PMO

## Prioridad Inmediata

```text
Fase 1
Infraestructura Institucional Base
```

---

## Prioridad Alta

```text
Fase 2
Planeación Institucional
```

---

## Prioridad Alta

```text
Fase 3
Operación Institucional
```

---

## Prioridad Media

```text
Fase 4
Seguimiento Institucional
```

---

## Prioridad Media

```text
Fase 5
Integración de Métricas
```

---

## Prioridad Media

```text
Fase 6
Vista Jerárquica
```

---

## Prioridad Baja

```text
Fase 7
Gantt Institucional
```

---

## Prioridad Baja

```text
Fase 8
Dashboard Institucional
```

---

# Riesgos

## Riesgo 1

Intentar implementar todas las fases simultáneamente.

---

## Riesgo 2

Implementar UI antes de consolidar el modelo de datos.

---

## Riesgo 3

Implementar dashboards sin métricas consolidadas.

---

# Hito Estratégico

La culminación de las Fases 1 a 5 permitirá que APPSisGOE se convierta formalmente en un Sistema Institucional de Gestión alineado con la Guía 34.

---

# Estado de la Decisión

APROBADO

VIGENTE

OBLIGATORIO PARA LA PLANIFICACIÓN DEL CORE INSTITUCIONAL

Base para:

* IMPL-GESTION-CORE-001
* IMPL-GESTION-PLAN-001
* IMPL-GESTION-OPS-001
* ROADMAP-GENERAL-APPSISGOE
