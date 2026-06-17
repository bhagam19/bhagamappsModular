# IMPL-GESTION-CORE-001

# Implementación de Gestiones, Procesos y Componentes del CORE Institucional

## Estado

AUTORIZADA

---

# Tipo

Documento de Implementación

---

# Proyecto

APPSisGOE

---

# Contexto

Esta implementación corresponde a la Fase 1 definida en:

* ROADMAP-GESTION-001
* PLAN-GESTION-CORE-001

y se encuentra respaldada por:

* DDOM-GESTION-001
* DDOM-GESTION-002
* DDOM-GESTION-DATA-001
* ADR-GESTION-DATA-001
* AUDIT-GESTION-MEN-001

---

# Objetivo

Implementar la estructura institucional oficial de APPSisGOE basada en la Guía 34 del MEN.

La implementación debe dejar operativa la jerarquía:

```text
Gestión
 └── Proceso
       └── Componente
```

como parte permanente del CORE institucional.

---

# Alcance

## Incluye

### Base de Datos

Implementar las tablas:

```text
gestiones
procesos
componentes
```

---

### Modelos

Implementar:

```text
Gestion
Proceso
Componente
```

---

### Relaciones

```php
Gestion::hasMany(Proceso::class);

Proceso::belongsTo(Gestion::class);
Proceso::hasMany(Componente::class);

Componente::belongsTo(Proceso::class);
```

---

### Seeders

Implementar:

```text
GestionesSeeder
ProcesosSeeder
ComponentesSeeder
```

con la totalidad de la estructura definida en DDOM-GESTION-001.

---

### Permisos

Implementar:

```text
ver-gestiones
crear-gestiones
editar-gestiones

ver-procesos
crear-procesos
editar-procesos

ver-componentes
crear-componentes
editar-componentes
```

---

### Interfaz Inicial

Implementar una vista institucional jerárquica para validación funcional.

No se requiere CRUD completo en esta fase.

---

# Exclusiones

No implementar todavía:

```text
objetivos
metas
indicadores
meta_indicador
seguimientos
actividades
tareas
responsables
evidencias
metricas_operativas
dashboard institucional
gantt institucional
```

Estas funcionalidades pertenecen a fases posteriores.

---

# Diseño de Base de Datos

## Tabla: gestiones

Campos:

```text
id
codigo
nombre
descripcion
orden
activo
created_at
updated_at
deleted_at
```

Restricciones:

```text
codigo UNIQUE
```

Implementar SoftDeletes.

---

## Tabla: procesos

Campos:

```text
id
gestion_id
codigo
nombre
descripcion
orden
activo
created_at
updated_at
deleted_at
```

Restricciones:

```text
gestion_id FK
codigo UNIQUE
```

Implementar SoftDeletes.

---

## Tabla: componentes

Campos:

```text
id
proceso_id
codigo
nombre
descripcion
orden
activo
created_at
updated_at
deleted_at
```

Restricciones:

```text
proceso_id FK
codigo UNIQUE
```

Implementar SoftDeletes.

---

# Reglas de Integridad

## RI-001

No puede existir un proceso sin gestión.

---

## RI-002

No puede existir un componente sin proceso.

---

## RI-003

Los códigos institucionales deben ser únicos.

---

## RI-004

Las eliminaciones serán lógicas mediante SoftDeletes.

---

# Seeders Obligatorios

## Gestiones

```text
GD
Gestión Directiva

GA
Gestión Académica

GAF
Gestión Administrativa y Financiera

GC
Gestión de la Comunidad
```

---

## Procesos

Implementar todos los procesos definidos en DDOM-GESTION-001.

---

## Componentes

Implementar todos los componentes definidos en DDOM-GESTION-001.

No omitir ninguno.

---

# Permisos

Registrar los permisos dentro de la infraestructura RBAC existente.

Asignar inicialmente:

```text
Administrador
```

con acceso completo.

---

# Interfaz Mínima Requerida

Implementar una vista tipo árbol institucional.

Ejemplo:

```text
▶ Gestión Directiva

▶ Gestión Académica

▶ Gestión Administrativa y Financiera

▶ Gestión de la Comunidad
```

Expansión:

```text
▼ Gestión Académica

   ▼ Seguimiento Académico

      • Seguimiento a Resultados Académicos
      • Uso Pedagógico de Evaluaciones Externas
      • Seguimiento a la Asistencia
      • Actividades de Recuperación
      • Apoyo Pedagógico
      • Seguimiento a Egresados
```

---

# QA Obligatorio

## QA-001

Existen exactamente:

```text
4 gestiones
```

---

## QA-002

Existen todos los procesos oficiales documentados.

---

## QA-003

Existen todos los componentes oficiales documentados.

---

## QA-004

Las relaciones Eloquent funcionan correctamente.

---

## QA-005

Los permisos fueron registrados correctamente.

---

## QA-006

La vista jerárquica representa correctamente:

```text
Gestión
→ Proceso
→ Componente
```

---

# Entregables Esperados

## Código

* Migraciones
* Modelos
* Seeders
* Permisos
* Vista jerárquica

---

## Auditoría

Crear:

```text
docs/audits/AUDIT-GESTION-CORE-001.md
```

---

## Changelog

Actualizar:

```text
CHANGELOG.md
```

---

# Commit Obligatorio

Formato:

```text
[CORE] Implementa estructura institucional base (IMPL-GESTION-CORE-001)
```

---

# Push Obligatorio

Realizar push al repositorio remoto.

---

# Resultado Esperado

Al finalizar esta implementación, APPSisGOE dispondrá de la estructura institucional oficial de la Guía 34 persistida en la base de datos y disponible para ser utilizada por:

* Inventario
* Comunidad Educativa
* Planeación Institucional
* Calidad
* Riesgo de Deserción
* Dashboard Institucional
* Módulos futuros

---

# Estado de la Autorización

AUTORIZADA

APROBADA POR PMO

LISTA PARA EJECUCIÓN
