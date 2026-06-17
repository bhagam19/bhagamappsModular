# IMPL-GESTION-OPS-001

# Implementación de Actividades, Tareas y Responsables Operativos

## Estado

EJECUTADA

---

# Proyecto

APPSisGOE

---

# Fase

Fase 3 — Operación Institucional

---

# Fecha de Ejecución

2026-06-17

---

# Documentación de Referencia

* PLAN-GESTION-OPS-001
* ADR-GESTION-OPS-001
* ADR-GESTION-OPS-002
* AUDIT-GESTION-OPS-001
* DDOM-GESTION-MAP-001

---

# Migraciones Ejecutadas

## 2026_06_17_000006_create_actividades_table

Tabla: actividades

Campos: id, meta_id (FK metas), componente_id (FK componentes), codigo, nombre, descripcion, estado (string), avance_manual (unsignedTinyInteger), avance_calculado (unsignedTinyInteger), fecha_inicio (date), fecha_fin (date), activo, timestamps, soft_deletes.

Estado: EJECUTADA

---

## 2026_06_17_000007_create_tareas_table

Tabla: tareas

Campos: id, actividad_id (FK actividades), responsable_tipo (string 20 nullable), responsable_id (unsignedBigInteger nullable), codigo, nombre, descripcion, estado (string), avance (unsignedTinyInteger), fecha_inicio (date), fecha_fin (date), activo, timestamps, soft_deletes.

Nota: responsable_id no tiene FK declarada — soporta polimorfismo hacia users, roles o dependencias según ADR-GESTION-OPS-002.

Estado: EJECUTADA

---

## 2026_06_17_000008_add_operacion_permissions

Permisos creados: ver-operacion, crear-actividades, editar-actividades, crear-tareas, editar-tareas.

Categoría: operacion-institucional.

Asignados a: Administrador.

Estado: EJECUTADA

---

# Modelos Implementados

## Actividad

Archivo: app/Models/Actividad.php

SoftDeletes: SI

Relaciones:

```text
belongsTo Meta
belongsTo Componente
hasMany Tareas
```

Método adicional:

```text
calcularAvance(): int
```

Calcula promedio simple del campo avance de las tareas activas asociadas.

---

## Tarea

Archivo: app/Models/Tarea.php

SoftDeletes: SI

Namespace de User: Modules\User\Entities\User (NO App\Models\User)

Relaciones:

```text
belongsTo Actividad
```

Métodos adicionales:

```text
responsable(): User|Role|Dependencia|null
```

Resuelve el responsable polimórfico según ADR-GESTION-OPS-002.

```text
nombre_responsable: string (accessor)
```

Retorna nombre legible del responsable.

---

## Meta (actualizada)

Relación añadida:

```text
hasMany Actividades
```

---

## Componente (actualizada)

Relación añadida:

```text
hasMany Actividades
```

---

# Controlador

Archivo: app/Http/Controllers/Ppal/OperacionController.php

Métodos:

```text
index()          — carga metas con actividades y tareas. Pasa usuarios, roles, dependencias y estados al view.
storeActividad() — crea actividad con validación completa.
updateActividad() — actualiza actividad y recalcula avance_calculado.
storeTarea()     — crea tarea y actualiza avance_calculado de la actividad padre.
updateTarea()    — actualiza tarea y recalcula avance_calculado de la actividad padre.
```

Estados válidos validados en capa de aplicación:

```text
Pendiente
En Proceso
Completada
Suspendida
Cancelada
```

---

# Interfaz PMV

Ruta: /operacion

Nombre: operacion.index

Controlador: App\Http\Controllers\Ppal\OperacionController

Vista: resources/views/operacion/index.blade.php

Tecnología: AlpineJS (x-data, x-show, x-cloak, @click, eventos personalizados) + Tailwind CSS

Visualización:

```text
Meta (expandible)
 └── Actividad (expandible con estado y barra de avance calculado)
       └── Tarea (con responsable, estado, barra de avance individual)
```

Funcionalidades implementadas:

