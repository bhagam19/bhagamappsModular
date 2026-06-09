<div>
    @if ($editando)
        <input type="number" class="form-control form-control-sm"
               style="width: 70px;"
               wire:model.lazy="orden"
               wire:blur="guardar"
               wire:keydown.enter="guardar"
               wire:keydown.escape="$set('editando', false)"
               min="0" max="999"
               autofocus>
        @error('orden') <div class="text-danger small">{{ $message }}</div> @enderror
    @else
        <span class="badge badge-light border" ondblclick="@this.call('editar')" style="cursor: pointer;" title="Doble clic para editar">
            {{ $orden }}
        </span>
        @if (auth()->user()->hasPermission('administrar-apps'))
            <button wire:click="editar" class="btn btn-sm btn-outline-primary d-md-none ml-1">
                <i class="fas fa-pencil-alt"></i>
            </button>
        @endif
    @endif
</div>
