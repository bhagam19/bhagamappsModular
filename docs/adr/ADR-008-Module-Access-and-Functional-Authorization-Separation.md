# ADR-008 — Module Access and Functional Authorization Separation

## Estado

Aceptado

## Fecha

2026-06-08

## Tipo

Architecture ADR

## Relacionado con

* ADR-APPS-001 — Arquitectura del Módulo Central de Aplicaciones
* ADR-AUTHORIZATION-001 — Arquitectura Definitiva de Autorización (Propuesto)
* ADR-005 — Documentation and Repository Governance
* ADR-006 — Agent Responsibilities and Delivery Contracts
* AUDIT-APPS-002 — Auditoría funcional del módulo Apps
* AUDIT-APPS-003 — Apps as Module Access Registry (origen de esta decisión)
* IMPL-APPS-002 — Protección del panel de administración Apps

---

## Contexto

BhagamAppsModular ha evolucionado hacia una arquitectura multiaplicación donde los módulos
funcionales son registrados dentro del módulo Apps.

Actualmente existen:

* Tabla `apps` — catálogo de módulos de la plataforma
* Tabla `app_role` — asignación de apps a roles
* Tabla `app_user` — asignación directa de apps a usuarios
* Método `App::visiblesPara($user)` — fuente de apps visibles por usuario
* Dashboard "Mis Aplicaciones" — construido exclusivamente desde `visiblesPara()`
* Sistema RBAC personalizado basado en Roles y Permissions

AUDIT-APPS-003 concluyó que el módulo Apps ya controla efectivamente la visibilidad de
aplicaciones para los usuarios, mientras que el sistema RBAC controla las acciones
internas de cada módulo. Sin embargo, se identificó una brecha arquitectónica:

```
Apps  →  controla visibilidad  (implementado)
Apps  →  controla acceso       (no implementado)
RBAC  →  controla autorización funcional (implementado)
```

Esta brecha genera escenarios donde un usuario puede no visualizar una aplicación pero
acceder a ella mediante URL directa, puesto que el enforcement de acceso a módulos
reside actualmente en el sistema de permisos individuales (`permission:slug`) y no en
una capa de acceso a módulo formalmente controlada por Apps.

---

## Problema

La plataforma requiere una separación clara entre:

1. **Visibilidad de aplicaciones** — ¿qué apps ve el usuario en el dashboard y el menú?
2. **Acceso a módulos** — ¿puede el usuario entrar a este módulo?
3. **Autorización funcional** — ¿puede el usuario ejecutar esta acción dentro del módulo?

Estas tres responsabilidades no están formalmente documentadas ni enforceadas de forma
consistente. La ausencia de esta separación genera:

* Posibilidad de acceso por URL directa a módulos no visibles en el dashboard
* Inconsistencia entre lo que el usuario ve y lo que puede acceder
* Ausencia de una capa formal de enforcement de acceso a módulos
* Ambigüedad en la semántica de `app_user.role_id`

---

## Decisión

Se adopta el siguiente modelo arquitectónico de tres capas:

### Capa 1 — Visibilidad

**Responsable:** Módulo Apps

**Implementa:**

* Registro oficial de aplicaciones en tabla `apps`
* Activación y desactivación de módulos (campo `habilitada`)
* Asignación de aplicaciones a roles (`app_role`)
* Asignación de aplicaciones a usuarios (`app_user`)
* Dashboard "Mis Aplicaciones"
* Menú lateral dinámico

**Fuente oficial de verdad:** `App::visiblesPara($user)`

### Capa 2 — Acceso

**Responsable:** Módulo Apps (enforcement en rutas)

**Implementa:**

* Control de acceso al entrar a un módulo
* Verificación de que el usuario tiene el módulo asignado antes de servir la ruta

**Fuente oficial de verdad:** `App::visiblesPara($user)` (la misma que Capa 1)

### Capa 3 — Autorización funcional

**Responsable:** Sistema RBAC (Roles + Permissions)

