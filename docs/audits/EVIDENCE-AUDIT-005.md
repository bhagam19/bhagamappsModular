# EVIDENCE-AUDIT-005 — Versioning and Changelog Compliance Audit

**Fecha de recopilación:** 2026-06-08
**Agente recopilador:** ⚙️ Implementación (Claude Code)
**Propósito:** Evidencia objetiva para la elaboración de AUDIT-005 por el Agente Auditoría.
**Estado:** Solo lectura — sin modificaciones al repositorio.

---

## 1. Versiones actuales por módulo

| Módulo | Versión declarada | Fuente de verdad (runtime) | Última actualización declarada |
|--------|-------------------|----------------------------|-------------------------------|
| BhagamApps (plataforma) | 1.4.0 | `config/versiones.php` | 2026-06-08 |
| Inventario | 2.4.0 | `config/versiones.php` | 2026-06-08 |
| User | 2.1.1 | `config/versiones.php` | 2026-06-08 |
| Apps | 1.0.0 | `config/versiones.php` | 2025-06-07 |
| CrudGenerator | 1.1.0 | `config/versiones.php` | 2025-06-23 |

**Contenido literal de `config/versiones.php`:**

```php
return [
    'BhagamApps'    => '1.4.0',
    'User'          => '2.1.1',
    'Inventario'    => '2.4.0',
    'Apps'          => '1.0.0',
    'CrudGenerator' => '1.1.0',
];
```

---

## 2. Tags Git existentes

### 2.1. Árbol actual (HEAD / main branch)

La rama `main` tiene como commit raíz `3f6d944` (2026-06-08). Solo **un tag** pertenece a este árbol:

| Tag | Commit | Fecha de creación del tag | Mensaje del tag |
|-----|--------|--------------------------|-----------------|
| `BhagamApps-v1.4.0` | `470958f` | 2026-06-08 12:34 | Baseline producción 2026-06-08 tras recuperación Git y sincronización GitHub |

Este tag apunta al commit `470958f` (`chore(git): finalize repository migration and tracking configuration`), que NO es el último commit de `main`. El commit HEAD actual es `3b3054e` — **8 commits posteriores al tag** sin tag de versión.

### 2.2. Árbol histórico (orphan tree — raíz `6d9614c`)

Los siguientes 24 tags pertenecen al árbol histórico, desconectado de `main`. Fueron creados durante el desarrollo original (2025-06-22 a 2025-06-26) antes de la migración git (IMPL-GIT-001).

| Tag | Commit corto | Fecha de creación del tag | Descripción |
|-----|-------------|--------------------------|-------------|
| `BhagamApps-v1.0.0` | `47a387c` | 2025-06-22 15:34 | Creación inicial Laravel 11, Jetstream, Livewire |
| `BhagamApps-v1.1.0` | `6b61eda` | 2025-06-22 15:43 | Migración a estructura modular |
| `BhagamApps-v1.2.0` | `4bd2001` | 2025-06-22 16:15 | Refactor estructura, navegación, permisos |
| `BhagamApps-v1.3.0` | `0d3bd80` | 2025-06-23 21:48 | CrudGenerator completo |
| `Users-v1.0.0` | `39876bd` | 2025-06-22 15:56 | Creación módulo Users |
| `Users-v1.1.0` | `f5da6d4` | 2025-06-22 16:16 | Seeders de roles y permisos |
| `Users-v1.1.1` | `f4b8913` | 2025-06-22 16:21 | Actualización nombres de permisos |
| `User-v2.0.0` | `6b1db65` | 2025-06-26 13:09 | Renombrado y reimplementación completa |
| `App-v1.0.0` | `26d2a6c` | 2025-06-22 16:19 | Creación inicial módulo App |
| `Inventario-v2.0.0` | `9f979ce` | 2025-06-22 16:00 | Refactor completo modular |
| `Inventario-v2.1.0` | `02ae7b3` | 2025-06-22 16:05 | Interfaz docentes, acordeón filtros |
| `Inventario-v2.1.1` | `19ce854` | 2025-06-22 16:09 | Formulario desplegable edición mobile |
| `Inventario-v2.2.0` | `51c33de` | 2025-06-22 16:10 | Acta de entrega inicial |
| `Inventario-v2.2.1` | `e8deb09` | 2025-06-22 16:11 | Mejora impresión acta |
| `Inventario-v2.2.2` | `7b24495` | 2025-06-22 16:11 | Paginación, diseño, impresión acta |
| `Inventario-v2.3.0` | `f6b8e35` | 2025-06-22 16:12 | Gestión cambios pendientes, notificaciones |
| `Inventario-v2.3.1` | `7a004b3` | 2025-06-22 16:16 | Refactor notificaciones y rutas |
| `Inventario-v2.3.2` | `ab92afe` | 2025-06-22 16:22 | Flujo modificación de detalles |
| `Inventario-v2.3.3` | `5c93e85` | 2025-06-22 16:33 | Gestión de eliminaciones de bienes |
| `Inventario-v2.3.4` | `481ae91` | 2025-06-22 16:39 | Centralización aprobaciones en HmbController |
| `Inventario-v2.3.5` | `e4ee488` | 2025-06-22 16:41 | Centralización aprobaciones (continuación) |
| `Inventario-v2.3.6` | `38bca14` | 2025-06-22 16:43 | Encabezados con logo, ordenamiento bienes |
| `CrudGenerator-v1.0.0` | `8ce808b` | 2025-06-23 21:49 | CrudGenerator inicial |
| `CrudGenerator-v1.1.0` | `2098314` | 2025-06-26 23:42 | Servicios generación CRUD completos |

