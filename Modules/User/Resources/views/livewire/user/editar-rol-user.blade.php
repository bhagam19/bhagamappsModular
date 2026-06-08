<div>
    @if ($editando)
        <select class="form-control" wire:model.lazy="role_id" wire:change="guardar" wire:blur="guardar" autofocus>
            <option value="">-- Selecciona una opción --</option>
            <option value="1">Administrador</option>
            <option value="2">Rector</option>
            <option value="3">Coordinador</option>
            <option value="4">Auxiliar</option>
            <option value="5">Docente</option>
            <option value="6">Estudiante</option>
            <option value="7">Invitado</option>


        </select>
    @else
        <div class="d-flex justify-content-between align-items-center">
            {{-- Texto editable con doble click para escritorio --}}
            <span class="editable-desktop" ondblclick="@this.call('editar')" style="cursor: pointer;">
                {{ $user->role->nombre }}
            </span>

            {{-- Botón visible solo en móvil --}}
            @if (auth()->user()->hasPermission('editar-user'))
                <button wire:click="editar" class="btn btn-sm btn-outline-primary d-md-none">
                    Editar
                </button>
            @endif
        </div>
    @endif
</div>
