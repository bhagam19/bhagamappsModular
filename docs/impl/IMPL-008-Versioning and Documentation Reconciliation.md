# IMPL-008 — Versioning and Documentation Reconciliation

**Fecha:** 2026-06-08
**Estado:** COMPLETADO
**Plan de referencia:** PLAN-IMPL-008
**Riesgo final:** BAJO

---

## Objetivo

Restablecer la coherencia entre versiones declaradas, tags Git, changelogs, ADR-004
y VERSIONING.md, conforme a los hallazgos de AUDIT-005 (EVIDENCE-AUDIT-005).

---

## Hallazgos corregidos

| ID | Hallazgo | Acción aplicada |
|----|----------|-----------------|
| H-001 | Versiones sin tag Git | Tags creados: `User-v2.1.0`, `User-v2.1.1`, `Inventario-v2.4.0`, `Inventario-v2.4.1`, `Inventario-v2.4.2` |
| H-002 | Tags sin changelog | Entradas añadidas en `inventario.md`: v2.1.1, v2.2.0, v2.2.1, v2.2.2, v2.3.0, v2.3.3 |
| H-003 | IMPL-004 sin registro | Registrado en `inventario.md` como v2.4.1 y en `bhagamapps.md` v1.4.1 |
| H-004 | IMPL-007 parcialmente registrado | Registrado en `inventario.md` como v2.4.2; `bhagamapps.md` v1.4.1 con referencia `[IMPL-007]` |
| H-005 | Divergencia ADR-004 vs implementación | ADR-004 actualizado: `config/modules.php` → `config/versiones.php` |
| H-008 | Documentos sin registro en changelog | IMPL-GIT-001, ADR-005, ADR-006 registrados en `bhagamapps.md` v1.4.1 |
| H-009 | Tag de plataforma rezagado | `BhagamApps-v1.4.1` creado en HEAD |

---

## Fase A — Normalización Documental

### docs/changelog/inventario.md

Entradas añadidas (H-002):
- v2.1.1 (2025-06-01): formulario desplegable en vista móvil
- v2.2.0 (2025-06-02): acta de entrega inicial
- v2.2.1 (2025-06-02): mejoras de impresión del acta
- v2.2.2 (2025-06-04): paginación, diseño y encabezado del acta
- v2.3.0 (2025-06-04): gestión de cambios pendientes y notificaciones
- v2.3.3 (2025-06-21): gestión de eliminaciones de bienes

Entradas añadidas (H-003, H-004):
- v2.4.1 (2026-06-08): IMPL-004 — FLOAT → DECIMAL(12,2)
- v2.4.2 (2026-06-08): IMPL-007 — carga inicial 1,420 bienes

### docs/changelog/bhagamapps.md

- v1.4.0: referencia `[IMPL-007]` añadida; sección `### Documentation` normalizada.
- v1.4.1 (nueva): IMPL-004, IMPL-007, IMPL-GIT-001, ADR-005, ADR-006,
  BASELINE-001, PMP-001, ROADMAP-001, EVIDENCE-AUDIT-005.

### CHANGELOG.md (raíz)

- Entrada v1.4.1 añadida.

---

## Fase B — Normalización de Versionado

### Tags creados en árbol actual (main)

| Tag | Apunta a | Commit | Justificación |
|-----|----------|--------|---------------|
| `User-v2.1.0` | `3f6d944` | 2026-06-08 12:22 | Estado de producción que incluye IMPL-001/002 (User security) |
| `User-v2.1.1` | `3f6d944` | 2026-06-08 12:22 | Estado de producción que incluye IMPL-003 (permission_role cleanup) |
| `Inventario-v2.4.0` | `3f6d944` | 2026-06-08 12:22 | Estado de producción que incluye IMPL-001/002 (Inventario security) |
| `Inventario-v2.4.1` | `b7b1bc2` | 2026-06-08 13:21 | Commit exacto de IMPL-004 (FLOAT→DECIMAL) |
| `Inventario-v2.4.2` | `3b3054e` | 2026-06-08 14:17 | Commit exacto de IMPL-007 (data load) |

### Versiones imposibles de reconstruir con tag individual

`User-v2.1.0` y `User-v2.1.1` apuntan al mismo commit `3f6d944` porque ambos
cambios (IMPL-001/002 y IMPL-003) formaban parte del estado de producción previo
a la migración Git y fueron capturados juntos en el commit de baseline. No existe
un commit intermedio que represente solo IMPL-001/002 sin IMPL-003.

---

## Fase C — Regularización ADR-004

**Opción adoptada:** Opción 1 — Actualizar ADR-004 para reflejar la implementación real.

**Cambios en `docs/adr/ADR-004-Modular-Versioning.md`:**
- Estado actualizado.
- Sección 3: `config/modules.php` → `config/versiones.php`.
- Estructura de datos actual documentada.
- Estructura futura (FASE 3) descrita como propuesta.
- Referencia a `config/modules.php` en consecuencias corregida.
- Estado de implementación actualizado: `config/versiones.php` marcado como ✅.
- Historial de revisiones del ADR añadido.

**Cambios en `VERSIONING.md`:**
- Tabla de versiones actualizada (BhagamApps v1.4.1, Inventario v2.4.2).
- Sección de configuración centralizada actualizada con nota explicativa.

---

## Fase D — Tag de Plataforma

- `BhagamApps-v1.4.1` creado en HEAD (commit del commit final de IMPL-008).
- `config/versiones.php` actualizado: BhagamApps → '1.4.1', Inventario → '2.4.2'.

---

## Criterios de aceptación verificados

| CA | Criterio | Estado |
|----|----------|--------|
| CA-01 | Todas las implementaciones auditadas registradas en changelog | ✅ |
| CA-02 | Versiones activas con tag o justificación documentada | ✅ |
| CA-03 | ADR-004 refleja implementación real | ✅ |
| CA-04 | VERSIONING.md consistente con ADR-004 | ✅ |
| CA-05 | Plataforma tiene tag vigente en HEAD | ✅ |
| CA-06 | Hallazgos H-001, H-002, H-003, H-004, H-005, H-008, H-009 cerrados | ✅ |
