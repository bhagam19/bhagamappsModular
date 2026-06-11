<div class="d-inline">
    @if ($user->bloqueado)
        @if (auth()->user()->hasPermission('desbloquear-usuarios'))
            <button type="button"
                class="btn btn-sm btn-success"
                title="Desbloquear usuario"
                wire:click="desbloquear"
                onclick="confirm('¿Desbloquear a {{ $user->nombres }}?') || event.stopImmediatePropagation()">
                <i class="fas fa-unlock"></i>
            </button>
        @else
            <span class="badge badge-danger">Bloqueado</span>
        @endif
    @else
        @if (auth()->user()->hasPermission('bloquear-usuarios'))
            <button type="button"
                class="btn btn-sm btn-secondary"
                title="Bloquear usuario"
                wire:click="bloquear"
                onclick="confirm('¿Bloquear a {{ $user->nombres }}?') || event.stopImmediatePropagation()">
                <i class="fas fa-lock"></i>
            </button>
        @endif
    @endif
</div>
