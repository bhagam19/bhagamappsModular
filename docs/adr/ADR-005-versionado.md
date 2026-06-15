# ADR-005 — Versionado y Compatibilidad

| Campo | Valor |
|-------|-------|
| **ID** | ADR-005 |
| **Título** | Versionado del CORE, Módulos y Estrategia de Compatibilidad |
| **Estado** | Aprobado |
| **Fecha** | 2026-06-14 |
| **Autores** | Equipo APPSisGOE |
| **Revisores** | — |
| **Documentos base** | AUDIT-APPS-002-APPSisGOE.md · ARCH-ANALYSIS-001-APPSisGOE.md |
| **Depende de** | ADR-001 (Core Mínimo) · ADR-002 (Gobernanza de Módulos) |

---

## 1. Estado

**Aprobado.** Esta decisión define la política de versionado para APPSisGOE. Es vinculante para el CORE y para todos los módulos.

---

## 2. Contexto

BhagamAppsModular implementó versionado semver desde la versión 1.0.0, documentado en el CHANGELOG y en un módulo propio de gestión de versiones. La auditoría documentó:

**Evidencia de AUDIT-APPS-002 §8.1:**
> "APPSisGOE tiene manifiestos con hash/versión. BhagamApps no tiene manifiestos — los datos están en seeders."

**Evidencia de AUDIT-APPS-002 §7.2:**
> "APPSisGOE ya resuelve esto con manifiestos + hash de integridad. Mantener."

APPSisGOE ya tiene la infraestructura de manifiestos. Lo que falta es una política formal que responda:

1. ¿Qué constituye un cambio MAJOR, MINOR, PATCH en el CORE y en los módulos?
2. ¿Cómo declara un módulo qué versión del CORE requiere?
3. ¿Cómo se verifica la compatibilidad antes de activar un módulo?
4. ¿Cuál es la estrategia de rollback si una actualización rompe algo?
5. ¿Qué garantías de compatibilidad ofrece el CORE a los módulos?

---

## 3. Problema

Sin una política de versionado formal:

- Un módulo puede activarse sobre una versión del CORE que no soporta sus dependencias
- El equipo no tiene criterio claro para decidir si un cambio es MAJOR, MINOR o PATCH
- Los módulos no pueden anunciar qué versión mínima del CORE necesitan
- No hay estrategia de rollback para actualizaciones del CORE que rompen módulos existentes
- BhagamApps demostró que los datos de configuración en seeders (sin manifiestos) son difíciles de versionar y recuperar

---

## 4. Alternativas consideradas

### Alternativa A — Versionado único del sistema (monolítico)

Un único número de versión para todo el sistema (CORE + todos los módulos). Todos los módulos cambian de versión cuando el CORE cambia.

**Rechazada porque:** Elimina la independencia de los módulos. Si el módulo Inventario necesita una corrección, se obliga a versionar también Biblioteca aunque no cambió nada. El propósito de la arquitectura modular es el versionado independiente.

### Alternativa B — Sin versionado explícito entre módulos

Los módulos no declaran dependencias de versión. El administrador gestiona la compatibilidad manualmente.

**Rechazada porque:** Es exactamente lo que ocurrió en BhagamApps — las dependencias ocultas entre módulos (`roles.app_id`) causaron errores difíciles de diagnosticar. ARCH-ANALYSIS-001 ERROR-001 documenta el impacto.

### Alternativa C — Semver independiente por componente con contrato `min_core` (decisión adoptada)

El CORE y cada módulo tienen versiones semver independientes. Los módulos declaran `min_core` en su manifiesto. El sistema valida la compatibilidad antes de activar cualquier módulo.

---

## 5. Decisión

### 5.1 Esquema de versiones

APPSisGOE usa **Semantic Versioning (semver) 2.0.0** para todos sus componentes.

```
MAJOR.MINOR.PATCH
  │      │    │
  │      │    └─ Correcciones de bugs sin cambio de API
  │      └────── Nuevas funcionalidades retrocompatibles
  └────────────── Cambios que rompen la compatibilidad
```

**Versiones iniciales:**
- CORE APPSisGOE: `1.0.0` (primera versión estable)
- Módulo Inventario: `1.0.0`
- Módulo User: `1.0.0`
- Módulo AdminSistema: `1.0.0`

---

### 5.2 Versionado del CORE

#### Cuándo incrementar MAJOR del CORE

Un cambio MAJOR del CORE (`1.x.x → 2.0.0`) ocurre cuando:

- Se elimina o renombra una interfaz pública que los módulos consumen (métodos de `ModuleVisibilityService`, contrato de `ActivityLogger`, estructura de `Capacidad` enum)
- Se cambia la firma de `ModuloAccessMiddleware` de forma no retrocompatible
- Se cambia la estructura de tablas del CORE de forma que requiere cambios en los módulos (`modules`, `module_role`, `module_user`, `users`)
- Se cambia la API de Spatie Permission de forma incompatible (upgrade de Spatie major)
- Se elimina un campo de `users` que los módulos usan (ej. `es_principal`, `bloqueado`)

