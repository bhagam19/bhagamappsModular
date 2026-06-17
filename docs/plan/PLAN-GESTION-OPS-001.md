# PLAN-GESTION-OPS-001

# Plan de Implementación de Operación Institucional

## Estado

APROBADO

---

# Proyecto

APPSisGOE

---

# Fase

Fase 3 — Operación Institucional

---

# Propósito

Definir el alcance funcional, técnico y arquitectónico para implementar el modelo operativo institucional aprobado en ADR-GESTION-OPS-001, permitiendo que las metas institucionales se ejecuten mediante actividades, tareas y responsables.

---

# Contexto

Actualmente APPSisGOE dispone de:

* Gestiones
* Procesos
* Componentes
* Objetivos
* Metas
* Indicadores

implementados y operativos.

Sin embargo, aún no existe la capa de ejecución institucional.

---

# Problema

Las metas pueden medirse.

Pero aún no pueden ejecutarse.

Actualmente el sistema puede responder:

¿Qué queremos lograr?

Pero no puede responder:

¿Qué debemos hacer para lograrlo?

---

# Objetivo General

Implementar el modelo:

```text
Meta
 ↓
Actividad
 ↓
Tarea
 ↓
Responsable
```

como capa operativa institucional oficial.

---

# Objetivos Específicos

## OE-01

Implementar actividades institucionales.

---

## OE-02

Implementar tareas institucionales.

---

## OE-03

Implementar responsables.

---

## OE-04

Implementar seguimiento operativo.

---

## OE-05

Preparar la base estructural para el Gantt Institucional.

---

# Alcance Funcional

## Actividades

Representan acciones institucionales planificadas.

Ejemplos:

* Actualizar inventario institucional.
* Realizar seguimiento a estudiantes en riesgo.
* Ejecutar plan de mantenimiento.

---

## Tareas

Representan unidades ejecutables.

Ejemplo:

Actividad:

```text
Actualizar inventario institucional
```

Tareas:

```text
Verificar aulas
Verificar laboratorios
Verificar biblioteca
```

---

## Responsables

Podrán ser:

* Usuarios
* Roles
* Dependencias

(según definición futura de implementación)

---

# Modelo Operativo

```text
Meta
 │
 └── Actividades
        │
        └── Tareas
               │
               └── Responsables
```

---

# Principio PMV

La Fase 3 deberá implementar únicamente la estructura mínima necesaria para operar metas institucionales.

No deberá incorporar funcionalidades avanzadas de planificación de proyectos.

---

# Entidades Previstas

## actividades

Campos preliminares:

```text
id
meta_id
componente_id

codigo
nombre
descripcion

fecha_inicio
fecha_fin

estado
avance

activo

created_at
updated_at
deleted_at
```

---

## tareas

Campos preliminares:

```text
id
actividad_id

codigo
nombre
descripcion

fecha_inicio
fecha_fin

estado
avance

activo

created_at
updated_at
deleted_at
```

---

## responsables

Modelo pendiente de definición definitiva.

La implementación deberá permitir evolución futura sin romper compatibilidad.

---

# Estados Operativos

## Actividades

Estados permitidos:

```text
Pendiente

En Proceso

Completada

Suspendida

Cancelada
```

---

## Tareas

Estados permitidos:

```text
Pendiente

En Proceso

Completada

Suspendida

Cancelada
```

---

# Avance

## Actividades

Las actividades almacenarán:

```text
avance
```

expresado en porcentaje:

```text
0 - 100
```

---

## Tareas

Las tareas almacenarán:

```text
avance
```

expresado en porcentaje:

```text
0 - 100
```

---

# Integración con Planeación

Toda actividad deberá asociarse obligatoriamente a:

```text
Meta
```

---

Toda meta podrá tener:

```text
0..N Actividades
```

---

# Integración con Componentes

Toda actividad deberá asociarse además a:

```text
Componente
```

para mantener la trazabilidad institucional.

---

# Integración con Indicadores

No se implementará en esta fase.

Será abordada en fases posteriores.

---

# Integración con Objetivos

La relación será indirecta:

```text
Objetivo
 ↓
Meta
 ↓
Actividad
```

---

# Gantt Institucional

No se implementará en esta fase.

Sin embargo, las entidades deberán soportar:

```text
fecha_inicio

fecha_fin

avance
```

para permitir su construcción futura.

---

# Exclusiones

No implementar:

* Dashboard Ejecutivo
* Gantt Institucional
* PMI
* Planes de Mejoramiento
* Alertas
* Notificaciones
* Automatizaciones
* Dependencias entre tareas

---

# Riesgos

## R-001

Complejidad excesiva del primer modelo operativo.

Mitigación:

```text
PMV
```

---

## R-002

Intentar implementar Gantt prematuramente.

Mitigación:

```text
Posponer para Fase 4
```

---

## R-003

Diseñar responsables demasiado rígidos.

Mitigación:

Definir una arquitectura extensible durante IMPL-GESTION-OPS-001.

---

# Resultado Esperado

APPSisGOE dispondrá de una capa operativa institucional capaz de ejecutar:

```text
Metas
 ↓
Actividades
 ↓
Tareas
 ↓
Responsables
```

manteniendo trazabilidad con:

```text
Gestiones
Procesos
Componentes
Objetivos
Metas
Indicadores
```

---

# Entregable Posterior

Una vez aprobado este plan deberá ejecutarse:

```text
IMPL-GESTION-OPS-001
```

---

# Criterios de Éxito

## CE-001

Actividades asociadas correctamente a metas.

---

## CE-002

Tareas asociadas correctamente a actividades.

---

## CE-003

Seguimiento operativo funcional.

---

## CE-004

Base preparada para futura implementación del Gantt.

---

# Estado

APROBADO

AUTORIZADO POR PMO
