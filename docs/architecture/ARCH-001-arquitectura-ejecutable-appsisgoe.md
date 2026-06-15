# ARCH-001 — Arquitectura Ejecutable de APPSisGOE

| Campo | Valor |
|-------|-------|
| **Documento** | ARCH-001 |
| **Versión** | 1.0.0 |
| **Estado** | **Aprobado — Vigente** |
| **Fecha** | 2026-06-14 |
| **Aplica a** | APPSisGOE v1.x |
| **Autoridad** | Documento de arquitectura oficial. Vinculante para diseño, desarrollo, auditoría y revisión de código. |
| **Fuentes** | ADR-001 · ADR-002 · ADR-003 · ADR-004 · ADR-005 · ARCH-ANALYSIS-001 · AUDIT-RBAC-001 · AUDIT-INV-002 · AUDIT-APPS-002 |

---

## Tabla de Contenidos

1. [Resumen Ejecutivo](#1-resumen-ejecutivo)
2. [Principios Arquitectónicos](#2-principios-arquitectónicos)
3. [Arquitectura General](#3-arquitectura-general)
4. [Arquitectura del CORE](#4-arquitectura-del-core)
5. [Gobernanza de Módulos](#5-gobernanza-de-módulos)
6. [Arquitectura de Autorización](#6-arquitectura-de-autorización)
7. [Arquitectura de Datos](#7-arquitectura-de-datos)
8. [Arquitectura de Eventos e Integración](#8-arquitectura-de-eventos-e-integración)
9. [Arquitectura de Auditoría](#9-arquitectura-de-auditoría)
10. [Arquitectura de Notificaciones](#10-arquitectura-de-notificaciones)
11. [Arquitectura de Versionado](#11-arquitectura-de-versionado)
12. [Arquitectura de Despliegue](#12-arquitectura-de-despliegue)
13. [Reglas Arquitectónicas Vinculantes](#13-reglas-arquitectónicas-vinculantes)
14. [Roadmap Arquitectónico Oficial](#14-roadmap-arquitectónico-oficial)
15. [Trazabilidad de Decisiones](#15-trazabilidad-de-decisiones)

---

## 1. Resumen Ejecutivo

### 1.1 Qué es APPSisGOE

APPSisGOE es una **plataforma institucional modular** para Instituciones de Educación Estatal (IEE) colombianas. Provee un núcleo de infraestructura (autenticación, autorización, gobernanza de módulos, auditoría y notificaciones) sobre el cual se construyen módulos de negocio independientes: Inventario, Biblioteca, Evaluación Docente, Préstamo de Tabletas, entre otros.

**Stack técnico:**

| Componente | Tecnología |
|-----------|-----------|
| Framework | Laravel 13 |
| PHP | 8.4 |
| Frontend | Livewire 3 + Alpine.js |
| Autorización | Spatie/laravel-permission |
| Autenticación | Fortify + Jetstream |
| Módulos | Clean Architecture (Actions, DTOs, Services) |
| Ciclo de vida de módulos | 6 estados con manifiestos JSON |

### 1.2 Qué problema resuelve

Las IEE colombianas operan con múltiples sistemas desconectados: inventario de bienes, biblioteca, evaluaciones, préstamo de equipos. APPSisGOE unifica estos sistemas bajo una plataforma única con:

- Un único sistema de autenticación y autorización
- Gestión centralizada de módulos (activar/desactivar sin reinstalar)
- Trazabilidad completa de acciones sobre datos institucionales
- Control de visibilidad: qué usuarios ven qué módulos según su rol

### 1.3 Diferencias frente a BhagamApps

BhagamAppsModular (predecesor funcional, auditado en junio 2026) es el prototipo que validó los conceptos de dominio. APPSisGOE los implementa con una arquitectura más sólida.

| Dimensión | BhagamAppsModular | APPSisGOE |
|-----------|-------------------|-----------|
| Arquitectura | Fat Models / Fat Components | Clean Architecture: Actions, DTOs, Services |
| RBAC | Implementación custom (2 queries por permiso, sin caché) | Spatie Permission (memoizado, constraints correctos) |
| Permisos | Dispersos en CSV, migraciones y seeders | `Capacidad` enum como única fuente de verdad |
| Ciclo de vida de módulos | 2 estados (habilitada/deshabilitada) | 6 estados con Actions explícitas |
| Descriptores de módulo | Seeders PHP (datos en BD) | `module.json` con hash de integridad |
| Visibilidad institucional | `App::visiblesPara()` en modelo | `ModuleVisibilityService` inyectable |
| Gates de autorización | 60 gates delegando a hasPermission | Solo 3 gates (operaciones críticas con `es_principal`) |
| Versionado | Semver en BhagamApps solo | Semver independiente por CORE y módulo con contrato `min_core` |
| Dependencias circulares | `roles.app_id → apps` (circular) | Los roles no tienen FK hacia módulos |

**Conceptos de BhagamApps adoptados sin cambios:**
- Arquitectura de 4 capas de autorización (defensa en profundidad)
- Concepto `es_principal` / Administrador Principal
- Trait `ProteccionAdminPrincipal`
- Tabla `auditoria_passwords` (inmutable)
- Lógica OR en visibilidad de módulos (rol OR usuario individual)
- Caché versioned con `cache()->increment()`
- Flujos HMB y HEB (workflows de aprobación para modificaciones y bajas)
- Modelo de cadena de custodia con `fecha_retiro IS NULL`
- SoftDeletes para bienes institucionales
- Módulos nuevos deshabilitados por defecto (mínimo privilegio)

**Conceptos de BhagamApps descartados:**
- `roles.app_id` FK (dependencia circular, eliminada)
- `User::hasPermission()` manual (reemplazado por Spatie)
- `Role::hasPermission($nombre)` (buscaba por nombre, no slug — eliminado)
- 60 gates delegando a hasPermission (boilerplate innecesario)
- Permisos en migraciones inline y seeders CSV (reemplazados por enum)
- Campo `origen` varchar en bienes (legacy, solo existe `origen_id`)
- `mantenimiento_id` FK directa en bienes (redundante con `mantenimientos_programados`)
- IDs de categorías hardcodeados en dashboard (reemplazados por slugs)
- Módulo `Apps` como módulo separado (absorbido en CORE-3)
- `CheckForzarCambioPassword` doble registro (un único punto en `bootstrap/app.php`)

### 1.4 Objetivo estratégico

Construir una plataforma que permita a cualquier IEE colombiana instalar y configurar los módulos que necesita, garantizando trazabilidad institucional, control de acceso por roles y cumplimiento de requerimientos de auditoría interna del sector educativo estatal.

---

## 2. Principios Arquitectónicos

Estos principios son normativos. Toda decisión de diseño que los contradiga requiere una ADR revisora aprobada.

---

### PRINC-01 — CORE Mínimo

**Definición:** El CORE contiene exclusivamente los componentes que todos los módulos necesitan y que no pueden ser opcionales. Un componente pertenece al CORE si: (a) todos los módulos lo necesitan, o (b) su ausencia impide que el sistema arranque, o (c) es un requerimiento transversal de seguridad, auditoría o gobernanza institucional.

**Implicación:** El CORE no contiene lógica de negocio. Contiene infraestructura. Un módulo de negocio (Inventario, Biblioteca) nunca es CORE.

**Origen:** ADR-001 §5, ARCH-ANALYSIS-001 §2.

---

### PRINC-02 — Modularidad Autónoma

**Definición:** Cada módulo de negocio es una unidad autónoma que declara en su `module.json` todo lo que el sistema necesita saber de él: identidad, versión, dependencias, compatibilidad y metadatos visuales. Un módulo puede instalarse, actualizarse y desinstalarse sin modificar el CORE ni otros módulos.

**Implicación:** Los módulos no se instalan en el código fuente — se registran en la base de datos y el sistema los carga condicionalmente según su estado.

**Origen:** ADR-002 §5.1-5.2, ARCH-ANALYSIS-001 §5.

---

### PRINC-03 — Desacoplamiento Horizontal

**Definición:** Los módulos de negocio no pueden depender directamente entre sí. Toda comunicación entre módulos ocurre a través del CORE: por eventos (CORE-5 Notificaciones), por servicios compartidos del CORE (CORE-4 ActivityLogger), o mediante contratos de API explícitos formalizados en una ADR.

**Implicación:** Un módulo no puede importar modelos, clases ni servicios de otro módulo sin una ADR que formalice esa interfaz. Esto previene la cadena de dependencias circulares que afectó a BhagamApps.

**Origen:** ARCH-ANALYSIS-001 ERROR-001, ADR-001 §5.2.

---

### PRINC-04 — Versionado Independiente

**Definición:** El CORE y cada módulo tienen versiones semver independientes. Los módulos declaran la versión mínima del CORE requerida (`min_core`). El sistema verifica compatibilidad antes de activar un módulo.

**Implicación:** Es posible actualizar el módulo Inventario de 1.1.0 a 1.2.0 sin tocar el CORE. Es posible actualizar el CORE de 1.0.0 a 1.1.0 sin que los módulos cambien, siempre que el cambio sea retrocompatible (MINOR).

**Origen:** ADR-005 §5.1-5.4.

---

### PRINC-05 — Auditoría Inmutable

**Definición:** Toda acción significativa sobre datos institucionales deja un registro inmutable. Los registros de auditoría son solo de inserción (no se actualizan, no se eliminan). Existen dos niveles: (a) auditoría transversal del CORE (`activity_logs`, `auditoria_passwords`) y (b) historiales de dominio dentro de cada módulo.

**Implicación:** No existe `UPDATE` ni `DELETE` sobre tablas de historial. Si se "cancela" una solicitud, se registra el estado de cancelación — no se elimina la solicitud.

**Origen:** ADR-001 CORE-4, ADR-004 §5.4-5.5, ARCH-ANALYSIS-001 ACIERTO-003.

---

### PRINC-06 — Trazabilidad Total

**Definición:** En APPSisGOE, siempre es posible responder: ¿quién hizo qué, sobre qué entidad, en qué momento, con qué resultado? Esta capacidad no es opcional ni depende del módulo — es un requerimiento institucional del sector educativo estatal colombiano.

**Implicación:** `ActivityLogger::log()` es obligatorio en toda acción que modifique estado persistente. Las operaciones sobre cuentas de usuario se registran en `auditoria_passwords`.

**Origen:** AUDIT-RBAC-001 §1.1, AUDIT-INV-002 §10.4, ADR-001 CORE-4.

---

### PRINC-07 — Gobernanza Explícita

**Definición:** Ninguna capacidad del sistema es accesible sin autorización explícita. Toda operación requiere: (1) que el usuario esté autenticado, (2) que el módulo esté activo y visible para el usuario, (3) que el usuario tenga el permiso específico, y (4) que la acción verifique el permiso en su punto de ejecución. El acceso por defecto es denegado.

**Implicación:** Los módulos nuevos se instalan con `status = Pendiente` y deben ser activados explícitamente. Los permisos nuevos deben asignarse explícitamente a roles. Ningún componente da acceso implícito.

**Origen:** ADR-003 §5.1, ADR-002 §5.6, ARCH-ANALYSIS-001 ACIERTO-007.

---

## 3. Arquitectura General

### 3.1 Vista de capas

```
╔══════════════════════════════════════════════════════════════════════╗
║                      INFRAESTRUCTURA                                 ║
║              Laravel 13 · PHP 8.4 · MySQL · Redis                   ║
╚══════════════════════════════════════════════════════════════════════╝
                              ▲
╔══════════════════════════════════════════════════════════════════════╗
║                         CORE APPSisGOE                               ║
║                                                                      ║
║  CORE-1: Users      CORE-2: Roles/Permisos   CORE-3: Módulos        ║
║  ─────────────      ───────────────────────   ─────────────────      ║
║  Fortify auth       Spatie Permission          modules table         ║
║  bloqueado          Capacidad enum             module_role           ║
║  es_principal       7 roles IEE                module_user           ║
║  forzar_cambio      Gates críticos             visiblesPara()        ║
║                                                ModuloAccess MW       ║
║  CORE-4: Auditoría  CORE-5: Notificaciones   CORE-6: Seguridad      ║
║  ─────────────────  ───────────────────────   ────────────────       ║
║  ActivityLogger     notifications table        ProteccionAdmin       ║
║  activity_logs      NotifDropdown              auditoria_passwords   ║
║  auditoria_pwds     NotifIcono                 CheckForzarPwd MW     ║
║                                                                      ║
║  Capas auth: CORE-1 → CORE-3 → CORE-2 → Action                     ║
╚══════════════════════════════════════════════════════════════════════╝
                              ▲ todos los módulos consumen el CORE
╔═══════════════╗  ╔═══════════════╗  ╔═══════════════╗  ╔══════════╗
║    MÓDULO     ║  ║    MÓDULO     ║  ║    MÓDULO     ║  ║  MÓDULO  ║
║  Inventario   ║  ║     User      ║  ║ AdminSistema  ║  ║ Bibliot. ║
║  ───────────  ║  ║  ──────────   ║  ║  ──────────   ║  ║  ──────  ║
║  bienes       ║  ║  UI usuarios  ║  ║  Backup/Rest. ║  ║ libros   ║
║  HMB / HEB   ║  ║  UI roles     ║  ║  Activity UI  ║  ║ préstamo ║
║  custodios    ║  ║  UI permisos  ║  ║               ║  ║          ║
║  dashboard    ║  ║               ║  ║               ║  ║          ║
╚═══════════════╝  ╚═══════════════╝  ╚═══════════════╝  ╚══════════╝
       │                  │                  │                  │
       └──────────────────┴──────────────────┴──────────────────┘
                    Los módulos no se comunican entre sí.
                Solo se comunican hacia arriba (CORE).
```

### 3.2 Flujo de una request típica

```
Usuario en browser
    │
    ▼
HTTP Request → Laravel Router
    │
    ├─ Middleware Stack (en orden):
    │   1. web (sesión, CSRF, cookies)
    │   2. auth + verified (CORE-1: autenticación)
    │   3. CheckForzarCambioPassword (CORE-6)
    │   4. modulo.access:{key} (CORE-3: visibilidad)
    │   5. can:{capacidad} (CORE-2: permiso de ruta)
    │
    ▼
Controller / Livewire Component
    │
    ├─ Capa 4 (verificación en Action):
    │   $this->authorize(Capacidad::EditarBienes->value)
    │
    ▼
Action ejecuta lógica de negocio
    ├─ DB::transaction() para mutaciones
    ├─ ActivityLogger::log(...) para trazabilidad
    ├─ Notification::send(...) si aplica
    │
    ▼
Response → Browser
```

### 3.3 Modelo de visibilidad de módulos

```
Usuario autenticado
    │
    ▼
ModuleVisibilityService::visiblesPara($user)
    │
    ├─ Caché versioned: "modules.visibles.{userId}.v{version}"
    │   ├─ HIT (TTL 300s) → retorna colección cacheada
    │   └─ MISS → ejecuta query:
    │
    │       SELECT modules.*
    │       FROM modules
    │       WHERE status = 'Activo'
    │       AND (
    │           EXISTS (SELECT 1 FROM module_role
    │                   WHERE module_role.module_id = modules.id
    │                   AND module_role.role_id = user.role_id)
    │           OR
    │           EXISTS (SELECT 1 FROM module_user
    │                   WHERE module_user.module_id = modules.id
    │                   AND module_user.user_id = user.id
    │                   AND module_user.activo = 1)
    │       )
    │       ORDER BY orden ASC, name ASC
    │
    ▼
Colección de módulos visibles → Dashboard institucional
```

---

## 4. Arquitectura del CORE

El CORE es la única capa de la que los módulos dependen. No tiene lógica de negocio. No conoce a Inventario, Biblioteca ni ningún módulo específico. **ADR-001.**

### 4.1 CORE-1 — Users (Identidad y Autenticación)

**Responsabilidades:**
- Autenticar usuarios mediante Fortify (login, logout, 2FA, verificación de email)
- Gestionar el estado de la cuenta: `bloqueado`, `forzar_cambio_password`, `es_principal`
- Proveer el modelo `User` con relaciones hacia roles y permisos (Spatie)
- Interceptar cuentas bloqueadas antes de cualquier acceso
- Forzar cambio de contraseña antes de cualquier acceso al sistema

**Campos institucionales del modelo `User`:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `userID` | varchar UNIQUE | Código institucional (ej. número de cédula) |
| `bloqueado` | boolean | Si true: la sesión se cierra y el login es bloqueado |
| `forzar_cambio_password` | boolean | Si true: redirige a cambiar contraseña en el próximo acceso |
| `es_principal` | boolean | Marca al Administrador Principal (solo 1 en el sistema) |

**Lo que CORE-1 NO incluye:**
- Gestión CRUD de usuarios (eso es el módulo User)
- Perfiles académicos o administrativos extendidos (eso es el módulo que los necesita)

**Por qué está en el CORE:** Sin autenticación no existe ninguna capa de seguridad. El modelo `User` es el punto de partida de todo el sistema de autorización. (AUDIT-RBAC-001 §2.1)

---

### 4.2 CORE-2 — Roles y Permisos (Autorización)

**Responsabilidades:**
- Proveer el motor de autorización granular: `spatie/laravel-permission`
- Mantener el enum `Capacidad` como única fuente de verdad de todos los permisos
- Definir los 7 roles institucionales de una IEE colombiana
- Definir los 3 gates con doble condición (`can + es_principal`)
- Proveer el middleware `can:{capacidad}` para protección de rutas

**Los 7 roles institucionales:**

| Rol | Descripción | Permisos base |
|-----|-------------|---------------|
| Administrador | Acceso completo | Todos los permisos |
| Rectoría | Orienta todos los procesos | Todos excepto CRUD de roles/permisos |
| Coordinación | Supervisa procesos | Usuarios básico + Bienes básico |
| Auxiliar | Apoya procesos | Bienes básico |
| Docente | Imparte clases | Bienes básico |
| Estudiante | Acceso a contenidos | Sin permisos por defecto |
| Invitado | Acceso de prueba | Sin permisos por defecto |

**Convención de slugs en `Capacidad` enum:**

| Scope | Formato | Ejemplo |
|-------|---------|---------|
| Módulos de negocio | `{verbo}-{entidad}` | `ver-bienes`, `crear-bienes` |
| CORE (gobernanza de módulos) | `modulos:{accion}` | `modulos:instalar`, `modulos:activar` |
| CORE (operaciones críticas) | `admin:{accion}` | `admin:ver-activity-log` |

**Lo que CORE-2 NO incluye:**
- La interfaz de gestión de roles y permisos (eso es el módulo User)
- Los permisos específicos de módulos en el código de los módulos — se declaran en el enum global

**Por qué está en el CORE:** Sin permisos, ningún módulo puede controlar qué hace cada usuario. El catálogo de 85 permisos de BhagamApps era consumido por todos los módulos. (AUDIT-RBAC-001 §4)

---

### 4.3 CORE-3 — Módulos (Gobernanza y Visibilidad)

**Responsabilidades:**
- Mantener el registro de todos los módulos instalados (`modules` table)
- Gestionar el ciclo de vida: Pendiente → Instalando → Inactivo → Activo / Error / Desinstalado
- Controlar la visibilidad: qué roles/usuarios ven qué módulos (`module_role`, `module_user`)
- Proveer `ModuleVisibilityService::visiblesPara($user)` con caché versioned
- Proveer `ModuloAccessMiddleware` (alias `modulo.access:{key}`)
- Validar compatibilidad antes de activar módulos (`ModuleActivateAction`)
- Exponer el dashboard institucional con los módulos visibles al usuario

**Esquema de tablas:**

```sql
-- Extiende la tabla 'modules' existente en APPSisGOE
ALTER TABLE modules
    ADD COLUMN icono        VARCHAR(255)  NULL,        -- ej: 'fas fa-boxes'
    ADD COLUMN color        VARCHAR(20)   NULL,        -- ej: '#28a745'
    ADD COLUMN orden        INT UNSIGNED  NOT NULL DEFAULT 99,
    ADD COLUMN ruta_entrada VARCHAR(255)  NULL,        -- ej: '/inventario'
    ADD COLUMN min_core     VARCHAR(20)   NULL;        -- ej: '1.0.0'

-- Visibilidad por rol (ALL users of this role see this module)
CREATE TABLE module_role (
    id        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    role_id   BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
    CONSTRAINT fk_mr_module FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    CONSTRAINT fk_mr_role   FOREIGN KEY (role_id)   REFERENCES roles(id)   ON DELETE CASCADE,
    UNIQUE KEY uq_module_role (module_id, role_id)
);

-- Excepciones individuales de visibilidad
CREATE TABLE module_user (
    id        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    user_id   BIGINT UNSIGNED NOT NULL,
    activo    TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
    CONSTRAINT fk_mu_module FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    CONSTRAINT fk_mu_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    UNIQUE KEY uq_module_user (module_id, user_id)
);
```

**Principio de visibilidad heredado de BhagamApps:** OR lógico entre rol e individual. El acceso individual no cancela el acceso por rol — es un canal adicional. (AUDIT-APPS-002 §3.2)

**Capacidades de gobernanza:**

| Acción | Capacidad requerida |
|--------|-------------------|
| Ver catálogo de módulos | `modulos:ver` |
| Instalar módulo | `modulos:instalar` |
| Activar módulo | `modulos:activar` |
| Desactivar módulo | `modulos:desactivar` |
| Asignar módulo a rol | `modulos:asignar_rol` |
| Asignar módulo a usuario | `modulos:asignar_usuario` |
| Desinstalar módulo | `modulos:desinstalar` |

**Por qué está en el CORE:** Sin el registro de módulos y el middleware de acceso, ningún módulo es accesible. En BhagamApps, la tabla `apps` era una dependencia de arranque — el CORE-3 la formaliza correctamente. (AUDIT-APPS-002 §5.4, §6.2)

---

### 4.4 CORE-4 — Auditoría (Trazabilidad)

**Responsabilidades:**
- Proveer `ActivityLogger` como servicio inyectable transversal
- Mantener `activity_logs`: registro de todas las acciones significativas en todos los módulos
- Mantener `auditoria_passwords`: registro inmutable de operaciones administrativas sobre cuentas
- Proteger el acceso al Activity Log con gate de doble condición (`es_principal`)

**Esquema `activity_logs`:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `modulo` | varchar | Módulo que generó el evento |
| `accion` | varchar | Acción realizada (crear, editar, aprobar, rechazar...) |
| `descripcion` | text | Descripción human-readable |
| `tipo_objeto` | varchar | Entidad afectada (Bien, Usuario, Módulo...) |
| `objeto_id` | bigint | ID de la entidad afectada |
| `user_id` | bigint FK | Usuario que realizó la acción |
| `created_at` | timestamp | Timestamp del evento |

**Esquema `auditoria_passwords`:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `usuario_afectado_id` | bigint FK | Usuario sobre el que se actuó |
| `administrador_id` | bigint FK | Administrador que ejecutó la acción |
| `accion` | enum | `password_reset`, `password_forced`, `user_blocked`, `user_unblocked` |
| `fecha_hora` | timestamp | Timestamp del evento |

La tabla `auditoria_passwords` no tiene `updated_at` — solo INSERT, nunca UPDATE.

**Contrato de uso de ActivityLogger:**

```php
ActivityLogger::log(
    modulo:      'Inventario',
    accion:      'aprobar',
    descripcion: "Modificación aprobada en Bien ID {$bienId}",
    tipoObjeto:  'Bien',
    objetoId:    $bienId,
);
```

**Por qué está en el CORE:** Es consumido por todos los módulos. En BhagamApps, el módulo ActivityLog ya existía como módulo separado — la auditoría demostró que es transversal. (AUDIT-INV-002 §10.4)

---

### 4.5 CORE-5 — Notificaciones

**Responsabilidades:**
- Mantener la tabla `notifications` (estándar Laravel Notifications)
- Proveer los componentes Livewire globales: `NotificacionesDropdown` y `NotificacionesIcono` (en el layout principal)
- Definir el contrato: cualquier módulo puede emitir notificaciones via `Notification::send()` y aparecerán en el dropdown global

**Contrato de uso:**

```php
// En cualquier módulo — emitir notificación:
Notification::send(
    User::role('Administrador')->get(),
    new NotificacionHmb($modificacion)  // Clase Notification del módulo Inventario
);

// El CORE muestra las notificaciones en el dropdown — sin modificar nada del CORE.
```

**Lo que CORE-5 NO incluye:**
- Las clases `Notification` concretas de cada módulo (pertenecen al módulo que las emite)
- Canales de notificación externos (email, SMS) — son configuración de infraestructura

**Por qué está en el CORE:** La tabla `notifications` y el UI son compartidos. BhagamApps puso `NotificacionesDropdown` dentro del módulo Inventario — un error de ubicación que este CORE corrige. (AUDIT-INV-002 §7.5, §10.4)

---

### 4.6 CORE-6 — Seguridad Administrativa

**Responsabilidades:**
- Definir el campo `es_principal` en `users` y su invariante (exactamente 1 en el sistema)
- Proveer el trait `ProteccionAdminPrincipal`
- Registrar el middleware `CheckForzarCambioPassword` en un único punto
- Definir los 3 gates con doble condición en `AuthServiceProvider`

**Invariante `es_principal`:** En todo momento, `SELECT COUNT(*) FROM users WHERE es_principal = true = 1`. Este invariante se verifica en la suite de tests CI.

**`ProteccionAdminPrincipal` trait:**

```php
trait ProteccionAdminPrincipal
{
    protected function verificarNoEsAdminPrincipal(User $target, string $accion): void
    {
        if (!$target->es_principal) return;

        AuditoriaPassword::create([
            'usuario_afectado_id' => $target->id,
            'administrador_id'    => auth()->id(),
            'accion'              => $accion,
            'fecha_hora'          => now(),
        ]);

        abort(403, 'El Administrador Principal no puede ser modificado.');
    }
}
```

**Uso obligatorio en:** cambio de rol, cambio de password (administrativo), bloqueo/desbloqueo, modificación de datos personales, eliminación de usuario.

**Gates con doble condición (los únicos 3 gates del sistema):**

```php
// En AuthServiceProvider::boot()
Gate::define('restaurar-backups',         fn($u) => $u->can('restaurar-backups')         && $u->es_principal);
Gate::define('importar-snapshot-backup',  fn($u) => $u->can('importar-snapshot-backup')  && $u->es_principal);
Gate::define('ver-activity-log',          fn($u) => $u->can('ver-activity-log')           && $u->es_principal);
```

**Por qué está en el CORE:** `ProteccionAdminPrincipal` se usa en 7 componentes distintos de BhagamApps. Es un patrón transversal de seguridad institucional. (AUDIT-RBAC-001 §2.7, §2.9)

---

## 5. Gobernanza de Módulos

**Basado en ADR-002.**

### 5.1 Identidad del módulo

Un módulo se identifica mediante su **key**: identificador kebab-case único, estable, que no cambia entre versiones.

**Formato:** `^[a-z][a-z0-9-]*$`

**Ejemplos:** `inventario`, `admin-sistema`, `evaluacion-docente`, `prestamo-tabletas`

La key es el parámetro del middleware (`modulo.access:inventario`), el identificador en `module_role` y `module_user`, y la referencia en `requires[]` de otros módulos.

---

### 5.2 Descriptor: `module.json`

Cada módulo contiene un `module.json` en su directorio raíz. Es la **única fuente de verdad** de los metadatos del módulo.

**Estructura canónica:**

```json
{
  "key": "inventario",
  "name": "Inventario",
  "description": "Gestión de bienes muebles institucionales con flujos de aprobación",
  "version": "1.0.0",
  "min_core": "1.0.0",
  "requires": [],
  "conflicts": [],
  "author": "Equipo APPSisGOE",
  "icono": "fas fa-boxes",
  "color": "#28a745",
  "orden": 2,
  "ruta_entrada": "/inventario",
  "capacidades": [
    "ver-bienes",
    "crear-bienes",
    "editar-bienes",
    "eliminar-bienes"
  ]
}
```

**Campos obligatorios:** `key`, `name`, `version`, `min_core`

**Campo `capacidades`:** Informativo. Los permisos se crean desde `Capacidad` enum, no desde este campo. Sirve para documentar qué permisos introdujo el módulo.

---

### 5.3 Ciclo de vida

```
[Pendiente]   ──── instalar ────► [Instalando] ──── ok ────► [Inactivo]
[Inactivo]    ──── activar  ────►                              [Activo]
[Activo]      ──── desactivar ──►                              [Inactivo]
[cualquiera]  ──── error    ────►                              [Error]
[Inactivo]    ──── eliminar ────►                              [Desinstalado]
```

**Precondiciones de activación** (`ModuleActivateAction`):
1. `status = Inactivo`
2. CORE instalado `>= min_core` del módulo
3. Todos los módulos en `requires[]` están en estado `Activo`
4. Ningún módulo en `conflicts[]` está en estado `Activo`

**Precondiciones de desactivación** (`ModuleDeactivateAction`):
1. `status = Activo`
2. Ningún módulo Activo declara este módulo en su `requires[]`

---

### 5.4 Descubrimiento

```bash
php artisan modules:discover
```

**Comportamiento:**
1. Escanea `Modules/*/module.json`
2. Para cada manifiesto: si la key no existe en `modules` → crea con `status = Pendiente`
3. Si ya existe: NO actualiza (preserva configuración del admin)
4. Idempotente: ejecutar múltiples veces es seguro

**Principio:** Descubrimiento no es activación. Un módulo descubierto no es visible hasta ser instalado y activado.

---

### 5.5 Invalidación de caché de visibilidad

Cualquier cambio que afecte la visibilidad debe llamar:

```php
$this->moduleVisibilityService->invalidarCache();
// que internamente ejecuta:
cache()->increment('modules.cache_version');
```

Esto invalida los caches de todos los usuarios simultáneamente sin conocer sus IDs. Patrón heredado de BhagamApps (`apps.cache_version`). (AUDIT-APPS-002 §3.3)

**Cuándo llamar `invalidarCache()`:**
- Al activar o desactivar un módulo
- Al cambiar asignaciones en `module_role`
- Al cambiar asignaciones en `module_user`
- Al cambiar `activo` en `module_user`

---

## 6. Arquitectura de Autorización

**Basado en ADR-003.**

### 6.1 Las cuatro capas (obligatorias)

Toda request en APPSisGOE que accede a un módulo pasa por las cuatro capas en secuencia:

```
HTTP Request
    │
    ▼
┌────────────────────────────────────────────────────────────────┐
│ CAPA 1 — AUTENTICACIÓN (Fortify + CORE-1)                      │
│                                                                │
│ ¿Hay usuario autenticado?                                      │
│  NO  → redirect('/login')                                      │
│  SÍ  → ¿bloqueado?  → logout + error                          │
│        ¿email sin verificar? → redirect('/verify')             │
│        ¿forzar_cambio_password? → redirect('/cambiar')         │
│        → continúa                                              │
└────────────────────────────────────────────────────────────────┘
    │
    ▼
┌────────────────────────────────────────────────────────────────┐
│ CAPA 2 — ACCESO AL MÓDULO (CORE-3: ModuloAccessMiddleware)     │
│ Middleware: modulo.access:{key}                                │
│                                                                │
│ ModuleVisibilityService::visiblesPara($user)                   │
│ ├─ ¿el módulo está Activo?                                     │
│ ├─ ¿el rol del usuario tiene acceso? (module_role)            │
│ └─ ¿el usuario tiene acceso individual? (module_user.activo)  │
│    false → abort(403)                                          │
│    true  → continúa                                            │
└────────────────────────────────────────────────────────────────┘
    │
    ▼
┌────────────────────────────────────────────────────────────────┐
│ CAPA 3 — PERMISO GRANULAR (CORE-2: Spatie Permission)          │
│ Middleware: can:{capacidad}                                    │
│                                                                │
│ $user->can(Capacidad::VerBienes->value)                        │
│ Spatie: consulta model_has_roles + role_has_permissions        │
│ Memoizado en la instancia User (0 queries adicionales)         │
│    false → abort(403)                                          │
│    true  → continúa                                            │
└────────────────────────────────────────────────────────────────┘
    │
    ▼
┌────────────────────────────────────────────────────────────────┐
│ CAPA 4 — VERIFICACIÓN EN ACCIÓN (Actions / Livewire)           │
│ Defensa de profundidad — previene bypasses de capas 2-3        │
│                                                                │
│ En Controller/Action:                                          │
│   $this->authorize(Capacidad::EditarBienes->value);            │
│                                                                │
│ En Livewire:                                                   │
│   abort_if(!$user->can(Capacidad::EditarBienes->value), 403); │
│                                                                │
│ Operaciones críticas (gates dobles):                           │
│   abort_unless(Gate::allows('restaurar-backups'), 403);        │
└────────────────────────────────────────────────────────────────┘
    │
    ▼
ACCESO CONCEDIDO — Action procesa la operación
```

### 6.2 Mecanismo de verificación por contexto

| Contexto | Mecanismo correcto | Incorrecto |
|---------|-------------------|-----------|
| Middleware de ruta | `->middleware('can:' . Capacidad::VerBienes->value)` | String literal `'can:ver-bienes'` |
| Controller / Action | `$this->authorize(Capacidad::EditarBienes->value)` | Gate custom delegando a Spatie |
| Livewire | `abort_if(!$user->can(Capacidad::EliminarBienes->value), 403)` | `hasPermission()` custom |
| Blade | `@can(App\Auth\Capacidad::VerBienes->value)` | String literal `@can('ver-bienes')` |
| Ops críticas | `Gate::allows('restaurar-backups')` | Permiso sin verificar `es_principal` |

### 6.3 El enum `Capacidad`

Es la **única fuente de verdad** de todos los permisos del sistema. Agregar un permiso = agregar un caso al enum. El `PermissionSeeder` itera `Capacidad::cases()` y upserta en Spatie.

```php
// app/Auth/Capacidad.php
enum Capacidad: string
{
    // ── CORE: Gobernanza de módulos ──────────────────────────
    case ModulosVer           = 'modulos:ver';
    case ModulosInstalar      = 'modulos:instalar';
    case ModulosActivar       = 'modulos:activar';
    case ModulosDesactivar    = 'modulos:desactivar';
    case ModulosAsignarRol    = 'modulos:asignar_rol';
    case ModulosAsignarUser   = 'modulos:asignar_usuario';
    case ModulosDesinstalar   = 'modulos:desinstalar';

    // ── CORE: Operaciones críticas (requieren es_principal) ──
    case RestaurarBackups     = 'restaurar-backups';
    case ImportarSnapshot     = 'importar-snapshot-backup';
    case VerActivityLog       = 'ver-activity-log';

    // ── Módulo: Inventario — bienes ──────────────────────────
    case VerBienes            = 'ver-bienes';
    case CrearBienes          = 'crear-bienes';
    case EditarBienes         = 'editar-bienes';
    case EliminarBienes       = 'eliminar-bienes';
    // ... resto de capacidades
}
```

### 6.4 Gates — uso estrictamente restringido

Solo existen 3 gates en APPSisGOE. Se crean gates **únicamente** para operaciones que requieren la doble condición `can($permiso) && $user->es_principal`.

**Criterio para crear un gate:** La operación debe ser potencialmente destructiva, irreversible, y de impacto sistémico. Requiere aprobación arquitectónica.

---

## 7. Arquitectura de Datos

### 7.1 Identificadores primarios

**Regla:** Las tablas usan `BIGINT UNSIGNED AUTO_INCREMENT` como clave primaria en APPSisGOE v1. Los UUIDs se evaluarán en una ADR futura si se detecta necesidad de distribución o portabilidad entre sistemas.

### 7.2 Identificadores de dominio — Slugs

Los IDs de base de datos son detalles de implementación. Los **slugs** son los identificadores estables del dominio.

**Regla:** Toda entidad de catálogo que sea referenciada en código (no solo en FK) debe tener un campo `slug` VARCHAR UNIQUE.

```sql
-- Correcto: categorias con slug
ALTER TABLE categorias ADD COLUMN slug VARCHAR(60) UNIQUE NULL;

-- Uso correcto en código:
$mobiliario = Categoria::where('slug', 'mobiliario')->first();

-- PROHIBIDO: referencia por ID
$mobiliario = Categoria::find(1);  -- ← el ID puede cambiar
```

**Catálogos que requieren `slug`:** `categorias`, `estados`, `origenes`, `almacenamientos`, `mantenimientos`, `ubicaciones`.

### 7.3 SoftDeletes — entidades institucionales

**Regla:** Toda entidad institucional cuya eliminación requiera historial o auditoría usa `SoftDeletes`. La eliminación "física" de datos institucionales está prohibida en producción.

**Entidades que usan SoftDeletes:**

| Tabla | Razón |
|-------|-------|
| `bienes` | Bienes dados de baja son consultables histórica y legalmente |
| `users` | Un usuario eliminado puede aparecer en historiales previos |

**Entidades que NO usan SoftDeletes:** Tablas de catálogo simple (estados, ubicaciones, almacenamientos) donde el borrado es definitivo y controlado por administradores.

### 7.4 Campos de auditoría de tabla

Toda tabla del CORE y de módulos debe incluir:

```sql
`created_at` TIMESTAMP NULL,
`updated_at` TIMESTAMP NULL
```

Excepción: tablas de solo-inserción como `auditoria_passwords` (sin `updated_at`) y `activity_logs` (sin `updated_at`).

### 7.5 Tablas pivot — constraints

Toda tabla pivot M:N debe tener:

```sql
UNIQUE KEY uq_{tabla1}_{tabla2} ({campo1_id}, {campo2_id})
```

BhagamApps omitió este constraint en `permission_user`, generando 76 duplicados (AUDIT-RBAC-001 §6.1 P-001). APPSisGOE lo requiere en todas las pivots.

### 7.6 Prácticas prohibidas

```
PROHIBIDO-DATA-001
Usar IDs de base de datos como identificadores de dominio en código.

Ejemplo incorrecto:
  $mobiliario = Categoria::find(1);
  $grupos = [1, 20, 5]; // IDs hardcodeados

Corrección:
  $mobiliario = Categoria::where('slug', 'mobiliario')->first();

Origen: ARCH-ANALYSIS-001 ERROR-007, ADR-004 §5.3
──────────────────────────────────────────────────────────────

PROHIBIDO-DATA-002
Crear tablas sin UNIQUE constraint en relaciones M:N (tablas pivot).

Origen: AUDIT-RBAC-001 §6.1 P-001, ADR-003 §5.2
──────────────────────────────────────────────────────────────

PROHIBIDO-DATA-003
Eliminar físicamente registros de tablas de historial o auditoría.

Origen: PRINC-05, ADR-001 CORE-4
──────────────────────────────────────────────────────────────

PROHIBIDO-DATA-004
Mantener campos legacy con la misma semántica que un campo FK normalizado.

Ejemplo incorrecto: bienes.origen (varchar) + bienes.origen_id (FK)
Solo se mantiene: bienes.origen_id (FK a catálogo normalizado)

Origen: ARCH-ANALYSIS-001 §1.4, ADR-004 §5.3
──────────────────────────────────────────────────────────────

PROHIBIDO-DATA-005
Definir FK de módulos de negocio hacia la tabla `modules` (CORE-3).

Los módulos no tienen FK hacia el CORE de gobernanza.
La única dirección permitida es: module_role → modules y module_user → modules.

Origen: ADR-002 §5.3, ARCH-ANALYSIS-001 ERROR-001
```

---

## 8. Arquitectura de Eventos e Integración

### 8.1 Regla fundamental

**Los módulos de negocio no pueden depender directamente entre sí.** No pueden importar modelos, clases, servicios ni traits de otros módulos de negocio.

```php
// PROHIBIDO en Modules/Biblioteca/
use Modules\Inventario\Entities\Bien; // ← violación de ARCH-RULE-001

// CORRECTO: si Biblioteca necesita datos de bienes, se define una interfaz
interface BienesConsultaInterface
{
    public function obtenerBienPorSerie(string $serie): ?BienDTO;
}
// Y se registra en el CORE como servicio compartido mediante una ADR.
```

### 8.2 Comunicación entre módulos — canales permitidos

| Canal | Mecanismo | Cuándo usar |
|-------|-----------|------------|
| Notificaciones | `Notification::send()` → CORE-5 | Alertar a usuarios sobre eventos del módulo |
| Eventos Laravel | `Event::dispatch()` + Listeners en otros módulos | Notificar cambios de estado sin acoplamiento |
| Servicios del CORE | Inyección de dependencia desde CORE | ActivityLogger, ModuleVisibilityService |
| API de consulta | Interfaz PHP + ADR | Cuando un módulo necesita datos de otro (formalizar primero) |

### 8.3 Eventos de dominio

Los módulos deben disparar eventos de dominio para acciones significativas. Esto permite que el CORE y otros módulos (con Listeners) reaccionen sin acoplamiento.

**Convención de nombres:** `{Módulo}\Events\{Entidad}{Acción}`

```php
// Módulo Inventario dispara:
BienModificadoAprobado::dispatch($bien, $modificacion);
BienDadoDeBaja::dispatch($bien, $solicitud);
ResponsableAsignado::dispatch($bien, $user);

// CORE-4 tiene un Listener global:
ActivityLogListener::listen(BienModificadoAprobado::class, ...)
```

### 8.4 Servicios compartidos del CORE disponibles para módulos

| Servicio | Namespace | Uso |
|---------|-----------|-----|
| `ActivityLogger` | `App\Services\ActivityLogger` | Registrar acciones en activity_logs |
| `ModuleVisibilityService` | `App\Services\ModuleVisibilityService` | Consultar módulos visibles para un usuario |

Cualquier otro servicio compartido requiere una ADR antes de implementarse.

---

## 9. Arquitectura de Auditoría

### 9.1 Dos niveles de auditoría

**Nivel 1 — Auditoría transversal del CORE (CORE-4):**

Registra acciones de todos los módulos de forma centralizada. Cualquier Action que modifique estado persistente debe llamar a `ActivityLogger::log()`.

**Nivel 2 — Historiales de dominio (por módulo):**

Cada módulo mantiene sus propios historiales de auditoría específicos del dominio. Estos registros son inmutables y capturan el estado antes y después de cada cambio.

| Historial | Módulo | Propósito |
|-----------|--------|-----------|
| `historial_modificaciones_bienes` | Inventario | Workflow HMB: propuestas de cambio con estado |
| `historial_eliminaciones_bienes` | Inventario | Workflow HEB: solicitudes de baja |
| `historial_dependencias_bienes` | Inventario | Traslados entre dependencias |
| `historial_ubicaciones_bienes` | Inventario | Cambios de ubicación física |

### 9.2 Eventos auditables (obligatorios)

Los siguientes eventos siempre se registran, independientemente del módulo:

| Categoría | Eventos |
|-----------|---------|
| Autenticación | Login, logout, intento de login fallido, 2FA |
| Cuenta | Bloqueo, desbloqueo, cambio forzado de contraseña, restablecimiento |
| Módulos | Activación, desactivación, cambio de acceso por rol o usuario |
| Admin Principal | Todo intento (bloqueado o no) de modificar al `es_principal` |

### 9.3 Integridad de historiales

- Los registros de historial son de **solo inserción**. No existe UPDATE ni DELETE.
- El estado de una solicitud (HMB, HEB) cambia insertando el estado nuevo en el campo `estado`, no eliminando la fila.
- El responsable actual de un bien es el registro con `fecha_retiro IS NULL` — los anteriores nunca se eliminan.

### 9.4 Conservación

La política de conservación de datos de auditoría no está definida en APPSisGOE v1. Se recomienda una ADR específica en la Fase 4. Por defecto: conservación indefinida.

---

## 10. Arquitectura de Notificaciones

### 10.1 Responsabilidades del CORE

- Mantener la tabla `notifications` (estándar Laravel)
- Proveer en el layout principal: `NotificacionesDropdown` (lista de notificaciones con marcar-como-leída) y `NotificacionesIcono` (campana con contador de no leídas)
- El CORE no genera notificaciones propias — solo las muestra

### 10.2 Responsabilidades de los módulos

- Definir clases `Notification` específicas del dominio
- Determinar a quién notificar y cuándo
- Emitir via `Notification::send()` o `$usuario->notify()`

**Ejemplo — Módulo Inventario:**

```php
// Módulo Inventario dispara:
Notification::send(
    User::role(['Administrador', 'Rectoría'])->get(),
    new NotificacionHmb($modificacion)
);

// El CORE-5 muestra automáticamente la notificación en el dropdown global.
// El módulo Inventario no necesita saber nada del UI de notificaciones.
```

### 10.3 Eventos disparadores estándar

| Módulo | Evento | Destinatarios |
|--------|--------|--------------|
| Inventario | HMB propuesto (campo de bien) | Administrador, Rectoría |
| Inventario | HEB propuesto (baja de bien) | Administrador, Rectoría |
| Inventario | Mantenimiento vencido | Coordinación, Administrador |
| Módulos | Módulo activado / desactivado | Administrador |
| CORE-1 | Usuario bloqueado | Administrador Principal |

### 10.4 Regla

Ningún módulo puede crear su propia tabla de notificaciones ni su propio sistema de inbox. Todo usa la tabla `notifications` del CORE y los componentes `NotificacionesDropdown` / `NotificacionesIcono`.

---

## 11. Arquitectura de Versionado

**Basado en ADR-005.**

### 11.1 Semver para CORE y módulos

APPSisGOE usa **Semantic Versioning 2.0.0** independiente por componente:

```
CORE:    1.0.0  →  versión del núcleo compartido
Módulos: 1.0.0  →  versión independiente de cada módulo
```

### 11.2 Criterios de incremento

| Tipo | CORE | Módulo |
|------|------|--------|
| **MAJOR** | API pública rota (métodos de servicios, estructura de tablas CORE, Spatie major) | Tablas del módulo renombradas/eliminadas, `min_core` sube de MAJOR |
| **MINOR** | Nuevos servicios, nuevas tablas (backward compatible), nuevas capacidades en enum | Nueva funcionalidad, nuevas tablas, nuevas capacidades |
| **PATCH** | Corrección de bugs sin cambio de API | Corrección de bugs, textos, validaciones |

### 11.3 Contrato `min_core`

```json
{ "min_core": "1.0.0" }
```

El módulo funciona con cualquier versión del CORE `>= min_core` dentro del mismo MAJOR. `ModuleActivateAction` verifica esto antes de activar.

**Ejemplos:**
- `min_core: "1.0.0"` → acepta CORE 1.0.0, 1.1.0, 1.5.3 — rechaza CORE 2.0.0
- `min_core: "1.2.0"` → acepta CORE 1.2.0+ — rechaza CORE 1.1.9

### 11.4 Estrategia de rollback

| Tipo de actualización | Estrategia de rollback |
|-----------------------|------------------------|
| PATCH del CORE | Revertir deploy + `migrate:rollback` |
| MINOR del CORE | Revertir deploy + rollback migraciones |
| MAJOR del CORE | Restaurar snapshot completo (requiere `es_principal`) |
| PATCH/MINOR de módulo | Desactivar → revertir archivos → rollback migraciones → reactivar |
| MAJOR de módulo | Desactivar → revertir → rollback migraciones → validar → reactivar |

### 11.5 Versión del CORE en configuración

```php
// config/appsisgoe.php
return [
    'core_version' => '1.0.0',
];
```

Este valor se actualiza en cada deploy del CORE. Es la referencia que usa `ModuleActivateAction` para validar `min_core`.

---

## 12. Arquitectura de Despliegue

### 12.1 Instalación inicial del sistema

```bash
# 1. Clonar repositorio
git clone {repo} appsisgoe && cd appsisgoe

# 2. Dependencias
composer install && npm install && npm run build

# 3. Configuración
cp .env.example .env && php artisan key:generate

# 4. Base de datos — CORE
php artisan migrate              # Crea tablas del CORE
php artisan db:seed --class=CoreSeeder  # Roles, permisos (desde Capacidad enum), admin principal

# 5. Descubrir módulos
php artisan modules:discover     # Lee module.json de cada módulo

# 6. Instalar y activar módulos (por cada módulo deseado)
php artisan modules:install inventario
php artisan modules:install user
```

### 12.2 Instalación de un módulo

```bash
# Descubrir (si no fue descubierto antes)
php artisan modules:discover

# Instalar (migra tablas del módulo)
php artisan modules:install {key}

# Activar desde UI: Módulos > {módulo} > Activar
# O desde CLI:
php artisan modules:activate {key}

# Asignar acceso a roles desde UI: Módulos > {módulo} > Gestionar acceso
```

### 12.3 Actualización de un módulo (MINOR/PATCH)

```bash
# 1. Copiar nuevos archivos del módulo
# 2. Ejecutar migraciones del módulo
php artisan migrate

# 3. El módulo permanece Activo durante la actualización
# 4. modules:discover actualizará los metadatos del manifiesto
php artisan modules:discover
```

### 12.4 Actualización de un módulo (MAJOR)

```bash
# 1. Desactivar el módulo desde UI (o CLI)
php artisan modules:deactivate {key}

# 2. Copiar nuevos archivos
# 3. Ejecutar migraciones
php artisan migrate

# 4. Verificar compatibilidad
# ModuleActivateAction valida min_core automáticamente

# 5. Reactivar
php artisan modules:activate {key}
```

### 12.5 Desactivación de un módulo

```bash
# Desde UI: Módulos > {módulo} > Desactivar
# ModuleDeactivateAction verifica que no hay dependientes activos

# El módulo queda en status = Inactivo
# Las rutas retornan 403 (modulo.access las bloquea)
# Los datos del módulo se conservan en la base de datos
```

### 12.6 Recuperación ante fallos

**Rollback de módulo:**

```bash
php artisan modules:deactivate {key}
php artisan migrate:rollback --step=N  # N = migraciones del módulo
# Revertir archivos a versión anterior
php artisan modules:activate {key}
```

**Rollback de CORE MAJOR (Disaster Recovery):**

```bash
# Solo disponible para el Administrador Principal (es_principal)
# Desde módulo AdminSistema: Restaurar Snapshot
# Gate: restaurar-backups = can('restaurar-backups') && es_principal
```

---

## 13. Reglas Arquitectónicas Vinculantes

Las siguientes reglas son obligatorias. Cualquier violación detectada en code review es motivo de rechazo del PR.

---

```
ARCH-RULE-001 — Desacoplamiento de módulos
Los módulos de negocio no pueden depender directamente entre sí.
No se permite usar o importar ninguna clase de otro módulo de negocio
sin una ADR que formalice esa interfaz.
Origen: PRINC-03, ARCH-ANALYSIS-001 ERROR-001.

ARCH-RULE-002 — El CORE no contiene lógica de negocio
El CORE contiene únicamente infraestructura transversal.
Entidades de dominio (bienes, libros, tabletas) no pertenecen al CORE.
Origen: PRINC-01, ADR-001 §5.

ARCH-RULE-003 — Todo módulo debe tener module.json válido
Sin module.json con los campos obligatorios (key, name, version, min_core),
un módulo no puede ser descubierto, instalado ni activado.
Origen: ADR-002 §5.2.

ARCH-RULE-004 — Las 4 capas de autorización son obligatorias
Toda ruta de módulo debe pasar por las 4 capas:
  Capa 1: auth (Fortify)
  Capa 2: modulo.access:{key} (ModuloAccessMiddleware)
  Capa 3: can:{capacidad} si la ruta requiere permiso
  Capa 4: $this->authorize() en la Action que modifica estado
Omitir cualquier capa es una violación de seguridad.
Origen: ADR-003 §5.1.

ARCH-RULE-005 — Capacidad enum es la única fuente de verdad de permisos
Agregar un permiso = agregar un caso al enum Capacidad.
Ninguna migración, seeder ni fixture puede definir permisos fuera del enum.
Los tests deben verificar: count(Capacidad::cases()) === Permission::count()
Origen: ADR-003 §5.3, ARCH-ANALYSIS-001 ERROR-006.

ARCH-RULE-006 — No usar IDs de BD como identificadores de dominio
Los slugs identifican entidades de catálogo en código.
Los IDs de base de datos solo se usan en FK.
Origen: ARCH-ANALYSIS-001 ERROR-007, ADR-004 §5.3.

ARCH-RULE-007 — Ningún módulo implementa su propio sistema de autorización
Todo usa Spatie Permission via CORE-2.
No se permite implementar hasPermission() custom en ningún módulo.
Origen: ADR-001 restricciones, ADR-003 §5.2.

ARCH-RULE-008 — SoftDeletes obligatorio en entidades institucionales
Los bienes, usuarios y cualquier entidad institucional usan SoftDeletes.
DELETE FROM bienes es una operación prohibida en producción.
Origen: PRINC-05, ADR-004 §5.3.

ARCH-RULE-009 — Mutaciones multi-tabla requieren transacción
Toda Action que modifique más de una tabla usa DB::transaction().
Si falla una operación, ninguna parte de la transacción persiste.
Origen: ARCH-ANALYSIS-001 ACIERTO-003, ADR-004 §5.4.

ARCH-RULE-010 — modulo.access obligatorio en rutas de módulo
Toda ruta de un módulo de negocio debe incluir:
  ->middleware(['web', 'auth', 'verified', 'modulo.access:{key}'])
Si el módulo no existe en esta middleware, es accesible sin validación de visibilidad.
Origen: ADR-002 §5.5, ARCH-ANALYSIS-001 ERROR-001.

ARCH-RULE-011 — ProteccionAdminPrincipal obligatorio en gestión de usuarios
Toda Action o componente Livewire que modifique datos de un usuario
debe usar el trait ProteccionAdminPrincipal y llamar
verificarNoEsAdminPrincipal($targetUser, $accion) como primer paso.
Origen: ADR-003 §5.7, AUDIT-RBAC-001 §2.7.

ARCH-RULE-012 — Gates solo para operaciones con es_principal
No se crean Gates para permisos estándar.
$user->can(Capacidad::VerBienes->value) es suficiente.
Los Gates se definen únicamente para operaciones que requieren
la doble condición can($permiso) && $user->es_principal.
Solo existen 3 gates en el sistema. Crear un cuarto requiere ADR.
Origen: ADR-003 §5.8.

ARCH-RULE-013 — ActivityLogger vía CORE-4
Toda acción que modifique estado persistente llama a ActivityLogger::log().
No se crean sistemas de log alternativos dentro de módulos.
Origen: ADR-001 CORE-4, AUDIT-INV-002 §10.4.

ARCH-RULE-014 — Notificaciones vía CORE-5
Ningún módulo crea su propia tabla de notificaciones.
Todas las notificaciones usan Notification::send() y la tabla notifications.
Los componentes NotificacionesDropdown y NotificacionesIcono son del CORE.
Origen: ADR-001 CORE-5, AUDIT-INV-002 §7.5.

ARCH-RULE-015 — Límites de tamaño en componentes Livewire
Los componentes Livewire tienen límites máximos de líneas:
  Componente de listado/CRUD principal: ≤ 300 líneas
  Componente de edición inline: ≤ 150 líneas
  Componente de aprobación/rechazo: ≤ 200 líneas
  Dashboard: ≤ 400 líneas
La lógica de negocio se extrae a Actions.
Origen: ARCH-ANALYSIS-001 ERROR-004, ADR-004 §5.10.

ARCH-RULE-016 — Las tablas pivot requieren UNIQUE constraint
Toda relación M:N en el sistema tiene UNIQUE(campo1_id, campo2_id).
Sin este constraint, los duplicados son posibles y generan bugs silenciosos.
Origen: AUDIT-RBAC-001 §6.1 P-001, ARCH-ANALYSIS-001 ERROR-002.

ARCH-RULE-017 — min_core obligatorio en todo módulo
Todo módulo declara min_core en su module.json.
ModuleActivateAction verifica min_core antes de activar.
No puede activarse un módulo con min_core incompatible con el CORE instalado.
Origen: ADR-005 §5.4-5.5.

ARCH-RULE-018 — Los roles no tienen FK hacia módulos
La tabla roles no contiene foreign keys hacia modules (ni hacia apps).
Los vínculos van en la dirección opuesta: module_role.role_id → roles.id.
Origen: ARCH-ANALYSIS-001 ERROR-001, ADR-002 §5.3.

ARCH-RULE-019 — Los flujos de aprobación son bidireccionales completos
Todo workflow de tipo "propuesta → aprobación/rechazo" debe tener:
  a. UI o endpoint para proponer
  b. UI o endpoint para APROBAR
  c. UI o endpoint para RECHAZAR
Un flujo sin (b) o sin (c) es una brecha funcional que no puede lanzarse.
Origen: ARCH-ANALYSIS-001 ERROR-005, ADR-004 §5.5.

ARCH-RULE-020 — El enum Capacidad se organiza por módulo/namespace
Los casos del enum se agrupan por módulo mediante comentarios.
Los permisos del CORE usan el formato: namespace:accion (modulos:instalar).
Los permisos de módulos usan: verbo-entidad (ver-bienes, crear-bienes).
No se usan strings literales de slugs fuera del enum.
Origen: ADR-003 §5.3-5.4.

ARCH-RULE-021 — Exactamente 1 usuario es_principal en todo momento
El sistema garantiza en todo momento: COUNT(*) WHERE es_principal = true = 1.
Este invariante se verifica en la suite CI como test de integración.
La migración inicial asigna es_principal al primer Administrador.
Ningún componente puede desasignar es_principal sin asignarlo a otro usuario.
Origen: ADR-003 §5.6, ADR-001 CORE-6.

ARCH-RULE-022 — Los módulos nuevos arrancan deshabilitados
Un módulo instalado tiene status = Inactivo por defecto.
El acceso solo se habilita después de: (a) activar el módulo, y
(b) asignar el módulo a al menos un rol o usuario.
Principio: mínimo privilegio por defecto en todos los niveles.
Origen: ADR-002 §5.7, ARCH-ANALYSIS-001 ACIERTO-007.

ARCH-RULE-023 — Los cambios de visibilidad invalidan caché inmediatamente
Toda acción que modifique module_role, module_user, o el status de un módulo
debe llamar a ModuleVisibilityService::invalidarCache() al completarse.
Origen: ADR-002 §5.4, AUDIT-APPS-002 §3.3.

ARCH-RULE-024 — Las migraciones incluyen método down() funcional
Toda migración de módulo implementa el método down() correctamente.
El rollback de una migración debe ser posible y probado en CI.
Origen: ADR-005 §7 (riesgo de rollback no reversible).

ARCH-RULE-025 — El CHANGELOG se actualiza antes de cada release
El CHANGELOG de APPSisGOE sigue el formato Keep a Changelog.
Se actualiza antes de crear el tag de versión, no después.
Origen: ADR-005 §5.10.
```

---

## 14. Roadmap Arquitectónico Oficial

**Basado en ARCH-ANALYSIS-001 §8.**

Las fases están ordenadas por dependencias estructurales. Una fase no puede iniciarse hasta que las dependencias de la fase anterior estén verificadas.

### Fase 1 — CORE Foundation

**Objetivo:** El sistema puede autenticar usuarios, verificar permisos y proteger rutas. Sin módulos de negocio, con toda la infraestructura de autorización correcta.

**Entregables obligatorios:**

| Componente | Descripción |
|-----------|-------------|
| CORE-1: Users | Modelo User con `userID`, `bloqueado`, `forzar_cambio_password`, `es_principal` |
| CORE-2: Spatie + Capacidad | Spatie configurado, enum Capacidad conectado al PermissionSeeder |
| CORE-2: 7 roles IEE | RoleSeeder con los 7 roles y sus permisos base |
| CORE-6: ProteccionAdminPrincipal | Trait disponible en el CORE |
| CORE-4: auditoria_passwords | Tabla y registro de operaciones sobre cuentas |
| CORE-1: Fortify auth | Login, logout, 2FA, verificación de email |
| CORE-6: CheckForzarCambioPassword | Middleware único en bootstrap/app.php |
| CORE-4: ActivityLogger | Servicio y tabla activity_logs |
| CORE-6: Gates doble condición | 3 gates en AuthServiceProvider |
| CORE-6: es_principal invariante | Test de integración en suite CI |

**Criterio de salida Fase 1:**
- Un usuario puede hacer login; `bloqueado = true` impide el acceso; `forzar_cambio_password = true` redirige al cambio
- `$user->can(Capacidad::VerBienes->value)` retorna el valor correcto según el rol
- El Administrador Principal no puede ser modificado por otro administrador
- `auditoria_passwords` registra cada operación sobre cuentas
- Test CI: `COUNT users WHERE es_principal = true = 1` pasa

**Dependencias:** Ninguna.

---

### Fase 2 — Module Governance

**Objetivo:** El sistema puede gestionar módulos con ciclo de vida, controlar visibilidad por rol/usuario y mostrar el dashboard institucional personalizado.

**Entregables obligatorios:**

| Componente | Descripción |
|-----------|-------------|
| CORE-3: campos visuales en modules | `icono`, `color`, `orden`, `ruta_entrada`, `min_core` |
| CORE-3: module_role | Tabla con UNIQUE, FKs correctas |
| CORE-3: module_user | Tabla con `activo`, UNIQUE, FKs correctas |
| CORE-3: ModuleVisibilityService | `visiblesPara()` con caché versioned |
| CORE-3: ModuloAccessMiddleware | Alias `modulo.access:{key}` |
| CORE-3: Dashboard institucional | Muestra módulos según visibilidad del usuario |
| CORE-3: ModuleActivateAction | Valida min_core, requires, conflicts |
| CORE-3: ModuleDeactivateAction | Valida dependientes inversos |
| CORE-3: modules:discover artisan | Idempotente, lee module.json |
| CORE-5: NotificacionesDropdown | Componente en layout principal |
| CORE-5: NotificacionesIcono | Campana con contador en navbar |
| CORE-3: UI gestión de módulos | Toggle acceso por rol y por usuario |

**Criterio de salida Fase 2:**
- Al activar Inventario → aparece en el dashboard del Coordinador (sin reiniciar)
- Al desactivar Inventario → `modulo.access:inventario` retorna 403
- Cambio en `module_role` invalida caché de visibilidad de todos los usuarios
- `modules:discover` sin errores; idempotente en múltiples ejecuciones
- No se puede activar un módulo con `min_core` superior al CORE instalado

**Dependencias:** Fase 1 completa.

---

### Fase 3 — Módulo Inventario

**Objetivo:** El módulo Inventario es completamente funcional, con HMB y HEB completos (incluyendo la UI de aprobación de HEB), custodios, dashboard y catálogos.

**Entregables obligatorios:**

| Componente | Descripción |
|-----------|-------------|
| module.json Inventario | Con todos los campos obligatorios |
| 16 tablas del dominio | Sin campos legacy: sin `origen` varchar, sin `mantenimiento_id` directo |
| `categorias.slug` | Campo slug añadido a categorías |
| BienesIndex Livewire | ≤ 300 líneas; CRUD + filtros facetados |
| EditarCampoBien + HMB | Flujo HMB completo con transacción |
| HmbIndex | aprobarModificacion() + rechazarModificacion() |
| **HebIndex (NUEVO)** | **aprobarBaja() + rechazarBaja() — brecha crítica de BhagamApps** |
| bienes_responsables transaccional | Asignar nuevo cierra el anterior en una transacción |
| InventarioDashboard | Grupos institucionales por slug, no por ID |
| Catálogos maestros (7 CRUD) | Con slugs en categorias |
| Permisos en Capacidad enum | Los 30+ permisos del módulo declarados en el enum |

**Criterio de salida Fase 3:**
- Flujo HMB completo: propuesta → pendiente → aprobada (bien modificado) / rechazada
- Flujo HEB completo: solicitud → pendiente → aprobado (soft delete) / rechazado
- HEB tiene UI de aprobación funcional (no solo lista)
- `BienesIndex` ≤ 300 líneas
- Dashboard muestra KPIs con datos reales y grupos institucionales por slug
- `Bien::find($id)` no retorna bien eliminado; `Bien::onlyTrashed()` sí
- No existen campos `origen` (varchar) ni `mantenimiento_id` en `bienes`

**Dependencias:** Fase 1 + Fase 2 completas.

---

### Fase 4 — Expansión Modular

**Objetivo:** Los módulos adicionales se implementan uno a uno. El CORE es estable. Cada módulo solo declara sus metadatos, permisos y lógica de negocio.

**Módulos planificados (en orden de prioridad sugerida):**

| Módulo | Key | Prerequisito | Complejidad |
|--------|-----|-------------|------------|
| Administración del Sistema | `admin-sistema` | Fase 1 (gates es_principal) | Media |
| Gestión de Usuarios (UI) | `user` | Fase 1 + 2 | Media |
| Biblioteca | `biblioteca` | Fase 2 | Media |
| SINAI vs SIMAT | `sinai-vs-simat` | Fase 2 | Baja-Media |
| Planeador | `planeador` | Fase 2 | Media |
| Evaluación Docente | `evaluacion-docente` | Fase 3 (usa modelos de usuario y dependencias) | Alta |
| Préstamo de Tabletas | `prestamo-tabletas` | Fase 3 (referencia bienes como tabletas) | Media |
| Creador de Exámenes | `creador-examenes` | Fase 2 | Alta |

**Infraestructura de expansión (primera acción en Fase 4):**

| Entregable | Descripción |
|-----------|-------------|
| Plantilla base de módulo | Scaffolding con module.json, estructura de directorios, Action base |
| Contrato de módulo documentado | Qué debe declarar un módulo para ser compatible |
| Suite de tests de contrato | Verifica module.json, rutas con modulo.access, permisos en enum |

**Criterio de evaluación para extraer ApprovalWorkflow:**

El patrón HMB (propuesta → aprobación → aplicación) se extrae a servicio compartido cuando **dos módulos** requieran implementarlo. En Fase 4 se revisa si algún módulo nuevo necesita el patrón. Si sí, se crea una ADR de extracción.

**Dependencias:** Fases 1, 2 y 3 completas para la mayoría. Módulo AdminSistema puede iniciar en Fase 1.

---

## 15. Trazabilidad de Decisiones

La siguiente matriz demuestra la procedencia de cada sección de este documento.

| Sección ARCH-001 | ADR origen | Auditoría origen | Concepto heredado de BhagamApps |
|-----------------|-----------|-----------------|--------------------------------|
| §4.1 CORE-1 Users | ADR-001 §5.1 CORE-1 | AUDIT-RBAC-001 §2.1, §2.9 | es_principal, bloqueado, forzar_cambio_password |
| §4.2 CORE-2 Roles/Permisos | ADR-001 §5.1 CORE-2, ADR-003 §5.3-5.5 | AUDIT-RBAC-001 §4-5 | 7 roles IEE, slugs kebab-case |
| §4.3 CORE-3 Módulos | ADR-001 §5.1 CORE-3, ADR-002 §5.3-5.9 | AUDIT-APPS-002 §3, §6, §8 | OR lógico rol/usuario, caché versioned |
| §4.4 CORE-4 Auditoría | ADR-001 §5.1 CORE-4 | AUDIT-RBAC-001 §1.1, AUDIT-INV-002 §10.4 | ActivityLogger, auditoria_passwords |
| §4.5 CORE-5 Notificaciones | ADR-001 §5.1 CORE-5 | AUDIT-INV-002 §7.5, §10.4 | notifications table, dropdown |
| §4.6 CORE-6 Seguridad | ADR-001 §5.1 CORE-6, ADR-003 §5.6-5.7 | AUDIT-RBAC-001 §2.7, §2.9 | ProteccionAdminPrincipal, gates doble condición |
| §5 Gobernanza | ADR-002 §5.1-5.9 | AUDIT-APPS-002 §2-4, §7 | Descubrimiento, ciclo de vida |
| §6 Autorización | ADR-003 §5.1-5.10 | AUDIT-RBAC-001 §3, §6 | 4 capas, CheckAppAccess (→ ModuloAccess) |
| §7 Arquitectura de Datos | — | ARCH-ANALYSIS-001 §7, ERROR-002/006/007 | SoftDeletes en bienes |
| §8 Eventos e Integración | — | ARCH-ANALYSIS-001 §8, PRINC-03 | Patrón de desacoplamiento |
| §9 Auditoría | ADR-001 CORE-4 | AUDIT-INV-002 §4-5 (HMB/HEB historiales) | Historiales de dominio inmutables |
| §10 Notificaciones | ADR-001 CORE-5 | AUDIT-INV-002 §7.4-7.5 | NotificacionHmb, NotificacionHeb |
| §11 Versionado | ADR-005 §5.1-5.11 | AUDIT-APPS-002 §7 | manifiestos con hash (ya en APPSisGOE) |
| §12 Despliegue | ADR-005 §5.7-5.9, ADR-002 §5.6-5.7 | AUDIT-APPS-002 §4 | apps:sync → modules:discover |
| §13 ARCH-RULE-001 | ADR-001 §5.2, PRINC-03 | ARCH-ANALYSIS-001 ERROR-001 | Dependencia circular roles↔apps |
| §13 ARCH-RULE-004 | ADR-003 §5.1 | AUDIT-RBAC-001 §3 | CheckAppAccess, 4 capas |
| §13 ARCH-RULE-005 | ADR-003 §5.3 | AUDIT-RBAC-001 §6.3 P-005 | Permisos en múltiples fuentes |
| §13 ARCH-RULE-009 | — | AUDIT-INV-002 §4.2, ARCH-ANALYSIS-001 ACIERTO-003 | DB::transaction en HMB |
| §13 ARCH-RULE-019 | ADR-004 §5.5 | AUDIT-INV-002 §5.4, ARCH-ANALYSIS-001 ERROR-005 | Brecha HEB |
| §14 Roadmap | — | ARCH-ANALYSIS-001 §8 | Fases con dependencias |

---

## Apéndice A — Decisiones heredadas de BhagamApps: Clasificación final

### Adoptadas sin cambios (patrón correcto, misma implementación conceptual)

| Concepto | Fuente |
|---------|--------|
| 4 capas de autorización | AUDIT-RBAC-001 §3 |
| `es_principal` y Administrador Principal | AUDIT-RBAC-001 §2.9 |
| Trait `ProteccionAdminPrincipal` | AUDIT-RBAC-001 §2.7 |
| Tabla `auditoria_passwords` (solo INSERT) | AUDIT-RBAC-001 §1.1 |
| Flujo HMB con transacción atómica | AUDIT-INV-002 §4 |
| Flujo HEB con soft delete | AUDIT-INV-002 §5 |
| Modelo de cadena de custodia (`fecha_retiro IS NULL`) | AUDIT-INV-002 §3.4 |
| Caché versioned (`cache()->increment(cache_version)`) | AUDIT-APPS-002 §3.3 |
| OR lógico en visibilidad de módulos | AUDIT-APPS-002 §3.2 |
| Módulos nuevos deshabilitados por defecto | AUDIT-APPS-002 §2.2 |
| SoftDeletes para bienes institucionales | AUDIT-INV-002 §2 |
| 7 roles IEE | AUDIT-RBAC-001 §5 |
| Slugs kebab-case como identificadores de permiso | AUDIT-RBAC-001 §2.1 |

### Adoptadas con mejoras

| Concepto | Mejora |
|---------|--------|
| `CheckAppAccess` → `ModuloAccessMiddleware` | Extrae lógica a servicio inyectable (no fat model) |
| `CheckForzarCambioPassword` | Registro en un solo punto (no dos) |
| Dashboard de Inventario | Grupos por slug de categoría, no por ID |
| HEB | Se agrega UI de aprobación completa (brecha crítica corregida) |
| Cadena de custodia | La asignación cierra el registro anterior en transacción atómica |
| `NotificacionesDropdown` | Movido al CORE (no dentro de Inventario) |

### Reemplazadas

| Componente BhagamApps | Reemplazo APPSisGOE |
|----------------------|---------------------|
| `User::hasPermission()` custom (2 queries) | `$user->can()` de Spatie (memoizado) |
| `permission_role` y `permission_user` manuales | Tablas de Spatie |
| 60 gates delegando a hasPermission | Eliminados — usar `$user->can()` directamente |
| Permisos en CSV / migraciones inline | `Capacidad` enum → `PermissionSeeder` |
| Módulo `Apps` como módulo separado | Absorbido en CORE-3 |
| `AppSeeder` con datos de módulos | `module.json` por módulo |

### Descartadas

| Componente BhagamApps | Razón |
|----------------------|-------|
| `roles.app_id` FK | Dependencia circular. Los roles no tienen FK hacia módulos. |
| `Role::hasPermission($nombre)` | Inconsistente con slug. Eliminado. |
| `apps.user_id` sin FK | Campo legacy sin uso funcional. |
| `bienes.origen` varchar | Normalizado a `origen_id` FK. El campo varchar no existe. |
| `bienes.mantenimiento_id` | Redundante con `mantenimientos_programados`. Eliminado. |
| IDs hardcodeados en `cargarGruposInstitucionales()` | Reemplazado por `categorias.slug`. |
| Seeders CSV multi-archivo de permisos | Reemplazado por `Capacidad` enum. |
| `CheckForzarCambioPassword` en dos registros | Un único registro. |
| `ActaPrinter` como clase estática | Pendiente: convertir a Service inyectable o Blade component. |

---

*Fin del documento ARCH-001 — Arquitectura Ejecutable de APPSisGOE v1.0.0*
*Vigente desde: 2026-06-14*
*Próxima revisión: al completar Fase 2 del roadmap o ante cualquier propuesta de cambio MAJOR.*
