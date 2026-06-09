# ADR-AUTHORIZATION-002 — Rechazo de `ver-{slug}` como Capa 2 y Estandarización de `app.access:{slug}`

**Estado:** Propuesto — pendiente de aprobación
**Fecha:** 2026-06-08
**Contexto:** PLAN-AUTH-001 — análisis de arquitectura de autorización
**Relacionado con:** ADR-008, ADR-AUTHORIZATION-001, IMPL-013

---

## Problema

BhagamAppsModular tiene una inconsistencia entre módulos en cómo se enforce el acceso de
Capa 2 (entrada a módulo):

- **Inventario** y **User**: usan `app.access:{slug}` — fuente de verdad: `App::visiblesPara()`
- **Apps** (`/apps/admin`): usa `permission:ver-apps` — fuente de verdad: `permission_role`

Adicionalmente, la propuesta de estandarizar `ver-{slug}` (ej. `ver-inventario`,
`ver-user`, `ver-academico`) como permiso mínimo obligatorio de módulo requiere una
decisión arquitectónica formal.

---

## Contexto

### Los tres sistemas paralelos (resumen)

| Sistema | Mecanismo | Fuente de verdad | Estado |
|---------|-----------|-----------------|--------|
| 1 | `CheckPermission` middleware (`permission:slug`) | `permission_role` + `permission_user` | Activo |
| 2 | `Gate::define()` en `AuthServiceProvider` | Mezcla hasPermission + hardcoded | Activo, problema |
| 3 | `App::visiblesPara()` + `CheckAppAccess` | `app_role` + `app_user` | Activo, parcial |

### Estado del middleware `app.access:{slug}`

`CheckAppAccess` fue implementado en IMPL-013 (commit cce5677). Está registrado en
`app/Http/Kernel.php` y activo en Inventario y User. Su lógica:

```php
if (! App::visiblesPara($user)->contains('slug', $slug)) {
    abort(403);
}
```

`App::visiblesPara()` incluye caché por versión global (TTL 300s), invalidable con
`cache()->increment('apps.cache_version')`.

### Lo que ADR-008 ya decidió

ADR-008 (estado: Aceptado) establece en DD-003:

> "El enforcement de acceso a módulos es responsabilidad de Apps. El mecanismo debe basarse
> en `App::visiblesPara($user)`, no en permisos individuales (`permission:ver-modulo`).
> Los permisos individuales quedan reservados exclusivamente para la Capa 3."

Esta ADR formaliza y extiende esa decisión con análisis de impacto adicional.

---

## Decisiones

### D1 — `ver-{slug}` como Capa 2 queda RECHAZADO

**Decisión:** Los permisos con patrón `ver-{slug-de-módulo}` (por ejemplo, `ver-inventario`,
`ver-user`, `ver-academico`) NO deben usarse como mecanismo de entrada a módulos (Capa 2).

**Razones:**

1. **Duplicación de gestión.** El administrador tendría que mantener `app_role` (para
   dashboard) Y `permission_role` (`ver-{slug}`) sincronizados para el mismo resultado.
   La incoherencia sería la norma, no la excepción.

2. **Rompe la fuente única de verdad.** `App::visiblesPara()` es la única fuente aprobada
   (ADR-008 DD-001). Introducir `permission:ver-{slug}` como Capa 2 crea una fuente
   secundaria con responsabilidad solapada.

3. **Contradice ADR-008 DD-003.** Un ADR aceptado debe respetarse hasta que sea
   formalmente reemplazado.

4. **`app.access:{slug}` ya funciona.** Inventario y User tienen acceso protegido por
   `App::visiblesPara()` y no han requerido `ver-{slug}` para Capa 2.

5. **Riesgo de escenario incoherente.** Si un rol tiene `inventario` en `app_role` pero
   no tiene `ver-inventario` en `permission_role`, el usuario ve el ícono en el dashboard
   pero recibe 403 al entrar. Ese escenario se elimina usando exclusivamente `app.access`.

---

### D2 — `app.access:{slug}` es el estándar universal de Capa 2

**Decisión:** Todos los módulos funcionales de BhagamAppsModular deben usar el middleware
`app.access:{slug-registrado-en-apps}` como mecanismo de Capa 2.

**Contrato:**

```
Capa 2 — Acceso a módulo:
  Middleware: app.access:{slug}
  Fuente: App::visiblesPara($user) → app_role (por rol) + app_user (individual)
  Comportamiento ante fallo: abort(403)
  Gestión de acceso: Panel /apps/admin → tabla app_role
```

