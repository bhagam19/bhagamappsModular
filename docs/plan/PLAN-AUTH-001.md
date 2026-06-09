# PLAN-AUTH-001 — Arquitectura Definitiva de Autorización: Análisis y Diseño

**Fecha:** 2026-06-08
**Estado:** Análisis — pendiente de aprobación para derivar implementaciones
**Relacionado con:** ADR-AUTHORIZATION-001, ADR-008, AUDIT-APPS-002, IMPL-013

---

## Objetivo

Analizar el estado actual de los tres sistemas de autorización paralelos en BhagamAppsModular
y definir si el estándar `ver-{slug}` debe convertirse en el permiso mínimo obligatorio de
entrada para cada módulo. El resultado es una decisión arquitectónica documentada en
ADR-AUTHORIZATION-002, sin implementación todavía.

---

## Estado actual de los tres sistemas

### Sistema 1 — Middleware `CheckPermission` (`permission:slug`)

**Implementación:** `app/Http/Middleware/CheckPermission.php`
**Fuente de verdad:** `permission_role` + `permission_user` (via `User::hasPermission($slug)`)

**Usos actuales:**

| Módulo   | Ruta                          | Permiso requerido                          | Capa |
|----------|-------------------------------|-------------------------------------------|------|
| Inventario | `/inventario/bienes`        | `permission:ver-bienes`                   | 3    |
| Inventario | `/inventario/actas`         | `permission:ver-actas-de-entrega`         | 3    |
| Inventario | `/inventario/hmb`           | `permission:gestionar-historial-modificaciones-bienes` | 3 |
| User     | `/users/users`                | `permission:ver-usuarios`                 | 3    |
| Apps     | `/apps/admin`                 | `permission:ver-apps`                     | 3    |

**Observación crítica:** Los permisos `ver-bienes` y `ver-actas-de-entrega` son permisos
de acceso a *secciones* dentro de Inventario, no al módulo Inventario en su totalidad.
Son correctamente Capa 3. El middleware en esas rutas opera como Capa 3, no como Capa 2.

---

### Sistema 2 — Gates en `AuthServiceProvider`

**Implementación:** `app/Providers/AuthServiceProvider.php`

**Dos patrones mezclados:**

**Patrón A — nombres de rol hardcoded (problema):**
```php
Gate::define('usuarios.user', fn($user) =>
    in_array($user->role->nombre, ['Administrador', 'Rector', 'Coordinador'])
);
Gate::define('admin.grupos', ...);  // misma lógica
Gate::define('admin.evaldoc', ...); // misma lógica
Gate::define('admin.biblioteca', ...); // misma lógica
```
Acoplamiento directo a nombres de roles en código. Un renombrado de rol rompe la autorización.

**Patrón B — delegado a `hasPermission()` (correcto):**
```php
Gate::define('ver-bienes', fn($user) => $user->hasPermission('ver-bienes'));
Gate::define('ver-apps',   fn($user) => $user->hasPermission('ver-apps'));
// ... otros ver-*, administrar-apps, aprobar-cambios-bienes
```
Estos Gates son un puente hacia Sistema 1. Su existencia es redundante cuando ya existe
`CheckPermission` middleware — pero son necesarios para el `can:` en AdminLTE.

**Usos en `config/adminlte.php`:**

| Ítem del menú              | Gate usado         | Patrón |
|----------------------------|--------------------|--------|
| Gestión de Accesos         | `usuarios.user`    | A (hardcoded) |
| Aplicaciones               | `ver-apps`         | B (hasPermission) |
| Bienes (submenú)           | `ver-bienes`       | B (hasPermission) |
| Actas de Entrega (submenú) | `ver-actas-de-entrega` | B (hasPermission) |
| Grupos                     | `admin.grupos`     | A (hardcoded) |
| Evaluación Docente         | `admin.evaldoc`    | A (hardcoded) |

---

### Sistema 3 — `App::visiblesPara()` + `app.access:{slug}`

**Implementación:**
- `Modules/Apps/Entities/App.php::visiblesPara()`
- `app/Http/Middleware/CheckAppAccess.php`
- Registrado en `app/Http/Kernel.php` como `app.access`

**Usos actuales:**

| Módulo     | Middleware           | Estado |
|------------|----------------------|--------|
| Inventario | `app.access:inventario` | ✅ Implementado |
| User       | `app.access:user`    | ✅ Implementado |
| Apps       | ❌ Sin `app.access`  | ⚠️ Inconsistencia |

**Observación:** El módulo Apps no tiene `app.access:apps` en sus rutas. La ruta
`/apps/admin` usa `permission:ver-apps` como mecanismo de acceso — que es un sistema
distinto. Esta inconsistencia es la fuente de ambigüedad que este plan resuelve.

---

## La pregunta central: ¿`ver-{slug}` como permiso mínimo obligatorio?

La propuesta es: cada módulo registrado en `apps` debe tener un permiso de entrada con
slug canónico `ver-{slug}`, y este permiso debe ser el mecanismo de Capa 2 (acceso a módulo).

