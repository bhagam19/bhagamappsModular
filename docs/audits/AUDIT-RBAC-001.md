# AUDIT-RBAC-001 — Arquitectura de Autorización BhagamAppsModular

**Fecha:** 2026-06-14  
**Versión auditada:** BhagamApps v1.4.0  
**Auditor:** Claude Sonnet 4.6 (arquitectónico — sin modificaciones al código)  
**Propósito:** Base para definir la arquitectura oficial de autorización de APPSisGOE

---

## 1. Modelo de Datos

### 1.1 Tablas involucradas (10 tablas)

#### Tabla: `users`

| Columna | Tipo | Nullable | Default | Descripción |
|---------|------|----------|---------|-------------|
| `id` | bigint PK | NO | — | — |
| `nombres` | varchar | NO | — | Primer nombre y segundo nombre |
| `apellidos` | varchar | NO | — | Apellidos del usuario |
| `userID` | varchar(191) UNIQUE | NO | — | Código institucional interno |
| `email` | varchar(191) UNIQUE | NO | — | Correo electrónico |
| `email_verified_at` | timestamp | SÍ | NULL | Verificación de email |
| `password` | varchar | NO | — | Hash bcrypt |
| `remember_token` | varchar | SÍ | NULL | Token de sesión persistente |
| `role_id` | bigint FK | NO | — | FK → `roles.id` ON DELETE RESTRICT |
| `current_team_id` | bigint | SÍ | NULL | Campo Jetstream (no usado funcionalmente) |
| `profile_photo_path` | varchar(2048) | SÍ | NULL | Foto de perfil Jetstream |
| `bloqueado` | tinyint(1) | NO | 0 | Cuenta bloqueada — impide login |
| `forzar_cambio_password` | tinyint(1) | NO | 0 | Fuerza cambio de contraseña en próximo login |
| `es_principal` | tinyint(1) | NO | 0 | Marca al Administrador Principal (super-admin de emergencia) |
| `created_at` | timestamp | SÍ | NULL | — |
| `updated_at` | timestamp | SÍ | NULL | — |

**Índices:** PK(id), UNIQUE(userID), UNIQUE(email), FK(role_id)

---

#### Tabla: `roles`

| Columna | Tipo | Nullable | Default | Descripción |
|---------|------|----------|---------|-------------|
| `id` | bigint PK | NO | — | — |
| `nombre` | varchar UNIQUE | NO | — | Nombre del rol |
| `descripcion` | varchar | SÍ | NULL | Descripción funcional |
| `app_id` | bigint FK | SÍ | NULL | FK → `apps.id` ON DELETE SET NULL (campo legacy) |

**Roles en producción (7):**

| ID | Nombre | Descripción |
|----|--------|-------------|
| 1 | Administrador | Acceso completo al sistema |
| 2 | Rectoría | Orienta todos los procesos en general |
| 3 | Coordinación | Supervisa procesos académicos o administrativos |
| 4 | Auxiliar | Apoya todos los procesos académicos o administrativos |
| 5 | Docente | Encargado de impartir clases y evaluar estudiantes |
| 6 | Estudiante | Acceso a contenidos y actividades académicas |
| 7 | Invitado | Acceso limitado para pruebas o demostraciones |

**Nota:** `app_id` es un campo legacy. Originalmente cada rol "pertenecía" a una app (FK CASCADE). Fue corregido a nullable + SET NULL en IMPL-003 para evitar pérdida masiva de roles al eliminar apps.

---

#### Tabla: `permissions`

| Columna | Tipo | Nullable | Default | Descripción |
|---------|------|----------|---------|-------------|
| `id` | bigint PK | NO | — | — |
| `nombre` | varchar UNIQUE | NO | — | Nombre legible del permiso |
| `slug` | varchar UNIQUE | NO | — | Identificador técnico (kebab-case) |
| `descripcion` | varchar | SÍ | NULL | Descripción funcional |
| `categoria` | varchar | NO | `'general'` | Agrupación temática del permiso |

**Total en producción: 85 permisos** (ver Sección 4 para catálogo completo).

---

#### Tabla: `permission_role`