**Implementa:**

* Operaciones CRUD dentro del módulo
* Procesos de negocio específicos
* Acciones administrativas
* Flujos de aprobación

**Fuente oficial de verdad:** `User::hasPermission($slug)` + `permission_role` + `permission_user`

---

## Modelo Conceptual

```
Usuario
  │
  ├── Apps (Capa 1 y 2)
  │     ├── Visibilidad: App::visiblesPara($user)
  │     └── Acceso al módulo: middleware de enforcement (pendiente — DP-002)
  │
  └── RBAC (Capa 3)
        ├── Roles → Permissions (permission_role)
        └── Users → Permissions (permission_user, excepciones directas)
```

### Flujo de acceso completo

```
Usuario autenticado
  │
  ▼
¿La app está en App::visiblesPara($user)?
  │
  ├── No → 403 / redirigir (Capa 2, pendiente implementación)
  │
  └── Sí → Accede al módulo
              │
              ▼
        ¿Tiene permiso para esta acción?
          │
          ├── No → 403 / ocultar UI
          │
          └── Sí → Ejecuta la acción
```

---

## Separación de Responsabilidades

| Función | Capa | Controlado por | Estado |
|---------|------|---------------|--------|
| Ver app en dashboard | 1 | Apps (`visiblesPara`) | ✅ Implementado |
| Ver app en menú lateral | 1 | Apps (`visiblesPara`) | ⏳ Pendiente |
| Entrar a URL del módulo | 2 | Apps (middleware) | ⏳ Pendiente |
| CRUD dentro del módulo | 3 | Permissions | ✅ Implementado |
| Procesos de negocio | 3 | Permissions | ✅ Implementado |
| Acciones administrativas | 3 | Permissions | ✅ Implementado |
| Activar/desactivar módulo | Admin | Apps (`habilitada`) | ✅ Implementado |
| Asignar app a rol | Admin | Apps (`app_role`) | ✅ Implementado |

---

## Decisiones Derivadas

### DD-001 — `App::visiblesPara()` es la única fuente de verdad para visibilidad

No se mantendrán listas paralelas de módulos visibles. El dashboard y el menú lateral
deberán consumir exclusivamente `App::visiblesPara($user)`.

### DD-002 — El menú lateral usará `App::visiblesPara()`

El sidebar de AdminLTE deberá construirse dinámicamente desde `App::visiblesPara($user)`,
eliminando la configuración estática de ítems de menú para módulos de la plataforma.

### DD-003 — El enforcement de acceso a módulos es responsabilidad de Apps

Cuando se implemente la capa 2, el mecanismo de enforcement deberá basarse en
`App::visiblesPara($user)` o en una verificación equivalente, no en permisos individuales
(`permission:ver-modulo`). Los permisos individuales quedan reservados exclusivamente
para la Capa 3 (autorización funcional).

---

## Decisiones Pendientes

### DP-001 — Semántica de `app_user.role_id`

El campo `role_id` en la tabla `app_user` existe en la migración y en el modelo, pero
ningún código lo lee para tomar decisiones. Su propósito no está definido.

**Opciones a evaluar:**
* Convertirlo en override de rol dentro del módulo para ese usuario específico
* Conservarlo como metadato contextual sin impacto en autorización
* Eliminarlo para simplificar el modelo

Esta decisión deberá resolverse antes de iniciar IMPL-013.

### DP-002 — Contrato del middleware de acceso a módulos

El contrato exacto del middleware de Capa 2 no está definido. Requiere decisión sobre:
* Parámetro de identificación: ¿slug de app, id de app, o nombre de módulo nWidart?
* Comportamiento ante acceso denegado: ¿403 o redirección al dashboard?
* Integración con `app_user.activo`: ¿se verifica en el middleware o solo en `visiblesPara()`?
* Manejo de rutas API: ¿aplica el mismo middleware o uno distinto?

Esta decisión deberá resolverse antes de iniciar IMPL-013.

---

