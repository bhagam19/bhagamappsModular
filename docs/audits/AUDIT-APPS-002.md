# AUDIT-APPS-002 — Auditoría Funcional y Arquitectónica Final del Módulo Apps

**Fecha:** 2026-06-08
**Auditor:** Claude (Sonnet 4.6)
**Alcance:** Módulo Apps + sistema de autorización de BhagamAppsModular
**Tipo:** Solo análisis — sin modificación de código
**Relacionado con:** AUDIT-APPS-001, ADR-APPS-001, IMPL-APPS-001

---

## 1. Flujo completo de autorización — recorrido por capas

```
Usuario autenticado (Jetstream/Sanctum)
         │
         ▼
  Middleware: auth, verified (Sanctum session)
         │
         ▼
  HomeController::index()
         │
         └──► App::visiblesPara(auth()->user())
                    │
                    ├──► WHERE app_role.role_id = user.role_id  [CAPA 1 — por rol]
                    └──► WHERE app_user.user_id = user.id       [CAPA 2 — individual]
                              AND app_user.activo = true
                              AND apps.habilitada = true
         │
         ▼
  Vista ppal::index → apps::index
  (íconos de apps visibles para este usuario)
         │
         ▼
  Usuario hace clic en un ícono de app → Navega a la ruta del módulo
         │
         ▼
  Middleware del módulo: varía por módulo
         │
         ├── Inventario: permission:ver-bienes (CheckPermission → hasPermission)
         ├── Users:      permission:ver-usuarios (CheckPermission → hasPermission)
         ├── Apps:       solo auth + verified  ← SIN verificación de permisos
         └── CrudGenerator: solo auth + verified ← SIN verificación de permisos
         │
         ▼
  Controlador del módulo → acción
         │
         ▼
  Vista Livewire / Blade
         │
         └──► Comprobaciones inline: auth()->user()->hasPermission('editar-bienes')
              O: auth()->user()->hasRole('Rector')   [⚠ mezcla de mecanismos]
```

**Conclusión del flujo:** Existen dos capas completamente desconectadas:
1. **Visibilidad** (dashboard): controlada por `App::visiblesPara()`
2. **Acceso** (rutas): controlada por middleware `permission:` por módulo
3. **Acciones internas**: controladas por `hasPermission()` inline en vistas

No existe un sistema unificado que vincule estas tres capas.

---

## 2. Diagrama de relaciones del modelo de datos

```
┌──────────────────────────────────────────────────────────────────────────┐
│                      ESQUEMA DE AUTORIZACIÓN                             │
└──────────────────────────────────────────────────────────────────────────┘

   ┌─────────────┐   role_id FK    ┌──────────────┐   app_id FK (!)  ┌──────────────┐
   │    users    │ ──────────────► │    roles     │ ───────────────► │     apps     │
   │─────────────│                 │──────────────│                  │──────────────│
   │ id          │                 │ id           │                  │ id           │
   │ role_id     │                 │ nombre       │                  │ nombre       │
   │ email       │                 │ descripcion  │  CASCADE DELETE  │ slug         │
   │ nombres     │                 │ app_id FK    │  ◄── ⚠ RIESGO   │ ruta         │
   │ apellidos   │                 └──────────────┘                  │ habilitada   │
   └─────────────┘                        │                          │ orden        │
          │                               │                          └──────────────┘
          │                        permission_role                          │
          │                        (pivot M:M)                    app_role (PENDING)
          │                               │                        (pivot M:M)
          │                               ▼                               │
          │                    ┌──────────────────┐                       │
          │   permission_user  │   permissions    │                       │
          │   (pivot M:M)      │──────────────────│    ◄──────────────────┘
          └─────────────────►  │ id               │
                               │ nombre           │
          app_user (pivot)     │ slug             │
          ┌──────────────┐     │ categoria        │
          │ user_id FK   │     └──────────────────┘
          │ app_id FK    │
          │ role_id FK ? │ ← nullable, semántica ambigua
          │ activo       │
          └──────────────┘

LEYENDA:
  ──► FK estándar
  ⚠   Riesgo identificado
  (!) Relación no implementada en el modelo Role
  (PENDING) Migración pendiente de ejecutar
```

### Qué controla qué

