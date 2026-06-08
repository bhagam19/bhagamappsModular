# ADR-001 — Arquitectura Modular con nwidart/laravel-modules

**Estado:** Aceptado
**Fecha:** [fecha de inicio del proyecto]
**Contexto:** BhagamApps Modular — Sistema de Gestión Escolar

---

## Contexto

BhagamApps necesita soportar múltiples dominios funcionales (gestión de usuarios,
inventario institucional, catálogo de aplicaciones, herramientas de desarrollo)
dentro de una sola instancia de Laravel, con la posibilidad de activar, desactivar
o extender cada dominio de forma independiente.

El proyecto vive en un subdirectorio (`/Modular`) dentro de un dominio existente,
y es mantenido por un equipo reducido con necesidad de separación clara de
responsabilidades entre dominios.

---

## Decisión

Se adopta **nwidart/laravel-modules** como sistema de modularización de la aplicación.

Cada dominio funcional es un módulo independiente bajo `Modules/<Nombre>/`, con su
propia estructura de directorios: `Entities`, `Http`, `Livewire`, `Database`,
`Routes`, `Resources`, `Providers`, `Tests`.

**Módulos actuales:**
| Módulo         | Dominio                                    |
|----------------|--------------------------------------------|
| `User`         | Gestión de usuarios, roles y permisos      |
| `Inventario`   | Inventario de bienes institucionales       |
| `Apps`         | Catálogo de aplicaciones asignadas         |
| `CrudGenerator`| Herramienta interna de generación de código|

---

## Consecuencias positivas

- **Separación de dominios:** los cambios en Inventario no afectan a User y viceversa.
- **Escalabilidad horizontal:** agregar un nuevo módulo (`Comunidad`, `Biblioteca`) es
  una operación aislada que no toca el código existente.
- **Versionado independiente:** cada módulo puede evolucionar a su propio ritmo.
- **Equipos paralelos:** diferentes desarrolladores pueden trabajar en módulos distintos
  sin conflictos frecuentes.
- **Testing aislado:** cada módulo tiene su propio directorio `Tests/`.

## Consecuencias negativas y mitigaciones

| Consecuencia | Mitigación |
|---|---|
| El modelo `User` vive en `Modules\User\Entities\User`, no en `App\Models\User`. Puede confundir. | Convención documentada: siempre usar el namespace del módulo. |
| Los imports cross-módulo crean acoplamiento implícito. | Minimizar dependencias entre módulos. El módulo `Inventario` no importa clases de `CrudGenerator`. |
| Complejidad de configuración inicial de Livewire en subdirectorio. | `setUpdateRoute()` configurado en `AppServiceProvider` (ver ADR-002). |

---

## Alternativas consideradas

- **Monolito sin módulos:** descartado por mezcla de dominios en `app/`.
- **Microservicios:** descartado por overhead operacional y complejidad innecesaria
  para el tamaño del sistema y el equipo.
- **Spatie Laravel Modules / manual:** nwidart tiene mayor ecosistema y documentación.
