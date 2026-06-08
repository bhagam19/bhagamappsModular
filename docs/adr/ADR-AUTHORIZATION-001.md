# ADR-AUTHORIZATION-001 — Arquitectura Definitiva de Autorización para BhagamAppsModular v1.5

**Estado:** Propuesto — pendiente de aprobación
**Fecha:** 2026-06-08
**Contexto:** AUDIT-APPS-002 — auditoría funcional y arquitectónica del módulo Apps
**Relacionado con:** ADR-APPS-001, ADR-001

---

## Problema central

BhagamAppsModular opera con **tres sistemas de autorización paralelos, independientes y potencialmente contradictorios**:

1. **Sistema Permission/Role** (`CheckPermission` middleware + `hasPermission()`) — controla acceso a rutas y acciones internas, basado en slugs de permisos almacenados en BD.

2. **Sistema Gate** (`Gate::define()` en `AuthServiceProvider`) — controla acceso a funcionalidades específicas, pero mezcla dos fuentes: `hasPermission()` y nombres de roles hardcoded en PHP.

3. **Sistema de visibilidad de apps** (`App::visiblesPara()`, `app_role`, `app_user`) — controla qué apps aparecen en el dashboard.

Estos tres sistemas no están coordinados. Un usuario puede ver una app (sistema 3), acceder a su ruta (sistema 1), pero fallar en un Gate interno (sistema 2) — o cualquier combinación de estas situaciones.

Adicionalmente, `roles.app_id` introduce una cuarta dimensión no implementada en código.

---

## Decisiones arquitectónicas para v1.5

### D1 — Las apps son el contexto de los permisos

**Decisión:** Cada permiso pertenece a una aplicación. La tabla `permissions` debe incorporar `app_id` como contexto. Esto permite que `App::visiblesPara()` y el sistema de permisos compartan la misma fuente de verdad.

**Modelo conceptual:**

```
App → tiene muchos Permisos (permissions.app_id)
App → es accesible por muchos Roles (app_role)
Role → tiene muchos Permisos (permission_role)
User → tiene un Role (users.role_id)
User → puede tener permisos directos adicionales (permission_user)
```

**Regla:** Si un rol tiene acceso a una app (`app_role`), el sistema debe garantizar que el rol tenga también los permisos base de esa app. Esto se gestiona en el panel `/apps/admin`.

---

### D2 — Tres capas de autorización con responsabilidades distintas

La arquitectura de v1.5 define tres capas con responsabilidades no superpuestas:

```
┌─────────────────────────────────────────────────────────────────┐
│  CAPA 1 — VISIBILIDAD (dashboard)                               │
│  Fuente: App::visiblesPara($user)                               │
│  Controla: ¿Qué apps ve el usuario en el dashboard?             │
│  Mecanismo: app_role (por rol) + app_user (individual)          │
│  Responsable: Módulo Apps / HomeController                      │
└─────────────────────────────────────────────────────────────────┘
                        │ coherencia obligatoria
                        ▼
┌─────────────────────────────────────────────────────────────────┐
│  CAPA 2 — ACCESO A MÓDULO (rutas)                               │
│  Fuente: Middleware permission:slug-de-entrada                  │
│  Controla: ¿Puede el usuario navegar a este módulo?             │
│  Mecanismo: permission_role + permission_user                   │
│  Responsable: routes/web.php de cada módulo                     │
└─────────────────────────────────────────────────────────────────┘
                        │ coherencia obligatoria
                        ▼
┌─────────────────────────────────────────────────────────────────┐
│  CAPA 3 — ACCIONES INTERNAS (funcionalidades)                   │
│  Fuente: hasPermission('slug-accion') en vistas y componentes   │
│  Controla: ¿Puede el usuario realizar esta acción específica?   │
│  Mecanismo: permission_role + permission_user                   │
│  Responsable: vistas Blade / Livewire / Controllers             │
└─────────────────────────────────────────────────────────────────┘
```

**Regla de coherencia:** Si un rol no tiene acceso a una app (Capa 1), el sistema NO debe mostrar el app en el dashboard. Si el rol tiene acceso, el permiso de entrada (Capa 2) DEBE existir y estar asignado automáticamente.

---

### D3 — Eliminar los Gates hardcoded (gradual)

**Decisión:** Los `Gate::define()` con nombres de roles hardcoded en `AuthServiceProvider` deben ser reemplazados gradualmente por permisos almacenados en BD.

**Antes (problema):**
```php
Gate::define('usuarios.user', function ($user) {
    return in_array($user->role->nombre, ['Administrador', 'Rector', 'Coordinador']);
});
```

**Después (target):**
```php
Gate::define('usuarios.user', function ($user) {
    return $user->hasPermission('administrar-usuarios');
});
```

El permiso `administrar-usuarios` se asigna a los roles `Administrador`, `Rector`, `Coordinador` en la BD — no en código.

**Migración:** No inmediata. Los Gates existentes funcionan. Se refactorizan módulo por módulo.

---

### D4 — Definir el propósito de `roles.app_id`

**Estado actual:** FK existente en producción, sin uso en código, con CASCADE DELETE que constituye un riesgo de datos.

**Opciones evaluadas:**

| Opción | Descripción | Veredicto |
|---|---|---|
| A | Mantener como "rol pertenece a un módulo" (scoping administrativo) | Rechazada — crea confusión con `app_role` |
| B | Eliminar la FK y reutilizar el campo como metadato opcional | Aceptada con condiciones |
| C | Convertirlo en el mecanismo primario de visibilidad (un rol → una app) | Rechazada — demasiado restrictivo |

