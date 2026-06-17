# ADR-GESTION-OPS-002

# Modelo de Responsables Operativos

## Estado

APROBADO

---

# Tipo

Architecture Decision Record (ADR)

---

# Proyecto

APPSisGOE

---

# Fecha

2026-06-17

---

# Contexto

Durante la preparación de la Fase 3 — Operación Institucional, se identificó la necesidad de definir formalmente cómo se asignarán los responsables de actividades y tareas institucionales.

La decisión impacta directamente:

* Actividades
* Tareas
* Seguimiento
* Dashboard
* Gantt
* Notificaciones futuras
* Planes de Mejoramiento
* PMI

---

# Problema

Una tarea institucional puede ser responsabilidad de:

* Un usuario específico.
* Un rol institucional.
* Una dependencia institucional.

Ejemplos:

```text
Verificar laboratorio de química
→ Usuario específico
```

```text
Actualizar planes de área
→ Coordinadores Académicos
```

```text
Ejecutar mantenimiento preventivo
→ Dependencia de Servicios Generales
```

El modelo debe soportar estos escenarios sin generar duplicidad de estructuras.

---

# Alternativas Evaluadas

## Alternativa A

### Responsable por Usuario

```text
tareas

usuario_id
```

### Ventajas

* Muy simple.

### Desventajas

* No soporta roles.
* No soporta dependencias.
* Requiere rediseño futuro.

---

## Alternativa B

### Tabla Independiente de Responsables

```text
responsables

tipo
usuario_id
rol_id
dependencia_id
```

### Ventajas

* Flexible.

### Desventajas

* Mayor complejidad.
* Más tablas.
* Sobredimensionada para PMV.

---

## Alternativa C

### Responsable Polimórfico Simplificado

```text
tareas

responsable_tipo
responsable_id
```

Ejemplos:

```text
usuario / 25
```

```text
rol / 3
```

```text
dependencia / 7
```

---

# Decisión

APPSisGOE adopta oficialmente la Alternativa C.

---

# Modelo Oficial

## Tareas

Campos obligatorios:

```text
id

actividad_id

responsable_tipo

responsable_id

codigo
nombre
descripcion

estado
avance

fecha_inicio
fecha_fin

activo

created_at
updated_at
deleted_at
```

---

# Valores Permitidos

## responsable_tipo

Valores válidos:

```text
usuario
rol
dependencia
```

---

# Interpretación

## Usuario

```text
responsable_tipo = usuario
responsable_id = users.id
```

---

## Rol

```text
responsable_tipo = rol
responsable_id = roles.id
```

---

## Dependencia

```text
responsable_tipo = dependencia
responsable_id = dependencias.id
```

---

# Beneficios

## Simplicidad

No requiere tablas adicionales.

---

## Escalabilidad

Permite incorporar nuevos tipos de responsables.

---

## Compatibilidad

Compatible con:

* Users Module
* Dependencias
* Planeación
* Inventario
* Dashboard
* Gantt

---

## Notificaciones Futuras

Permite implementar posteriormente:

```text
Notificar responsable
```

independientemente del tipo asignado.

---

# Integración con Actividades

Las actividades no tendrán responsable directo obligatorio.

La responsabilidad operativa se asignará a nivel de tarea.

---

# Integración con Gantt

Las tareas constituirán la unidad mínima visible en el Gantt Institucional.

Por tanto, cada tarea deberá tener:

```text
Responsable
Estado
Avance
Fecha Inicio
Fecha Fin
```

---

# Restricciones

No implementar aún:

* Múltiples responsables por tarea.
* Equipos de trabajo.
* Dependencias entre tareas.
* Asignaciones automáticas.

Estas capacidades podrán incorporarse en fases posteriores.

---

# Consecuencias

## Positivas

* Implementación rápida.
* Menor complejidad.
* Compatible con PMV.
* Compatible con evolución futura.

---

## Negativas

* Una tarea solo podrá tener un responsable principal.
* Los equipos deberán modelarse posteriormente.

---

# Impacto

Este ADR se convierte en referencia obligatoria para:

```text
IMPL-GESTION-OPS-001

AUDIT-GESTION-OPS-001

Dashboard Institucional

Gantt Institucional
```

---

# Estado de la Decisión

APROBADA

VIGENTE

OBLIGATORIA PARA LA OPERACIÓN INSTITUCIONAL DE APPSisGOE
