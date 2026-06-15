# ADR-001 — Core Mínimo de APPSisGOE

| Campo | Valor |
|-------|-------|
| **ID** | ADR-001 |
| **Título** | Definición del Core Mínimo |
| **Estado** | Aprobado |
| **Fecha** | 2026-06-14 |
| **Autores** | Equipo APPSisGOE |
| **Revisores** | — |
| **Documentos base** | AUDIT-RBAC-001.md · AUDIT-INV-002-APPSisGOE.md · AUDIT-APPS-002-APPSisGOE.md · ARCH-ANALYSIS-001-APPSisGOE.md |

---

## 1. Estado

**Aprobado.** Esta decisión es vinculante para toda la arquitectura de APPSisGOE. Cualquier propuesta que contradiga los límites del CORE aquí definidos requiere una ADR revisora aprobada.

---

## 2. Contexto

APPSisGOE es un sistema modular para Instituciones de Educación Estatal (IEE). Su arquitectura distingue entre un **núcleo compartido** (CORE) y **módulos de negocio** independientes. Esta distinción determina qué puede existir sin módulos instalados, qué deben importar los módulos, y qué no puede pertenecer a ningún módulo específico.

BhagamAppsModular (el predecesor funcional) demostró mediante su evolución orgánica que ciertos componentes terminan siendo consumidos por todos los módulos. La ausencia de una definición explícita del CORE en BhagamApps llevó a dependencias circulares (roles ↔ apps), acoplamiento entre módulos (User ↔ Apps ↔ Inventario), y lógica de infraestructura dispersa en módulos de negocio.

**Evidencia de ARCH-ANALYSIS-001 §2:**
> "Un componente pertenece al CORE si: (a) todos los módulos lo necesitan, o (b) su ausencia hace que el sistema no pueda arrancar, o (c) es un requerimiento transversal de seguridad, auditoría o gobernanza."

**Evidencia de AUDIT-APPS-002 §5.4:**
> "La dependencia circular (roles.app_id → apps) es el indicador más claro de que Apps no es un módulo ordinario: es infraestructura compartida."

---

## 3. Problema

No existe una definición oficial de qué pertenece al CORE de APPSisGOE. Sin esta definición:

- Los módulos pueden volverse mutuamente dependientes (acoplamiento horizontal)
- Componentes de infraestructura se implementan dentro de módulos de negocio
- El sistema no puede arrancar si falta algún módulo que en realidad es infraestructura
- La autorización, la auditoría y las notificaciones se duplican por módulo
- Es imposible diseñar el ciclo de vida de los módulos sin saber qué siempre está disponible

---

## 4. Alternativas consideradas

### Alternativa A — CORE vacío (todo en módulos)

El sistema no tiene núcleo. Cada módulo provee su propia autenticación, autorización y auditoría.

**Rechazada porque:** BhagamApps demostró que esto es impracticable. Los módulos User, Apps e Inventario terminaron importándose mutuamente. La autenticación es, por definición, previa a cualquier módulo. (AUDIT-RBAC-001 §3)

### Alternativa B — CORE máximo (todo lo institucional)

El CORE incluye Inventario, Biblioteca, Usuarios institucionales, etc.

**Rechazada porque:** Rompe la modularidad. Si Inventario está en el CORE, no puede desinstalarse ni actualizarse independientemente. El CORE debe contener solo lo que no puede ser un módulo opcional. (ARCH-ANALYSIS-001 §2.3)

### Alternativa C — CORE mínimo con contratos explícitos (decisión adoptada)

El CORE contiene exclusivamente los componentes que todos los módulos necesitan y que no pueden ser opcionales. Los módulos de negocio extienden el CORE pero no lo modifican. Esta es la alternativa adoptada.

---

## 5. Decisión

### 5.1 Definición de CORE

**El CORE de APPSisGOE está compuesto por seis componentes. Todos los módulos de negocio los consumen; ninguno los reemplaza ni los duplica.**

---

#### CORE-1: Users (Identidad y Autenticación)

**Qué incluye:**
- Modelo `User` con campos institucionales: `userID` (código institucional), `bloqueado`, `forzar_cambio_password`, `es_principal`
- Autenticación vía Fortify (login, logout, 2FA, verificación de email)
- Middleware `CheckForzarCambioPassword` — redirige a cambio de contraseña antes de acceder a cualquier módulo
- Perfil de usuario y foto de perfil (Jetstream)
- Relaciones con roles y permisos (Spatie)

