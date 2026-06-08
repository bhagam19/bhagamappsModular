# ROADMAP-001 — Hoja de Ruta Estratégica

## BhagamAppsModular

**Versión:** 1.0
**Estado:** Aprobado
**Periodo:** Junio 2026 – Junio 2027
**Propietario:** Dirección General

---

# 1. Propósito

Este documento define la hoja de ruta estratégica para la evolución de BhagamAppsModular durante el periodo 2026–2027.

Su objetivo es transformar las directrices definidas en PMP-001 en fases ejecutables con hitos, prioridades y criterios de éxito medibles.

---

# 2. Objetivo Principal

Durante el periodo de vigencia de este roadmap, BhagamAppsModular deberá alcanzar:

```text
Operación completa y estable del módulo Inventario.
```

---

# 3. Estado Inicial

Según BASELINE-001:

## Fortalezas

* GitHub sincronizado.
* Arquitectura modular estable.
* Sistema RBAC operativo.
* Documentación consolidada.
* Gobierno documental establecido.

## Debilidades

* Inventario sin datos operativos.
* Catálogos vacíos.
* Email no funcional.
* Cobertura de pruebas inexistente.
* Riesgos técnicos pendientes.

---

# 4. Fase 1 — Gobierno y Estabilización

## Periodo

Junio 2026

## Objetivo

Completar la estructura de gobierno y corregir riesgos críticos.

### Hitos

* ADR-005 publicado.
* PMP-001 publicado.
* ROADMAP-001 publicado.
* BASELINE-001 publicado.

### Implementaciones

* IMPL-004 — FLOAT → DECIMAL.
* AUDIT-004 — API Authentication.
* AUDIT-005 — Production Configuration.

### Criterio de éxito

Todos los riesgos críticos identificados en BASELINE-001 deben quedar mitigados.

---

# 5. Fase 2 — Inventario Operativo

## Periodo

Junio – Agosto 2026

## Objetivo

Preparar el sistema para operación institucional real.

### Hitos

* Catálogos completos.
* Seeders institucionales.
* Datos de referencia cargados.
* Validación funcional de procesos.

### Implementaciones

* IMPL-007 — Seeders de Inventario.
* Carga inicial de bienes.
* Validación de responsables.
* Validación de dependencias.

### Criterio de éxito

Registro exitoso de bienes reales dentro del sistema.

---

# 6. Fase 3 — Trazabilidad Completa

## Periodo

Septiembre – Noviembre 2026

## Objetivo

Garantizar trazabilidad integral sobre todos los movimientos de inventario.

### Hitos

* Diseño ActivityLog.
* Implementación ActivityLog.
* Auditoría de eventos.
* Reportes de trazabilidad.

### Implementaciones

* ActivityLog v1.
* Auditorías automáticas.
* Historial consolidado.

### Criterio de éxito

Toda modificación de inventario debe quedar registrada y auditada.

---

# 7. Fase 4 — Consolidación Operativa

## Periodo

Diciembre 2026 – Marzo 2027

## Objetivo

Mejorar estabilidad, rendimiento y mantenibilidad.

### Hitos

* Optimización de consultas.
* Refactorización de componentes complejos.
* Reducción de deuda técnica.

### Implementaciones

* Mejoras de rendimiento.
* Optimización Livewire.
* Revisión de seguridad.

### Criterio de éxito

Sistema estable bajo operación continua.

---

# 8. Fase 5 — Calidad y Madurez

## Periodo

Abril – Junio 2027

## Objetivo

Incrementar calidad técnica y sostenibilidad del proyecto.

### Hitos

* Primer conjunto de pruebas automatizadas.
* Documentación técnica completa.
* Manuales operativos.
* Auditoría general del sistema.

### Implementaciones

* Tests de Inventario.
* Tests de Usuarios.
* Manual de Usuario.
* Manual de Administrador.

### Criterio de éxito

Proyecto preparado para nuevas versiones mayores.

---

# 9. Riesgos Estratégicos

## R-01

Carga de datos con estructura incorrecta.

Mitigación:

* IMPL-004.

---

## R-02

Falta de adopción institucional.

Mitigación:

* Validación temprana con usuarios reales.

---

## R-03

Deuda técnica acumulada.

Mitigación:

* Auditorías periódicas.
* Refactorización continua.

---

## R-04

Documentación desactualizada.

Mitigación:

* Cumplimiento obligatorio de ADR-005.

---

# 10. Indicadores de Éxito

## Gobierno

* 100% de documentos oficiales en GitHub.

## Inventario

* Bienes reales registrados.
* Procesos operativos funcionando.

## Seguridad

* Riesgos críticos cerrados.

## Calidad

* Auditorías periódicas realizadas.

## Trazabilidad

* ActivityLog operativo.

---

# 11. Dependencias

Este roadmap depende de:

* PMP-001.
* ADR-005.
* BASELINE-001.
* DEVELOPMENT_WORKFLOW.md.

---

# 12. Revisión

ROADMAP-001 deberá revisarse:

* Trimestralmente.
* Al cierre de cada fase.
* Ante cambios significativos de alcance.

---

# Aprobación

Aprobado por Dirección General.

Fecha de aprobación: 2026-06-08.
