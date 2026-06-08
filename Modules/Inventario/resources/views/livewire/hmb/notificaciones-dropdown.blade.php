<li class="nav-item dropdown" x-data="{ openItem: null }">
    <a class="nav-link dropdown-toggle" href="#" id="notificacionesDropdown" role="button" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false" title="Cambios pendientes">
        <i class="fas fa-bell"></i>
        @if ($modificacionesPendientes->count() > 0)
            <span class="badge badge-danger">{{ $modificacionesPendientes->count() }}</span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0" aria-labelledby="notificacionesDropdown"
        style="width: 380px; max-height: 70vh; overflow-y: auto;">
        @if ($modificacionesPendientes->isEmpty())
            <div class="alert alert-info m-3">
                No hay cambios pendientes por aprobar.
            </div>
        @else
            @foreach ($modificacionesPendientes as $modificacion)
                <div class="border-bottom">
                    <div class="dropdown-item d-flex justify-content-between align-items-center"
                        @click.stop="openItem === {{ $modificacion->id }} ? openItem = null : openItem = {{ $modificacion->id }}"
                        @keydown.enter.prevent="openItem === {{ $modificacion->id }} ? openItem = null : openItem = {{ $modificacion->id }}"
                        @keydown.space.prevent="openItem === {{ $modificacion->id }} ? openItem = null : openItem = {{ $modificacion->id }}"
                        tabindex="0" role="button">
                        <div>
                            <strong>{{ $modificacion->bien->nombre ?? '—' }}</strong> |
                            {{ $modificacion->dependencia->nombre }}<br>
                            <small class="ml-auto badge badge-primary">
                                {{ ucfirst(str_replace('_id', '', $modificacion->campo)) }}
                            </small><small
                                class="text-muted ml-2">{{ $modificacion->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                        <i class="fas fa-chevron-down ml-2"
                            :class="{ 'fa-rotate-180': openItem === {{ $modificacion->id }} }"></i>
                    </div>

                    <div x-show="openItem === {{ $modificacion->id }}" x-collapse class="px-3 py-2 text-sm">
                        <div><strong>Bien ID:</strong> {{ $modificacion->bien_id }}</div>
                        <div><strong>Usuario:</strong> {{ $modificacion->dependencia->user->nombres ?? '—' }}</div>
                        <div><strong>Valor anterior:</strong> @switch($modificacion->campo)
                                @case('categoria_id')
                                    {{ $modificacion->valorAnteriorCategoria->nombre ?? $modificacion->valor_anterior }}
                                @break

                                @case('dependencia_id')
                                    {{ $modificacion->valorAnteriorDependencia->nombre ?? $modificacion->valor_anterior }}
                                @break

                                @case('estado_id')
                                    {{ $modificacion->valorAnteriorEstado->nombre ?? $modificacion->valor_anterior }}
                                @break

                                @default
                                    {{ $modificacion->valor_anterior }}
                            @endswitch
                        </div>
                        <div><strong>Valor nuevo:</strong> <span class="text-primary">
                                @switch($modificacion->campo)
                                    @case('categoria_id')
                                        {{ $modificacion->valorNuevoCategoria->nombre ?? $modificacion->valor_nuevo }}
                                    @break

                                    @case('dependencia_id')
                                        {{ $modificacion->valorNuevoDependencia->nombre ?? $modificacion->valor_nuevo }}
                                    @break

                                    @case('estado_id')
                                        {{ $modificacion->valorNuevoEstado->nombre ?? $modificacion->valor_nuevo }}
                                    @break

                                    @default
                                        {{ $modificacion->valor_nuevo }}
                                @endswitch
                            </span></div>
                        <div><strong>Estado:</strong>
                            <span
                                class="badge 
                                {{ $modificacion->estado === 'pendiente'
                                    ? 'badge-warning'
                                    : ($modificacion->estado === 'aprobado'
                                        ? 'badge-success'
                                        : 'badge-danger') }}">
                                {{ ucfirst($modificacion->estado) }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-end mt-2">
                            <button wire:click="aprobarCambio({{ $modificacion->id }})"
                                class="btn btn-success btn-sm mr-2">
                                <i class="fas fa-check"></i> Aprobar
                            </button>
                            <button wire:click="rechazarCambio({{ $modificacion->id }})" class="btn btn-danger btn-sm">
                                <i class="fas fa-times"></i> Rechazar
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</li>