## Consecuencias

### Positivas

* Separación clara y formal entre acceso y autorización
* Eliminación de inconsistencias entre dashboard y acceso real a módulos
* Escalabilidad para futuros módulos registrados via `apps:sync`
* `App::visiblesPara()` se consolida como fuente de verdad única para visibilidad y acceso
* Arquitectura reutilizable en API, SPA y aplicaciones móviles
* Reducción de ambigüedad en la gestión de acceso por parte de administradores

### Negativas

* Requiere implementación adicional para enforcement de acceso (middleware Capa 2)
* Requiere resolución de DP-001 y DP-002 antes de IMPL-013
* El menú lateral dinámico introduce acoplamiento con el sistema de caché de Apps

---

## Alternativas Consideradas

### Alternativa A — Mantener permisos individuales como capa de acceso

**Descripción:** Usar `permission:ver-{slug}` en cada módulo como único mecanismo de
acceso, sin involucrar Apps.

**Rechazada porque:**
* No elimina la brecha entre visibilidad (dashboard) y acceso (URL)
* Duplica la gestión: administrador debe sincronizar `app_role` y `permission_role`
* No escala: cada nuevo módulo requiere un permiso de entrada manual

### Alternativa B — Un solo sistema unificado

**Descripción:** Reemplazar Apps por permisos extendidos con contexto de módulo.

**Rechazada porque:**
* Rompe el modelo existente y funcional de `App::visiblesPara()`
* Requiere migración mayor de datos
* ADR-APPS-001 y IMPL-APPS-002 ya están implementados y estables

### Alternativa C (adoptada) — Tres capas con responsabilidades separadas

Apps controla visibilidad y acceso a módulos. RBAC controla autorización funcional interna.
Las capas no se superponen en responsabilidades.

---

## Relación con otros documentos

| Documento | Relación |
|-----------|----------|
| ADR-APPS-001 | Este ADR extiende y formaliza las decisiones de ADR-APPS-001 |
| ADR-AUTHORIZATION-001 | Propuesto. ADR-008 formaliza la arquitectura de tres capas descrita en ADR-AUTHORIZATION-001 D2. ADR-AUTHORIZATION-001 permanece como referencia técnica detallada |
| AUDIT-APPS-002 | Identificó los riesgos que motivaron esta decisión |
| AUDIT-APPS-003 | Auditoría técnica que validó la viabilidad del modelo y recomendó ADR-008 antes de IMPL-013 |
| IMPL-APPS-002 | Implementó el permiso `administrar-apps` y el caché de `visiblesPara()` |

---

## Próximo Paso

Con ADR-008 aprobado, el siguiente paso es:

**IMPL-013 — Apps Module Access Enforcement**

Implementación mínima:
1. Middleware `CheckAppAccess` para enforcement de Capa 2
2. Invalidación de caché al cambiar el rol de un usuario
3. Menú lateral dinámico desde `App::visiblesPara()`
4. UI para gestionar `app_user` (asignaciones directas)
5. Corrección del controlador referenciado en `Apps/routes/api.php`

**Precondición:** Resolución de DP-001 y DP-002 antes de iniciar IMPL-013.

---

## Estado de Implementación

| Componente | Estado |
|------------|--------|
| Tabla `app_role` | ✅ Implementado |
| Tabla `app_user` | ✅ Implementado |
| `App::visiblesPara()` | ✅ Implementado |
| Dashboard desde Apps | ✅ Implementado |
| Protección `/apps/admin` | ✅ Implementado |
| Caché de visibilidad | ✅ Implementado |
| Menú lateral desde Apps | ⏳ IMPL-013 |
| Middleware `CheckAppAccess` | ⏳ IMPL-013 |
| Invalidación caché por cambio de rol | ⏳ IMPL-013 |
| UI gestión `app_user` | ⏳ IMPL-013 |
| Resolución DP-001 | ⏳ Pendiente |
| Resolución DP-002 | ⏳ Pendiente |
