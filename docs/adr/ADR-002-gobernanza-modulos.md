# ADR-002 — Gobernanza de Módulos

| Campo | Valor |
|-------|-------|
| **ID** | ADR-002 |
| **Título** | Gobernanza de Módulos: Identidad, Ciclo de Vida, Visibilidad y Descubrimiento |
| **Estado** | Aprobado |
| **Fecha** | 2026-06-14 |
| **Autores** | Equipo APPSisGOE |
| **Revisores** | — |
| **Documentos base** | AUDIT-APPS-002-APPSisGOE.md · ARCH-ANALYSIS-001-APPSisGOE.md |
| **Depende de** | ADR-001 (Core Mínimo) |

---

## 1. Estado

**Aprobado.** Esta decisión define el contrato que todo módulo de APPSisGOE debe cumplir para ser considerado compatible con el sistema.

---

## 2. Contexto

BhagamAppsModular gestionó módulos mediante dos mecanismos que evolucionaron de forma independiente:

1. **nWidart/laravel-modules** — carga física de módulos como paquetes PHP
2. **Tabla `apps`** — catálogo de módulos con metadatos visuales, control de visibilidad por rol/usuario, y middleware de acceso

Esta dualidad generó los siguientes problemas documentados:

**Evidencia de AUDIT-APPS-002 §8.1:**
> BhagamApps tiene 2 estados de módulo (habilitada/deshabilitada). APPSisGOE tiene 6 (Pendiente/Instalando/Activo/Inactivo/Error/Desinstalado).

**Evidencia de AUDIT-APPS-002 §8.2 (brechas):**
> No hay `module_role`, no hay `module_user`, no hay `Module::visiblesPara()`, no hay middleware de acceso por visibilidad, no hay metadatos visuales en `modules`, no hay UI de gestión de visibilidad.

**Evidencia de AUDIT-APPS-002 §6.2:**
> "Apps provee servicios transversales que todos los módulos consumen. Sin Apps, ningún módulo es accesible."

APPSisGOE ya tiene el ciclo de vida de 6 estados, manifiestos y Actions. Le falta el modelo de **visibilidad institucional** que BhagamApps ya resolvió.

---

## 3. Problema

APPSisGOE carece de:

1. Una definición formal de la identidad de un módulo (qué lo identifica, qué declara)
2. Un mecanismo de visibilidad institucional (qué módulos ve cada usuario)
3. Un descriptor estándar (dónde se declaran metadatos, dependencias y compatibilidad)
4. Un contrato de activación/desactivación seguro (validación de dependencias)
5. Un mecanismo de descubrimiento (cómo el sistema registra un módulo nuevo)

---

## 4. Alternativas consideradas

### Alternativa A — Registro manual exclusivo

El administrador registra manualmente cada módulo en la base de datos mediante un formulario.

**Rechazada porque:** No escala. Cada instalación del sistema requeriría configuración manual de decenas de módulos. BhagamApps demostró que incluso con solo 12 módulos, la gestión manual genera inconsistencias (AUDIT-APPS-002 §4 — migration `cleanup_legacy_apps` eliminó 12 apps huérfanas con slug=NULL).

### Alternativa B — Descubrimiento automático total

El sistema descubre módulos desde el filesystem y los activa automáticamente.

**Rechazada porque:** Viola el principio de mínimo privilegio. BhagamApps adoptó conscientemente el principio contrario: módulos nuevos se crean con `habilitada = false` (AUDIT-APPS-002 §2.2). Cualquier módulo nuevo debe ser explícitamente activado y asignado a roles.

### Alternativa C — Descriptor + Descubrimiento guiado + Visibilidad explícita (decisión adoptada)

Cada módulo declara un manifiesto (`module.json`). El sistema descubre módulos desde manifiestos. La activación es explícita. La visibilidad es configurable por rol y por usuario.

---

## 5. Decisión

### 5.1 Identidad del módulo

Un módulo se identifica mediante su **key**: un identificador kebab-case único, estable y sin espacios.

**Reglas:**
- La key NO cambia entre versiones del módulo
- La key es el parámetro del middleware: `modulo.access:inventario`
- La key es el identificador en `module_role` y `module_user`
- La key deriva del directorio del módulo: `Modules/Inventario/` → key `inventario`

