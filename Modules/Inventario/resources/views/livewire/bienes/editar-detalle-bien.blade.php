<div class="d-flex align-items-center justify-content-between flex-nowrap w-100 py-0 overflow-hidden">

    @if (!$editandoDetalle)
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
            <button wire:click="toggleEdit" class="btn btn-sm text-primary p-0 ms-2" aria-label="Editar detalles">
                <i class="fas fa-edit"></i>
            </button>
        @endif
    @else
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