| Columna | Tipo | Nullable | Descripción |
|---------|------|----------|-------------|
| `id` | bigint PK | NO | — |
| `role_id` | bigint FK | NO | FK → `roles.id` CASCADE |
| `permission_id` | bigint FK | NO | FK → `permissions.id` CASCADE |
| `created_at` | timestamp | SÍ | — |
| `updated_at` | timestamp | SÍ | — |

**Índices:** UNIQUE(role_id, permission_id) — añadido en IMPL-003 para eliminar 76 duplicados.  
**Semántica:** Permisos heredados por rol. Todos los usuarios de un rol heredan sus permisos.

---

#### Tabla: `permission_user`

| Columna | Tipo | Nullable | Descripción |
|---------|------|----------|-------------|
| `id` | bigint PK | NO | — |
| `permission_id` | bigint FK | NO | FK → `permissions.id` CASCADE |
| `user_id` | bigint FK | NO | FK → `users.id` CASCADE |

**⚠️ Sin constraint UNIQUE(permission_id, user_id)** — riesgo de duplicados en asignaciones directas.  
**Semántica:** Permisos directos a usuarios individuales — sobreescribe o complementa al rol.

---

#### Tablas relacionadas con Apps (ver AUDIT-APPS-002-APPSisGOE.md)

| Tabla | Rol en RBAC |
|-------|-------------|
| `apps` | Catálogo de módulos del sistema |
| `app_role` | Determina qué roles acceden a qué módulos |
| `app_user` | Override individual de acceso a módulos |

---

#### Tabla: `auditoria_passwords`

| Columna | Tipo | Nullable | Descripción |
|---------|------|----------|-------------|
| `id` | bigint PK | NO | — |
| `usuario_afectado_id` | bigint FK | NO | FK → `users.id` CASCADE |
| `administrador_id` | bigint FK | NO | FK → `users.id` CASCADE |
| `accion` | enum | NO | `password_reset`, `password_forced`, `user_blocked`, `user_unblocked` |
| `fecha_hora` | timestamp | NO | — |

Registro inmutable de todas las operaciones administrativas sobre cuentas de usuario. Sin `updated_at` — solo se inserta, nunca se modifica.

---

#### Tabla: `activity_logs`

Log global del sistema — registra todas las acciones significativas en todos los módulos. Contiene índices compuestos para consultas por módulo, acción y fecha.

---

### 1.2 Diagrama Lógico (ASCII)

```
┌──────────┐         ┌──────────────────┐         ┌─────────────┐
│  users   │────────►│  permission_user │◄────────│ permissions │
│          │ 1     N │                  │ N     1 │             │
│ role_id ─┼────┐    └──────────────────┘         │ nombre      │
│ bloqueado│    │                                  │ slug        │
│ es_princi│    │    ┌──────────────────┐         │ categoria   │
└──────────┘    │    │ permission_role  │◄────────┘             │
                │    │                  │ N     1               │
                │    └──────────────────┘         ┌─────────────┘
                │              ▲                   
                │              │ N               
                └──────►┌──────┴───┐        ┌──────────┐
                         │  roles   │────────│ app_role │
                         │          │ 1    N │          │
                         │ app_id ──┼──┐    └──────────┘
                         └──────────┘  │
                                        │N  ┌──────────┐
                                        └──►│   apps   │◄───┐
                                            │          │    │
                                            └──────────┘    │
                         ┌──────────┐                        │
                         │ app_user │────────────────────────┘
                         │ activo   │ N                         
                         └──────────┘                           
```

**Cardinalidades:**
- `users` 1:N `roles` (un usuario tiene un rol; un rol tiene muchos usuarios)
- `users` M:N `permissions` via `permission_user` (permisos directos)
- `roles` M:N `permissions` via `permission_role` (permisos heredados)
- `apps` M:N `roles` via `app_role` (acceso de roles a módulos)
- `apps` M:N `users` via `app_user` (acceso individual a módulos)

---

## 2. Componentes del Sistema RBAC

### 2.1 Modelo User

**Archivo:** `Modules/User/Entities/User.php`

**Traits:** `HasApiTokens`, `HasFactory`, `HasProfilePhoto`, `Notifiable`, `TwoFactorAuthenticatable`

**Casts:** `email_verified_at → datetime`, `bloqueado → boolean`, `forzar_cambio_password → boolean`, `es_principal → boolean`

