# PLAN-GESTION-CORE-001

# Plan de Implementación de la Infraestructura Institucional Base

## Estado

APROBADO

---

# Tipo

Plan de Implementación

---

# Proyecto

APPSisGOE

---

# Fase Asociada

ROADMAP-GESTION-001

Fase 1

```text
Infraestructura Institucional Base
```

---

# Objetivo

Implementar la estructura institucional oficial de APPSisGOE basada en la Guía 34 del MEN.

Esta fase constituye la fundación del CORE Institucional y permitirá soportar todas las fases posteriores de:

* Planeación
* Calidad
* Riesgo de Deserción
* Inventario
* Comunidad Educativa
* Dashboard Institucional
* Gantt Institucional

---

# Alcance

## Incluye

### Gestiones

```text
GD
GA
GAF
GC
```

---

### Procesos

Todos los procesos oficiales definidos en DDOM-GESTION-001.

---

### Componentes

Todos los componentes oficiales definidos en DDOM-GESTION-001.

---

### Modelo de Datos

Implementación de:

```text
gestiones
procesos
componentes
```

---

### Seeders

Carga inicial de:

```text
4 Gestiones
Procesos oficiales
Componentes oficiales
```

---

### Modelos

```text
Gestion
Proceso
Componente
```

---

### Relaciones

```text
Gestion
hasMany Procesos
```

```text
Proceso
belongsTo Gestion
hasMany Componentes
```

```text
Componente
belongsTo Proceso
```

---

### Permisos

Lectura:

```text
ver-gestiones
ver-procesos
ver-componentes
```

Administración:

```text
crear-gestiones
editar-gestiones

crear-procesos
editar-procesos

crear-componentes
editar-componentes
```

---

### Interfaz

Vista institucional jerárquica.

---

# Exclusiones

Esta fase NO implementa:

```text
objetivos
```

```text
metas
```

```text
indicadores
```

```text
seguimientos
```

```text
actividades
```

```text
tareas
```

```text
responsables
```

```text
evidencias
```

```text
metricas_operativas
```

```text
dashboard institucional
```

```text
gantt institucional
```

Estas funcionalidades serán desarrolladas en fases posteriores.

---

# Entregables

## IMPL-GESTION-CORE-001

Migraciones.

---

## IMPL-GESTION-CORE-002

Modelos Eloquent.

---

## IMPL-GESTION-CORE-003

Seeders institucionales.

---

## IMPL-GESTION-CORE-004

Permisos.

---

## IMPL-GESTION-CORE-005

Vista jerárquica institucional.

---

# Diseño de Migraciones

## Tabla

```text
gestiones
```

Campos:

* id
* codigo
* nombre
* descripcion
* orden
* activo
* timestamps
* softDeletes

---

## Tabla

```text
procesos
```

Campos:

* id
* gestion_id
* codigo
* nombre
* descripcion
* orden
* activo
* timestamps
* softDeletes

---

## Tabla

```text
componentes
```

Campos:

* id
* proceso_id
* codigo
* nombre
* descripcion
* orden
* activo
* timestamps
* softDeletes

---

# Índices

## gestiones

```text
codigo
```

Único.

---

## procesos

```text
gestion_id
```

Índice.

```text
codigo
```

Único.

---

## componentes

```text
proceso_id
```

Índice.

```text
codigo
```

Único.

---

# Reglas de Integridad

## RI-001

No puede existir un proceso sin gestión.

---

## RI-002

No puede existir un componente sin proceso.

---

## RI-003

El código institucional debe ser único.

---

## RI-004

Las eliminaciones deberán ser lógicas.

---

# Seeders Oficiales

## Gestiones

```text
GD
Gestión Directiva
```

```text
GA
Gestión Académica
```

```text
GAF
Gestión Administrativa y Financiera
```

```text
GC
Gestión de la Comunidad
```

---

## Procesos

Cargar todos los procesos oficiales documentados.

---

## Componentes

Cargar todos los componentes oficiales documentados.

---

# Diseño de Interfaz

## Vista Inicial

```text
▶ Gestión Directiva

▶ Gestión Académica

▶ Gestión Administrativa y Financiera

▶ Gestión de la Comunidad
```

---

## Vista Expandida

```text
▼ Gestión Académica

   ▼ Seguimiento Académico

      • Evaluación

      • Promoción

      • Permanencia
```

---

# Criterios de Aceptación

## CA-001

Las cuatro gestiones existen en base de datos.

---

## CA-002

Todos los procesos oficiales fueron cargados.

---

## CA-003

Todos los componentes oficiales fueron cargados.

---

## CA-004

Las relaciones son navegables mediante Eloquent.

---

## CA-005

La estructura puede visualizarse jerárquicamente.

---

## CA-006

Los permisos funcionan correctamente.

---

# Riesgos

## Riesgo 1

Inconsistencias entre componentes documentados y seeders.

Mitigación:

Validación cruzada con DDOM-GESTION-001.

---

## Riesgo 2

Cambios futuros en la estructura institucional.

Mitigación:

Uso de SoftDeletes.

---

# Dependencias

Depende de:

```text
DDOM-GESTION-001
DDOM-GESTION-002
DDOM-GESTION-DATA-001
ADR-GESTION-DATA-001
ROADMAP-GESTION-001
```

---

# Resultado Esperado

Al finalizar esta fase, APPSisGOE dispondrá de la estructura institucional oficial completamente implementada y persistida en la base de datos, sirviendo como base para todas las funcionalidades de planeación, seguimiento, calidad y gestión institucional.

---

# Estado de la Decisión

APROBADO

LISTO PARA IMPLEMENTACIÓN

Autoriza:

```text
IMPL-GESTION-CORE-001
```
