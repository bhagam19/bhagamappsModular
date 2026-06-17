# DDOM-GESTION-001

# Modelo Institucional de Gestión, Procesos y Componentes

## Estado

APROBADO

---

# Propósito

Definir la estructura institucional oficial de APPSisGOE basada en la Guía 34 del Ministerio de Educación Nacional (MEN).

Este documento establece el modelo organizacional que servirá como base para todos los módulos presentes y futuros de la plataforma.

---

# Decisión Arquitectónica Fundamental

La estructura institucional definida por la Guía 34 pertenece al CORE de APPSisGOE.

No pertenece a ningún módulo funcional específico.

Por tanto:

* Gestiones
* Procesos
* Componentes

son entidades CORE obligatorias.

---

# Principios Arquitectónicos

## Principio 1

La estructura institucional es única para toda la institución.

---

## Principio 2

Los módulos consumen la estructura institucional.

Los módulos no la redefinen.

---

## Principio 3

La estructura institucional es transversal.

Puede ser utilizada simultáneamente por:

* Inventario
* Comunidad Educativa
* Planeación
* Calidad
* Riesgo de Deserción
* Académico
* Convivencia
* Mantenimiento
* Módulos futuros

---

## Principio 4

El CORE es propietario de:

* Gestiones
* Procesos
* Componentes

---

# Modelo Institucional Oficial

```text
Gestión
 └── Proceso
       └── Componentes
```

---

# Gestiones Institucionales

APPSisGOE adopta las cuatro gestiones definidas por la Guía 34.

---

# GD

## Gestión Directiva

Responsable del direccionamiento estratégico institucional.

### GD-01

Direccionamiento Estratégico y Horizonte Institucional

Componentes:

* Misión, Visión y Principios
* Metas Institucionales
* Conocimiento y Apropiación del Direccionamiento
* Política de Inclusión

### GD-02

Gestión Estratégica

Componentes:

* Liderazgo
* Articulación de Planes, Proyectos y Acciones
* Estrategia Pedagógica
* Uso de Información
* Seguimiento y Autoevaluación

### GD-03

Gobierno Escolar

Componentes:

* Consejo Directivo
* Consejo Académico
* Comisión de Evaluación y Promoción
* Comité de Convivencia
* Personería Estudiantil
* Consejo Estudiantil
* Asamblea y Consejo de Padres

### GD-04

Cultura Institucional

Componentes:

* Mecanismos de Comunicación
* Trabajo en Equipo
* Reconocimiento de Logros
* Identificación y Divulgación de Buenas Prácticas

### GD-05

Clima Escolar

Componentes:

* Pertenencia y Participación
* Ambiente Físico
* Inducción
* Motivación
* Manual de Convivencia
* Actividades Extracurriculares
* Bienestar del Alumnado
* Manejo de Conflictos

### GD-06

Relaciones con el Entorno

Componentes:

* Padres de Familia
* Autoridades Educativas
* Otras Instituciones
* Sector Productivo

---

# GA

## Gestión Académica

Responsable de los procesos pedagógicos y formativos.

### GA-01

Diseño Pedagógico

Componentes:

* Plan de Estudios
* Enfoque Metodológico
* Recursos para el Aprendizaje
* Jornada Escolar
* Evaluación

### GA-02

Prácticas Pedagógicas

Componentes:

* Opciones Didácticas
* Estrategias para las Tareas Escolares
* Uso Articulado de Recursos
* Uso del Tiempo para el Aprendizaje

### GA-03

Gestión de Aula

Componentes:

* Relación Pedagógica
* Planeación de Clases
* Estilo Pedagógico
* Evaluación en el Aula

### GA-04

Seguimiento Académico

Componentes:

* Seguimiento a Resultados Académicos
* Uso Pedagógico de Evaluaciones Externas
* Seguimiento a la Asistencia
* Actividades de Recuperación
* Apoyo Pedagógico para Estudiantes con Dificultades
* Seguimiento a Egresados

---

# GAF

## Gestión Administrativa y Financiera

Responsable del soporte institucional.

### GAF-01

Apoyo a la Gestión Académica

Componentes:

* Proceso de Matrícula
* Archivo Académico
* Boletines de Evaluación

### GAF-02

Administración de Planta Física y Recursos

Componentes:

* Mantenimiento de Planta Física
* Programas para Adecuación y Embellecimiento
* Seguimiento al Uso de Espacios
* Adquisición de Recursos para el Aprendizaje
* Suministros y Dotación
* Mantenimiento de Equipos y Recursos para el Aprendizaje
* Seguridad y Protección

### GAF-03

Administración de Servicios Complementarios

Componentes:

* Transporte
* Restaurante Escolar
* Salud Ocupacional
* Apoyo a Estudiantes con Necesidades Particulares

### GAF-04

Talento Humano

Componentes:

* Perfiles
* Inducción
* Formación y Capacitación
* Asignación Académica
* Bienestar del Talento Humano
* Evaluación del Desempeño

### GAF-05

Apoyo Financiero y Contable

Componentes:

* Presupuesto
* Contabilidad
* Ingresos y Gastos
* Control Fiscal

---

# GC

## Gestión de la Comunidad

Responsable de la relación entre la institución y su comunidad.

### GC-01

Accesibilidad

Componentes:

* Atención Educativa a Grupos Poblacionales Diversos
* Atención a Estudiantes con Necesidades Educativas Especiales
* Permanencia Escolar

### GC-02

Proyección a la Comunidad

Componentes:

* Escuela de Padres
* Oferta de Servicios a la Comunidad
* Uso de la Planta Física
* Servicio Social Estudiantil

### GC-03

Participación y Convivencia

Componentes:

* Participación de los Estudiantes
* Participación de los Padres de Familia
* Asamblea y Consejo de Padres
* Participación de Egresados

### GC-04

Prevención de Riesgos

Componentes:

* Prevención de Riesgos Físicos
* Prevención de Riesgos Psicosociales
* Programas de Seguridad

---

# Dependencias Institucionales

Los siguientes módulos consumirán esta estructura:

* Inventario
* Comunidad Educativa
* Planeación Institucional
* Calidad Institucional
* Riesgo de Deserción
* Académico
* Convivencia
* Mantenimiento
* Módulos futuros

---

# Estado de la Decisión

APROBADA

VIGENTE

OBLIGATORIA PARA TODO APPSisGOE
