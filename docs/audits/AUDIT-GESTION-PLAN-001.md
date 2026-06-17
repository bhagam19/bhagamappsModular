# AUDIT-GESTION-PLAN-001

# Auditoría Integral del Modelo de Planeación Institucional

## Estado

AUTORIZADA

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

# Hallazgos Esperados

La auditoría deberá identificar:

## Críticos

Problemas que impidan la implementación.

---

## Altos

Problemas que generen refactorización posterior.

---

## Medios

Problemas de diseño mejorables.

---

## Bajos

Mejoras documentales.

---

# Entregables

## Informe de Auditoría

Actualizar este documento con:

* Hallazgos
* Riesgos
* Recomendaciones

---

## Decisión

Emitir una de las siguientes conclusiones:

### Estado A

```text
APROBADO PARA IMPLEMENTACIÓN
```

---

### Estado B

```text
APROBADO CON AJUSTES
```

---

### Estado C

```text
NO APROBADO
```

---

# Resultado Esperado

Determinar si APPSisGOE se encuentra listo para iniciar:

```text
IMPL-GESTION-PLAN-001
```

sin requerir cambios arquitectónicos mayores.

---

# Estado de la Auditoría

AUTORIZADA

PENDIENTE DE EJECUCIÓN

Solicitada por PMO
