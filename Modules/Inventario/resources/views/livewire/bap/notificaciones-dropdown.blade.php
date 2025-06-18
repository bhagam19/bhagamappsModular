<li class="nav-item dropdown" x-data="{ openItem: null }">
    <a class="nav-link dropdown-toggle" href="#" id="notificacionesDropdown" role="button" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false" title="Cambios pendientes">
        <i class="fas fa-bell"></i>
        @if ($aprobacionesPendientes->count() > 0)
            <span class="badge badge-danger">{{ $aprobacionesPendientes->count() }}</span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0" aria-labelledby="notificacionesDropdown"
        style="width: 380px; max-height: 70vh; overflow-y: auto;">
        @if ($aprobacionesPendientes->isEmpty())
            <div class="alert alert-info m-3">
                No hay cambios pendientes por aprobar.
            </div>
        @else
            @foreach ($aprobacionesPendientes as $aprobacion)
                <div class="border-bottom">
                    <div class="dropdown-item d-flex justify-content-between align-items-center"
                        @click.stop="openItem === {{ $aprobacion->id }} ? openItem = null : openItem = {{ $aprobacion->id }}"
                        @keydown.enter.prevent="openItem === {{ $aprobacion->id }} ? openItem = null : openItem = {{ $aprobacion->id }}"
                        @keydown.space.prevent="openItem === {{ $aprobacion->id }} ? openItem = null : openItem = {{ $aprobacion->id }}"
                        tabindex="0" role="button">
                        <div>
                            <strong>{{ $aprobacion->bien->nombre ?? '—' }}</strong> |
                            {{ $aprobacion->dependencia->nombre }}<br>
                            <small class="ml-auto badge badge-primary">
                                {{ ucfirst(str_replace('_id', '', $aprobacion->campo)) }}
                            </small><small
                                class="text-muted ml-2">{{ $aprobacion->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                        <i class="fas fa-chevron-down ml-2"
                            :class="{ 'fa-rotate-180': openItem === {{ $aprobacion->id }} }"></i>
                    </div>

                    <div x-show="openItem === {{ $aprobacion->id }}" x-collapse class="px-3 py-2 text-sm">
                        <div><strong>Bien ID:</strong> {{ $aprobacion->bien_id }}</div>
                        <div><strong>Usuario:</strong> {{ $aprobacion->dependencia->usuario->nombres ?? '—' }}</div>
                        <div><strong>Valor anterior:</strong> @switch($aprobacion->campo)
                                @case('categoria_id')
                                    {{ $aprobacion->valorAnteriorCategoria->nombre ?? $aprobacion->valor_anterior }}
                                @break

                                @case('dependencia_id')
                                    {{ $aprobacion->valorAnteriorDependencia->nombre ?? $aprobacion->valor_anterior }}
                                @break

                                @case('estado_id')
                                    {{ $aprobacion->valorAnteriorEstado->nombre ?? $aprobacion->valor_anterior }}
                                @break

                                @default
                                    {{ $aprobacion->valor_anterior }}
                            @endswitch
                        </div>
                        <div><strong>Valor nuevo:</strong> <span class="text-primary">
                                @switch($aprobacion->campo)
                                    @case('categoria_id')
                                        {{ $aprobacion->valorNuevoCategoria->nombre ?? $aprobacion->valor_nuevo }}
                                    @break

                                    @case('dependencia_id')
                                        {{ $aprobacion->valorNuevoDependencia->nombre ?? $aprobacion->valor_nuevo }}
                                    @break

                                    @case('estado_id')
                                        {{ $aprobacion->valorNuevoEstado->nombre ?? $aprobacion->valor_nuevo }}
                                    @break

                                    @default
                                        {{ $aprobacion->valor_nuevo }}
                                @endswitch
                            </span></div>
                        <div><strong>Estado:</strong>
                            <span
                                class="badge 
                                {{ $aprobacion->estado === 'pendiente'
                                    ? 'badge-warning'
                                    : ($aprobacion->estado === 'aprobado'
                                        ? 'badge-success'
                                        : 'badge-danger') }}">
                                {{ ucfirst($aprobacion->estado) }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-end mt-2">
                            <button wire:click="aprobarCambio({{ $aprobacion->id }})"
                                class="btn btn-success btn-sm mr-2">
                                <i class="fas fa-check"></i> Aprobar
                            </button>
                            <button wire:click="rechazarCambio({{ $aprobacion->id }})" class="btn btn-danger btn-sm">
                                <i class="fas fa-times"></i> Rechazar
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</li>
