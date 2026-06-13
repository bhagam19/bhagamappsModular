<div>
    {{-- ── Alerta de estado ─────────────────────────────────────────────── --}}
    @if ($ultimoBackup)
        @php
            $alertClass = match($alerta) {
                'verde'   => 'alert-success',
                'amarillo'=> 'alert-warning',
                'rojo'    => 'alert-danger',
                default   => 'alert-secondary',
            };
            $alertIcon = match($alerta) {
                'verde'   => 'fas fa-check-circle',
                'amarillo'=> 'fas fa-exclamation-triangle',
                'rojo'    => 'fas fa-times-circle',
                default   => 'fas fa-info-circle',
            };
            $alertText = match($alerta) {
                'verde'   => 'Sistema de respaldo operativo — Último respaldo hace menos de 24 horas.',
                'amarillo'=> 'Advertencia — Han pasado más de 24 horas desde el último respaldo.',
                'rojo'    => 'Crítico — Han pasado más de 48 horas sin un respaldo exitoso.',
                default   => 'Estado desconocido.',
            };
        @endphp
        <div class="alert {{ $alertClass }} alert-dismissible">
            <i class="{{ $alertIcon }} mr-2"></i>{{ $alertText }}
        </div>
    @else
        <div class="alert alert-danger">
            <i class="fas fa-times-circle mr-2"></i>No se encontraron respaldos en el sistema.
        </div>
    @endif

    {{-- ── Mensaje de resultado de generación ──────────────────────────── --}}
    @if ($mensaje)
        <div class="alert alert-{{ $estadoMensaje }} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            <i class="fas fa-{{ $estadoMensaje === 'success' ? 'check' : 'exclamation-triangle' }} mr-2"></i>
            {{ $mensaje }}
        </div>
    @endif

    {{-- ── KPI Cards ────────────────────────────────────────────────────── --}}
    <div class="row">
        {{-- Último respaldo --}}
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-{{ $alerta === 'verde' ? 'success' : ($alerta === 'amarillo' ? 'warning' : 'danger') }} elevation-1">
                    <i class="fas fa-database"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Último Respaldo</span>
                    @if ($ultimoBackup)
                        <span class="info-box-number" style="font-size:1rem;">
                            {{ $ultimoBackup['fecha'] }}
                        </span>
                        <span class="progress-description">
                            {{ $ultimoBackup['meta']['generado_en'] ?? '—' }}
                        </span>
                    @else
                        <span class="info-box-number text-muted">Sin respaldos</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tamaño último ZIP --}}
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-info elevation-1">
                    <i class="fas fa-file-archive"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Tamaño</span>
                    <span class="info-box-number">
                        {{ $ultimoBackup ? $this->formatSize($ultimoBackup['zip_size']) : '—' }}
                    </span>
                    <span class="progress-description">
                        {{ $ultimoBackup ? ($ultimoBackup['meta']['total_registros'] ?? 0) . ' registros' : '' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Cantidad disponible --}}
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-primary elevation-1">
                    <i class="fas fa-copy"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Respaldos Disponibles</span>
                    <span class="info-box-number">{{ count($backups) }}</span>
                    <span class="progress-description">Política: 30 diarios / 12 mensuales</span>
                </div>
            </div>
        </div>

        {{-- Próxima ejecución --}}
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-secondary elevation-1">
                    <i class="fas fa-clock"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Próxima Ejecución</span>
                    <span class="info-box-number" style="font-size:1rem;">{{ $proximaEjec }}</span>
                    <span class="progress-description">Schedule: 02:00 AM diario</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Generación manual ────────────────────────────────────────────── --}}
    @can('generar-backups')
    <div class="card card-outline card-primary mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-play-circle mr-2"></i>Generación Manual</h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Genera un respaldo inmediato de todos los datos institucionales.
                El proceso puede tardar varios segundos.
            </p>
            <button
                wire:click="generarBackup"
                wire:loading.attr="disabled"
                class="btn btn-primary"
                @if ($generando) disabled @endif
            >
                <span wire:loading.remove wire:target="generarBackup">
                    <i class="fas fa-database mr-1"></i> Generar Respaldo
                </span>
                <span wire:loading wire:target="generarBackup">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Generando respaldo...
                </span>
            </button>
        </div>
    </div>
    @endcan

    {{-- ── Listado de respaldos ─────────────────────────────────────────── --}}
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list mr-2"></i>Respaldos Disponibles</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" wire:click="cargarDatos" wire:loading.attr="disabled">
                    <i class="fas fa-sync-alt" wire:loading.class="fa-spin" wire:target="cargarDatos"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if (count($backups) === 0)
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-database fa-2x mb-2 d-block"></i>
                    No hay respaldos disponibles.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Versión IEE</th>
                                <th>Versión Inventario</th>
                                <th>Usuarios</th>
                                <th>Bienes</th>
                                <th>Tamaño</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($backups as $backup)
                                @php
                                    $meta = $backup['meta'];
                                @endphp
                                <tr>
                                    <td>
                                        <span class="font-weight-bold">{{ $backup['fecha'] }}</span>
                                    </td>
                                    <td>{{ $meta['version_iee'] ?? '—' }}</td>
                                    <td>{{ $meta['version_inventario'] ?? '—' }}</td>
                                    <td>{{ $meta['conteos']['users'] ?? '—' }}</td>
                                    <td>{{ $meta['conteos']['bienes'] ?? '—' }}</td>
                                    <td>{{ $this->formatSize($backup['zip_size']) }}</td>
                                    <td>
                                        @if ($meta)
                                            <span class="badge badge-success">OK</span>
                                        @else
                                            <span class="badge badge-warning">Sin metadata</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.backups.detalle', $backup['fecha']) }}"
                                           class="btn btn-xs btn-outline-info mr-1"
                                           title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('descargar-backups')
                                        <a href="{{ route('admin.backups.descargar', $backup['fecha']) }}"
                                           class="btn btn-xs btn-outline-success"
                                           title="Descargar ZIP">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
