# PLAN-PORTABILIDAD-ASG-001

# Plan Maestro de Portabilidad APPSisGOE

## Estado

APROBADO

AUTORIZADO PARA EJECUCIÓN

---

# Contexto

La auditoría:

AUDIT-MIGRACION-ASG-001

identificó 79 artefactos desarrollados durante la iniciativa institucional APPSisGOE ejecutada en:

/home/adolfo/web/bhagamapps.com/private/bhagamappsModular

que deben ser trasladados hacia la instancia oficial:

/home/adolfo/web/bhagamapps.com/public_html

---

# Referencias Obligatorias

* AUDIT-MIGRACION-ASG-001
* ADR-MIGRACION-ASG-001

---

# Objetivo

Definir la estrategia detallada para incorporar a APPSisGOE Oficial los artefactos institucionales desarrollados entre:

DDOM-GESTION-001

y

PLAN-GESTION-DASH-001

sin afectar la estabilidad de producción.

---

# Principio Rector

La instancia oficial es:

/home/adolfo/web/bhagamapps.com/public_html

Todo artefacto proveniente de bhagamappsModular deberá adaptarse a la arquitectura oficial.

Nunca al contrario.

---

# Fase MP-01

## Clasificación de Artefactos

Generar matriz completa:

### Categoría A

Reutilización directa

Artefactos compatibles sin cambios.

---

### Categoría B

Adaptación

Artefactos que requieren modificaciones.

---

### Categoría C

Reimplementación

Artefactos incompatibles con la arquitectura oficial.

---

### Categoría D

Descarte

Artefactos que no serán migrados.

---

# Fase MP-02

## Portabilidad Documental

Inventariar:

* ADR
* DDOM
* PLAN
* AUDIT
* ROADMAP

Determinar ubicación final en APPSisGOE Oficial.

---

# Fase MP-03

## Portabilidad de Datos

Analizar:

* gestiones
* procesos
* componentes
* objetivos
* metas
* indicadores

Comparar schemas.

Definir migraciones de adaptación.

---

# Fase MP-04

## Portabilidad Operativa

Analizar:

* meta_indicador
* actividades
* tareas

Determinar compatibilidad.

---

# Fase MP-05

## Portabilidad de Código

Inventariar:

* modelos
* controladores
* rutas
* vistas

Clasificar:

Reutilizar / Adaptar / Reimplementar.

---

# Fase MP-06

## Seguridad

Analizar:

* permisos
* roles
* middleware

Determinar estrategia de integración con el RBAC oficial.

---

# Fase MP-07

## Plan de Ejecución

Generar cronograma de portabilidad.

Definir:

* orden de implementación
* riesgos
* dependencias
* estimación de esfuerzo

---

# Entregables

## Documento principal

Actualizar:

docs/plan/PLAN-PORTABILIDAD-ASG-001.md

---

## Matriz de Portabilidad

Crear:

docs/analysis/ANALYSIS-PORTABILIDAD-ASG-001.md

---

## Resultado Esperado

Obtener una hoja de ruta exacta para trasladar el CORE Institucional desarrollado en bhagamappsModular hacia APPSisGOE Oficial sin pérdida funcional ni riesgo para producción.

---

# Restricciones

No implementar.

No copiar archivos.

No modificar producción.

No ejecutar migraciones.

Solo planificar.

---

# Estado

APROBADO

AUTORIZADO POR PMO
