# ADR-004 — Versionado Modular y Consulta de Historial desde la Interfaz

**Estado:** Aceptado — implementación parcial (config + changelogs activos; UI pendiente FASE 3)
**Fecha:** 2026-06-08
**Contexto:** BhagamApps Modular — trazabilidad de cambios y transparencia con usuarios

---

## Contexto

A medida que BhagamApps crece en producción con usuarios reales (116 usuarios activos,
institución educativa), surge la necesidad de:

1. **Trazabilidad interna:** saber exactamente qué versión de cada módulo está
   corriendo en producción y qué cambios incluye.
2. **Transparencia con usuarios:** los usuarios (rectores, coordinadores, docentes)
   deben poder conocer qué funcionalidades tienen disponibles y qué cambió en
   cada actualización, sin necesidad de consultar archivos de texto planos.
3. **Independencia de evolución:** el módulo Inventario puede recibir 5 actualizaciones
   mientras User solo recibe 1. Un versionado global único no captura esta realidad.
4. **Sin historial git:** el proyecto no tiene repositorio git. La documentación en
   archivos Markdown es el único registro histórico disponible.

---

## Decisión

### 1. Changelog por módulo

Cada módulo tiene su propio archivo de historial en `docs/changelog/<modulo>.md`.

**Reglas del changelog:**
- La versión más reciente aparece primero.
- Las versiones antiguas se conservan siempre (nunca eliminar historial).
- El formato sigue [Keep a Changelog](https://keepachangelog.com/es/1.0.0/).
- Las secciones válidas son: `Added`, `Changed`, `Fixed`, `Security`, `Deprecated`, `Removed`.

**Fuentes de changelog:**
| Archivo | Scope |
|---|---|
| `docs/changelog/bhagamapps.md` | Cambios transversales y de plataforma |
| `docs/changelog/inventario.md` | Solo cambios en `Modules/Inventario/` |
| `docs/changelog/user.md` | Solo cambios en `Modules/User/` |
| `docs/changelog/apps.md` | Solo cambios en `Modules/Apps/` |
| `docs/changelog/crudgenerator.md` | Solo cambios en `Modules/CrudGenerator/` |

El archivo raíz `CHANGELOG.md` es un **resumen ejecutivo** de la plataforma,
no un duplicado de los changelogs modulares.

### 2. Versionado independiente por módulo

Cada módulo tiene una versión `Major.Minor.Patch` independiente. La versión de un
módulo solo cambia cuando ese módulo recibe cambios. Ver `VERSIONING.md` para
las reglas de incremento.

### 3. Configuración centralizada en config/versiones.php

> **Corrección IMPL-008 (2026-06-08):** La propuesta original nombraba `config/modules.php`
> como fuente de verdad. Ese archivo ya existe como configuración del paquete
> `nwidart/laravel-modules` (namespace, stubs, activators) y no puede usarse para
> versiones. La implementación real utiliza `config/versiones.php`.

La **fuente de verdad de versiones para la interfaz de usuario** es:

```php
// config/versiones.php
return [
    'BhagamApps'    => '1.4.1',
    'Inventario'    => '2.4.2',
    'User'          => '2.1.1',
    'Apps'          => '1.0.0',
    'CrudGenerator' => '1.1.0',
];
```

Este archivo es el único lugar donde se actualiza la versión al desplegar. Las
vistas leen `config('versiones.Inventario')`, nunca hardcodean la versión.

**Estructura futura (FASE 3):** Cuando se implemente `ChangelogParserService`,
`config/versiones.php` se extenderá para incluir la ruta del changelog:

```php
// config/versiones.php — estructura FASE 3 (propuesta)
return [
    'Inventario' => [
        'version'   => '2.4.2',
        'changelog' => 'docs/changelog/inventario.md',
    ],
    // ...
];
```

### 4. Consulta de historial desde la interfaz

Cada módulo mostrará su versión actual como un elemento interactivo. Al hacer clic,
se presenta el historial completo de cambios parseado desde el archivo `.md`.

**Flujo de datos:**
```
config/versiones.php → versión actual
docs/changelog/<modulo>.md → historial de cambios
    ↓ parseado por ChangelogParserService
    ↓ renderizado por ModuleVersionBadge (Livewire)
    ↓ mostrado en la UI del módulo
```

### 5. Visualización para usuarios finales

El historial de cambios es visible para todos los usuarios autenticados, no solo
para administradores. El objetivo es que un rector o coordinador pueda entender
qué cambió en el módulo de inventario sin necesidad de contactar al administrador.

---

## Consecuencias

### Positivas
- Trazabilidad completa de qué versión corre en producción.
- Transparencia con usuarios finales sobre el estado del sistema.
- `docs/changelog/` es la única fuente de verdad — no hay duplicación.
- La UI siempre refleja la documentación real.

### Negativas y mitigaciones

| Consecuencia | Mitigación |
|---|---|
| El archivo `config/versiones.php` debe actualizarse manualmente en cada deploy | Es un archivo de una línea por módulo; el proceso es parte del workflow de deploy documentado en `VERSIONING.md` |
| El `ChangelogParserService` debe parsear Markdown sin librería externa | El formato del changelog es estricto y predecible: `## v1.2.0 — FECHA`. Se puede parsear con regex simples. |
| Un changelog malformado rompe la UI | El parser debe ser tolerante a fallos y mostrar mensaje de error en lugar de crashear. |

---

## Alternativas consideradas

- **Versión única global para toda la plataforma:** descartado porque oculta la
  evolución independiente de cada módulo.
- **Versión hardcodeada en cada vista:** descartado por el riesgo de inconsistencias
  entre lo que muestra la UI y la versión real.
- **Base de datos como fuente de versiones:** descartado por complejidad innecesaria;
  los archivos de configuración son suficientes y más fáciles de auditar.
- **No mostrar versiones a usuarios:** descartado porque la transparencia mejora
  la confianza de los usuarios en el sistema y reduce las consultas de soporte.

---

## Estado de implementación

| Componente | Estado |
|---|---|
| `docs/changelog/<modulo>.md` | ✅ Implementado |
| `VERSIONING.md` | ✅ Implementado |
| `config/versiones.php` (fuente de verdad actual) | ✅ Implementado |
| `ChangelogParserService` | ⏳ Pendiente — FASE 3 |
| `ModuleVersionBadge` (Livewire) | ⏳ Pendiente — FASE 3 |
| Integración en vistas de módulos | ⏳ Pendiente — FASE 3 |
| Migración de `config/versiones.php` a estructura extendida (FASE 3) | ⏳ Pendiente — FASE 3 |

Ver `docs/architecture/MODULE_VERSIONING_UI.md` para la especificación técnica completa.

> **Historial de revisiones de este ADR:**
> - 2026-06-08 — Creación inicial.
> - 2026-06-08 — **IMPL-008**: Corrección de referencia `config/modules.php` →
>   `config/versiones.php`; actualización del estado de implementación.
