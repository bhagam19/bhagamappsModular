# DDOM-GESTION-DATA-001

# Modelo de Datos Institucional

## Estado

APROBADO

---

# Propósito

Definir el modelo de datos oficial del CORE Institucional de APPSisGOE para soportar la estructura de gestión basada en la Guía 34 del MEN y el Modelo Operativo Institucional aprobado.

Este documento constituye la base para:

* Migraciones
* Modelos Eloquent
* DTOs
* Actions
* ReadServices
* APIs
* Dashboard Institucional
* Vista Árbol
* Vista Gantt

---

# Dependencias

Este documento depende de:

* DDOM-GESTION-001
* DDOM-GESTION-002
* DDOM-GESTION-003
* DDOM-GESTION-004
* DDOM-GESTION-005
* DDOM-GESTION-005A
* DDOM-GESTION-006

---

# Modelo Institucional Oficial

```text
Gestión
 └── Proceso
       ├── Objetivos
       └── Componentes
             ├── Metas
             ├── Indicadores
             ├── Actividades
             │      └── Tareas
             ├── Responsables
             ├── Evidencias
             └── Seguimientos
```

---

# Entidades CORE

## gestiones

Representa las cuatro gestiones institucionales.

Campos:

* id
* codigo
* nombre
* descripcion
* orden
* activo
* created_at
* updated_at
* deleted_at

---

## procesos

Campos:

* id
* gestion_id
* codigo
* nombre
* descripcion
* orden
* activo
* created_at
* updated_at
* deleted_at

---

Relación:

```text
Gestion
hasMany Procesos
```

---

## componentes

Campos:

* id
* proceso_id
* codigo
* nombre
* descripcion
* orden
* activo
* created_at
* updated_at
* deleted_at

---

Relación:

```text
Proceso
hasMany Componentes
```

---

# Objetivos

## objetivos

Campos:

* id
* proceso_id
* codigo
* nombre
* descripcion
* justificacion
* estado
* fecha_inicio
* fecha_fin
* created_at
* updated_at
* deleted_at

---

Estados:

* borrador
* activo
* cerrado

---

Relación:

```text
Proceso
hasMany Objetivos
```

---

# Metas

## metas

Campos:

* id
* componente_id
* codigo
* nombre
* descripcion
* anio
* valor_objetivo
* unidad
* estado
* created_at
* updated_at
* deleted_at

---

Estados:

* pendiente
* seguimiento
* cumplida
* incumplida

---

Relación:

```text
Componente
hasMany Metas
```

---

# Indicadores

## indicadores

Campos:

* id
* componente_id
* codigo
* nombre
* descripcion
* formula
* unidad
* frecuencia
* linea_base
* valor_actual
* valor_objetivo
* tipo
* estado
* created_at
* updated_at
* deleted_at

---

Tipos:

* simple
* compuesto

---

Frecuencias:

* mensual
* bimestral
* trimestral
* semestral
* anual

---

Relación:

```text
Componente
hasMany Indicadores
```

---

# Seguimientos

## seguimientos_indicadores

Campos:

* id
* indicador_id
* fecha
* valor
* observaciones
* tipo
* registrado_por
* created_at
* updated_at

---

Tipos:

* manual
* automatico

---

Relación:

```text
Indicador
hasMany Seguimientos
```

---

# Actividades

## actividades

Campos:

* id
* componente_id
* meta_id
* codigo
* nombre
* descripcion
* fecha_inicio
* fecha_fin
* porcentaje_avance
* estado
* created_at
* updated_at
* deleted_at

---

Estados:

* planeada
* ejecucion
* finalizada
* cancelada

---

Relaciones:

```text
Componente
hasMany Actividades
```

```text
Meta
hasMany Actividades
```

---

# Tareas

## tareas

Campos:

* id
* actividad_id
* codigo
* nombre
* descripcion
* fecha_inicio
* fecha_fin
* porcentaje_avance
* estado
* created_at
* updated_at
* deleted_at

