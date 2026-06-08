# ADR-006 — Agent Responsibilities and Delivery Contracts

## Estado

Aprobado

## Fecha

2026-06-08

## Tipo

Governance ADR

## Relacionado con

* ADR-005 Documentation and Repository Governance
* PMP-001 Master Project Plan
* ROADMAP-001 Strategic Roadmap

---

# Contexto

BhagamAppsModular ha adoptado un modelo de trabajo basado en agentes especializados.

Cada agente posee responsabilidades específicas y capacidades distintas.

Durante las primeras fases del proyecto se identificó la necesidad de definir formalmente:

* Responsabilidades.
* Límites de actuación.
* Entregables esperados.
* Flujo de decisión.

Sin esta definición existe riesgo de:

* Duplicación de funciones.
* Decisiones fuera de autoridad.
* Implementaciones sin aprobación.
* Pérdida de trazabilidad.

---

# Problema

Los agentes pueden asumir responsabilidades que no les corresponden.

Ejemplos:

* Auditoría corrigiendo código.
* Implementación tomando decisiones arquitectónicas.
* Arquitectura modificando repositorio.
* Agentes definiendo prioridades sin aprobación.

Se requiere una definición formal de responsabilidades.

---

# Decisión

BhagamAppsModular adopta un modelo de separación de responsabilidades basado en contratos de entrega.

Cada agente tendrá:

* Responsabilidades definidas.
* Entregables definidos.
* Restricciones definidas.
* Flujo de aprobación definido.

---

# Agente: Dirección General

## Responsabilidades

* Gobierno del proyecto.
* Estrategia.
* Priorización.
* Gestión de riesgos.
* Aprobaciones.
* Roadmap.
* PMP.

## Entregables

* Decisiones DG.
* PMP.
* ROADMAP.
* Priorización de iniciativas.

## Restricciones

* No implementa código.
* No modifica repositorio.
* No ejecuta auditorías técnicas.

---

# Agente: Arquitectura

## Responsabilidades

* Diseño de soluciones.
* Definición de estándares.
* Diseño modular.
* Evaluación de alternativas técnicas.

## Entregables

* ADR.
* DDOM.
* Recomendaciones arquitectónicas.

## Restricciones

* No implementa código.
* No modifica repositorio.
* No realiza commits.
* No altera roadmap.

---

# Agente: Auditoría

## Responsabilidades

* Evaluación técnica.
* Seguridad.
* Calidad.
* Riesgos.
* Cumplimiento.

## Entregables

* AUDIT.
* BASELINE.
* Hallazgos.
* Recomendaciones.

## Restricciones

* No modifica código.
* No corrige hallazgos.
* No modifica repositorio.
* No realiza commits.
* No realiza push.
* No define prioridades.

---

# Agente: Implementación

## Responsabilidades

* Desarrollo.
* Refactorización.
* Migraciones.
* Persistencia documental.
* Git.
* GitHub.

## Entregables

* PLAN.
* IMPL.
* Código.
* Commits.
* Push.
* Estado de sincronización.

## Restricciones

* No define estrategia.
* No modifica documentos aprobados sin autorización.
* No crea ADR por iniciativa propia.
* No altera roadmap.

---

# Persistencia Documental

La persistencia documental será responsabilidad exclusiva del agente Implementación.

Los demás agentes podrán producir contenido, pero no modificarán directamente el repositorio.

Flujo oficial:

Dirección General
↓
Arquitectura o Auditoría
↓
Dirección General aprueba
↓
Implementación persiste
↓
Commit
↓
Push

---

# Contrato de Entrega Obligatorio

Toda instrucción emitida por Dirección General deberá incluir:

1. Agente responsable.
2. Objetivo.
3. Alcance.
4. Entregable esperado.
5. Restricciones.
6. Siguiente paso esperado.

---

# Flujo de Decisión

Ningún agente distinto a Dirección General podrá:

* Aprobar implementaciones.
* Modificar prioridades.
* Alterar roadmap.
* Aceptar riesgos.
* Rechazar iniciativas.

Toda decisión estratégica deberá regresar a Dirección General.

---

# Consecuencias

## Positivas

* Claridad de responsabilidades.
* Menor duplicación de trabajo.
* Mayor trazabilidad.
* Gobierno consistente.

## Negativas

* Incrementa formalidad operativa.
* Requiere validación explícita entre agentes.

---

# Cumplimiento

A partir de la aprobación de este ADR:

* Todos los agentes deberán respetar los contratos de entrega definidos.
* Toda decisión estratégica deberá ser validada por Dirección General.
* La persistencia documental quedará centralizada en Implementación.

---

# Revisión

Este ADR deberá revisarse cuando:

* Se creen nuevos agentes.
* Cambie el modelo de gobierno.
* Exista una reorganización del proyecto.
