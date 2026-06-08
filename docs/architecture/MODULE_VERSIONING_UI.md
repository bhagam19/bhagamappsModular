# Especificación de Arquitectura — Visualización de Versiones de Módulos

**Estado:** Propuesta arquitectónica — pendiente de implementación
**Referencia:** ADR-004-Modular-Versioning
**Prioridad:** FASE 3 (después de estabilidad de datos y SSL)

---

## Objetivo

Permitir que cada módulo muestre su versión actual en su interfaz y que el
usuario pueda consultar el historial de cambios directamente desde la aplicación,
sin necesidad de acceder a los archivos de documentación.

---

## Comportamiento esperado

### Elemento de versión en encabezado de módulo

Cada página principal de módulo incluirá un elemento visible con la versión:

```
Inventario  v1.2.0
             ^^^^^^ — elemento clicable
```

- El texto `v1.2.0` debe ser visible para todos los usuarios autenticados.
- Al hacer clic, se abre un panel con el historial de cambios.
- El elemento debe ser discreto (no intrusivo) — sugerido: texto pequeño, gris.

### Panel de historial (modal o slideover)

Al hacer clic sobre la versión, se presenta:

```
┌─────────────────────────────────────┐
│  Inventario  v1.2.0                 │
│  Actualizado: 2026-06-08            │
│─────────────────────────────────────│
│  v1.2.0  ───  2026-06-08            │
│  • Middleware de permisos en rutas  │
│  • Fix: DB facade en Notificaciones │
│  • Fix: campo origen en bienes      │
│                                     │
│  v1.1.0  ───  [fecha]               │
│  • Versión inicial en producción    │
└─────────────────────────────────────┘
```

**Reglas del panel:**
- La versión más reciente aparece siempre en la parte superior.
- El historial se muestra en orden descendente (más nuevo → más antiguo).
- El contenido proviene del changelog del módulo (fuente única de verdad).
- No se duplica manualmente en la vista.

---

## Fuente de datos

La versión y el historial de cambios se obtienen desde:

1. **Versión actual:** `config/modules.php` → clave `version` del módulo.
2. **Historial de cambios:** `docs/changelog/<modulo>.md` — parseado al vuelo
   o pre-procesado en un servicio.

```php
// config/modules.php (propuesta — ver ADR-004)
return [
    'inventario' => [
        'name'      => 'Inventario',
        'version'   => '1.2.0',
        'changelog' => 'docs/changelog/inventario.md',
    ],
    'user' => [
        'name'      => 'User',
        'version'   => '1.2.0',
        'changelog' => 'docs/changelog/user.md',
    ],
    'apps' => [
        'name'      => 'Apps',
        'version'   => '1.0.0',
        'changelog' => 'docs/changelog/apps.md',
    ],
    'crudgenerator' => [
        'name'      => 'CrudGenerator',
        'version'   => '1.0.0',
        'changelog' => 'docs/changelog/crudgenerator.md',
    ],
];
```

---

## Arquitectura de implementación sugerida

### Componente Livewire (genérico)

```
Modules/<Modulo>/Livewire/Shared/ModuleVersionBadge.php
```

**Props:**
- `string $module` — clave del módulo en `config/modules.php`

**Comportamiento:**
- Lee `config('modules.' . $module)` para obtener `name` y `version`.
- Lee el archivo `changelog` y lo parsea con un servicio.
- Renderiza el badge + abre el modal al hacer clic.

### Servicio de parsing del changelog

```
app/Services/ChangelogParserService.php
```

**Responsabilidades:**
- Leer el archivo `.md` indicado en `config/modules.php`.
- Parsear la estructura de secciones `## v1.2.0 — FECHA`.
- Retornar colección de versiones con sus entradas.

### Vista parcial del badge

```blade
{{-- resources/views/partials/version-badge.blade.php --}}
<livewire:shared.module-version-badge module="inventario" />
```

Cada módulo incluye este partial en su layout o encabezado principal.

---

## Restricciones de implementación

- **La UI no debe duplicar información:** el changelog `.md` es la única fuente.
  Si se actualiza el changelog, la UI refleja el cambio automáticamente.
- **No hardcodear versiones en vistas:** solo en `config/modules.php`.
- **El historial completo debe ser accesible:** no mostrar solo las últimas N versiones.
- **La visibilidad del badge no debe depender de permisos:** todos los usuarios
  autenticados pueden ver la versión y el historial.

---

## Dependencias a implementar

| Dependencia             | Descripción                                       |
|-------------------------|---------------------------------------------------|
| `config/modules.php`    | Fuente de verdad de versiones por módulo          |
| `ChangelogParserService`| Servicio de lectura y parsing de changelogs `.md` |
| `ModuleVersionBadge`    | Componente Livewire reutilizable                  |
| `version-badge.blade.php` | Partial de vista para incluir en módulos        |

---

## Consideraciones de caché

El changelog de cada módulo cambia raramente (solo en despliegues). Se recomienda:

```php
Cache::remember("changelog_{$module}", 3600, fn () => $this->parseChangelog($path));
```

Invalidar el caché al desplegar: `php artisan cache:clear`.

---

*Este documento es solo una especificación. La implementación corresponde a FASE 3.*
*Ver también: `VERSIONING.md`, `docs/adr/ADR-004-Modular-Versioning.md`*