**Regla de cumplimiento:** Todo módulo nuevo registrado en `apps` debe declarar en
`routes/web.php`:

```php
Route::middleware(['web', 'auth', 'app.access:{slug}'])
    ->prefix('{slug}')
    ->group(function () { ... });
```

---

### D3 — Módulo Apps es una excepción válida a D2

**Decisión:** El módulo Apps NO agrega `app.access:apps` a sus rutas de administración.

**Razón:** El módulo Apps es el *Application Registry* — el sistema que mantiene la tabla
`apps` y controla qué módulos existen. Aplicarle `app.access:apps` lo haría
auto-dependiente: para gestionar el acceso a apps, el administrador necesitaría primero
tener acceso a apps en el registro que él mismo administra.

**Acceso a `/apps/admin`** sigue protegido por `permission:administrar-apps` (Capa 3 —
funcionalidad administrativa). Esto es correcto y no es una incoherencia.

**Acceso al dashboard de apps** está en `HomeController::index()` con `App::visiblesPara()`
sin middleware adicional — correcto también.

---

### D4 — Rol de los permisos `ver-{nombre-sección}` existentes

**Decisión:** Los permisos `ver-bienes`, `ver-actas-de-entrega`, `ver-usuarios`,
`ver-apps`, etc. son permisos de Capa 3 (acceso a secciones específicas dentro del módulo)
y deben permanecer como están.

**Mapa de capas con nomenclatura aclarada:**

```
CAPA 1 — VISIBILIDAD (dashboard + menú)
  Fuente: App::visiblesPara($user)
  No requiere permiso explícito

CAPA 2 — ACCESO AL MÓDULO (enforcement de URL)
  Middleware: app.access:{slug}
  No requiere permiso en tabla — solo app_role

CAPA 3 — ACCIONES DENTRO DEL MÓDULO
  Middleware: permission:{slug-de-acción}   ← ver-bienes, ver-usuarios, etc.
  Fuente: permission_role + permission_user
```

---

### D5 — Gates hardcoded deben migrarse gradualmente a `hasPermission()`

**Decisión:** Los `Gate::define()` con nombres de roles hardcoded en `AuthServiceProvider`
deben ser reemplazados por permisos en BD. La migración es gradual, módulo por módulo.

**Catálogo de Gates a migrar:**

| Gate hardcoded | Permiso propuesto | Categoría |
|----------------|-------------------|-----------|
| `usuarios.user` | `administrar-accesos` | accesos |
| `admin.grupos` | `administrar-grupos` | grupos |
| `admin.evaldoc` | `administrar-evaldoc` | evaldoc |
| `admin.biblioteca` | `administrar-biblioteca` | biblioteca |

**Patrón de migración:**

```php
// Antes (hardcoded):
Gate::define('usuarios.user', fn($user) =>
    in_array($user->role->nombre, ['Administrador', 'Rector', 'Coordinador'])
);

// Después (en BD):
Gate::define('usuarios.user', fn($user) =>
    $user->hasPermission('administrar-accesos')
);
```

El permiso se asigna en `permission_role` a los roles que corresponda.
Los Gates existentes siguen funcionando durante la migración.

**Regla para módulos nuevos:** Ningún Gate nuevo puede usar nombres de roles hardcoded.
Siempre debe delegarse a `hasPermission()`.

---

### D6 — Gates de visibilidad de menú se eliminan con IMPL-013

**Decisión:** Los Gates `can:ver-bienes`, `can:ver-actas-de-entrega`, `can:usuarios.user`,
`can:ver-apps` en `config/adminlte.php` son una solución transitoria. Cuando IMPL-013
implemente el menú dinámico desde `App::visiblesPara()`, estos Gates serán evaluados
para eliminación.

**Condición de eliminación:** Solo cuando IMPL-013 esté completo y el menú lateral refleje
correctamente los módulos de `App::visiblesPara($user)`.

---

### D7 — `roles.app_id` FK CASCADE se elimina antes de operar con apps en producción

**Decisión:** La FK `roles.app_id` con `CASCADE DELETE` es un riesgo de datos (DT-001).
Debe cambiarse a `onDelete('set null')` mediante migración antes de que se eliminen apps
en producción.

**Semántica de `roles.app_id` post-migración:** Campo de contexto/agrupación.
Indica "este rol fue creado en el contexto de esta app" — para clasificación en el
panel de administración de roles. Sin impacto en autorización.

