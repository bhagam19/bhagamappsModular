<div>
    @if ($editando)
        <select class="form-control" wire:model.lazy="role_id" wire:change="guardar" wire:blur="guardar" autofocus>
            <option value="">-- Selecciona una opción --</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}">{{ $role->nombre }}</option>
            @endforeach
        </select>
    @else
        <div class="d-flex justify-content-between align-items-center">
            {{-- Texto editable con doble click para escritorio --}}
            <span class="editable-desktop" ondblclick="@this.call('editar')" style="cursor: pointer;">
                {{ $user->role->nombre }}
            </span>

            {{-- Botón visible solo en móvil --}}
            @if (auth()->user()->hasPermission('editar-usuarios'))
                <button wire:click="editar" class="btn btn-sm btn-outline-primary d-md-none">
                    Editar
                </button>
            @endif
        </div>
    @endif
</div>
