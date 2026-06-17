# DDOM-GESTION-003

# Modelo de Objetivos, Metas, Indicadores y Seguimientos

## Estado

APROBADO

---

# Propósito

Definir el modelo institucional para la formulación, seguimiento y evaluación de objetivos, metas e indicadores dentro de APPSisGOE.

Este documento establece cómo la institución transforma los resultados de la autoevaluación en procesos de mejoramiento continuo.

---

# Origen Institucional

Los objetivos no surgen de manera aislada.

Su origen institucional es:

```text
Autoevaluación
    ↓
Hallazgos
    ↓
Objetivos
    ↓
Metas
    ↓
Indicadores
    ↓
Seguimientos
```

---

# Principio Fundamental

Los objetivos pertenecen a los procesos.

Las metas, indicadores y seguimientos pertenecen a los componentes.

---

# Modelo Institucional

```text
Gestión
 └── Proceso
       ├── Objetivos
       └── Componentes
              ├── Metas
              ├── Indicadores
              └── Seguimientos
```

---

# Objetivos

## Definición

Representan los resultados estratégicos que la institución desea alcanzar dentro de un proceso.

---

## Propietario

Los objetivos pertenecen al proceso.

---

## Formato Obligatorio

Todo objetivo deberá construirse utilizando:

```text
Verbo
+
Objeto
+
Condición de Calidad
```

---

## Ejemplos

```text
Fortalecer la permanencia escolar mediante estrategias oportunas de acompañamiento integral.
```

```text
Garantizar la disponibilidad de recursos físicos institucionales para apoyar los procesos educativos.
```

---

# Reglas de Negocio

## RN-OBJ-001

Todo proceso debe tener al menos un objetivo activo.

---

## RN-OBJ-002

Un proceso puede tener múltiples objetivos.

---

## RN-OBJ-003

Los objetivos podrán ser revisados y ajustados por los equipos responsables de cada proceso.

---

## RN-OBJ-004

APPSisGOE podrá proponer objetivos base como referencia institucional.

---

# Metas

## Definición

Representan resultados específicos que la institución espera alcanzar en un componente determinado.

---

## Propietario

Las metas pertenecen a los componentes.

---

## Principio SMART

Toda meta deberá ser:

* Específica
* Medible
* Alcanzable
* Relevante
* Temporal

---

## Naturaleza Temporal

Las metas son anuales.

---

## Ejemplo

Meta 2027:

```text
Alcanzar una cobertura de inventario institucional del 80%.
```

Meta 2028:

```text
Alcanzar una cobertura de inventario institucional del 90%.
```

---

# Reglas de Negocio

## RN-META-001

Un componente puede tener múltiples metas.

---

## RN-META-002

Las metas deben estar asociadas a un año de vigencia.

---

## RN-META-003

Las metas podrán renovarse o redefinirse para periodos posteriores.

---

# Indicadores

## Definición

Permiten medir el avance de las metas institucionales.

---

## Propietario

Los indicadores pertenecen a los componentes.

---

## Información Obligatoria

Todo indicador deberá incluir:

* Nombre
* Descripción
* Fórmula
* Unidad
* Frecuencia
* Línea Base
* Valor Objetivo
* Valor Actual

---

## Ejemplo

Indicador:

```text
Cobertura de Inventario Institucional
```

Fórmula:

```text
(Bienes registrados / Bienes identificados) × 100
```

Unidad:

```text
Porcentaje
```

---

# Indicadores Simples

Son calculados a partir de una única fuente de datos.

Ejemplo:

```text
Cobertura de Inventario
```

Fuente:

```text
Inventario
```

---

# Indicadores Compuestos

Son calculados a partir de múltiples fuentes.

Ejemplo:

```text
Riesgo de Deserción Escolar
```

Fuentes:

* Comunidad Educativa
* Académico
* Convivencia
* Asistencia
* Caracterización

---

# Reglas de Negocio

## RN-IND-001

Un componente puede tener múltiples indicadores.

---

## RN-IND-002

Un indicador puede ser alimentado por múltiples métricas operativas.

---

## RN-IND-003

Las métricas operativas no son indicadores.

---

# Seguimientos

## Definición

Permiten registrar la evolución de una meta o indicador durante su periodo de vigencia.

---

# Tipos

## Seguimiento Manual

Registrado por responsables institucionales.

Ejemplo:

```text
Actividad ejecutada al 45%.
```

---

## Seguimiento Automático

Calculado por APPSisGOE a partir de datos provenientes de módulos.

Ejemplo:

```text
Inventario reporta:

91%

Meta:

95%

Avance:

95.7%
```

---

# Frecuencia

Los seguimientos podrán realizarse:

* Mensualmente
* Bimestralmente
* Trimestralmente
* Semestralmente
* Anualmente

Según la naturaleza del indicador.

---

# Estados

## Meta

* Pendiente
* En Seguimiento
* Cumplida
* Incumplida

---

## Indicador

* Activo
* Suspendido
* Cerrado

---

## Seguimiento

* Registrado
* Validado
* Cerrado

---

# Beneficios del Modelo

* Planeación basada en evidencia.
* Seguimiento institucional permanente.
* Comparación anual de resultados.
* Construcción de indicadores institucionales.
* Integración con módulos operativos.
* Soporte para mejoramiento continuo.

---

# Dependencias

Este documento depende de:

* DDOM-GESTION-001
* DDOM-GESTION-002

---

# Estado de la Decisión

APROBADA

VIGENTE

OBLIGATORIA PARA TODO APPSisGOE

Base para:

* DDOM-GESTION-004
* DDOM-GESTION-005
* DDOM-GESTION-006