**Decisión adoptada — Opción B:**
- `roles.app_id` se convierte en un campo de contexto/agrupación (sin FK CASCADE)
- Propósito: "este rol fue creado en el contexto de esta app" — para clasificación visual en el panel de roles
- La FK CASCADE DELETE se reemplaza por `onDelete('set null')` o se elimina la FK

**No se implementa en esta ADR** — requiere migración separada con análisis de datos existentes.

---

### D5 — Protección del panel `/apps/admin`

**Decisión:** El panel `/apps/admin` y los métodos Livewire `toggleHabilitada` y `guardarRoles` deben estar protegidos por el permiso `administrar-apps`.

**Implementación:**
- Crear permiso `administrar-apps` en BD
- Asignar a roles: `Administrador`, `Rector` (o roles que corresponda según la institución)
- Agregar `middleware('permission:administrar-apps')` a la ruta `/apps/admin`
- Agregar verificación `auth()->user()->hasPermission('administrar-apps')` en `AppsIndex` (toggle y guardar)

**Este es un cambio de código — debe implementarse en IMPL-APPS-002.**

---

### D6 — Permiso de entrada estandarizado por módulo

**Decisión:** Cada módulo registrado en `apps` debe tener un permiso de entrada con slug canónico:

```
ver-{slug-del-app}
```

Ejemplos:
- App slug `inventario` → permiso `ver-inventario`
- App slug `user` → permiso `ver-user`
- App slug `academico` → permiso `ver-academico`

Este permiso de entrada es el "puente" entre Capa 1 y Capa 2. Cuando un rol se asigna a una app en `app_role`, el sistema debe verificar que ese rol tenga el permiso `ver-{slug}`.

**El panel `/apps/admin` debe mostrar advertencia** si un rol tiene asignada una app pero no tiene el permiso de entrada correspondiente (Caso A de AUDIT-APPS-002).

---

### D7 — `App::visiblesPara()` permanece en el modelo (con plan de extracción)

**Decisión para v1.5:** El método estático permanece en `Modules/Apps/Entities/App.php`.

**Plan de extracción (v2.0):**
```
Modules/Apps/Services/AppVisibilityService.php
  → visiblesPara(User $user): Collection
  → cachedFor(User $user, int $ttlSeconds = 300): Collection
```

La extracción se realiza cuando:
1. El método necesite inyectar dependencias (caché, config)
2. Se requiera testear sin base de datos
3. El método crezca en complejidad con más condiciones

---

### D8 — Cacheo de `App::visiblesPara()`

**Decisión:** Agregar cacheo por usuario en `App::visiblesPara()` para evitar recalcular en cada carga del dashboard.

```php
// Propuesta de implementación (no ahora — IMPL-APPS-002):
Cache::remember("apps.visibles.{$user->id}", 300, function () use ($user) {
    // query actual
});

// Invalidar caché cuando:
// - Se modifica app_role para ese rol
// - Se modifica app_user para ese usuario
// - Se cambia habilitada en una app
```

**No se implementa en esta ADR** — requiere IMPL-APPS-002.

---

## Resumen de decisiones

| ID | Decisión | Implementación |
|---|---|---|
| D1 | Permisos pertenecen a apps (contexto) | Requiere migración (IMPL-APPS-003) |
| D2 | Tres capas de autorización con responsabilidades separadas | Marco conceptual — guía futura |
| D3 | Eliminar Gates hardcoded gradualmente | Módulo por módulo, sin fecha fija |
| D4 | `roles.app_id` → campo de contexto sin FK CASCADE | Requiere migración con análisis de datos |
| D5 | Proteger `/apps/admin` con permiso `administrar-apps` | IMPL-APPS-002 (próxima implementación) |
| D6 | Permiso de entrada `ver-{slug}` estandarizado por módulo | IMPL-APPS-002 + guía para módulos futuros |
| D7 | `visiblesPara()` permanece en modelo hasta v2.0 | Sin acción inmediata |
| D8 | Cacheo de `visiblesPara()` por usuario | IMPL-APPS-002 |

---

## Arquitectura objetivo v1.5

```
MÓDULO APPS (Application Registry)
├── app_role             → control de visibilidad por rol
├── app_user             → override individual de visibilidad
├── App::visiblesPara()  → fuente única del dashboard
├── apps:sync            → sincronización con nWidart
└── /apps/admin          → panel de gestión (protegido)

MÓDULO USER (Authorization Core)
├── roles                → definición de roles (1 por usuario)
├── permissions          → catálogo de permisos por módulo
├── permission_role      → permisos por rol
├── permission_user      → permisos directos (excepciones)
├── CheckPermission      → middleware de acceso a rutas
└── hasPermission()      → verificación en vistas/componentes

AUTHSERVICEPROVIDER
└── Gates                → reemplazar hardcoded por hasPermission() gradualmente

CADA MÓDULO
├── routes/web.php       → middleware('permission:ver-{slug}') en ruta de entrada
└── vistas               → hasPermission('accion-especifica') para acciones
```

---

## Próximas implementaciones derivadas de esta ADR

| Ticket | Descripción | Prioridad |
|---|---|---|
| IMPL-APPS-002 | Proteger `/apps/admin` + cacheo `visiblesPara()` + permiso `administrar-apps` | Alta |
| IMPL-AUTH-001 | Estandarizar permisos de entrada `ver-{slug}` por módulo | Alta |
| IMPL-AUTH-002 | Refactorizar Gates hardcoded a `hasPermission()` | Media |
| IMPL-AUTH-003 | Redefinir `roles.app_id` — migración de FK CASCADE | Media |
| IMPL-APPS-003 | Agregar `app_id` a `permissions` (permisos con contexto de app) | Baja/v2.0 |
