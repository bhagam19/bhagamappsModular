# ADR-003 — Arquitectura de Autorización

| Campo | Valor |
|-------|-------|
| **ID** | ADR-003 |
| **Título** | Arquitectura de Autorización: Capas, RBAC, Spatie, Gates y Admin Principal |
| **Estado** | Aprobado |
| **Fecha** | 2026-06-14 |
| **Autores** | Equipo APPSisGOE |
| **Revisores** | — |
| **Documentos base** | AUDIT-RBAC-001.md · ARCH-ANALYSIS-001-APPSisGOE.md |
| **Depende de** | ADR-001 (Core Mínimo) · ADR-002 (Gobernanza de Módulos) |

---

## 1. Estado

**Aprobado.** Esta decisión define la arquitectura de autorización oficial de APPSisGOE. Ningún módulo puede implementar un mecanismo alternativo de autorización.

---

## 2. Contexto

BhagamAppsModular implementó un sistema RBAC propio que evolucionó durante varios ciclos de desarrollo. La auditoría AUDIT-RBAC-001 documentó:

- **10 tablas** involucradas en autorización
- **85 permisos** en 17 categorías
- **7 roles** institucionales
- **4 capas de defensa** en profundidad
- **60 gates** definidos manualmente en `AuthServiceProvider`
- **7 problemas identificados** (P-001 a P-007), 3 críticos y 1 de performance

La arquitectura de BhagamApps demostró que el patrón de 4 capas es correcto. Sin embargo, la implementación acumuló deuda técnica: implementación manual de tablas que tienen soluciones establecidas (Spatie), inconsistencias entre métodos (nombre vs slug), permisos dispersos en múltiples fuentes, y problemas de performance por ausencia de memoización.

APPSisGOE ya usa `spatie/laravel-permission`, lo que resuelve automáticamente los problemas P-001 (UNIQUE en permission_user), P-002 (consistencia nombre/slug), y P-004 (memoización). Esta ADR formaliza qué conservar, qué reemplazar, y cómo se integran las piezas.

---

## 3. Problema

APPSisGOE hereda el diseño conceptual de BhagamApps pero necesita decisiones formales sobre:

1. ¿Cuántas capas de autorización existen y cuál es la responsabilidad de cada una?
2. ¿Qué rol juega Spatie Permission y qué no hace por sí solo?
3. ¿Cómo se gestiona el catálogo de permisos sin dispersión?
4. ¿Cuál es el rol de los Gates de Laravel?
5. ¿Cómo se implementa `es_principal` y qué operaciones lo requieren?
6. ¿Cómo se verifica un permiso a nivel de componente?

---

## 4. Alternativas consideradas

### Alternativa A — RBAC completamente personalizado (como BhagamApps)

Mantener la implementación custom con `permission_role`, `permission_user`, `hasPermission()`.

**Rechazada porque:** Reproduce los problemas P-001 a P-007 documentados en AUDIT-RBAC-001. Spatie ya resuelve exactamente este dominio. No hay ventaja en reimplementarlo. (ARCH-ANALYSIS-001 §4.2)

### Alternativa B — Solo Spatie, sin capas adicionales

Usar Spatie y confiar en que `$user->can()` sea suficiente.

**Rechazada porque:** Spatie gestiona permisos pero no el acceso por módulo (CORE-3). Un usuario puede tener `ver-bienes` pero si Inventario está desactivado, no debe acceder. Spatie no conoce el estado de los módulos. La capa 2 (ModuloAccessMiddleware) es responsabilidad del CORE-3 y no puede ser reemplazada por Spatie. (AUDIT-RBAC-001 §3, ARCH-ANALYSIS-001 §4.3)

### Alternativa C — Spatie + capas adicionales + es_principal + Capacidad enum (decisión adoptada)

Usar Spatie como motor de autorización granular, implementar las capas de módulo y componente en el CORE, y usar el concepto `es_principal` para operaciones críticas de sistema.

---

## 5. Decisión

### 5.1 Las cuatro capas de autorización

Toda request en APPSisGOE pasa por las cuatro capas en secuencia. Cada capa falla con 403/redirect si el usuario no cumple la condición. Si una capa no falla, la request continúa a la siguiente.

