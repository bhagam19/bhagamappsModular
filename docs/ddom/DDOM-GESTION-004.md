# DDOM-GESTION-004

# Modelo de Fuentes de Datos y Métricas Operativas

## Estado

APROBADO

---

# Propósito

Definir el modelo oficial mediante el cual los módulos operativos de APPSisGOE aportan información al CORE institucional para la construcción de indicadores, metas y seguimiento institucional.

---

# Principio Arquitectónico Fundamental

Los módulos no generan indicadores institucionales.

Los módulos generan métricas operativas.

El CORE transforma dichas métricas en indicadores institucionales.

---

# Modelo Oficial

```text
Módulo
   ↓
Métricas Operativas
   ↓
Componente
   ↓
Indicadores
   ↓
Metas
   ↓
Objetivos
   ↓
Proceso
   ↓
Gestión
```

---

# Definiciones

## Fuente de Datos

Representa el origen institucional de una información.

Puede corresponder a:

* Un módulo
* Un servicio
* Un proceso institucional
* Una integración externa

---

## Métrica Operativa

Dato producido directamente por la operación institucional.

Las métricas representan hechos.

No representan interpretación institucional.

---

## Indicador

Resultado calculado a partir de una o varias métricas operativas.

Los indicadores representan interpretación institucional.

---

# Principio de Separación

Las métricas operativas NO son indicadores.

Los indicadores NO son datos operativos.

---

# Ejemplo

Métrica:

```text
5829 bienes registrados
```

Indicador:

```text
Cobertura de Recursos Físicos
```

---

# Fuentes Institucionales Iniciales

## Inventario

Responsable de generar métricas relacionadas con:

* Bienes
* Categorías
* Ubicaciones
* Responsables
* Mantenimientos
* Estado de los bienes

---

## Comunidad Educativa

Responsable de generar métricas relacionadas con:

* Estudiantes
* Docentes
* Directivos
* Administrativos
* Padres de Familia
* Egresados

---

## Académico

Responsable de generar métricas relacionadas con:

* Rendimiento
* Asistencia
* Promoción
* Reprobación
* Evaluaciones

---

## Convivencia

Responsable de generar métricas relacionadas con:

* Casos disciplinarios
* Mediaciones
* Acuerdos
* Seguimientos

---

## Planeación

Responsable de generar métricas relacionadas con:

* Cumplimiento de actividades
* Ejecución de acciones
* Avance institucional

---

# Inventario como Fuente de Datos

## Métricas Operativas Iniciales

* Total de bienes
* Bienes activos
* Bienes inactivos
* Bienes por sede
* Bienes por ubicación
* Bienes por responsable
* Bienes con mantenimiento
* Bienes fuera de servicio
* Valor patrimonial
* Cobertura fotográfica

---

## Componentes Impactados

### GAF-02

Administración de Planta Física y Recursos

---

### GAF-02-03

Seguimiento al Uso de Espacios

---

### GAF-02-04

Adquisición de Recursos para el Aprendizaje

---

### GAF-02-05

Suministros y Dotación

---

### GAF-02-06

Mantenimiento de Equipos y Recursos para el Aprendizaje

---

### GAF-02-07

Seguridad y Protección

---

# Comunidad Educativa como Fuente de Datos

## Métricas Operativas Iniciales

* Total estudiantes
* Total docentes
* Total directivos
* Total padres
* Total egresados
* Participación estudiantil
* Participación familiar
* Permanencia escolar

---

## Componentes Impactados

### GC-01

Accesibilidad

### GC-03

Participación y Convivencia

### GC-04

Prevención de Riesgos

### GA-04

Seguimiento Académico

---

# Indicadores Simples

## Definición

Indicadores alimentados por una única fuente de datos.

---

## Ejemplos

* Cobertura de Inventario
* Cobertura de Dotación
* Participación Familiar

---

# Indicadores Compuestos

## Definición

Indicadores alimentados por múltiples fuentes.

---

## Ejemplos

### Riesgo de Deserción

Fuentes:

* Comunidad Educativa
* Académico
* Convivencia
* Asistencia
* Caracterización

---

### Calidad Institucional

Fuentes:

* Planeación
* Inventario
* Académico
* Comunidad Educativa

---

# Relación Métrica → Indicador

Una métrica operativa puede alimentar múltiples indicadores.

Un indicador puede consumir múltiples métricas.

---

# Modelo Relacional

```text
Métrica Operativa
       ↕
Indicador
```

Relación:

```text
N:M
```

---

# Reglas de Negocio

## RN-DAT-001

Toda métrica debe tener una fuente identificable.

---

## RN-DAT-002

Toda métrica debe pertenecer a un módulo o servicio.

---

## RN-DAT-003

Todo indicador debe estar asociado a un componente.

---

## RN-DAT-004

Un componente puede recibir métricas de múltiples módulos.

---

## RN-DAT-005

Los módulos no pueden crear indicadores institucionales directamente.

---

# Beneficios

* Integración modular.
* Reutilización de información.
* Eliminación de duplicidad.
* Indicadores institucionales consistentes.
* Escalabilidad.
* Trazabilidad completa.

---

# Dependencias

Este documento depende de:

* DDOM-GESTION-001
* DDOM-GESTION-002
* DDOM-GESTION-003

---

# Estado de la Decisión

APROBADA

VIGENTE

OBLIGATORIA PARA TODO APPSisGOE

Base para:

* DDOM-GESTION-005
* DDOM-GESTION-006
* Diseño del Dashboard Institucional
* Diseño de Indicadores Institucionales
