# ADR-GESTION-DATA-001

# Resolución de Hallazgos del Modelo de Datos Institucional

## Estado

APROBADO

---

# Tipo

Architecture Decision Record (ADR)

---

# Fecha

2026-06-16

---

# Contexto

Durante la revisión arquitectónica posterior a la elaboración de:

* DDOM-GESTION-001
* DDOM-GESTION-002
* DDOM-GESTION-003
* DDOM-GESTION-004
* DDOM-GESTION-005
* DDOM-GESTION-005A
* DDOM-GESTION-006
* DDOM-GESTION-DATA-001

se identificaron varios hallazgos relacionados con trazabilidad, responsabilidad institucional, seguimiento y medición.

La arquitectura general fue considerada consistente y adecuada para implementación.

Sin embargo, se detectaron relaciones que debían formalizarse antes de iniciar el diseño definitivo de migraciones y modelos.

---

# Hallazgo 1

## Relación Meta ↔ Indicador

### Situación Inicial

El modelo permitía:

```text
Componente
 ├── Metas
 └── Indicadores
```

pero no definía explícitamente la relación entre ambos.

---

### Problema

No existía un mecanismo formal para determinar:

* Qué indicadores miden una meta.
* Qué metas son medidas por un indicador.

---

### Decisión

Se establece la relación:

```text
Meta
 ↔
Indicador
```

---

### Cardinalidad

```text
N:M
```

---

### Implementación

Tabla:

```text
meta_indicador
```

Campos mínimos:

* id
* meta_id
* indicador_id
* created_at
* updated_at

---

### Justificación

Una meta puede requerir múltiples indicadores.

Un indicador puede participar en la medición de múltiples metas.

---

# Hallazgo 2

## Responsables de Componentes

### Situación Inicial

El modelo solo contemplaba:

```text
actividad_responsables
```

---

### Problema

La responsabilidad institucional normalmente existe desde el componente y no únicamente desde las actividades.

---

### Decisión

Se crea:

```text
componente_responsables
```

---

### Cardinalidad

```text
Componente
 ↔
Usuario
```

```text
N:M
```

---

### Justificación

Permite asignar líderes institucionales a cada componente.

Las actividades podrán heredar responsables desde el componente.

---

# Hallazgo 3

## Responsables de Objetivos

### Situación Inicial

Los objetivos carecen de responsables explícitos.

---

### Decisión

Se crea:

```text
objetivo_responsables
```

---

### Cardinalidad

```text
Objetivo
 ↔
Usuario
```

```text
N:M
```

---

### Justificación

Permite identificar responsables estratégicos del cumplimiento de objetivos.

---

# Hallazgo 4

## Evidencias

### Situación Inicial

Las evidencias estaban asociadas únicamente a actividades.

---

### Problema

Las instituciones generan evidencias asociadas a:

* Actividades
* Metas
* Indicadores
* Seguimientos

---

### Decisión

La entidad Evidencia será polimórfica.

---

### Modelo

```text
Evidencia
 ├── Actividad
 ├── Meta
 ├── Indicador
 └── Seguimiento
```

---

### Implementación

Campos mínimos:

* evidenciable_type
* evidenciable_id

---

### Justificación

Aumenta flexibilidad sin duplicar estructuras.

---

# Hallazgo 5

## Seguimientos

### Situación Inicial

Solo existían:

```text
seguimientos_indicadores
```

---

### Problema

La operación institucional requiere seguimiento independiente para:

* Indicadores
* Metas
* Actividades
* Tareas

---

### Decisión

Se separan los seguimientos por nivel.

---

### Estructuras

```text
seguimientos_indicadores
```

```text
seguimientos_metas
```

```text
seguimientos_actividades
```

```text
seguimientos_tareas
```

---

### Justificación

Cada nivel posee información y periodicidades distintas.

---

# Hallazgo 6

## Hallazgos Institucionales

### Situación Inicial

Los hallazgos pertenecían a un único componente.

---

### Problema

Algunos hallazgos impactan múltiples componentes.

Ejemplo:

```text
Baja participación familiar.
```

Puede afectar simultáneamente:

* GC-02
* GC-03

---

### Decisión

Se mantiene inicialmente:

```text
Hallazgo
belongsTo Componente
```

---

### Resolución

La relación múltiple se declara:

```text
DIFERIDA
```

hasta contar con evidencia real de uso.

---

### Justificación

Aplicar YAGNI.

Evitar complejidad prematura.

---

# Principios Adoptados

## Principio 1

La responsabilidad institucional puede existir en múltiples niveles.

---

## Principio 2

Los indicadores son instrumentos de medición reutilizables.

---

## Principio 3

Las evidencias son transversales al modelo.

---

## Principio 4

Los seguimientos deben reflejar el nivel operativo al que pertenecen.

---

## Principio 5

La complejidad futura solo se incorpora cuando exista necesidad demostrada.

---

# Impacto sobre DDOM-GESTION-DATA-001

Este ADR modifica y complementa:

```text
DDOM-GESTION-DATA-001
```

---

# Impacto sobre Implementación

Antes de iniciar:

```text
IMPL-GESTION-CORE-001
```

deberán incorporarse estas decisiones en:

* Migraciones
* Modelos Eloquent
* DTOs
* Actions
* ReadServices

---

# Riesgo Residual

Bajo.

No se identifican bloqueantes arquitectónicos para la implementación del CORE Institucional.

---

# Estado de la Decisión

APROBADA

VIGENTE

OBLIGATORIA PARA TODO APPSisGOE

Precede a:

```text
IMPL-GESTION-CORE-001
```