```
HTTP Request
    │
    ▼
┌─────────────────────────────────────────────────────────────────┐
│  CAPA 1 — AUTENTICACIÓN (Fortify + CORE-1)                      │
│  Responsabilidad: verificar que el usuario existe y está activo │
│                                                                 │
│  $user = request()->user()                                       │
│  ├─ null              → redirect('/login')                      │
│  ├─ bloqueado = true  → logout() + redirect con error          │
│  ├─ email_verified_at IS NULL → redirect('/email/verify')      │
│  └─ forzar_cambio_password = true → redirect('/cambiar')       │
└─────────────────────────────────────────────────────────────────┘
    │
    ▼
┌─────────────────────────────────────────────────────────────────┐
│  CAPA 2 — ACCESO AL MÓDULO (ModuloAccessMiddleware + CORE-3)   │
│  Responsabilidad: verificar que el módulo es visible al usuario │
│  Alias: modulo.access:{key}                                     │
│                                                                 │
│  ModuleVisibilityService::visiblesPara($user)                   │
│  ├─ caché versioned (TTL 300s)                                  │
│  └─ .contains('key', {key})                                     │
│      ├─ true  → continúa                                        │
│      └─ false → abort(403, "Sin acceso al módulo {key}")       │
└─────────────────────────────────────────────────────────────────┘
    │
    ▼
┌─────────────────────────────────────────────────────────────────┐
│  CAPA 3 — PERMISO GRANULAR (Spatie Permission + CORE-2)        │
│  Responsabilidad: verificar que el usuario puede hacer la acción│
│  Alias: can:{capacidad}                                         │
│                                                                 │
│  $user->can(Capacidad::VerBienes->value)                        │
│  ├─ Spatie revisa: model_has_roles + role_has_permissions       │
│  ├─ Memoizado automáticamente en la instancia User              │
│  ├─ true  → continúa                                            │
│  └─ false → abort(403) o redirect según contexto               │
└─────────────────────────────────────────────────────────────────┘
    │
    ▼
┌─────────────────────────────────────────────────────────────────┐
│  CAPA 4 — VERIFICACIÓN EN ACCIÓN (Actions / Livewire)           │
│  Responsabilidad: verificar el permiso en el punto de ejecución │
│  (defensa de profundidad — previene bypasses de las capas 2-3)  │
│                                                                 │
│  $this->authorize(Capacidad::EditarBienes->value);              │
│  // o en Livewire:                                              │
│  abort_if(!$user->can(Capacidad::EditarBienes->value), 403);   │
│                                                                 │
│  Para operaciones críticas de sistema (gates dobles):           │
│  Gate::allows('restaurar-backups')                              │
│  = can('restaurar-backups') && $user->es_principal             │
└─────────────────────────────────────────────────────────────────┘
    │
    ▼
ACCESO CONCEDIDO → Action / Controller / Livewire procesa
```

**Invariante de las 4 capas:**
- La capa 1 es obligatoria para cualquier request autenticado
- La capa 2 es obligatoria para cualquier ruta de módulo
- La capa 3 es obligatoria para cualquier ruta que requiera permiso específico
- La capa 4 es obligatoria en toda Action que modifique estado

---

### 5.2 Papel de Spatie Permission

Spatie (`spatie/laravel-permission`) es el **motor de autorización granular** de APPSisGOE. Gestiona:

- Asignación de roles a usuarios (`model_has_roles`)
- Asignación de permisos a roles (`role_has_permissions`)
- Asignación directa de permisos a usuarios (`model_has_permissions`)
- Verificación con memoización: `$user->can($slug)` — el resultado se cachea en la instancia User durante la request
- Integración nativa con `Gate::before()`, `@can()` en Blade, `$this->authorize()` en Controllers

**Lo que Spatie NO hace (y el CORE suple):**
- No verifica el acceso a módulos por rol/usuario (eso es CORE-3)
- No implementa `es_principal` ni gates con doble condición (eso es CORE-6)
- No protege contra modificación del admin principal (eso es ProteccionAdminPrincipal trait)
- No registra operaciones administrativas en `auditoria_passwords` (eso es CORE-4)

---

### 5.3 El enum `Capacidad` como única fuente de verdad

**Problema de BhagamApps:** Los 85 permisos se definían en migraciones inline, seeders CSV y seeders PHP — sin única fuente de verdad. (AUDIT-RBAC-001 §6.3, P-005)

**Decisión:** El enum `Capacidad` (ya existente en `app/Auth/Capacidad.php`) es la **única fuente de verdad** de todos los permisos del sistema.

