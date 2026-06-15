# IMPLEMENTATION-READINESS-REPORT

**Documento:** IMPL-CORE-001 — Reporte de Preparación del Repositorio
**Versión:** 1.0.0
**Estado:** Aprobado — Vigente
**Fecha:** 2026-06-14
**Autor:** Ingeniero Principal de Implementación
**Base:** ARCH-001 · ADR-001 · ADR-002 · ADR-003 · ADR-004 · DAT-INV-001

---

## Tabla de Contenidos

1. [Resumen Ejecutivo](#1-resumen-ejecutivo)
2. [Estado Tecnológico](#2-estado-tecnológico)
3. [Compatibilidad con ARCH-001](#3-compatibilidad-con-arch-001)
4. [Riesgos Técnicos](#4-riesgos-técnicos)
5. [Bloqueantes](#5-bloqueantes)
6. [Evaluación por Componente CORE](#6-evaluación-por-componente-core)
7. [Veredicto Final](#7-veredicto-final)

---

## 1. Resumen Ejecutivo

El repositorio inspeccionado es **BhagamAppsModular**, el sistema predecesor de APPSisGOE. El repositorio contiene código funcional en producción y ha sido la base sobre la cual se construyeron las auditorías, ADRs y documentos de dominio que definen APPSisGOE.

APPSisGOE **no existe como repositorio separado**. La implementación se realizará sobre este mismo repositorio, transformando progresivamente su arquitectura de BhagamApps a la arquitectura APPSisGOE definida en ARCH-001.

**Conclusión:** El repositorio puede iniciar la implementación del CORE Foundation **con condiciones**. Existen 5 bloqueantes que deben resolverse antes o durante la primera fase. Ninguno es imposible de resolver — todos son de naturaleza técnica y ejecutable.

---

## 2. Estado Tecnológico

### 2.1 Stack actual

| Componente | Versión detectada | Versión requerida (ARCH-001) | Estado |
|-----------|-------------------|------------------------------|--------|
| PHP | 8.4.14 | 8.4 | ✓ Cumple |
| Laravel | 11.44.7 | 13 | ⚠️ Brecha de versión |
| Livewire | ^3.0 (instalado) | 3 | ✓ Cumple |
| Alpine.js | ^3.14.9 | Alpine.js | ✓ Cumple |
| Tailwind CSS | ^3.4.0 | Tailwind | ✓ Cumple |
| nwidart/laravel-modules | ^12.0 | nwidart/laravel-modules | ✓ Cumple |
| laravel/jetstream | ^5.3 | Jetstream | ✓ Cumple |
| laravel/fortify | vía Jetstream | Fortify | ✓ Cumple |
| spatie/laravel-permission | **NO INSTALADO** | Requerido | 🔴 Bloqueante |
| laravel/sanctum | ^4.0 | — | ✓ Disponible |

### 2.2 Módulos existentes

| Módulo | Estado | Observación |
|--------|--------|-------------|
| User | Activo (producción) | RBAC custom, no Spatie. Imports ilegales desde Inventario. |
| Apps | Activo (producción) | Concepto BhagamApps: tabla `apps`, no tabla `modules`. No es CORE-3. |
| Inventario | Activo (producción) | Módulo de negocio completo. No tocar durante CORE Foundation. |
| ActivityLog | Activo (producción) | Parcialmente CORE-4. Necesita moverse al CORE formal. |
| AdminSistema | Activo (producción) | Backups e infraestructura. Fuera del alcance del CORE Foundation. |
| CrudGenerator | Activo | Herramienta de desarrollo interna. Sin impacto en CORE. |

### 2.3 Migraciones existentes relevantes

```
Base de datos (database/migrations/):
  ✓ users table (con bloqueado, forzar_cambio_password, es_principal, nombres, apellidos, userID)
  ✓ password_reset_tokens table
  ✓ sessions table
  ✓ grupos table
  ⚠️ auditoria_passwords (en User module migrations)

Módulo User (Modules/User/Database/Migrations/):
  ⚠️ roles table (custom — tiene app_id como FK circular hacia apps)
  ⚠️ permissions table (custom — sin guard_name ni formato Spatie)
  ⚠️ permission_role pivot (custom)
  ⚠️ permission_user pivot (custom)
  ✓ auditoria_passwords table

Módulo ActivityLog (Modules/ActivityLog/Database/Migrations/):
  ✓ activity_logs table (estructura correcta, compatible con CORE-4)

Módulo Inventario (Modules/Inventario/Database/Migrations/):
  ✓ notifications table (formato Laravel estándar: UUID, morphs, data JSON)
```

### 2.4 Artefactos relevantes ya implementados

| Artefacto | Ruta | Estado |
|-----------|------|--------|
| `Capacidad` enum | `app/Auth/Capacidad.php` | ⚠️ Existe, referencia Spatie no instalado |
| `SincronizarRolesYPermisosCoreAction` | `app/Actions/Core/` | ⚠️ Existe, no puede ejecutar (RolInstitucional missing) |
| `ProteccionAdminPrincipal` trait | `Modules/User/Traits/` | ✓ Funcional |
| `CheckForzarCambioPassword` middleware | `Modules/User/Http/Middleware/` | ✓ Funcional |
| `ActivityLogger` service | `Modules/ActivityLog/Services/` | ✓ Funcional |
| `auditoria_passwords` migration | `Modules/User/Database/Migrations/` | ✓ Migrado |
| `activity_logs` migration | `Modules/ActivityLog/Database/Migrations/` | ✓ Migrado |
| `notifications` migration | `Modules/Inventario/Database/Migrations/` | ✓ Migrado |
| `CheckAppAccess` middleware | `app/Http/Middleware/` | ⚠️ Parcial (usa App::visiblesPara()) |
| `CheckPermission` middleware | `app/Http/Middleware/` | ⚠️ Parcial (usa user->hasPermission() custom) |
| `NotificacionesDropdown` Livewire | `Modules/Inventario/Livewire/` | ⚠️ En módulo incorrecto |

---

## 3. Compatibilidad con ARCH-001

### 3.1 Ya existe y cumple ARCH-001

| Elemento | Verificación |
|----------|-------------|
| PHP 8.4 | Instalado en el servidor (8.4.14) |
| Livewire 3 | Instalado y funcional |
| Fortify + Jetstream para autenticación | Instalado y funcional |
| nwidart/laravel-modules 12 | Instalado y configurado |
| Alpine.js + Tailwind CSS | Instalado y compilado |
| `es_principal` en users | Migrado y funcional |
| `bloqueado` en users | Migrado y funcional |
| `forzar_cambio_password` en users | Migrado y funcional |
| `auditoria_passwords` table | Migrada y funcional |
| `activity_logs` table | Migrada con estructura correcta |
| `notifications` table | Migrada (formato Laravel estándar) |
| `CheckForzarCambioPassword` middleware | Implementado y registrado en bootstrap/app.php |
| `ProteccionAdminPrincipal` trait | Implementado y en uso |
| `ActivityLogger` service | Implementado y en uso (en módulo incorrecto) |
| `Capacidad` enum | Existe con naming correcto (requiere ajuste) |
| Clean Architecture: `app/Actions/` | Patrón iniciado |
| Autoload `Modules/` namespace | Configurado en composer.json |

### 3.2 Debe adaptarse (existe pero no cumple ARCH-001)

| Elemento | Estado actual | Lo que se necesita |
|----------|---------------|-------------------|
| RBAC system | Custom (Role/Permission models propios) | Migrar a Spatie Permission |
| `roles` table | Custom, tiene `app_id` FK (circular) | Reemplazar por tablas Spatie |
| `permissions` table | Custom, sin `guard_name` | Reemplazar por tablas Spatie |
| `Gate` definitions | 60+ gates delegando a hasPermission | Reducir a 3 gates críticos (ARCH-001 §6) |
| `CheckPermission` middleware | Delega a `user->hasPermission()` custom | Adaptar a `can:` middleware de Laravel/Spatie |
| `CheckAppAccess` middleware | Usa `App::visiblesPara()` (tabla `apps`) | Reemplazar por `modulo.access:key` con `modules` table |
| `module.json` format | Formato básico nwidart (sin version, min_core, status, hash) | Extender al formato APPSisGOE |
| `modules_statuses.json` | 2 estados (true/false) | Migrar a 6-state lifecycle en base de datos |
| `ActivityLogger` | En `Modules/ActivityLog/Services/` | Registrar como servicio CORE accesible globalmente |
| `NotificacionesDropdown` | En `Modules/Inventario/Livewire/` | Mover a CORE-5 |
| `ProteccionAdminPrincipal` | En `Modules/User/Traits/` | Mantener o mover a `app/Traits/` |
| `isAdminPrincipal()` en User | Usa campo `es_principal` ✓ | Garantizar coherencia con Spatie |
| `User` model | Importa `Modules\Inventario\Entities\*` (violación) | Eliminar imports ilegales |

### 3.3 Debe construirse desde cero

| Elemento | ARCH-001 ref | Descripción |
|----------|-------------|-------------|
| `RolInstitucional` enum | ADR-003 | Define los 7 roles institucionales fijos. No existe en el repo. |
| `modules` table | ADR-002 §5.1 | Tabla APPSisGOE para el ciclo de vida de módulos (6 estados). No existe — hay `apps` table que es distinta. |
| `module_role` pivot | ADR-002 §5.3 | Asignación de roles a módulos. No existe. |
| `module_user` pivot | ADR-002 §5.3 | Asignación individual de módulos a usuarios. No existe. |
| `ModuleVisibilityService` | ARCH-001 §3.3 | Servicio inyectable de visibilidad de módulos. No existe (solo método estático en `App::visiblesPara()`). |
| 6-state module lifecycle | ADR-002 | Pendiente/Instalando/Activo/Inactivo/Error/Desinstalando. No implementado. |
| `modulo.access:key` middleware | ARCH-001 §3.2 | Middleware formal que usa la `modules` table. `CheckAppAccess` usa la `apps` table. |
| Gate::before para AdminPrincipal | ARCH-001 §6 | Bypass de autorización para el admin principal vía Gate::before. No implementado. |
| `module.json` versión APPSisGOE | ADR-002 §5.2 | Formato con version, min_core, hash, status declarativo. |
| Install/Activate/Deactivate Actions | ADR-002 §5.6 | Las 6 Actions del ciclo de vida de módulos. No existen. |
| Versioned cache para módulos | ARCH-001 §3.3 | Cache con `cache()->increment()` para invalidación por versión. Parcialmente en `App::visiblesPara()` pero sobre tabla incorrecta. |
| `Grupo` migration formal | — | Existe tabla y modelo pero no está integrada al CORE. |

---

## 4. Riesgos Técnicos

### Críticos

| ID | Riesgo | Impacto | Mitigación |
|----|--------|---------|------------|
| RISK-001 | Migración RBAC custom → Spatie puede romper producción | Muy Alto | Implementar en rama separada; migración de datos de roles/permisos existentes con script validado |
| RISK-002 | `roles` table tiene FK hacia `apps` (circular). Eliminación de `app_id` puede romper seeders y datos existentes | Alto | Migration de limpieza antes de cambiar estructura |
| RISK-003 | `User` model importa desde `Modules\Inventario\*`. Romper estos imports puede afectar funcionalidad del módulo Inventario activo | Alto | Crear interfaces de consulta antes de eliminar imports; ejecutar tests de regresión |

### Altos

| ID | Riesgo | Impacto | Mitigación |
|----|--------|---------|------------|
| RISK-004 | Laravel 11 → 13 (diferencia de 2 versiones major) puede requerir cambios en API de framework | Alto | Evaluar si la implementación del CORE puede hacerse en Laravel 11 con compatiblidad futura, o si el upgrade es condición previa |
| RISK-005 | `apps` table tiene registros en producción. La transición a `modules` table requiere migración de datos | Alto | Script de migración de datos (`apps` → `modules`) con rollback documentado |
| RISK-006 | 60+ Gate definitions en `AuthServiceProvider`. Reducir a 3 puede dejar permisos sin cubrir | Alto | Auditar qué gates se usan actualmente antes de eliminar |

### Medios

| ID | Riesgo | Impacto | Mitigación |
|----|--------|---------|------------|
| RISK-007 | `NotificacionesDropdown` en `Modules/Inventario` mezcla notificaciones de negocio con infra CORE | Medio | Extraer a CORE-5 sin romper interfaz de Inventario |
| RISK-008 | `ActivityLogger` referenciado por múltiples módulos. Si se mueve al CORE, todos los imports deben actualizarse | Medio | Mantener alias de compatibilidad durante la transición |
| RISK-009 | `modules_statuses.json` de nwidart actualmente controla qué módulos cargan. La transición a BD puede hacer que módulos no carguen | Medio | Mantener nwidart mientras se construye el sistema APPSisGOE; coexistencia temporal |

### Bajos

| ID | Riesgo | Impacto | Mitigación |
|----|--------|---------|------------|
| RISK-010 | Tests de Feature existentes (Inventario, User) pueden fallar con cambios de RBAC | Bajo | Ejecutar suite antes y después de cada cambio |
| RISK-011 | `Capacidad` enum referencia Spatie antes de que esté instalado | Bajo | Spatie es el primer paso; enum ya tiene el naming correcto |
| RISK-012 | `Grupo` model/migration no tiene relación clara con ningún dominio definido | Bajo | Aclarar destino (¿CORE? ¿módulo futuro?) antes de continuar |

---

## 5. Bloqueantes

### BLOCKER-001 — Crítico — Spatie Permission no instalado

```
Descripción: spatie/laravel-permission no está en composer.json ni instalado.
Impacto: TODA la arquitectura CORE-2 (Autorización) depende de Spatie.
SincronizarRolesYPermisosCoreAction no puede ejecutarse.
Resolución: composer require spatie/laravel-permission
Estimación: 30 minutos + validación
```

### BLOCKER-002 — Crítico — RolInstitucional enum no existe

```
Descripción: app/Auth/RolInstitucional.php no existe.
SincronizarRolesYPermisosCoreAction lo importa y crashearía si se ejecuta.
Impacto: El sembrado de roles institucionales fijos no puede ejecutarse.
Resolución: Crear enum RolInstitucional con los 7 roles del dominio.
Estimación: 1 hora
```

### BLOCKER-003 — Alto — Laravel 11 vs. Laravel 13 (ARCH-001)

```
Descripción: ARCH-001 especifica Laravel 13. El sistema está en Laravel 11.44.7.
PHP 8.4 ya está en el servidor.
Impacto: Decisión de arquitectura: ¿implementar en 11 con upgrade planificado,
o hacer el upgrade antes de empezar?
Evaluación: Laravel 11 es LTS hasta 2026-08. Laravel 13 no es LTS.
Todos los patrones definidos en ARCH-001 (Livewire 3, Spatie, nwidart) son
compatibles con Laravel 11. La brecha no es técnica sino de versión de framework.
Resolución recomendada: Implementar CORE Foundation en Laravel 11.44.7.
Registrar el upgrade a Laravel 13 como tarea futura en una ADR específica.
Estimación upgrade (si se decide ahora): 4-8 horas de pruebas
```

### BLOCKER-004 — Alto — User model viola PRINC-03 (Desacoplamiento)

```
Descripción: Modules/User/Entities/User.php importa directamente:
  - Modules\Inventario\Entities\Bien
  - Modules\Inventario\Entities\Dependencia
  - Modules\Inventario\Entities\BienResponsable
Impacto: El módulo CORE User está acoplado al módulo de negocio Inventario.
ARCH-001 PRINC-03 prohíbe explícitamente estas dependencias.
Resolución: Eliminar los métodos que dependen de Inventario del User model.
Si se necesitan, crear contratos de API o eliminar la dependencia.
Estimación: 2-3 horas (requiere auditar qué usa esos métodos primero)
```

### BLOCKER-005 — Alto — No existe tabla `modules` (CORE-3)

```
Descripción: La tabla `apps` de BhagamApps no es equivalente a la tabla `modules`
de APPSisGOE. El ciclo de vida de 6 estados no existe.
Impacto: CORE-3 completo debe construirse desde cero. La transición de `apps` a
`modules` requiere migración de datos para no romper el sistema en producción.
Resolución: Construir tabla `modules` nueva; migrar datos de `apps` con script;
deprecar `apps` en una segunda fase.
Estimación: 6-8 horas de implementación + migración de datos
```

### Respuesta a la pregunta principal

```
¿Existe algún bloqueante técnico para implementar el Core Foundation?

Sí. Existen 5 bloqueantes. Ninguno es irresoluble.

Los bloqueantes BLOCKER-001 y BLOCKER-002 son condiciones previas absolutas
(menos de 2 horas de resolución combinada).

BLOCKER-003 requiere una decisión de arquitectura (Laravel 11 vs. 13).
La recomendación técnica es comenzar en Laravel 11.

BLOCKER-004 y BLOCKER-005 deben resolverse durante la implementación del CORE,
no necesariamente antes de comenzar.

Recomendación: Iniciar IMPL-CORE-002 con secuencia:
1. Instalar Spatie (BLOCKER-001)
2. Crear RolInstitucional enum (BLOCKER-002)
3. Confirmar decisión Laravel 11 vs. 13 (BLOCKER-003)
4. Luego ejecutar el plan de implementación en el orden definido en CORE-FOUNDATION-IMPLEMENTATION-PLAN.md
```

---

## 6. Evaluación por Componente CORE

| Componente | Completitud actual | Trabajo restante |
|-----------|-------------------|-----------------|
| CORE-1: Users | 65% | Spatie migration, eliminar imports ilegales, formalizar estructura |
| CORE-2: Authorization | 30% | Instalar Spatie, crear RolInstitucional, simplificar Gates, rebuild middleware |
| CORE-3: Modules | 10% | Tabla modules, 6-state lifecycle, ModuleVisibilityService, module.json format |
| CORE-4: Audit | 80% | Registrar ActivityLogger como CORE service formal; leve reorganización |
| CORE-5: Notifications | 40% | Mover NotificacionesDropdown a CORE, formalizar como servicio CORE |
| CORE-6: Security | 70% | Consolidar en CORE, formalizar Gate::before para AdminPrincipal |

---

## 7. Veredicto Final

```
Estado: CONDICIONALMENTE LISTO

El repositorio tiene la base tecnológica correcta (PHP 8.4, Livewire 3,
nwidart modules) y artefactos parciales valiosos (Capacidad enum,
ActivityLogger, auditoria_passwords, ProteccionAdminPrincipal).

La implementación del CORE Foundation puede comenzar inmediatamente
después de resolver los 2 bloqueantes críticos:
  1. composer require spatie/laravel-permission
  2. Crear RolInstitucional enum

El resto de los bloqueantes se resuelven durante la implementación
siguiendo el orden definido en CORE-FOUNDATION-IMPLEMENTATION-PLAN.md.
```

---

*Fin del documento IMPLEMENTATION-READINESS-REPORT v1.0.0*
*Generado: 2026-06-14*
*Siguiente documento: CORE-FOUNDATION-IMPLEMENTATION-PLAN.md*