Ejemplos de la propuesta:
- App slug `inventario` → permiso `ver-inventario` en routes middleware
- App slug `user` → permiso `ver-user` en routes middleware
- App slug `apps` → permiso `ver-apps` *(ya existe — caso actual)*
- App slug `academico` → permiso `ver-academico` en routes middleware

### Análisis de impacto de adoptar `ver-{slug}` como Capa 2

**Consecuencia 1 — Duplicación de gestión:**
El administrador debería mantener en sincronía:
- `app_role`: roles que tienen acceso al módulo (para el dashboard y menú)
- `permission_role`: permisos `ver-{slug}` por rol (para poder entrar a la URL)

Si un rol tiene `inventario` en `app_role` pero no tiene `ver-inventario` en `permission_role`,
el usuario ve el ícono en el dashboard pero recibe 403 al hacer clic. Este es exactamente
el escenario de riesgo que ADR-008 identificó como brecha arquitectónica.

**Consecuencia 2 — Contradicción con ADR-008 DD-003:**
ADR-008 (estado: Aceptado) establece explícitamente:

> "El enforcement de acceso a módulos es responsabilidad de Apps. El mecanismo de enforcement
> deberá basarse en `App::visiblesPara($user)`, no en permisos individuales
> (`permission:ver-modulo`). Los permisos individuales quedan reservados para la Capa 3."

Adoptar `ver-{slug}` como Capa 2 contradice directamente este ADR aprobado.

**Consecuencia 3 — Ruptura de la fuente única de verdad:**
`App::visiblesPara()` fue adoptado como fuente única de verdad para visibilidad y acceso.
Introducir `ver-{slug}` como Capa 2 crea una fuente secundaria con semántica solapada.

**Consecuencia 4 — Reducción de funcionalidad del panel de Apps:**
El panel `/apps/admin` pierde valor si la asignación de roles a apps (`app_role`) no
implica automáticamente acceso real al módulo. El administrador tendría que gestionar
dos tablas para el mismo resultado funcional.

**Consecuencia 5 — Carga de migración:**
Los módulos Inventario y User ya usan `app.access:{slug}` y funcionan correctamente.
Migrar estos módulos a `permission:ver-{slug}` implica:
- Crear permisos `ver-inventario`, `ver-user` en BD
- Asignar estos permisos a cada rol con acceso
- Cambiar el middleware en routes
- Sincronizar `app_role` y `permission_role` manualmente o via código

Sin beneficio funcional observable para el usuario.

---

## Análisis del caso `ver-apps` existente

El permiso `ver-apps` (id: 29) existe en la BD y se usa en dos contextos:
1. **Menú sidebar** — `can:ver-apps` en `config/adminlte.php`: muestra el ítem "Aplicaciones"
2. **Ruta** — `permission:ver-apps` en `/apps/admin`: controla acceso al panel de administración

Interpretación correcta: `/apps/admin` es el panel de **administración** de apps, no el
acceso general al módulo Apps. Es una funcionalidad de Capa 3 (acción administrativa),
no la entrada al módulo.

Los usuarios finales acceden a "sus apps" desde el **dashboard** (`/ppal`), no desde
`/apps/admin`. El panel `/apps/admin` es una herramienta de gobernanza para administradores.

Por lo tanto, `ver-apps` es un permiso de Capa 3 correctamente nombrado, equivalente a
`ver-usuarios` o `ver-bienes`. No hay contradicción — solo un caso de nomenclatura confusa
que puede clarificarse renombrándolo a `administrar-apps-admin` en el futuro, pero no es
bloqueante.

---

## Evaluación de los tres patrones de control en menú

El menú sidebar en `config/adminlte.php` usa tres mecanismos distintos:

| Mecanismo | Ejemplo | Problema |
|-----------|---------|----------|
| Gate hardcoded a rol | `can:usuarios.user` | Acoplamiento a nombres de rol en código PHP |
| Gate → hasPermission | `can:ver-bienes` | Funciona, pero duplica lógica ya en CheckPermission |
| Sin control | Inventario (padre) | El ítem padre aparece para todos; solo los hijos filtran |

El menú actual es estático y no usa `App::visiblesPara()`. Esto es una deuda técnica
reconocida en ADR-008 DD-002 (menú dinámico pendiente en IMPL-013).

Mientras el menú sea estático, se requieren Gates para filtrar ítems. Una vez IMPL-013
migre el menú a ser dinámico desde Apps, los Gates de visibilidad de menú son eliminables.

---

## Riesgos identificados

### R-001 — Incoherencia visual vs. acceso real
**Probabilidad:** Alta (ya existe para módulos no en `app_role` del usuario)
**Impacto:** Usuarios ven ícono en dashboard pero reciben 403 al entrar
**Mitigación actual:** `app.access:{slug}` previene esto para Inventario y User
**Si se adopta `ver-{slug}`:** El riesgo se multiplica — requiere sincronización manual doble

### R-002 — Gates hardcoded rompen al renombrar roles
**Probabilidad:** Media
**Impacto:** Pérdida de acceso silenciosa para todos los usuarios de un rol renombrado
**Mitigación:** IMPL-AUTH-002 (migrar Gates a hasPermission)