```text
Crear Actividad     — formulario inline por meta
Editar Actividad    — formulario inline por actividad (nombre, descripción, estado, avance manual, fechas)
Crear Tarea         — formulario inline por actividad con selector dinámico de responsable
Editar Tarea        — formulario inline por tarea con reasignación de responsable
Asignar Responsable — selector dinámico usuario/rol/dependencia via AlpineJS
Actualizar Estado   — selector en formularios de edición
Actualizar Avance   — campo numérico en formularios de edición
```

---

# Rutas

```text
GET  /operacion                                    → operacion.index
POST /operacion/actividades                        → operacion.actividades.store
PUT  /operacion/actividades/{actividad}            → operacion.actividades.update
POST /operacion/actividades/{actividad}/tareas     → operacion.tareas.store
PUT  /operacion/tareas/{tarea}                     → operacion.tareas.update
```

Todas bajo middleware: auth:sanctum, jetstream.auth_session, verified.

---

# Cálculo de Avance

## Actividad

Dos campos implementados:

```text
avance_manual    — ingresado manualmente por el usuario
avance_calculado — promedio simple del avance de tareas activas
```

Se recalcula en:

```text
updateActividad()
storeTarea()
updateTarea()
```

## Tarea

Campo:

```text
avance — porcentaje individual (0-100)
```

---

# Implementación ADR-GESTION-OPS-002

Modelo polimórfico implementado íntegramente:

```text
responsable_tipo: usuario | rol | dependencia
responsable_id:  FK implícita a users.id | roles.id | dependencias.id
```

Namespace utilizado: Modules\User\Entities\User

---

# QA

## QA-001 — Migraciones

```text
actividades: EJECUTADA
tareas: EJECUTADA
add_operacion_permissions: EJECUTADA
PASS
```

---

## QA-002 — Relación Meta→Actividad

```text
Meta::actividades() existe — belongsTo declarado en ambos extremos
PASS
```

---

## QA-003 — Relación Actividad→Tarea

```text
Actividad::tareas() existe — hasMany Tarea declarado
PASS
```

---

## QA-004 — Resolución de responsable

```text
Tarea::responsable() resuelve usuario, rol y dependencia correctamente
Tarea::nombre_responsable (accessor) retorna nombre legible
PASS
```

---

## QA-005 — Permisos

```text
5/5 permisos creados en categoría operacion-institucional
5/5 permisos asignados al rol Administrador
PASS
```

---

## QA-006 — Vista /operacion

```text
Ruta operacion.index registrada
Vista resources/views/operacion/index.blade.php creada
Árbol Meta → Actividad → Tarea funcional
PASS
```

---

## QA-007 — Estados válidos

```text
Columna estado tipo string en actividades y tareas
Validación en capa de aplicación: in:Pendiente,En Proceso,Completada,Suspendida,Cancelada
PASS
```

---

## QA-008 — Avances almacenados

```text
actividades.avance_manual: unsignedTinyInteger (0-100)
actividades.avance_calculado: unsignedTinyInteger (0-100)
tareas.avance: unsignedTinyInteger (0-100)
PASS
```

---

## QA-009 — Cálculo de avance de actividad

```text
Actividad::calcularAvance() implementado
Recalcula avance_calculado en store/update de tareas y update de actividad
PASS
```

---

# Hallazgos de Implementación

## HI-001

Responsable polimórfico sin FK declarada en BD

Por diseño (ADR-GESTION-OPS-002 Alternativa C). La integridad referencial se garantiza en capa de aplicación. No bloquea funcionalidad.

---

## HI-002

Selector dinámico de responsable con AlpineJS

El selector de responsable_id se rellena dinámicamente según el tipo seleccionado (usuario/rol/dependencia) usando eventos personalizados de AlpineJS. Los datos se pasan como JSON desde el controlador.

---

# Estado Final

```text
Migraciones: 3 EJECUTADAS
Modelos: 2 nuevos + 2 actualizados
Permisos: 5 creados + asignados
Rutas: 5 registradas
Vista: /operacion ACTIVA
QA: 9/9 PASS
```

---

# Estado de la Decisión

EJECUTADA

VIGENTE

FASE 3 — OPERACIÓN INSTITUCIONAL: ACTIVA
