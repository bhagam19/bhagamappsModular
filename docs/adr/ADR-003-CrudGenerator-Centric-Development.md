# ADR-003 — CrudGenerator como Herramienta Central de Generación de Código

**Estado:** Aceptado
**Fecha:** [fecha de inicio del proyecto]
**Contexto:** BhagamApps Modular — workflow de desarrollo de nuevas funcionalidades

---

## Contexto

BhagamApps es un sistema en evolución activa donde se agregan regularmente nuevas
entidades (bienes, actas, usuarios, apps). Crear manualmente Controllers, Models,
Migrations, Views y Livewire components para cada entidad es repetitivo y propenso
a inconsistencias en las convenciones del proyecto.

El equipo necesita una forma de generar código que respete las convenciones
establecidas (AdminLTE, Livewire-first, RBAC propio) sin copiar y pegar código
entre módulos.

---

## Decisión

Se implementa el módulo **CrudGenerator** (`Modules/CrudGenerator/`) como herramienta
interna de generación de código scaffolding para nuevas entidades en cualquier módulo.

**Responsabilidades del CrudGenerator:**
- Generar la estructura base de un nuevo recurso (Controller, Entity, Migration,
  Livewire components Index/Create/Edit, routes, views).
- Respetar las convenciones del proyecto: namespace modular, layouts AdminLTE,
  permisos via `hasPermission()`, componentes Livewire en `Modules/<X>/Livewire/`.
- Funcionar tanto desde consola (`php artisan`) como desde la interfaz web del módulo.

**Política de modificación:**
El módulo CrudGenerator es infraestructura de desarrollo y **no se toca** durante
fases de corrección de bugs, hardening de seguridad o documentación. Solo se modifica
en fases explícitas de evolución del generador.

---

## Consecuencias positivas

- Consistencia en la generación de código entre módulos.
- Reduce el tiempo de creación de nuevas entidades.
- Las convenciones del proyecto quedan codificadas en las plantillas del generador.
- Al modificar una plantilla, todos los futuros recursos generados heredan el cambio.

## Consecuencias negativas y mitigaciones

| Consecuencia | Mitigación |
|---|---|
| El código generado puede quedar desactualizado si las convenciones del proyecto evolucionan | Actualizar las plantillas del CrudGenerator en paralelo con los cambios de convención |
| El módulo en sí no tiene permisos de acceso RBAC granulares (es una herramienta interna) | Acceso restringido por rol a nivel de ruta; no expuesto en producción a usuarios finales |
| El código generado es punto de partida, no código de producción final | Documentado: el código generado siempre requiere revisión y ajuste antes de hacer merge |

---

## Relación con otros módulos

El CrudGenerator **genera** código para otros módulos pero **no depende** de ellos.
Los módulos generados tampoco importan clases del CrudGenerator en producción.
La relación es unidireccional y solo ocurre en tiempo de desarrollo.

---

## Alternativas consideradas

- **Artisan stubs personalizados:** limitado a la estructura de Laravel base, no
  genera Livewire components ni respeta convenciones AdminLTE.
- **Blueprint (Laravel Blueprint):** buena herramienta pero no integrada en la
  interfaz web de la aplicación ni adaptada a la arquitectura modular de nwidart.
- **Copiar y pegar entre módulos:** descartado por inconsistencias y divergencia
  de convenciones a lo largo del tiempo.