**Relaciones:**
- `role()` — `belongsTo(Role, 'role_id')` — rol único del usuario
- `permissions()` — `belongsToMany(Permission)` via `permission_user` — permisos directos
- `apps()` — `belongsToMany(App, 'app_user', 'user_id', 'app_id')` — módulos asignados individualmente
- `dependencias()` — `hasMany(Dependencia, 'user_id')` — unidades a cargo

**Métodos de autorización:**

```php
// Verifica un permiso — primero directo (permission_user), luego heredado (permission_role)
public function hasPermission($slug): bool
{
    if ($this->permissions()->where('slug', $slug)->exists()) {
        return true;  // 1 query SQL
    }
    if ($this->role && $this->role->permissions()->where('slug', $slug)->exists()) {
        return true;  // 1 query SQL adicional
    }
    return false;
}

// Verifica un rol por nombre
public function hasRole($roleNombre): bool
{
    return $this->role && $this->role->nombre === $roleNombre;  // 0 queries si ya cargado
}

// Verifica si es el Administrador Principal
public function isAdminPrincipal(): bool
{
    return (bool) $this->es_principal;  // campo en memoria — 0 queries
}
```

**⚠️ Problema de rendimiento:** `hasPermission()` ejecuta hasta 2 queries SQL por llamada. En una vista con múltiples `@can` o un componente Livewire con varios `abort_if()`, se generan decenas de queries por render.

---

### 2.2 Modelo Role

**Archivo:** `Modules/User/Entities/Role.php`

**Relaciones:**
- `users()` — `hasMany(User)`
- `permissions()` — `belongsToMany(Permission, 'permission_role', 'role_id', 'permission_id')`

**Método de verificación:**

```php
public function hasPermission($permissionName): bool
{
    return $this->permissions->contains('nombre', $permissionName);
    // ⚠️ Busca por 'nombre' (texto legible), NO por 'slug' — inconsistente con User::hasPermission($slug)
}
```

**⚠️ Bug de consistencia:** `Role::hasPermission()` busca por `nombre` mientras `User::hasPermission()` busca por `slug`. Son dos sistemas distintos en el mismo codebase.

---

### 2.3 Modelo Permission

**Atributos:** `nombre` (UNIQUE), `slug` (UNIQUE), `descripcion`, `categoria`  
**Relaciones:** `belongsToMany(Role)` via `permission_role`, `belongsToMany(User)` via `permission_user`

---

### 2.4 Middleware: `CheckPermission`

**Archivo:** `app/Http/Middleware/CheckPermission.php`  
**Alias:** `permission` (registrado en `bootstrap/app.php`)

```php
public function handle(Request $request, Closure $next, $permission): Response
{
    $user = $request->user();
    if (!$user || !$user->hasPermission($permission)) {
        abort(403, 'No tienes permiso para acceder a este recurso.');
    }
    return $next($request);
}
```

**Uso en rutas:** `->middleware('permission:ver-bienes')`

---

### 2.5 Middleware: `CheckAppAccess`

**Archivo:** `app/Http/Middleware/CheckAppAccess.php`  
**Alias:** `app.access` (registrado en `bootstrap/app.php`)

```php
public function handle(Request $request, Closure $next, string $slug): Response
{
    $user = $request->user();
    if (!$user) {
        abort(403, 'No tienes acceso a este módulo.');
    }
    if (!App::visiblesPara($user)->contains('slug', $slug)) {
        abort(403, 'No tienes acceso al módulo "' . $slug . '".');
    }
    return $next($request);
}
```

Usa `App::visiblesPara()` con caché versioned de 300 segundos.

---

### 2.6 Middleware: `CheckForzarCambioPassword`

Redirige al usuario a cambiar contraseña si `forzar_cambio_password = true`.

**⚠️ Registrado en dos lugares:** `app/Http/Kernel.php` Y `bootstrap/app.php` — riesgo de aplicación doble en cada request.

---

### 2.7 Trait: `ProteccionAdminPrincipal`

**Archivo:** `Modules/User/Traits/ProteccionAdminPrincipal.php`

```php
protected function verificarNoEsAdminPrincipal(User $target, string $accion): void
{
    if (!$target->isAdminPrincipal()) return;
    
    AuditoriaPassword::create([...]);  // Registra el intento
    abort(403, 'El Administrador Principal no puede ser modificado.');
}
```

