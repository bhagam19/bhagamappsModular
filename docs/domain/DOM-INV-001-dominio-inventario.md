# DOM-INV-001 — Dominio Inventario

**Documento:** DOM-INV-001
**Versión:** 1.0.0
**Estado:** Aprobado — Vigente
**Fecha:** 2026-06-14
**Naturaleza:** Especificación funcional de dominio. Lenguaje de negocio. Sin referencias tecnológicas.
**Autoridad:** Fuente Oficial del Conocimiento del Dominio Inventario. Base obligatoria para DAT-INV-001, APP-INV-001, UI-INV-001 y la implementación del módulo Inventario en APPSisGOE.
**Documentos base:** ADR-004-dominio-inventario.md · AUDIT-INV-002-APPSisGOE.md · ARCH-ANALYSIS-001-APPSisGOE.md · ARCH-001-arquitectura-ejecutable-appsisgoe.md

---

## Tabla de Contenidos

1. [Propósito del Dominio](#1-propósito-del-dominio)
2. [Glosario Oficial](#2-glosario-oficial)
3. [Actores del Dominio](#3-actores-del-dominio)
4. [Capacidades del Dominio](#4-capacidades-del-dominio)
5. [Agregados del Dominio](#5-agregados-del-dominio)
6. [Reglas de Negocio](#6-reglas-de-negocio)
7. [Procesos del Dominio](#7-procesos-del-dominio)
8. [Máquinas de Estado](#8-máquinas-de-estado)
9. [Eventos de Dominio](#9-eventos-de-dominio)
10. [Indicadores del Dominio](#10-indicadores-del-dominio)
11. [Límites del Dominio](#11-límites-del-dominio)
12. [Riesgos Operativos](#12-riesgos-operativos)
13. [Modelo Conceptual Integrado](#13-modelo-conceptual-integrado)
14. [Criterios de Éxito](#14-criterios-de-éxito)

---

## 1. Propósito del Dominio

### 1.1 Qué problema resuelve

Las Instituciones de Educación Estatal (IEE) en Colombia son responsables ante el Estado de administrar el patrimonio público que se les asigna: escritorios, computadores, proyectores, tabletas, mobiliario de oficina, instrumentos musicales, equipos de laboratorio y cualquier otro bien mueble de propiedad institucional.

Sin un sistema formal de gestión patrimonial, una IEE enfrenta cuatro problemas concretos:

**Pérdida de trazabilidad.** No es posible responder con certeza quién tiene un bien en este momento, qué le ha ocurrido desde que ingresó a la institución, o por qué ya no está en el inventario. Las auditorías de la Contraloría o del DAFP exigen esta trazabilidad.

**Modificaciones no controladas.** Cualquier funcionario puede cambiar el estado, la ubicación o la valoración de un bien sin dejar registro, sin aprobación, y sin posibilidad de revertir el cambio. Esto produce un inventario incorrecto que no refleja la realidad.

**Bajas sin autorización.** Los bienes se "desaparecen" del inventario sin seguir el proceso formal de baja: solicitud documentada, motivo justificado y aprobación de la autoridad correspondiente. Una baja no autorizada es una irregularidad patrimonial.

**Desconocimiento del estado del patrimonio.** Los directivos no tienen una visión de cuántos bienes posee la institución, en qué condición están, cuántos están en mantenimiento, y si hay bienes sin responsable asignado.

El Dominio Inventario de APPSisGOE resuelve estos cuatro problemas.

---

### 1.2 Qué responsabilidades tiene

El Dominio Inventario es responsable de gestionar el **ciclo de vida completo** de los bienes muebles de la institución, desde su ingreso hasta su baja formal:

- Registrar cada bien mueble con sus atributos de identificación, clasificación, valoración y condición física.
- Controlar quién tiene la custodia de cada bien en todo momento, manteniendo el historial completo de custodios anteriores.
- Garantizar que cualquier cambio en los atributos de un bien pase por un proceso de propuesta y aprobación, dejando registro inmutable del intento, el resultado y el responsable.
- Garantizar que la baja de un bien no sea una eliminación silenciosa sino un proceso documentado, justificado y autorizado.
- Registrar el historial de ubicaciones físicas y traslados entre dependencias administrativas de cada bien.
- Programar, monitorear y registrar los mantenimientos preventivos y correctivos de los bienes.
- Generar documentos formales de actas de entrega que acrediten la custodia de bienes por parte de funcionarios específicos.
- Proveer indicadores de gestión que permitan a la dirección institucional tomar decisiones fundamentadas sobre el estado del patrimonio.

---

### 1.3 Qué responsabilidades NO tiene

El Dominio Inventario no gestiona:

- **La identidad de los funcionarios.** No crea, edita ni elimina cuentas de usuario. Los custodios son personas ya registradas en el sistema por quien administra los usuarios.
- **Los permisos de acceso al sistema.** No decide quién puede entrar al sistema ni con qué rol. Eso pertenece al núcleo de autorización de APPSisGOE.
- **Los registros generales de auditoría del sistema.** El registro transversal de qué acciones se ejecutan en el sistema es responsabilidad del núcleo. El Dominio Inventario provee sus propios historiales de dominio (modificaciones, bajas, ubicaciones, dependencias) como parte de su responsabilidad patrimonial.
- **La interfaz de notificaciones.** El Dominio Inventario genera eventos que producen notificaciones, pero la bandeja de notificaciones y su presentación visual pertenecen al núcleo de APPSisGOE.
- **La contabilidad financiera.** Los bienes tienen un valor registrado, pero el cálculo contable de depreciación, amortización o valoración formal pertenece a un dominio financiero distinto.
- **Los bienes inmuebles.** El dominio gestiona exclusivamente bienes muebles (los que se pueden trasladar físicamente). Los bienes inmuebles (edificios, terrenos) no forman parte de su alcance.
- **Los libros de biblioteca.** Si la institución posee un módulo de biblioteca, la gestión de su acervo documental es responsabilidad del Dominio Biblioteca, aunque los estantes y computadores de la biblioteca sí son bienes del Dominio Inventario.

---

## 2. Glosario Oficial

Todos los términos de este glosario son inequívocos dentro del Dominio Inventario. Cuando un término tenga un significado común diferente al aquí definido, prevalece la definición de este documento.

---

**Bien**
Todo objeto mueble de propiedad institucional que la IEE recibe, adquiere, o tiene bajo su responsabilidad patrimonial. Un bien es la entidad central del dominio. Se identifica por su placa institucional y su número de serie. Un bien existe en el dominio desde su registro inicial hasta su eventual baja formal; incluso después de la baja, su información histórica permanece en el registro. Los bienes inmuebles no son bienes en el sentido de este dominio.

**Placa**
Código de identificación institucional asignado por la IEE a un bien al momento de su ingreso al inventario. La placa es el identificador primario del bien para los funcionarios de la institución. Ejemplo: "INV-2024-0342". La placa es única e invariable durante toda la vida del bien en el inventario.

**Número de serie**
Código alfanumérico asignado por el fabricante o proveedor al bien. Permite identificar un bien individualmente dentro de una misma categoría y modelo. Es complementario a la placa. No todos los bienes tienen número de serie (por ejemplo, el mobiliario de madera generalmente no lo tiene).

**Categoría**
Clasificación primaria de los bienes de la institución. Las categorías son definidas y gestionadas por la administración. Ejemplos: Mobiliario, Equipos Tecnológicos, Equipos Audiovisuales, Material Didáctico, Instrumentos Musicales, Herramientas. Las categorías se organizan en grupos semánticos institucionales para la gestión agregada del patrimonio. Cada categoría tiene un código estable (identificador semántico) que permite referenciarla con seguridad aunque cambie su nombre.

**Subcategoría**
Clasificación secundaria dentro de una categoría. Permite mayor granularidad en la descripción de los bienes. La subcategoría es opcional: no todo bien necesita una. Ejemplo: dentro de la categoría Equipos Tecnológicos, puede existir la subcategoría Portátiles.

**Dependencia**
Unidad administrativa de la institución bajo cuya responsabilidad se encuentran bienes. Cada dependencia tiene un coordinador responsable que responde por el patrimonio asignado a ella. Ejemplos: Rectoría, Coordinación Académica, Biblioteca, Sala de Sistemas, Sala de Profesores, Almacén. Una dependencia tiene una ubicación física asociada.

**Ubicación**
Espacio físico específico dentro del plantel donde se encuentra un bien en un momento dado. La ubicación es más granular que la dependencia: una dependencia puede abarcar múltiples ubicaciones. Ejemplos: Aula 101, Sala de Informática 2, Bodega Principal. La ubicación describe el dónde físico; la dependencia describe el quién administrativo.

**Espacio físico**
Sinónimo de Ubicación en el contexto de este dominio. Se usa intercambiablemente para referirse al lugar dentro del plantel donde está un bien.

**Custodio**
Persona que tiene responsabilidad material directa sobre uno o más bienes. El custodio es el funcionario que recibe los bienes en custodia y responde por su conservación y disponibilidad. La relación entre un custodio y sus bienes está documentada formalmente mediante un Acta de Entrega. Un bien tiene exactamente un custodio activo en cualquier momento.

**Responsable**
En el contexto del Dominio Inventario, responsable y custodio son equivalentes. El término responsable se usa para referirse a la persona asignada como custodio de un bien en un período específico. El "responsable actual" es quien tiene la custodia vigente del bien.

**Custodia**
El vínculo formal entre un bien y su custodio durante un período de tiempo determinado. Una custodia tiene una fecha de inicio (cuando se asigna el bien al custodio) y una fecha de término (cuando el bien es reasignado o dado de baja). La custodia vigente no tiene fecha de término. La historia completa de custodios de un bien se denomina cadena de custodia.

**Cadena de custodia**
El registro cronológico completo de todos los custodios que han tenido responsabilidad sobre un bien, en orden temporal. La cadena de custodia nunca se elimina ni se modifica. Permite responder en cualquier momento quién tuvo el bien en una fecha específica del pasado.

**Movimiento**
Todo cambio en la ubicación física o la dependencia administrativa de un bien. Un movimiento queda registrado en el historial del bien con la procedencia, el destino y el responsable del traslado. No todo movimiento implica cambio de custodio: el mismo custodio puede trasladar un bien de un espacio a otro dentro de la misma dependencia.

**Traslado**
Tipo específico de movimiento en el que un bien cambia de dependencia administrativa. El traslado implica un cambio en la responsabilidad institucional: la dependencia de origen deja de ser responsable del bien y la dependencia de destino asume esa responsabilidad. Todo traslado queda registrado en el historial de dependencias del bien.

**Mantenimiento**
Actividad programada o ejecutada sobre un bien para preservar o restaurar su funcionalidad. Existen dos tipos de mantenimiento:
- Preventivo: planificado con anticipación para evitar daños o deterioro. Tiene una fecha programada de ejecución.
- Correctivo: ejecutado en respuesta a un daño o falla ya ocurrida. Puede ser no planificado.

El mantenimiento tiene un ciclo de vida propio: puede estar pendiente de ejecución, realizado o cancelado.

**Condición física**
Descripción del estado de conservación de un bien en un momento dado. Las condiciones reconocidas son: Nuevo (sin uso previo), Bueno (en uso, sin deterioro significativo), Regular (con deterioro visible pero funcional), Malo (deterioro severo que afecta su funcionalidad o utilidad).

**Origen**
Procedencia del bien: cómo llegó a ser patrimonio de la institución. Ejemplos: Compra directa, Donación, Transferencia de otra entidad, Comodato, Fabricación propia. El origen es un dato del catálogo institucional, no un texto libre.

**Baja**
Proceso formal mediante el cual un bien sale del inventario activo de la institución. La baja no destruye los datos del bien: lo marca como "dado de baja" preservando toda su información histórica. Una baja puede producirse por: pérdida, deterioro irreparable, obsolescencia, donación, hurto documentado, o disposición por parte de la autoridad competente. La baja siempre debe ser autorizada.

**Solicitud**
Petición formal de un funcionario para realizar una acción que requiere aprobación. Existen dos tipos de solicitudes en el Dominio Inventario:
- Solicitud de Modificación: propuesta de cambio en uno o más atributos de un bien (ver HMB).
- Solicitud de Baja: petición documentada para dar de baja un bien (ver HEB).
Toda solicitud queda registrada con su estado, el solicitante, el momento de creación y el resultado final (aprobación o rechazo).

**HMB — Historial de Modificaciones de Bienes**
Nombre formal del proceso de control de modificaciones de atributos de bienes. El HMB implementa el flujo: propuesta de cambio → registro en pendiente → aprobación o rechazo por autoridad. El HMB garantiza que ningún atributo de un bien cambie sin dejar un registro inmutable del intento, el responsable y el resultado.

**HEB — Historial de Eliminaciones de Bienes**
Nombre formal del proceso de control de bajas de bienes. El HEB implementa el flujo: solicitud de baja con motivo → registro en pendiente → aprobación (el bien es dado de baja) o rechazo (el bien permanece activo). El HEB garantiza que ningún bien salga del inventario sin autorización documentada.

**Historial**
Registro cronológico e inmutable de eventos ocurridos sobre una entidad del dominio. El Dominio Inventario mantiene cuatro historiales:
- Historial de modificaciones: registro de propuestas de cambio de atributos (HMB).
- Historial de bajas: registro de solicitudes de baja (HEB).
- Historial de dependencias: registro de traslados entre dependencias administrativas.
- Historial de ubicaciones: registro de cambios de ubicación física.
Ningún registro de historial se elimina ni modifica.

**Acta de Entrega**
Documento formal que acredita que un funcionario tiene bajo su custodia un conjunto de bienes. El acta lista los bienes asignados al custodio al momento de su generación, con sus datos de identificación. El acta es imprimible y firmable. Su firma compromete formalmente al custodio con la conservación de los bienes listados.

**Aprobador**
Actor institucional con autoridad para revisar solicitudes de modificación o baja y emitir una decisión vinculante (aprobación o rechazo). En el contexto del Dominio Inventario, el rol de Aprobador es ejercido por los Administradores del sistema y por la Rectoría.

**Modificación directa**
Cambio en los atributos de un bien realizado por un Aprobador sin pasar por el flujo de solicitud. Los Aprobadores no necesitan proponer un cambio: pueden aplicarlo directamente. Esta distinción es intencional: los Aprobadores son los garantes del proceso, por lo tanto tienen autoridad para modificar sin requerir su propia aprobación.

**Detalle técnico**
Conjunto de especificaciones físicas y técnicas de un bien que complementan su descripción básica: marca, color, material, dimensiones, características especiales. El detalle técnico es opcional: no todos los bienes requieren estas especificaciones. Cuando existe, también está sujeto al proceso HMB para modificaciones.

---

## 3. Actores del Dominio

Los actores son las personas o roles institucionales que interactúan con el Dominio Inventario. Cada actor tiene responsabilidades definidas y un conjunto de acciones que puede ejecutar.

---

### Actor 1 — Auxiliar de Inventario

**Quién es:** Funcionario operativo encargado del registro y mantenimiento de los datos del inventario. Generalmente es el primer punto de contacto con el bien físico.

**Responsabilidades:**
- Registrar bienes nuevos en el inventario con todos sus datos de identificación, clasificación y condición física.
- Registrar y actualizar el detalle técnico de los bienes.
- Fotografiar los bienes para el registro visual del inventario.
- Proponer modificaciones en los atributos de bienes (vía HMB).
- Solicitar la baja de bienes con deterioro u otras causas justificadas (vía HEB).
- Registrar cambios de ubicación física de bienes.
- Programar mantenimientos para bienes de su dependencia.
- Registrar mantenimientos realizados.

**Limitaciones:**
- No puede modificar directamente los atributos de un bien: sus propuestas quedan pendientes de aprobación.
- No puede ejecutar una baja directamente: su solicitud queda pendiente de aprobación.
- Solo puede solicitar baja de bienes de la dependencia a la que pertenece.
- No puede aprobar ni rechazar solicitudes de otros usuarios.

---

### Actor 2 — Coordinador de Dependencia

**Quién es:** Funcionario responsable de una dependencia administrativa. Responde institucionalmente por los bienes asignados a su dependencia.

**Responsabilidades:**
- Supervisar que todos los bienes de su dependencia tengan custodio asignado.
- Proponer modificaciones en los atributos de bienes de su dependencia.
- Solicitar traslados de bienes hacia o desde su dependencia.
- Solicitar la baja de bienes de su dependencia.
- Consultar el historial de bienes de su dependencia.
- Generar actas de entrega para los funcionarios a su cargo.
- Verificar la completitud de los datos de los bienes de su dependencia.

**Limitaciones:**
- Sus solicitudes de modificación y baja también requieren aprobación.
- No puede modificar la definición de la dependencia (nombre, coordinador responsable).

---

### Actor 3 — Aprobador (Administrador / Rectoría)

**Quién es:** Funcionario con autoridad máxima sobre el inventario institucional. En el contexto de una IEE colombiana, este rol es ejercido por el Administrador del sistema y por la Rectoría.

**Responsabilidades:**
- Revisar y resolver todas las solicitudes de modificación pendientes (HMB).
- Revisar y resolver todas las solicitudes de baja pendientes (HEB).
- Modificar directamente los atributos de bienes sin requerir solicitud previa.
- Ejecutar directamente la baja de bienes sin requerir solicitud previa.
- Asignar y cambiar custodios de bienes.
- Gestionar los catálogos maestros (categorías, dependencias, ubicaciones, estados, orígenes, mantenimientos).
- Supervisar el estado general del patrimonio institucional mediante los indicadores del dashboard.
- Generar actas de entrega para cualquier funcionario.

**Posición institucional:** El Aprobador es el garante del proceso patrimonial. Sus acciones son definitivas y quedan registradas en el historial.

---

### Actor 4 — Custodio

**Quién es:** Cualquier funcionario de la institución que tiene uno o más bienes bajo su custodia formal. El custodio puede coincidir con el Auxiliar de Inventario o el Coordinador, pero es un rol de negocio independiente: es el tenedor material del bien.

**Responsabilidades:**
- Conservar en buen estado los bienes a su cargo.
- Informar cuando un bien sufre daño, deterioro o pérdida.
- Firmar el Acta de Entrega que certifica que recibe los bienes.
- Cooperar en los procesos de verificación física del inventario.

**Posición institucional:** La custodia es una responsabilidad formal con consecuencias legales y administrativas. El Acta de Entrega firmada es el documento que acredita la responsabilidad del custodio sobre los bienes.

---

### Actor 5 — Auditor Institucional

**Quién es:** Funcionario interno o externo que verifica el estado del inventario, la integridad de los procesos y el cumplimiento de las normas patrimoniales. Puede ser un coordinador del plantel, un auditor de la Secretaría de Educación, o un visitante de la Contraloría.

**Responsabilidades:**
- Consultar el estado actual de cualquier bien.
- Consultar el historial completo de modificaciones, bajas, ubicaciones y custodios.
- Verificar que las bajas de bienes estén debidamente autorizadas.
- Verificar que los bienes activos tengan custodio asignado.
- Consultar los indicadores de calidad de datos del inventario.
- Revisar las actas de entrega.

**Limitaciones:**
- El auditor consulta; no modifica. Ninguna de sus acciones altera el estado del dominio.

---

### Actor 6 — Solicitante de Baja

**Quién es:** Cualquier funcionario con permiso para solicitar la baja de un bien (Auxiliar o Coordinador). No tiene autoridad para ejecutar la baja, solo para iniciar el proceso.

**Nota:** Este actor es funcionalmente un subconjunto del Auxiliar y el Coordinador. Se define por separado porque su rol en el proceso HEB tiene restricciones específicas: solo puede solicitar baja de bienes de su dependencia, y no puede tener más de una solicitud pendiente por bien.

---

## 4. Capacidades del Dominio

Las capacidades son las grandes áreas funcionales que el Dominio Inventario ofrece a la institución. Cada capacidad agrupa procesos, reglas y actores relacionados.

---

### Capacidad 1 — Gestión de Bienes

Es la capacidad central del dominio. Comprende el registro de nuevos bienes y la consulta del estado actual de todos los bienes institucionales.

El registro de un bien establece su identidad (placa, serie, nombre), su clasificación (categoría, subcategoría), su valoración (precio de adquisición, fecha), su condición física inicial y su ubicación de partida. Un bien recién registrado es un bien activo del inventario.

La consulta de bienes permite buscar, filtrar y visualizar el inventario mediante distintos criterios: por dependencia, por categoría, por condición, por custodio, por rango de fechas, o por texto libre sobre el nombre o la descripción.

---

### Capacidad 2 — Control de Modificaciones (HMB)

Es la capacidad que garantiza que los atributos de los bienes no cambien sin registro ni autorización.

Cuando un funcionario de rol básico necesita actualizar un atributo de un bien (por ejemplo, cambiar su condición de Bueno a Regular, o actualizar su dependencia), no lo hace directamente: propone el cambio. La propuesta queda registrada con el valor actual y el valor propuesto. El Aprobador revisa la propuesta y decide aprobarla (el cambio se aplica) o rechazarla (el bien queda igual).

Los Aprobadores pueden modificar directamente sin propuesta, pero la modificación igualmente queda registrada.

Esta capacidad garantiza que el inventario solo cambie con voluntad explícita de la autoridad competente, y que toda modificación —exitosa o no— sea auditable.

---

### Capacidad 3 — Control de Bajas (HEB)

Es la capacidad que garantiza que ningún bien desaparezca del inventario sin autorización documentada.

Cuando un funcionario considera que un bien debe ser dado de baja (por deterioro, pérdida, hurto, obsolescencia), no lo elimina: solicita la baja con un motivo formal. El Aprobador revisa la solicitud y decide. Si aprueba, el bien es marcado como "dado de baja" y sale del inventario activo, pero su información completa permanece en los registros. Si rechaza, el bien continúa activo.

Los Aprobadores pueden ejecutar la baja directamente sin solicitud previa, para casos en que la autoridad actúa por iniciativa propia.

Esta capacidad previene las "desapariciones" de bienes del inventario sin justificación ni autorización.

---

### Capacidad 4 — Gestión de Custodios

Es la capacidad que mantiene la cadena de custodia de cada bien: quién tuvo el bien, desde cuándo, hasta cuándo.

Comprende la asignación inicial de custodio al registrar un bien, el cambio de custodio cuando un bien pasa de un funcionario a otro, y la consulta del historial completo de custodios de cualquier bien.

La asignación de custodio es siempre atómica: el sistema garantiza que en el momento del cambio no existe ningún período en que el bien no tenga custodio activo.

---

### Capacidad 5 — Seguimiento de Ubicaciones y Traslados

Es la capacidad que registra dónde ha estado cada bien a lo largo del tiempo.

Comprende el registro de cambios de ubicación física (de una sala a otra, de un piso a otro) y el registro de traslados entre dependencias administrativas (de la Sala de Informática al Almacén). Ambos tipos de movimiento quedan en historiales separados, porque representan consecuencias distintas: el traslado entre dependencias implica un cambio de responsabilidad institucional.

---

### Capacidad 6 — Gestión de Mantenimientos

Es la capacidad que planifica y registra el mantenimiento preventivo y correctivo de los bienes.

Comprende la programación de mantenimientos con fecha prevista, la actualización del estado del mantenimiento cuando se ejecuta, la cancelación de mantenimientos que no se realizarán, y la consulta de la agenda de mantenimientos por bien, por dependencia o por período.

El dominio genera alertas cuando un mantenimiento programado no se ejecutó en la fecha prevista.

---

### Capacidad 7 — Generación de Actas de Entrega

Es la capacidad que produce documentación formal de la custodia de bienes.

Un Acta de Entrega es un documento que lista todos los bienes bajo la custodia de un funcionario en el momento de su generación. El acta es imprimible y está diseñada para ser firmada por el custodio. La firma del custodio es el acto formal mediante el cual acepta la responsabilidad sobre los bienes listados.

---

### Capacidad 8 — Gestión de Catálogos Maestros

Es la capacidad que mantiene actualizados los valores de referencia que clasifican a los bienes.

Los catálogos del Dominio Inventario son: Categorías, Dependencias, Ubicaciones, Condiciones físicas (Estados), Tipos de almacenamiento, Tipos de mantenimiento, y Orígenes de los bienes. Los catálogos son gestionados exclusivamente por los Aprobadores y Coordinadores.

---

### Capacidad 9 — Indicadores de Gestión (Dashboard)

Es la capacidad que transforma los datos del inventario en información de decisión para la dirección institucional.

El dashboard presenta indicadores de cuatro tipos: contadores del estado actual, porcentajes de salud del inventario, distribuciones por dimensión de clasificación, y alertas operativas. Se describe en detalle en la sección 10.

---

### Capacidad 10 — Trazabilidad Patrimonial

Es la capacidad transversal que garantiza que toda acción significativa sobre el patrimonio institucional deja un rastro inmutable, con el responsable y el momento exacto.

La trazabilidad no es una función que el usuario invoque: es una propiedad del dominio que se cumple automáticamente en cada proceso. Un auditor puede reconstruir el historial completo de cualquier bien desde su registro inicial hasta su estado actual.

---

## 5. Agregados del Dominio

Los agregados son las unidades de coherencia del dominio. Cada agregado encapsula un conjunto de entidades y garantiza sus invariantes. Los agregados se tratan como unidades indivisibles en las operaciones de escritura.

---

### Agregado 1 — Bien

**Propósito:** Representa la existencia de un bien mueble de la institución y concentra toda la información sobre su identidad, clasificación y estado actual.

**Ciclo de vida:**

```
[Sin existencia]
    │
    │ Se registra (alta)
    ▼
[Activo]
    │
    │ Se aprueba una baja (HEB)
    ▼
[Dado de baja]
```

Un bien en estado Activo puede recibir modificaciones de atributos (vía HMB o modificación directa), cambios de custodio, cambios de ubicación, traslados y mantenimientos. Un bien Dado de baja no puede recibir ninguna de estas operaciones.

**Invariantes:**
- Todo bien activo debe pertenecer a exactamente una categoría.
- Todo bien activo debe tener exactamente un custodio activo en todo momento.
- Un bien dado de baja conserva toda su información y sus historiales.
- No puede existir más de una solicitud de modificación pendiente por campo en el mismo bien.
- No puede existir más de una solicitud de baja pendiente para el mismo bien.

**Relaciones:**
- Tiene un detalle técnico (opcional).
- Tiene una galería de imágenes (opcional, múltiple).
- Tiene exactamente un custodio activo (obligatorio mientras esté activo).
- Tiene un historial de custodios (cadena de custodia).
- Tiene un historial de modificaciones (HMB).
- Tiene un historial de bajas (HEB).
- Tiene un historial de ubicaciones.
- Tiene un historial de dependencias.
- Tiene una agenda de mantenimientos.

---

### Agregado 2 — Custodia

**Propósito:** Representa la responsabilidad material de un funcionario sobre un bien durante un período de tiempo específico. La custodia es el vínculo formal entre el bien y su tenedor.

**Ciclo de vida:**

```
[Sin existencia]
    │
    │ Se asigna el bien a un custodio
    ▼
[Abierta]  ← Estado: sin fecha de término
    │
    │ Se asigna un nuevo custodio al mismo bien
    ▼
[Cerrada]  ← Estado: con fecha de término registrada
```

Una custodia abierta es la custodia vigente. Solo puede existir una custodia abierta por bien en todo momento.

**Invariantes:**
- Toda custodia tiene una fecha de inicio.
- La custodia abierta no tiene fecha de término.
- Una custodia cerrada tiene fecha de término posterior o igual a la de inicio.
- El cierre de la custodia anterior y la apertura de la nueva ocurren en la misma operación atómica. No existe un instante entre ellas.
- Una custodia cerrada nunca se reabre.
- Una custodia nunca se elimina.

**Relaciones:**
- Pertenece a exactamente un bien.
- Está asociada a exactamente un funcionario custodio.

---

### Agregado 3 — Solicitud de Modificación (HMB)

**Propósito:** Representa la intención documentada de cambiar el valor de un atributo específico de un bien. Encapsula la propuesta, el estado de la aprobación y el resultado.

**Ciclo de vida:** Ver sección 8 (Máquinas de Estado).

**Invariantes:**
- Toda solicitud de modificación debe especificar: el bien afectado, el atributo a modificar, el valor actual en el momento de la solicitud, y el valor propuesto.
- Una solicitud de modificación nunca se modifica una vez creada. Solo cambia su estado (pendiente → aprobada | rechazada) y se registra quién tomó la decisión.
- Una solicitud de modificación nunca se elimina del registro.
- No puede existir más de una solicitud pendiente para el mismo campo del mismo bien en el mismo momento.
- Una solicitud aprobada produce el cambio en el bien. Una solicitud rechazada no produce ningún cambio.
- Si el campo modificado es la dependencia del bien, la aprobación registra adicionalmente el traslado en el historial de dependencias.

**Relaciones:**
- Pertenece a exactamente un bien.
- Fue creada por un funcionario (solicitante).
- Fue resuelta por un Aprobador (puede ser nulo si aún está pendiente).

---

### Agregado 4 — Solicitud de Baja (HEB)

**Propósito:** Representa la intención documentada de retirar un bien del inventario activo. Encapsula la solicitud, el motivo, el estado de la aprobación y el resultado.

**Ciclo de vida:** Ver sección 8 (Máquinas de Estado).

**Invariantes:**
- Toda solicitud de baja debe incluir el bien afectado, el funcionario que la solicita, y el motivo de la baja.
- Una solicitud de baja nunca se modifica ni elimina del registro.
- No puede existir más de una solicitud de baja pendiente para el mismo bien en el mismo momento.
- Una solicitud aprobada produce la marcación del bien como "dado de baja" (baja lógica). Una solicitud rechazada no modifica el bien.
- Un bien dado de baja por esta vía puede ser consultado en registros históricos.

**Relaciones:**
- Pertenece a exactamente un bien.
- Fue creada por un funcionario (solicitante).
- Fue resuelta por un Aprobador (puede ser nulo si aún está pendiente).

---

### Agregado 5 — Mantenimiento Programado

**Propósito:** Representa una actividad de mantenimiento planificada o en ejecución para un bien específico. Permite la gestión de la agenda de mantenimiento institucional.

**Ciclo de vida:**

```
[Sin existencia]
    │
    │ Se programa el mantenimiento
    ▼
[Pendiente]  ← Fecha programada en el futuro o presente
    │
    ├── Se ejecuta el mantenimiento → [Realizado]
    │
    └── Se cancela → [Cancelado]
```

Un mantenimiento en estado Pendiente con fecha pasada está vencido y genera alerta.

**Invariantes:**
- Todo mantenimiento programado debe estar asociado a un bien activo.
- Todo mantenimiento tiene un tipo (preventivo o correctivo) y una fecha programada.
- Un mantenimiento realizado registra la fecha real de ejecución.
- Un mantenimiento nunca se elimina del registro.

**Relaciones:**
- Pertenece a exactamente un bien.
- Puede estar asociado a un responsable de mantenimiento (quien lo ejecutó o lo programó).

---

## 6. Reglas de Negocio

Catálogo completo de las reglas que rigen el comportamiento del Dominio Inventario.

---

### RN-001
Todo bien registrado en el sistema debe pertenecer a exactamente una categoría. Un bien sin categoría no puede completar su registro.

### RN-002
Todo bien activo debe tener exactamente un custodio activo en todo momento. Un bien sin custodio representa una irregularidad patrimonial que debe ser resuelta.

### RN-003
Un bien dado de baja conserva toda su información histórica: atributos, custodios, modificaciones, ubicaciones y traslados. La baja lógica no destruye datos.

### RN-004
Un bien dado de baja no puede recibir nuevas asignaciones de custodia.

### RN-005
Un bien dado de baja no puede ser objeto de nuevas solicitudes de modificación.

### RN-006
Un bien dado de baja no puede recibir mantenimientos programados.

### RN-007
Los funcionarios de rol básico (Auxiliar, Coordinador, Docente) no pueden modificar directamente los atributos de un bien. Toda propuesta de modificación de estos roles queda en estado pendiente hasta que un Aprobador la resuelva.

### RN-008
Los Aprobadores (Administrador, Rectoría) pueden modificar directamente los atributos de un bien sin crear una solicitud pendiente. La modificación directa igualmente queda registrada.

### RN-009
Toda solicitud de modificación debe especificar: el bien afectado, el atributo que se desea cambiar, el valor actual del atributo en el momento de la solicitud, y el valor propuesto.

### RN-010
No puede existir más de una solicitud de modificación pendiente para el mismo atributo del mismo bien en el mismo momento. Un atributo con solicitud pendiente no puede recibir una nueva propuesta hasta que la solicitud existente sea resuelta.

### RN-011
La aprobación de una solicitud de modificación produce el cambio del valor del atributo en el bien, de forma atómica con el cambio de estado de la solicitud. Si el cambio falla, la solicitud permanece pendiente.

### RN-012
El rechazo de una solicitud de modificación no altera ningún atributo del bien. El bien mantiene sus valores originales.

### RN-013
Toda solicitud de modificación, independientemente de su resultado, permanece en el registro de forma inmutable.

### RN-014
Cuando el atributo modificado es la dependencia del bien, la aprobación registra adicionalmente el traslado en el historial de dependencias del bien, con la dependencia de origen, la dependencia de destino y el aprobador.

### RN-015
Los funcionarios de rol básico no pueden ejecutar directamente la baja de un bien. Deben solicitar la baja con un motivo formal.

### RN-016
Los Aprobadores pueden ejecutar directamente la baja de un bien sin crear una solicitud previa. La baja directa igualmente queda registrada en el historial de bajas.

### RN-017
Toda solicitud de baja debe incluir un motivo. El motivo es obligatorio para garantizar la trazabilidad de la decisión.

### RN-018
No puede existir más de una solicitud de baja pendiente para el mismo bien en el mismo momento.

### RN-019
Un funcionario de rol básico solo puede solicitar la baja de bienes pertenecientes a su propia dependencia.

### RN-020
La aprobación de una solicitud de baja produce la marcación del bien como dado de baja, de forma atómica con el cambio de estado de la solicitud.

### RN-021
El rechazo de una solicitud de baja no altera el estado del bien. El bien continúa activo.

### RN-022
Toda solicitud de baja, independientemente de su resultado, permanece en el registro de forma inmutable.

### RN-023
El cambio de custodio de un bien es una operación atómica: en la misma operación se cierra el registro del custodio anterior (registrando la fecha de retiro) y se abre el registro del nuevo custodio (registrando la fecha de asignación). No puede existir un instante en el que el bien no tenga custodio activo durante el proceso de cambio.

### RN-024
El historial de custodios de un bien nunca se elimina ni se modifica. Los registros de custodia son inmutables.

### RN-025
Un custodio cuyo registro de custodia fue cerrado (fecha de retiro registrada) ya no es responsable del bien.

### RN-026
Todo traslado de un bien entre dependencias administrativas queda registrado en el historial de dependencias, con la dependencia de origen, la dependencia de destino, el ejecutor del traslado y la fecha.

### RN-027
Todo cambio de ubicación física de un bien queda registrado en el historial de ubicaciones, con la ubicación de origen, la ubicación de destino, el ejecutor del movimiento, la fecha y las observaciones opcionales.

### RN-028
Los historiales de ubicaciones y dependencias son inmutables. Los registros de movimiento no se eliminan.

### RN-029
Un mantenimiento programado puede ser de tipo preventivo o correctivo. El tipo define si el mantenimiento fue planificado con anticipación o es una respuesta a un daño.

### RN-030
Un mantenimiento en estado Pendiente con fecha programada anterior a la fecha actual es un mantenimiento vencido. Los mantenimientos vencidos generan alertas en el dashboard.

### RN-031
Al registrar la ejecución de un mantenimiento, se registra la fecha real de ejecución. Esta fecha puede diferir de la fecha programada.

### RN-032
Un mantenimiento nunca se elimina del registro, incluso si fue cancelado.

### RN-033
Los catálogos maestros del dominio (categorías, dependencias, ubicaciones, condiciones, orígenes, tipos de mantenimiento, tipos de almacenamiento) solo pueden ser gestionados por los Aprobadores y los Coordinadores.

### RN-034
Los grupos semánticos de categorías (por ejemplo: Mobiliario, Equipos Tecnológicos, Audiovisuales) se identifican mediante un código estable asignado a la categoría, no mediante el orden de creación ni el identificador interno del catálogo. Este código estable garantiza que el sistema pueda agrupar categorías de forma fiable aunque se modifiquen, reordenen o migren datos del catálogo.

### RN-035
Una categoría del catálogo no puede eliminarse si tiene bienes asociados. Primero deben reclasificarse o darse de baja los bienes de esa categoría.

### RN-036
Todo registro de historial en el Dominio Inventario es de solo inserción. No existen operaciones de modificación ni eliminación sobre los historiales (modificaciones, bajas, dependencias, ubicaciones). Esta propiedad no es opcional: es el fundamento de la auditoría patrimonial.

### RN-037
El Acta de Entrega refleja el estado de custodia en el momento exacto de su generación. No es un documento dinámico: una vez generada, refleja una fotografía del estado de la custodia en ese instante.

### RN-038
Un bien sin placa institucional no puede completar su registro. La placa es obligatoria y debe ser única en el inventario.

### RN-039
La condición física de un bien (Nuevo, Bueno, Regular, Malo) es parte de los datos de clasificación del bien. Todo bien debe tener una condición física registrada.

### RN-040
La fecha de adquisición de un bien no puede ser posterior a la fecha de registro en el inventario.

---

## 7. Procesos del Dominio

---

### Proceso 1 — Registrar Bien

**Objetivo:** Dar de alta en el inventario un nuevo bien mueble institucional, estableciendo su identidad, clasificación, valoración inicial y primera asignación de custodia.

**Disparador:** Un bien nuevo ingresa al inventario de la institución (por compra, donación, transferencia u otra causa). El Auxiliar de Inventario o el Coordinador inicia el registro.

**Flujo principal:**
1. El funcionario reúne la información del bien: nombre, placa institucional (si ya fue asignada o se asignará en este acto), número de serie (si aplica), categoría, dependencia de destino, condición física, origen, valor de adquisición y fecha de adquisición.
2. El funcionario registra los datos básicos del bien en el sistema.
3. El sistema verifica que la placa no esté duplicada en el inventario.
4. Opcionalmente, el funcionario registra el detalle técnico (marca, color, material, características especiales).
5. Opcionalmente, el funcionario adjunta imágenes del bien.
6. El sistema asigna el primer custodio al bien. El custodio inicial es el coordinador de la dependencia de destino, o el funcionario que lo registra, según corresponda.
7. El sistema registra el evento `BienRegistrado`.

**Resultado esperado:** El bien existe en el inventario con estado Activo, tiene categoría asignada, tiene un custodio activo, y está ubicado en una dependencia. Su historial de custodios contiene el primer registro con la fecha de inicio.

---

### Proceso 2 — Modificar Campo de un Bien (HMB)

**Objetivo:** Cambiar el valor de un atributo de un bien activo mediante el flujo de aprobación.

**Disparador:** Un funcionario detecta que un dato de un bien debe actualizarse (por ejemplo: el bien fue dado de baja de su condición Bueno a Regular por uso; o la dependencia del bien debe actualizarse porque fue trasladado).

**Flujo principal:**

*Camino A — Aprobador modifica directamente:*
1. El Aprobador selecciona el bien y el campo a modificar.
2. El Aprobador ingresa el nuevo valor.
3. El sistema aplica el cambio inmediatamente.
4. El sistema registra el evento `BienModificado` con el valor anterior, el nuevo valor, y el Aprobador responsable.

*Camino B — Funcionario básico propone modificación:*
1. El funcionario selecciona el bien y el campo a modificar.
2. El sistema muestra el valor actual del campo.
3. El funcionario ingresa el valor propuesto.
4. El sistema verifica que no exista ya una solicitud pendiente para ese campo del mismo bien.
5. El sistema registra la Solicitud de Modificación en estado Pendiente, con el valor actual y el valor propuesto.
6. El sistema notifica a los Aprobadores sobre la solicitud pendiente.
7. El evento `ModificacionPropuesta` es registrado.
8. Un Aprobador revisa la solicitud:
   - Si Aprueba: el sistema actualiza el campo en el bien, registra la aprobación, y emite `ModificacionAprobada`.
   - Si Rechaza: el sistema registra el rechazo, el bien no cambia, y emite `ModificacionRechazada`.

**Resultado esperado:** El campo del bien tiene el nuevo valor (si fue aprobado o si lo cambió un Aprobador directamente). La Solicitud de Modificación queda en el historial con su estado definitivo. Si el campo modificado fue la dependencia, el historial de dependencias tiene el nuevo registro de traslado.

---

### Proceso 3 — Trasladar Bien (cambio de dependencia)

**Objetivo:** Registrar que un bien ha pasado a ser responsabilidad de una dependencia administrativa diferente.

**Disparador:** Un bien debe moverse de una dependencia a otra por reorganización institucional, reubicación o reasignación de funciones.

**Flujo principal:**
1. El funcionario inicia una Solicitud de Modificación sobre el campo "dependencia" del bien.
2. Sigue el Camino B del Proceso 2.
3. Cuando el Aprobador aprueba la solicitud, el sistema actualiza la dependencia del bien Y registra automáticamente el traslado en el historial de dependencias con: dependencia anterior, dependencia nueva, funcionario que propuso, Aprobador que autorizó, y fecha.
4. El sistema emite `BienTrasladado`.

**Resultado esperado:** El bien tiene la nueva dependencia. El historial de dependencias registra el traslado. Si el bien cambia de custodio como consecuencia del traslado, se ejecuta adicionalmente el Proceso 5 (Cambiar Custodio).

---

### Proceso 4 — Cambiar Ubicación Física

**Objetivo:** Registrar que un bien ha cambiado de espacio físico dentro del plantel, sin necesariamente cambiar de dependencia administrativa.

**Disparador:** Un bien es movido de un aula a otra, de una oficina a un pasillo, del aula al almacén, etc.

**Flujo principal:**
1. El funcionario selecciona el bien y la nueva ubicación de destino.
2. El sistema registra el movimiento en el historial de ubicaciones con: ubicación anterior, ubicación nueva, funcionario que reporta el movimiento, fecha, y observaciones opcionales.
3. El sistema emite `UbicacionCambiada`.

**Nota:** El cambio de ubicación física no requiere aprobación. Cualquier funcionario con permiso puede registrarlo. La trazabilidad está garantizada por el historial.

**Resultado esperado:** El historial de ubicaciones del bien registra el movimiento. La ubicación actual del bien refleja el nuevo espacio.

---

### Proceso 5 — Cambiar Custodio

**Objetivo:** Reasignar la responsabilidad material de un bien de un funcionario a otro, manteniendo la continuidad de la cadena de custodia.

**Disparador:** Un funcionario deja la institución, es reasignado a otra dependencia, o la dirección decide reasignar un bien a un custodio diferente.

**Flujo principal:**
1. El Aprobador o Coordinador selecciona el bien y el nuevo custodio.
2. El sistema verifica que el nuevo custodio es un funcionario activo.
3. El sistema ejecuta la operación atómica:
   a. Registra la fecha de retiro en el registro de custodia actual.
   b. Crea el nuevo registro de custodia con el nuevo custodio y la fecha de asignación.
4. El sistema emite `CustodioAsignado` (para el nuevo custodio) y `CustodioLiberado` (para el custodio anterior).

**Resultado esperado:** El bien tiene un nuevo custodio activo. El historial de custodios registra el cierre de la custodia anterior y la apertura de la nueva. No existe ningún momento en que el bien no tenga custodio activo.

---

### Proceso 6 — Programar Mantenimiento

**Objetivo:** Planificar un mantenimiento preventivo o correctivo para un bien activo.

**Disparador:** El Coordinador o Aprobador detecta que un bien necesita mantenimiento en una fecha determinada.

**Flujo principal:**
1. El funcionario selecciona el bien.
2. El funcionario ingresa: tipo de mantenimiento (preventivo/correctivo), título descriptivo, descripción opcional, y fecha programada.
3. El sistema registra el mantenimiento en estado Pendiente.
4. El sistema emite `MantenimientoProgramado`.

**Resultado esperado:** El bien tiene un mantenimiento en su agenda con fecha programada y estado Pendiente.

---

### Proceso 7 — Registrar Mantenimiento Realizado

**Objetivo:** Actualizar el estado de un mantenimiento programado para reflejar su ejecución.

**Disparador:** El mantenimiento programado fue ejecutado. El funcionario responsable actualiza el registro.

**Flujo principal:**
1. El funcionario accede a la agenda de mantenimientos.
2. Selecciona el mantenimiento realizado.
3. Registra la fecha real de ejecución y observaciones sobre el resultado.
4. El sistema actualiza el estado del mantenimiento a Realizado.
5. El sistema emite `MantenimientoRealizado`.

**Resultado esperado:** El mantenimiento queda en estado Realizado con la fecha de ejecución registrada.

---

### Proceso 8 — Solicitar Baja (HEB)

**Objetivo:** Iniciar formalmente el proceso de retirada de un bien del inventario activo.

**Disparador:** Un funcionario detecta que un bien ya no es útil para la institución (deterioro irreparable, pérdida, hurto, obsolescencia, u otra causa).

**Flujo principal:**

*Camino A — Aprobador ejecuta baja directa:*
1. El Aprobador selecciona el bien y activa la baja directa.
2. El sistema solicita confirmación e ingreso del motivo.
3. El sistema crea el registro de baja con estado Aprobado, el motivo, el Aprobador como solicitante y aprobador.
4. El sistema marca el bien como Dado de baja.
5. El sistema emite `BajaAprobada`.

*Camino B — Funcionario básico solicita baja:*
1. El funcionario selecciona el bien de su dependencia.
2. El sistema verifica que no existe una solicitud de baja pendiente para ese bien.
3. El sistema solicita confirmación e ingreso del motivo.
4. El sistema crea el registro de baja con estado Pendiente.
5. El sistema notifica a los Aprobadores sobre la solicitud pendiente.
6. El sistema emite `BajaSolicitada`.

**Resultado esperado (Camino A):** El bien está Dado de baja. El historial registra la baja directa.
**Resultado esperado (Camino B):** Existe una solicitud de baja Pendiente para el bien. El bien continúa Activo hasta que sea resuelta.

---

### Proceso 9 — Aprobar o Rechazar Baja (HEB)

**Objetivo:** Resolver las solicitudes de baja pendientes generadas por roles básicos.

**Disparador:** Un Aprobador revisa la lista de solicitudes de baja pendientes.

**Flujo principal:**
1. El Aprobador accede a la lista de solicitudes de baja pendientes.
2. Revisa los datos de la solicitud: bien afectado, motivo, solicitante, fecha de solicitud.
3. Decide:
   - Si Aprueba: el sistema ejecuta la baja lógica del bien y actualiza la solicitud a Aprobado. El sistema emite `BajaAprobada`.
   - Si Rechaza: el sistema actualiza la solicitud a Rechazado. El bien continúa Activo. El sistema emite `BajaRechazada`.

**Resultado esperado:** La solicitud queda resuelta. Si fue aprobada, el bien está Dado de baja. Si fue rechazada, el bien continúa Activo. El historial registra la decisión, el Aprobador y el momento.

---

### Proceso 10 — Consultar Historial

**Objetivo:** Obtener la trazabilidad completa de la vida de un bien en el inventario.

**Disparador:** Un auditor, coordinador o aprobador necesita conocer qué le ha sucedido a un bien en el pasado.

**Flujo principal:**
1. El actor selecciona el bien que desea consultar.
2. El sistema muestra el estado actual del bien (atributos, custodio actual, ubicación).
3. El actor selecciona el tipo de historial que desea consultar: modificaciones, bajas, custodios, ubicaciones, dependencias, o mantenimientos.
4. El sistema presenta el historial cronológico con todos los registros.

**Resultado esperado:** El actor puede reconstruir el estado del bien en cualquier momento pasado a partir de los registros del historial.

---

### Proceso 11 — Generar Acta de Entrega

**Objetivo:** Producir el documento formal que acredita la custodia de un conjunto de bienes por parte de un funcionario.

**Disparador:** Un Aprobador o Coordinador necesita formalizar documentalmente la custodia de bienes de un funcionario (al inicio de sus funciones, al cambiar de cargo, o para proceso de auditoría).

**Flujo principal:**
1. El actor selecciona el funcionario para quien se genera el acta.
2. El sistema identifica todos los bienes activos cuyo custodio activo es ese funcionario.
3. El sistema genera el documento con los datos del funcionario, los datos de cada bien bajo su custodia, y los datos institucionales.
4. El documento queda disponible para impresión o descarga.

**Resultado esperado:** El Acta de Entrega refleja la custodia vigente al momento de su generación. Es imprimible y está diseñada para ser firmada por el custodio.

---

## 8. Máquinas de Estado

---

### HMB — Máquina de Estados de Solicitud de Modificación

**Estados:**

- **Propuesta:** La solicitud ha sido creada por un funcionario básico. Existe en el sistema pero aún no ha sido presentada formalmente al Aprobador. *(En la implementación, este estado puede unificarse con Pendiente; se distingue aquí conceptualmente para mayor claridad.)*
- **Pendiente:** La solicitud ha sido registrada y está esperando revisión por parte de un Aprobador. Es visible en la cola de trabajo del Aprobador.
- **Aprobada:** El Aprobador ha aceptado el cambio propuesto. El valor del atributo en el bien ha sido actualizado. Estado terminal.
- **Rechazada:** El Aprobador ha rechazado el cambio propuesto. El bien no ha sido modificado. Estado terminal.

**Diagrama de transiciones:**

```
                    ┌──────────────────────────────────────────────┐
                    │        SOLICITUD DE MODIFICACIÓN (HMB)       │
                    └──────────────────────────────────────────────┘

 [Bien activo con campo X = valor_actual]
           │
           │  Funcionario básico propone cambio:
           │  campo = X
           │  valor_anterior = valor_actual
           │  valor_nuevo = valor_propuesto
           │
           ▼
 ┌─────────────────┐
 │    PENDIENTE    │ ◄── Visible en la cola del Aprobador
 │                 │     Notificación enviada al Aprobador
 └────────┬────────┘
          │
          │  El Aprobador revisa
          │
          ├─── Aprobador APRUEBA ────────────────────────────────►┌───────────┐
          │                                                         │ APROBADA  │
          │         bien.campo ← valor_propuesto                   │           │
          │         (atomico con el cambio de estado)              │ bien ha   │
          │                                                         │ cambiado  │
          │         Si campo = "dependencia":                       └───────────┘
          │           + Registro en historial_dependencias
          │           + Evento BienTrasladado
          │
          └─── Aprobador RECHAZA ───────────────────────────────►┌───────────┐
                                                                   │ RECHAZADA │
                                                                   │           │
                     bien.campo ← sin cambios                      │ bien no   │
                                                                   │ cambia    │
                                                                   └───────────┘

 RESTRICCIONES:
 ─────────────
 · Un campo con solicitud PENDIENTE no acepta nuevas solicitudes hasta ser resuelto.
 · Las solicitudes APROBADA y RECHAZADA son estados terminales (inmutables).
 · Los Aprobadores pueden modificar el campo directamente sin crear solicitud.
   La modificación directa registra evento BienModificado sin pasar por este flujo.
```

---

### HEB — Máquina de Estados de Solicitud de Baja

**Estados:**

- **Pendiente:** La solicitud de baja fue creada por un funcionario básico y está esperando revisión. El bien permanece activo.
- **Aprobado:** El Aprobador aceptó la baja. El bien ha sido marcado como Dado de baja. Estado terminal.
- **Rechazado:** El Aprobador rechazó la baja. El bien permanece activo. Estado terminal.

**Diagrama de transiciones:**

```
              ┌─────────────────────────────────────────────────────────┐
              │             SOLICITUD DE BAJA (HEB)                      │
              └─────────────────────────────────────────────────────────┘

 [Bien activo]
       │
       │  ¿Quién solicita la baja?
       │
       ├─── Aprobador (Administrador / Rectoría)
       │         │
       │         │  Baja directa: sin pasar por flujo de solicitud
       │         │  El sistema crea registro con estado = Aprobado
       │         ▼
       │    ┌───────────┐
       │    │ APROBADO  │ ← Bien marcado como Dado de baja
       │    │ (directo) │   Evento BajaAprobada emitido
       │    └───────────┘
       │
       └─── Funcionario básico (Auxiliar / Coordinador de la misma dependencia)
                 │
                 │  Verificaciones previas:
                 │    a. No existe solicitud PENDIENTE para este bien
                 │    b. El funcionario pertenece a la dependencia del bien
                 │
                 │  Registra solicitud + motivo
                 │
                 ▼
          ┌─────────────────┐
          │    PENDIENTE    │ ◄── Visible en HebIndex del Aprobador
          │                 │     Notificación enviada al Aprobador
          └────────┬────────┘
                   │
                   │  El Aprobador revisa
                   │
                   ├─── Aprobador APRUEBA ──────────────────────────►┌───────────┐
                   │                                                    │ APROBADO  │
                   │         bien ← Dado de baja (baja lógica)         │           │
                   │         (atómico con el cambio de estado)         │ bien sale  │
                   │         Evento BajaAprobada emitido               │ del activo │
                   │                                                    └───────────┘
                   │
                   └─── Aprobador RECHAZA ─────────────────────────►┌───────────┐
                                                                       │ RECHAZADO │
                             bien ← permanece activo                   │           │
                             Evento BajaRechazada emitido              │ bien activo│
                                                                       └───────────┘

 RESTRICCIONES:
 ─────────────
 · Un bien con solicitud PENDIENTE no acepta nuevas solicitudes de baja.
 · Los estados APROBADO y RECHAZADO son terminales (inmutables).
 · Un bien Dado de baja conserva toda su información y sus historiales.
 · Un bien Dado de baja no puede recibir nuevas operaciones (custodia,
   modificaciones, mantenimientos).
```

---

### Bien — Máquina de Estados del Bien

```
 [Sin existencia]
       │
       │  Proceso 1: Registrar Bien
       ▼
 ┌─────────────┐
 │    ACTIVO   │  Recibe: modificaciones, custodios, ubicaciones,
 │             │  traslados, mantenimientos, solicitudes HMB/HEB
 └──────┬──────┘
        │
        │  Baja aprobada (HEB)
        ▼
 ┌──────────────┐
 │ DADO DE BAJA │  Conserva toda información histórica.
 │              │  No recibe nuevas operaciones.
 └──────────────┘

 NOTA: No existe proceso de reactivación formal en APPSisGOE v1.
 Si un bien fue dado de baja incorrectamente, se debe registrar uno nuevo.
```

---

### Mantenimiento — Máquina de Estados

```
 [Sin existencia]
       │
       │  Proceso 6: Programar mantenimiento
       ▼
 ┌─────────────┐
 │  PENDIENTE  │  Mantenimiento programado, aún no ejecutado.
 │             │  Si fecha_programada < hoy: VENCIDO (alerta en dashboard)
 └──────┬──────┘
        │
        ├─── Se ejecuta → [REALIZADO]  Fecha real de ejecución registrada.
        │
        └─── Se cancela → [CANCELADO]  No se ejecutará.

 Los estados REALIZADO y CANCELADO son terminales.
 Los mantenimientos nunca se eliminan.
```

---

## 9. Eventos de Dominio

Los eventos de dominio representan hechos significativos que han ocurrido en el Dominio Inventario. Son la forma en que el dominio comunica al resto del sistema que algo cambió.

---

**BienRegistrado**
Significado: Un nuevo bien mueble ha sido incorporado al inventario institucional. Es el punto de inicio del ciclo de vida del bien.
Produce: El bien existe con estado Activo. El primer registro de custodia está abierto. Se pueden adjuntar detalles técnicos e imágenes a partir de este momento.

**BienModificado**
Significado: Un Aprobador ha modificado directamente uno o más atributos de un bien activo, sin pasar por el flujo de solicitud.
Produce: El atributo del bien tiene el nuevo valor. El cambio queda registrado con el responsable.

**ModificacionPropuesta**
Significado: Un funcionario básico ha creado una solicitud para cambiar el valor de un atributo de un bien. El bien no ha cambiado todavía.
Produce: Existe una solicitud de modificación en estado Pendiente. Los Aprobadores son notificados.

**ModificacionAprobada**
Significado: Un Aprobador ha aceptado la solicitud de modificación. El atributo del bien ahora tiene el valor propuesto.
Produce: El bien tiene el nuevo valor en el atributo. La solicitud está en estado Aprobada. Si el atributo era la dependencia, adicionalmente se emite BienTrasladado.

**ModificacionRechazada**
Significado: Un Aprobador ha rechazado la solicitud de modificación. El bien no ha cambiado.
Produce: La solicitud está en estado Rechazada. El bien tiene sus valores originales.

**CustodioAsignado**
Significado: Un funcionario ha sido designado como custodio de un bien. Este evento marca el inicio de una nueva custodia.
Produce: Existe un registro de custodia abierto para el funcionario y el bien. El funcionario tiene responsabilidad formal sobre el bien.

**CustodioLiberado**
Significado: El registro de custodia de un funcionario sobre un bien ha sido cerrado. El funcionario ya no es responsable del bien.
Produce: El registro de custodia tiene fecha de retiro. La cadena de custodia tiene un eslabón cerrado.
Nota: CustodioLiberado y CustodioAsignado siempre ocurren en la misma operación atómica.

**BienTrasladado**
Significado: Un bien ha cambiado de dependencia administrativa. La responsabilidad institucional sobre el bien ha pasado de una unidad a otra.
Produce: El bien tiene la nueva dependencia. El historial de dependencias registra el traslado con origen, destino y responsable.

**UbicacionCambiada**
Significado: Un bien ha cambiado de ubicación física dentro del plantel.
Produce: El historial de ubicaciones registra el movimiento con la ubicación anterior, la nueva y el responsable del movimiento.

**MantenimientoProgramado**
Significado: Se ha planificado una actividad de mantenimiento para un bien.
Produce: Existe un registro de mantenimiento en estado Pendiente con fecha programada.

**MantenimientoRealizado**
Significado: Un mantenimiento programado fue ejecutado.
Produce: El registro de mantenimiento está en estado Realizado con la fecha real de ejecución.

**MantenimientoCancelado**
Significado: Un mantenimiento programado no se ejecutará y ha sido cancelado formalmente.
Produce: El registro de mantenimiento está en estado Cancelado.

**BajaSolicitada**
Significado: Un funcionario básico ha creado una solicitud formal para dar de baja un bien.
Produce: Existe una solicitud de baja en estado Pendiente. Los Aprobadores son notificados. El bien continúa activo.

**BajaAprobada**
Significado: La baja de un bien ha sido autorizada y ejecutada. El bien sale del inventario activo.
Produce: El bien está en estado Dado de baja. La solicitud está en estado Aprobado (si existía) o el sistema registra la baja directa. Los historiales del bien permanecen íntegros.

**BajaRechazada**
Significado: La solicitud de baja de un bien fue revisada y rechazada por el Aprobador.
Produce: La solicitud está en estado Rechazado. El bien continúa en estado Activo sin modificaciones.

**ActaGenerada**
Significado: Se ha producido un Acta de Entrega formal para un funcionario custodio.
Produce: El documento refleja el estado de custodia en el momento de su generación. Queda disponible para impresión.

---

## 10. Indicadores del Dominio

El dashboard del Dominio Inventario transforma los datos del patrimonio institucional en información de decisión. Los indicadores se clasifican en cuatro tipos según el tipo de decisión que apoyan.

---

### Indicadores Operativos

Los indicadores operativos responden la pregunta: ¿cuál es el estado actual del inventario?

- **Total de bienes activos:** Cuántos bienes está gestionando actualmente la institución. Es el indicador fundamental de la magnitud del patrimonio.

- **Bienes por dependencia:** Cuántos bienes tiene asignada cada unidad administrativa. Permite identificar concentraciones de activos y dependencias con mayor responsabilidad patrimonial.

- **Bienes por categoría:** Distribución del inventario según la clasificación de los bienes. Permite visualizar la composición del patrimonio: cuánto es mobiliario, cuánto es tecnología, cuánto es material didáctico.

- **Custodios activos:** Cuántos funcionarios tienen bienes bajo su responsabilidad. Es un indicador de la distribución de la responsabilidad patrimonial en la institución.

- **Bienes por condición física:** Distribución del inventario según el estado de conservación (Nuevo, Bueno, Regular, Malo). Permite anticipar necesidades de mantenimiento o reposición.

- **Bienes estratégicos TIC:** Conteo específico de los equipos tecnológicos más críticos: portátiles, computadores de escritorio, tablets, video beam, impresoras, televisores. La tecnología es el activo institucional de mayor valor unitario y mayor riesgo de pérdida.

---

### Indicadores de Control

Los indicadores de control responden la pregunta: ¿qué requiere atención inmediata?

- **Solicitudes de modificación pendientes (HMB pendientes):** Cuántas propuestas de cambio de atributos de bienes están esperando revisión. Un número alto indica que los Aprobadores tienen trabajo acumulado o que el proceso está bloqueado.

- **Solicitudes de baja pendientes (HEB pendientes):** Cuántas solicitudes de baja están esperando aprobación. Bienes que deberían salir del inventario activo pero aún no han sido formalmente procesados.

- **Mantenimientos vencidos:** Cuántos mantenimientos programados no se ejecutaron en la fecha prevista. Es un indicador de riesgo: los mantenimientos vencidos representan bienes con mayor probabilidad de daño.

- **Bienes sin custodio:** Cuántos bienes activos no tienen un custodio asignado en este momento. Cada bien sin custodio es una irregularidad patrimonial que debe resolverse. El objetivo es que este indicador sea siempre cero.

- **Bienes sin ubicación registrada:** Cuántos bienes activos no tienen ningún registro de ubicación física. Indica bienes que no pueden ser localizados.

---

### Indicadores de Gestión

Los indicadores de gestión responden la pregunta: ¿cómo está evolucionando el patrimonio?

- **Bienes dados de baja:** Cuántos bienes han sido retirados del inventario activo. Permite calcular la tasa de rotación del patrimonio.

- **Tasa de bienes activos:** Porcentaje del total histórico de bienes que aún permanecen activos. `activos / (activos + dados de baja) × 100`. Una tasa decreciente puede indicar obsolescencia acelerada del patrimonio.

- **Tasa de bienes en mantenimiento:** Porcentaje de bienes activos que tienen al menos un mantenimiento pendiente. Una tasa alta puede indicar un patrimonio envejecido o que requiere inversión en renovación.

- **Grupos institucionales:** Agrupaciones semánticas del inventario por función: Mobiliario, Equipos Tecnológicos, Equipos Audiovisuales, Material Didáctico, Instrumentos Musicales, Herramientas, Equipos Administrativos. Cada grupo muestra el total de bienes y su porcentaje del inventario. Permite a la dirección institucional entender la composición del patrimonio en términos de misión institucional, no solo de clasificación técnica.

- **Top 10 dependencias por volumen:** Las diez dependencias con mayor cantidad de bienes asignados. Útil para la planificación de verificaciones físicas y para entender la distribución de la responsabilidad patrimonial.

---

### Indicadores de Auditoría

Los indicadores de auditoría responden la pregunta: ¿qué tan confiable y completa es la información del inventario?

- **Índice de calidad de datos:** Promedio de cinco métricas de completitud del registro de bienes:
  - Porcentaje de bienes con custodio asignado.
  - Porcentaje de bienes con ubicación registrada.
  - Porcentaje de bienes con categoría asignada.
  - Porcentaje de bienes con condición física registrada.
  - Porcentaje de bienes con origen registrado.
  Un índice del 100% indica que ningún bien tiene campos obligatorios en blanco. El objetivo institucional es mantener este índice lo más cercano posible al 100%.

- **Últimas modificaciones aprobadas:** Registro de las modificaciones de atributos de bienes aprobadas más recientemente. Permite al auditor verificar que los cambios son razonables y están respaldados.

- **Últimas bajas registradas:** Registro de los bienes dados de baja más recientemente, con sus motivos. Permite detectar patrones anómalos (muchas bajas en poco tiempo, bienes nuevos dados de baja, etc.).

- **Últimos movimientos de ubicación:** Registro de los cambios de ubicación física más recientes. Útil para la verificación del inventario físico.

---

## 11. Límites del Dominio

---

### Qué pertenece al Dominio Inventario

Todo lo relacionado con el ciclo de vida de bienes muebles institucionales:

- El registro, consulta, modificación y baja de bienes.
- Los catálogos maestros propios: categorías, dependencias, ubicaciones, condiciones físicas, tipos de almacenamiento, tipos de mantenimiento, orígenes.
- El flujo HMB: solicitudes, estados, resoluciones, historial.
- El flujo HEB: solicitudes, estados, resoluciones, historial.
- La cadena de custodia: asignaciones, retiros, historial de custodios.
- Los historiales de dominio: modificaciones, bajas, dependencias, ubicaciones.
- La agenda de mantenimientos programados.
- El detalle técnico de los bienes (marca, color, material, características).
- Las imágenes de los bienes.
- Las actas de entrega.
- El dashboard de indicadores patrimoniales.
- Las notificaciones relacionadas con eventos del dominio (aunque la interfaz de visualización de notificaciones pertenece al CORE).

---

### Qué pertenece al CORE de APPSisGOE

El Dominio Inventario consume estos servicios del CORE pero no los implementa:

- La **autenticación** de usuarios (login, sesión, bloqueo de cuenta).
- El **control de acceso** (quién puede entrar al módulo Inventario, qué permisos tiene cada rol).
- El **registro transversal de actividad** del sistema (quién hizo qué en el sistema, en cualquier módulo).
- La **interfaz de notificaciones** (el icono de campana y la lista de notificaciones en la pantalla).
- La **gestión de usuarios** (crear, editar y eliminar cuentas de funcionarios).
- Los **roles institucionales** (la definición de Administrador, Rectoría, Coordinación, etc.).

---

### Qué pertenece a otros dominios

Hay áreas que están fuera del Dominio Inventario aunque comparten conceptos con él:

- **Dominio Biblioteca:** Los libros, revistas y materiales del acervo documental de la institución no son bienes del Inventario. Son responsabilidad del Dominio Biblioteca. Sin embargo, el mobiliario y la tecnología de la sala de biblioteca (estantes, computadores, lectores de código de barras) sí son bienes del Inventario.

- **Dominio Préstamo de Tabletas:** Si la institución gestiona préstamo de tablets a estudiantes para uso externo, ese proceso es responsabilidad de un dominio separado. Las tablets como activos físicos sí están en el Inventario, pero el flujo de préstamo y devolución pertenece al Dominio Préstamo.

- **Dominio Financiero (futuro):** La valoración contable formal del patrimonio, la depreciación de activos y los informes financieros pertenecerían a un dominio financiero especializado, que consumiría datos del Inventario pero tendría su propia lógica de negocio.

- **Gestión de dependencias como unidades organizacionales:** Si otro módulo (por ejemplo, el módulo Evaluación Docente) necesita saber a qué dependencia pertenece un funcionario, no puede consumir directamente los datos de dependencias del Inventario. Las dependencias del Inventario son catálogos patrimoniales, no el organigrama institucional general.

---

### Frontera crítica: Dependencias

El concepto de "dependencia" existe tanto en el Dominio Inventario como en el organigrama general de la institución. En el Dominio Inventario, una dependencia es la unidad administrativa responsable de un conjunto de bienes. Este concepto puede o no coincidir con la estructura organizacional general. Esta ambigüedad debe resolverse explícitamente en el momento en que otro módulo necesite referenciar dependencias: se define si se trata de la misma entidad o de conceptos distintos con el mismo nombre.

---

## 12. Riesgos Operativos

---

### RIESGO-INV-001 — Pérdida de trazabilidad por baja sin flujo

**Descripción:** Un bien desaparece del inventario activo sin que exista un registro formal de quién autorizó la baja, con qué justificación y en qué momento.

**Cuándo ocurre:** Cuando el sistema permite eliminar directamente un bien sin pasar por el proceso HEB, o cuando el proceso HEB puede completarse sin quedar registrado.

**Consecuencia:** Imposibilidad de responder a una auditoría patrimonial sobre el bien. Responsabilidad administrativa para los funcionarios a cargo del inventario.

**Reglas de negocio que lo mitigan:** RN-015, RN-016, RN-020, RN-022, RN-036.

---

### RIESGO-INV-002 — Custodias inconsistentes

**Descripción:** Un bien tiene múltiples custodios activos simultáneos, o no tiene ningún custodio activo, o el sistema permite asignar un custodio sin cerrar el anterior.

**Cuándo ocurre:** Cuando el cambio de custodio no es atómico: el registro anterior se cierra pero el nuevo no se abre (o viceversa), o cuando ambos registros quedan abiertos.

**Consecuencia:** Dos funcionarios creen ser responsables del mismo bien (nadie asume responsabilidad real). O nadie es responsable y el bien queda huérfano. Ambas situaciones son irregularidades patrimoniales.

**Reglas de negocio que lo mitigan:** RN-002, RN-023, RN-024.

---

### RIESGO-INV-003 — Flujo HEB sin UI de aprobación (brecha heredada)

**Descripción:** Las solicitudes de baja creadas por roles básicos quedan en estado Pendiente indefinidamente porque no existe una interfaz para que el Aprobador las revise y resuelva.

**Cuándo ocurre:** Si se implementa la creación de solicitudes de baja pero no se implementa la interfaz de aprobación/rechazo.

**Consecuencia:** Los bienes que deberían darse de baja permanecen en el inventario activo. Los funcionarios básicos no pueden completar el proceso y la solicitud queda en un limbo permanente. La dirección no puede cumplir su rol de Aprobador.

**Nota:** Esta brecha existía en BhagamAppsModular (sistema predecesor). APPSisGOE debe corregirla como condición obligatoria antes del lanzamiento del módulo.

**Reglas de negocio que lo mitigan:** RN-020, RN-021 (definición del flujo bilateral).

---

### RIESGO-INV-004 — Modificaciones sin aprobación por bypass del flujo

**Descripción:** Un funcionario de rol básico logra modificar directamente un atributo de un bien sin crear una solicitud de modificación, obviando el proceso HMB.

**Cuándo ocurre:** Si existen rutas alternativas en el sistema para modificar datos del bien que no pasan por el control de autorización.

**Consecuencia:** El inventario contiene datos que no han sido validados por la autoridad competente. La trazabilidad de cambios está incompleta.

**Reglas de negocio que lo mitigan:** RN-007, RN-009.

---

### RIESGO-INV-005 — Identificación inestable de grupos institucionales

**Descripción:** Los grupos semánticos de categorías (Mobiliario, TIC, Audiovisuales...) están definidos en el sistema usando identificadores internos variables del catálogo en lugar de códigos semánticos estables.

**Cuándo ocurre:** Si los grupos institucionales se construyen usando el identificador de base de datos de las categorías (por ejemplo: "categorías con ID 1 y 20 = Mobiliario") en lugar de un código estable.

**Consecuencia:** Si el catálogo de categorías se migra, se reimporta, o si el orden de creación de categorías cambia, los grupos institucionales del dashboard muestran datos incorrectos sin ningún error visible. El problema es silencioso y difícil de detectar.

**Reglas de negocio que lo mitigan:** RN-034.

---

### RIESGO-INV-006 — Información desactualizada en catálogos

**Descripción:** Los catálogos maestros (categorías, dependencias, ubicaciones) están desactualizados respecto a la realidad institucional: dependencias que ya no existen, ubicaciones renombradas, categorías que ya no se usan.

**Cuándo ocurre:** Cuando no existe un proceso periódico de revisión y actualización de catálogos, o cuando hay resistencia institucional a eliminar opciones del catálogo por temor a perder datos históricos.

**Consecuencia:** Los usuarios registran bienes en dependencias inexistentes o desaparecidas. Los indicadores del dashboard agrupan datos en categorías que no corresponden a la realidad actual.

**Reglas de negocio que lo mitigan:** RN-033, RN-035.

---

### RIESGO-INV-007 — Pérdida de información por eliminación física de datos

**Descripción:** Datos de historial, custodia, modificaciones o bajas son eliminados del sistema en lugar de conservarse de forma inmutable.

**Cuándo ocurre:** Si el sistema permite operaciones DELETE sobre tablas de historial, o si los procesos de limpieza de datos incluyen estas tablas.

**Consecuencia:** Pérdida de trazabilidad patrimonial. En una auditoría, el sistema no puede responder sobre acciones pasadas. Posible responsabilidad administrativa por destrucción de registros institucionales.

**Reglas de negocio que lo mitigan:** RN-024, RN-028, RN-032, RN-036.

---

### RIESGO-INV-008 — Actas de entrega que no reflejan la custodia real

**Descripción:** Un Acta de Entrega se genera pero no corresponde al estado real de la custodia porque los datos de bienes_responsables están desactualizados.

**Cuándo ocurre:** Si el proceso de cambio de custodio no mantiene la cadena de custodia actualizada, o si existen bienes con múltiples custodios activos simultáneos.

**Consecuencia:** El acta firmada no tiene validez para atribuir responsabilidad al custodio, porque el sistema no puede garantizar que los bienes listados son realmente los que están bajo su cuidado.

**Reglas de negocio que lo mitigan:** RN-002, RN-021, RN-023, RN-037.

---

## 13. Modelo Conceptual Integrado

---

### Mapa Conceptual del Dominio

```
╔══════════════════════════════════════════════════════════════════════════════╗
║                        DOMINIO INVENTARIO                                    ║
║                                                                              ║
║  CATÁLOGOS MAESTROS                                                          ║
║  ┌────────────────────────────────────────────────────────────────────────┐  ║
║  │  Categorías · Dependencias · Ubicaciones · Condiciones · Orígenes     │  ║
║  │  Tipos de almacenamiento · Tipos de mantenimiento                      │  ║
║  └──────────────────────────────────────────┬───────────────────────────┘  ║
║                                             │ clasifica / ubica             ║
║                                             ▼                               ║
║                             ╔══════════════════════════╗                    ║
║                             ║          BIEN             ║ ◄─ entidad raíz   ║
║                             ║  ─────────────────────── ║                    ║
║                             ║  placa · serie · nombre   ║                    ║
║                             ║  categoría · dependencia  ║                    ║
║                             ║  condición · origen       ║                    ║
║                             ║  valor · fecha adquisición║                    ║
║                             ╚═══════════╤══════════════╝                    ║
║                                         │                                   ║
║        ┌────────────────────────────────┼──────────────────────────────┐   ║
║        │                                │                              │   ║
║        ▼                                ▼                              ▼   ║
║  ┌─────────────┐             ┌──────────────────┐            ┌──────────┐  ║
║  │  CUSTODIA   │             │  MODIFICACIÓN    │            │   BAJA   │  ║
║  │ ─────────── │             │  CONTROLADA      │            │ ──────── │  ║
║  │ custodio    │             │  (HMB)           │            │ (HEB)    │  ║
║  │ fecha inicio│             │ ──────────────── │            │ motivo   │  ║
║  │ fecha retiro│             │ campo · valor old │            │ estado   │  ║
║  │             │             │ valor new · estado│            │ aprobador│  ║
║  │  CADENA DE  │             │ aprobador         │            └──────────┘  ║
║  │  CUSTODIA   │             └──────────────────┘                          ║
║  └─────────────┘                                                            ║
║                                                                              ║
║        ▼                                ▼                                   ║
║  ┌──────────────┐             ┌────────────────────┐                        ║
║  │ HISTORIAL    │             │   MANTENIMIENTO    │                        ║
║  │ UBICACIONES  │             │   PROGRAMADO       │                        ║
║  │              │             │ ────────────────── │                        ║
║  │ HISTORIAL    │             │ tipo · título      │                        ║
║  │ DEPENDENCIAS │             │ fecha · estado     │                        ║
║  │ (traslados)  │             │ preventivo/correct.│                        ║
║  └──────────────┘             └────────────────────┘                        ║
║                                                                              ║
║                              ┌────────────────────┐                        ║
║                              │  DETALLE TÉCNICO   │                        ║
║                              │  marca · color      │                        ║
║                              │  material · medidas │                        ║
║                              └────────────────────┘                        ║
╚══════════════════════════════════════════════════════════════════════════════╝
```

---

### Mapa de Actores

```
╔══════════════════════════════════════════════════════════════════════════════╗
║                            ACTORES DEL DOMINIO                               ║
╠══════════════════════════════════════════════════════════════════════════════╣
║                                                                              ║
║   REGISTRA / PROPONE                    APRUEBA / EJECUTA                   ║
║   ─────────────────                     ──────────────────                  ║
║                                                                              ║
║   Auxiliar de Inventario                Administrador                        ║
║   · Registra bienes nuevos              · Modifica bienes directamente       ║
║   · Propone modificaciones (HMB)        · Aprueba/rechaza HMB                ║
║   · Solicita bajas (HEB)                · Ejecuta bajas directas             ║
║   · Registra movimientos                · Aprueba/rechaza HEB                ║
║   · Programa mantenimientos             · Asigna custodios                   ║
║                                         · Gestiona catálogos                 ║
║   Coordinador de Dependencia            · Ve todos los indicadores           ║
║   · Supervisa bienes de su unidad                                            ║
║   · Propone modificaciones (HMB)        Rectoría                             ║
║   · Solicita traslados                  · Mismas capacidades que Admin       ║
║   · Solicita bajas (HEB)                · Orienta el proceso patrimonial     ║
║   · Genera actas de entrega                                                  ║
║                                                                              ║
║   ─────────────────────────────────────────────────────────────────         ║
║                                                                              ║
║   CONSULTA / AUDITA                     TIENE LA CUSTODIA                   ║
║   ─────────────────                     ─────────────────                   ║
║                                                                              ║
║   Auditor Institucional                 Custodio                             ║
║   · Consulta historiales                · Tiene bienes bajo su cargo        ║
║   · Verifica integridad del proceso     · Firma actas de entrega             ║
║   · Revisa índice de calidad de datos   · Reporta daños o novedades         ║
║   · Lee (nunca modifica)                · Cuida la conservación del bien    ║
║                                                                              ║
╚══════════════════════════════════════════════════════════════════════════════╝
```

---

### Mapa de Procesos

```
╔══════════════════════════════════════════════════════════════════════════════╗
║                          PROCESOS DEL DOMINIO                                ║
╠══════════════════════════════════════════════════════════════════════════════╣
║                                                                              ║
║  ENTRADA AL INVENTARIO                                                       ║
║  ────────────────────                                                        ║
║  Proceso 1: Registrar Bien                                                   ║
║  └─► BienRegistrado + CustodioAsignado (primer custodio)                    ║
║                                                                              ║
║  OPERACIONES SOBRE EL BIEN ACTIVO                                            ║
║  ─────────────────────────────────                                           ║
║  Proceso 2: Modificar campo (HMB)  ──────────────────────────────────┐     ║
║    · Camino A: Aprobador modifica directo → BienModificado            │     ║
║    · Camino B: Básico propone → Pendiente → Aprobación/Rechazo        │     ║
║                                                                        │     ║
║  Proceso 3: Trasladar bien ─────────────────────────────── (caso      │     ║
║    · Es Proceso 2 con campo = dependencia                  especial   │     ║
║    · + BienTrasladado + historial dependencias)            de Proc.2) ┘     ║
║                                                                              ║
║  Proceso 4: Cambiar ubicación física                                         ║
║    · Inmediato, sin aprobación                                               ║
║    · + UbicacionCambiada + historial ubicaciones                            ║
║                                                                              ║
║  Proceso 5: Cambiar custodio                                                 ║
║    · Atómico: cierra anterior, abre nuevo                                   ║
║    · + CustodioLiberado + CustodioAsignado                                  ║
║                                                                              ║
║  Proceso 6: Programar mantenimiento → MantenimientoProgramado               ║
║  Proceso 7: Registrar mantenimiento realizado → MantenimientoRealizado      ║
║                                                                              ║
║  SALIDA DEL INVENTARIO ACTIVO                                                ║
║  ────────────────────────────                                                ║
║  Proceso 8: Solicitar baja (HEB)                                             ║
║    · Camino A: Aprobador baja directo → BajaAprobada → Bien dado de baja   ║
║    · Camino B: Básico solicita → Pendiente                                  ║
║  Proceso 9: Aprobar/Rechazar baja                                            ║
║    · Aprueba → BajaAprobada → Bien dado de baja (datos conservados)         ║
║    · Rechaza → BajaRechazada → Bien continúa activo                         ║
║                                                                              ║
║  DOCUMENTACIÓN Y CONSULTA                                                    ║
║  ─────────────────────────                                                   ║
║  Proceso 10: Consultar historial (solo lectura)                              ║
║  Proceso 11: Generar Acta de Entrega                                         ║
║                                                                              ║
╚══════════════════════════════════════════════════════════════════════════════╝
```

---

### Mapa de Eventos

```
╔══════════════════════════════════════════════════════════════════════════════╗
║                          MAPA DE EVENTOS                                     ║
╠══════════════════════════════════════════════════════════════════════════════╣
║                                                                              ║
║  ALTA                       MODIFICACIÓN                   BAJA              ║
║  ────                       ────────────                   ────              ║
║  BienRegistrado             ModificacionPropuesta          BajaSolicitada    ║
║       │                          │                              │            ║
║       ▼                          │  aprobada                    │ aprobada   ║
║  CustodioAsignado           ModificacionAprobada           BajaAprobada     ║
║                                  │  rechazada                   │            ║
║                             ModificacionRechazada           BajaRechazada   ║
║                                  │                                           ║
║                             BienModificado                                   ║
║                             (modificación directa)                           ║
║                                                                              ║
║  CUSTODIA                   UBICACIÓN                    MANTENIMIENTO       ║
║  ────────                   ─────────                    ─────────────       ║
║  CustodioAsignado           BienTrasladado               MantenimientoProg. ║
║  CustodioLiberado           (dependencia)                MantenimientoReal. ║
║       │                     UbicacionCambiada            MantenimientoCanc. ║
║       │                     (ubicación física)                               ║
║       └── ambos siempre                                                      ║
║           en la misma                                                        ║
║           operación atómica                                                  ║
║                                                                              ║
║  DOCUMENTACIÓN                                                               ║
║  ─────────────                                                               ║
║  ActaGenerada                                                                ║
║                                                                              ║
╚══════════════════════════════════════════════════════════════════════════════╝
```

---

## 14. Criterios de Éxito

El Dominio Inventario puede considerarse exitoso dentro de APPSisGOE cuando la institución puede responder afirmativamente a las siguientes preguntas:

---

### Trazabilidad

- ¿Puede el sistema responder quién tiene cualquier bien en este momento? **Sí, si cada bien tiene custodio activo (RN-002).**
- ¿Puede el sistema responder quién tuvo un bien en cualquier fecha pasada? **Sí, si la cadena de custodia está completa e inmutable (RN-024).**
- ¿Puede el sistema responder qué modificaciones se hicieron a un bien, quién las propuso y quién las aprobó? **Sí, si el flujo HMB está completo e inmutable (RN-013).**
- ¿Puede el sistema responder por qué y por autorización de quién fue dado de baja un bien? **Sí, si el flujo HEB está completo (incluyendo la UI de aprobación) e inmutable (RN-022).**
- ¿Puede el sistema responder dónde ha estado físicamente un bien a lo largo del tiempo? **Sí, si el historial de ubicaciones está completo (RN-028).**

---

### Control

- ¿Todo bien activo tiene un custodio activo en todo momento? **El indicador "bienes sin custodio" debe ser cero.**
- ¿Todas las modificaciones de bienes propuestas por roles básicos fueron revisadas por un Aprobador? **El indicador "HMB pendientes" debe ser gestionado activamente.**
- ¿Todas las solicitudes de baja pendientes pueden ser revisadas y resueltas por los Aprobadores? **El proceso HEB tiene interfaz de aprobación completa (criterio de lanzamiento).**
- ¿Los mantenimientos vencidos son identificados y gestionados? **El indicador "mantenimientos vencidos" es visible en el dashboard.**

---

### Calidad de datos

- ¿El índice de calidad de datos supera el 90%? **Los bienes tienen categoría, custodio, condición, origen y ubicación completos.**
- ¿Los grupos institucionales del dashboard reflejan correctamente la composición del patrimonio? **Las categorías tienen códigos estables y los grupos se identifican por esos códigos, no por identificadores internos.**

---

### Auditabilidad

- ¿Puede un auditor externo verificar la integridad del inventario sin depender de la buena fe de los funcionarios? **Los historiales son inmutables y el sistema muestra el rastro completo de cada bien.**
- ¿Las actas de entrega firmadas reflejan con exactitud los bienes bajo custodia de cada funcionario? **La cadena de custodia está actualizada y el acta se genera desde los datos reales de custodia.**
- ¿Un bien dado de baja es siempre identificable con su motivo y autorización? **Toda baja tiene registro en el HEB con estado, motivo y Aprobador.**

---

### Integración institucional

- ¿Los directivos tienen información suficiente para tomar decisiones sobre el patrimonio sin necesidad de exportar datos a Excel ni hacer consultas manuales? **El dashboard presenta KPIs, distribuciones, alertas y calidad de datos en tiempo real.**
- ¿El módulo Inventario puede activarse, configurarse y operarse sin modificar el núcleo de APPSisGOE? **El módulo es autónomo, declara sus capacidades en su manifiesto, y consume los servicios del CORE sin acoplamiento.**

---

*Fin del documento DOM-INV-001 — Dominio Inventario v1.0.0*
*Vigente desde: 2026-06-14*
*Base para: DAT-INV-001 (Modelo de Datos) · APP-INV-001 (Arquitectura de Aplicación) · UI-INV-001 (Experiencia de Usuario)*
