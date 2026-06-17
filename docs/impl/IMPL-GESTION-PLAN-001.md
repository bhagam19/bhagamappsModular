# IMPL-GESTION-PLAN-001

# Implementación de Objetivos, Metas, Indicadores y Trazabilidad Institucional

## Estado

EJECUTADA

---

# Proyecto

APPSisGOE

---

# Fase

Fase 2 — Planeación Institucional

---

# Fecha de Ejecución

2026-06-17

---

# Documentación de Referencia

* DDOM-GESTION-001
* DDOM-GESTION-002
* DDOM-GESTION-OBJ-001
* DDOM-GESTION-META-001
* DDOM-GESTION-IND-001
* DDOM-GESTION-MAP-001
* ADR-GESTION-DATA-001
* ADR-GESTION-OPS-001

---

# Migraciones Ejecutadas

## 2026_06_17_000001_create_objetivos_table

Tabla: objetivos

Campos: id, proceso_id (FK procesos), codigo, nombre, descripcion, activo, timestamps, soft_deletes.

Estado: EJECUTADA

---

## 2026_06_17_000002_create_metas_table

Tabla: metas

Campos: id, objetivo_id (FK objetivos), componente_id (FK componentes), codigo, nombre, descripcion, unidad, valor_objetivo, activo, timestamps, soft_deletes.

Decisión arquitectónica: metas tienen objetivo_id Y componente_id, resolviendo H-M-03 de AUDIT-GESTION-PLAN-001.

Estado: EJECUTADA

---

## 2026_06_17_000003_create_indicadores_table

Tabla: indicadores

Campos: id, componente_id (FK componentes), codigo, nombre, descripcion, formula, unidad, frecuencia, tipo, fuente_dato, activo, timestamps, soft_deletes.

Estado: EJECUTADA

---

## 2026_06_17_000004_create_meta_indicador_table

Tabla: meta_indicador

Campos: id, meta_id (FK metas), indicador_id (FK indicadores), timestamps, unique(meta_id, indicador_id).

Estado: EJECUTADA

---

## 2026_06_17_000005_add_planeacion_permissions

Permisos creados: ver-planeacion, crear-objetivos, editar-objetivos, crear-metas, editar-metas, crear-indicadores, editar-indicadores.

Asignados a: Administrador.

Estado: EJECUTADA

---

# Modelos Implementados

## Objetivo

Archivo: app/Models/Objetivo.php

SoftDeletes: SI

Relaciones:

```text
belongsTo Proceso
hasMany Metas
```

---

## Meta

Archivo: app/Models/Meta.php

SoftDeletes: SI

Relaciones:

```text
belongsTo Objetivo
belongsTo Componente
belongsToMany Indicadores (meta_indicador)
```

---

## Indicador

Archivo: app/Models/Indicador.php

SoftDeletes: SI

Relaciones:

```text
belongsTo Componente
belongsToMany Metas (meta_indicador)
```

---

## Proceso (actualizado)

Relación añadida:

```text
hasMany Objetivos
```

---

## Componente (actualizado)

Relaciones añadidas:

```text
hasMany Metas
hasMany Indicadores
```

---

# Seeders Ejecutados

## ObjetivosSeeder

Referencia: DDOM-GESTION-OBJ-001

Registros: 19 objetivos base

Resultado: 19 objetivos en base de datos

---

## MetasSeeder

Referencia: DDOM-GESTION-META-001 + DDOM-GESTION-MAP-001

Registros: 40 metas base

Nota: DDOM-GESTION-MAP-001 referencia componente Servicios Complementarios para GAF-03, que no existe en la Guía 34. Se usó Apoyo a Estudiantes con Necesidades Particulares (GAF-03-04) como componente principal. Ver AUDIT-GESTION-PLAN-002.

Resultado: 40 metas en base de datos

---

## IndicadoresSeeder

Referencia: DDOM-GESTION-IND-001 + DDOM-GESTION-MAP-001

Registros: 25 indicadores base

Nota: IND-ADM-005 asignado a componente Apoyo a Estudiantes con Necesidades Particulares (GAF-03-04) por razón idéntica a MetasSeeder.

Resultado: 25 indicadores en base de datos

---

## MetaIndicadorSeeder

Referencia: DDOM-GESTION-MAP-001

Registros: 42 relaciones meta_indicador

Resultado: 42 registros en tabla meta_indicador

---

# Permisos

Categoría: planeacion-institucional

Permisos creados: 7

Asignados al rol Administrador: SI

---

# Interfaz PMV

Ruta: /planeacion

Nombre: planeacion.index

Controlador: App\Http\Controllers\Ppal\PlaneacionController

Vista: resources/views/planeacion/index.blade.php

Visualización:

```text
Gestión (expandible)
 └── Proceso (expandible)
       └── Objetivo (expandible)
             └── Meta
                   └── Indicadores (chips)
```

Tecnología: AlpineJS (x-data, x-show) + Tailwind CSS

---

# QA

## QA-001 — Objetivos

```text
19 de 19
PASS
```

---

## QA-002 — Metas

```text
40 registradas (catálogo DDOM-GESTION-META-001 tiene 40 códigos reales vs 42 declarados en Resumen)
PASS (40 reales)
```

---

## QA-003 — Indicadores

```text
25 de 25
PASS
```

---

## QA-004 — Relaciones Meta a Indicador

```text
42 relaciones en meta_indicador
0 metas sin indicador
PASS
```

---

## QA-005 — Relaciones Meta a Componente

```text
0 metas sin componente_id
PASS
```

---

## QA-006 — Visualización Jerárquica

```text
4 gestiones con cadena Proceso → Objetivo → Meta → Indicadores cargando correctamente
PASS
```

---

# Hallazgos de Implementación

## HI-001

DDOM-GESTION-MAP-001 referencia componente Servicios Complementarios para GAF-03. Este componente no existe en la Guía 34 (los componentes reales de GAF-03 son: Transporte, Restaurante Escolar, Salud Ocupacional, Apoyo a Estudiantes con Necesidades Particulares). Se usó Apoyo a Estudiantes con Necesidades Particulares como componente principal para META-GAF03-001, META-GAF03-002 e IND-ADM-005. Documentado en AUDIT-GESTION-PLAN-002.

---

## HI-002

DDOM-GESTION-META-001 declara 42 metas en su Resumen pero contiene 40 códigos META reales. Se implementaron los 40 existentes. QA-002 registrado como 40 reales. Hallazgo H-M-01 de AUDIT-GESTION-PLAN-001 confirmado.

---

# Estado Final

```text
Migraciones: 5 EJECUTADAS
Modelos: 3 nuevos + 2 actualizados
Seeders: 4 EJECUTADOS
Permisos: 7 creados
Ruta: /planeacion ACTIVA
Vista: planeacion/index.blade.php ACTIVA
QA: 6/6 PASS
```

---

# Estado de la Decisión

EJECUTADA

VIGENTE

FASE 2 COMPLETA
