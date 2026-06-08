{{-- Versión clicable que abre el modal del historial de cambios --}}
<a href="#"
   data-toggle="modal"
   data-target="#{{ $modalId }}"
   class="text-muted"
   style="text-decoration: none; font-size: inherit;">v{{ $version }}</a>

{{--
    El modal se teleporta a <body> para evitar el stacking context del footer fijo.
    AdminLTE aplica position:fixed + z-index:1032 al .main-footer, lo que atrapa al
    .modal dentro de ese contexto y lo deja por debajo del .modal-backdrop (z-index:1040).
    appendTo('body') lo saca de ese contexto y restaura el orden correcto de capas.
--}}
<div class="modal fade"
     id="{{ $modalId }}"
     tabindex="-1"
     role="dialog"
     aria-labelledby="{{ $modalId }}-label"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">

            <div class="modal-header py-2">
                <h6 class="modal-title font-weight-bold" id="{{ $modalId }}-label">
                    {{ $module }}
                    <span class="badge badge-secondary ml-1">v{{ $version }}</span>
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body py-2">
                {!! $parsedChangelog !!}
            </div>

            <div class="modal-footer py-1">
                <button type="button"
                        class="btn btn-sm btn-secondary"
                        data-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>

{{--
    Teleporta el modal al <body> para romper el stacking context del footer fijo.
    DOMContentLoaded garantiza que el modal ya existe en el DOM antes de moverlo.
    Se usa vanilla JS para no depender de que jQuery esté cargado en este momento
    (AdminLTE carga jQuery en @section('adminlte_js'), DESPUÉS del footer HTML).
--}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('{{ $modalId }}');
        if (el && el.parentNode !== document.body) {
            document.body.appendChild(el);
        }
    });
</script>