| Dimensión | Mecanismo | Tabla(s) | Estado |
|---|---|---|---|
| **Visibilidad de app** (dashboard) | `App::visiblesPara($user)` | `app_role`, `app_user`, `apps.habilitada` | Implementado (migraciones pending) |
| **Acceso a ruta de módulo** | Middleware `permission:slug` | `permissions`, `permission_role`, `permission_user` | Implementado (Inventario, User) |
| **Acciones internas** | `hasPermission()` inline | `permissions`, `permission_role`, `permission_user` | Implementado parcialmente |
| **Acceso administrativo** | `Gate::define()` hardcoded | Ninguna tabla | Implementado (solo Inventario/User) |
| **Roles de usuario** | `users.role_id` | `roles` | Implementado — 1 rol por usuario |

---

## 3. Inconsistencias detectadas por escenario

### Caso A — App visible en dashboard pero 403 al ingresar

**Estado actual:** OCURRE — no hay protección

**Ejemplo:**
- Usuario "Docente" tiene `app_role` → Inventario asignado (lo ve en el dashboard)
- Ruta `GET /inventario/bienes` tiene `middleware('permission:ver-bienes')`
- Si "Docente" no tiene el permiso `ver-bienes`, recibe `403 Forbidden`
- El usuario ve la app pero no puede entrar

**Comportamiento actual:** Dashboard muestra la app → usuario hace clic → 403
**Comportamiento esperado:** Si la app es visible, el usuario debería poder acceder a su página de inicio

**Causa raíz:** Desconexión total entre `App::visiblesPara()` y el sistema de permisos de ruta. Son sistemas independientes con fuentes de datos distintas.

**Severidad:** Alta — genera confusión y experiencia de usuario degradada

---

### Caso B — Usuario con permisos internos pero app no visible

**Estado actual:** OCURRE

**Ejemplo:**
- Un usuario tiene permiso `editar-bienes` asignado directamente (vía `permission_user`)
- Pero Inventario no está en `app_role` para su rol ni en `app_user` individual
- `App::visiblesPara()` no devuelve Inventario
- El usuario puede acceder a `/inventario/bienes` por URL directa (si tiene el permiso) pero no ve el ícono

**Comportamiento actual:** Usuario funcional dentro de Inventario pero sin visibilidad en dashboard
**Comportamiento esperado:** Coherencia — si tiene permisos funcionales, debería ver la app

**Severidad:** Media — solo afecta UX, no seguridad

---

### Caso C — App habilitada sin roles asignados

**Estado actual:** OCURRE para todas las apps tras IMPL-APPS-001

**Situación:**
- `app_role` fue creada pero aún no tiene datos (migraciones pending)
- `app_user` puede tener asignaciones antiguas
- Con `App::visiblesPara()` como fuente única, una app habilitada sin rol asignado Y sin usuario en `app_user` → invisible para todos

**Comportamiento actual:** Apps habilitadas pueden ser invisibles si `app_role` está vacía
**Comportamiento esperado:** Al menos el admin debe ver todas las apps habilitadas

**Severidad:** Alta — post-migración, el dashboard puede quedar vacío si no se configuran roles

---

### Caso D — Conflictos entre mecanismos

**Conflicto D1: `roles.app_id` vs `app_role`**

La tabla `roles` tiene `app_id FK → apps (CASCADE DELETE)`. Este campo implica:
- Cada rol "pertenece" a una app (1:1)
- Si se elimina un app, sus roles son eliminados en cascada — incluyendo el `role_id` que tienen los usuarios
- Esto destruiría todos los `users.role_id` que apunten a esos roles

Pero `app_role` (nueva) expresa: "una app puede tener muchos roles" (M:M).

Estas son semánticas contradictorias:
- `roles.app_id` = "un rol pertenece a UNA app" (escopo de administración)
- `app_role` = "una app es accesible por VARIOS roles"

**El modelo `Role.php` no implementa la relación `belongsTo(App)`** — el `app_id` en roles es una FK sin uso en código.

**Conflicto D2: `app_user.role_id` vs todo lo demás**

La tabla `app_user` tiene un `role_id nullable`. Este campo no está en `App::visiblesPara()` ni en ningún query visible. Su propósito es ambiguo: ¿significa "el usuario accede a esta app con este rol"? ¿O es un remanente del diseño original?

**Conflicto D3: Tres sistemas de autorización en producción**