Usado en: `GestionEstadoUser`, `GestionPasswordUser`, `EditarRolUser`, `EditarNombresUser`, `EditarApellidosUser`, `EditarEmailUser`, `EditarUserIDUser` — protege al admin principal de ser modificado accidentalmente por otros administradores.

---

### 2.8 Gates en AuthServiceProvider

**Archivo:** `app/Providers/AuthServiceProvider.php`

**Total: 60 gates definidos** — todos delegan a `hasPermission($slug)`.

**Patrón estándar (57 gates):**
```php
Gate::define('ver-bienes', fn($user) => $user->hasPermission('ver-bienes'));
```

**Patrón con doble condición — requieren `es_principal` (3 gates):**

| Gate | Condición |
|------|-----------|
| `restaurar-backups` | `hasPermission('restaurar-backups') && isAdminPrincipal()` |
| `importar-snapshot-backup` | `hasPermission('importar-snapshot-backup') && isAdminPrincipal()` |
| `ver-activity-log` | `hasPermission('ver-activity-log') && isAdminPrincipal()` |

**Políticas registradas:** Ninguna — el array `$policies` está vacío.

**Gate especial:**
```php
Gate::define('guest-only', fn($user = null) => $user === null);
```

---

### 2.9 Campo `es_principal` — Administrador Principal

**Naturaleza:** Campo booleano en `users` (migración `2026_06_12_000011`).

**Asignación automática:** Al ejecutar la migración, se marca como `es_principal = true` al primer usuario con rol `Administrador` (ID más bajo).

**Solo puede existir uno:** No hay constraint UNIQUE — es una convención del sistema. El seeder garantiza un único `es_principal = true`.

**Protecciones aplicadas sobre el Administrador Principal:**
1. Gates `restaurar-backups`, `importar-snapshot-backup`, `ver-activity-log` requieren `es_principal`
2. Trait `ProteccionAdminPrincipal` — 7 componentes lo usan para evitar modificación del admin principal
3. `UserIndex` oculta botones de edición para `es_principal = true`
4. Blade muestra badge especial en la interfaz para identificarlo

---

## 3. Flujo Completo de Autorización

```
HTTP Request
    │
    ▼
┌─────────────────────────────────────────────────────────────────┐
│  CAPA 1 — AUTENTICACIÓN (Fortify)                               │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  $user = request->user()                                │   │
│  │  ├─ NULL → redirect('/login')                          │   │
│  │  ├─ bloqueado = true → logout + error                  │   │
│  │  ├─ email_verified_at IS NULL → redirect('/verify')    │   │
│  │  └─ forzar_cambio_password = true → redirect('/cambiar')│   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
    │
    ▼
┌─────────────────────────────────────────────────────────────────┐
│  CAPA 2 — ACCESO AL MÓDULO (CheckAppAccess)                    │
│  Middleware: app.access:{slug}                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  App::visiblesPara($user)                               │   │
│  │  ├─ Consulta caché versioned (300s)                    │   │
│  │  ├─ HIT → colección cacheada                           │   │
│  │  └─ MISS → query:                                      │   │
│  │       SELECT * FROM apps WHERE habilitada = 1          │   │
│  │       AND (app_role.role_id = user.role_id              │   │
│  │            OR app_user.user_id = user.id AND activo)    │   │
│  │  .contains('slug', {slug})                              │   │
│  │  ├─ false → abort(403, 'Sin acceso al módulo')         │   │
│  │  └─ true  → continúa                                   │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
    │
    ▼
┌─────────────────────────────────────────────────────────────────┐
│  CAPA 3 — PERMISO GRANULAR (CheckPermission)                   │
│  Middleware: permission:{slug}                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  $user->hasPermission($slug)                            │   │
│  │  ├─ permission_user WHERE slug = {slug} EXISTS? (query1)│   │
│  │  │   └─ SÍ → acceso concedido                          │   │
│  │  ├─ role.permissions WHERE slug = {slug} EXISTS? (query2)│  │
│  │  │   └─ SÍ → acceso concedido                          │   │
│  │  └─ NO → abort(403, 'Sin permiso')                     │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
    │
    ▼
┌─────────────────────────────────────────────────────────────────┐
│  CAPA 4 — VERIFICACIÓN EN COMPONENTE (Livewire / Controller)   │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  abort_if(!$user->hasPermission('editar-bienes'), 403)  │   │
│  │  ó                                                       │   │
│  │  abort_unless($user->hasPermission('crear-bienes'), 403)│   │
│  │                                                          │   │
│  │  Para operaciones críticas (restaurar, importar):        │   │
│  │  Gate::allows('restaurar-backups')                       │   │
│  │  = hasPermission(slug) && isAdminPrincipal()             │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
    │
    ▼
ACCESO CONCEDIDO → Controlador / Livewire procesa la operación
```