**Formato:** `^[a-z][a-z0-9-]*$` (kebab-case minúsculas, empieza con letra)

**Ejemplos válidos:** `inventario`, `admin-sistema`, `evaluacion-docente`, `prestamo-tabletas`

---

### 5.2 Descriptor del módulo (module.json)

Cada módulo contiene un archivo `module.json` en su directorio raíz. Este archivo es la **única fuente de verdad** de los metadatos del módulo.

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

**Campos obligatorios:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `key` | string | Identificador único del módulo |
| `name` | string | Nombre legible para UI |
| `version` | string | Versión semver del módulo |
| `min_core` | string | Versión mínima del CORE requerida |

**Campos opcionales:**

| Campo | Tipo | Default | Descripción |
|-------|------|---------|-------------|
| `description` | string | `""` | Descripción institucional |
| `requires` | array | `[]` | Keys de módulos que deben estar Activos |
| `conflicts` | array | `[]` | Keys de módulos incompatibles |
| `icono` | string | `"fas fa-puzzle-piece"` | Clase FontAwesome |
| `color` | string | `"#6c757d"` | Color hexadecimal para la tarjeta |
| `orden` | int | `99` | Orden de aparición en el dashboard |
| `ruta_entrada` | string | `"/{key}"` | URL de entrada al módulo |
| `capacidades` | array | `[]` | Slugs de permisos que este módulo declara |

**El campo `capacidades` es informativo** — los permisos se crean desde `Capacidad` enum. Este campo permite documentar qué permisos introdujo este módulo.

---

### 5.3 Modelo de datos en el CORE

```sql
-- Tabla modules (ya existe en APPSisGOE — agregar campos visuales)
ALTER TABLE modules
    ADD COLUMN icono       VARCHAR(255)  NULL,
    ADD COLUMN color       VARCHAR(20)   NULL,
    ADD COLUMN orden       INT UNSIGNED  NOT NULL DEFAULT 99,
    ADD COLUMN ruta_entrada VARCHAR(255) NULL,
    ADD COLUMN min_core    VARCHAR(20)   NULL;

-- Tabla module_role — visibilidad por rol
CREATE TABLE module_role (
    id        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    role_id   BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_module_role_module FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    CONSTRAINT fk_module_role_role   FOREIGN KEY (role_id)   REFERENCES roles(id)   ON DELETE CASCADE,
    UNIQUE KEY uq_module_role (module_id, role_id)
);

-- Tabla module_user — excepciones individuales de visibilidad
CREATE TABLE module_user (
    id        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    user_id   BIGINT UNSIGNED NOT NULL,
    activo    TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_module_user_module FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    CONSTRAINT fk_module_user_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    UNIQUE KEY uq_module_user (module_id, user_id)
);
```

**Nota:** `roles` no tiene FK hacia `modules`. El vínculo es exclusivamente de `module_role` hacia `roles` — sin dependencias circulares.

---

### 5.4 Visibilidad institucional

**Regla de visibilidad:** Un usuario ve un módulo si:
1. El módulo tiene `status = Activo`, Y
2. El rol del usuario está en `module_role` para ese módulo (acceso por rol), O
3. El usuario está en `module_user` con `activo = true` para ese módulo (excepción individual)

**Lógica OR (no precedencia):** El acceso individual NO cancela el acceso por rol. Es un canal adicional.

**Servicio:**

```php
// app/Services/ModuleVisibilityService.php
class ModuleVisibilityService
{
    public function visiblesPara(User $user): Collection
    {
        $version = (int) cache()->get('modules.cache_version', 0);
        $cacheKey = "modules.visibles.{$user->id}.v{$version}";

        return cache()->remember($cacheKey, seconds: 300, callback: fn() =>
            Module::where('status', ModuleStatus::Activo)
                ->where(function ($q) use ($user) {
                    $q->whereHas('roles', fn($r) => $r->where('roles.id', $user->role_id))
                      ->orWhereHas('users', fn($u) => $u
                            ->where('users.id', $user->id)
                            ->where('module_user.activo', true));
                })
                ->orderBy('orden')
                ->orderBy('name')
                ->get()
        );
    }

    public function invalidarCache(): void
    {
        cache()->increment('modules.cache_version');
    }
}
```

