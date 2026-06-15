# ARCH-ANALYSIS-001 — Análisis Arquitectónico: BhagamApps → APPSisGOE

**Fecha:** 2026-06-14  
**Fuentes:** AUDIT-RBAC-001.md · AUDIT-INV-002-APPSisGOE.md · AUDIT-APPS-002-APPSisGOE.md  
**Propósito:** Puente entre el conocimiento extraído de BhagamApps y la arquitectura objetivo de APPSisGOE.  
**Base para:** ARCH-001 (Arquitectura Ejecutable de APPSisGOE)

---

## Índice

1. [Clasificación de componentes: qué migrar y cómo](#1-clasificación-de-componentes-qué-migrar-y-cómo)
2. [El CORE definitivo de APPSisGOE](#2-el-core-definitivo-de-appsisgoe)
3. [Servicios compartidos derivados de Inventario](#3-servicios-compartidos-derivados-de-inventario)
4. [¿Debe APPSisGOE conservar la arquitectura RBAC actual?](#4-debe-appsisgoe-conservar-la-arquitectura-rbac-actual)
5. [Arquitectura objetivo del módulo Apps](#5-arquitectura-objetivo-del-módulo-apps)
6. [Errores arquitectónicos críticos — cómo evitarlos](#6-errores-arquitectónicos-críticos--cómo-evitarlos)
7. [Aciertos arquitectónicos — cómo potenciarlos](#7-aciertos-arquitectónicos--cómo-potenciarlos)
8. [Roadmap recomendado para APPSisGOE](#8-roadmap-recomendado-para-appsisgoe)

---

## 1. Clasificación de componentes: qué migrar y cómo

### Criterio de clasificación

El análisis no evalúa código sino conceptos y patrones. Un componente "se adopta sin cambios" cuando el diseño es correcto y APPSisGOE lo necesita exactamente igual. Se "rediseña" cuando el patrón es correcto pero la implementación tiene defectos conocidos o puede mejorar con el stack de APPSisGOE.

---

### 1.1 Adoptar sin cambios conceptuales

Estos patrones están correctamente diseñados en BhagamApps. APPSisGOE debe implementarlos fielmente, adaptando solo el stack tecnológico.

| Componente | Fuente | Justificación |
|-----------|--------|---------------|
| **Arquitectura de 4 capas de autorización** | RBAC-001 §3 | Auth → Módulo → Permiso granular → Componente. Defensa en profundidad correcta. Ni Spatie ni Fortify solos la proveen — es un patrón arquitectónico consciente. |
| **Concepto `es_principal`** | RBAC-001 §2.9 | Un único super-admin de recuperación que no puede ser modificado ni degradado accidentalmente. Requerimiento de seguridad en IEE, no una preferencia. |
| **Trait `ProteccionAdminPrincipal`** | RBAC-001 §2.7 | Protege al admin principal en 7 puntos de entrada distintos. El patrón (interceptar en la acción, registrar intento, abortar) es correcto y exhaustivo. |
| **`auditoria_passwords`** | RBAC-001 §1.1 | Registro inmutable de operaciones administrativas sobre cuentas. Solo INSERT, sin UPDATE. Requerimiento de auditoría interna en instituciones estatales. |
| **Workflow HMB (propuesta → aprobación → aplicación)** | INV-002 §4 | El flujo completo con `tipo_objeto`, `campo`, `valor_anterior`, `valor_nuevo`, `estado` y transacción DB en aprobación es arquitectónicamente correcto. Es un requerimiento de control interno en IEE, no una decisión de diseño opcional. |
| **Modelo de custodios con `fecha_retiro`** | INV-002 §3.4 | Cadena de custodia temporal. El responsable actual = `fecha_retiro IS NULL`. Patrón de registro histórico sin destruir datos previos. Correcto e irreemplazable. |
| **OR lógico en visibilidad de módulos** | APPS-002 §3.2 | `app_role OR app_user` — acceso por rol o por excepción individual. La semántica OR (no override) es intencional y correcta: maximiza el acceso sin necesitar lógica de precedencia. |
| **Caché versioned con `cache_version`** | APPS-002 §3.3 | `cache()->increment('apps.cache_version')` invalida caches de todos los usuarios sin conocer sus IDs. Patrón brillante para invalidación global eficiente. |
| **Nuevos módulos deshabilitados por defecto** | APPS-002 §4 | `apps:sync` siempre crea con `habilitada = false`. Principio de mínimo privilegio aplicado al descubrimiento de módulos. |
| **SoftDeletes en bienes** | INV-002 §2 | La baja de bienes institucionales nunca debe ser permanente. Históricamente y legalmente, los bienes dados de baja deben poder consultarse. |
| **ActivityLogger como servicio separado** | INV-002 §10.4 | Ya existe como módulo independiente (`Modules\ActivityLog\Services\ActivityLogger`). El patrón de separar el log de actividad del dominio es correcto. |

---

### 1.2 Adoptar con mejoras menores

Estos componentes tienen diseño correcto pero defectos puntuales identificados en las auditorías. APPSisGOE debe implementarlos corrigiendo esos defectos.

| Componente | Defecto en BhagamApps | Mejora en APPSisGOE |
|-----------|----------------------|---------------------|
| **`middleware CheckForzarCambioPassword`** | Registrado en dos lugares (Kernel + bootstrap) — posible doble ejecución. | Registrar solo en `bootstrap/app.php`. Laravel 11 usa exclusivamente ese archivo. |
| **`middleware CheckAppAccess`** | La lógica `App::visiblesPara()` vive en el modelo (fat model). | Extraer a `ModuleVisibilityService::visiblesPara($user)` — servicio inyectable en el CORE. |
| **Catálogo de grupos institucionales en Dashboard** | IDs de categorías hardcodeados en `cargarGruposInstitucionales()`. | Usar `slugs` o un campo `grupo` en la tabla `categorias`. Los IDs de BD no son identificadores estables. |
| **`NotificacionesDropdown` y `NotificacionesIcono`** | Implementados dentro del módulo Inventario aunque usan la tabla `notifications` de Laravel (global). | Moverlos al CORE como componentes transversales. Cualquier módulo debe poder emitir notificaciones que aparezcan en el mismo dropdown. |
| **HEB (Historial de Eliminaciones de Bienes)** | `HebController` solo lista — no tiene acciones de aprobar/rechazar para solicitudes creadas por roles no-admin. Brecha funcional documentada. | Implementar `HebIndex` Livewire equivalente a `HmbIndex` con `aprobarEliminacion()` y `rechazarEliminacion()`. |
| **`bienes_responsables` asignación** | No está claro si cerrar el registro anterior (fecha_retiro) es automático o manual. | Garantizar que `asignarResponsable()` cierre automáticamente el registro previo en una transacción única. |
| **`mantenimientos_programados.tipo`** | Campo varchar libre — no normalizado. | Normalizar a enum o FK al catálogo `mantenimientos`. |

---

### 1.3 Rediseñar completamente

Estos componentes tienen un propósito correcto pero la implementación tiene problemas fundamentales que APPSisGOE no debe heredar.

| Componente | Problema fundamental | Rediseño en APPSisGOE |
|-----------|---------------------|-----------------------|
| **`User::hasPermission($slug)`** | 2 queries SQL por llamada, sin memoización. En un componente Livewire con 10 `@can`, son 20 queries solo para autorización. | Usar `$user->can($ability)` de Spatie Permission, que memoiza automáticamente los resultados. |
| **`permission_role` y `permission_user`** | Implementación manual. `permission_user` sin UNIQUE constraint. `permission_role` tuvo 76 duplicados (corregidos en IMPL-003). | Usar `Spatie\Permission` — gestiona correctamente ambas tablas con constraints apropiados. |
| **Permisos definidos en múltiples fuentes** | Migraciones inline, seeders CSV, seeders PHP, fixtures. No hay única fuente de verdad. Auditar qué permisos existen es complejo. | `Capacidad` enum como única fuente de verdad. Todos los seeders se generan a partir del enum. |
| **`Role::hasPermission($nombre)`** | Busca por `nombre` (texto legible) mientras `User::hasPermission()` busca por `slug`. Son inconsistentes. | Eliminar este método. Usar solo `$user->can($slug)` de Spatie en todo el codebase. |
| **Tabla `apps`** | Módulo separado cuando en realidad es infraestructura del CORE. Genera dependencia circular (roles.app_id). | Fusionar en la tabla `modules` de APPSisGOE con campos visuales adicionales. No existe como módulo separado. |
| **`roles.app_id`** | FK de `roles` hacia `apps` — dependencia circular que convierte a Apps en un requisito de arranque del sistema. | Eliminar el campo. Los roles son globales en APPSisGOE, no pertenecen a ningún módulo. |
| **Gates en `AuthServiceProvider`** | 60 gates definidos manualmente, todos delegando a `hasPermission()`. Boilerplate masivo. | Usar `Gate::before()` con un único callback que delegue a Spatie. O eliminar los gates por completo y usar `$this->authorize()` directamente. |
| **`AppSeeder` con seeders CSV** | Datos de configuración visual en seeders CSV. Difícil de mantener y versionar. | Manifiestos `module.json` en cada módulo declaran sus propios metadatos visuales. El installer los registra en `modules`. |

---

### 1.4 No migrar

Estos elementos no deben existir en APPSisGOE. Son deuda técnica identificada en BhagamApps.

| Componente | Razón |
|-----------|-------|
| **`apps.user_id`** | Campo legacy sin FK, sin uso funcional. Residuo del sistema anterior donde cada app tenía un "dueño". |
| **`bienes.origen` (varchar)** | Campo legacy que coexiste con `origen_id` FK. Los datos ya fueron migrados al catálogo. Solo existe por backward compatibility. |
| **`bienes.mantenimiento_id`** | FK directa al catálogo de tipos de mantenimiento en el bien. Redundante con `mantenimientos_programados`. No tiene semántica clara. |
| **`app_user.role_id`** | Campo ya eliminado en BhagamApps (`drop_app_user_role_id`). No existe en producción. |
| **`roles.app_id`** | Campo legacy corregido a nullable. No tiene semántica activa. |
| **Seeders CSV multi-archivo** | Patrón de mantenimiento difícil. APPSisGOE usa manifiestos JSON y enums PHP como fuentes de verdad. |
| **`user_id` en `apps` sin FK** | Dato huérfano sin constraint de integridad. |
| **`ActaPrinter` como clase estática** | Helper con lógica de presentación acoplado al dominio. Reemplazar por Blade component parametrizado o Service inyectable. |

---

## 2. El CORE definitivo de APPSisGOE

### Criterio de inclusión en el CORE

Un componente pertenece al CORE si: (a) todos los módulos lo necesitan, o (b) su ausencia hace que el sistema no pueda arrancar, o (c) es un requerimiento transversal de seguridad, auditoría o gobernanza.

La evidencia proviene de las tres auditorías en conjunto.

---

### 2.1 Componentes del CORE — con justificación

#### CORE-1: Users (Identidad y Autenticación)

**Evidencia:**
- RBAC-001 §2.1: Todos los flujos de autorización parten del modelo `User`. El campo `es_principal` es un requerimiento de seguridad sistémico. `bloqueado` y `forzar_cambio_password` son controles de cuenta que aplican en el arranque de cualquier sesión, independientemente del módulo.
- APPS-002 §3: `CheckAppAccess` necesita `$user` para ejecutar.
- INV-002 §8: Las 17 rutas de Inventario usan `middleware(['web', 'auth', ...])` — auth es capa 0.

**Capacidades del CORE:**
- Autenticación (Fortify)
- Registro y perfil de usuario
- `bloqueado` → impide login
- `forzar_cambio_password` → redirige al cambio antes de acceder a cualquier módulo
- `es_principal` → marca al super-admin de recuperación
- `ProteccionAdminPrincipal` trait
- Verificación de email (Fortify)

---

#### CORE-2: Roles y Permisos (Autorización)

**Evidencia:**
- RBAC-001 §4: 85 permisos en 17 categorías. Sin el sistema de permisos, ningún módulo puede controlar quién hace qué.
- RBAC-001 §5: 7 roles que representan la estructura real de una IEE colombiana (Administrador, Rectoría, Coordinación, Auxiliar, Docente, Estudiante, Invitado). Esta taxonomía es institucional, no técnica.
- RBAC-001 §2.8: Los gates `restaurar-backups` e `importar-snapshot-backup` requieren `es_principal` además del permiso — el CORE de autorización tiene conocimiento del concepto de admin principal.
- APPS-002 §7.7: La gobernanza de módulos (ver, instalar, activar, asignar) requiere capacidades específicas definidas en el sistema de permisos.

**Capacidades del CORE:**
- Spatie Permission como motor
- `Capacidad` enum como única fuente de verdad (ya existe, pendiente de conectar)
- 7 roles IEE predefinidos
- Gates especiales con doble condición (`can + es_principal`)
- Middleware `CheckPermission` (renombrar a `RequiereCapacidad` en APPSisGOE)
- UI de gestión de roles y permisos

---

#### CORE-3: Módulos (Gobernanza y Visibilidad)

**Evidencia:**
- APPS-002 §6.2-6.3: "Apps no es un módulo de negocio" y "debe absorberse en el CORE". Sin la tabla `apps` (o `modules`), el middleware `CheckAppAccess` no puede funcionar y ningún módulo es accesible.
- APPS-002 §5.4: `roles.app_id` FK genera dependencia circular — Apps es una "dependencia de arranque del sistema".
- APPS-002 §8.2: APPSisGOE ya tiene 6 estados de ciclo de vida, manifiestos y Actions. Le faltan `module_role`, `module_user`, `visiblesPara()` y `ModuloAccessMiddleware`. Exactamente lo que BhagamApps tiene.
- INV-002 §8: Las rutas de Inventario usan `middleware(['app.access:inventario'])` — sin el sistema de módulos, Inventario no es accesible.

**Capacidades del CORE:**
- Registro de módulos con metadatos visuales (`icono`, `color`, `orden`, `ruta_entrada`)
- Ciclo de vida de módulos (6 estados existentes en APPSisGOE)
- `module_role` y `module_user` (M:M con activo en module_user)
- `Module::visiblesPara(User $user)` con caché versioned
- `ModuloAccessMiddleware` (alias `modulo.access:{key}`)
- Dashboard institucional (muestra módulos visibles al usuario autenticado)
- UI de gestión de visibilidad por rol y por usuario

---

#### CORE-4: Auditoría (Trazabilidad)

**Evidencia:**
- RBAC-001 §1.1: `auditoria_passwords` — registro inmutable de operaciones sobre cuentas. Requerimiento de auditoría interna institucional.
- INV-002 §10.4: "ActivityLogger ya es un servicio del módulo ActivityLog. APPSisGOE debe preservar este patrón pero moverlo al CORE como servicio transversal."
- RBAC-001 §2.8: El gate `ver-activity-log` requiere `es_principal` — el log de actividad es una herramienta del super-admin, no de un módulo específico.
- INV-002 §7.4: `HmbIndex::aprobarModificacion()` llama a `ActivityLogger::log(...)` — la auditoría cruza todos los módulos.

**Capacidades del CORE:**
- `ActivityLogger` como servicio inyectable transversal
- `activity_logs` tabla con índices por módulo, acción, fecha
- `auditoria_passwords` tabla (operaciones sobre cuentas)
- UI de Activity Log (protegida por `es_principal`)

---

#### CORE-5: Notificaciones

**Evidencia:**
- INV-002 §10.4: "El sistema de notificaciones Laravel (tabla `notifications` + `Notification::send()`) es compartido. No duplicar — usar el mismo mecanismo en todos los módulos APPSisGOE."
- INV-002 §7.4-7.5: `NotificacionHmb` y `NotificacionHeb` usan la tabla `notifications` — las notificaciones del HMB y HEB aparecen en el mismo dropdown global.
- INV-002 §7.5: `NotificacionesDropdown` y `NotificacionesIcono` son componentes Livewire que deben estar en el CORE, no en Inventario.

**Capacidades del CORE:**
- Tabla `notifications` (estándar Laravel)
- `NotificacionesDropdown` y `NotificacionesIcono` como componentes CORE
- Cada módulo emite notificaciones; el CORE las muestra

---

#### CORE-6: Seguridad Administrativa

**Evidencia:**
- RBAC-001 §2.7: `ProteccionAdminPrincipal` se usa en 7 componentes distintos del módulo User. Es un patrón transversal.
- RBAC-001 §2.6: `CheckForzarCambioPassword` aplica en el arranque de cualquier sesión, antes de que ningún módulo sea accesible.

**Capacidades del CORE:**
- `ProteccionAdminPrincipal` trait disponible para cualquier componente
- `CheckForzarCambioPassword` middleware
- Gestión de contraseñas administrativas con `auditoria_passwords`

---

### 2.2 Diagrama del CORE APPSisGOE

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           CORE APPSisGOE                                │
│                                                                         │
│  ┌──────────────────┐     ┌──────────────────┐     ┌────────────────┐  │
│  │  CORE-1: Users   │────►│  CORE-2: Roles   │────►│  CORE-3: Mód. │  │
│  │  ───────────────  │     │  y Permisos       │     │  ─────────────│  │
│  │  - Fortify auth  │     │  ───────────────  │     │  - modules    │  │
│  │  - bloqueado     │     │  - Spatie Perm.  │     │  - module_role│  │
│  │  - forzar_cambio │     │  - Capacidad enum│     │  - module_user│  │
│  │  - es_principal  │     │  - 7 roles IEE   │     │  - visiblesPa-│  │
│  │  - ProteccionAP  │     │  - Gates críticos│     │    ra()       │  │
│  └──────────────────┘     └──────────────────┘     │  - Middleware  │  │
│           │                        │                │  - Lifecycle  │  │
│           └────────────────────────┘                └────────────────┘  │
│                         │                                    │          │
│           ┌─────────────▼────────────┐   ┌─────────────────▼────────┐  │
│           │  CORE-4: Auditoría       │   │  CORE-5: Notificaciones  │  │
│           │  ────────────────────     │   │  ─────────────────────── │  │
│           │  - ActivityLogger        │   │  - notifications table   │  │
│           │  - auditoria_passwords   │   │  - NotificacionesDropdown│  │
│           │  - activity_logs         │   │  - Módulos emiten,       │  │
│           │  - UI (solo es_principal)│   │    CORE muestra          │  │
│           └──────────────────────────┘   └──────────────────────────┘  │
│                                                                         │
│  ┌──────────────────────────────────────────────────────────────────┐  │
│  │  CORE-6: Seguridad Administrativa                                │  │
│  │  - CheckForzarCambioPassword  - ProteccionAdminPrincipal trait  │  │
│  └──────────────────────────────────────────────────────────────────┘  │
│                                                                         │
│  Capas de autorización activas para TODA request:                       │
│  Auth (CORE-1) → ModuloAccess (CORE-3) → Permiso (CORE-2) → Action    │
└─────────────────────────────────────────────────────────────────────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
         MÓDULO            MÓDULO          MÓDULO
       Inventario         Biblioteca        CTE
```

---

### 2.3 Lo que NO pertenece al CORE

| Candidato | Razón de exclusión |
|-----------|-------------------|
| Backup / Recovery | Módulo operacional (AdminSistema). Requiere `es_principal` pero eso es un gate de seguridad, no una razón para incluirlo en el CORE. |
| Dashboard ejecutivo | Derivado de datos de módulos. El CORE provee la infraestructura (módulos visibles); cada módulo provee sus propias métricas. |
| Bienes / Inventario | Dominio de negocio específico de la institución. No es infraestructura. |
| Catálogos maestros | Pertenecen al dominio que los usa (dependencias → Inventario). |

---

## 3. Servicios compartidos derivados de Inventario

### 3.1 Análisis por componente

#### ActivityLogger — **MOVER AL CORE**

Ya existe como módulo separado. Las tres auditorías lo referencian como consumidor (`HmbIndex`, `AppsIndex`, módulo User). No es un servicio de Inventario — es transversal. APPSisGOE debe moverlo al CORE (ver CORE-4).

---

#### Sistema de Notificaciones — **MOVER AL CORE**

`NotificacionHmb` y `NotificacionHeb` usan la tabla `notifications` de Laravel, que es globalmente compartida. Los componentes `NotificacionesDropdown` y `NotificacionesIcono` deben estar en el CORE. Lo que permanece en Inventario son solo las clases Notification específicas del dominio (`NotificacionHmb`, `NotificacionHeb`).

---

#### HMB (Historial de Modificaciones) — **Patrón extraíble, implementación en Inventario**

El patrón subyacente — "modificar cualquier campo de cualquier entidad requiere aprobación antes de aplicarse" — es genérico. Podría implementarse como:

```
ApprovalWorkflow::propose(
    entity: $bien,
    field:  'estado_id',
    value:  $nuevoValor,
    approvers: [Roles::Admin, Roles::Rector]
)
```

Sin embargo, la evidencia de BhagamApps muestra que la implementación concreta (`tipo_objeto`, `bien_id`, la lógica de `dependencia_id` auto-creating `historial_dependencias`) está profundamente acoplada al dominio de Inventario. **Recomendación:** Mantener en Inventario en APPSisGOE v1. Extraer a servicio compartido solo cuando un segundo módulo necesite el mismo patrón (principio: no abstraer por anticipación).

---

#### HEB (Historial de Eliminaciones) — **Patrón extraíble, implementación en Inventario**

Similar a HMB. El patrón "baja controlada con aprobación antes de soft-delete" es genérico. Pero la única evidencia de necesidad es en Inventario. **Recomendación:** Implementar completo en Inventario (incluyendo la brecha de la UI de aprobación). Considerar extracción solo si un segundo módulo requiere bajas controladas.

---

#### Historiales de Auditoría de Dominio — **Patrón en Inventario, guía para otros módulos**

`historial_dependencias_bienes`, `historial_ubicaciones_bienes`, `historial_modificaciones_bienes`, `historial_eliminaciones_bienes` son todos registros inmutables de cambios de estado del dominio Inventario. El patrón (tabla de historial con `_anterior`/`_nuevo`, `user_id`, `aprobado_por`, timestamp) puede usarse como plantilla para otros módulos. No existe razón para compartir la implementación — pero sí el patrón de diseño.

---

#### Dashboard — Métricas de calidad de datos — **Componente Blade reutilizable**

`cargarCalidadDatos()` de `InventarioDashboard` calcula un índice de completitud: % de registros con cada campo no nulo. Este concepto (índice de completitud de datos por entidad) es reutilizable en cualquier módulo. APPSisGOE puede implementar un componente Blade/Livewire genérico:

```php
<livewire:data-quality-index 
    :entity="App\Modules\Inventario\Models\Bien::class"
    :fields="['categoria_id', 'estado_id', 'origen_id', 'dependencia_id']"
/>
```

Esto no requiere un servicio compartido — un Blade component parametrizable es suficiente.

---

### 3.2 Tabla resumen

| Componente | Decisión | Scope |
|-----------|---------|-------|
| ActivityLogger | Mover al CORE | CORE-4 |
| Notificaciones (tabla + dropdown) | Mover al CORE | CORE-5 |
| Clases Notification específicas (HmbNotif, HebNotif) | Permanecen en Inventario | Módulo |
| Patrón HMB | Implementar en Inventario, extraer si hay segundo caso | Módulo → futuro Service |
| Patrón HEB | Implementar en Inventario (completo con UI), extraer si hay segundo caso | Módulo → futuro Service |
| Historiales de dominio | Patrón compartido documentado, implementación en cada módulo | Patrón |
| Índice de calidad de datos | Blade component genérico parametrizable | CORE (UI toolkit) |
| Mantenimientos programados | Dominio específico de Inventario | Módulo |
| Actas de entrega | Dominio específico | Módulo |
| BienesIndex (CRUD facetado) | Patrón de referencia para otros listados complejos | Módulo (guía) |

---

## 4. ¿Debe APPSisGOE conservar la arquitectura RBAC actual?

**Respuesta: Parcialmente — conservar los conceptos, reemplazar la implementación.**

### 4.1 Qué conservar (conceptual)

| Concepto | Por qué conservarlo |
|---------|---------------------|
| 4 capas de autorización | Es el único patrón que garantiza que no se puede acceder a una operación protegida sin pasar por todas las verificaciones. Ninguna capa sola es suficiente. |
| 7 roles IEE | Reflejan la estructura real de una institución educativa colombiana. No son técnicos — son organizacionales. Cambiarlos requeriría análisis institucional, no arquitectónico. |
| `es_principal` como doble condición | Las 3 operaciones críticas (restaurar backup, importar snapshot, ver activity log) requieren `es_principal` además del permiso. Esta doble condición protege contra escalada de privilegios accidental. |
| Permisos con `slug` kebab-case | Identificadores estables, legibles y seguros para serialización. El slug `'editar-bienes'` no cambia aunque el nombre legible cambie. |
| `permission_user` (permisos directos) | Permite casos de excepción individual sin cambiar el rol del usuario. Necesario en una institución donde un docente puede necesitar un permiso específico sin convertirse en coordinador. |

### 4.2 Qué reemplazar (implementación)

| Implementación actual | Reemplazo en APPSisGOE | Por qué |
|----------------------|----------------------|---------|
| `hasPermission()` manual (2 queries) | `$user->can($ability)` de Spatie (memoizado) | Elimina el problema de rendimiento P-004 |
| `permission_role` y `permission_user` manuales | Tablas de Spatie (`model_has_permissions`, `model_has_roles`, `role_has_permissions`) | Spatie las gestiona correctamente con constraints |
| 60 gates en `AuthServiceProvider` | `Gate::before()` + Spatie, o eliminar gates y usar `$this->authorize()` | Elimina el boilerplate masivo |
| Permisos en seeders CSV y migraciones inline | `Capacidad` enum como única fuente de verdad | Única fuente de verdad, auditable, versionable |
| `Role::hasPermission($nombre)` | Eliminar — usar solo `$user->can($slug)` | Elimina inconsistencia nombre vs slug |
| Registro manual de roles en seeder | `RoleSeeder` generado desde `Capacidad` enum | Consistencia con la fuente de verdad |

### 4.3 Arquitectura objetivo de autorización en APPSisGOE

```
HTTP Request
    │
    ▼
┌─────────────────────────────────────────────────────────┐
│  CAPA 1 — Fortify (CORE-1: Users)                       │
│  ├─ $user → NULL: redirect('/login')                    │
│  ├─ bloqueado: logout + mensaje                         │
│  ├─ email_verified_at NULL: redirect('/verify')         │
│  └─ forzar_cambio_password: redirect('/cambiar')        │
└─────────────────────────────────────────────────────────┘
    │
    ▼
┌─────────────────────────────────────────────────────────┐
│  CAPA 2 — ModuloAccessMiddleware (CORE-3: Módulos)      │
│  Middleware: modulo.access:{key}                        │
│  ├─ ModuleVisibilityService::visiblesPara($user)        │
│  │   └─ OR(module_role, module_user.activo=true)        │
│  │   └─ caché versioned 300s                            │
│  └─ .contains('key', {key}) o abort(403)               │
└─────────────────────────────────────────────────────────┘
    │
    ▼
┌─────────────────────────────────────────────────────────┐
│  CAPA 3 — Spatie Permission (CORE-2: Roles/Permisos)    │
│  Middleware: can:{capacidad}                            │
│  ├─ $user->can($capacidad)                              │
│  │   └─ Spatie: revisa roles + permisos directos        │
│  │   └─ Memoizado automáticamente en la instancia       │
│  └─ false → abort(403)                                  │
└─────────────────────────────────────────────────────────┘
    │
    ▼
┌─────────────────────────────────────────────────────────┐
│  CAPA 4 — Action / Controller / Livewire                │
│  ├─ $this->authorize(Capacidad::EditarBienes)           │
│  ├─ abort_if(!$user->can(...), 403)                     │
│  └─ Para operaciones críticas:                          │
│     Gate::allows('restaurar-backups')                   │
│     = can($cap) && $user->es_principal                  │
└─────────────────────────────────────────────────────────┘
```

---

## 5. Arquitectura objetivo del módulo Apps

### 5.1 Veredicto

**Apps no debe existir como módulo en APPSisGOE. Su lógica pertenece al CORE-3.**

**Evidencia de las auditorías:**
- APPS-002 §6.1: "Apps no gestiona ningún concepto del dominio educativo. No tiene entidades de negocio propias."
- APPS-002 §5.4: "La dependencia circular (roles.app_id → apps) es el indicador más claro de que Apps no es un módulo ordinario: es infraestructura compartida."
- APPS-002 §9: "El módulo Apps NO debe migrarse como módulo separado a APPSisGOE. Su lógica debe absorberse en el CORE."

### 5.2 ¿Módulo de negocio, servicio compartido o componente CORE?

**CORE — componente de gobernanza de módulos.**

Un "módulo de negocio" encapsula un dominio institucional (bienes, personas, aulas). Un "servicio compartido" provee capacidad técnica a otros módulos (logging, notificaciones). Un "componente CORE" es infraestructura sin la cual el sistema no puede operar.

Apps (en BhagamApps) es el tercer tipo: sin él, ningún módulo es visible ni accesible. En APPSisGOE, esto se llama CORE-3: Módulos.

### 5.3 Diseño objetivo

**Modelo de datos (extiende la tabla `modules` existente):**

```sql
-- Campos visuales a agregar a la tabla modules existente
ALTER TABLE modules ADD COLUMN icono      VARCHAR(255)    NULL;
ALTER TABLE modules ADD COLUMN color      VARCHAR(20)     NULL;
ALTER TABLE modules ADD COLUMN orden      INT UNSIGNED    NOT NULL DEFAULT 99;
ALTER TABLE modules ADD COLUMN ruta_entrada VARCHAR(255)  NULL;
-- status ya existe con 6 estados

-- Nuevas tablas
CREATE TABLE module_role (
    id        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,  -- FK modules.id CASCADE
    role_id   BIGINT UNSIGNED NOT NULL,  -- FK roles.id  CASCADE
    UNIQUE (module_id, role_id)
);

CREATE TABLE module_user (
    id        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,  -- FK modules.id CASCADE
    user_id   BIGINT UNSIGNED NOT NULL,  -- FK users.id  CASCADE
    activo    TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE (module_id, user_id)
);
```

**Servicio de visibilidad:**

```php
class ModuleVisibilityService
{
    public function visiblesPara(User $user): Collection
    {
        $version = (int) cache()->get('modules.cache_version', 0);
        $key = "modules.visibles.{$user->id}.v{$version}";

        return cache()->remember($key, 300, fn() =>
            Module::where('status', ModuleStatus::Activo)
                ->where(fn($q) => $q
                    ->whereHas('roles', fn($r) => $r->where('roles.id', $user->role_id))
                    ->orWhereHas('users', fn($u) => $u
                        ->where('users.id', $user->id)
                        ->where('module_user.activo', true))
                )
                ->orderBy('orden')
                ->orderBy('name')
                ->get()
        );
    }

    public function invalidarCache(): void
    {
        cache()->increment('modules.cache_version');
    }
}
```

**Middleware:**

```php
class ModuloAccessMiddleware
{
    public function __construct(private ModuleVisibilityService $visibility) {}

    public function handle(Request $request, Closure $next, string $key): Response
    {
        if (!$this->visibility->visiblesPara($request->user())->contains('key', $key)) {
            abort(403, "Sin acceso al módulo '{$key}'.");
        }
        return $next($request);
    }
}
```

**Cada módulo declara en sus rutas:**
```php
Route::middleware(['web', 'auth', 'modulo.access:inventario'])->group(...)
```

**Metadatos visuales en manifiestos:**
```json
// Modules/Inventario/module.json
{
  "key": "inventario",
  "name": "Inventario",
  "icono": "fas fa-boxes",
  "color": "#28a745",
  "orden": 2,
  "ruta_entrada": "/inventario",
  "requires": ["comunidad"],
  "version": "1.0.0",
  "min_core": "1.0.0"
}
```

El `ModuleInstallAction` lee el manifiesto y registra los metadatos visuales en `modules` al instalar. El seeder inicial carga el manifiesto de cada módulo incluido por defecto.

---

## 6. Errores arquitectónicos críticos — cómo evitarlos

Priorizados por impacto en correctitud, mantenibilidad y seguridad.

---

### ERROR-001 — Dependencia circular entre módulos (Crítico)

**BhagamApps:** `roles.app_id` FK → `apps.id`. El módulo User depende de Apps, pero Apps depende de User para sus relaciones. La tabla `apps` debe existir antes de poder crear roles.

**Impacto:** Apps se convierte en un requisito de arranque oculto. Si se elimina el módulo Apps, los roles pierden la FK y el sistema puede quedar en estado inconsistente.

**Cómo evitarlo en APPSisGOE:**
- Los roles son globales y pertenecen exclusivamente al CORE. No tienen FK hacia ningún módulo.
- Las tablas `module_role` y `module_user` son FK desde los módulos hacia los roles, no al revés.
- El CORE no depende de ningún módulo de negocio.

---

### ERROR-002 — Sin constraint de unicidad en asignaciones directas (Crítico)

**BhagamApps:** `permission_user` no tiene `UNIQUE(permission_id, user_id)`. Se pueden insertar el mismo permiso dos veces para el mismo usuario. `permission_role` tuvo 76 duplicados antes de IMPL-003.

**Impacto:** Datos inconsistentes. `hasPermission()` puede retornar resultados incorrectos si hay duplicados que generan ambigüedad en EXISTS queries.

**Cómo evitarlo en APPSisGOE:**
- Spatie Permission gestiona correctamente sus tablas con constraints apropiados.
- `module_role` y `module_user` deben tener UNIQUE declarado explícitamente en la migración (no confiar en que el ORM lo maneje).

---

### ERROR-003 — Dos implementaciones inconsistentes del mismo comportamiento (Crítico)

**BhagamApps:** `User::hasPermission($slug)` busca por `slug`. `Role::hasPermission($nombre)` busca por `nombre`. Son dos contratos diferentes para la misma operación.

**Impacto:** Cualquier código que llame a `Role::hasPermission()` directamente obtendrá resultados incorrectos. El error es silencioso — no hay excepción, simplemente retorna `false` cuando debería retornar `true`.

**Cómo evitarlo en APPSisGOE:**
- Un único mecanismo de autorización: `$user->can(Capacidad::EditarBienes->value)`.
- El enum `Capacidad` garantiza que no hay strings libres en el código — siempre se usa la constante tipada.
- Prohibir el método `Role::hasPermission()` — si se necesita verificar permisos de un rol, es a través del usuario.

---

### ERROR-004 — Lógica de negocio en modelos (Fat Models)

**BhagamApps:** `App::visiblesPara($user)` con caché versioned vive directamente en el modelo `App`. `BienesIndex` tiene 668 líneas con CRUD completo, filtros facetados, soft delete, notificaciones y gestión de modal de eliminación.

**Impacto:** Los modelos no son testeables de forma aislada. El modelo `App` no puede usarse sin la tabla de caché. `BienesIndex` es difícil de mantener por su tamaño.

**Cómo evitarlo en APPSisGOE:**
- `ModuleVisibilityService` (no método estático en el modelo).
- Livewire components con máximo 200-300 líneas — delegar a Actions y Services.
- APPSisGOE ya usa el patrón de Actions con DTOs — mantenerlo consistentemente.

---

### ERROR-005 — Brecha funcional en flujo de aprobación HEB (Grave)

**BhagamApps:** El `HebController` lista solicitudes de eliminación pero no tiene acciones de aprobar/rechazar. Un bien cuya eliminación fue solicitada por un rol básico queda en estado `pendiente` indefinidamente porque no existe UI para que el Admin lo resuelva.

**Impacto:** El flujo de baja controlada queda incompleto. Los bienes permanecen en estado zombie (solicitud pendiente sin resolución posible).

**Cómo evitarlo en APPSisGOE:**
- Implementar `HebIndex` Livewire completo antes de lanzar el módulo Inventario.
- Verificar que tanto HMB como HEB tengan los flujos completos (propuesta → aprobación → aplicación).
- Los flujos de aprobación son binarios: siempre deben tener la rama de aprobación Y la de rechazo implementadas.

---

### ERROR-006 — Permisos sin fuente de verdad única (Grave)

**BhagamApps:** Los 85 permisos se definen en: migraciones inline `up()`, seeders PHP de módulos, seeders CSV. No hay un inventario centralizado excepto la base de datos en sí.

**Impacto:** Auditar qué permisos existen requiere inspeccionar múltiples archivos. Agregar un permiso nuevo no tiene un lugar canónico. Los tests no pueden verificar la integridad del catálogo de permisos.

**Cómo evitarlo en APPSisGOE:**
- El enum `Capacidad` (ya existe en `app/Auth/Capacidad.php`) es la fuente de verdad.
- El `PermissionSeeder` itera `Capacidad::cases()` y crea los permisos en Spatie.
- Agregar un permiso = agregar un caso al enum. Nada más.
- Tests pueden verificar que todos los casos del enum existen en la BD.

---

### ERROR-007 — IDs de BD como identificadores de dominio (Moderado)

**BhagamApps:** `cargarGruposInstitucionales()` en el dashboard usa `categoria_id IN (1, 20)` para identificar "Mobiliario". Si el seeder se ejecuta en otro orden o las categorías se re-crean, los IDs pueden cambiar.

**Impacto:** El dashboard de grupos institucionales se rompe silenciosamente — muestra 0 bienes donde debería mostrar 50.

**Cómo evitarlo en APPSisGOE:**
- Usar `slug` o un campo `grupo` en `categorias` para identificar agrupaciones.
- Los IDs de base de datos son detalles de implementación, no identificadores de dominio.

---

## 7. Aciertos arquitectónicos — cómo potenciarlos

Priorizados por valor entregado al sistema.

---

### ACIERTO-001 — Defensa en profundidad con 4 capas (Valor máximo)

**BhagamApps:** Ninguna capa sola puede ser vulnerada sin que las otras fallen también. Un usuario puede bypassear la capa 3 (permiso en ruta) pero la capa 4 (abort_if en componente) lo detiene igualmente.

**Cómo potenciarlo en APPSisGOE:**
- Hacer explícito el contrato: cada módulo DEBE implementar las 4 capas. Documentarlo en la guía de desarrollo de módulos.
- La capa 2 (ModuloAccessMiddleware) se registra automáticamente cuando un módulo se activa — no es opcional.
- Los Actions de APPSisGOE son la capa 4 — cada Action debe verificar el permiso al inicio con `$this->authorize()`.

---

### ACIERTO-002 — Caché versioned para invalidación global sin conocer IDs (Valor alto)

**BhagamApps:** `cache()->increment('apps.cache_version')` invalida los caches de visibilidad de todos los usuarios en O(1), sin necesitar conocer ni iterar sus IDs.

**Cómo potenciarlo en APPSisGOE:**
- Extender el patrón a otras entidades cacheadas (roles asignados a usuarios, lista de capacidades de un rol).
- Documentar el patrón como estándar en APPSisGOE: cualquier cache que dependa de configuración global usa versión incremental, no TTL solo.
- El `ModuleVisibilityService` propaga la invalidación al activar/desactivar módulos o cambiar asignaciones de roles.

---

### ACIERTO-003 — Workflow HMB con transacción atómica (Valor alto)

**BhagamApps:** `aprobarModificacion()` envuelve toda la operación en `DB::beginTransaction()`. Si falla la actualización del bien, el historial no queda en estado `'aprobada'` con el bien sin cambiar.

**Cómo potenciarlo en APPSisGOE:**
- El mismo patrón para HEB: la aprobación de la baja y el soft delete son una transacción.
- Formalizar como regla: toda acción que modifica más de una tabla debe usar transacción.
- Los Actions de APPSisGOE son el lugar natural para wrappear en transacción.

---

### ACIERTO-004 — Concepto `es_principal` como guardia de recuperación (Valor alto)

**BhagamApps:** Siempre existe al menos un administrador que no puede ser modificado ni degradado, incluso por otros administradores. Este es el "break-glass" del sistema.

**Cómo potenciarlo en APPSisGOE:**
- Agregar constraint en la capa de datos: trigger o migration check que garantice que siempre exista exactamente 1 `es_principal = true`.
- Considerar auditar *todos* los cambios al usuario con `es_principal = true`, no solo los intentos bloqueados. Actualmente `auditoria_passwords` registra los intentos fallidos — los cambios permitidos al mismo usuario también deberían quedar registrados.
- UI muestra al admin principal con un badge especial y sin botones de edición — mantener este comportamiento.

---

### ACIERTO-005 — OR lógico en visibilidad de módulos (Valor medio-alto)

**BhagamApps:** `app_role OR app_user.activo` — un usuario ve una app si su rol la tiene asignada, O si tiene una asignación individual activa. No hay lógica de precedencia compleja.

**Cómo potenciarlo en APPSisGOE:**
- Mantener la semántica OR. No añadir "override negativo" (listas de exclusión) a menos que haya un caso de negocio probado — la complejidad del sistema de visibilidad crece exponencialmente con cada nueva dimensión de control.
- `module_user.activo = false` puede usarse para retirar el acceso individual sin eliminar el registro, conservando el historial de que ese usuario tuvo acceso.

---

### ACIERTO-006 — `auditoria_passwords` como registro inmutable (Valor medio-alto)

**BhagamApps:** Tabla de solo inserción que registra quién hizo qué sobre la cuenta de quién, con timestamp. No tiene `updated_at` — la inmutabilidad es estructural, no solo por convención.

**Cómo potenciarlo en APPSisGOE:**
- Extender el concepto a otras operaciones administrativas críticas: cambios de rol, cambios de `es_principal`, desactivación de módulos.
- Agregar un campo `ip_address` y `user_agent` para trazabilidad forense.
- Considerar que `activity_logs` y `auditoria_passwords` cubren diferentes propósitos: el log de actividad es operacional (quién editó qué bien), la auditoría de passwords es de seguridad (quién modificó qué cuenta con qué privilegio). Mantenerlos separados.

---

### ACIERTO-007 — Módulos deshabilitados por defecto al descubrirse (Valor medio)

**BhagamApps:** `apps:sync` crea módulos con `habilitada = false`. El administrador debe activar explícitamente cada módulo.

**Cómo potenciarlo en APPSisGOE:**
- El mismo principio aplicado al ciclo de vida: un módulo instalado no es un módulo activo. APPSisGOE ya tiene estos estados (Instalando → Inactivo → Activo).
- Agregar: al activar un módulo, no asignar automáticamente ningún rol. El administrador debe configurar explícitamente quién accede.
- Principio: **mínimo privilegio por defecto en todos los niveles** — cuenta, módulo, rol, permiso.

---

## 8. Roadmap recomendado para APPSisGOE

### Principio de secuenciación

Las fases se ordenan por dependencias estructurales, no por prioridad de negocio. Una fase no puede iniciarse hasta que sus dependencias estén completas y verificadas.

```
Fase 1 (CORE Foundation) → Todo lo demás
Fase 2 (Module Governance) → Fase 3 y Fase 4
Fase 3 (Inventario) → Módulos que usen Inventario
Fase 4 (Expansión) → Fase 2 + Fase 3
```

---

### FASE 1 — CORE Foundation (Prerequisito total)

**Objetivo:** El sistema puede autenticar usuarios, verificar permisos y proteger rutas. Sin módulos de negocio, pero con toda la infraestructura de autorización correcta.

**Entregables:**

| Componente | Descripción | Prioridad |
|-----------|-------------|-----------|
| Users con campos institucionales | `bloqueado`, `forzar_cambio_password`, `es_principal`, `userID` | Crítica |
| Spatie Permission configurado | Tablas, guards, cache, política de roles | Crítica |
| `Capacidad` enum conectado al seeder | Todos los permisos del sistema nacen del enum | Crítica |
| 7 roles IEE sembrados | Con permisos asignados desde Capacidad | Crítica |
| Middleware `CheckForzarCambioPassword` | Un solo punto de registro en bootstrap | Alta |
| `ProteccionAdminPrincipal` trait | Disponible en el CORE, listo para usar | Alta |
| `auditoria_passwords` tabla + registro | Con los 4 tipos de acción | Alta |
| Fortify + Jetstream base | Login, logout, 2FA, perfil | Alta |
| `activity_logs` tabla | Con ActivityLogger service | Media |
| Gates con doble condición `es_principal` | Para operaciones críticas de sistema | Media |

**Verificación de Fase 1:**
- Un usuario puede hacer login y que `bloqueado` y `forzar_cambio_password` funcionen
- `$user->can(Capacidad::VerBienes->value)` retorna el resultado correcto según rol
- El admin principal no puede ser modificado por otro administrador
- `auditoria_passwords` registra cada operación sobre cuentas

**Dependencias:** Ninguna (es la base).

---

### FASE 2 — Module Governance (Prerequisito para todos los módulos)

**Objetivo:** El sistema puede gestionar módulos con ciclo de vida, controlar qué roles/usuarios acceden a cada módulo, y mostrar el dashboard institucional personalizado.

**Entregables:**

| Componente | Descripción | Prioridad |
|-----------|-------------|-----------|
| Campos visuales en `modules` | `icono`, `color`, `orden`, `ruta_entrada` | Crítica |
| Tabla `module_role` | M:M con UNIQUE | Crítica |
| Tabla `module_user` | M:M con `activo` y UNIQUE | Crítica |
| `ModuleVisibilityService` | `visiblesPara()` con caché versioned | Crítica |
| `ModuloAccessMiddleware` | Alias `modulo.access:{key}` | Crítica |
| Dashboard institucional | Muestra módulos según visibilidad del usuario | Alta |
| Metadatos en manifiestos | `module.json` con campos visuales | Alta |
| UI de gestión de módulos | Toggle acceso por rol y por usuario | Alta |
| Propagación de caché en cambios | Al activar/desactivar módulo o cambiar rol | Alta |
| Validación de dependencias | No desactivar A si B requiere A y está Activo | Media |
| `NotificacionesDropdown` en CORE | Componente global de notificaciones | Media |

**Verificación de Fase 2:**
- Administrador activa el módulo Inventario → aparece en el dashboard del Coordinador
- Módulo desactivado → `modulo.access:inventario` retorna 403
- Cambio en `module_role` invalida la caché de visibilidad de todos los usuarios
- El dashboard muestra módulos ordenados por `orden`

**Dependencias:** Fase 1 completa.

---

### FASE 3 — Módulo Inventario (Primer módulo de negocio)

**Objetivo:** El módulo Inventario está completamente funcional, con HMB, HEB (completo), custodios, dashboard, catálogos y actas de entrega.

**Entregables:**

| Componente | Descripción | Prioridad | Nota |
|-----------|-------------|-----------|------|
| 16 tablas del dominio | bienes, detalles, historial*, responsables, etc. | Crítica | — |
| `BienesIndex` Livewire | CRUD + filtros facetados + soft delete | Crítica | Limitar a ≤ 300 líneas con Actions |
| `EditarCampoBien` + HMB completo | Con flujo de aprobación funcional | Crítica | — |
| `HmbIndex` con aprobar/rechazar | Con transacción + ActivityLogger | Crítica | — |
| **`HebIndex` con aprobar/rechazar** | **Brecha actual — implementar completo** | **Crítica** | Brecha existente en BhagamApps |
| Catálogos maestros (7 CRUD) | Con `slug` en `categorias` para grupos | Alta | — |
| `bienes_responsables` con cierre auto | La asignación nueva cierra la anterior | Alta | — |
| Dashboard Inventario | 8 grupos de métricas, Chart.js | Alta | Grupos por slug, no por ID |
| Actas de entrega | PDF con DomPDF | Media | — |
| Mantenimientos programados | Con `tipo` normalizado | Media | — |
| Permisos del módulo en `Capacidad` | Los 30+ permisos de Inventario | Crítica | Via enum, no migración inline |

**Verificación de Fase 3:**
- Flujo HMB completo: propuesta → pendiente → aprobada (bien modificado) / rechazada (bien sin cambios)
- Flujo HEB completo: solicitud → pendiente → aprobado (soft delete) / rechazado (bien activo)
- Dashboard muestra KPIs correctos con datos reales
- Bienes dados de baja son consultables via `Bien::onlyTrashed()`

**Dependencias:** Fase 1 + Fase 2 completas (rutas usan `modulo.access:inventario`).

---

### FASE 4 — Expansión modular

**Objetivo:** Los módulos adicionales (Biblioteca, EduInclusiva, etc.) se implementan o migran uno a uno. El CORE es estable y cada módulo solo necesita declarar sus metadatos, permisos y lógica de negocio.

**Entregables por módulo:**

| Módulo | Complejidad estimada | Prerequisito |
|--------|---------------------|--------------|
| Biblioteca | Media | Fase 2 |
| SINAI vs SIMAT | Baja-Media | Fase 2 |
| Evaluación Docente | Alta | Fase 3 (usa modelos de usuario y dependencias) |
| Planeador | Media | Fase 2 |
| Préstamo Tabletas | Media | Fase 2 + Inventario (bienes como tabletas) |
| AdminSistema (Backup) | Media | Fase 1 (gates con es_principal) |

**Infraestructura de expansión (Fase 4 temprana):**

| Componente | Descripción |
|-----------|-------------|
| `ModuleInstallCLI` | Artisan para instalar módulo desde manifiesto |
| Documentación del contrato de módulo | Qué debe declarar un módulo para ser compatible |
| Testing base para módulos | Suite de tests que verifica el contrato |
| Extracción opcional de ApprovalWorkflow | Si dos módulos necesitan HMB-like, extraer a servicio compartido |

**Dependencias:** Fases 1, 2 y 3 completas. Algunos módulos específicos pueden requerir Inventario.

---

### Diagrama de dependencias del roadmap

```
Fase 1 — CORE Foundation
    │  (Users · Auth · Spatie · Capacidad · es_principal · auditoria_passwords)
    │
    ├──► Fase 2 — Module Governance
    │       │  (modules · module_role · module_user · visiblesPara · ModuloAccess)
    │       │
    │       ├──► Fase 3 — Inventario
    │       │       │  (bienes · HMB · HEB completo · Dashboard · Catálogos)
    │       │       │
    │       │       └──► Préstamo Tabletas (usa bienes como entidades)
    │       │
    │       ├──► Biblioteca (Fase 2 suficiente)
    │       ├──► SINAI vs SIMAT (Fase 2 suficiente)
    │       ├──► Planeador (Fase 2 suficiente)
    │       └──► AdminSistema (Fase 1 + gates es_principal)
    │
    └──► Evaluación Docente (Fase 3 + modelos de usuario)
```

---

## Conclusión

BhagamAppsModular no es el sistema que APPSisGOE reemplaza — es el **prototipo funcional** que prueba qué decisiones de diseño sobreviven al contacto con la realidad institucional.

Las tres auditorías demuestran que las decisiones correctas de BhagamApps son patrones de dominio (HMB, custodios, es_principal, caché versioned, 4 capas de auth) — no implementaciones de código. APPSisGOE hereda esos patrones y los implementa con un stack más sólido (Spatie, Clean Architecture, manifiestos, DTOs).

Los errores de BhagamApps (dependencias circulares, fat models, sin UNIQUE, sin fuente de verdad única) son exactamente los que el stack de APPSisGOE está diseñado para prevenir — si se usan correctamente (enum Capacidad conectado al seeder, Actions en lugar de lógica en modelos, módulos sin FK hacia el CORE).

**APPSisGOE ya tiene la arquitectura correcta. Le faltan los conceptos de dominio que BhagamApps ya resolvió.**

---

*Referencia cruzada:*
- *AUDIT-RBAC-001.md — Arquitectura de autorización completa*
- *AUDIT-INV-002-APPSisGOE.md — Módulo Inventario: 16 tablas, HMB, HEB, Dashboard*
- *AUDIT-APPS-002-APPSisGOE.md — Módulo Apps: visibilidad, ciclo de vida, diseño objetivo*
