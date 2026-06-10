<div>
    @php $user = auth()->user(); @endphp

    <style>
        .table-hover tbody tr:hover:not(.bg-warning):not(.bg-info) {
            background-color: rgb(135, 180, 174) !important;
            transition: background-color 0.1s ease-in-out;
        }
        .badge-custodio { background-color: #17a2b8; color: #fff; }
        .badge-sin-custodio { background-color: #6c757d; color: #fff; }
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

    {{-- Panel de asignación / transferencia --}}
    @if($asignandoBienId || $transfiriendoBienId)
    @php
        $bienIdPanel = $asignandoBienId ?? $transfiriendoBienId;
        $bienPanel   = $bienes->firstWhere('id', $bienIdPanel)
                    ?? \Modules\Inventario\Entities\Bien::with('responsableActual.user','dependencia')->find($bienIdPanel);
        $esTransferencia = (bool) $transfiriendoBienId;
    @endphp
    <div class="card card-body bg-light mb-3 border-{{ $esTransferencia ? 'warning' : 'success' }}">
        <h6 class="font-weight-bold mb-1">
            <i class="fas fa-{{ $esTransferencia ? 'exchange-alt' : 'user-plus' }} mr-1"></i>
            {{ $esTransferencia ? 'Transferir' : 'Asignar' }} Responsable
        </h6>
        <p class="mb-2 text-muted small">
            Bien: <strong>{{ $bienPanel?->nombre ?? "ID {$bienIdPanel}" }}</strong>
            @if($bienPanel?->dependencia)
                — <em>{{ $bienPanel->dependencia->nombre }}</em>
            @endif
        </p>
        @if($esTransferencia && $bienPanel?->responsableActual)
        <p class="mb-2 text-muted small">
            Responsable actual: <strong>{{ $bienPanel->responsableActual->user?->nombre_completo ?? '—' }}</strong>
            (desde {{ \Carbon\Carbon::parse($bienPanel->responsableActual->fecha_asignacion)->format('d/m/Y') }})
        </p>
        @endif

        <div class="row">
            <div class="form-group col-md-4 mb-2">
                <label class="small font-weight-bold">Nuevo Responsable <span class="text-danger">*</span></label>
                <select wire:model.lazy="nuevoUserId" class="form-control form-control-sm @error('nuevoUserId') is-invalid @enderror">
                    <option value="">— Seleccionar usuario —</option>
                    @foreach($usuarios as $uid => $unombre)
                        <option value="{{ $uid }}">{{ $unombre }}</option>
                    @endforeach
                </select>
                @error('nuevoUserId')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group col-md-3 mb-2">
                <label class="small font-weight-bold">Fecha de Asignación <span class="text-danger">*</span></label>
                <input wire:model.lazy="nuevaFechaAsignacion" type="date"
                    class="form-control form-control-sm @error('nuevaFechaAsignacion') is-invalid @enderror">
                @error('nuevaFechaAsignacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
            @if($esTransferencia)
                <button wire:click="confirmarTransferencia" class="btn btn-warning btn-sm">
                    <i class="fas fa-exchange-alt mr-1"></i> Confirmar Transferencia
                </button>
            @else
                <button wire:click="confirmarAsignacion" class="btn btn-success btn-sm">
                    <i class="fas fa-user-check mr-1"></i> Confirmar Asignación
                </button>
            @endif
            <button wire:click="cancelar" class="btn btn-secondary btn-sm">Cancelar</button>
        </div>
    </div>
    @endif

    {{-- Barra superior --}}
    <div class="row align-items-end mb-3">
        <div class="col-md-4 mb-2 mb-md-0">
            <label class="small font-weight-bold d-block">Buscar bien</label>
            <input wire:model.live.debounce.300ms="busqueda" type="text"
                class="form-control form-control-sm" placeholder="Nombre del bien...">
        </div>
        <div class="col-md-3 mb-2 mb-md-0">
            <label class="small font-weight-bold d-block">Dependencia</label>
            <select wire:model.lazy="filtroDependencia" class="form-control form-control-sm">
                <option value="">Todas</option>
                @foreach($dependencias as $did => $dnombre)
                    <option value="{{ $did }}">{{ $dnombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 mb-2 mb-md-0">
            <label class="small font-weight-bold d-block">Responsable</label>
            <select wire:model.lazy="filtroResponsable" class="form-control form-control-sm">
                <option value="">Todos</option>
                @foreach($usuarios as $uid => $unombre)
                    <option value="{{ $uid }}">{{ $unombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end justify-content-md-end">
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
                    <th style="width:180px">Dependencia</th>
                    <th style="width:180px">Responsable Vigente</th>
                    <th style="width:110px" class="text-center">Desde</th>
                    <th style="width:175px" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bienes as $bien)
                    <tr class="{{ ($asignandoBienId === $bien->id || $transfiriendoBienId === $bien->id) ? 'bg-warning' : '' }}">
                        <td class="align-middle">{{ $bien->id }}</td>
                        <td class="align-middle">{{ $bien->nombre }}</td>
                        <td class="align-middle small">{{ $bien->dependencia?->nombre ?? '—' }}</td>
                        <td class="align-middle">
                            @if($bien->responsableActual && $bien->responsableActual->user)
                                <span class="badge badge-custodio">
                                    <i class="fas fa-user mr-1"></i>{{ $bien->responsableActual->user->nombre_completo }}
                                </span>
                            @else
                                <span class="badge badge-sin-custodio">Sin custodio</span>
                            @endif
                        </td>
                        <td class="text-center align-middle small">
                            @if($bien->responsableActual)
                                {{ \Carbon\Carbon::parse($bien->responsableActual->fecha_asignacion)->format('d/m/Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            @if($bien->responsableActual && $bien->responsableActual->user)
                                @can('transferir-responsables-bienes')
                                <button wire:click="iniciarTransferencia({{ $bien->id }})"
                                    class="btn btn-warning btn-xs mr-1" title="Transferir responsable">
                                    <i class="fas fa-exchange-alt"></i>
                                </button>
                                @endcan
                                @can('editar-responsables-bienes')
                                <button wire:click="liberarResponsable({{ $bien->id }})"
                                    class="btn btn-secondary btn-xs mr-1" title="Liberar responsable"
                                    onclick="return confirm('¿Confirma liberar al responsable de este bien?')">
                                    <i class="fas fa-user-times"></i>
                                </button>
                                @endcan
                            @else
                                @can('asignar-responsables-bienes')
                                <button wire:click="iniciarAsignacion({{ $bien->id }})"
                                    class="btn btn-success btn-xs mr-1" title="Asignar responsable">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                                @endcan
                            @endif
                            <button wire:click="toggleHistorial({{ $bien->id }})"
                                class="btn btn-info btn-xs" title="Ver historial">
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
                                    <i class="fas fa-history mr-1 text-info"></i>
                                    Historial de Responsables — {{ $bien->nombre }}
                                </h6>
                                @if($historial->isEmpty())
                                    <p class="text-muted mb-0 small">Sin historial registrado.</p>
                                @else
                                    <table class="table table-xs table-bordered table-sm mb-0 bg-white">
                                        <thead>
                                            <tr class="bg-secondary text-white">
                                                <th>Responsable</th>
                                                <th style="width:110px">Fecha Asignación</th>
                                                <th style="width:110px">Fecha Retiro</th>
                                                <th>Observaciones</th>
                                                <th style="width:80px" class="text-center">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($historial as $reg)
                                            <tr class="{{ is_null($reg->fecha_retiro) ? 'table-success' : '' }}">
                                                <td>{{ $reg->user?->nombre_completo ?? '—' }}</td>
                                                <td class="text-center">{{ \Carbon\Carbon::parse($reg->fecha_asignacion)->format('d/m/Y') }}</td>
                                                <td class="text-center">
                                                    {{ $reg->fecha_retiro ? \Carbon\Carbon::parse($reg->fecha_retiro)->format('d/m/Y') : '—' }}
                                                </td>
                                                <td class="small">{{ $reg->observaciones ?? '—' }}</td>
                                                <td class="text-center">
                                                    @if(is_null($reg->fecha_retiro))
                                                        <span class="badge badge-success">Vigente</span>
                                                    @else
                                                        <span class="badge badge-secondary">Retirado</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                                <div class="mt-2">
                                    <button wire:click="toggleHistorial({{ $bien->id }})" class="btn btn-secondary btn-xs">
                                        <i class="fas fa-times mr-1"></i> Cerrar historial
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">No se encontraron bienes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación inferior --}}
    <div class="mt-2">{{ $bienes->links('pagination::bootstrap-4') }}</div>
</div>
