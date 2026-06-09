<div>
    @if ($editando)
        <input type="text" class="form-control form-control-sm"
               wire:model.lazy="icono"
               wire:blur="guardar"
               wire:keydown.enter="guardar"
               wire:keydown.escape="$set('editando', false)"
               placeholder="fas fa-th-large"
               autofocus>
        @error('icono') <div class="text-danger small">{{ $message }}</div> @enderror
    @else
        <span ondblclick="@this.call('editar')" style="cursor: pointer;" title="Doble clic para editar">
            @if ($icono)
                <i class="{{ $icono }} mr-1"></i>
                <small class="text-muted">{{ $icono }}</small>
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
