<div>
    @if ($editando)
        <input
            type="text"
            class="form-control"
            wire:model.lazy="nombre"
            wire:blur="guardar"
            wire:keydown.enter="guardar"
            autofocus
        >
    @else
        <span ondblclick="@this.call('editar')" style="cursor: pointer;">
            {{ $nombre }}
        </span>
    @endif
</div>