**Evidencia:** AUDIT-RBAC-001 §2.1 — todos los flujos de autorización parten del modelo `User`. Sin autenticación, ninguna capa de autorización puede operar.

**Lo que NO incluye:**
- Gestión avanzada de cuentas (cambio de contraseña administrativo → Seguridad Administrativa, CORE-6)
- Perfil académico o institucional extendido (pertenece a módulos de dominio)

---

#### CORE-2: Roles y Permisos (Autorización)

**Qué incluye:**
- Motor de autorización: `spatie/laravel-permission`
- `Capacidad` enum como **única fuente de verdad** de todos los permisos del sistema
- 7 roles institucionales predefinidos: Administrador, Rectoría, Coordinación, Auxiliar, Docente, Estudiante, Invitado
- Gates con doble condición `permission + es_principal` para operaciones críticas
- Middleware `RequiereCapacidad` (análogo a `CheckPermission` de BhagamApps) con alias `can:{capacidad}`
- Seeder de permisos generado desde `Capacidad::cases()`

**Evidencia:** AUDIT-RBAC-001 §4 — 85 permisos en 17 categorías; sin el sistema de permisos, ningún módulo puede controlar quién hace qué. ARCH-ANALYSIS-001 §2.1 CORE-2.

**Lo que NO incluye:**
- La interfaz de gestión de roles (es una pantalla del módulo User que *usa* el CORE-2, no parte del CORE)
- Permisos específicos de módulos de negocio — estos se declaran en `Capacidad` pero su UI de gestión pertenece al módulo respectivo

---

#### CORE-3: Módulos (Gobernanza y Visibilidad)

**Qué incluye:**
- Tabla `modules` con ciclo de vida (6 estados: Pendiente, Instalando, Activo, Inactivo, Error, Desinstalado)
- Campos visuales en `modules`: `icono`, `color`, `orden`, `ruta_entrada`
- Tabla `module_role` (M:N con UNIQUE) — qué roles acceden a qué módulos
- Tabla `module_user` (M:N con `activo` y UNIQUE) — excepciones individuales de acceso
- `ModuleVisibilityService::visiblesPara(User $user)` con caché versioned (300s, invalidación por `modules.cache_version`)
- Middleware `ModuloAccessMiddleware` con alias `modulo.access:{key}`
- Actions del ciclo de vida: `ModuleInstallAction`, `ModuleActivateAction`, `ModuleDeactivateAction`
- Dashboard institucional: muestra módulos visibles al usuario autenticado
- Validación de dependencias entre módulos antes de activar/desactivar

**Evidencia:** AUDIT-APPS-002 §6.3 — "APPSisGOE debe incorporar exactamente esa solución, adaptada a su arquitectura limpia". ARCH-ANALYSIS-001 §5 — "Apps no debe existir como módulo en APPSisGOE. Su lógica pertenece al CORE-3."

**Lo que NO incluye:**
- El módulo `Apps` de BhagamApps como módulo separado — su lógica queda absorbida en CORE-3
- Contenido de negocio de módulos individuales

---

#### CORE-4: Auditoría (Trazabilidad)

**Qué incluye:**
- `ActivityLogger` — servicio inyectable transversal disponible para todos los módulos
- Tabla `activity_logs` con índices compuestos por módulo, acción, objeto, fecha
- Tabla `auditoria_passwords` — registro inmutable (solo INSERT) de operaciones administrativas sobre cuentas
- Interfaz de Activity Log protegida por `es_principal` (gate con doble condición)

**Evidencia:** AUDIT-INV-002 §10.4 — "ActivityLogger ya es un servicio del módulo ActivityLog. APPSisGOE debe preservar este patrón pero moverlo al CORE como servicio transversal." AUDIT-RBAC-001 §2.8 — gate `ver-activity-log` requiere `es_principal`.

**Lo que NO incluye:**
- Historiales de dominio (historial_modificaciones_bienes, historial_ubicaciones_bienes, etc.) — estos pertenecen a los módulos de negocio
- Auditoría de backups — pertenece al módulo AdminSistema

---

#### CORE-5: Notificaciones

**Qué incluye:**
- Tabla `notifications` (estándar Laravel Notifications)
- Componentes Livewire globales: `NotificacionesDropdown` y `NotificacionesIcono` (en el layout del CORE)
- Contrato de notificación: cualquier módulo puede emitir notificaciones vía `Notification::send()` y aparecerán en el dropdown global