---

## 4. Catálogo Completo de Permisos (85 permisos)

| # | Categoría | Slug | Descripción inferida | Módulo |
|---|-----------|------|---------------------|--------|
| 1 | `actas de entrega` | `ver-actas-de-entrega` | Acceder a actas de entrega | Inventario |
| 2 | `admin-sistema` | `descargar-backups` | Descargar archivos de backup | AdminSistema |
| 3 | `admin-sistema` | `generar-backups` | Crear nuevos snapshots | AdminSistema |
| 4 | `admin-sistema` | `importar-snapshot-backup` | Importar snapshot externo (+es_principal) | AdminSistema |
| 5 | `admin-sistema` | `restaurar-backups` | Restaurar desde backup (+es_principal) | AdminSistema |
| 6 | `admin-sistema` | `sincronizar-backup-drive` | Sincronizar con Google Drive | AdminSistema |
| 7 | `admin-sistema` | `ver-activity-log` | Ver log de actividad (+es_principal) | ActivityLog |
| 8 | `admin-sistema` | `ver-backup-drive` | Ver backups en Google Drive | AdminSistema |
| 9 | `admin-sistema` | `ver-backups` | Ver lista de backups | AdminSistema |
| 10 | `administracion-passwords` | `bloquear-usuarios` | Bloquear cuentas de usuario | User |
| 11 | `administracion-passwords` | `desbloquear-usuarios` | Desbloquear cuentas | User |
| 12 | `administracion-passwords` | `restablecer-passwords` | Restablecer contraseñas | User |
| 13 | `administracion-passwords` | `ver-administracion-passwords` | Acceder al panel de admin de contraseñas | User |
| 14 | `apps` | `administrar-apps` | Habilitar/deshabilitar y asignar roles a apps | Apps |
| 15 | `apps` | `crear-apps` | Registrar nueva aplicación | Apps |
| 16 | `apps` | `editar-apps` | Modificar datos de apps | Apps |
| 17 | `apps` | `eliminar-apps` | Eliminar app del catálogo | Apps |
| 18 | `apps` | `ver-apps` | Acceder al panel de administración de apps | Apps |
| 19 | `aprobaciones pendientes` | `aprobar-pendientes-bienes` | Aprobar solicitudes pendientes | Inventario |
| 20 | `aprobaciones pendientes` | `editar-aprobaciones-pendientes-bienes` | Editar solicitudes pendientes | Inventario |
| 21 | `aprobaciones pendientes` | `eliminar-aprobaciones-pendientes-bienes` | Eliminar solicitudes pendientes | Inventario |
| 22 | `aprobaciones pendientes` | `gestionar-historial-eliminaciones-bienes` | Gestionar HEB | Inventario |
| 23 | `aprobaciones pendientes` | `gestionar-historial-modificaciones-bienes` | Gestionar HMB | Inventario |
| 24 | `biblioteca` | `ver-biblioteca` | Acceder a la biblioteca | Biblioteca |
| 25 | `bienes` | `aprobar-bienes` | Aprobar directamente cambios en bienes | Inventario |
| 26 | `bienes` | `asignar-responsables-a-bienes` | Asignar custodio (vía bienes) | Inventario |
| 27 | `bienes` | `crear-bienes` | Registrar nuevo bien | Inventario |
| 28 | `bienes` | `editar-bienes` | Proponer cambios en bienes | Inventario |
| 29 | `bienes` | `eliminar-bienes` | Solicitar baja de bien | Inventario |
| 30 | `bienes` | `ver-bienes` | Acceder al listado de bienes | Inventario |
| 31 | `bienes` | `ver-historial-bienes` | Ver historial de cambios | Inventario |
| 32 | `bienes` | `ver-imagenes-de-bienes` | Ver galería de imágenes | Inventario |
| 33 | `catalogos` | `crear-almacenamientos` | Crear tipo de almacenamiento | Inventario |
| 34 | `catalogos` | `crear-categorias` | Crear categoría | Inventario |
| 35 | `catalogos` | `crear-dependencias` | Crear dependencia | Inventario |
| 36 | `catalogos` | `crear-estados` | Crear estado de bien | Inventario |
| 37 | `catalogos` | `crear-mantenimientos` | Crear tipo de mantenimiento | Inventario |
| 38 | `catalogos` | `crear-origenes` | Crear origen de adquisición | Inventario |
| 39 | `catalogos` | `crear-ubicaciones` | Crear ubicación física | Inventario |
| 40 | `catalogos` | `editar-almacenamientos` | Editar almacenamiento | Inventario |
| 41 | `catalogos` | `editar-categorias` | Editar categoría | Inventario |
| 42 | `catalogos` | `editar-dependencias` | Editar dependencia | Inventario |
| 43 | `catalogos` | `editar-estados` | Editar estado | Inventario |
| 44 | `catalogos` | `editar-mantenimientos` | Editar mantenimiento | Inventario |
| 45 | `catalogos` | `editar-origenes` | Editar origen | Inventario |
| 46 | `catalogos` | `editar-ubicaciones` | Editar ubicación | Inventario |
| 47 | `catalogos` | `eliminar-almacenamientos` | Eliminar almacenamiento | Inventario |
| 48 | `catalogos` | `eliminar-categorias` | Eliminar categoría | Inventario |
| 49 | `catalogos` | `eliminar-dependencias` | Eliminar dependencia | Inventario |
| 50 | `catalogos` | `eliminar-estados` | Eliminar estado | Inventario |
| 51 | `catalogos` | `eliminar-mantenimientos` | Eliminar mantenimiento | Inventario |
| 52 | `catalogos` | `eliminar-origenes` | Eliminar origen | Inventario |
| 53 | `catalogos` | `eliminar-ubicaciones` | Eliminar ubicación | Inventario |
| 54 | `catalogos` | `ver-almacenamientos` | Ver almacenamientos | Inventario |
| 55 | `catalogos` | `ver-categorias` | Ver categorías | Inventario |
| 56 | `catalogos` | `ver-dependencias` | Ver dependencias | Inventario |
| 57 | `catalogos` | `ver-estados` | Ver estados | Inventario |
| 58 | `catalogos` | `ver-mantenimientos` | Ver mantenimientos | Inventario |
| 59 | `catalogos` | `ver-origenes` | Ver orígenes | Inventario |
| 60 | `catalogos` | `ver-ubicaciones` | Ver ubicaciones | Inventario |
| 61 | `evaluacion-docente` | `ver-evaluacion-docente` | Acceder a evaluación docente | Futuro |
| 62 | `grupos` | `ver-grupos` | Acceder a módulo de grupos | Futuro |
| 63 | `mantenimientos` | `cancelar-mantenimientos-programados` | Cancelar/completar mantenimientos | Inventario |
| 64 | `mantenimientos` | `crear-mantenimientos-programados` | Programar nuevos mantenimientos | Inventario |
| 65 | `mantenimientos` | `editar-mantenimientos-programados` | Editar mantenimientos programados | Inventario |
| 66 | `mantenimientos` | `ver-mantenimientos-programados` | Ver agenda de mantenimientos | Inventario |
| 67 | `permisos` | `crear-permisos` | Crear nuevos permisos | User |
| 68 | `permisos` | `editar-permisos` | Editar permisos | User |
| 69 | `permisos` | `eliminar-permisos` | Eliminar permisos | User |
| 70 | `permisos` | `ver-permisos` | Ver catálogo de permisos | User |
| 71 | `responsables` | `asignar-responsables-bienes` | Asignar custodio | Inventario |
| 72 | `responsables` | `editar-responsables-bienes` | Editar responsables | Inventario |
| 73 | `responsables` | `transferir-responsables-bienes` | Transferir custodia | Inventario |
| 74 | `responsables` | `ver-responsables-bienes` | Ver panel de custodios | Inventario |
| 75 | `roles` | `asignar-permisos-a-roles` | Asignar/desasignar permisos a roles | User |
| 76 | `roles` | `crear-roles` | Crear nuevos roles | User |
| 77 | `roles` | `editar-roles` | Editar roles | User |
| 78 | `roles` | `eliminar-roles` | Eliminar roles | User |
| 79 | `roles` | `ver-roles` | Ver catálogo de roles | User |
| 80 | `ubicaciones` | `cambiar-ubicacion-bienes` | Registrar cambio de ubicación física | Inventario |
| 81 | `ubicaciones` | `ver-historial-ubicaciones-bienes` | Ver historial de ubicaciones | Inventario |
| 82 | `usuarios` | `crear-usuarios` | Registrar nuevos usuarios | User |
| 83 | `usuarios` | `editar-usuarios` | Editar datos de usuarios | User |
| 84 | `usuarios` | `eliminar-usuarios` | Eliminar usuarios | User |
| 85 | `usuarios` | `ver-usuarios` | Ver listado de usuarios | User |