**Invalidación de caché:** Se llama a `invalidarCache()` en cualquier cambio que afecte la visibilidad: activar/desactivar módulo, cambiar asignación de roles, cambiar asignación individual de usuario.

**Evidencia de AUDIT-APPS-002 §3.3:**
> "`cache()->increment('apps.cache_version')` invalida las entradas de TODOS los usuarios simultáneamente, sin necesidad de conocer sus IDs."

---

### 5.5 Middleware de acceso

```php
// Declaración en las rutas de cada módulo:
Route::middleware(['web', 'auth', 'verified', 'modulo.access:inventario'])
    ->prefix('inventario')
    ->group(function () {
        // rutas del módulo
    });
```

El middleware verifica que el módulo sea visible para el usuario antes de procesar cualquier request. Si el módulo está desactivado o el usuario no tiene acceso, retorna 403.

---

### 5.6 Ciclo de vida del módulo

```
[Pendiente] ──instalar──► [Instalando] ──ok──► [Inactivo]
[Inactivo]  ──activar───► [Activo]
[Activo]    ──desactivar─► [Inactivo]
[cualquiera]──error──────► [Error]
[Inactivo]  ──eliminar───► [Desinstalado]
```

**Activación — precondiciones:**
1. `status` debe ser `Inactivo`
2. Todos los módulos en `requires[]` deben estar en estado `Activo`
3. Ningún módulo en `conflicts[]` puede estar en estado `Activo`
4. La versión del CORE instalado debe ser `>= min_core` declarado en el manifiesto

**Desactivación — precondiciones:**
1. `status` debe ser `Activo`
2. Ningún módulo Activo que declare `requires: [this.key]` puede estar activo

**Al activar:** El `ModuleActivateAction` lee el `module.json`, valida precondiciones, actualiza `status = Activo`, registra metadatos visuales en la tabla, e invalida la caché de visibilidad.

**Al desactivar:** El `ModuleDeactivateAction` verifica dependientes inversos, actualiza `status = Inactivo`, e invalida la caché de visibilidad.

---

### 5.7 Descubrimiento de módulos

El descubrimiento es el proceso por el cual el sistema registra un módulo en la base de datos a partir de su `module.json`.

**Mecanismo:**

```bash
php artisan modules:discover
```

**Comportamiento:**
1. Escanea `Modules/*/module.json`
2. Para cada manifiesto encontrado:
   - Si `key` no existe en `modules`: crea registro con `status = Pendiente`, `orden = 99`, metadatos del manifiesto
   - Si `key` ya existe: NO actualiza (preserva configuración existente del admin)
3. Reporta: nuevos descubiertos, ya existentes, errores de validación de manifiesto

**Principio heredado de BhagamApps:** Los módulos nuevos se crean con estado `Pendiente` (equivalente a `habilitada = false`). Deben ser instalados y activados explícitamente.

**Idempotencia:** Ejecutar `modules:discover` múltiples veces es seguro. No genera duplicados ni sobreescribe configuración existente.

---

### 5.8 Versionado del módulo

Ver ADR-005 para la política completa. Resumen aplicable a este contexto:

- Los módulos usan semver: `MAJOR.MINOR.PATCH`
- `min_core` usa comparación `>=` (el módulo funciona con esa versión del CORE o superior)
- `requires` especifica key del módulo requerido, sin restricción de versión en v1 (añadir versionado de dependencias entre módulos en v2 si es necesario)

---

### 5.9 Gobernanza de acceso — capacidades requeridas

| Acción | Capacidad | Roles por defecto |
|--------|-----------|------------------|
| Ver catálogo de módulos | `modulos:ver` | Administrador, Rectoría |
| Descubrir módulos (artisan) | Solo CLI | Solo CLI (no UI) |
| Instalar módulo | `modulos:instalar` | Administrador |
| Activar módulo | `modulos:activar` | Administrador, Rectoría |
| Desactivar módulo | `modulos:desactivar` | Administrador, Rectoría |
| Asignar módulo a rol | `modulos:asignar_rol` | Administrador, Rectoría, Coordinación |
| Asignar módulo a usuario | `modulos:asignar_usuario` | Administrador, Rectoría, Coordinación |
| Desinstalar módulo | `modulos:desinstalar` | Administrador |

