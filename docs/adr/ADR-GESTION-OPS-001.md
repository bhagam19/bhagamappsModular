# ADR-GESTION-OPS-001

# Modelo Operativo Institucional: Relación entre Metas, Actividades, Tareas y Responsables

## Estado

APROBADO

---

# Tipo

Architecture Decision Record (ADR)

---

# Proyecto

APPSisGOE

---

# Fecha

2026-06-16

---

# Contexto

Durante la construcción del CORE Institucional y del modelo de Planeación Institucional surgió la necesidad de definir formalmente cómo se ejecutan los objetivos y metas institucionales.

Las decisiones previas establecieron:

```text
Gestión
 ↓
Proceso
 ↓
Objetivo
 ↓
Meta
 ↓
Indicador
```

Sin embargo, este modelo describe únicamente la planeación y la medición.

No describe la ejecución institucional.

---

# Problema

Una meta no se cumple automáticamente.

La institución debe ejecutar acciones concretas para alcanzarla.

Era necesario definir formalmente:

* Cómo se ejecutan las metas.
* Cómo se organizan las actividades.
* Cómo se asignan responsables.
* Cómo se registran avances.
* Cómo se soportará el futuro Gantt Institucional.

---

# Análisis

## Planeación

La planeación responde:

```text
¿Qué queremos lograr?
```

Representada mediante:

```text
Objetivo
 ↓
Meta
```

---

## Medición

La medición responde:

```text
¿Cómo sabemos si avanzamos?
```

Representada mediante:

```text
Meta
 ↓
Indicador
```

---

## Ejecución

La ejecución responde:

```text
¿Qué debemos hacer para lograr la meta?
```

La respuesta no puede ser un indicador.

Debe existir una capa operativa.

---

# Decisión

APPSisGOE adopta oficialmente el siguiente modelo:

```text
Gestión
 │
 └── Proceso
      │
      ├── Componentes
      │
      └── Objetivos
            │
            └── Metas
                  │
                  ├── Indicadores
                  │
                  └── Actividades
                        │
                        └── Tareas
                              │
                              └── Responsables
```

---

# Rol de los Componentes

Los componentes continúan representando:

```text
Áreas institucionales de intervención
```

Ejemplo:

```text
GA-04
Seguimiento Académico

Componente:
Seguimiento a Resultados Académicos
```

---

## Relación con Actividades

Las actividades deberán asociarse a uno o varios componentes.

Esto permitirá conocer:

```text
Qué componente institucional está siendo fortalecido.
```

---

# Definición de Actividad

## Concepto

Una actividad representa una acción institucional planificada para contribuir al logro de una meta.

---

## Ejemplos

```text
Implementar plan de apoyo pedagógico.
```

```text
Realizar seguimiento mensual a estudiantes en riesgo.
```

```text
Actualizar inventario físico institucional.
```

---

# Definición de Tarea

## Concepto

Una tarea representa una unidad operativa ejecutable dentro de una actividad.

---

## Ejemplos

Actividad:

```text
Actualizar inventario físico institucional.
```

Tareas:

```text
Verificar aulas.
Verificar laboratorios.
Verificar biblioteca.
Verificar oficinas.
```

---

# Definición de Responsable

## Concepto

Persona o dependencia encargada de ejecutar una tarea.

---

## Responsabilidad

El avance de las tareas alimentará el avance de las actividades.

---

# Modelo Operativo Oficial

## Relación Meta → Actividad

```text
Meta
 ↓
Actividades
```

Una meta puede requerir múltiples actividades.

---

## Relación Actividad → Tarea

```text
Actividad
 ↓
Tareas
```

Una actividad puede contener múltiples tareas.

---

## Relación Tarea → Responsable

```text
Tarea
 ↓
Responsable
```

Cada tarea deberá tener al menos un responsable.

---

# Seguimiento

## Avance de Tareas

Las tareas registrarán:

```text
Porcentaje de avance
Estado
Fecha inicio
Fecha fin
```

---

## Avance de Actividades

El avance de las actividades podrá calcularse automáticamente a partir del avance de sus tareas.

---

## Avance de Metas

Las metas podrán calcular avance mediante:

### Método 1

Indicadores.

---

### Método 2

Cumplimiento de actividades.

---

### Método 3

Modelo híbrido.

---

# Integración con el Gantt

Se establece oficialmente que:

```text
El Gantt Institucional se construirá sobre Actividades y Tareas.
```

No sobre objetivos.

No sobre metas.

No sobre indicadores.

---

## Datos del Gantt

Las actividades y tareas deberán soportar:

```text
Fecha inicio
Fecha fin
Duración
Responsable
Estado
Avance
```

---

# Integración con Módulos

Las actividades podrán generar evidencias provenientes de módulos institucionales.

Ejemplo:

## Inventario

```text
Actualizar inventario de aulas.
```

Evidencia:

```text
Bienes registrados.
Ubicaciones actualizadas.
```

---

## Comunidad Educativa

```text
Caracterizar estudiantes en riesgo.
```

Evidencia:

```text
Formularios diligenciados.
```

---

# Beneficios

## Planeación

Claridad entre lo que se quiere lograr y lo que se hará.

---

## Ejecución

Asignación formal de responsabilidades.

---

## Seguimiento

Cálculo de avances institucionales.

---

## Dashboard

Visualización de cumplimiento.

---

## Escalabilidad

Base para:

* PMI.
* Autoevaluación.
* Seguimiento Institucional.
* Dashboard Ejecutivo.
* Gantt Institucional.

---

# Consecuencias

## Positivas

* Conecta planeación y operación.
* Permite seguimiento real.
* Facilita la gestión institucional.
* Facilita la automatización futura.

---

## Negativas

* Incrementa la complejidad del modelo.
* Requiere entidades adicionales.

---

# Modelo Institucional Consolidado

```text
Gestión
 │
 └── Proceso
      │
      ├── Componentes
      │
      └── Objetivos
            │
            └── Metas
                  │
                  ├── Indicadores
                  │
                  └── Actividades
                        │
                        └── Tareas
                              │
                              └── Responsables
```

---

# Impacto

Este ADR se convierte en referencia obligatoria para:

```text
DDOM-GESTION-META-001
DDOM-GESTION-IND-001
DDOM-GESTION-ACT-001
PLAN-GESTION-PLAN-001
IMPL-GESTION-PLAN-001
IMPL-GESTION-OPS-001
```

---

# Estado de la Decisión

APROBADA

VIGENTE

OBLIGATORIA PARA TODO EL MODELO OPERATIVO INSTITUCIONAL DE APPSisGOE
