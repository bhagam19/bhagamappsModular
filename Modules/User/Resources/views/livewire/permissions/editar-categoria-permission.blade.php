<div>
    @if ($editando)
        <select 
            class="form-control"
            wire:model.lazy="categoria"
            wire:change="guardar"
            wire:blur="guardar"
            wire:keydown.enter="guardar"
            autofocus
        >
            <option value="">-- Seleccione categoría --</option>
            <option value="Usuarios">Usuarios</option>
            <option value="Roles">Roles</option>
            <option value="Permisos">Permisos</option>
            {{-- Añade aquí más opciones si hay --}}
        </select>
    @else
        <div class="d-flex justify-content-between align-items-center">
            {{-- Texto editable con doble click para escritorio --}}
            <span 
                class="editable-desktop" 
                ondblclick="@this.call('editar')" 
                style="cursor: pointer;"
            >
                {{ ucfirst($permission->categoria) }}
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