**Impacto:** Un cambio MAJOR del CORE potencialmente requiere actualizar todos los módulos.

#### Cuándo incrementar MINOR del CORE

Un cambio MINOR del CORE (`1.0.x → 1.1.0`) ocurre cuando:

- Se agrega un nuevo servicio al CORE (nuevo servicio inyectable disponible para módulos)
- Se agregan campos nuevos a las tablas del CORE (backward compatible)
- Se agregan nuevas capacidades al enum `Capacidad` para el CORE mismo
- Se agrega un nuevo middleware disponible para los módulos
- Se agrega soporte para un nuevo tipo de notificación en CORE-5

**Impacto:** Los módulos existentes no necesitan cambios. Pueden optar por usar las nuevas capacidades.

#### Cuándo incrementar PATCH del CORE

Un cambio PATCH del CORE (`1.0.0 → 1.0.1`) ocurre cuando:

- Se corrige un bug que no cambia la API pública
- Se corrige un problema de performance (ej. optimización de caché)
- Se corrigen textos, mensajes de error, traducciones
- Se agrega o corrige un índice en una tabla del CORE

---

### 5.3 Versionado de módulos

Los módulos tienen versión independiente del CORE.

#### Cuándo incrementar MAJOR de un módulo

Un cambio MAJOR de módulo (`1.x.x → 2.0.0`) ocurre cuando:

- Se eliminan o renombran tablas del módulo de forma no retrocompatible
- Se cambia la API de consulta que otros módulos usan (si existe)
- Se requiere una versión mayor del CORE (`min_core` sube a un MAJOR diferente)

#### Cuándo incrementar MINOR de un módulo

- Se agrega nueva funcionalidad al módulo (nueva entidad, nuevo flujo, nuevo proceso)
- Se agregan nuevas capacidades al módulo (nuevas entradas en `Capacidad` enum)
- Se agregan nuevas rutas, nuevos componentes Livewire, nuevas tablas

#### Cuándo incrementar PATCH de un módulo

- Se corrigen bugs
- Se mejora performance
- Se corrigen textos, validaciones, mensajes de usuario

---

### 5.4 Declaración de compatibilidad en `module.json`

Todo módulo declara en su `module.json`:

```json
{
  "key": "inventario",
  "version": "1.0.0",
  "min_core": "1.0.0",
  "requires": ["user"],
  "conflicts": []
}
```

**Semántica de `min_core`:** El módulo funciona con cualquier versión del CORE `>= min_core` dentro del mismo MAJOR.

Ejemplos:
- `"min_core": "1.0.0"` → funciona con CORE 1.0.0, 1.1.0, 1.5.3, pero NO con CORE 2.0.0
- `"min_core": "1.2.0"` → funciona con CORE 1.2.0 o superior (dentro de MAJOR 1), pero NO con CORE 1.1.0

**Semántica de `requires`:** Lista de keys de módulos que deben estar en estado `Activo` antes de activar este módulo.

En la versión 1 de APPSisGOE, `requires` especifica solo la key del módulo (sin versión). El versionado de dependencias entre módulos se añadirá en una ADR posterior si se detecta la necesidad.

---

### 5.5 Verificación de compatibilidad

El `ModuleActivateAction` verifica la compatibilidad antes de activar un módulo:

```php
class ModuleActivateAction
{
    public function execute(Module $module): ModuleActivateResult
    {
        // 1. Verificar versión del CORE
        $coreVersion = config('appsisgoe.core_version'); // ej. "1.2.0"
        $minCore = $module->min_core;                    // ej. "1.0.0"

        if (!$this->isCompatible($coreVersion, $minCore)) {
            return ModuleActivateResult::failure(
                "El módulo {$module->key} requiere CORE >= {$minCore}. CORE instalado: {$coreVersion}."
            );
        }

        // 2. Verificar módulos requeridos
        foreach ($module->requires as $requiredKey) {
            $required = Module::where('key', $requiredKey)->first();
            if (!$required || $required->status !== ModuleStatus::Activo) {
                return ModuleActivateResult::failure(
                    "El módulo '{$requiredKey}' requerido por '{$module->key}' no está Activo."
                );
            }
        }

        // 3. Verificar conflictos
        foreach ($module->conflicts as $conflictKey) {
            $conflict = Module::where('key', $conflictKey)
                ->where('status', ModuleStatus::Activo)
                ->first();
            if ($conflict) {
                return ModuleActivateResult::failure(
                    "El módulo '{$module->key}' es incompatible con '{$conflictKey}' (activo)."
                );
            }
        }

        // 4. Activar
        $module->update(['status' => ModuleStatus::Activo]);
        $this->visibilityService->invalidarCache();

        return ModuleActivateResult::success();
    }

    private function isCompatible(string $installed, string $required): bool
    {
        // Mismo MAJOR + installed >= required
        [$instMajor] = explode('.', $installed);
        [$reqMajor]  = explode('.', $required);
        return $instMajor === $reqMajor && version_compare($installed, $required, '>=');
    }
}
```

