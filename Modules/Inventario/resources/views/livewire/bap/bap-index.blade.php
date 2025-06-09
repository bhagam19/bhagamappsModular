<div>
    @php $user = auth()->user(); @endphp

    <style>
        .table-hover tbody tr:hover {
            background-color: rgb(135, 180, 174) !important;
            transition: background-color 0.1s ease-in-out;
        }
    </style>

    {{-- Mensaje de sesión --}}
    @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Barra superior --}}
    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <input type="text" wire:model.lazy="filtroNombre" class="form-control"
                placeholder="🔍 Buscar por nombre...">
        </div>

        <div class="col-md-6 d-flex justify-content-end align-items-center">
            <label class="mr-2 mb-0">Mostrar</label>
            <select wire:model.lazy="perPage" class="form-control w-auto">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span class="ml-2">registros</span>
        </div>
    </div>

    {{-- Paginación superior --}}
    <div class="mt-3">
        <div class="d-md-block d-flex overflow-auto">
            <div class="mx-auto">
                {{ $aprobacionesPendientes->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- Tabla escritorio --}}
    <div class="table-responsive d-none d-md-block" style="max-height: 600px; overflow-y: auto;">
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
                    <th style="position: sticky; top: 0; background-color:#12304e;">Estado</th>
                    <th style="position: sticky; top: 0; background-color:#12304e;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($aprobacionesPendientes as $aprobacion)
                    <tr>
                        <td>{{ $aprobacion->id }}</td>
                        <td>{{ $aprobacion->bien_id }}</td>
                        <td>{{ $aprobacion->bien->nombre ?? '—' }}</td>
                        <td>{{ ucfirst($aprobacion->campo) }}</td>
                        <td>{{ $aprobacion->valor_anterior }}</td>
                        <td>{{ $aprobacion->valor_nuevo }}</td>
                        <td>{{ $aprobacion->dependencia->nombre ?? '—' }}</td>
                        <td>
                            <span
                                class="badge 
                            {{ $aprobacion->estado === 'pendiente'
                                ? 'badge-warning'
                                : ($aprobacion->estado === 'aprobado'
                                    ? 'badge-success'
                                    : 'badge-danger') }}">
                                {{ ucfirst($aprobacion->estado) }}
                            </span>
                        </td>
                        <td>
                            @if ($user->hasPermission('ver-aprobaciones-pendientes-bienes'))
                                <button wire:click="aprobar({{ $aprobacion->id }})" class="btn btn-sm btn-success">
                                    Aprobar
                                </button>
                            
                                <button wire:click="rechazar({{ $aprobacion->id }})" class="btn btn-sm btn-danger">
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
    <div class="d-block d-md-none" x-data="{ openId: null }">
        <div id="accordionMobilePendientes">
            @forelse($aprobacionesPendientes as $aprobacion)
                @php
                    $isOpen = "openId === {$aprobacion->id}";
                    $toggleOpen = "{$isOpen} ? openId = null : openId = {$aprobacion->id}";
                @endphp

                <div class="card mb-2">
                    <div class="card-header p-2 d-flex align-items-center" @click="{{ $toggleOpen }}"
                        @keydown.enter.prevent="{{ $toggleOpen }}" @keydown.space.prevent="{{ $toggleOpen }}"
                        tabindex="0" role="button">
                        <span>
                            {{ $aprobacion->id }}. {{ $aprobacion->bien->nombre ?? '—' }}
                        </span>
                        <span class="ml-auto badge badge-primary">
                            {{ ucfirst($aprobacion->campo) }}
                        </span>
                    </div>

                    <div x-show="{{ $isOpen }}" x-collapse class="card-body p-2">
                        <div class="mb-2"><strong>Valor anterior:</strong> {{ $aprobacion->valor_anterior }}</div>
                        <div class="mb-2"><strong>Valor nuevo:</strong> {{ $aprobacion->valor_nuevo }}</div>
                        <div class="mb-2"><strong>Usuario:</strong> {{ $aprobacion->usuario->name ?? '—' }}</div>
                        <div class="mb-2"><strong>Estado:</strong>
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

                        <div class="d-flex justify-content-between">
                            @if ($user?->hasPermission('aprobar-bienes'))
                                <button wire:click="aprobar({{ $aprobacion->id }})"
                                    class="btn btn-sm btn-success w-45">Aprobar</button>
                            @endif
                            @if ($user?->hasPermission('rechazar-bienes'))
                                <button wire:click="rechazar({{ $aprobacion->id }})"
                                    class="btn btn-sm btn-danger w-45">Rechazar</button>
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
                {{ $aprobacionesPendientes->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
