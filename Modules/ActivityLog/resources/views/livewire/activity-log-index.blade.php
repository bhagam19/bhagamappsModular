<div>

    {{-- ── Dashboard rápido (LOG-012) ─────────────────────────────────────── --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-info"><i class="fas fa-calendar-day"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Acciones hoy</span>
                    <span class="info-box-number">{{ number_format($accionesHoy) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-primary"><i class="fas fa-calendar-week"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Esta semana</span>
                    <span class="info-box-number">{{ number_format($accionesSemana) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-secondary"><i class="fas fa-list-ul"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total registros</span>
                    <span class="info-box-number">{{ number_format($registros->total()) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Últimos 5 eventos ───────────────────────────────────────────────── --}}
    @if (count($ultimosEventos) > 0)
    <div class="card card-outline card-secondary mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bolt mr-1 text-warning"></i>Últimos eventos</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:150px">Fecha</th>
                        <th>Usuario</th>
                        <th>Módulo</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ultimosEventos as $ev)
                    <tr>
                        <td class="text-muted small">
                            {{ \Carbon\Carbon::parse($ev['created_at'])->format('d/m/Y H:i') }}
                        </td>
                        <td class="small">
                            @if ($ev['user'])
                                {{ $ev['user']['nombres'] ?? '' }} {{ $ev['user']['apellidos'] ?? '' }}
                            @else
                                <span class="text-muted">Sistema</span>
                            @endif
                        </td>
                        <td><span class="badge badge-light border">{{ $ev['modulo'] }}</span></td>
                        <td>
                            @php
                                $badgeColor = match($ev['accion']) {
                                    'crear'             => 'success',
                                    'editar'            => 'primary',
                                    'eliminar'          => 'danger',
                                    'bloquear'          => 'warning',
                                    'restaurar'         => 'info',
                                    'importar'          => 'info',
                                    'generar'           => 'secondary',
                                    'descargar'         => 'secondary',
                                    'aprobar'           => 'success',
                                    'rechazar'          => 'danger',
                                    'asignar-rol'       => 'primary',
                                    'asignar-permiso'   => 'primary',
                                    default             => 'secondary',
                                };
                            @endphp
                            <span class="badge badge-{{ $badgeColor }}">{{ $ev['accion'] }}</span>
                        </td>
                        <td class="small">{{ Str::limit($ev['descripcion'], 60) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ── Filtros (LOG-010) ───────────────────────────────────────────────── --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter mr-1"></i>Filtros
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="text-muted small mb-1">Usuario</label>
                        <input
                            type="text"
                            wire:model.live.debounce.400ms="filtroUsuario"
                            class="form-control form-control-sm"
                            placeholder="Nombre, apellido o email"
                        >
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="text-muted small mb-1">Módulo</label>
                        <select wire:model.live="filtroModulo" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach ($modulosDisponibles as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="text-muted small mb-1">Acción</label>
                        <select wire:model.live="filtroAccion" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            @foreach ($accionesDisponibles as $a)
                                <option value="{{ $a }}">{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="text-muted small mb-1">Desde</label>
                        <input type="date" wire:model.live="filtroDesde" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="text-muted small mb-1">Hasta</label>
                        <input type="date" wire:model.live="filtroHasta" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <div class="form-group mb-2 w-100">
                        <button wire:click="limpiarFiltros" class="btn btn-sm btn-outline-secondary w-100" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tabla principal (LOG-010) ──────────────────────────────────────── --}}
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history mr-1"></i>
                Registro de auditoría
                <span class="badge badge-secondary ml-1">{{ number_format($registros->total()) }}</span>
            </h3>
            <div class="card-tools">
                <select wire:model.live="perPage" class="form-control form-control-sm d-inline-block w-auto">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div wire:loading.class="opacity-50">
                @if ($registros->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-search fa-2x mb-2 d-block"></i>
                        No se encontraron registros con los filtros aplicados.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:140px">Fecha / Hora</th>
                                    <th style="width:160px">Usuario</th>
                                    <th style="width:100px">Módulo</th>
                                    <th style="width:90px">Acción</th>
                                    <th style="width:90px">Tipo objeto</th>
                                    <th style="width:60px">ID</th>
                                    <th>Descripción</th>
                                    <th style="width:110px">IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($registros as $log)
                                <tr>
                                    <td class="text-muted small text-nowrap">
                                        {{ $log->created_at->format('d/m/Y') }}<br>
                                        <span class="text-xs">{{ $log->created_at->format('H:i:s') }}</span>
                                    </td>
                                    <td class="small">
                                        @if ($log->user)
                                            <span class="font-weight-bold">{{ $log->user->nombres }}</span><br>
                                            <span class="text-muted text-xs">{{ $log->user->email }}</span>
                                        @else
                                            <span class="text-muted font-italic">Sistema</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-light border text-dark">{{ $log->modulo }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $bc = match($log->accion) {
                                                'crear'             => 'success',
                                                'editar'            => 'primary',
                                                'eliminar'          => 'danger',
                                                'bloquear'          => 'warning',
                                                'desbloquear'       => 'info',
                                                'restaurar'         => 'info',
                                                'importar'          => 'info',
                                                'generar'           => 'secondary',
                                                'descargar'         => 'secondary',
                                                'aprobar'           => 'success',
                                                'rechazar'          => 'danger',
                                                'asignar-rol'       => 'primary',
                                                'asignar-permiso'   => 'primary',
                                                default             => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $bc }}">{{ $log->accion }}</span>
                                    </td>
                                    <td class="small text-muted">{{ $log->tipo_objeto ?? '—' }}</td>
                                    <td class="small text-muted text-center">{{ $log->objeto_id ?? '—' }}</td>
                                    <td class="small">
                                        {{ $log->descripcion }}
                                        @if ($log->datos_anteriores || $log->datos_nuevos)
                                            <button
                                                type="button"
                                                class="btn btn-xs btn-outline-secondary ml-1"
                                                data-toggle="tooltip"
                                                title="{{ json_encode(['antes' => $log->datos_anteriores, 'después' => $log->datos_nuevos], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}"
                                            ><i class="fas fa-code fa-xs"></i></button>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $log->ip_address ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        @if ($registros->hasPages())
        <div class="card-footer">
            {{ $registros->links() }}
        </div>
        @endif
    </div>

</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('[data-toggle="tooltip"]').tooltip({ html: false, placement: 'left' });
    });
</script>
@endpush