---

## 5. Roles del Sistema — Matriz de Permisos

| Rol | Categorías de permisos asignadas |
|-----|----------------------------------|
| **Administrador** | Todos (85 permisos) |
| **Rectoría** | Todos excepto `roles` y `permisos` (puede ver roles/permisos pero no CRUD sobre ellos) |
| **Coordinación** | `user` (básico) + `bienes` (básico) |
| **Auxiliar** | `bienes` (básico) |
| **Docente** | `bienes` (básico) |
| **Estudiante** | Sin permisos asignados |
| **Invitado** | Sin permisos asignados |

---

## 6. Problemas Identificados

### 6.1 Críticos (afectan correctitud)

**P-001 — `permission_user` sin UNIQUE constraint**  
La tabla `permission_user` no tiene `UNIQUE(permission_id, user_id)`. Es posible asignar el mismo permiso directamente a un usuario más de una vez, resultando en filas duplicadas. `permission_role` fue corregido (IMPL-003) pero `permission_user` no.

**P-002 — `Role::hasPermission()` busca por `nombre`, no por `slug`**  
`Role::hasPermission($permissionName)` hace `.contains('nombre', $permissionName)` en lugar de `.contains('slug', $slug)`. Inconsistente con `User::hasPermission($slug)`. Si se llama directamente a `Role::hasPermission()`, fallará.

