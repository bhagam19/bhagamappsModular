# DDOM-GESTION-002

# Modelo Institucional Oficial APPSisGOE

## Estado

APROBADO

---

# Propósito

Definir el modelo institucional oficial de APPSisGOE y las relaciones entre los elementos que conforman la estructura de gestión institucional.

Este documento formaliza la arquitectura conceptual que permitirá integrar todos los módulos actuales y futuros bajo una estructura común basada en la Guía 34 del MEN.

---

# Alcance

Este documento aplica a:

* CORE
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

# Visión Institucional

APPSisGOE no se concibe como un conjunto de aplicaciones independientes.

APPSisGOE se concibe como un Sistema Institucional de Gestión que integra la operación, el seguimiento, la evaluación y el mejoramiento continuo de la institución educativa.

---

# Principio Arquitectónico Fundamental

La estructura institucional pertenece al CORE.

Los módulos consumen dicha estructura.

Los módulos no son propietarios de la estructura institucional.

---

# Estructura Institucional Oficial

```text
Gestión
 └── Proceso
       └── Componentes
```

---

# Responsabilidades del CORE

El CORE es propietario de:

* Instituciones
* Sedes
* Usuarios
* Roles
* Permisos
* Miembros de la Comunidad Educativa
* Gestiones
* Procesos
* Componentes
* Objetivos
* Metas
* Indicadores
* Seguimientos
* Fuentes de Datos
* Métricas Operativas
* Evidencias
* Activity Logs
* Notificaciones
* Módulos

---

# Responsabilidades de los Módulos

Los módulos son propietarios únicamente de sus datos operativos.

Ejemplos:

Inventario:

* Bienes
* Categorías
* Ubicaciones
* Mantenimientos
* Responsables

Comunidad Educativa:

* Miembros
* Caracterizaciones
* Relaciones
* Participaciones

Académico:

* Cursos
* Asignaturas
* Calificaciones
* Asistencia

---

# Regla de Integración

Los módulos no se conectan directamente con:

* Gestiones
* Procesos
* Objetivos

Los módulos se integran mediante:

```text
Módulo
   ↓
Métrica Operativa
   ↓
Componente
```

---

# Modelo Institucional Integrado

```text
Gestión
 └── Proceso
       ├── Objetivos
       └── Componentes
```

---

# Objetivos

Los objetivos pertenecen a los procesos.

Representan los resultados estratégicos que la institución desea alcanzar.

Formato obligatorio:

```text
Verbo
+
Objeto
+
Condición de Calidad
```

Ejemplo:

```text
Fortalecer la permanencia escolar mediante estrategias oportunas de acompañamiento integral.
```

---

# Componentes

Los componentes constituyen la unidad básica de gestión institucional.

Representan los aspectos evaluados por la Guía 34.

Sobre ellos recaen:

* Evaluación
* Seguimiento
* Evidencias
* Actividades
* Mejoramiento

---

# Principio de Gestión

Todo componente puede:

* Tener evidencias
* Recibir métricas operativas
* Alimentar indicadores
* Participar en procesos de mejoramiento

---

# Metas

Las metas representan resultados esperados.

Características:

* Específicas
* Medibles
* Alcanzables
* Relevantes
* Temporales

Modelo SMART obligatorio.

---

# Indicadores

Los indicadores permiten medir el avance de las metas.

Todo indicador debe poseer:

* Nombre
* Fórmula
* Unidad
* Frecuencia
* Línea Base
* Valor Actual
* Valor Objetivo

---

# Seguimientos

Los seguimientos permiten registrar la evolución de las metas y los indicadores.

Tipos:

## Manual

Registrado por responsables institucionales.

---

## Automático

Calculado a partir de métricas provenientes de módulos.

---

# Fuentes de Datos

Representan el origen institucional de la información.

Ejemplos:

* Inventario
* Comunidad Educativa
* Académico
* Convivencia
* Planeación
* Calidad

---

# Métricas Operativas

Las métricas operativas son datos producidos por los módulos.

Ejemplos:

Inventario:

* Total bienes
* Bienes operativos
* Bienes por ubicación
* Valor patrimonial

Comunidad Educativa:

* Total estudiantes
* Total docentes
* Participación familiar

---

# Principio de Separación

Las métricas operativas NO son indicadores.

Las métricas operativas alimentan indicadores.

---

Ejemplo:

Métrica Operativa:

```text
5829 bienes registrados
```

Indicador:

```text
Cobertura de recursos físicos institucionales
```

---

# Instalabilidad de Módulos

Los siguientes elementos son obligatorios y permanentes:

* Gestiones
* Procesos
* Componentes
* Objetivos
* Metas
* Indicadores

Por tanto:

```text
CORE = obligatorio
```

---

Los módulos sí son instalables:

```text
Inventario
Comunidad Educativa
Planeación
Académico
Convivencia
Calidad
Riesgo de Deserción
```

---

# Dependencias Arquitectónicas

Los módulos dependen del CORE.

El CORE no depende de los módulos.

Modelo:

```text
Inventario
Comunidad Educativa
Académico
Convivencia
Planeación
Calidad
Deserción
      ↓
      CORE
```

---

# Beneficios del Modelo

* Reutilización institucional.
* Integración transversal.
* Escalabilidad.
* Independencia de módulos.
* Gobierno institucional centralizado.
* Soporte para indicadores institucionales.
* Soporte para mejoramiento continuo.

---

# Estado de la Decisión

APROBADA

VIGENTE

OBLIGATORIA PARA TODO APPSisGOE

Complementa:

* DDOM-GESTION-001

Sirve de base para:

* DDOM-GESTION-003
* DDOM-GESTION-004
* DDOM-GESTION-005
* DDOM-GESTION-006