---

### 5.6 Hash de integridad de manifiesto

Cada `module.json` tiene un hash calculado en tiempo de instalación:

```json
{
  "key": "inventario",
  "version": "1.0.0",
  "_hash": "sha256:abc123..."
}
```

El hash se calcula sobre el contenido canónico del manifiesto (excluyendo el campo `_hash`). Se almacena en la tabla `modules.manifest_hash`.

**Propósito:** Detectar modificaciones no autorizadas al manifiesto después de la instalación. Si el hash no coincide al activar, se advierte al administrador.

**Evidencia de AUDIT-APPS-002 §7.2:**
> "APPSisGOE ya resuelve esto con manifiestos + hash de integridad. Mantener."

---

### 5.7 Estrategia de actualización del CORE

#### Actualización PATCH (1.0.0 → 1.0.1)

1. No requiere validación de compatibilidad con módulos
2. Migración de base de datos si aplica
3. Sin período de deprecación

#### Actualización MINOR (1.0.0 → 1.1.0)

1. Ejecutar en entorno de staging primero
2. Verificar que todos los módulos Activos siguen funcionando (sus `min_core` siguen siendo satisfechos)
3. Las nuevas capacidades son opt-in — los módulos existentes no están obligados a usarlas
4. Actualizar `config('appsisgoe.core_version')` como parte del deploy

#### Actualización MAJOR (1.x.x → 2.0.0)

1. Publicar lista de cambios breaking con al menos 60 días de anticipación
2. Mantener compatibilidad con MAJOR anterior por un período de transición (si es técnicamente viable)
3. Verificar cada módulo Activo por separado
4. Los módulos que no sean compatibles con MAJOR 2 deben ser desactivados antes de la actualización del CORE
5. Actualizar `min_core` de cada módulo para declarar su compatibilidad con el nuevo MAJOR

---

### 5.8 Estrategia de actualización de módulos

#### Actualización PATCH o MINOR de un módulo

1. `modules:discover` detecta la nueva versión del manifiesto (si el hash cambió)
2. El administrador ejecuta el proceso de actualización desde la UI o CLI
3. Se ejecutan migraciones del módulo
4. El módulo permanece en estado `Activo` durante la actualización si la migración es retrocompatible

#### Actualización MAJOR de un módulo

1. El módulo debe desactivarse antes de actualizar (`ModuleDeactivateAction`)
2. Se ejecutan las migraciones de la nueva versión
3. Se verifica la compatibilidad con el CORE instalado
4. El módulo se reactiva (`ModuleActivateAction`)

---

### 5.9 Estrategia de rollback

#### Rollback de PATCH del CORE

Revertir el deploy. Las migraciones de PATCH son retrocompatibles — el rollback de migración (`php artisan migrate:rollback`) es seguro.

#### Rollback de MINOR del CORE

1. Revertir el deploy
2. Ejecutar rollback de migraciones hasta la versión anterior
3. Los módulos que usaban capacidades nuevas del CORE vuelven a funcionar sin ellas (si las capacidades eran opt-in)

#### Rollback de MAJOR del CORE

El rollback de MAJOR es una operación de disaster recovery, no de mantenimiento rutinario.

1. Restaurar desde snapshot de backup (módulo AdminSistema, capacidad `restaurar-backups` + `es_principal`)
2. El snapshot garantiza consistencia total del estado del sistema
3. El módulo AdminSistema gestiona esta capacidad — ver capacidades `restaurar-backups` e `importar-snapshot-backup` en AUDIT-RBAC-001 §2.8

**Evidencia:** Esta es la razón por la que `restaurar-backups` requiere doble condición `es_principal` — el rollback de MAJOR es la operación de mayor impacto en el sistema. (ADR-003 §5.6)

#### Rollback de módulo

1. `ModuleDeactivateAction` desactiva el módulo
2. Rollback de migraciones del módulo
3. Revertir los archivos del módulo a la versión anterior
4. Reactivar con `ModuleActivateAction`

---

### 5.10 CHANGELOG y trazabilidad de versiones

