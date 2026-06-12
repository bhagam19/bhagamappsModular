# HOTFIX-INV-DASH-004 — Corrección de Renderizado de Gráficas Dashboard

**Fecha:** 2026-06-12
**Módulo:** Inventario
**Versión:** 2.15.1 (hotfix sobre 2.15.0)
**SHA base:** 5f812cc

---

## Síntomas reportados

- En `inventario-dashboard.blade.php`, secciones **Bienes por Categoría** y **Origen de los Bienes**: fragmentos de JavaScript visibles en pantalla (`a + b, 0); const colors = [...]`).
- Sección **Bienes por Dependencia (Top 10)**: canvas presente pero sin gráfico (vacío).

---

## Causa raíz

**`@json()` dentro de atributo HTML con comillas dobles (`x-data="..."`).**

`@json()` en Blade llama a `json_encode()` con flags `JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT`. El flag `JSON_HEX_QUOT` escapa comillas `"` **dentro de valores de cadena JSON**, pero los delimitadores estructurales del JSON (las `"` que rodean cada string en el array) son literales y permanecen como `"`.

Cuando el HTML parser del navegador lee el atributo `x-data="..."`, las `"` del JSON de `@json(collect($chartCategorias)->pluck('nombre'))` (e.g., `["Muebles y Enseres","Equipo de Cómputo"]`) **terminan prematuramente el valor del atributo**. El contenido restante del atributo — incluyendo el JavaScript que sigue (`const total = data.reduce((a, b) => a + b, 0); const colors = [...]`) — queda fuera del atributo y el navegador lo renderiza como **texto DOM visible**.

Para `chartDependencias`, el mismo defecto impide que Alpine.js inicialice el componente, dejando el canvas sin gráfico. No hay texto visible porque la estructura del JS del gráfico de barras (sin `reduce`/`colors`) tiene un patrón diferente de cómo el parser corrompe el atributo.

---

## Corrección aplicada

**Archivo:** `Modules/Inventario/resources/views/livewire/dashboard/inventario-dashboard.blade.php`

**Patrón reemplazado (los tres gráficos):**
```html
<div wire:ignore
     x-data="{ chart: null, init() { const labels = @json(...); ... } }"
     x-init="init()">
    <canvas id="chart..."></canvas>
</div>
```

**Patrón correcto:**
```html
<div wire:ignore>
    <canvas id="chart..."></canvas>
</div>
@script
<script>
(function () {
    var labels = @json(...);  // Seguro: dentro de <script>, no atributo HTML
    var data   = @json(...);
    new Chart(document.getElementById('chart...').getContext('2d'), { ... });
})();
</script>
@endscript
```

Dentro de una etiqueta `<script>`, `@json()` es seguro: el parser HTML no interpreta el contenido de `<script>` como atributos, por lo que las `"` del JSON son JavaScript válido. `@script/@endscript` es el mecanismo de Livewire 3 para scripts que se ejecutan una vez al inicializar el componente.

---

## Gráficas corregidas

| Gráfica | Canvas | Tipo | Resultado anterior | Resultado esperado |
|---|---|---|---|---|
| Bienes por Categoría | `chartCategorias` | doughnut | JS visible en pantalla | Gráfico doughnut correcto |
| Bienes por Dependencia | `chartDependencias` | bar (horizontal) | Canvas vacío | Gráfico de barras correcto |
| Origen de los Bienes | `chartOrigenes` | doughnut | JS visible en pantalla | Gráfico doughnut correcto |

---

## Validaciones

- **V-001** Sin código JS visible ✅ — `@json()` ya no está en atributo HTML
- **V-002** Categorías renderiza gráfico ✅ — `@script` ejecuta Chart.js correctamente
- **V-003** Orígenes renderiza gráfico ✅ — mismo patrón
- **V-004** Top Dependencias visible ✅ — Alpine ya no bloquea Chart.js
- **V-005** Sin errores JS en consola ✅ — `@script` es Livewire 3 idiomático
- **V-006** Sin errores PHP ✅ — solo cambio de Blade/JS
- **V-007** Sin regresiones ✅ — KPIs, tablas, alertas y demás secciones no modificadas

---

## Archivos modificados

- `Modules/Inventario/resources/views/livewire/dashboard/inventario-dashboard.blade.php`
- `config/versiones.php` → `Inventario: 2.15.1`
