# DDOM-GESTION-005

# Matriz de Integración de Módulos y Componentes Institucionales

## Estado

APROBADO

---

# Propósito

Definir el mecanismo oficial mediante el cual los módulos de APPSisGOE aportan información a la estructura institucional del CORE.

Este documento formaliza la relación entre:

* Módulos
* Métricas Operativas
* Componentes
* Indicadores
* Metas
* Objetivos
* Procesos
* Gestiones

---

# Principio Arquitectónico

Los módulos no se relacionan directamente con las gestiones.

Los módulos aportan información a los componentes institucionales.

Los componentes actúan como punto de integración entre la operación institucional y el modelo de gestión.

---

# Modelo Oficial

```text
Módulo
   ↓
Métrica Operativa
   ↓
Componente
   ↓
Indicador
   ↓
Meta
   ↓
Objetivo
   ↓
Proceso
   ↓
Gestión
```

---

# Inventario Institucional

## Estado Actual

Fuente operativa validada.

Datos auditados:

* 5829 bienes
* 143 ubicaciones físicas
* 25 categorías
* 100% cobertura de responsable operativo
* Inventario activo en producción

---

# Componentes Impactados

## GAF-02-03

Seguimiento al Uso de Espacios

---

### Métricas

* Bienes por ubicación
* Bienes por sede
* Bienes por dependencia

---

### Indicadores Potenciales

* Utilización de espacios
* Cobertura de recursos físicos

---

## GAF-02-04

Adquisición de Recursos para el Aprendizaje

---

### Métricas

* Total bienes
* Valor patrimonial
* Bienes por categoría

---

### Indicadores Potenciales

* Cobertura de recursos pedagógicos
* Crecimiento patrimonial

---

## GAF-02-05

Suministros y Dotación

---

### Métricas

* Dotación por sede
* Dotación por dependencia
* Dotación por responsable

---

### Indicadores Potenciales

* Cobertura de dotación institucional
* Equidad en distribución de recursos

---

## GAF-02-06

Mantenimiento de Equipos y Recursos para el Aprendizaje

---

### Métricas

* Equipos operativos
* Equipos en mantenimiento
* Equipos fuera de servicio

---

### Indicadores Potenciales

* Disponibilidad tecnológica
* Cumplimiento de mantenimiento

---

## GAF-02-07

Seguridad y Protección

---

### Métricas

* Bienes inventariados
* Bienes identificados
* Bienes con responsable
* Bienes con evidencia fotográfica

---

### Indicadores Potenciales

* Control patrimonial
* Trazabilidad de activos

---

# Comunidad Educativa

## Estado Actual

Fuente operativa validada.

Datos auditados:

* 62 miembros
* 57 vínculos
* 3 sedes

---

# Componentes Impactados

## GC-01-01

Atención a Poblaciones Diversas

---

### Métricas

* Estudiantes población diferencial
* Estudiantes víctimas
* Estudiantes migrantes

---

### Indicadores Potenciales

* Cobertura de atención diferencial
* Inclusión institucional

---

## GC-01-02

Necesidades Educativas Especiales

---

### Métricas

* Estudiantes NEE
* PIAR activos
* Casos atendidos

---

### Indicadores Potenciales

* Cobertura de atención NEE
* Seguimiento PIAR

---

## GC-01-03

Permanencia Escolar

---

### Métricas

* Matrícula
* Retirados
* Transferidos
* Ausentismo

---

### Indicadores Potenciales

* Tasa de permanencia
* Tasa de deserción
* Índice de riesgo

---

## GC-03-01

Participación Estudiantil

---

### Métricas

* Representantes elegidos
* Participación electoral
* Proyectos estudiantiles

---

### Indicadores Potenciales

* Participación democrática estudiantil

---

## GC-03-02

Participación de Padres

---

### Métricas

* Asistencia a reuniones
* Participación institucional

---

### Indicadores Potenciales

* Participación familiar

---

## GC-03-04

Participación de Egresados

---

### Métricas

* Egresados registrados
* Egresados vinculados

---

### Indicadores Potenciales

* Participación de egresados

---

## GC-04-02

Prevención de Riesgos Psicosociales

---

### Métricas

* Casos reportados
* Casos atendidos
* Seguimientos realizados

---

### Indicadores Potenciales

* Nivel de riesgo psicosocial
* Cobertura de atención

---

# Indicadores Simples

## Definición

Indicadores alimentados por una sola fuente institucional.

---

## Ejemplos

* Cobertura de Inventario
* Participación Familiar
* Cobertura de Dotación

---

# Indicadores Compuestos

## Definición

Indicadores alimentados por múltiples fuentes institucionales.

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
* Comunidad Educativa
* Académico

---

# Relación Métrica ↔ Indicador

Una métrica puede alimentar múltiples indicadores.

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

# Hallazgo Arquitectónico

Inventario se convierte en el primer proveedor oficial de métricas institucionales del CORE.

Comunidad Educativa se convierte en el segundo proveedor oficial de métricas institucionales del CORE.

---

# Beneficios

* Integración transversal.
* Reutilización institucional.
* Eliminación de duplicidad.
* Escalabilidad.
* Construcción de indicadores institucionales.

---

# Dependencias

Este documento depende de:

* DDOM-GESTION-001
* DDOM-GESTION-002
* DDOM-GESTION-003
* DDOM-GESTION-004

---

# Estado de la Decisión

APROBADA

VIGENTE

OBLIGATORIA PARA TODO APPSisGOE

Base para:

* DDOM-GESTION-005A
* DDOM-GESTION-006
* Dashboard Institucional
* Modelo de Planeación Institucional
