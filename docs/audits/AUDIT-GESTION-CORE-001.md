# AUDIT-GESTION-CORE-001

# Certificación de Implementación — Infraestructura Institucional Base

## Estado

APROBADO

---

# Tipo

Auditoría de Implementación

---

# Fecha

2026-06-16

---

# Implementación Auditada

IMPL-GESTION-CORE-001

---

# Contexto

Este documento certifica la correcta implementación de la Fase 1 del CORE Institucional de APPSisGOE, correspondiente a la Infraestructura Institucional Base definida en ROADMAP-GESTION-001 y PLAN-GESTION-CORE-001.

---

# Artefactos Creados

## Migraciones

| Archivo | Estado |
|---|---|
| `2026_06_16_000001_create_gestiones_table.php` | EJECUTADA |
| `2026_06_16_000002_create_procesos_table.php` | EJECUTADA |
| `2026_06_16_000003_create_componentes_table.php` | EJECUTADA |
| `2026_06_16_000004_add_gestion_permissions.php` | EJECUTADA |

---

## Modelos Eloquent

| Archivo | Estado |
|---|---|
| `app/Models/Gestion.php` | CREADO |
| `app/Models/Proceso.php` | CREADO |
| `app/Models/Componente.php` | CREADO |

---

## Seeders

| Archivo | Estado |
|---|---|
| `database/seeders/GestionesSeeder.php` | EJECUTADO |
| `database/seeders/ProcesosSeeder.php` | EJECUTADO |
| `database/seeders/ComponentesSeeder.php` | EJECUTADO |

---

## Interfaz

| Archivo | Estado |
|---|---|
| `app/Http/Controllers/Ppal/GestionInstitucionalController.php` | CREADO |
| `resources/views/gestion/arbol.blade.php` | CREADO |
| Ruta `/gestion-institucional` → `gestion.arbol` | REGISTRADA |

---

# Verificación QA

## QA-001 — 4 Gestiones

```text
PASS
```

Resultado: 4 gestiones (GD, GA, GAF, GC)

---

## QA-002 — Procesos oficiales

```text
PASS
```

Resultado: 19 procesos

---

## QA-003 — Componentes oficiales

```text
PASS
```

Resultado: 89 componentes

---

## QA-004 — Relaciones Eloquent

```text
PASS
```

Gestion→hasMany(Proceso), Proceso→hasMany(Componente), SoftDeletes activos.

---

## QA-005 — Permisos

```text
PASS
```

9 permisos categoria `gestion-institucional` creados y asignados al rol Administrador.

---

## QA-006 — Vista jerárquica

```text
PASS
```

Ruta `/gestion-institucional` disponible con árbol Gestión → Proceso → Componente.

---

# Resumen de Datos Cargados

| Entidad | Total |
|---|---|
| Gestiones | 4 |
| Procesos | 19 |
| Componentes | 89 |
| Permisos | 9 |

---

# Reglas de Integridad Verificadas

| RI | Descripción | Estado |
|---|---|---|
| RI-001 | FK gestion_id en procesos | ACTIVA |
| RI-002 | FK proceso_id en componentes | ACTIVA |
| RI-003 | codigo UNIQUE en gestiones, procesos y componentes | ACTIVA |
| RI-004 | SoftDeletes en las tres tablas | ACTIVO |

---

# Exclusiones Confirmadas

Las siguientes entidades NO fueron implementadas en esta fase, conforme al alcance definido en IMPL-GESTION-CORE-001:

* objetivos
* metas
* indicadores
* meta_indicador
* seguimientos
* actividades
* tareas
* responsables
* evidencias
* metricas_operativas
* dashboard institucional
* gantt institucional

---

# Estado

IMPLEMENTACIÓN CERTIFICADA

LISTA PARA FASE 2 — PLANEACIÓN INSTITUCIONAL

---

# Siguiente Fase

IMPL-GESTION-PLAN-001
