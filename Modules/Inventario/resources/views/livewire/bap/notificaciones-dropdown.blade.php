<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="notificacionesDropdown" role="button" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false" title="Cambios pendientes">
        <i class="fas fa-bell"></i>
        @if ($cambiosPendientes->count() > 0)
            <span class="badge badge-danger">{{ $cambiosPendientes->count() }}</span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0" aria-labelledby="notificacionesDropdown"
        style="width: 380px; max-height: 70vh; overflow-y: auto;">
        @if ($cambiosPendientes->isEmpty())
            <div class="alert alert-info m-3">
                No hay cambios pendientes por aprobar.
            </div>
        @else
            @foreach ($cambiosPendientes as $cambio)
                <div class="dropdown-item border-bottom">
                    <strong>Bien ID:</strong> {{ $cambio->bien_id }}<br>
                    <strong>Bien:</strong> {{ $cambio->bien->nombre ?? 'N/A' }}<br>
                    <strong>Campo:</strong> {{ ucfirst($cambio->campo_nombre) }}<br>
                    <strong>Nuevo valor:</strong> <span
                        class="text-primary">{{ $cambio->valor_nuevo_nombre }}</span><br>
                    <small class="text-muted">{{ $cambio->created_at->format('d/m/Y H:i') }}</small>

                    <div class="mt-2 d-flex justify-content-end">
                        <button wire:click="aprobarCambio({{ $cambio->id }})" class="btn btn-success btn-sm mr-2">
                            <i class="fas fa-check"></i> Aprobar
                        </button>
                        <button wire:click="rechazarCambio({{ $cambio->id }})" class="btn btn-danger btn-sm">
                            <i class="fas fa-times"></i> Rechazar
                        </button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</li>