**P-003 — `CheckForzarCambioPassword` registrado dos veces**  
El middleware aparece en `app/Http/Kernel.php` Y en `bootstrap/app.php`. En Laravel 11 el Kernel es legacy — el middleware debería estar solo en `bootstrap/app.php`. La doble definición puede causar ejecución doble del middleware por request.

### 6.2 Performance

**P-004 — `hasPermission()` sin memoización (cache)**  
Cada llamada a `hasPermission()` ejecuta hasta 2 queries SQL. En componentes Livewire con múltiples verificaciones por render, esto puede significar 20-40 queries solo para autorización por ciclo de render.

### 6.3 Estructurales

**P-005 — Permisos definidos en múltiples fuentes**  
Los permisos se definen en: seeders CSV, migraciones de módulos (inline en `up()`), seeders PHP de módulos, y fixtures de tests. No existe una única fuente de verdad. Dificulta auditoría y mantenimiento.

**P-006 — CrudGenerator sin protección de acceso por módulo**  
Las rutas del módulo CrudGenerator solo tienen `auth` + `verified`, sin `app.access:{slug}` ni permisos específicos.

**P-007 — Enum `Capacidad` desconectado del RBAC activo**  
Existe `app/Auth/Capacidad.php` con 44 capacidades para APPSisGOE — el sistema de permisos planificado. Pero APPSisGOE ya usa Spatie Permission, no este enum. La clase existe pero no está integrada al RBAC en producción.

---

## 7. Evaluación Arquitectónica

### 7.1 Mantener en APPSisGOE

**El concepto de `es_principal` / Administrador Principal**  
Es un patrón de seguridad importante para sistemas IEE: garantiza que siempre exista un super-admin de recuperación que no puede ser accidentalmente bloqueado, modificado o degradado. APPSisGOE debe preservar este concepto bajo el nombre `es_principal` o equivalente.

