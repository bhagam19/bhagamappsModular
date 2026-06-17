# DDOM-GESTION-006

# Modelo Operativo Institucional

## Estado

APROBADO

---

# Propósito

Definir el modelo operativo institucional de APPSisGOE para la planeación, ejecución, seguimiento y evaluación de las acciones institucionales derivadas de la estructura de gestión establecida por la Guía 34 del MEN.

Este documento transforma la arquitectura conceptual definida en los documentos anteriores en un modelo funcional implementable.

---

# Dependencias

Este documento depende de:

* DDOM-GESTION-001
* DDOM-GESTION-002
* DDOM-GESTION-003
* DDOM-GESTION-004
* DDOM-GESTION-005
* DDOM-GESTION-005A

---

# Principio Fundamental

Los componentes constituyen la unidad operativa básica del sistema institucional de gestión.

Toda acción institucional debe poder relacionarse con un componente.

---

# Modelo Operativo Oficial

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

# Hallazgos

## Definición

Representan situaciones identificadas mediante:

* Autoevaluación institucional
* Auditorías
* Evaluaciones externas
* Seguimientos
* Análisis de indicadores

---

## Propósito

Los hallazgos justifican la formulación de objetivos.

---

## Modelo

```text
Hallazgo
    ↓
Objetivo
```

---

# Objetivos

## Definición

Resultados estratégicos esperados para un proceso institucional.

---

## Propietario

Proceso.

---

## Formato

Todo objetivo deberá formularse utilizando:

```text
Verbo
+
Objeto
+
Condición de Calidad
```

---

## Ejemplo

```text
Fortalecer la permanencia escolar mediante estrategias oportunas de acompañamiento integral.
```

---

# Metas

## Definición

Resultados específicos esperados para un componente.

---

## Propietario

Componente.

---

## Características

Modelo SMART obligatorio.

---

## Vigencia

Las metas son anuales.

---

## Ejemplo

```text
Incrementar la cobertura de inventario institucional al 90% durante la vigencia 2027.
```

---

# Indicadores

## Definición

Instrumentos de medición del avance de las metas.

---

## Propietario

Componente.

---

## Tipos

### Indicadores Simples

Una fuente de datos.

---

### Indicadores Compuestos

Múltiples fuentes de datos.

---

## Información mínima

* Nombre
* Fórmula
* Unidad
* Línea Base
* Meta
* Valor Actual
* Frecuencia

---

# Actividades

## Definición

Acciones institucionales que permiten alcanzar una meta.

---

## Propietario

Componente.

---

## Ejemplo

```text
Implementar programa institucional de seguimiento a estudiantes en riesgo.
```

---

# Relación

```text
Meta
   ↓
Actividades
```

---

## Reglas

### RN-ACT-001

Una meta puede tener múltiples actividades.

---

### RN-ACT-002

Toda actividad debe pertenecer a una meta.

---

# Tareas

## Definición

Acciones concretas necesarias para ejecutar una actividad.

---

## Ejemplo

Actividad:

```text
Implementar programa institucional de seguimiento.
```

Tareas:

```text
Identificar estudiantes.

Realizar entrevistas.

Registrar caracterización.

Programar visitas.

Generar informe.
```

---

## Relación

```text
Actividad
    ↓
Tareas
```

---

# Responsables

## Definición

Usuarios o equipos encargados de ejecutar actividades y tareas.

---

## Fuentes

* Usuarios
* Roles
* Miembros de Comunidad Educativa

---

## Modalidades

### Responsable Individual

```text
Usuario
```

---

### Responsable Colectivo

```text
Equipo
Grupo
Comité
Dependencia
```

---

# Evidencias

## Definición

Soportes que permiten demostrar la ejecución de actividades y tareas.

---

## Tipos

* Documento
* Imagen
* Video
* Acta
* Informe
* Enlace
* Archivo adjunto

---

# Seguimientos

## Definición

Registros periódicos del avance institucional.

---

## Tipos

### Manual

Registrado por responsables.

---

### Automático

Calculado por APPSisGOE.

---

## Ejemplos

Manual:

```text
Actividad ejecutada al 60%.
```

Automático:

```text
Indicador actualizado desde Inventario.
```

---

# Avance

## Avance de Actividad

Calculado mediante:

```text
Tareas completadas
/
Tareas totales
```

---

## Avance de Meta

Calculado mediante:

```text
Indicadores asociados
```

---

## Avance de Componente

Calculado mediante:

```text
Metas cumplidas
/
Metas definidas
```

---

## Avance de Proceso

Calculado mediante:

```text
Componentes asociados
```

---

## Avance de Gestión

Calculado mediante:

```text
Procesos asociados
```

---

# Cronograma

## Propósito

Planificar temporalmente actividades y tareas.

---

## Información mínima

* Fecha inicio
* Fecha fin
* Responsable
* Estado
* Avance

---

# Estados

## Actividad

* Planeada
* En ejecución
* Finalizada
* Cancelada

---

## Tarea

* Pendiente
* En curso
* Terminada

---

# Vista Operativa

La navegación principal será jerárquica.

---

## Nivel 1

```text
Gestiones
```

---

## Nivel 2

```text
Procesos
```

---

## Nivel 3

```text
Componentes
```

---

## Nivel 4

```text
Metas
Indicadores
Actividades
```

---

## Nivel 5

```text
Tareas
Evidencias
Seguimientos
```

---

# Vista Árbol

Modelo esperado:

```text
▶ Gestión Académica

   ▼ Seguimiento Académico

      ▼ Permanencia Escolar

         Meta 2027

         Indicadores

         Actividades
```

---

# Vista Gantt

El sistema deberá permitir visualizar:

* Actividades
* Tareas
* Responsables
* Fechas
* Avances

---

## Ejemplo

| Actividad               | Responsable | %   | Ene | Feb | Mar | Abr |
| ----------------------- | ----------- | --- | --- | --- | --- | --- |
| Seguimiento estudiantes | Orientador  | 80% | ███ | ███ | ███ |     |
| Escuela de padres       | Coordinador | 40% |     | ███ | ███ | ███ |

---

# Dashboard Ejecutivo

El dashboard institucional deberá mostrar:

* Avance por Gestión
* Avance por Proceso
* Avance por Componente
* Indicadores críticos
* Actividades vencidas
* Actividades próximas
* Cumplimiento institucional

---

# Beneficios

* Planeación institucional integrada.
* Mejoramiento continuo.
* Trazabilidad completa.
* Seguimiento basado en evidencia.
* Integración con módulos operativos.
* Gestión orientada a resultados.

---

# Hallazgo Arquitectónico Final

APPSisGOE evoluciona desde una plataforma de aplicaciones modulares hacia un Sistema Institucional de Gestión basado en:

```text
Hallazgos
↓
Objetivos
↓
Metas
↓
Indicadores
↓
Actividades
↓
Tareas
↓
Seguimientos
↓
Resultados
```

integrado completamente con la estructura:

```text
Gestión
↓
Proceso
↓
Componente
```

---

# Estado de la Decisión

APROBADA

VIGENTE

OBLIGATORIA PARA TODO APPSisGOE

Base para:

* Diseño de entidades CORE
* Modelo de datos institucional
* Diseño UI institucional
* Dashboard institucional
* Vista Gantt institucional
* ROADMAP-GESTION-001
