# AUDIT-GESTION-PLAN-002

# Auditoría de Certificación — Fase 2 Planeación Institucional

## Estado

EJECUTADA

---

# Tipo

Auditoría de Certificación Post-Implementación

---

# Proyecto

APPSisGOE

---

# Fecha

2026-06-17

---

# Implementación Auditada

IMPL-GESTION-PLAN-001

---

# Objetivo

Certificar la correcta implementación de la Fase 2 del CORE Institucional de APPSisGOE:

```text
Objetivos
Metas
Indicadores
meta_indicador
Permisos
Vista PMV
```

---

# Resultados QA

## QA-001 — Objetivos

Esperados: 19

Registrados: 19

```text
PASS
```

---

## QA-002 — Metas

Esperados según spec: 42

Registrados: 40

Nota: DDOM-GESTION-META-001 declara 42 en su Resumen pero contiene 40 códigos META reales. Se implementaron los 40 existentes. Hallazgo H-M-01 de AUDIT-GESTION-PLAN-001 confirmado en implementación.

```text
PASS — 40 metas reales implementadas
```

---

## QA-003 — Indicadores

Esperados: 25

Registrados: 25

```text
PASS
```

---

## QA-004 — Relaciones Meta a Indicador

Relaciones meta_indicador: 42

Metas sin indicador: 0

```text
PASS
```

---

## QA-005 — Relaciones Meta a Componente

Metas sin componente_id: 0

```text
PASS
```

---

## QA-006 — Visualización Jerárquica

Ruta /planeacion activa.

Cadena Gestión → Proceso → Objetivo → Meta → Indicadores cargando correctamente.

4 gestiones con datos completos.

```text
PASS
```

---

# Hallazgos de Certificación

## HC-001 — Componente Servicios Complementarios inexistente

Severidad: MEDIO

DDOM-GESTION-MAP-001 referencia el componente Servicios Complementarios para asignación de META-GAF03-001, META-GAF03-002 e IND-ADM-005. Este componente no existe en la Guía 34 ni en la base de datos.

Componentes reales de GAF-03 (Administración de Servicios Complementarios):

```text
GAF-03-01: Transporte
GAF-03-02: Restaurante Escolar
GAF-03-03: Salud Ocupacional
GAF-03-04: Apoyo a Estudiantes con Necesidades Particulares
```

Resolución aplicada: se usó Apoyo a Estudiantes con Necesidades Particulares (GAF-03-04) como componente principal. Esta asignación es funcional pero no está explícitamente respaldada por DDOM-GESTION-MAP-001.

Recomendación: actualizar DDOM-GESTION-MAP-001 para especificar un componente válido de GAF-03.

---

## HC-002 — Discrepancia numérica confirmada en DDOM-GESTION-META-001

Severidad: BAJO

El Resumen de DDOM-GESTION-META-001 declara 42 metas. El conteo real de códigos META en el documento es 40. La implementación refleja los 40 códigos reales. Hallazgo H-M-01 de AUDIT-GESTION-PLAN-001 confirmado.

Recomendación: corregir el Resumen de DDOM-GESTION-META-001 a 40, o añadir los 2 metas faltantes.

---

# Artefactos Verificados

| Artefacto | Estado |
|---|---|
| database/migrations/2026_06_17_000001_create_objetivos_table.php | EJECUTADA |
| database/migrations/2026_06_17_000002_create_metas_table.php | EJECUTADA |
| database/migrations/2026_06_17_000003_create_indicadores_table.php | EJECUTADA |
| database/migrations/2026_06_17_000004_create_meta_indicador_table.php | EJECUTADA |
| database/migrations/2026_06_17_000005_add_planeacion_permissions.php | EJECUTADA |
| app/Models/Objetivo.php | CREADO |
| app/Models/Meta.php | CREADO |
| app/Models/Indicador.php | CREADO |
| app/Models/Proceso.php | ACTUALIZADO (hasMany Objetivos) |
| app/Models/Componente.php | ACTUALIZADO (hasMany Metas, hasMany Indicadores) |
| database/seeders/ObjetivosSeeder.php | EJECUTADO — 19 registros |
| database/seeders/MetasSeeder.php | EJECUTADO — 40 registros |
| database/seeders/IndicadoresSeeder.php | EJECUTADO — 25 registros |
| database/seeders/MetaIndicadorSeeder.php | EJECUTADO — 42 relaciones |
| app/Http/Controllers/Ppal/PlaneacionController.php | CREADO |
| resources/views/planeacion/index.blade.php | CREADA |
| routes/web.php | ACTUALIZADO (/planeacion) |

---

# Veredicto

```text
FASE 2 CERTIFICADA
```

6 de 6 validaciones QA: PASS

2 hallazgos menores identificados (HC-001, HC-002). No bloquean funcionalidad.

APPSisGOE dispone de infraestructura completa de Planeación Institucional:

```text
Gestión → Proceso → Objetivo → Meta ↔ Indicador
```

con trazabilidad completa a Componente y preparada para Fase 3 (Operación Institucional).

---

# Estado

CERTIFICADA

FASE 2 — PLANEACIÓN INSTITUCIONAL: COMPLETA