**Observación estructural:** Los commits de los tags históricos NO son ancestros de `HEAD`. Las dos historias son completamente independientes (ninguna rama las conecta). El repositorio tiene 2 raíces: `3f6d944` (árbol actual) y `6d9614c` (árbol histórico).

**Commits compartidos entre múltiples tags:**
- `874e62797` ← apuntado por `BhagamApps-v1.2.0`, `Inventario-v2.3.1`, y `Users-v1.1.0` (tres tags al mismo commit)
- `e5d1784cc` ← apuntado por `BhagamApps-v1.3.0` y `CrudGenerator-v1.0.0` (dos tags al mismo commit)

---

## 3. Changelogs

### 3.1. Estado por archivo

| Archivo | Última versión registrada | Versiones documentadas |
|---------|--------------------------|------------------------|
| `CHANGELOG.md` (raíz) | v1.4.0 — 2026-06-08 | v1.0.0, v1.2.0, v1.3.0, v1.4.0 |
| `docs/changelog/bhagamapps.md` | v1.4.0 — 2026-06-08 | v1.0.0, v1.1.0, v1.2.0, v1.3.0, v1.4.0 |
| `docs/changelog/inventario.md` | v2.4.0 — 2026-06-08 | v2.0.0, v2.1.0, v2.3.1, v2.3.2, v2.3.4, v2.3.5, v2.3.6, v2.4.0 |
| `docs/changelog/user.md` | v2.1.1 — 2026-06-08 | Users v1.0.0, v1.1.0, v1.1.1, v2.0.0, v2.1.0, v2.1.1 |
| `docs/changelog/apps.md` | v1.0.0 — 2025-06-07 | v1.0.0 |
| `docs/changelog/crudgenerator.md` | v1.1.0 — 2025-06-23 | v1.0.0, v1.1.0 |

### 3.2. Versiones con tag git pero sin entrada en changelog

| Tag git | Versión | Entrada en changelog |
|---------|---------|---------------------|
| `Inventario-v2.1.1` | 2.1.1 | Ausente en `inventario.md` |
| `Inventario-v2.2.0` | 2.2.0 | Ausente en `inventario.md` |
| `Inventario-v2.2.1` | 2.2.1 | Ausente en `inventario.md` |
| `Inventario-v2.2.2` | 2.2.2 | Ausente en `inventario.md` |
| `Inventario-v2.3.0` | 2.3.0 | Ausente en `inventario.md` |
| `Inventario-v2.3.3` | 2.3.3 | Ausente en `inventario.md` |

### 3.3. Versiones declaradas en `config/versiones.php` sin tag git