Estas capacidades se agregan al enum `Capacidad` y se siembran en la instalación del CORE.

---

## 6. Consecuencias

### Positivas
- Cada módulo es autónomo: declara en su `module.json` todo lo que el sistema necesita saber de él
- La visibilidad es configurable por rol y por usuario sin modificar código
- El mecanismo de caché versioned previene queries de visibilidad en cada request
- La validación de dependencias previene estados inconsistentes (módulo B activo con módulo A desactivado)
- Nuevos módulos no son visibles hasta que el administrador los activa explícitamente

### Negativas / Trade-offs
- Los desarrolladores de módulos deben mantener `module.json` sincronizado con el código
- La caché de visibilidad (300s TTL) implica que cambios de acceso tardan hasta 5 minutos en propagarse — aceptable en el contexto institucional, no en un contexto de tiempo real estricto
- La validación de dependencias entre módulos agrega complejidad a las acciones de ciclo de vida

### Restricciones que impone esta decisión
- **Todo módulo debe tener `module.json` válido** para ser descubierto
- **Las rutas de todo módulo deben usar `modulo.access:{key}`** como middleware
- **Los módulos no pueden tener FK hacia `modules`** — la única FK permitida es `module_role.module_id` y `module_user.module_id` (ambas en el CORE)
- **Un módulo con dependientes activos no puede desactivarse**

---

## 7. Riesgos

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|-------------|---------|------------|
| `module.json` desincronizado con el código | Alta | Medio | Test de validación de manifiesto en CI por módulo |
| Dependencia circular entre módulos (A requiere B, B requiere A) | Baja | Alto | Validar grafo de dependencias en `ModuleActivateAction` con detección de ciclos |
| Caché de visibilidad desactualizada por tiempo > 300s | Muy baja | Medio | `invalidarCache()` se llama en cada cambio de configuración; el TTL es de seguridad |
| Admin desactiva un módulo con dependientes sin advertencia | Media | Alto | `ModuleDeactivateAction` verifica dependientes y retorna error descriptivo antes de proceder |
| Key de módulo duplicada en dos módulos distintos | Baja | Muy alto | UNIQUE constraint en `modules.key`; `modules:discover` reporta error si hay colisión |

---

## 8. Justificación basada en evidencia

| Afirmación | Evidencia |
|-----------|-----------|
| Los módulos nuevos deben estar deshabilitados por defecto | AUDIT-APPS-002 §4: `apps:sync` siempre crea con `habilitada = false`. ARCH-ANALYSIS-001 ACIERTO-007. |
| La visibilidad debe soportar OR(rol, usuario) | AUDIT-APPS-002 §3.2: "No hay precedencia — es OR lógico. Si el usuario tiene acceso por cualquiera de las dos vías, ve la app." |
| La caché de visibilidad debe invalidarse por versión global | AUDIT-APPS-002 §3.3: `cache()->increment('apps.cache_version')` invalida sin conocer IDs de usuarios. ARCH-ANALYSIS-001 ACIERTO-002. |
| El módulo Apps no debe existir como módulo separado | AUDIT-APPS-002 §9 y ARCH-ANALYSIS-001 §5.1: su lógica se absorbe en el CORE. |
| Las dependencias entre módulos deben validarse | ARCH-ANALYSIS-001 §5.3: "Validación de dependencias antes de desactivar: si módulo B requiere A, no desactivar A mientras B esté Activo." |
| Los roles no deben tener FK hacia módulos | ARCH-ANALYSIS-001 ERROR-001: "La FK entre roles y apps creó una dependencia circular innecesaria." AUDIT-RBAC-001 §1.1: `roles.app_id` es campo legacy a eliminar. |

---

*Decisiones relacionadas: ADR-001 (Core Mínimo) · ADR-003 (Autorización) · ADR-005 (Versionado)*
