# ADR-007 — Strategic Decisions Registry

**Estado:** Aceptado
**Fecha:** 2026-06-08
**Contexto:** BhagamApps Modular — Gobierno Estratégico y Trazabilidad de Decisiones

---

# Contexto

BhagamAppsModular ha evolucionado desde un proyecto centrado en desarrollo técnico hacia una plataforma gestionada mediante procesos formales de:

* Arquitectura (ADR).
* Planeación (PMP).
* Roadmap.
* Auditorías (AUDIT).
* Implementaciones (IMPL).
* Baselines.
* Changelog y Versionado.

Durante esta evolución han surgido decisiones estratégicas tomadas por Dirección General que impactan:

* Priorización del roadmap.
* Aprobación de auditorías.
* Aprobación de implementaciones.
* Gestión de riesgos.
* Gobierno documental.
* Arquitectura futura.

Actualmente dichas decisiones quedan registradas únicamente en conversaciones y no poseen un mecanismo formal de persistencia dentro del repositorio.

La ausencia de un registro oficial genera riesgos de:

* Pérdida de contexto histórico.
* Decisiones no trazables.
* Dificultad para justificar cambios futuros.
* Dependencia de conversaciones externas al repositorio.

---

# Decisión

Se establece un Registro Oficial de Decisiones Estratégicas de Dirección General.

La ubicación oficial será:

```text
docs/dg/
```

Cada decisión será almacenada como un documento independiente.

---

# Formato de Identificación

Las decisiones utilizarán la nomenclatura:

```text
DG-XXX
```

Ejemplos:

```text
DG-013
DG-014
DG-015
DG-016
```

La numeración será secuencial y nunca reutilizable.

Los números retirados u obsoletos permanecerán reservados.

---

# Estructura de Archivos

```text
docs/
└── dg/
    ├── DG-013-*.md
    ├── DG-014-*.md
    ├── DG-015-*.md
    └── ...
```

El nombre del archivo deberá describir brevemente la decisión.

Ejemplo:

```text
DG-013-RBAC-System-Assessment.md
```

---

# Estructura Mínima de una Decisión DG

Cada documento deberá contener:

```markdown
# DG-XXX — Título

Estado: Aprobada
Fecha: YYYY-MM-DD

## Contexto

## Decisión

## Justificación

## Impacto

## Documentos relacionados

## Estado
```

---

# Ciclo de Vida

Toda decisión DG tendrá uno de los siguientes estados:

## Propuesta

La decisión ha sido planteada pero aún no aprobada.

## Aprobada

La decisión ha sido aceptada por Dirección General.

## Sustituida

La decisión fue reemplazada por una decisión posterior.

Debe indicar explícitamente:

```text
Sustituida por: DG-XXX
```

## Obsoleta

La decisión dejó de ser relevante debido a cambios en el proyecto.

---

# Relación con ADR

Las decisiones DG no sustituyen los ADR.

## ADR

Define:

```text
Arquitectura
Diseño
Principios técnicos
```

## DG

Define:

```text
Prioridades
Aprobaciones
Gobierno
Dirección estratégica
```

Cuando una decisión implique un cambio arquitectónico permanente deberá generar o modificar un ADR.

---

# Relación con PMP

Las decisiones DG pueden:

* Aprobar planes.
* Repriorizar iniciativas.
* Cambiar objetivos.

Toda decisión que altere el PMP deberá referenciarlo explícitamente.

---

# Relación con ROADMAP

Las decisiones DG pueden:

* Adelantar iniciativas.
* Posponer iniciativas.
* Cancelar iniciativas.

Toda modificación al roadmap deberá quedar documentada mediante una decisión DG.

---

# Relación con AUDIT

Las auditorías pueden recomendar acciones.

Sin embargo:

```text
Una auditoría no toma decisiones.
```

La decisión final corresponde a Dirección General.

Cuando una auditoría sea aprobada o rechazada formalmente, deberá existir una decisión DG asociada.

---

# Relación con PLAN-IMPL

Los planes proponen acciones.

La autorización de ejecución corresponde a Dirección General.

Cuando un PLAN-IMPL sea aprobado, la aprobación podrá registrarse mediante una decisión DG.

---

# Cuándo Crear una Decisión DG

Se deberá crear una decisión DG cuando ocurra cualquiera de los siguientes eventos:

* Aprobación de una auditoría.
* Rechazo de una auditoría.
* Aprobación de un PLAN-IMPL.
* Cambio de prioridad del roadmap.
* Cierre de riesgos estratégicos.
* Cambio de dirección del proyecto.
* Creación o retiro de módulos principales.
* Aprobación de iniciativas mayores.

---

# Consecuencias

## Positivas

* Trazabilidad estratégica completa.
* Independencia respecto a conversaciones externas.
* Historial verificable de decisiones.
* Mejor relación entre gobierno y ejecución.
* Facilita futuras auditorías.

## Negativas

* Mayor volumen documental.
* Requiere disciplina para mantener actualizado el registro.

---

# Alternativas Consideradas

## Registrar decisiones dentro del PMP

Descartado.

El PMP debe contener planificación, no historial de decisiones.

## Registrar decisiones dentro de ADR

Descartado.

Los ADR documentan arquitectura, no dirección estratégica.

## No registrar decisiones

Descartado.

Genera dependencia de conversaciones externas y pérdida de trazabilidad.

---

# Implementación Inicial

La primera carga histórica deberá incluir:

```text
DG-013
DG-014
DG-015
DG-016
DG-017
DG-018
```

Estas decisiones deberán persistirse posteriormente como documentos individuales dentro de:

```text
docs/dg/
```

---

# Estado de Implementación

| Componente | Estado      |
| ---------- | ----------- |
| ADR-007    | ✅ Aprobado  |
| docs/dg/   | ⏳ Pendiente |
| DG-013     | ⏳ Pendiente |
| DG-014     | ⏳ Pendiente |
| DG-015     | ⏳ Pendiente |
| DG-016     | ⏳ Pendiente |
| DG-017     | ⏳ Pendiente |
| DG-018     | ⏳ Pendiente |

---

# Decisión Final

BhagamAppsModular adopta oficialmente un Registro de Decisiones Estratégicas de Dirección General mediante documentos DG-XXX almacenados en:

```text
docs/dg/
```

Este registro complementa, pero no reemplaza, los ADR, PMP, ROADMAP, AUDIT e IMPL del proyecto.