| Módulo | Versión declarada | Tag git correspondiente |
|--------|------------------|------------------------|
| Inventario | 2.4.0 | No existe `Inventario-v2.4.0` |
| User | 2.1.1 | No existe `User-v2.1.1` |
| User | 2.1.0 | No existe `User-v2.1.0` |

---

## 4. VERSIONING.md — Resumen ejecutivo de reglas vigentes

**Archivo:** `VERSIONING.md` (raíz del proyecto)

**Fuente de verdad de versiones (runtime):** Declarada como `config/modules.php` en el documento. En la práctica, el archivo operativo es `config/versiones.php`.

**Reglas de incremento de versión de plataforma:**

| Tipo | Condición |
|------|-----------|
| Major (`X.0.0`) | Cambio de arquitectura fundamental, migración de stack |
| Minor (`x.Y.0`) | Nueva funcionalidad transversal, nuevo módulo, cambio de seguridad relevante |
| Patch (`x.y.Z`) | Correcciones de bugs del core, hotfixes, documentación crítica |

**Reglas de incremento de versión de módulo:**

| Tipo | Condición |
|------|-----------|
| Major (`X.0.0`) | Rediseño de interfaz, cambios incompatibles de datos, migración destructiva |
| Minor (`x.Y.0`) | Nueva funcionalidad, nueva sección, cambio de comportamiento significativo |
| Patch (`x.y.Z`) | Bug fix, corrección de validación, ajuste de UI menor, fix de seguridad acotado |

**Procedimiento declarado al finalizar cada sesión:**
1. Identificar módulos con cambios.
2. Incrementar versión según tipo de cambio.
3. Actualizar tabla en `VERSIONING.md`.
4. Registrar en `docs/changelog/<modulo>.md`.
5. Si el cambio es transversal, también en `docs/changelog/bhagamapps.md`.

**Cambios que NO ameritan incremento de versión:**
- Correcciones de documentación.
- Cambios en `.env.example`, `vite.config.js`.
- Cambios en seeders sin impacto en producción.
- Cambios solo en tests.

---

## 5. ADR-004 — Estado de implementación declarado

**Archivo:** `docs/adr/ADR-004-Modular-Versioning.md`
**Estado:** Aceptado (documentación y configuración) / Pendiente (implementación UI)

| Componente | Estado declarado en ADR-004 |
|------------|----------------------------|
| `docs/changelog/<modulo>.md` | ✅ Implementado |
| `VERSIONING.md` | ✅ Implementado |
| `config/modules.php` (versiones) | ⏳ Pendiente — FASE 3 |
| `ChangelogParserService` | ⏳ Pendiente — FASE 3 |
| `ModuleVersionBadge` (Livewire) | ⏳ Pendiente — FASE 3 |
| Integración en vistas de módulos | ⏳ Pendiente — FASE 3 |

**Observación:** ADR-004 declara `config/modules.php` como fuente de verdad pendiente de implementación. El archivo `config/modules.php` existe en el repositorio y contiene la configuración del paquete `nwidart/laravel-modules` (namespace, stubs, paths, activators), no datos de versiones. La fuente de verdad de versiones actualmente en uso es `config/versiones.php`, que no está mencionada en ADR-004.

---

## 6. Commits relacionados con implementaciones y documentos auditados

| Identificador | Commit | Fecha | Mensaje |
|--------------|--------|-------|---------|
| ADR-005 | `dc25149` | 2026-06-08 13:10 | `docs(adr): add ADR-005 documentation governance` |
| ADR-006 | `d995af0` | 2026-06-08 13:37 | `docs(adr): add ADR-006 agent responsibilities and delivery contracts` |
| AUDIT-004 | `a6f983f` | 2026-06-08 13:52 | `docs(audit): persist AUDIT-004 API authentication assessment` |
| IMPL-004 (plan) | `4186475` | 2026-06-08 13:19 | `docs(plan): add PLAN-IMPL-004 float to decimal migration plan` |
| IMPL-004 (exec) | `b7b1bc2` | 2026-06-08 13:21 | `fix(inventario): migrate bienes.precio from FLOAT to DECIMAL(12,2)` |
| IMPL-007 | `3b3054e` | 2026-06-08 14:17 | `feat(inventario): complete initial inventory data load — IMPL-007` |
| IMPL-GIT-001 (doc) | `54f20e3` | 2026-06-08 12:34 | `docs(git): document repository recovery and migration` |
| IMPL-GIT-001 (exec) | `470958f` | 2026-06-08 12:34 | `chore(git): finalize repository migration and tracking configuration` |

