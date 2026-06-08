# PLAN-IMPL-004 — Migración de FLOAT a DECIMAL para valores monetarios

## Estado

Aprobado

## Fecha

2026-06-08

## Relacionado con

* BASELINE-001
* PMP-001
* ROADMAP-001

---

# 1. Antecedentes

Durante la auditoría BASELINE-001 se identificó una deuda técnica crítica en el módulo Inventario.

La tabla:

```sql
bienes
```

contiene el campo:

```sql
precio FLOAT
```

El uso de FLOAT para almacenar valores monetarios puede generar errores de precisión debido a la representación binaria de números decimales.

---

# 2. Problema

Los valores monetarios requieren exactitud.

FLOAT puede producir:

```text
1500000.00
↓
1499999.875
```

o diferencias acumulativas en cálculos posteriores.

Esto representa un riesgo para:

* Inventario institucional.
* Avalúos.
* Reportes.
* Actas.
* Exportaciones.

---

# 3. Justificación

BASELINE-001 confirmó:

```text
Tabla bienes = 0 registros
```

Por lo tanto:

* No existen datos que migrar.
* No existe riesgo de pérdida de información.
* El costo de corrección es mínimo.

La corrección debe realizarse antes de la carga de bienes reales.

---

# 4. Objetivo

Modificar el esquema de datos para reemplazar:

```sql
precio FLOAT
```

por:

```sql
precio DECIMAL(12,2)
```

---

# 5. Alcance

Incluye:

* Migración de base de datos.
* Revisión de modelos.
* Revisión de validaciones.
* Revisión de componentes Livewire.
* Revisión de exportaciones.
* Revisión de reportes.
* Revisión de actas PDF.

---

# 6. Exclusiones

No incluye:

* Carga de bienes.
* Cambios funcionales.
* Nuevos campos.
* Refactorizaciones no relacionadas.

---

# 7. Actividades

## A-01

Verificar estructura actual de la tabla bienes.

---

## A-02

Crear migración:

```text
FLOAT
↓
DECIMAL(12,2)
```

---

## A-03

Ejecutar migración.

---

## A-04

Verificar integridad del esquema.

---

## A-05

Revisar:

* Modelos.
* Requests.
* Validaciones.
* Componentes Livewire.

---

## A-06

Revisar:

* Reportes.
* Exportaciones.
* Actas PDF.

---

## A-07

Documentar resultados.

---

# 8. Riesgos

## R-01

Algún componente podría asumir que el valor es FLOAT.

Mitigación:

* Revisión completa del código relacionado.

---

## R-02

Cambio de esquema incorrecto.

Mitigación:

* Verificación posterior a la migración.

---

# 9. Criterios de Éxito

La implementación será exitosa cuando:

* precio sea DECIMAL(12,2).
* No existan errores funcionales.
* No existan regresiones visibles.
* La migración quede registrada en Git.
* IMPL-004 sea aprobado.

---

# 10. Entregables

* Migración ejecutada.
* Código validado.
* IMPL-004.
* Commit.
* Push.
* GitHub sincronizado.

---

# 11. Aprobación

Aprobado por Dirección General.

Fecha de aprobación: 2026-06-08.
