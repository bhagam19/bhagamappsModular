<div class="d-flex align-items-center justify-content-between flex-nowrap w-100 py-0 overflow-hidden">

    {{-- Mostrar en versión móvil (pantallas pequeñas) --}}
    <div class="d-block w-100">
        @if (!$editandoDetalle)
            <div class="d-flex align-items-center justify-content-between flex-nowrap w-100 py-0 overflow-hidden">
                <div class="text-truncate me-1" style="white-space: nowrap;">
                    {{-- Etiqueta estilo badge (solo en móvil) --}}
                    <span class="badge badge-light border border-primary text-muted small px-2 py-1 d-sm-none">
                        Detalle:
                    </span>

                    @php
                        $cambioPendiente = $this->detalleTieneAprobacionPendiente();
                        $valoresNuevos = $cambioPendiente ? json_decode($cambioPendiente->valor_nuevo, true) : [];
                    @endphp

                    @if ($detalle && collect($detalle)->filter()->isNotEmpty())
                        <small class="d-block mt-1 text-muted">
                            @foreach (['car_especial', 'marca', 'color', 'tamano', 'material', 'otra'] as $attr)
                                @php
                                    $valorActual = $detalle[$attr] ?? null;
                                    $nuevoValor = $valoresNuevos[$attr] ?? $valorActual;
                                    $hayCambio = array_key_exists($attr, $valoresNuevos);

                                    // Si hay cambio, determinar el estilo del badge
                                    $badgeClass = !$hayCambio
                                        ? ''
                                        : (empty($nuevoValor)
                                            ? 'bg-danger text-white'
                                            : 'bg-info text-dark');

                                    // Qué mostrar:
                                    // → Si eliminando → mostrar *valor anterior* (lo que se va a eliminar)
                                    // → Si modificando → mostrar nuevo valor
                                    $mostrar =
                                        empty($nuevoValor) && $hayCambio
                                            ? $valorActual ?? 'null'
                                            : ($nuevoValor ?:
                                            'null');
                                @endphp

                                @if (!empty($valorActual) || $hayCambio)
                                    <span
                                        @if ($hayCambio) class="badge {{ $badgeClass }}" title="Anterior: {{ $valorActual ?? 'null' }} → Nuevo: {{ empty($nuevoValor) ? 'null' : $nuevoValor }}" @endif>
                                        {{ $mostrar }}
                                        @if ($hayCambio)
                                            <i class="fas fa-hourglass-half ms-1"></i>
                                        @endif
                                    </span> |
                                @endif
                            @endforeach
                        </small>
                    @else
                        <span class="text-muted fst-italic">Sin detalles</span>
                    @endif
                </div>

                {{-- Botón editar --}}
                @if (auth()->user()->hasPermission('editar-bienes'))
                    <button wire:click="toggleEdit" class="btn btn-sm text-primary p-0 ms-2"
                        aria-label="Editar detalles">
                        <i class="fas fa-edit"></i>
                    </button>
                @endif
            </div>
        @else
            {{-- Formulario directo SOLO en móvil --}}
            <form wire:submit.prevent="actualizar" class="mt-2">
                @foreach (['car_especial', 'marca', 'color', 'tamano', 'material', 'otra'] as $campo)
                    <div class="row align-items-center mb-2">
                        <div class="col-4">
                            <label class="small text-muted mb-0">
                                {{ ucfirst(str_replace('_', ' ', $campo)) }}
                            </label>
                        </div>
                        <div class="col-8 text-end">
                            <input type="text" wire:model.defer="detalle.{{ $campo }}"
                                class="form-control form-control-sm d-inline-block w-auto"
                                placeholder="{{ ucfirst(str_replace('_', ' ', $campo)) }}">
                        </div>
                    </div>
                @endforeach

                <button type="submit" class="btn btn-sm btn-primary mt-2">
                    Guardar cambios
                </button>
            </form>
        @endif
    </div>


    {{-- Mostrar en escritorio --}} {{-- La implementación de este modal la dejé en standby para revisarla luego --}}
    <div class="d-none align-items-center w-100">
        <div class="text-truncate me-2" style="white-space: nowrap;">
            @if ($detalle && collect($detalle)->filter()->isNotEmpty())
                <small class="d-block mt-1 text-muted">
                    @foreach (['car_especial', 'marca', 'color', 'tamano', 'material', 'otra'] as $attr)
                        @if (!empty($detalle[$attr]))
                            {{ $detalle[$attr] }} |
                        @endif
                    @endforeach
                </small>
            @else
                <span class="text-muted fst-italic">Sin detalles</span>
            @endif
        </div>

        @if (auth()->user()->hasPermission('editar-bienes'))
            {{-- BOTÓN PARA ABRIR EL MODAL EN ESCRITORIO --}}
            <button type="button" class="btn btn-sm text-primary p-0 ms-2"
                onclick="abrirModalDetalles({{ $bienId }})">
                <i class="fas fa-edit"></i>
            </button>
        @endif
    </div>
</div>