### R-003 — Módulo Apps sin `app.access` propio
**Probabilidad:** Baja (Apps es un módulo de administración, no de usuario final)
**Impacto:** Usuario con `ver-apps` puede acceder al panel sin estar en `app_role` de Apps
**Mitigación:** El permiso `ver-apps` ya actúa como filtro; la inconsistencia es semántica

### R-004 — `roles.app_id` FK CASCADE sigue activa
**Probabilidad:** Baja (no se eliminan apps frecuentemente)
**Impacto:** Eliminación de una app destruye sus roles y desconecta a sus usuarios
**Mitigación pendiente:** IMPL-AUTH-003 (cambiar FK a SET NULL)

### R-005 — Menú estático desincronizado de `app.access`
**Probabilidad:** Media
**Impacto:** El menú muestra ítems de módulos a los que el usuario no tiene acceso real
**Mitigación:** IMPL-013 (menú dinámico desde Apps — pendiente)

---

## Decisión recomendada sobre `ver-{slug}`

**`ver-{slug}` NO debe convertirse en el mecanismo de Capa 2 (acceso a módulo).**

Razones:
1. ADR-008 (Aceptado) explícitamente lo prohíbe para Capa 2
2. `app.access:{slug}` ya está implementado y funciona en Inventario y User
3. Adoptar `ver-{slug}` duplicaría la gestión para administradores
4. Rompería la fuente única de verdad (`App::visiblesPara()`)

**El estándar de Capa 2 debe ser `app.access:{slug}` de forma universal.**

Los permisos `ver-{nombre-de-seccion}` existen correctamente como Capa 3 (acceso a secciones
específicas dentro de un módulo), no como puertas de entrada al módulo.

---

## Plan de estandarización (`app.access:{slug}` universal)

Esta sección describe la migración necesaria para consistencia, **sin implementar todavía**.

### Gap 1 — Apps routes sin `app.access:apps` (inconsistencia menor)
El módulo Apps es un caso especial: su única ruta "pública" para administradores es
`/apps/admin`, que ya está protegida por `permission:ver-apps`. No tiene un flujo de
usuario final comparable a Inventario o User.

**Decisión:** No agregar `app.access:apps` al panel de administración. El módulo Apps
es el *registry* de apps — no puede auto-referenciarse para su acceso.

### Gap 2 — Gates hardcoded en AuthServiceProvider (IMPL-AUTH-002)
Los cuatro Gates con nombres de rol hardcoded deben migrarse a `hasPermission()`:

| Gate actual | Permiso nuevo propuesto | Roles a asignar |
|-------------|------------------------|-----------------|
| `usuarios.user` | `administrar-accesos` | Administrador, Rector, Coordinador |
| `admin.grupos` | `administrar-grupos` | Administrador, Rector, Coordinador |
| `admin.evaldoc` | `administrar-evaldoc` | Administrador, Rector, Coordinador |
| `admin.biblioteca` | `administrar-biblioteca` | Administrador, Rector, Coordinador |

**Migración gradual:** Los Gates actuales siguen funcionando. Se migran módulo por módulo.

### Gap 3 — Menú estático con Gates de visibilidad (IMPL-013)
Una vez IMPL-013 implemente el menú dinámico desde `App::visiblesPara()`, los Gates
`can:ver-bienes`, `can:ver-actas-de-entrega`, `can:usuarios.user`, `can:ver-apps` en
`config/adminlte.php` deben evaluarse para eliminación o migración.

### Gap 4 — `roles.app_id` FK CASCADE (IMPL-AUTH-003)
La FK debe cambiar de `CASCADE DELETE` a `SET NULL` antes de que se eliminen apps
en producción. Es una migración segura con análisis de datos previo.

---

## Guía para módulos futuros

Con este plan aprobado, todos los módulos nuevos que se registren en `apps` deben seguir:

```
routes/web.php del módulo:
  Route::middleware(['web', 'auth', 'app.access:{slug-del-app}'])
      ->prefix('{slug-del-app}')
      ->group(function () {
          // Rutas internas con permission:ver-{seccion} solo donde aplique (Capa 3)
      });
```

El administrador asigna el módulo al rol en `/apps/admin` (gestiona `app_role`).
No se requiere permiso adicional `ver-{slug}` para el acceso de Capa 2.

---

## Entregables

1. **PLAN-AUTH-001** (este documento) — análisis y diseño
2. **ADR-AUTHORIZATION-002** — decisión formal sobre `ver-{slug}` y estandarización de Capa 2

---

## Implementaciones derivadas (no iniciar todavía)

| Ticket | Descripción | Prioridad | Precondición |
|--------|-------------|-----------|--------------|
| IMPL-AUTH-002 | Migrar Gates hardcoded a `hasPermission()` | Media | ADR-AUTHORIZATION-002 aprobado |
| IMPL-AUTH-003 | Cambiar `roles.app_id` FK a SET NULL | Media | Sin precondición técnica |
| IMPL-013 | Menú dinámico desde `App::visiblesPara()` | Alta | DP-001 y DP-002 resueltos (ADR-008) |