**Implementación:** IMPL-AUTH-003 — requiere análisis de datos existentes antes de ejecutar.

---

## Resumen de decisiones

| ID | Decisión | Estado |
|----|----------|--------|
| D1 | `ver-{slug}` como Capa 2 → RECHAZADO | Propuesto |
| D2 | `app.access:{slug}` como estándar universal de Capa 2 | Propuesto |
| D3 | Módulo Apps es excepción válida a D2 | Propuesto |
| D4 | `ver-{nombre-sección}` permanece como Capa 3 | Propuesto |
| D5 | Gates hardcoded → migración gradual a hasPermission | Propuesto |
| D6 | Gates de menú → eliminación condicionada a IMPL-013 | Propuesto |
| D7 | `roles.app_id` FK CASCADE → SET NULL (IMPL-AUTH-003) | Propuesto |

---

## Arquitectura objetivo confirmada (post-aprobación)

```
USUARIO AUTENTICADO
  │
  ▼
¿La app está en App::visiblesPara($user)?         ← Capa 1 (dashboard)
  │
  ├── No → App no aparece en dashboard ni menú
  │
  └── Sí → App visible en dashboard
              │
              ▼ [clic → navega al módulo]
         middleware app.access:{slug}               ← Capa 2 (enforcement)
              │
              ├── No → abort(403)
              │
              └── Sí → Entra al módulo
                          │
                          ▼ [acciones dentro del módulo]
                     permission:{acción}             ← Capa 3 (CRUD/flujos)
                     hasPermission('acción')
                          │
                          ├── No → 403 / UI oculta
                          └── Sí → Ejecuta la acción
```

---

## Alternativas consideradas

### Alternativa A — Adoptar `ver-{slug}` como Capa 2 (rechazada)

**Descripción:** Crear `ver-inventario`, `ver-user`, `ver-academico`, etc. como permisos
de entrada. Middleware `permission:ver-{slug}` en todas las rutas de módulo.

**Rechazada porque:**
- Requiere doble gestión (`app_role` + `permission_role`) para el mismo resultado
- Contradice ADR-008 DD-003 (aprobado)
- `app.access:{slug}` ya funciona en producción para dos módulos
- Crea riesgo de incoherencia visible vs. acceso real

### Alternativa B — Unificar en `permission:ver-{slug}` y eliminar `app.access` (rechazada)

**Descripción:** Reemplazar `app.access:{slug}` por `permission:ver-{slug}` y eliminar
el middleware `CheckAppAccess`.

**Rechazada porque:**
- Elimina `App::visiblesPara()` como fuente única de verdad
- Desactiva el caché de visibilidad ya implementado
- Requiere migración mayor de código existente sin beneficio funcional
- El panel `/apps/admin` pierde su utilidad como único punto de gestión de accesos

### Alternativa C — `app.access:{slug}` universal (adoptada)

Apps controla visibilidad (Capa 1) y acceso a módulos (Capa 2) desde `app_role`.
RBAC controla autorización funcional (Capa 3) desde `permission_role`.
Las capas no se superponen. El administrador gestiona cada capa desde su herramienta natural.

---

## Consecuencias

### Positivas

- `App::visiblesPara()` se confirma como fuente única de verdad para Capas 1 y 2
- Administrador gestiona acceso a módulos desde un solo lugar: `/apps/admin`
- No se requieren permisos adicionales por módulo para Capa 2
- Consistencia entre lo que el usuario ve (Capa 1) y lo que puede acceder (Capa 2)
- Escalable: módulos futuros solo necesitan `app.access:{slug}` en sus rutas

### Negativas

- El menú sidebar en AdminLTE sigue siendo estático hasta IMPL-013
- Gates hardcoded permanecen hasta que se ejecute IMPL-AUTH-002 módulo por módulo
- La inconsistencia de Apps (`permission:ver-apps` en lugar de `app.access:apps`) es
  intencional (D3) pero requiere documentación clara para nuevos desarrolladores

---

## Próximas implementaciones

| Ticket | Descripción | Prioridad | Precondición |
|--------|-------------|-----------|--------------|
| IMPL-AUTH-002 | Migrar Gates hardcoded a `hasPermission()` | Media | Este ADR aprobado |
| IMPL-AUTH-003 | `roles.app_id` FK → SET NULL | Media | Análisis de datos en BD |
| IMPL-013 | Menú dinámico desde `App::visiblesPara()` | Alta | DP-001, DP-002 (ADR-008) |
