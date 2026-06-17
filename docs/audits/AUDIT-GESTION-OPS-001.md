# AUDIT-GESTION-OPS-001

# Auditoría Previa a la Implementación de Operación Institucional

## Estado

AUTORIZADA

---

# Tipo

Auditoría Técnica de Pre-Implementación

---

# Proyecto

APPSisGOE

---

# Fase

Fase 3 — Operación Institucional

---

# Objetivo

Verificar que la plataforma se encuentra preparada para iniciar la implementación de:

```text
Actividades
Tareas
Responsables
```

sin generar conflictos arquitectónicos, funcionales o de datos con los componentes ya implementados.

---

# Alcance

La auditoría deberá revisar:

## Infraestructura Institucional

* Gestiones
* Procesos
* Componentes

---

## Planeación Institucional

* Objetivos
* Metas
* Indicadores
* MetaIndicador

---

## Seguridad

* Usuarios
* Roles
* Permisos

---

## Dependencias

* Dependencias institucionales existentes
* Relación con usuarios

---

## Inventario

* Compatibilidad con futuras actividades operativas
* Compatibilidad con seguimiento institucional

---

# Validaciones Obligatorias

## AUD-OPS-001

### Integridad de Metas

Verificar:

```text
Todas las metas poseen objetivo asociado.
```

---

## AUD-OPS-002

### Integridad de Componentes

Verificar:

```text
Todas las metas poseen componente asociado.
```

---

## AUD-OPS-003

### Integridad de Indicadores

Verificar:

```text
Todas las metas poseen al menos un indicador.
```

---

## AUD-OPS-004

### Compatibilidad de Responsables

Validar existencia de:

```text
users
roles
dependencias
```

como posibles responsables operativos.

---

## AUD-OPS-005

### Compatibilidad de Relaciones

Validar que el modelo:

```text
Meta
 ↓
Actividad
 ↓
Tarea
```

puede implementarse sin conflictos con las relaciones existentes.

---

## AUD-OPS-006

### Compatibilidad de Permisos

Determinar permisos mínimos requeridos para:

```text
ver-operacion

crear-actividades
editar-actividades

crear-tareas
editar-tareas
```

---

## AUD-OPS-007

### Compatibilidad de Seguimiento

Validar soporte para:

```text
estado

avance

fecha_inicio

fecha_fin
```

---

## AUD-OPS-008

### Compatibilidad con Gantt Futuro

Validar que la arquitectura aprobada soporta:

```text
Visualización temporal futura
```

sin requerir refactorización.

---

# Hallazgos

Clasificar:

## Críticos

Impiden implementación.

---

## Altos

Requieren ajuste previo.

---

## Medios

Recomendados.

---

## Bajos

Mejoras futuras.

---

# Entregables

## Informe de Auditoría

Con:

* Hallazgos
* Riesgos
* Recomendaciones

---

## Veredicto

### Estado A

```text
APROBADO PARA IMPLEMENTACIÓN
```

---

### Estado B

```text
APROBADO CON AJUSTES
```

---

### Estado C

```text
NO APROBADO
```

---

# Restricciones

No modificar código.

No ejecutar migraciones.

No crear tablas.

No generar implementación.

Solo auditar.

---

# Resultado Esperado

Determinar si APPSisGOE se encuentra listo para iniciar:

```text
IMPL-GESTION-OPS-001
```

---

# Estado de la Auditoría

AUTORIZADA

PENDIENTE DE EJECUCIÓN

Aprobada por PMO
