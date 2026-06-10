<div>
    @php $user = auth()->user(); @endphp

    <style>
        .table-hover tbody tr:hover:not(.bg-warning):not(.bg-info) {
            background-color: rgb(135, 180, 174) !important;
            transition: background-color 0.1s ease-in-out;
        }
        .badge-ubicacion  { background-color: #17a2b8; color: #fff; }
        .badge-sin-ubicacion { background-color: #6c757d; color: #fff; }
    </style>

    {{-- Mensaje flotante --}}
    <div x-data="{ show: false, mensaje: '', tipo: 'success' }" x-show="show" x-transition
        class="position-fixed top-0 start-50 translate-middle-x mt-1"
        style="z-index: 9999; width: auto; max-width: 90%;"
        @mostrar-mensaje.window="
            mensaje = $event.detail.mensaje;
            tipo    = $event.detail.tipo ?? 'success';
            show    = true;
            setTimeout(() => show = false, 7000);
        ">
        <div :class="{
            'alert alert-success alert-dismissible fade show': tipo === 'success',
            'alert alert-danger alert-dismissible fade show':  tipo === 'error',
            'alert alert-warning alert-dismissible fade show': tipo === 'warning'
        }" role="alert">
            <span x-text="mensaje"></span>
            <button type="button" class="btn-close" @click="show = false" aria-label="Cerrar"></button>
        </div>
    </div>

    {{-- Panel de cambio de ubicación --}}
    @if($cambiandoBienId)
    @php
        $bienPanel = $bienes->firstWhere('id', $cambiandoBienId)
                  ?? \Modules\Inventario\Entities\Bien::with('ubicacionActual.ubicacionDestino','dependencia')->find($cambiandoBienId);
    @endphp
    <div class="card card-body bg-light mb-3 border-info">
        <h6 class="font-weight-bold mb-1">
            <i class="fas fa-map-marker-alt mr-1 text-info"></i>
            Cambiar Ubicación
        </h6>
        <p class="mb-2 text-muted small">
            Bien: <strong>{{ $bienPanel?->nombre ?? "ID {$cambiandoBienId}" }}</strong>
            @if($bienPanel?->dependencia)
                — <em>{{ $bienPanel->dependencia->nombre }}</em>
            @endif
        </p>
        @if($bienPanel?->ubicacionActual?->ubicacionDestino)
        <p class="mb-2 text-muted small">
            Ubicación actual:
            <strong>{{ $bienPanel->ubicacionActual->ubicacionDestino->nombre }}</strong>
        </p>
        @endif

        <div class="row">
            <div class="form-group col-md-4 mb-2">
                <label class="small font-weight-bold">Nueva Ubicación <span class="text-danger">*</span></label>
                <select wire:model.lazy="nuevaUbicacionId"
                    class="form-control form-control-sm @error('nuevaUbicacionId') is-invalid @enderror">
                    <option value="">— Seleccionar ubicación —</option>
                    @foreach($ubicaciones as $uid => $unombre)
                        <option value="{{ $uid }}">{{ $unombre }}</option>
                    @endforeach
                </select>
                @error('nuevaUbicacionId')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group col-md-3 mb-2">
                <label class="small font-weight-bold">Fecha de Movimiento <span class="text-danger">*</span></label>
                <input wire:model.lazy="nuevaFechaMovimiento" type="date"
                    class="form-control form-control-sm @error('nuevaFechaMovimiento') is-invalid @enderror">
                @error('nuevaFechaMovimiento')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group col-md-5 mb-2">
                <label class="small font-weight-bold">Observaciones</label>
                <input wire:model.lazy="nuevasObservaciones" type="text"
                    class="form-control form-control-sm @error('nuevasObservaciones') is-invalid @enderror"
                    placeholder="Opcional">
                @error('nuevasObservaciones')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="d-flex gap-2 mt-1">
            <button wire:click="confirmarCambio" class="btn btn-info btn-sm">
                <i class="fas fa-map-marker-alt mr-1"></i> Confirmar Cambio
            </button>
            <button wire:click="cancelar" class="btn btn-secondary btn-sm">Cancelar</button>
        </div>
    </div>
    @endif

    {{-- Barra superior --}}
    <div class="row align-items-end mb-3">
        <div class="col-md-5 mb-2 mb-md-0">
            <label class="small font-weight-bold d-block">Buscar bien</label>
            <input wire:model.live.debounce.300ms="busqueda" type="text"
                class="form-control form-control-sm" placeholder="Nombre del bien...">
        </div>
        <div class="col-md-4 mb-2 mb-md-0">
            <label class="small font-weight-bold d-block">Dependencia</label>
            <select wire:model.lazy="filtroDependencia" class="form-control form-control-sm">
                <option value="">Todas</option>
                @foreach($dependencias as $did => $dnombre)
                    <option value="{{ $did }}">{{ $dnombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end justify-content-md-end">
            <select wire:model.lazy="perPage" class="form-control form-control-sm w-auto">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    {{-- Paginación superior --}}
    <div class="mb-2">{{ $bienes->links('pagination::bootstrap-4') }}</div>

    {{-- Tabla --}}
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th style="width:50px">ID</th>
                    <th>
                        <a href="#" wire:click.prevent="sortBy('nombre')" class="text-white text-decoration-none">
                            Bien
                            @if($sortField === 'nombre')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 text-muted"></i>
                            @endif
                        </a>
                    </th>
                    <th style="width:180px">
                        <a href="#" wire:click.prevent="sortBy('dependencia_id')" class="text-white text-decoration-none">
                            Dependencia
                            @if($sortField === 'dependencia_id')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 text-muted"></i>
                            @endif
                        </a>
                    </th>
                    <th style="width:200px">Ubicación Actual</th>
                    <th style="width:110px" class="text-center">Desde</th>
                    <th style="width:120px" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bienes as $bien)
                    <tr class="{{ $cambiandoBienId === $bien->id ? 'bg-warning' : '' }}">
                        <td class="align-middle">{{ $bien->id }}</td>
                        <td class="align-middle">{{ $bien->nombre }}</td>
                        <td class="align-middle small">{{ $bien->dependencia?->nombre ?? '—' }}</td>
                        <td class="align-middle">
                            @if($bien->ubicacionActual?->ubicacionDestino)
                                <span class="badge badge-ubicacion">
                                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $bien->ubicacionActual->ubicacionDestino->nombre }}
                                </span>
                            @else
                                <span class="badge badge-sin-ubicacion">Sin ubicación</span>
                            @endif
                        </td>
                        <td class="text-center align-middle small">
                            @if($bien->ubicacionActual)
                                {{ \Carbon\Carbon::parse($bien->ubicacionActual->fecha_movimiento)->format('d/m/Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            @can('cambiar-ubicacion-bienes')
                            <button wire:click="iniciarCambio({{ $bien->id }})"
                                class="btn btn-info btn-xs mr-1" title="Cambiar ubicación">
                                <i class="fas fa-map-marker-alt"></i>
                            </button>
                            @endcan
                            <button wire:click="toggleHistorial({{ $bien->id }})"
                                class="btn btn-secondary btn-xs" title="Ver historial de ubicaciones">
                                <i class="fas fa-history"></i>
                            </button>
                        </td>
                    </tr>

                    {{-- Historial inline --}}
                    @if($historialBienId === $bien->id)
                    <tr>
                        <td colspan="6" class="p-0">
                            <div class="bg-light border-top border-bottom p-3">
                                <h6 class="font-weight-bold mb-2">
                                    <i class="fas fa-history mr-1 text-secondary"></i>
                                    Historial de Ubicaciones — {{ $bien->nombre }}
                                </h6>
                                @if($historial->isEmpty())
                                    <p class="text-muted mb-0 small">Sin historial de ubicaciones registrado.</p>
                                @else
                                    <table class="table table-xs table-bordered table-sm mb-0 bg-white">
                                        <thead>
                                            <tr class="bg-secondary text-white">
                                                <th>Origen</th>
                                                <th>Destino</th>
                                                <th style="width:110px">Fecha Movimiento</th>
                                                <th>Registrado por</th>
                                                <th>Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($historial as $reg)
                                            <tr class="{{ $loop->first ? 'table-info' : '' }}">
                                                <td class="small">
                                                    {{ $reg->ubicacionOrigen?->nombre ?? '<em class="text-muted">Primera asignación</em>' }}
                                                </td>
                                                <td class="small font-weight-bold">
                                                    {{ $reg->ubicacionDestino?->nombre ?? '—' }}
                                                </td>
                                                <td class="text-center small">
                                                    {{ \Carbon\Carbon::parse($reg->fecha_movimiento)->format('d/m/Y') }}
                                                </td>
                                                <td class="small">{{ $reg->user?->nombre_completo ?? '—' }}</td>
                                                <td class="small">{{ $reg->observaciones ?? '—' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endif

                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            No se encontraron bienes.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación inferior --}}
    <div class="mt-2">{{ $bienes->links('pagination::bootstrap-4') }}</div>
</div>
