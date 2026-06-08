# AUDIT-001 — Changelog Modal y Revisión de Versionado

**Fecha:** 2026-06-08
**Estado:** COMPLETADO
**Tipo:** Corrección de UI + Auditoría de Semantic Versioning

---

## Parte 1 — Corrección del Modal de Changelog

### Problema observado

El modal se abría pero quedaba completamente bloqueado:

- No permitía scroll.
- No permitía interactuar con el contenido.
- No era posible cerrarlo.
- El fondo opaco (backdrop) permanecía activo sobre el modal.

### Análisis de causa raíz

**Stacking context del footer fijo de AdminLTE:**

```
config/adminlte.php: 'layout_fixed_footer' => true
↓
CSS resultante:
.layout-footer-fixed .wrapper .main-footer {
    position: fixed;
    z-index: 1032;
}
```

`position: fixed` con `z-index` explícito crea un **stacking context CSS** en
el elemento `<footer class="main-footer">`. Todo el HTML del modal queda dentro
de ese contexto, efectivamente fijado en `z-index: 1032` desde la perspectiva
global de la página.

Bootstrap, al mostrar un modal, genera dinámicamente un `<div class="modal-backdrop">`
y lo añade directamente al `<body>` con `z-index: 1040`. El resultado:

```
Orden visual (de fondo a frente):
  [3] .modal-backdrop  — body level, z-index: 1040  ← encima
  [2] .main-footer     — stacking context, z-index: 1032
  [1]   └── .modal     — dentro del footer, "z-index: 1050" es relativo al contexto
```

El backdrop queda **encima del modal**, bloqueando toda interacción.

### Solución aplicada

Teleportar el modal al `<body>` en `DOMContentLoaded`, rompiendo el stacking
context antes de que Bootstrap lo active:

**`resources/views/components/changelog-modal.blade.php`** — script añadido
al final del componente:

```javascript
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('{{ $modalId }}');
    if (el && el.parentNode !== document.body) {
        document.body.appendChild(el);
    }
});
```

Se usa `DOMContentLoaded` con vanilla JS (no jQuery) porque AdminLTE carga
jQuery en `@section('adminlte_js')`, **después** del HTML del footer. jQuery
no está disponible en el momento en que el inline script del footer se parsea.

Una vez en `<body>`, el orden correcto se restaura:

```
Orden visual (de fondo a frente):
  [3] .modal           — body level, z-index: 1050  ← encima del backdrop
  [2] .modal-backdrop  — body level, z-index: 1040
  [1] .main-footer     — stacking context, z-index: 1032
```

### Contexto Bootstrap 5 en `/ppal`

`resources/views/ppal/index.blade.php` carga Bootstrap 5 CSS vía `@push('css')`.
Esto convive con AdminLTE Bootstrap 4 (JS). El modal usa `data-toggle`/`data-dismiss`
(Bootstrap 4 JS, correcto para AdminLTE). El CSS de Bootstrap 5 aplica estilos
distintos sobre las mismas clases `.modal`, `.modal-dialog`, etc., pero no
interfiere con el comportamiento JS. Se considera **riesgo conocido y aceptado**:
el CSS de Bootstrap 5 es requerido por la vista de Apps (usa clase `ms-3`).

---

## Parte 2 — Auditoría de Semantic Versioning

### Análisis por componente

| Componente     | Versión anterior | Versión correcta | Motivo de corrección                     |
|----------------|------------------|------------------|------------------------------------------|
| **User**       | v2.2.0           | **v2.1.1** ✓     | IMPL-003 es PATCH (solo limpieza de DB)  |
| BhagamApps     | v1.4.0           | v1.4.0 ✓         | MINOR correcto (modal = nueva feature)   |
| Inventario     | v2.4.0           | v2.4.0 ✓         | Versión correcta                         |
| Apps           | v1.0.0           | v1.0.0 ✓         | Sin cambios                              |
| CrudGenerator  | v1.1.0           | v1.1.0 ✓         | Sin cambios                              |

**Solo User requería corrección.**

### Razonamiento

**User v2.2.0 → v2.1.1 (PATCH):**
IMPL-003 eliminó 76 registros duplicados de `permission_role` y añadió un
`UNIQUE` constraint. No cambió ninguna funcionalidad del módulo User, no
modificó modelos, componentes, ni vistas. Es una corrección de integridad
de datos: PATCH.

**BhagamApps v1.4.0 mantiene MINOR:**
El Changelog Modal es una nueva funcionalidad visible al usuario (nueva UI,
nuevo Blade component, nuevo sistema de parseo de changelogs). MINOR correcto.

### Inconsistencias históricas documentadas (no corregibles)

Estas versiones provienen del historial original del proyecto. No se pueden
cambiar retroactivamente sin invalidar el historial real.

| Versión        | Inconsistencia SemVer                                              |
|----------------|--------------------------------------------------------------------|
| Inventario v2.3.1 | No existe v2.2.x — salto desde v2.1.0 sin documentar la gap   |
| Inventario v2.3.4 | Incluye nueva funcionalidad (historial dependencias) → debería ser MINOR (v2.4.x) |
| Inventario v2.3.6 | Incluye nuevas features (encabezados logo, ordenamiento) → debería ser MINOR |

Estas entradas se conservan intactas. La nota sobre la gap v2.1.0 → v2.3.1
está implícita en el historial como ausencia de entradas intermedias.

### Archivos modificados

| Archivo                                    | Cambio                          |
|--------------------------------------------|----------------------------------|
| `docs/changelog/user.md`                  | v2.2.0 → v2.1.1                 |
| `config/versiones.php`                     | User: '2.2.0' → '2.1.1'        |
| `VERSIONING.md`                            | Tabla: User v2.2.0 → v2.1.1    |
| `CHANGELOG.md`                             | v1.4.0: User → v2.1.1, Added modal feature |
| `docs/changelog/bhagamapps.md`            | Entrada v1.4.0 añadida          |

---

## Parte 3 — Changelog Modal en Footer Principal

### Cambio aplicado

`resources/views/dashboard_personal/footer.blade.php`:

```blade
{{-- Antes --}}
Versión: {{ config('versiones.BhagamApps') }}

{{-- Después --}}
Versión: <x-changelog-modal module="BhagamApps" />
```

El componente `<x-changelog-modal>` busca el archivo `docs/changelog/bhagamapps.md`
(vía `strtolower('BhagamApps')` = `'bhagamapps'`) y lo parsea.

Este footer es incluido por `resources/views/ppal/index.blade.php`:
```blade
@section('footer')
    @include('dashboard_personal.footer')
@stop
```

### Consistencia del sistema

Los tres módulos que tienen footer ahora usan el mismo componente:

| Vista                                                        | Componente               |
|--------------------------------------------------------------|--------------------------|
| `Modules/Inventario/resources/views/components/footer.blade.php` | `<x-changelog-modal module="Inventario" />` |
| `Modules/User/Resources/views/components/footer.blade.php`        | `<x-changelog-modal module="User" />`       |
| `resources/views/dashboard_personal/footer.blade.php`            | `<x-changelog-modal module="BhagamApps" />` |
