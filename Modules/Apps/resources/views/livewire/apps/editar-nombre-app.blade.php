<div>
    @if ($editando)
        <input type="text" class="form-control form-control-sm"
               wire:model.lazy="nombre"
               wire:blur="guardar"
               wire:keydown.enter="guardar"
               wire:keydown.escape="$set('editando', false)"
               autofocus>
        @error('nombre') <div class="text-danger small">{{ $message }}</div> @enderror
    @else
        <div class="d-flex justify-content-between align-items-center">
            <strong ondblclick="@this.call('editar')" style="cursor: pointer;" title="Doble clic para editar">
                {{ $nombre }}
            </strong>
            @if (auth()->user()->hasPermission('administrar-apps'))
                <button wire:click="editar" class="btn btn-sm btn-outline-primary d-md-none ml-1">
                    <i class="fas fa-pencil-alt"></i>
                </button>
            @endif
        </div>
    @endif
</div>