**Reglas:**
1. Agregar un permiso = agregar un caso al enum `Capacidad`
2. El `PermissionSeeder` itera `Capacidad::cases()` y upserta los registros en la tabla de Spatie
3. Las migraciones no contienen definiciones de permisos (solo esquema de tablas)
4. Los módulos no tienen seeders de permisos propios — declaran sus permisos en `module.json[capacidades]` como documentación, pero el seeder los crea desde el enum
5. Los tests pueden verificar `count(Capacidad::cases()) === Permission::count()`

**Estructura del enum:**

```php
enum Capacidad: string
{
    // Módulo: bienes
    case VerBienes    = 'ver-bienes';
    case CrearBienes  = 'crear-bienes';
    case EditarBienes = 'editar-bienes';
    case EliminarBienes = 'eliminar-bienes';

    // Módulo: modulos (CORE-3)
    case ModulosVer        = 'modulos:ver';
    case ModulosInstalar   = 'modulos:instalar';
    case ModulosActivar    = 'modulos:activar';
    case ModulosAsignarRol = 'modulos:asignar_rol';

    // ... resto de capacidades
}
```

**Uso en código:**

```php
// En middleware
->middleware('can:' . Capacidad::VerBienes->value)

// En Action
$this->authorize(Capacidad::EditarBienes->value);

// En Livewire
abort_if(!auth()->user()->can(Capacidad::EliminarBienes->value), 403);

// En Blade
@can(App\Auth\Capacidad::VerBienes->value)
```

---

### 5.4 Permisos: convención de slugs

**Formato:** kebab-case para permisos de módulo, `namespace:accion` para permisos del CORE.

| Scope | Formato | Ejemplo |
|-------|---------|---------|
| Módulos de negocio | `{verbo}-{entidad}` | `ver-bienes`, `editar-bienes` |
| CORE (módulos) | `modulos:{accion}` | `modulos:instalar`, `modulos:activar` |
| CORE (admin) | `admin:{accion}` | `admin:ver-activity-log` |

**Razón:** Los slugs son identificadores estables que no cambian aunque el nombre legible cambie. Un slug siempre se referencia como constante del enum, no como string literal en el código. (ARCH-ANALYSIS-001 ACIERTO vs ERROR-003)

---

### 5.5 Los 7 roles institucionales

Los roles de APPSisGOE reflejan la estructura orgánica de una IEE colombiana. No son técnicos — son organizacionales. Su definición se obtiene de BhagamApps y se valida como correcta.

| ID esperado | Nombre | Descripción | Permisos base |
|-------------|--------|-------------|---------------|
| 1 | Administrador | Acceso completo al sistema | Todos los permisos |
| 2 | Rectoría | Orienta todos los procesos | Todos excepto gestión de roles/permisos CRUD |
| 3 | Coordinación | Supervisa procesos académicos/administrativos | Usuarios (básico) + bienes (básico) |
| 4 | Auxiliar | Apoya procesos académicos/administrativos | Bienes (básico) |
| 5 | Docente | Imparte clases y evalúa estudiantes | Bienes (básico) |
| 6 | Estudiante | Acceso a contenidos académicos | Sin permisos por defecto |
| 7 | Invitado | Acceso limitado para pruebas | Sin permisos por defecto |

**Nota:** La asignación de permisos a roles se gestiona en el `RoleSeeder` y en la UI de Rectoría/Coordinación.

---

### 5.6 El concepto `es_principal` — Administrador Principal

**Definición:** El campo `es_principal` en `users` marca al Administrador Principal: el único usuario del sistema que:
- No puede ser bloqueado por otro administrador
- No puede tener su rol cambiado por otro administrador
- No puede ser eliminado
- Es el único autorizado para las 3 operaciones críticas de sistema

**Invariante:** Exactamente 1 usuario tiene `es_principal = true` en todo momento. La migración inicial asigna `es_principal` al primer usuario con rol Administrador.

**Verificación del invariante:**

```php
// En suite de tests de integración:
public function test_exactamente_un_admin_principal(): void
{
    $this->assertSame(1, User::where('es_principal', true)->count());
}
```

**Gates con doble condición:**

Los gates que requieren tanto un permiso como `es_principal` se definen en el `AuthServiceProvider`. Solo 3 operaciones requieren esta doble condición:

```php
// En AuthServiceProvider::boot()
Gate::define('restaurar-backups', fn(User $user) =>
    $user->can('restaurar-backups') && $user->es_principal
);

Gate::define('importar-snapshot-backup', fn(User $user) =>
    $user->can('importar-snapshot-backup') && $user->es_principal
);

Gate::define('ver-activity-log', fn(User $user) =>
    $user->can('ver-activity-log') && $user->es_principal
);
```

**Uso en código:**

```php
abort_unless(Gate::allows('restaurar-backups'), 403);
```

**Criterio para añadir un gate con doble condición:** La operación debe ser potencialmente destructiva y no reversible (restaurar backup sobreescribe datos; importar snapshot puede comprometer integridad; activity log revela información de seguridad). El criterio no es arbitrario — se requiere aprobación arquitectónica explícita.

---

### 5.7 Trait `ProteccionAdminPrincipal`

El trait previene que cualquier componente (Livewire, Action, Controller) modifique al usuario con `es_principal = true`.

**Uso obligatorio en:**
- Cambio de rol de usuario
- Cambio de password de usuario (administrativo)
- Bloqueo de usuario
- Desbloqueo de usuario
- Modificación de datos personales (nombres, email, userID)
- Eliminación de usuario

**Comportamiento:**

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

---

### 5.8 Gates de Laravel — uso restringido

**Decisión:** Los Gates de Laravel se usan **únicamente** para las 3 operaciones críticas con doble condición (`es_principal` + permiso). No se crean gates para permisos estándar.

**Razón:** BhagamApps definió 60 gates en `AuthServiceProvider`, todos delegando a `hasPermission()` — boilerplate puro sin valor añadido. Con Spatie, `$user->can($slug)` ya funciona directamente sin necesidad de definir gates. (ARCH-ANALYSIS-001 §4.2, ERROR tabla)

**Patrón correcto en APPSisGOE:**

```php
// ✅ Correcto — Spatie maneja esto directamente
$user->can(Capacidad::VerBienes->value)

// ✅ Correcto — Gate solo para operaciones críticas con es_principal
Gate::allows('restaurar-backups')

// ❌ Incorrecto — Gate redundante que solo delega a Spatie
Gate::define('ver-bienes', fn($user) => $user->can('ver-bienes'))
```

---

### 5.9 Verificación de permisos por contexto

| Contexto | Mecanismo | Ejemplo |
|---------|-----------|---------|
| Middleware de ruta | `can:{slug}` | `middleware('can:ver-bienes')` |
| Controller / Action | `$this->authorize()` | `$this->authorize(Capacidad::EditarBienes->value)` |
| Livewire component | `abort_if(!$user->can(...), 403)` | `abort_if(!$user->can(Capacidad::EliminarBienes->value), 403)` |
| Blade template | `@can(...)` | `@can(App\Auth\Capacidad::VerBienes->value)` |
| Operaciones críticas | `Gate::allows(...)` | `abort_unless(Gate::allows('restaurar-backups'), 403)` |

---

### 5.10 Tabla de decisiones: BhagamApps vs APPSisGOE

| Componente BhagamApps | Decisión | Implementación APPSisGOE |
|----------------------|---------|--------------------------|
| `permission_role` manual | Reemplazar | Spatie `role_has_permissions` |
| `permission_user` manual (sin UNIQUE) | Reemplazar | Spatie `model_has_permissions` (con constraints) |
| `User::hasPermission($slug)` (2 queries) | Reemplazar | `$user->can($slug)` de Spatie (memoizado) |
| `Role::hasPermission($nombre)` (inconsistente) | Eliminar | No tiene equivalente — usar `$user->can($slug)` |
| 60 gates delegando a hasPermission | Eliminar | No tienen equivalente — usar `$user->can()` directamente |
| 3 gates con doble condición | Conservar | 3 gates idénticos en `AuthServiceProvider` |
| Permisos en CSV / migraciones inline | Reemplazar | `Capacidad` enum → `PermissionSeeder` |
| 7 roles institucionales | Conservar | `RoleSeeder` desde enum de roles |
| 4 capas de autorización | Conservar y formalizar | Documentadas en esta ADR, implementadas en CORE |
| `es_principal` concepto | Conservar | Campo en `users`, gates, trait |
| `ProteccionAdminPrincipal` trait | Conservar | Trait en CORE, obligatorio en módulos de usuario |
| `auditoria_passwords` tabla | Conservar | CORE-4, tabla solo INSERT |
| `CheckForzarCambioPassword` middleware | Conservar | Un único punto de registro en `bootstrap/app.php` |
| `CheckAppAccess` middleware | Reemplazar | `ModuloAccessMiddleware` en CORE-3 |
| `roles.app_id` FK | Eliminar | Los roles no tienen FK hacia módulos |

