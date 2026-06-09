<div class="d-flex align-items-center">
    @if ($editando)
        <input type="color" class="form-control form-control-sm p-0"
               style="width: 40px; height: 28px; cursor: pointer;"
               wire:model.lazy="color"
               wire:blur="guardar"
               wire:keydown.escape="$set('editando', false)"
               autofocus>
        <button wire:click="guardar" class="btn btn-sm btn-success ml-1">
            <i class="fas fa-check"></i>
        </button>
        @error('color') <div class="text-danger small">{{ $message }}</div> @enderror
    @else
        <span ondblclick="@this.call('editar')" style="cursor: pointer;" title="Doble clic para editar">
            @if ($color)
                <span class="badge" style="background-color: {{ $color }}; color: #fff; border: 1px solid #ccc;">
                    {{ $color }}
                </span>
            @else
                <small class="text-muted">—</small>
            @endif
        </span>
        @if (auth()->user()->hasPermission('administrar-apps'))
            <button wire:click="editar" class="btn btn-sm btn-outline-primary d-md-none ml-1">
                <i class="fas fa-pencil-alt"></i>
            </button>
        @endif
    @endif
</div>