**La arquitectura de 4 capas (defensa en profundidad)**  
Autenticación → Acceso al módulo → Permiso granular en ruta → Verificación en componente. Este patrón es correcto y debe mantenerse en APPSisGOE, posiblemente usando Spatie para capas 3 y 4.

**La tabla `auditoria_passwords`**  
El registro inmutable de operaciones administrativas sobre cuentas es un requerimiento de auditoría en instituciones educativas estatales. Preservar en APPSisGOE.

**El `Trait ProteccionAdminPrincipal`**  
El patrón de proteger al admin principal contra modificaciones accidentales es correcto. Reutilizar en APPSisGOE.

**`permission_role` con UNIQUE constraint**  
La corrección aplicada en IMPL-003 es el comportamiento correcto. APPSisGOE hereda esto automáticamente al usar Spatie.

### 7.2 Rediseñar en APPSisGOE

**`hasPermission()` sin cache**  
Implementar memoización: cachear el resultado por slug en una propiedad de la instancia User, o usar `Auth::user()->can()` de Spatie que ya memoiza automáticamente.

**Permisos en múltiples fuentes**  
APPSisGOE ya usa el enum `Capacidad` como fuente de verdad. Consolidar todos los permisos en ese enum y generar seeders a partir de él.

**`Role::hasPermission()` con nombre en lugar de slug**  
Corregir a `contains('slug', $slug)` o eliminar el método y usar solo `User::hasPermission()`.

### 7.3 Eliminar en APPSisGOE

**`roles.app_id`**  
La FK entre `roles` y `apps` creó una dependencia circular innecesaria. En APPSisGOE los roles son globales, no pertenecen a ninguna app. Eliminar el campo.

**Seeders CSV multi-archivo**  
Reemplazar por seeders PHP limpios que lean desde el enum `Capacidad`. Los CSV dispersos son difíciles de mantener y auditar.

---

## 8. Recomendación Final para APPSisGOE

### 8.1 ¿Debe APPSisGOE conservar esta arquitectura RBAC?

**Sí, en esencia — pero ya lo superó.**

APPSisGOE ya usa **Spatie Permission** (`spatie/laravel-permission`), que es exactamente la evolución correcta del RBAC de BhagamAppsModular:
- Elimina la necesidad de implementar `permission_user` / `permission_role` a mano
- El método `hasPermission()` con memoización automática resuelve P-004
- `@can('ver-bienes')` en Blade / `$this->authorize('ver-bienes')` en Controller son idiomáticos

**Lo que BhagamAppsModular aporta que APPSisGOE debe incorporar:**

1. **Concepto `es_principal`** — agregar campo `es_principal` a `users` en APPSisGOE
2. **`ProteccionAdminPrincipal` trait** — implementar protección equivalente
3. **`auditoria_passwords`** — crear tabla equivalente para operaciones administrativas
4. **CheckForzarCambioPassword** — middleware para forzar cambio de contraseña
5. **Conexión del enum `Capacidad` al sistema activo** — el enum ya existe pero no está vinculado al seeder de Spatie

### 8.2 Hoja de ruta

**Correcciones inmediatas en BhagamAppsModular:**
1. Agregar `UNIQUE(permission_id, user_id)` a `permission_user`
2. Corregir `Role::hasPermission()` para usar `slug`
3. Remover registro duplicado de `CheckForzarCambioPassword`

**Para APPSisGOE (nueva arquitectura):**
1. Mantener Spatie Permission como motor RBAC
2. Agregar `es_principal` a `users` + `ProteccionAdminPrincipal` trait
3. Crear `auditoria_passwords` tabla
4. Conectar enum `Capacidad` al `DatabaseSeeder` de Spatie
5. Implementar middleware `ModuloAccessMiddleware` (equivalente a `CheckAppAccess`)
6. Mantener las 4 capas de defensa en profundidad

---

*Referencia cruzada:*  
- *Para el módulo Apps (visibilidad, app_role, app_user): ver AUDIT-APPS-002-APPSisGOE.md*  
- *Para el módulo Inventario (flujos HMB/HEB, permisos específicos): ver AUDIT-INV-002-APPSisGOE.md*