---

Estados:

* pendiente
* en_curso
* terminada

---

Relación:

```text
Actividad
hasMany Tareas
```

---

# Responsables

## actividad_responsables

Tabla pivote.

Campos:

* id
* actividad_id
* user_id
* rol_responsable
* created_at
* updated_at

---

Relación:

```text
Actividad
belongsToMany Usuarios
```

---

# Evidencias

## evidencias

Campos:

* id
* actividad_id
* tipo
* titulo
* descripcion
* archivo
* url
* fecha_evidencia
* registrado_por
* created_at
* updated_at
* deleted_at

---

Tipos:

* documento
* imagen
* video
* acta
* informe
* enlace

---

Relación:

```text
Actividad
hasMany Evidencias
```

---

# Métricas Operativas

## metricas_operativas

Campos:

* id
* modulo
* codigo
* nombre
* descripcion
* unidad
* frecuencia
* activo
* created_at
* updated_at

---

Ejemplos:

* Total bienes
* Bienes activos
* Valor patrimonial
* Total estudiantes
* Participación familiar

---

# Relación Indicadores ↔ Métricas

## indicador_metrica

Tabla pivote.

Campos:

* id
* indicador_id
* metrica_operativa_id
* peso
* created_at
* updated_at

---

Relación:

```text
Indicador
belongsToMany MetricasOperativas
```

---

# Hallazgos

## hallazgos

Campos:

* id
* componente_id
* codigo
* nombre
* descripcion
* fuente
* fecha_hallazgo
* estado
* created_at
* updated_at
* deleted_at

---

Fuentes:

* autoevaluacion
* auditoria
* evaluacion_externa
* seguimiento
* otro

---

Relación:

```text
Componente
hasMany Hallazgos
```

---

# Relación Hallazgos ↔ Objetivos

## hallazgo_objetivo

Tabla pivote.

Campos:

* id
* hallazgo_id
* objetivo_id
* created_at
* updated_at

---

Relación:

```text
Hallazgo
belongsToMany Objetivos
```

---

# Cardinalidades Oficiales

```text
Gestion
1:N
Proceso
```

```text
Proceso
1:N
Objetivo
```

```text
Proceso
1:N
Componente
```

```text
Componente
1:N
Meta
```

```text
Componente
1:N
Indicador
```

```text
Componente
1:N
Actividad
```

```text
Actividad
1:N
Tarea
```

```text
Indicador
1:N
Seguimiento
```

```text
Indicador
N:M
MetricaOperativa
```

```text
Hallazgo
N:M
Objetivo
```

---

# Soft Deletes

Las siguientes entidades deberán implementar SoftDeletes:

* gestiones
* procesos
* componentes
* objetivos
* metas
* indicadores
* actividades
* tareas
* evidencias
* hallazgos

---

# Auditoría

Todas las entidades deberán registrar:

* created_at
* updated_at

Y estarán sujetas a:

```text
activity_logs
```

del CORE.

---

# Convención de Códigos

Gestiones:

```text
GD
GA
GAF
GC
```

---

Procesos:

```text
GD-01
GA-04
GAF-02
GC-03
```

---

Componentes:

```text
GC-01-03
GAF-02-06
```

---

Metas:

```text
META-GC-01-03-2027-001
```

---

Indicadores:

```text
IND-GAF-02-06-001
```

---

Actividades:

```text
ACT-GC-01-03-2027-001
```

---

# Hallazgo Arquitectónico

El componente se consolida como la unidad operativa fundamental del sistema institucional.

Todo seguimiento, medición, actividad y mejoramiento institucional converge en un componente.

---

# Estado de la Decisión

APROBADA

VIGENTE

OBLIGATORIA PARA TODO APPSisGOE

Base para:

* ADR-GESTION-DATA-001
* ROADMAP-GESTION-001
* Migraciones CORE
* Implementación Institucional
* Dashboard Institucional
* Vista Árbol
* Vista Gantt