| Sistema | Donde se usa | Fuente de verdad |
|---|---|---|
| `CheckPermission` middleware + `hasPermission()` | Rutas Inventario, User; vistas inline | `permission_role` + `permission_user` |
| `Gate::define()` | AuthServiceProvider | Mezcla: `hasPermission()` + nombre de rol hardcoded |
| `hasRole('Rector')` directo | Vistas Inventario | `users.role_id → roles.nombre` |

Estos tres sistemas pueden dar respuestas contradictorias para el mismo usuario:
- `Gate::define('usuarios.user', ...)` → `in_array($user->role->nombre, ['Administrador', 'Rector', 'Coordinador'])` — basado en nombre de rol
- `hasPermission('ver-usuarios')` → busca en `permission_role` y `permission_user` — basado en registro en BD

Un "Rector" con permiso `ver-usuarios` eliminado de la BD pasaría el Gate pero fallaría el middleware `permission:ver-usuarios`.

---

## 4. Evaluación de `App::visiblesPara($user)`

### Ubicación actual

`Modules/Apps/Entities/App.php` — método estático en el modelo.

### Análisis

**A favor:**
- Centraliza la lógica de visibilidad en un único lugar
- Conveniente para el controlador: `App::visiblesPara(auth()->user())`
- No hay duplicidad actual (HomeController ya no tiene la lógica)

**En contra:**
- Un modelo de entidad Eloquent no debería contener lógica de autorización
- Mezcla dos responsabilidades: persistencia y reglas de negocio
- El método hace 2 subqueries (`whereHas`) sin cacheo — N+1 potencial si se llama varias veces
- El método no es testeable de forma aislada sin base de datos (no es injectable)

**¿Es la ubicación correcta?**

Para el tamaño actual del sistema: **Sí, es aceptable.** Para v1.5 con 10+ módulos: **No — debería migrar a un `AppVisibilityService` o `AppVisibilityQuery`.**

**¿Existe duplicidad?**

No actualmente. El `HomeController` delegó completamente en este método. Riesgo futuro: que otros controladores reimplementen la lógica.

**¿Puede ser la fuente única?**

**Sí para el dashboard.** No para acceso funcional — eso requiere el sistema de permisos por ruta.

---

## 5. Evaluación de deuda técnica

| Item | Descripción | Severidad |
|---|---|---|
| **DT-001** | `roles.app_id` CASCADE DELETE — puede destruir roles si se elimina una app | **Alta** |
| **DT-002** | `/apps/admin` sin protección de permisos — cualquier usuario autenticado puede gestionar apps | **Alta** |
| **DT-003** | Tres sistemas de autorización paralelos (middleware, Gates, hasRole directo) sin coordinación | **Alta** |
| **DT-004** | Gates en `AuthServiceProvider` con nombres de roles hardcoded (`'Administrador'`, `'Rector'`) — acoplamiento a datos | **Alta** |
| **DT-005** | `App::visiblesPara()` hace 2 subqueries por carga del dashboard sin cacheo | **Media** |
| **DT-006** | `app_user.role_id` sin uso en queries ni documentación de propósito | **Media** |
| **DT-007** | `roles.app_id` sin relación Eloquent implementada en `Role.php` — FK sin uso en código | **Media** |
| **DT-008** | `hasPermission()` llamado inline en vistas Blade (lógica de negocio en presentación) | **Media** |
| **DT-009** | `App::visiblesPara()` no cachea resultados — llamada repetida recalcula todo | **Media** |
| **DT-010** | Livewire `AppsIndex` sin protección de permisos en `toggleHabilitada` ni `guardarRoles` | **Media** |
| **DT-011** | `permission_user` (permisos directos a usuario) contradice el modelo rol-céntrico del sistema | **Baja** |
| **DT-012** | Livewire component `PermissionsIndex` registrado en `routes/web.php` (acoplamiento ruta-componente) | **Baja** |
| **DT-013** | Vista `apps::index` usa estilos inline en exceso | **Baja** |

---

## 6. Dashboard dinámico — viabilidad de `App::visiblesPara()` como fuente única

### Viabilidad

**Sí es viable** como fuente única para el dashboard. La estructura actual ya lo implementa correctamente tras IMPL-APPS-001.

### Ventajas

- Un solo método para cambiar el comportamiento de visibilidad para todos los usuarios
- La asignación de roles a apps desde `/apps/admin` es suficiente para gestionar el dashboard
- `apps:sync` permite registrar automáticamente nuevos módulos
- Extensible: se pueden agregar más condiciones (teams, dependencias) en el método

### Riesgos

