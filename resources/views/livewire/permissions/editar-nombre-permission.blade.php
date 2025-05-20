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
        <div class="d-flex justify-content-between align-items-center">
            {{-- Texto editable con doble click para escritorio --}}
            <span 
                class="editable-desktop" 
                ondblclick="@this.call('editar')" 
                style="cursor: pointer;"
            >
                {{ $nombre }}
            </span>

            {{-- Botón visible solo en móvil --}}
            <button 
                wire:click="editar" 
                class="btn btn-sm btn-outline-primary d-md-none"
            >
                Editar
            </button>
        </div>
    @endif
</div>
