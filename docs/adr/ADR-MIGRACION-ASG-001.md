# ADR-MIGRACION-ASG-001

# Resolución de Bloqueantes de Portabilidad APPSisGOE

## Estado

APROBADO

---

# Contexto

La auditoría:

```text
AUDIT-MIGRACION-ASG-001
```

identificó dos bloqueantes críticos para la migración de los artefactos desarrollados entre:

```text
DDOM-GESTION-001
```

y

```text
PLAN-GESTION-DASH-001
```

desde:

```text
/home/adolfo/web/bhagamapps.com/private/bhagamappsModular
```

hacia:

```text
/home/adolfo/web/bhagamapps.com/public_html
```

Instancia oficial de APPSisGOE.

---

# Bloqueante B-001

## Incompatibilidad de Schema

Las tablas:

```text
gestiones
procesos
componentes
objetivos
metas
indicadores
```

existen en ambos proyectos pero presentan diferencias estructurales.

---

## Alternativas evaluadas

### Alternativa A

Modificar el schema oficial de APPSisGOE para hacerlo idéntico al schema de bhagamappsModular.

### Alternativa B

Conservar el schema oficial de APPSisGOE y adaptar los artefactos migrados.

---

## Decisión

Se adopta la:

```text
ALTERNATIVA B
```

---

## Justificación

APPSisGOE Oficial es:

```text
/home/adolfo/web/bhagamapps.com/public_html
```

Por tanto:

* El schema oficial es el existente en APPSisGOE.
* No se realizarán reemplazos masivos de tablas.
* No se ejecutarán migraciones destructivas.
* Los artefactos provenientes de bhagamappsModular deberán adaptarse al modelo existente.

---

## Consecuencia Arquitectónica

Las migraciones:

```text
gestiones
procesos
componentes
objetivos
metas
indicadores
```

NO serán portadas directamente.

Se generarán migraciones de adaptación específicas para APPSisGOE.

---

# Bloqueante B-002

## Responsable Operativo

El modelo implementado en bhagamappsModular permite:

```text
usuario
rol
dependencia
```

como tipos válidos de responsable.

---

## Situación actual

APPSisGOE Oficial:

```text
/public_html
```

no posee:

```text
dependencias
```

ni módulo Inventario.

---

## Alternativas evaluadas

### Alternativa A

Crear Dependencias Administrativas antes de la migración.

### Alternativa B

Eliminar temporalmente el tipo:

```text
dependencia
```

y mantener únicamente:

```text
usuario
rol
```

---

## Decisión

Se adopta la:

```text
ALTERNATIVA B
```

---

## Justificación

La operación institucional puede funcionar correctamente con:

```text
usuario
rol
```

sin requerir Dependencias.

La incorporación futura de Dependencias será tratada mediante una ADR independiente.

---

## Consecuencia Arquitectónica

El campo:

```text
responsable_tipo
```

queda restringido a:

```text
usuario
rol
```

en la migración inicial hacia APPSisGOE.

---

# Estrategia de Portabilidad Aprobada

La migración se realizará en el siguiente orden:

## Fase MP-01

Portabilidad documental

Artefactos:

```text
25 documentos
```

---

## Fase MP-02

Portabilidad de tablas nuevas

```text
meta_indicador
actividades
tareas
```

---

## Fase MP-03

Adaptación de modelos

```text
Actividad
Tarea
```

---

## Fase MP-04

Adaptación RBAC

Conversión de permisos hacia:

```text
Spatie Permission
```

---

## Fase MP-05

Adaptación de controladores

```text
GestionInstitucionalController
PlaneacionController
OperacionController
```

---

## Fase MP-06

Adaptación de rutas

Ajustadas a la arquitectura oficial de APPSisGOE.

---

## Fase MP-07

Adaptación de vistas

```text
gestion/arbol
planeacion/index
operacion/index
```

---

# Resultado Esperado

APPSisGOE Oficial incorporará:

```text
Gestión
Proceso
Componente
Objetivo
Meta
Indicador
Actividad
Tarea
```

sin alterar la integridad de la instancia productiva.

---

# Estado de la Decisión

```text
APROBADA
VIGENTE
OBLIGATORIA
```