| Riesgo | Descripción | Mitigación recomendada |
|---|---|---|
| Dashboard vacío post-migración | Si `app_role` está vacía, ningún usuario ve apps | Ejecutar seeder + asignar roles inmediatamente |
| Performance | 2 subqueries por carga del dashboard | Cacheo por usuario (`cache()->remember()`) |
| Caso A | App visible pero 403 al ingresar | Ver ADR-AUTHORIZATION-001 |
| Sin superadmin | No hay un mecanismo para que el admin vea TODAS las apps siempre | Requiere rol especial o bypass en `visiblesPara()` |

### Requisitos previos para activar en producción

1. Ejecutar `php artisan migrate`
2. Ejecutar seed de Apps (actualizado)
3. Asignar roles a apps desde `/apps/admin` (protegida con permiso)
4. Definir qué roles tienen acceso a qué apps
5. Proteger `/apps/admin` con middleware de permisos

---

## 7. Integración con módulos futuros

### Flujo para un módulo nuevo

```
php artisan module:make Academico
   → Genera Modules/Academico/ con estructura nWidart

php artisan apps:sync
   → Crea registro en apps {nombre: 'Academico', slug: 'academico', habilitada: false}

Admin → /apps/admin
   → Habilita la app, asigna roles

php artisan apps:sync (no duplica, idempotente)
```

### Compatibilidad por módulo

| Módulo | Compatibilidad con App Registry | Acción requerida |
|---|---|---|
| **Inventario** | Total — ya registrado en seeder | Asignar roles en `app_role` post-migrate |
| **Users** | Total — ya registrado en seeder | Asignar roles en `app_role` post-migrate |
| **CrudGenerator** | Total — `apps:sync` lo detecta | Decidir si debe aparecer en dashboard |
| **Académico** (futuro) | Total — `apps:sync` lo registra | Crear permisos correspondientes |
| **Biblioteca** (futuro) | Total — ya en seeder (habilitada=false) | Habilitar y asignar roles cuando esté listo |
| **Calidad** (futuro) | Total — `apps:sync` lo registra | Crear permisos + habilitar |
| **Talento Humano** (futuro) | Total — `apps:sync` lo registra | Crear permisos + habilitar |
| **Actas** (futuro) | Total — `apps:sync` lo registra | Crear permisos + habilitar |
| **Contratación** (futuro) | Total — `apps:sync` lo registra | Crear permisos + habilitar |

**Observación:** `apps:sync` NO crea permisos automáticamente. Cada módulo nuevo requerirá:
1. Registro manual de permisos (`permissions` table)
2. Asignación de permisos a roles (`permission_role`)
3. Definición de gates si el módulo los necesita
4. Asignación del app a roles en `app_role`

CrudGenerator ya tiene stubs para gates (`gate.stub`) pero no tiene integración con `app_role`.

---

## 8. Conclusión — ¿Está el módulo Apps listo como Application Registry oficial?

### Veredicto: **CONDICIONALMENTE LISTO**

El módulo Apps tiene la infraestructura correcta pero requiere tres acciones bloqueantes antes de ser declarado Application Registry oficial:

| Condición bloqueante | Descripción | Prioridad |
|---|---|---|
| **B1** | Ejecutar migraciones pendientes | Inmediata |
| **B2** | Proteger `/apps/admin` con middleware de permisos | Alta |
| **B3** | Proteger métodos Livewire (`toggleHabilitada`, `guardarRoles`) con verificación de permisos | Alta |

Y dos condiciones de mejora para v1.5:

| Mejora | Descripción | Prioridad |
|---|---|---|
| **M1** | Unificar los tres sistemas de autorización (ver ADR-AUTHORIZATION-001) | Alta |
| **M2** | Definir el propósito de `roles.app_id` o eliminarlo/refactorizarlo | Media |

### Estado por objetivo

| Objetivo IMPL-APPS-001 | Estado |
|---|---|
| Registrar aplicaciones | ✅ Implementado |
| Activar/desactivar aplicaciones | ✅ Implementado (Livewire toggle) |
| Asociar aplicaciones a roles | ⚠ Infraestructura lista, migraciones pending |
| Mostrar apps autorizadas a cada usuario | ⚠ `visiblesPara()` implementado, pero depende de migraciones |
| Sincronización con módulos nWidart | ✅ `apps:sync` implementado |
| Panel de administración | ⚠ Funcional pero sin protección de permisos |