**Commit previo a los anteriores (seeder fix, sin identificador):**

| Commit | Fecha | Mensaje |
|--------|-------|---------|
| `d539e19` | 2026-06-08 14:15 | `fix(inventario): correct seeder bugs before initial data load` |

---

## 7. Correspondencia entre versiones declaradas, tags, changelogs e implementaciones

### 7.1. BhagamApps

| Versión | Tag git | Changelog (`bhagamapps.md`) | Implementaciones mencionadas |
|---------|---------|----------------------------|------------------------------|
| v1.0.0 | `BhagamApps-v1.0.0` (orphan) | ✅ v1.0.0 | — |
| v1.1.0 | `BhagamApps-v1.1.0` (orphan) | ✅ v1.1.0 | — |
| v1.2.0 | `BhagamApps-v1.2.0` (orphan) | ✅ v1.2.0 | CrudGenerator v1.0.0→v1.1.0, User v2.0.0 |
| v1.3.0 | `BhagamApps-v1.3.0` (orphan) | ✅ v1.3.0 | IMPL-001, IMPL-002 |
| **v1.4.0** | `BhagamApps-v1.4.0` (**árbol actual**) | ✅ v1.4.0 | AUDIT-001, IMPL-003, AUDIT-004 (mención), IMPL-007 (sin ID) |

### 7.2. Inventario

| Versión | Tag git | Changelog (`inventario.md`) | Implementaciones mencionadas |
|---------|---------|----------------------------|------------------------------|
| v2.0.0 | `Inventario-v2.0.0` (orphan) | ✅ v2.0.0 | — |
| v2.1.0 | `Inventario-v2.1.0` (orphan) | ✅ v2.1.0 | — |
| v2.1.1 | `Inventario-v2.1.1` (orphan) | ❌ Ausente | — |
| v2.2.0 | `Inventario-v2.2.0` (orphan) | ❌ Ausente | — |
| v2.2.1 | `Inventario-v2.2.1` (orphan) | ❌ Ausente | — |
| v2.2.2 | `Inventario-v2.2.2` (orphan) | ❌ Ausente | — |
| v2.3.0 | `Inventario-v2.3.0` (orphan) | ❌ Ausente | — |
| v2.3.1 | `Inventario-v2.3.1` (orphan) | ✅ v2.3.1 | — |
| v2.3.2 | `Inventario-v2.3.2` (orphan) | ✅ v2.3.2 | — |
| v2.3.3 | `Inventario-v2.3.3` (orphan) | ❌ Ausente | — |
| v2.3.4 | `Inventario-v2.3.4` (orphan) | ✅ v2.3.4 | — |
| v2.3.5 | `Inventario-v2.3.5` (orphan) | ✅ v2.3.5 | — |
| v2.3.6 | `Inventario-v2.3.6` (orphan) | ✅ v2.3.6 | — |
| **v2.4.0** | ❌ No existe tag | ✅ v2.4.0 | IMPL-001, IMPL-002 |

**Versiones afectadas por IMPL-004 (FLOAT→DECIMAL) e IMPL-007 (carga datos):** No aparecen como entradas propias en `inventario.md`. `bhagamapps.md` v1.4.0 contiene la mención de carga de datos bajo sección `### Data Migration` sin identificador `[IMPL-007]`.

### 7.3. User

| Versión | Tag git | Changelog (`user.md`) | Implementaciones mencionadas |
|---------|---------|----------------------|------------------------------|
| Users v1.0.0 | `Users-v1.0.0` (orphan) | ✅ Users v1.0.0 | — |
| Users v1.1.0 | `Users-v1.1.0` (orphan) | ✅ Users v1.1.0 | — |
| Users v1.1.1 | `Users-v1.1.1` (orphan) | ✅ Users v1.1.1 | — |
| v2.0.0 | `User-v2.0.0` (orphan) | ✅ v2.0.0 | — |
| **v2.1.0** | ❌ No existe tag | ✅ v2.1.0 | IMPL-001, IMPL-002 |
| **v2.1.1** | ❌ No existe tag | ✅ v2.1.1 | IMPL-003 |

