# PLAN-GESTION-DASH-001

# Plan de Implementación del Dashboard Ejecutivo Institucional

## Estado

APROBADO

AUTORIZADO PARA IMPLEMENTACIÓN

---

# Proyecto

APPSisGOE

---

# Fase

Fase 4 — Dashboard Ejecutivo Institucional

---

# Propósito

Definir el alcance funcional, técnico y arquitectónico para implementar el Dashboard Ejecutivo Institucional de APPSisGOE.

El Dashboard permitirá visualizar en tiempo real el estado de cumplimiento de la gestión institucional utilizando la información generada por las fases ya implementadas.

---

# Contexto

Actualmente APPSisGOE dispone de:

```text
Gestiones
Procesos
Componentes

Objetivos
Metas
Indicadores

Actividades
Tareas
Responsables
```

completamente implementados y auditados.

La información institucional ya existe.

Se requiere ahora transformarla en información ejecutiva para apoyar la toma de decisiones.

---

# Principios Arquitectónicos

## Principio 1

El Dashboard es consumidor de información.

No es propietario de datos.

---

## Principio 2

Toda métrica debe calcularse a partir de información existente.

---

## Principio 3

El Dashboard no modifica registros.

Su función es exclusivamente analítica y visual.

---

## Principio 4

Debe operar aun cuando la institución tenga información parcial.

---

## Principio 5

La visualización debe mantener trazabilidad institucional completa.

---

# Objetivo General

Implementar una capa ejecutiva capaz de visualizar el desempeño institucional mediante indicadores, gráficos y semáforos de gestión.

---

# Objetivos Específicos

## OE-01

Visualizar cumplimiento institucional global.

---

## OE-02

Visualizar cumplimiento por gestión.

---

## OE-03

Visualizar cumplimiento por proceso.

---

## OE-04

Visualizar cumplimiento por componente.

---

## OE-05

Visualizar carga operativa institucional.

---

## OE-06

Identificar riesgos y retrasos institucionales.

---

# Alcance Funcional

## Dashboard General

Deberá mostrar una vista consolidada de toda la institución.

---

## Dashboard por Gestión

Deberá permitir visualizar:

```text
Gestión Directiva

Gestión Académica

Gestión Administrativa y Financiera

Gestión de la Comunidad
```

---

## Dashboard por Proceso

Deberá mostrar desempeño específico de cada proceso.

---

## Dashboard por Componente

Deberá mostrar desempeño específico de cada componente institucional.

---

# KPIs Institucionales Obligatorios

## KPI-001

Cumplimiento Institucional Global

Fórmula:

```text
Promedio del avance de todas las metas activas.
```

Visualización:

```text
Porcentaje (%)
```

---

## KPI-002

Metas Completadas

Fórmula:

```text
Metas completadas / Metas totales
```

Visualización:

```text
Cantidad
Porcentaje
```

---

## KPI-003

Actividades Completadas

Fórmula:

```text
Actividades completadas / Actividades totales
```

---

## KPI-004

Tareas Completadas

Fórmula:

```text
Tareas completadas / Tareas totales
```

---

## KPI-005

Indicadores en Riesgo

Fórmula:

```text
Indicadores por debajo del umbral esperado.
```

---

## KPI-006

Objetivos con Retraso

Fórmula:

```text
Objetivos que poseen metas con retraso.
```

---

## KPI-007

Responsables con Mayor Carga

Ranking:

```text
Top 10 responsables
```

según cantidad de tareas activas.

---

# Dashboard por Gestión

Cada gestión deberá mostrar:

```text
Objetivos

Metas

Indicadores

Actividades

Tareas

Avance promedio
```

---

# Dashboard por Proceso

Cada proceso deberá mostrar:

```text
Proceso
 ↓
Objetivos
 ↓
Metas
 ↓
Actividades
 ↓
Tareas
```

---

# Dashboard por Componente

Cada componente deberá mostrar:

```text
Componente
 ↓
Objetivos
 ↓
Metas
 ↓
Indicadores
```

---

# Semaforización Oficial

## Verde

```text
>= 80%
```

Representa cumplimiento satisfactorio.

---

## Amarillo

```text
>= 60%
< 80%
```

Representa atención requerida.

---

## Rojo

```text
< 60%
```

Representa riesgo institucional.

---

# Widgets PMV

## Widget 1

Cumplimiento Institucional Global.

---

## Widget 2

Metas por Estado.

---

## Widget 3

Actividades por Estado.

---

## Widget 4

Tareas por Estado.

---

## Widget 5

Top Responsables.

---

## Widget 6

Avance por Gestión.

---

# Filtros PMV

Implementar filtros por:

```text
Gestión

Proceso

Componente

Responsable
```

---

# Requisitos Técnicos

Preferir:

```text
Laravel Blade

AdminLTE

Chart.js
```

---

Compatibilidad:

```text
Desktop

Tablet

Mobile
```

---

# Exclusiones

No implementar en esta fase:

```text
Gantt

PMI

Planes de Mejoramiento

Notificaciones

Alertas automáticas

Analítica predictiva

IA
```

---

# Riesgos

## R-DASH-001

Consultas excesivas.

Mitigación:

```text
Eager Loading
Agregaciones SQL
```

---

## R-DASH-002

Datos insuficientes para algunos indicadores.

Mitigación:

```text
Mostrar métricas parciales.
```

---

# QA Obligatorio

## QA-DASH-001

Dashboard carga correctamente.

---

## QA-DASH-002

KPIs calculados correctamente.

---

## QA-DASH-003

Semáforos aplican reglas oficiales.

---

## QA-DASH-004

Filtros funcionan correctamente.

---

## QA-DASH-005

Gráficos muestran datos reales.

---

## QA-DASH-006

No existen consultas N+1 críticas.

---

## QA-DASH-007

Compatibilidad móvil validada.

---

# Entregables

## Documento

```text
docs/plan/PLAN-GESTION-DASH-001.md
```

---

## CHANGELOG

Actualizar.

---

## Git

Commit.

Push.

---

# Resultado Esperado

APPSisGOE dispondrá de un Dashboard Ejecutivo Institucional capaz de visualizar:

```text
Gestión
 ↓
Proceso
 ↓
Componente
 ↓
Objetivo
 ↓
Meta
 ↓
Indicador
 ↓
Actividad
 ↓
Tarea
 ↓
Responsable
```

mediante:

```text
KPIs

Gráficos

Semáforos

Rankings

Tableros ejecutivos
```

facilitando la toma de decisiones institucionales.

---

# Entregable Posterior

Una vez aprobado este plan deberá iniciarse:

```text
IMPL-GESTION-DASH-001
```

---

# Estado

APROBADO

AUTORIZADO POR PMO
