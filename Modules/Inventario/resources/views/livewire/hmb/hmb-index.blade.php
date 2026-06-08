<div>
    @php $user = auth()->user(); @endphp

    <style>
        .table-hover tbody tr:hover {
            background-color: rgb(135, 180, 174) !important;
            transition: background-color 0.1s ease-in-out;
        }
    </style>

    {{-- Mensaje de sesión --}}
    <div x-data="{ show: false, mensaje: '', tipo: 'success' }" x-show="show" x-transition class="position-fixed top-0 start-50 translate-middle-x mt-1"
        style="z-index: 9999; width: auto; max-width: 90%;"
        @mostrar-mensaje.window="
        mensaje = $event.detail.mensaje; 
        tipo = $event.detail.tipo ?? 'success'; 
        show = true; 
        setTimeout(() => show = false, 10000);
    ">
        <div :class="{
            'alert alert-success alert-dismissible fade show': tipo === 'success',
            'alert alert-danger alert-dismissible fade show': tipo === 'error',
            'alert alert-warning alert-dismissible fade show': tipo === 'warning'
        }"
            role="alert">
            <span x-text="mensaje"></span>

            <button type="button" class="btn-close" @click="show = false" aria-label="Cerrar"></button>
        </div>
    </div>

    {{-- Barra superior --}}
    <div class="row align-items-center mb-3">
        {{-- Columna izquierda: Botón --}}
        <div class="col-md-6 d-flex justify-content-start mb-2 mb-md-0">
            <a href="{{ route('inventario.bienes.index') }}" class="btn btn-primary btn-sm">
                Ir a Bienes
            </a>
        </div>

        {{-- Columna derecha: Selector --}}
        <div class="col-md-6 d-flex justify-content-end align-items-center">
            <label for="perPage" class="mr-2 mb-0">Mostrar</label>
            <select id="perPage" wire:model.lazy="perPage" class="form-control w-auto">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            <span class="ml-2">registros</span>
        </div>
    </div>

    {{-- Paginación superior --}}
    <div class="mt-3">
        <div class="d-md-block d-flex overflow-auto">
            <div class="mx-auto">
                {{ $modificacionesPendientes->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- Tabla escritorio --}}
    <div class="table-responsive d-none d-md-block" style="max-height: 600px; overflow-y: auto;" wire:poll.10s>
        <table class="table table-striped table-sm table-hover w-100 mb-0">
            <thead class="thead-dark">
                <tr>
                    <th style="position: sticky; top: 0; background-color:#12304e;">No.</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Bien Id</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Bien</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Campo</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Valor anterior</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Valor nuevo</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Dependencia</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Usuario</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Estado</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Gestionado por</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Fecha</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Acciones</th>
                </tr>
            </thead>
            <tbody>

                @forelse ($modificacionesPendientes as $modificacion)
                    @php
                        $usuarioDependencia =
                            $modificacion->dependencia->user->nombres .
                            ' ' .
                            $modificacion->dependencia->user->apellidos;
                    @endphp
                    <tr class="{{ $modificacion->estado === 'pendiente' ? '' : 'text-muted' }}">
                        <td>{{ $modificacion->id }}</td>
                        <td>{{ $modificacion->bien_id }}</td>
                        <td>{{ $modificacion->bien->nombre ?? '—' }}</td>
                        <td>{{ ucfirst(str_replace('_id', '', $modificacion->campo)) }}</td>
                        <td>
                            @switch($modificacion->campo)
                                @case('categoria_id')
                                    {{ $modificacion->valorAnteriorCategoria->nombre ?? $modificacion->valor_anterior }}
                                @break

                                @case('dependencia_id')
                                    {{ $modificacion->valorAnteriorDependencia->nombre ?? $modificacion->valor_anterior }}
                                @break

                                @case('estado_id')
                                    {{ $modificacion->valorAnteriorEstado->nombre ?? $modificacion->valor_anterior }}
                                @break

                                @case('detalle')
                                    @php
                                        $valores = json_decode($modificacion->valor_anterior, true);
                                    @endphp
                                    @if (is_array($valores))
                                        {!! collect($valores)->map(function ($valor, $campo) {
                                                return '<span class="badge border border-primary text-primary bg-white">' .
                                                    Str::headline($campo) .
                                                    '</span>: ' .
                                                    e($valor);
                                            })->implode('<br>') !!}
                                    @endif
                                @break

                                @default
                                    {{ $modificacion->valor_anterior }}
                            @endswitch
                        </td>
                        <td>
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

                                @case('detalle')
                                    @php
                                        $valores = json_decode($modificacion->valor_nuevo, true);
                                    @endphp
                                    @if (is_array($valores))
                                        {!! collect($valores)->map(function ($valor, $campo) {
                                                return '<span class="badge border border-primary text-primary bg-white">' .
                                                    Str::headline($campo) .
                                                    '</span>: ' .
                                                    (trim($valor) !== '' ? e($valor) : '-');
                                            })->implode('<br>') !!}
                                    @endif
                                @break

                                @default
                                    {{ $modificacion->valor_nuevo }}
                            @endswitch
                        </td>
                        <td>{{ $modificacion->dependencia->nombre ?? '—' }}</td>
                        <td>{{ $usuarioDependencia ?? '—' }}</td>
                        <td>
                            @php
                                $claseEstado = match ($modificacion->estado) {
                                    'pendiente' => 'badge-warning',
                                    'aprobada' => 'badge-success',
                                    'rechazada' => 'badge-danger',
                                    default => 'badge-secondary', // opcional para estados inesperados
                                };
                            @endphp

                            <span class="badge {{ $claseEstado }}">
                                {{ ucfirst($modificacion->estado) }}
                            </span>

                        </td>
                        <td>
                            {{-- Mostrar "aprobado por" solo si no es pendiente --}}
                            @if ($modificacion->estado !== 'pendiente')
                                {{ $modificacion->aprobador?->nombre_completo ?? '—' }}
                            @endif
                        </td>
                        <td>
                            {{ $modificacion->updated_at->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            @if ($user->hasPermission('gestionar-historial-modificaciones-bienes') && $modificacion->estado === 'pendiente')
                                <button wire:click="aprobarModificacion({{ $modificacion->id }})"
                                    class="btn btn-sm btn-success">
                                    Aprobar
                                </button>

                                <button wire:click="rechazarModificacion({{ $modificacion->id }})"
                                    class="btn btn-sm btn-danger">
                                    Rechazar
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No hay cambios pendientes de aprobación.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Vista móvil --}}
        <div class="d-block d-md-none" x-data="{ openId: null }" wire:poll.10s>
            <div id="accordionMobilePendientes">
                @forelse($modificacionesPendientes as $modificacion)
                    @php
                        $isOpen = "openId === {$modificacion->id}";
                        $toggleOpen = "{$isOpen} ? openId = null : openId = {$modificacion->id}";
                    @endphp

                    <div class="card mb-2">
                        <div class="card-header p-2 d-flex align-items-center" @click="{{ $toggleOpen }}"
                            @keydown.enter.prevent="{{ $toggleOpen }}" @keydown.space.prevent="{{ $toggleOpen }}"
                            tabindex="0" role="button">
                            <span>
                                <small class="badge badge-light border border-primary text-muted small px-2 py-1 d-sm-none">
                                    {{ $modificacion->bien->nombre ?? '—' }}
                                </small>

                                <small text-muted ml-2>{{ $modificacion->dependencia->nombre ?? '—' }}</small>
                            </span>
                            <span class="ml-auto badge badge-primary">
                                {{ ucfirst(str_replace('_id', '', $modificacion->campo)) }}
                            </span>
                            @php
                                $badgeClass = match ($modificacion->estado) {
                                    'pendiente' => 'badge-warning',
                                    'aprobada' => 'badge-success',
                                    'rechazada' => 'badge-danger',
                                    default => 'badge-secondary', // por si surge otro estado
                                };
                            @endphp

                            <span class=" ml-auto badge {{ $badgeClass }} px-2 small text-dark">
                                {{ ucfirst($modificacion->estado) }}
                            </span>
                        </div>

                        <div x-show="{{ $isOpen }}" x-collapse class="card-body p-2">

                            <div class="text-truncate me-2" style="white-space: nowrap;">
                                <span class="badge badge-light border border-primary text-muted small px-2 py-1 d-sm-none">
                                    Bien Id:
                                </span>
                                <span class="px-2 small text-dark" style="cursor: pointer">
                                    {{ $modificacion->bien_id }}
                                </span>
                            </div>

                            <div class="text-truncate me-2" style="white-space: nowrap;">
                                <span class="badge badge-light border border-primary text-muted small px-2 py-1 d-sm-none">
                                    Usuario:
                                </span>
                                <span class="px-2 small text-dark" style="cursor: pointer">
                                    {{ $usuarioDependencia ?? '—' }}
                                </span>
                            </div>
                            <div class="text-truncate me-2" style="white-space: nowrap;">
                                <span class="badge badge-light border border-primary text-muted small px-2 py-1 d-sm-none">
                                    Valor anterior:
                                </span>
                                <span class="px-2 small text-dark" style="cursor: pointer">
                                    @switch($modificacion->campo)
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
                                </span>
                            </div>
                            <div class="text-truncate me-2" style="white-space: nowrap;">
                                <span class="badge badge-light border border-primary text-muted small px-2 py-1 d-sm-none">
                                    Valor nuevo:
                                </span>
                                <span class="px-2 small text-dark" style="cursor: pointer">
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

                                </span>
                            </div>
                            <div class="text-truncate me-2" style="white-space: nowrap;">
                                <span class="badge badge-light border border-primary text-muted small px-2 py-1 d-sm-none">
                                    Estado:
                                </span>
                                @php
                                    $badgeClass = match ($modificacion->estado) {
                                        'pendiente' => 'badge-warning',
                                        'aprobada' => 'badge-success',
                                        'rechazada' => 'badge-danger',
                                        default => 'badge-secondary', // por si surge otro estado
                                    };
                                @endphp

                                <span class=" ml-auto badge {{ $badgeClass }} px-2 small text-dark">
                                    {{ ucfirst($modificacion->estado) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-center mt-2">
                                @if ($user?->hasPermission('gestionar-historial-modificaciones-bienes'))
                                    <button wire:click="aprobarModificacion({{ $modificacion->id }})"
                                        class="btn btn-sm btn-success w-45 mr-2">Aprobar</button>

                                    <button wire:click="rechazarModificacion({{ $modificacion->id }})"
                                        class="btn btn-sm btn-danger w-45 ml-2">Rechazar</button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                        <p class="text-center text-muted">No hay cambios pendientes de aprobación.</p>
                    @endforelse
                </div>
            </div>

            {{-- Paginación inferior --}}
            <div class="mt-3">
                <div class="d-md-block d-flex overflow-auto">
                    <div class="mx-auto">
                        {{ $modificacionesPendientes->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