### 7.4. Apps

| Versión | Tag git | Changelog (`apps.md`) | Implementaciones mencionadas |
|---------|---------|----------------------|------------------------------|
| v1.0.0 | `App-v1.0.0` (orphan) | ✅ v1.0.0 | — |

**Observación:** El tag usa nombre `App` (singular); el módulo y el changelog usan `Apps` (plural).

### 7.5. CrudGenerator

| Versión | Tag git | Changelog (`crudgenerator.md`) | Implementaciones mencionadas |
|---------|---------|-------------------------------|------------------------------|
| v1.0.0 | `CrudGenerator-v1.0.0` (orphan) | ✅ v1.0.0 | — |
| v1.1.0 | `CrudGenerator-v1.1.0` (orphan) | ✅ v1.1.0 | — |

---

## 8. Documentos `docs/impl/` y su registro en changelogs

| Documento | Estado del IMPL | Registrado en changelog |
|-----------|----------------|------------------------|
| `IMPL-001-Critical-Fixes.md` | COMPLETADO | ✅ `bhagamapps.md` v1.3.0, `inventario.md` v2.4.0, `user.md` v2.1.0 |
| `IMPL-002-Security-Hardening.md` | COMPLETADO | ✅ `bhagamapps.md` v1.3.0, `inventario.md` v2.4.0, `user.md` v2.1.0 |
| `IMPL-003-PermissionRole-Cleanup.md` | COMPLETADO | ✅ `bhagamapps.md` v1.4.0, `user.md` v2.1.1 |
| `IMPL-004.md` | COMPLETADO | ❌ Ausente en todos los changelogs |
| `IMPL-007.md` | COMPLETADO | ⚠️ Parcial — `bhagamapps.md` v1.4.0 sin ID `[IMPL-007]`, ausente en `inventario.md` |
| `IMPL-GIT-001.md` | COMPLETADO | ❌ Ausente en todos los changelogs |

---

## 9. Documentos `docs/adr/` y su registro en changelogs

| Documento | Registrado en changelog |
|-----------|------------------------|
| `ADR-001-Modular-Architecture.md` | ✅ `bhagamapps.md` v1.3.0 (mención general "ADRs documentados") |
| `ADR-002-Livewire-First-Strategy.md` | ✅ `bhagamapps.md` v1.3.0 (mención general) |
| `ADR-003-CrudGenerator-Centric-Development.md` | ✅ `bhagamapps.md` v1.3.0 (mención general) |
| `ADR-004-Modular-Versioning.md` | ✅ `bhagamapps.md` v1.3.0 (mención general) |
| `ADR-005-Documentation-and-Repository-Governance.md` | ❌ Ausente en todos los changelogs |
| `ADR-006-Agent-Responsibilities-and-Delivery-Contracts.md` | ❌ Ausente en todos los changelogs |

---

## 10. Secciones no estándar detectadas en changelogs

Los siguientes encabezados de sección encontrados en los changelogs NO están definidos como válidos en Keep a Changelog (secciones válidas: `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, `Security`):

| Archivo | Versión | Sección no estándar |
|---------|---------|---------------------|
| `docs/changelog/bhagamapps.md` | v1.4.0 | `### Data Migration` |
| `CHANGELOG.md` (raíz) | v1.4.0 | `### Documentation` |
| `docs/changelog/bhagamapps.md` | v1.4.0 | `### Documentation` |
| `docs/changelog/bhagamapps.md` | v1.3.0 | `### Documentation` |
| `docs/changelog/user.md` | v2.1.0 | `### Security` (válida, pero en este archivo también hay entradas `Fixed` y `Security` mezcladas — sin anomalía formal) |

**Nota:** `### Documentation` no figura en la especificación Keep a Changelog. Está presente en múltiples entradas. `### Data Migration` aparece únicamente en `bhagamapps.md` v1.4.0.

---

*Fin del documento EVIDENCE-AUDIT-005*
*Generado por: ⚙️ Implementación (Claude Code) — 2026-06-08*
*No commitear sin instrucción explícita de Dirección General.*