**Evidencia:** AUDIT-INV-002 §10.4 — "El sistema de notificaciones Laravel (tabla `notifications` + `Notification::send()`) es compartido. No duplicar." AUDIT-INV-002 §7.5 — `NotificacionesDropdown` y `NotificacionesIcono` no son de Inventario, son de infraestructura.

**Lo que NO incluye:**
- Las clases `Notification` concretas de cada módulo (ej. `NotificacionHmb`, `NotificacionHeb`) — estas pertenecen al módulo que las emite
- Canales de notificación externos (email, SMS) — son configuración de infraestructura

---

#### CORE-6: Seguridad Administrativa

**Qué incluye:**
- Campo `es_principal` en `users` — marca al Administrador Principal (super-admin de recuperación)
- Trait `ProteccionAdminPrincipal` — disponible para cualquier componente del sistema; aborta y registra intentos de modificar al admin principal
- Tabla `auditoria_passwords` y registro de operaciones (compartida con CORE-4)
- Middleware `CheckForzarCambioPassword` (compartido con CORE-1)
- Gates con doble condición: `can($permiso) && $user->es_principal`

**Evidencia:** AUDIT-RBAC-001 §2.9 — "Solo puede existir uno [es_principal]. Protecciones aplicadas en 7 componentes distintos." ARCH-ANALYSIS-001 §7.4 — "El concepto de `es_principal` como guardia de recuperación tiene valor alto."

**Invariante del sistema:** Debe existir siempre exactamente 1 usuario con `es_principal = true`. Verificable en tests de integridad.

---

### 5.2 Qué NO pertenece al CORE

| Componente | Categoría | Razón |
|-----------|-----------|-------|
| Backup y recuperación | Módulo AdminSistema | Capacidad operacional, no infraestructura. Requiere `es_principal` pero eso es un gate de seguridad, no una razón de pertenencia al CORE. |
| Dashboard de métricas de módulos | Módulos individuales | Cada módulo provee sus propias métricas. El CORE provee el container (layout), no el contenido. |
| Inventario (bienes, HMB, HEB) | Módulo Inventario | Dominio de negocio específico de la institución. |
| Catálogos maestros (categorías, ubicaciones, etc.) | Módulos de dominio | Pertenecen al módulo que los usa. |
| Evaluación docente, Biblioteca, etc. | Módulos de negocio | Dominios institucionales específicos. |
| Gestión CRUD de roles (UI) | Módulo User | *Usa* CORE-2 pero no es CORE. La interfaz de gestión no es infraestructura. |
| Interfaz de gestión de permisos (UI) | Módulo User | Igual que roles. |

---

### 5.3 Diagrama del CORE

```
┌─────────────────────────────────────────────────────────────────────────┐
│                       CORE APPSisGOE                                    │
│                                                                         │
│  CORE-1             CORE-2               CORE-3                         │
│  ┌───────────┐      ┌──────────────┐     ┌─────────────────────┐       │
│  │  Users    │─────►│  Roles &     │────►│  Modules            │       │
│  │           │      │  Permisos    │     │                     │       │
│  │ Fortify   │      │              │     │ modules table       │       │
│  │ bloqueado │      │ Spatie       │     │ module_role         │       │
│  │ forzar_cp │      │ Capacidad    │     │ module_user         │       │
│  │ es_princi │      │ enum         │     │ visiblesPara()      │       │
│  └───────────┘      │ 7 roles IEE  │     │ ModuloAccess MW     │       │
│        │            └──────────────┘     │ Lifecycle Actions   │       │
│        │                   │             └─────────────────────┘       │
│        │            CORE-4 │ CORE-5              │                     │
│        │  ┌────────────────▼──────────────────┐  │                     │
│        └─►│  Auditoría  │  Notificaciones     │◄─┘                     │
│           │             │                     │                        │
│           │ ActivityLog │ notifications table  │                        │
│           │ audit_pwds  │ NotifDropdown        │                        │
│           └─────────────┴─────────────────────┘                        │
│                                                                         │
│  CORE-6 — Seguridad Administrativa                                      │
│  ┌──────────────────────────────────────────────────────────────────┐  │
│  │ ProteccionAdminPrincipal trait  ·  Gates doble condición         │  │
│  │ CheckForzarCambioPassword MW    ·  auditoria_passwords (shared)  │  │
│  └──────────────────────────────────────────────────────────────────┘  │
│                                                                         │
│  Capas de autorización activas en TODA request:                         │
│  CORE-1 (auth) → CORE-3 (modulo.access) → CORE-2 (can) → Action       │
└─────────────────────────────────────────────────────────────────────────┘
         ↓ consume          ↓ consume          ↓ consume
    ┌──────────┐       ┌──────────┐       ┌──────────┐
    │ Módulo   │       │ Módulo   │       │ Módulo   │
    │Inventario│       │Biblioteca│       │  User    │
    └──────────┘       └──────────┘       └──────────┘
```

