<div>
    @if ($editando)
        <input type="text" class="form-control form-control-sm"
               wire:model.lazy="ruta"
               wire:blur="guardar"
               wire:keydown.enter="guardar"
               wire:keydown.escape="$set('editando', false)"
               placeholder="/ruta/de/la/app"
               autofocus>
        @error('ruta') <div class="text-danger small">{{ $message }}</div> @enderror
    @else
        <span class="text-muted small" ondblclick="@this.call('editar')" style="cursor: pointer;" title="Doble clic para editar">
            {{ $ruta }}
        </span>
        @if (auth()->user()->hasPermission('administrar-apps'))
            <button wire:click="editar" class="btn btn-sm btn-outline-primary d-md-none ml-1">
                <i class="fas fa-pencil-alt"></i>
            </button>
        @endif
    @endif
</div>
