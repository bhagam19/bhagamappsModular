<div>
    @php $user = auth()->user(); @endphp

    <style>
        .table-hover tbody tr:hover:not(.bg-dark) {
            background-color: rgb(135, 180, 174) !important;
            transition: background-color 0.1s ease-in-out;
        }
    </style>

    {{-- Mensaje flotante --}}
    <div x-data="{ show: false, mensaje: '', tipo: 'success' }" x-show="show" x-transition class="position-fixed top-0 start-50 translate-middle-x mt-1"
        style="z-index: 9999; width: auto; max-width: 90%;"
        @mostrar-mensaje.window="
            mensaje = $event.detail.mensaje;
            tipo = $event.detail.tipo ?? 'success';
            show = true;
            setTimeout(() => show = false, 7000);
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

    {{-- Paginación Superior --}}
    <div class="mt-3">
        <div class="d-md-block d-flex overflow-auto">
            <div class="mx-auto">{{ $solicitudes->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>

    {{-- Tabla escritorio --}}
    <div class="table-responsive d-none d-md-block" style="max-height: 600px; overflow-y: auto;" wire:poll.10s>
        <table class="table table-striped table-sm table-hover w-100 mb-0">
            <thead class="thead-dark">
                <tr>
                    <th style="position: sticky; top: 0; background-color:#12304e;">ID</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Bien Id</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Nombre del Bien</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Dependencia</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Usuario</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Motivo</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Estado</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Gestionado por</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Fecha</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($solicitudes as $solicitud)
                    <tr class="{{ $solicitud->estado === 'pendiente' ? '' : 'text-muted' }}">
                        <td>{{ $solicitud->id }}</td>
                        <td>{{ $solicitud->bien?->id }}</td>
                        <td>{{ $solicitud->bien?->nombre ?? '—' }}</td>
                        <td>{{ $solicitud->dependencia->nombre ?? '—' }}</td>
                        <td>{{ $solicitud->user->nombre_completo ?? '—' }}</td>
                        <td>{{ $solicitud->motivo ?? '—' }}</td>
                        <td>
                            @php
                                $badgeClass = match ($solicitud->estado) {
                                    'pendiente' => 'badge-warning',
                                    'aprobado' => 'badge-success',
                                    'rechazado' => 'badge-danger',
                                    default => 'badge-secondary',
                                };
                            @endphp

                            <span class="badge {{ $badgeClass }}">
                                {{ ucfirst($solicitud->estado) }}
                            </span>
                        </td>
                        <td>
                            {{-- Mostrar "aprobado por" solo si no es pendiente --}}
                            @if ($solicitud->estado !== 'pendiente')
                                {{ $solicitud->aprobador?->nombre_completo ?? '—' }}
                            @endif
                        </td>
                        <td>
                            {{ $solicitud->updated_at->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            @if ($user->hasPermission('gestionar-historial-eliminaciones-bienes') && $solicitud->estado === 'pendiente')
                                <button wire:click="aprobarEliminacion({{ $solicitud->id }})"
                                    class="btn btn-sm btn-success">Aprobar</button>
                                <button wire:click="rechazarEliminacion({{ $solicitud->id }})"
                                    class="btn btn-sm btn-danger">Rechazar</button>
                            @endif
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No hay solicitudes pendientes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Vista móvil --}}
    <div class="d-block d-md-none" x-data="{ openId: null }" wire:poll.10s>
        <div id="accordionMobileEliminaciones">
            @forelse($solicitudes as $solicitud)
                @php
                    $isOpen = "openId === {$solicitud->id}";
                    $toggleOpen = "{$isOpen} ? openId = null : openId = {$solicitud->id}";
                @endphp

                <div class="card mb-2 {{ $solicitud->estado !== 'pendiente' ? 'bg-light text-muted' : '' }}">
                    <div class="card-header p-2 d-flex align-items-center" @click="{{ $toggleOpen }}"
                        @keydown.enter.prevent="{{ $toggleOpen }}" @keydown.space.prevent="{{ $toggleOpen }}"
                        tabindex="0" role="button">
                        <span>
                            <small class="badge badge-light border border-primary text-muted small px-2 py-1">
                                {{ $solicitud->bien->nombre ?? '—' }}
                            </small>
                            <small class="ml-2">{{ $solicitud->dependencia->nombre ?? '—' }}</small>
                        </span>

                        @php
                            $badgeClass = match ($solicitud->estado) {
                                'pendiente' => 'badge-warning',
                                'aprobado' => 'badge-success',
                                'rechazado' => 'badge-danger',
                                default => 'badge-secondary', // por si surge otro estado
                            };
                        @endphp

                        <span class=" ml-auto badge {{ $badgeClass }} px-2 small text-dark">
                            {{ ucfirst($solicitud->estado) }}
                        </span>
                    </div>

                    {{-- Registros --}}
                    <div x-show="{{ $isOpen }}" x-collapse class="card-body p-2 text-sm">
                        <div class="text-truncate me-2" style="white-space: nowrap;">
                            <span class="badge badge-light border border-primary text-muted small px-2 py-1 d-sm-none">
                                Bien Id:
                            </span>
                            <span class="px-2 small text-dark" style="cursor: pointer">
                                {{ $solicitud->bien_id }}
                            </span>
                        </div>
                        <div class="text-truncate me-2" style="white-space: nowrap;">
                            <span class="badge badge-light border border-primary text-muted small px-2 py-1 d-sm-none">
                                Dependencia:
                            </span>
                            <span class="px-2 small text-dark" style="cursor: pointer">
                                {{ $solicitud->dependencia->nombre }}
                            </span>
                        </div>
                        <div class="text-truncate me-2" style="white-space: nowrap;">
                            <span class="badge badge-light border border-primary text-muted small px-2 py-1 d-sm-none">
                                Usuario:
                            </span>
                            <span class="px-2 small text-dark" style="cursor: pointer">
                                {{ $solicitud->user->nombre_completo }}
                            </span>
                        </div>
                        <div class="text-truncate me-2" style="white-space: nowrap;">
                            <span class="badge badge-light border border-primary text-muted small px-2 py-1 d-sm-none">
                                Motivo:
                            </span>
                            <span class="px-2 small text-dark" style="cursor: pointer">
                                {{ $solicitud->motivo }}
                            </span>
                        </div>
                        <div class="text-truncate me-2" style="white-space: nowrap;">
                            <span class="badge badge-light border border-primary text-muted small px-2 py-1 d-sm-none">
                                Estado:
                            </span>
                            @php
                                $badgeClass = match ($solicitud->estado) {
                                    'pendiente' => 'badge-warning',
                                    'aprobado' => 'badge-success',
                                    'rechazado' => 'badge-danger',
                                    default => 'badge-secondary', // por si surge otro estado
                                };
                            @endphp

                            <span class="badge {{ $badgeClass }} px-2 small text-dark">
                                {{ ucfirst($solicitud->estado) }}
                            </span>
                        </div>
                        @if ($solicitud->estado !== 'pendiente')
                            <div class="text-truncate me-2" style="white-space: nowrap;">
                                <span
                                    class="badge badge-light border border-primary text-muted small px-2 py-1 d-sm-none">
                                    Aprobado por:
                                </span>
                                <span class="px-2 small text-dark" style="cursor: pointer">
                                    {{ $solicitud->aprobadoPor?->nombre_completo }}
                                </span>
                            </div>
                        @endif
                        <div class="text-truncate me-2" style="white-space: nowrap;">
                            <span class="badge badge-light border border-primary text-muted small px-2 py-1 d-sm-none">
                                Fecha:
                            </span>
                            <span class="px-2 small text-dark" style="cursor: pointer">
                                {{ $solicitud->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        {{-- Botones de acción --}}
                        @if ($user->hasPermission('gestionar-historial-eliminaciones-bienes'))
                            <div class="d-flex justify-content-center gap-2 mt-2">
                                {{-- Botones de acción --}}
                                <button wire:click="aprobarEliminacion({{ $solicitud->id }})"
                                    class="btn btn-sm btn-success w-50 mr-2">Aprobar</button>
                                <button wire:click="rechazarEliminacion({{ $solicitud->id }})"
                                    class="btn btn-sm btn-danger w-50 ml-2">Rechazar</button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-center text-muted">No hay eliminaciones pendientes.</p>
            @endforelse
        </div>
    </div>

    {{-- Paginación inferior --}}
    <div class="mt-3">
        <div class="d-md-block d-flex overflow-auto">
            <div class="mx-auto">{{ $solicitudes->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
</div>