---

## 6. Consecuencias

### Positivas
- El sistema puede arrancar y autenticar usuarios sin ningún módulo de negocio instalado
- Los módulos no tienen dependencias horizontales entre sí — solo dependen del CORE
- La lógica de autorización, auditoría y notificaciones no se duplica en cada módulo
- Un módulo puede instalarse, activarse y desinstalarse sin afectar a otros módulos
- Los tests del CORE validan la infraestructura de forma aislada

### Negativas / Trade-offs
- El CORE tiene más responsabilidades que un framework base típico — su equipo debe mantenerlo con disciplina
- Un bug en CORE-2 (permisos) afecta a todos los módulos simultáneamente
- La curva de onboarding para nuevos desarrolladores de módulos requiere conocer el contrato del CORE antes de comenzar

### Restricciones que impone esta decisión
- **Ningún módulo puede definir su propio sistema de autenticación.** Toda autenticación usa CORE-1.
- **Ningún módulo puede definir su propio sistema de permisos.** Todo usa CORE-2 (Spatie + Capacidad enum).
- **Ningún módulo puede crear su propia tabla `notifications`.** Todo usa CORE-5.
- **Ningún módulo puede ser accesible sin pasar por `modulo.access:{key}`.** Todo usa CORE-3.
- **El Administrador Principal (`es_principal`) no puede ser modificado por ningún módulo de negocio.** Solo CORE-6.

---

## 7. Riesgos

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|-------------|---------|------------|
| CORE evoluciona rompiendo módulos | Media | Alto | Versionado semver del CORE; módulos declaran `min_core` en manifiesto (ver ADR-005) |
| `Capacidad` enum crece sin control | Alta | Medio | Revisión arquitectónica requerida para agregar capacidades de nuevas categorías |
| Bug en `ModuleVisibilityService` deja a usuarios sin acceso a módulos | Baja | Muy alto | Tests de integración obligatorios; caché con TTL máximo de 300s (auto-recuperación) |
| Dos módulos intentan definir el mismo slug de permiso | Media | Alto | El enum `Capacidad` con slug único previene duplicados en tiempo de compilación |
| `es_principal` queda sin usuario asignado | Muy baja | Muy alto | Test de invariante: `users WHERE es_principal = true COUNT = 1` en suite CI |

---

## 8. Justificación basada en evidencia

| Afirmación | Evidencia |
|-----------|-----------|
| Users es CORE | AUDIT-RBAC-001 §3: todas las capas de autorización parten de `$user`. Sin auth no hay sistema. |
| Roles y Permisos es CORE | AUDIT-RBAC-001 §4-5: 85 permisos en 17 categorías usados por todos los módulos. ARCH-ANALYSIS-001 ERROR-006: sin fuente de verdad única el catálogo se fragmenta. |
| Módulos es CORE (no es un módulo) | AUDIT-APPS-002 §6.1: "Apps no gestiona ningún concepto del dominio educativo." §5.4: "dependencia circular indica que Apps es infraestructura compartida." |
| Auditoría es CORE | AUDIT-INV-002 §10.4: ActivityLogger ya es transversal. AUDIT-RBAC-001 §1.1: `auditoria_passwords` es requerimiento de instituciones estatales. |
| Notificaciones es CORE | AUDIT-INV-002 §7.5: NotificacionesDropdown/Icono deben estar en el layout global. §10.4: "No duplicar — usar el mismo mecanismo en todos los módulos." |
| Seguridad Administrativa es CORE | AUDIT-RBAC-001 §2.9: `ProteccionAdminPrincipal` se usa en 7 componentes distintos. ARCH-ANALYSIS-001 ACIERTO-004: "es_principal como guardia de recuperación tiene valor alto." |
| Inventario NO es CORE | AUDIT-INV-002 §1: es un dominio de gestión de bienes institucionales. No es infraestructura. |
| Apps NO es un módulo separado | AUDIT-APPS-002 §9: "Su lógica debe absorberse en el CORE." ARCH-ANALYSIS-001 §5.1: "Apps no debe existir como módulo en APPSisGOE." |

---

*Decisiones relacionadas: ADR-002 (Gobernanza de Módulos) · ADR-003 (Autorización) · ADR-005 (Versionado)*