---

## 6. Consecuencias

### Positivas
- Spatie elimina automáticamente P-001, P-002, P-004 de BhagamApps
- El enum `Capacidad` elimina P-005 — existe una única fuente de verdad
- La arquitectura de 4 capas formalizada previene bypasses de seguridad
- `es_principal` garantiza siempre un admin de recuperación
- Los permisos son constantes tipadas del enum — no strings literales dispersos

### Negativas / Trade-offs
- Spatie añade dependencia de un paquete externo al CORE. Si Spatie cambia su API, el CORE se ve afectado
- El enum `Capacidad` puede crecer significativamente con cada módulo — requiere organización disciplinada por namespaces
- La doble verificación (middleware capa 3 + capa 4 en Action) implica que Spatie se consulta dos veces por request — aceptable dado que memoiza la primera consulta

### Restricciones que impone esta decisión
- **Ningún módulo puede implementar su propio sistema de permisos.** Todo usa Spatie + Capacidad enum.
- **Las 4 capas son obligatorias en todo módulo.** No se puede omitir la capa 2 (ModuloAccessMiddleware) ni la capa 4 (verificación en Action).
- **Gates solo para operaciones con `es_principal`.** No crear gates para permisos estándar.
- **El enum `Capacidad` es la fuente de verdad.** Agregar un permiso fuera del enum es una violación de esta ADR.
- **`ProteccionAdminPrincipal` es obligatorio** en cualquier componente que modifique datos de un usuario.

---

## 7. Riesgos

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|-------------|---------|------------|
| Desarrollador agrega permiso como string literal (no enum) | Alta | Bajo | Code review; `grep -r "can('" --include="*.php"` en CI detecta strings que no usen el enum |
| Bug en Spatie afecta autorización globalmente | Muy baja | Muy alto | Pinear versión de Spatie; suite de tests de autorización en CI |
| `es_principal` queda sin usuario asignado tras migración | Muy baja | Muy alto | Test de invariante en CI; la migración inicial lo garantiza |
| Módulo bypasea capa 4 (no verifica permiso en Action) | Media | Medio | Code review; plantilla base de Action incluye `$this->authorize()` |
| Acumulación de permisos en `Capacidad` sin orden | Alta | Bajo | Organizar por namespace; revisión en PR de nuevas capacidades |

---

## 8. Justificación basada en evidencia

| Afirmación | Evidencia |
|-----------|-----------|
| 4 capas son necesarias (no solo 1 o 2) | AUDIT-RBAC-001 §3: el flujo completo muestra que cada capa intercepta un tipo diferente de acceso no autorizado. Una sola capa no es suficiente. |
| Spatie reemplaza la implementación manual | AUDIT-RBAC-001 §6.1: P-001 (sin UNIQUE en permission_user), P-004 (sin memoización) — Spatie resuelve ambos. ARCH-ANALYSIS-001 §4.2. |
| `Capacidad` enum elimina la dispersión | AUDIT-RBAC-001 §6.3 P-005: "Permisos definidos en múltiples fuentes... No existe una única fuente de verdad." |
| Gates solo para operaciones críticas | ARCH-ANALYSIS-001 ERROR-004 / §4.2: "60 gates definidos manualmente, todos delegando a hasPermission() — boilerplate masivo." |
| `es_principal` tiene valor de seguridad real | AUDIT-RBAC-001 §2.9: "Solo puede existir uno. Garantiza que siempre exista un super-admin de recuperación." ARCH-ANALYSIS-001 ACIERTO-004. |
| `Role::hasPermission($nombre)` debe eliminarse | AUDIT-RBAC-001 §2.2 P-002: "Busca por 'nombre' mientras User::hasPermission() busca por 'slug'. Son dos sistemas distintos." |
| La doble condición `can + es_principal` es arquitectónicamente correcta | AUDIT-RBAC-001 §2.8: las 3 operaciones críticas (restaurar backups, importar snapshot, ver activity log) justifican la doble condición por su naturaleza destructiva e irreversible. |

---

*Decisiones relacionadas: ADR-001 (Core Mínimo) · ADR-002 (Gobernanza de Módulos) · ADR-004 (Dominio Inventario) · ADR-005 (Versionado)*