El CHANGELOG de APPSisGOE sigue el formato [Keep a Changelog](https://keepachangelog.com/):

```markdown
## [1.1.0] — 2026-07-01

### Añadido
- CORE-3: Campo `slug` en `categorias` para identificación estable de grupos institucionales

### Corregido
- CORE-4: ActivityLogger ahora incluye `ip_address` en todos los logs

## [1.0.0] — 2026-06-14

### CORE inicial
- CORE-1: Users con es_principal, bloqueado, forzar_cambio_password
- CORE-2: Spatie Permission + Capacidad enum
- CORE-3: Módulos con module_role, module_user, visiblesPara()
- CORE-4: ActivityLogger + auditoria_passwords
- CORE-5: Notificaciones
- CORE-6: ProteccionAdminPrincipal + CheckForzarCambioPassword
```

**Regla:** Cada commit que modifica el CORE o un módulo debe incluir en el mensaje de commit la versión resultante. El CHANGELOG se actualiza antes de cada release.

---

### 5.11 Versión del CORE en configuración

La versión instalada del CORE se registra en `config/appsisgoe.php`:

```php
return [
    'core_version' => '1.0.0',
];
```

Este valor se usa por `ModuleActivateAction` para verificar `min_core`. Se actualiza como parte del proceso de deploy del CORE.

---

## 6. Consecuencias

### Positivas
- Los módulos pueden actualizarse independientemente del CORE
- El sistema detecta incompatibilidades antes de activar un módulo (no después de que algo falla)
- El rollback de módulos es una operación ordinaria, no una emergencia
- El hash de integridad del manifiesto previene modificaciones no autorizadas en producción
- La política clara de MAJOR/MINOR/PATCH guía las decisiones de versionado sin ambigüedad

### Negativas / Trade-offs
- Cada módulo requiere mantenimiento de su `module.json` — overhead de disciplina de desarrollo
- Una actualización MAJOR del CORE requiere coordinación con todos los módulos — el costo crece con el número de módulos
- El versionado de dependencias entre módulos (versión de `requires`, no solo key) queda diferido — es una deuda técnica aceptada

### Restricciones que impone esta decisión
- **Todo módulo debe tener `min_core` declarado en `module.json`**
- **`ModuleActivateAction` debe verificar `min_core` antes de activar** — no es opcional
- **Un cambio MAJOR del CORE requiere aprobación arquitectónica** (nueva ADR o revisión de esta)
- **El CHANGELOG se actualiza antes de cada release**, no después
- **`config('appsisgoe.core_version')` debe actualizarse en cada deploy del CORE**

---

## 7. Riesgos

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|-------------|---------|------------|
| `min_core` desactualizado en `module.json` | Alta | Medio | Test automatizado que verifica que el módulo funciona con la versión declarada en `min_core` |
| Módulo omite declarar `requires` y falla en producción | Media | Alto | `modules:discover` valida el manifiesto contra un schema JSON; campo `requires` no puede ser null |
| Actualización MAJOR del CORE sin comunicación previa | Baja | Muy alto | Proceso de aprobación arquitectónica requerido para cambios MAJOR |
| Rollback de migración de módulo no es reversible | Media | Alto | Las migraciones deben incluir método `down()` funcional; test de reversibilidad en CI |
| Hash de manifiesto manipulado en producción | Muy baja | Medio | Hash verificado en `ModuleActivateAction`; alerta si no coincide |

---

## 8. Justificación basada en evidencia

| Afirmación | Evidencia |
|-----------|-----------|
| Los manifiestos con hash son la solución correcta | AUDIT-APPS-002 §7.2: "APPSisGOE ya resuelve esto con manifiestos + hash de integridad. Mantener." |
| Los módulos deben declarar versión mínima del CORE | AUDIT-APPS-002 §7.3: formato propuesto con `min_core` en module.json. |
| La validación de dependencias es necesaria | AUDIT-APPS-002 §7.5: "Verificación de dependientes inversos antes de desactivar". ARCH-ANALYSIS-001 ERROR-001: dependencia circular entre módulos. |
| El rollback de MAJOR requiere snapshot de backup | AUDIT-RBAC-001 §2.8: `restaurar-backups` requiere `es_principal`. ARCH-ANALYSIS-001 ACIERTO-004. |
| BhagamApps sin manifiestos generó deuda técnica | AUDIT-APPS-002 §8.1: "BhagamApps no tiene manifiestos — datos en seeders. APPSisGOE tiene manifiestos." ARCH-ANALYSIS-001 §1.3: "Rediseñar — AppSeeder con seeders CSV → manifiestos module.json." |
| La política semver requiere criterios explícitos de MAJOR | ARCH-ANALYSIS-001 §8 (Fase 4): la expansión modular requiere que el CORE sea estable y versionado con contrato claro para los módulos. |

---

*Decisiones relacionadas: ADR-001 (Core Mínimo) · ADR-002 (Gobernanza de Módulos) · ADR-003 (Autorización) · ADR-004 (Dominio Inventario)*
