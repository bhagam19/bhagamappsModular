<div>

{{-- ══════════════════════════════════════════════════════════════════
     ENCABEZADO DEL DASHBOARD
══════════════════════════════════════════════════════════════════ --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0 font-weight-bold" style="color:#1a3c5e;">
            <i class="fas fa-boxes mr-2"></i>Dashboard Ejecutivo — Inventario IEE
        </h4>
        <small class="text-muted">Estado general del inventario institucional · {{ now()->format('d/m/Y H:i') }}</small>
    </div>
    <div>
        <span class="badge badge-secondary">
            <i class="fas fa-sync-alt mr-1"></i>Actualizado al cargar
        </span>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     DASH-013: ACCESOS RÁPIDOS
══════════════════════════════════════════════════════════════════ --}}
<div class="row mb-1">
    <div class="col-12">
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header py-2">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt mr-2 text-secondary"></i>Accesos Rápidos
                </h5>
            </div>
            <div class="card-body py-2">
                <div class="row">
                    @php
                        $accesos = [
                            ['ruta' => 'inventario.bienes.index',               'icon' => 'fa-boxes',          'label' => 'Bienes',               'color' => 'btn-info'],
                            ['ruta' => 'inventario.responsables.index',         'icon' => 'fa-user-check',     'label' => 'Responsables',         'color' => 'btn-teal'],
                            ['ruta' => 'inventario.catalogos.dependencias',     'icon' => 'fa-sitemap',        'label' => 'Dependencias',         'color' => 'btn-purple'],
                            ['ruta' => 'inventario.catalogos.ubicaciones',      'icon' => 'fa-map-marker-alt', 'label' => 'Ubicaciones',          'color' => 'btn-warning'],
                            ['ruta' => 'inventario.catalogos.categorias',       'icon' => 'fa-tags',           'label' => 'Categorías',           'color' => 'btn-cyan'],
                            ['ruta' => 'inventario.mantenimientos.programados', 'icon' => 'fa-tools',          'label' => 'Mantenimientos',       'color' => 'btn-danger'],
                            ['ruta' => 'inventario.ubicaciones.historial',      'icon' => 'fa-route',          'label' => 'Hist. Ubicaciones',    'color' => 'btn-secondary'],
                            ['ruta' => 'inventario.hmb',                        'icon' => 'fa-history',        'label' => 'Hist. Modificaciones', 'color' => 'btn-dark'],
                        ];
                    @endphp
                    @foreach($accesos as $ai => $acceso)
                        <div class="col-6 col-sm-4 col-md-3 col-lg-auto mb-2" wire:key="acceso-{{ $ai }}">
                            <a href="{{ route($acceso['ruta']) }}"
                               class="btn {{ $acceso['color'] }} btn-block btn-sm d-flex flex-column align-items-center justify-content-center py-2"
                               style="min-height:64px; min-width:100px; font-size:0.78rem;">
                                <i class="fas {{ $acceso['icon'] }} mb-1" style="font-size:1.3rem;"></i>
                                {{ $acceso['label'] }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     DASH-001: KPIs — FILA 1
══════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-12 col-sm-6 col-md-3 mb-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-boxes"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total de Bienes</span>
                <span class="info-box-number">{{ number_format($totalBienes) }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-purple elevation-1"><i class="fas fa-sitemap"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Dependencias</span>
                <span class="info-box-number">{{ number_format($totalDependencias) }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-teal elevation-1"><i class="fas fa-user-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Responsables</span>
                <span class="info-box-number">{{ number_format($totalResponsables) }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-cyan elevation-1"><i class="fas fa-tags"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Categorías</span>
                <span class="info-box-number">{{ number_format($totalCategorias) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- DASH-011: KPIs — FILA 2 (con porcentajes) --}}
<div class="row">
    <div class="col-12 col-sm-6 col-md-3 mb-3">
        <div class="small-box bg-success shadow-sm">
            <div class="inner">
                <h3>{{ number_format($totalBienesActivos) }}
                    <sup style="font-size:1rem; font-weight:normal;">{{ $pctActivos }}%</sup>
                </h3>
                <p>Bienes Activos</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('inventario.bienes.index') }}" class="small-box-footer">
                Ver bienes <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-3">
        <div class="small-box bg-danger shadow-sm">
            <div class="inner">
                <h3>{{ number_format($totalBajas) }}
                    <sup style="font-size:1rem; font-weight:normal;">{{ $pctBajas }}%</sup>
                </h3>
                <p>Bienes Dados de Baja</p>
            </div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
            <a href="{{ route('inventario.heb') }}" class="small-box-footer">
                Ver historial <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-3">
        <div class="small-box bg-warning shadow-sm">
            <div class="inner">
                <h3>{{ number_format($totalMantPendientes) }}
                    <sup style="font-size:1rem; font-weight:normal;">{{ $pctMantPendientes }}%</sup>
                </h3>
                <p>Mantenimientos Pendientes</p>
            </div>
            <div class="icon"><i class="fas fa-tools"></i></div>
            <a href="{{ route('inventario.mantenimientos.programados') }}" class="small-box-footer">
                Ver mantenimientos <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-3">
        <div class="small-box bg-primary shadow-sm">
            <div class="inner">
                <h3>{{ number_format($totalMantRealizados) }}</h3>
                <p>Mantenimientos Realizados</p>
            </div>
            <div class="icon"><i class="fas fa-clipboard-check"></i></div>
            <a href="{{ route('inventario.mantenimientos.programados') }}" class="small-box-footer">
                Ver historial <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     DASH-026: TABLERO EJECUTIVO — ESTADO DEL INVENTARIO
══════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-12 mb-3">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie mr-2 text-success"></i>Estado del Inventario
                </h5>
                <div class="card-tools">
                    <span class="badge badge-secondary text-xs">Tablero ejecutivo de ciclo de vida</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($estadoEjecutivo as $ei => $estado)
                        <div class="col-12 col-sm-6 col-md-3 mb-3" wire:key="estado-exec-{{ $ei }}">
                            <div class="card shadow-sm border-{{ $estado['color'] }} h-100" style="border-left: 4px solid; border-left-color: inherit;">
                                <div class="card-body text-center py-3">
                                    <div class="mb-1">
                                        <i class="fas {{ $estado['icon'] }} fa-2x text-{{ $estado['color'] }}"></i>
                                    </div>
                                    <div class="h3 mb-0 font-weight-bold text-{{ $estado['color'] }}">
                                        {{ number_format($estado['total']) }}
                                    </div>
                                    @if($estado['pct'] !== null)
                                        <div class="text-muted small">{{ $estado['pct'] }}%</div>
                                        <div class="progress mt-2" style="height:6px;">
                                            <div class="progress-bar bg-{{ $estado['color'] }}"
                                                 style="width:{{ min(100, $estado['pct']) }}%"></div>
                                        </div>
                                    @else
                                        <div class="text-muted small">&nbsp;</div>
                                    @endif
                                    <div class="small font-weight-bold mt-1">{{ $estado['label'] }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Condición física como barra complementaria --}}
                @if(count($chartCondicion) > 0)
                    <div class="border-top pt-3 mt-1">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small font-weight-bold text-muted">
                                <i class="fas fa-flag mr-1"></i>Condición física de bienes activos
                            </span>
                            <small class="text-muted">{{ number_format($totalBienes) }} bienes</small>
                        </div>
                        <div class="d-flex flex-wrap" style="gap:0.5rem;">
                            @php
                                $condColors = ['Nuevo' => 'success', 'Bueno' => 'primary', 'Regular' => 'warning', 'Malo' => 'danger', 'Sin estado' => 'secondary'];
                            @endphp
                            @foreach($chartCondicion as $ci => $cond)
                                @php
                                    $cColor = $condColors[$cond['nombre']] ?? 'secondary';
                                    $cPct   = $totalBienes > 0 ? round($cond['total'] / $totalBienes * 100, 1) : 0;
                                @endphp
                                <div class="d-flex align-items-center" wire:key="condicion-{{ $ci }}" style="min-width:120px; flex:1;">
                                    <span class="badge badge-{{ $cColor }} mr-2" style="min-width:14px; height:14px; border-radius:50%; padding:0;"></span>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <small class="font-weight-bold">{{ $cond['nombre'] }}</small>
                                            <small class="text-muted">{{ number_format($cond['total']) }} ({{ $cPct }}%)</small>
                                        </div>
                                        <div class="progress" style="height:5px;">
                                            <div class="progress-bar bg-{{ $cColor }}" style="width:{{ $cPct }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     DASH-027: INDICADORES DE GESTIÓN + DASH-007: ALERTAS
══════════════════════════════════════════════════════════════════ --}}
<div class="row">

    {{-- Indicadores de gestión --}}
    <div class="col-12 col-md-8 mb-3">
        <div class="card card-outline card-info shadow-sm h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tachometer-alt mr-2 text-info"></i>Indicadores de Gestión
                </h5>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-12 col-sm-6 mb-3">
                        <div class="d-flex align-items-start">
                            <span class="badge badge-primary badge-pill mr-2 mt-1" style="min-width:22px;">1</span>
                            <div>
                                <div class="text-muted small">Categoría predominante</div>
                                <div class="font-weight-bold">
                                    @if(count($chartCategorias) > 0)
                                        {{ $chartCategorias[0]['nombre'] }}
                                        <span class="text-muted font-weight-normal small">({{ number_format($chartCategorias[0]['total']) }} bienes)</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 mb-3">
                        <div class="d-flex align-items-start">
                            <span class="badge badge-purple badge-pill mr-2 mt-1" style="min-width:22px;">2</span>
                            <div>
                                <div class="text-muted small">Dependencia con más bienes</div>
                                <div class="font-weight-bold">
                                    @if(count($chartDependencias) > 0)
                                        <span title="{{ $chartDependencias[0]['nombre'] }}">{{ Str::limit($chartDependencias[0]['nombre'], 30) }}</span>
                                        <span class="text-muted font-weight-normal small">({{ number_format($chartDependencias[0]['total']) }})</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 mb-3">
                        <div class="d-flex align-items-start">
                            <span class="badge badge-teal badge-pill mr-2 mt-1" style="min-width:22px;">3</span>
                            <div>
                                <div class="text-muted small">Responsable con más bienes</div>
                                <div class="font-weight-bold">
                                    @if(count($topResponsables) > 0)
                                        {{ $topResponsables[0]['nombre'] }}
                                        <span class="text-muted font-weight-normal small">({{ $topResponsables[0]['total'] }} · {{ $topResponsables[0]['pct'] }}%)</span>
                                    @else
                                        <span class="text-muted">Sin responsable asignado</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 mb-3">
                        <div class="d-flex align-items-start">
                            <span class="badge badge-info badge-pill mr-2 mt-1" style="min-width:22px;">4</span>
                            <div>
                                <div class="text-muted small">Tipo de bien predominante</div>
                                <div class="font-weight-bold">
                                    @if(count($chartCondicion) > 0)
                                        {{ $chartCondicion[0]['nombre'] }}
                                        <span class="text-muted font-weight-normal small">
                                            ({{ number_format($chartCondicion[0]['total']) }} bienes ·
                                            {{ $totalBienes > 0 ? round($chartCondicion[0]['total'] / $totalBienes * 100, 1) : 0 }}%)
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 mb-3">
                        <div class="d-flex align-items-start">
                            <span class="badge {{ $alertSolicitudesPendientes > 0 ? 'badge-warning' : 'badge-success' }} badge-pill mr-2 mt-1" style="min-width:22px;">5</span>
                            <div>
                                <div class="text-muted small">Solicitudes pendientes</div>
                                <div class="font-weight-bold {{ $alertSolicitudesPendientes > 0 ? 'text-warning' : 'text-success' }}">
                                    {{ $alertSolicitudesPendientes }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 mb-3">
                        <div class="d-flex align-items-start">
                            <span class="badge {{ $totalMantPendientes > 0 ? 'badge-warning' : 'badge-success' }} badge-pill mr-2 mt-1" style="min-width:22px;">6</span>
                            <div>
                                <div class="text-muted small">Bienes en mantenimiento</div>
                                <div class="font-weight-bold {{ $totalBienesEnMant > 0 ? 'text-warning' : 'text-success' }}">
                                    {{ number_format($totalBienesEnMant) }}
                                    <span class="text-muted font-weight-normal small">({{ $totalMantPendientes }} órdenes pendientes)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- DASH-007: Alertas --}}
    <div class="col-12 col-md-4 mb-3">
        <div class="card card-outline card-danger shadow-sm h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle mr-2 text-danger"></i>Alertas
                </h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center {{ $alertMantVencidos > 0 ? 'list-group-item-danger' : '' }}">
                        <span class="small"><i class="fas fa-calendar-times mr-2"></i>Mantenimientos vencidos</span>
                        <span class="badge {{ $alertMantVencidos > 0 ? 'badge-danger' : 'badge-success' }} badge-pill">{{ $alertMantVencidos }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center {{ $alertSinResponsable > 0 ? 'list-group-item-warning' : '' }}">
                        <span class="small"><i class="fas fa-user-times mr-2"></i>Bienes sin responsable</span>
                        <span class="badge {{ $alertSinResponsable > 0 ? 'badge-warning' : 'badge-success' }} badge-pill">{{ $alertSinResponsable }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center {{ $alertSinUbicacion > 0 ? 'list-group-item-warning' : '' }}">
                        <span class="small"><i class="fas fa-map-marker-alt mr-2"></i>Bienes sin ubicación</span>
                        <span class="badge {{ $alertSinUbicacion > 0 ? 'badge-warning' : 'badge-success' }} badge-pill">{{ $alertSinUbicacion }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center {{ $alertInfoIncompleta > 0 ? 'list-group-item-warning' : '' }}">
                        <span class="small"><i class="fas fa-info-circle mr-2"></i>Info incompleta</span>
                        <span class="badge {{ $alertInfoIncompleta > 0 ? 'badge-warning' : 'badge-success' }} badge-pill">{{ $alertInfoIncompleta }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center {{ $alertSolicitudesPendientes > 0 ? 'list-group-item-info' : '' }}">
                        <span class="small"><i class="fas fa-clock mr-2"></i>Solicitudes pendientes</span>
                        <span class="badge {{ $alertSolicitudesPendientes > 0 ? 'badge-info' : 'badge-success' }} badge-pill">{{ $alertSolicitudesPendientes }}</span>
                    </li>
                </ul>
                @php $totalAlertas = $alertMantVencidos + $alertSinResponsable + $alertSinUbicacion + $alertInfoIncompleta + $alertSolicitudesPendientes; @endphp
                <div class="p-3">
                    @if($totalAlertas === 0)
                        <div class="alert alert-success mb-0 py-2 small">
                            <i class="fas fa-check-circle mr-1"></i>Sin alertas activas. ¡Todo en orden!
                        </div>
                    @else
                        <div class="alert alert-warning mb-0 py-2 small">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            <strong>{{ $totalAlertas }}</strong> situaciones requieren atención.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════
     DASH-022/023: BIENES ESTRATÉGICOS
     Clasificación: keyword en nombre del bien (portátil, video beam, etc.)
══════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-12 mb-3">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-star mr-2 text-info"></i>Bienes Estratégicos
                </h5>
                <div class="card-tools">
                    <span class="badge badge-secondary text-xs">Clasificación por descripción del bien</span>
                </div>
            </div>
            <div class="card-body">
                @if(count($bienesEstrategicos) > 0 && $totalBienes > 0)
                    <div class="row">
                        @foreach($bienesEstrategicos as $bei => $be)
                            @if($be['total'] > 0)
                                <div class="col-12 col-sm-6 col-md-4 col-xl-3 mb-3" wire:key="be-{{ $bei }}">
                                    <div class="d-flex align-items-center p-2 rounded border">
                                        <span class="badge badge-pill badge-secondary mr-2" style="min-width:22px; font-size:0.7rem;">{{ $bei + 1 }}</span>
                                        <i class="fas {{ $be['icon'] }} text-{{ $be['color'] }} mr-2" style="font-size:1.2rem; width:20px; text-align:center;"></i>
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="d-flex justify-content-between align-items-baseline">
                                                <span class="small font-weight-bold text-truncate">{{ $be['label'] }}</span>
                                                <span class="ml-1 small text-muted text-nowrap">{{ number_format($be['total']) }} · {{ $be['pct'] }}%</span>
                                            </div>
                                            <div class="progress mt-1" style="height:5px;">
                                                <div class="progress-bar bg-{{ $be['color'] }}" style="width:{{ min(100, $be['pct'] * 5) }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @php
                        $totalEstrategicos = collect($bienesEstrategicos)->sum('total');
                        $pctEstrategicos   = $totalBienes > 0 ? round($totalEstrategicos / $totalBienes * 100, 1) : 0;
                    @endphp
                    <div class="border-top pt-2 mt-1">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Los bienes estratégicos representan <strong>{{ $pctEstrategicos }}%</strong> del inventario total
                            ({{ number_format($totalEstrategicos) }} de {{ number_format($totalBienes) }} bienes).
                            Clasificación basada en palabras clave en el nombre del bien.
                        </small>
                    </div>
                @else
                    <p class="text-muted text-center py-3 small">
                        <i class="fas fa-info-circle mr-1"></i>Sin bienes estratégicos identificados.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     DASH-028: GRUPOS INSTITUCIONALES + DASH-029: RANKING TECNOLÓGICO
══════════════════════════════════════════════════════════════════ --}}
<div class="row">

    {{-- DASH-028: Grupos Institucionales --}}
    <div class="col-12 col-md-7 mb-3">
        <div class="card card-outline card-primary shadow-sm h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-layer-group mr-2 text-primary"></i>Grupos Institucionales
                </h5>
                <div class="card-tools">
                    <span class="badge badge-secondary text-xs">Clasificación por categoría</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if(count($gruposInstitucionales) > 0)
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="pl-3" style="width:36px;">#</th>
                                <th>Grupo</th>
                                <th class="text-right">Bienes</th>
                                <th class="text-right pr-3" style="width:60px;">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gruposInstitucionales as $gi => $grupo)
                                <tr wire:key="grupo-inst-{{ $gi }}">
                                    <td class="pl-3 text-muted small">{{ $gi + 1 }}</td>
                                    <td class="small">
                                        <i class="fas {{ $grupo['icon'] }} text-{{ $grupo['color'] }} mr-1"></i>
                                        {{ $grupo['label'] }}
                                    </td>
                                    <td class="text-right small font-weight-bold">{{ number_format($grupo['total']) }}</td>
                                    <td class="text-right pr-3">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <small class="text-muted mr-1">{{ $grupo['pct'] }}%</small>
                                            <div class="progress" style="width:40px; height:6px;">
                                                <div class="progress-bar bg-{{ $grupo['color'] }}" style="width:{{ $grupo['pct'] }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted text-center py-4 small"><i class="fas fa-info-circle mr-1"></i>Sin datos.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- DASH-029: Inventario Tecnológico --}}
    <div class="col-12 col-md-5 mb-3">
        <div class="card card-outline card-cyan shadow-sm h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-microchip mr-2 text-cyan"></i>Inventario Tecnológico
                </h5>
                <div class="card-tools">
                    <span class="badge badge-secondary text-xs">Top bienes TIC</span>
                </div>
            </div>
            <div class="card-body">
                @php
                    $ticItems = collect($bienesEstrategicos)->filter(fn($b) =>
                        in_array($b['label'], ['Portátiles','Computadores','Video Beam','Impresoras','Televisores','Tablets','UPS','Switch / Red','Servidores'])
                        && $b['total'] > 0
                    )->values();
                    $totalTic = $ticItems->sum('total');
                    $pctTic   = $totalBienes > 0 ? round($totalTic / $totalBienes * 100, 1) : 0;
                @endphp
                @if($ticItems->count() > 0)
                    @foreach($ticItems as $ti => $tic)
                        <div class="d-flex align-items-center mb-2" wire:key="tic-{{ $ti }}">
                            <i class="fas {{ $tic['icon'] }} text-{{ $tic['color'] }} mr-2" style="width:16px; text-align:center;"></i>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <span class="small font-weight-bold">{{ $tic['label'] }}</span>
                                    <span class="small text-muted">{{ number_format($tic['total']) }} ({{ $tic['pct'] }}%)</span>
                                </div>
                                <div class="progress" style="height:5px;">
                                    <div class="progress-bar bg-{{ $tic['color'] }}" style="width:{{ min(100, $tic['pct'] * 5) }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="border-top pt-2 mt-1 text-center">
                        <span class="badge badge-info">
                            Total TIC: {{ number_format($totalTic) }} bienes · {{ $pctTic }}% del inventario
                        </span>
                    </div>
                @else
                    <p class="text-muted text-center py-3 small">
                        <i class="fas fa-info-circle mr-1"></i>Sin bienes tecnológicos identificados.
                    </p>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════
     DASH-024/025: TOP DEPENDENCIAS + TOP RESPONSABLES
══════════════════════════════════════════════════════════════════ --}}
<div class="row">

    {{-- DASH-024: Top 10 Dependencias con responsable --}}
    <div class="col-12 col-md-6 mb-3">
        <div class="card card-outline card-purple shadow-sm h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-sitemap mr-2 text-purple"></i>Top 10 Dependencias
                </h5>
            </div>
            <div class="card-body p-0">
                @if(count($chartDependencias) > 0)
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="pl-3" style="width:28px;">#</th>
                                <th>Dependencia</th>
                                <th>Responsable</th>
                                <th class="text-right">Bienes</th>
                                <th class="text-right pr-3">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($chartDependencias as $di => $dep)
                                <tr wire:key="top-dep-{{ $di }}">
                                    <td class="pl-3 text-muted small">{{ $di + 1 }}</td>
                                    <td class="small" style="max-width:140px; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;"
                                        title="{{ $dep['nombre'] }}">{{ $dep['nombre'] }}</td>
                                    <td class="small text-muted" style="max-width:120px; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;"
                                        title="{{ $dep['responsable'] }}">{{ $dep['responsable'] }}</td>
                                    <td class="text-right small font-weight-bold">{{ number_format($dep['total']) }}</td>
                                    <td class="text-right pr-3 small text-muted">
                                        {{ $totalBienes > 0 ? number_format($dep['total'] / $totalBienes * 100, 1) : '0.0' }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted text-center py-4 small"><i class="fas fa-info-circle mr-1"></i>Sin datos.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- DASH-025: Top 10 Responsables con porcentaje --}}
    <div class="col-12 col-md-6 mb-3">
        <div class="card card-outline card-teal shadow-sm h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-check mr-2 text-teal"></i>Top 10 Responsables
                </h5>
            </div>
            <div class="card-body p-0">
                @if(count($topResponsables) > 0)
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="pl-3" style="width:28px;">#</th>
                                <th>Responsable</th>
                                <th class="text-right">Bienes</th>
                                <th class="text-right pr-3">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topResponsables as $ri => $resp)
                                <tr wire:key="top-resp-{{ $ri }}">
                                    <td class="pl-3 text-muted small">{{ $ri + 1 }}</td>
                                    <td class="small">{{ $resp['nombre'] }}</td>
                                    <td class="text-right small font-weight-bold">{{ number_format($resp['total']) }}</td>
                                    <td class="text-right pr-3 small text-muted">{{ $resp['pct'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted text-center py-4 small">
                        <i class="fas fa-info-circle mr-1"></i>Sin responsables con bienes asignados.
                    </p>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════
     DASH-014: CALIDAD DE DATOS
══════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-12 mb-3">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar mr-2 text-success"></i>Calidad de Datos
                </h5>
            </div>
            <div class="card-body">
                @if($totalBienes > 0)
                    @php
                        $calidadItems = [
                            ['icon' => 'fa-user-check',     'color' => 'teal',    'label' => 'Con Responsable', 'count' => $countConResponsable, 'pct' => $pctConResponsable],
                            ['icon' => 'fa-map-marker-alt', 'color' => 'warning', 'label' => 'Con Ubicación',   'count' => $countConUbicacion,   'pct' => $pctConUbicacion],
                            ['icon' => 'fa-tags',           'color' => 'primary', 'label' => 'Con Categoría',   'count' => $countConCategoria,   'pct' => $pctConCategoria],
                            ['icon' => 'fa-flag',           'color' => 'success', 'label' => 'Con Estado',      'count' => $countConEstado,      'pct' => $pctConEstado],
                            ['icon' => 'fa-truck',          'color' => 'info',    'label' => 'Con Origen',      'count' => $countConOrigen,      'pct' => $pctConOrigen],
                        ];
                        $promCalidad  = (int) round(($pctConResponsable + $pctConUbicacion + $pctConCategoria + $pctConEstado + $pctConOrigen) / 5);
                        $colorCalidad = $promCalidad >= 80 ? 'success' : ($promCalidad >= 50 ? 'warning' : 'danger');
                    @endphp
                    <div class="row align-items-end">
                        @foreach($calidadItems as $ci_i => $ci)
                            <div class="col-12 col-sm-6 col-md-4 col-lg mb-3" wire:key="calidad-{{ $ci_i }}">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small font-weight-bold"><i class="fas {{ $ci['icon'] }} mr-1 text-{{ $ci['color'] }}"></i>{{ $ci['label'] }}</span>
                                    <span class="small text-muted">{{ number_format($ci['count']) }}/{{ number_format($totalBienes) }}</span>
                                </div>
                                <div class="progress mb-1" style="height:10px;" title="{{ $ci['pct'] }}%">
                                    <div class="progress-bar bg-{{ $ci['color'] }}"
                                         role="progressbar"
                                         style="width:{{ $ci['pct'] }}%"
                                         aria-valuenow="{{ $ci['pct'] }}"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="text-right">
                                    <small class="text-muted">{{ $ci['pct'] }}%</small>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-12 col-lg-auto mb-3 text-center">
                            <div class="px-3">
                                <span class="h4 font-weight-bold text-{{ $colorCalidad }}">{{ $promCalidad }}%</span>
                                <br><small class="text-muted">Índice general<br>de calidad</small>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-muted text-center py-3 small">
                        <i class="fas fa-info-circle mr-1"></i>No hay bienes registrados aún.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     DASH-002/003: GRÁFICAS — FILA 1
══════════════════════════════════════════════════════════════════ --}}
<div class="row">

    {{-- DASH-002: Bienes por Categoría --}}
    <div class="col-12 col-md-6 mb-3">
        <div class="card card-outline card-primary shadow-sm h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tags mr-2 text-primary"></i>Bienes por Categoría
                </h5>
            </div>
            <div class="card-body">
                @if(count($chartCategorias) > 0)
                    <div
                        wire:ignore
                        x-data="{
                            chart: null,
                            init() {
                                const labels = @json(collect($chartCategorias)->pluck('nombre'));
                                const data   = @json(collect($chartCategorias)->pluck('total'));
                                const total  = data.reduce((a, b) => a + b, 0);
                                const colors = ['#4dc9f6','#f67019','#f53794','#537bc4','#acc236','#166a8f','#00a950','#58595b','#8549ba','#e6194b','#3cb44b','#ffe119','#4363d8','#f58231','#911eb4'];
                                const ctx = this.$el.querySelector('#chartCategorias').getContext('2d');
                                this.chart = new Chart(ctx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: labels,
                                        datasets: [{ data: data, backgroundColor: colors.slice(0, labels.length), borderWidth: 1 }]
                                    },
                                    options: {
                                        responsive: true,
                                        plugins: {
                                            legend: { position: 'right', labels: { font: { size: 11 } } },
                                            tooltip: {
                                                callbacks: {
                                                    label: (ctx) => {
                                                        const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                                        return ` ${ctx.label}: ${ctx.raw} (${pct}%)`;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            }
                        }"
                        x-init="init()"
                    >
                        <canvas id="chartCategorias" style="max-height:280px;"></canvas>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            Total: <strong>{{ array_sum(array_column($chartCategorias, 'total')) }}</strong> bienes en
                            <strong>{{ count($chartCategorias) }}</strong> categorías
                        </small>
                    </div>
                @else
                    <p class="text-muted text-center py-4"><i class="fas fa-info-circle mr-1"></i>Sin datos de categorías disponibles.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- DASH-003: Bienes por Dependencia (Top 10) --}}
    <div class="col-12 col-md-6 mb-3">
        <div class="card card-outline card-purple shadow-sm h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-sitemap mr-2 text-purple"></i>Bienes por Dependencia (Top 10)
                </h5>
            </div>
            <div class="card-body">
                @if(count($chartDependencias) > 0)
                    <div
                        wire:ignore
                        x-data="{
                            chart: null,
                            init() {
                                const labels = @json(collect($chartDependencias)->pluck('nombre'));
                                const data   = @json(collect($chartDependencias)->pluck('total'));
                                const ctx    = this.$el.querySelector('#chartDependencias').getContext('2d');
                                this.chart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                            label: 'Bienes',
                                            data: data,
                                            backgroundColor: 'rgba(102,16,242,0.65)',
                                            borderColor: 'rgba(102,16,242,1)',
                                            borderWidth: 1,
                                        }]
                                    },
                                    options: {
                                        indexAxis: 'y',
                                        responsive: true,
                                        plugins: { legend: { display: false } },
                                        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
                                    }
                                });
                            }
                        }"
                        x-init="init()"
                    >
                        <canvas id="chartDependencias" style="max-height:280px;"></canvas>
                    </div>
                @else
                    <p class="text-muted text-center py-4"><i class="fas fa-info-circle mr-1"></i>Sin datos de dependencias disponibles.</p>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════
     DASH-005: ORIGEN DE LOS BIENES
══════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-12 col-md-6 mb-3">
        <div class="card card-outline card-warning shadow-sm h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-truck mr-2 text-warning"></i>Origen de los Bienes
                </h5>
            </div>
            <div class="card-body">
                @if(count($chartOrigenes) > 0)
                    <div
                        wire:ignore
                        x-data="{
                            chart: null,
                            init() {
                                const labels = @json(collect($chartOrigenes)->pluck('nombre'));
                                const data   = @json(collect($chartOrigenes)->pluck('total'));
                                const total  = data.reduce((a, b) => a + b, 0);
                                const colors = ['#6c757d','#007bff','#fd7e14','#20c997','#e83e8c','#6610f2','#17a2b8','#ffc107','#28a745','#dc3545','#343a40','#f8f9fa','#6f42c1','#e83e8c','#17a2b8'];
                                const ctx    = this.$el.querySelector('#chartOrigenes').getContext('2d');
                                this.chart = new Chart(ctx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: labels,
                                        datasets: [{ data: data, backgroundColor: colors.slice(0, labels.length), borderWidth: 1 }]
                                    },
                                    options: {
                                        responsive: true,
                                        plugins: {
                                            legend: { position: 'right', labels: { font: { size: 11 } } },
                                            tooltip: {
                                                callbacks: {
                                                    label: (ctx) => {
                                                        const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                                        return ` ${ctx.label}: ${ctx.raw} (${pct}%)`;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            }
                        }"
                        x-init="init()"
                    >
                        <canvas id="chartOrigenes" style="max-height:280px;"></canvas>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <strong>{{ number_format($countConOrigen) }}</strong> bienes con origen conocido ·
                            <strong>{{ number_format($totalBienes - $countConOrigen) }}</strong> sin origen registrado
                        </small>
                    </div>
                @else
                    <p class="text-muted text-center py-4"><i class="fas fa-info-circle mr-1"></i>Sin datos de origen disponibles.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Concentración del inventario (top 10 dependencias) --}}
    <div class="col-12 col-md-6 mb-3">
        <div class="card card-outline card-secondary shadow-sm h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-compress-alt mr-2 text-secondary"></i>Concentración del Inventario
                </h5>
                <div class="card-tools">
                    <span class="badge badge-secondary text-xs">Top 10 dependencias</span>
                </div>
            </div>
            <div class="card-body">
                @if($totalBienes > 0 && count($chartDependencias) > 0)
                    @php
                        $top10Total = collect($chartDependencias)->sum('total');
                        $pctConcentracion = round($top10Total / $totalBienes * 100, 1);
                        $pctRestante = 100 - $pctConcentracion;
                    @endphp
                    <div class="text-center mb-3">
                        <div class="h2 font-weight-bold text-secondary mb-0">{{ $pctConcentracion }}%</div>
                        <div class="text-muted small">del inventario en las 10 principales dependencias</div>
                        <div class="text-muted small">({{ number_format($top10Total) }} de {{ number_format($totalBienes) }} bienes)</div>
                    </div>
                    <div class="progress mb-3" style="height:16px; border-radius:8px;">
                        <div class="progress-bar bg-secondary" style="width:{{ $pctConcentracion }}%; border-radius:8px 0 0 8px;">
                            <small>Top 10</small>
                        </div>
                        <div class="progress-bar bg-light text-dark" style="width:{{ $pctRestante }}%; border-radius:0 8px 8px 0;">
                            <small>Resto</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span><i class="fas fa-circle text-secondary mr-1"></i>Top 10: {{ number_format($top10Total) }} bienes ({{ $pctConcentracion }}%)</span>
                        <span><i class="fas fa-circle text-light mr-1" style="border:1px solid #dee2e6; border-radius:50%;"></i>Resto: {{ number_format($totalBienes - $top10Total) }} ({{ $pctRestante }}%)</span>
                    </div>
                    <div class="border-top pt-2 mt-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Una concentración mayor al 80% en pocas dependencias puede indicar necesidad de redistribución.
                        </small>
                    </div>
                @else
                    <p class="text-muted text-center py-4 small"><i class="fas fa-info-circle mr-1"></i>Sin datos suficientes.</p>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════
     DASH-006: ÚLTIMOS MOVIMIENTOS
══════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-12 mb-3">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history mr-2 text-info"></i>Últimos Movimientos
                </h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="nav nav-tabs px-3 pt-2" id="tabMovimientos" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-mods" data-toggle="tab" href="#pane-mods" role="tab">
                            <i class="fas fa-edit mr-1"></i>Modificaciones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-ubic" data-toggle="tab" href="#pane-ubic" role="tab">
                            <i class="fas fa-map-marker-alt mr-1"></i>Ubicaciones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-elim" data-toggle="tab" href="#pane-elim" role="tab">
                            <i class="fas fa-trash-alt mr-1"></i>Eliminaciones
                        </a>
                    </li>
                </ul>
                <div class="tab-content px-3 py-2">

                    {{-- Modificaciones --}}
                    <div class="tab-pane fade show active" id="pane-mods" role="tabpanel">
                        @forelse($ultimasModificaciones as $mod)
                            <div class="d-flex align-items-center py-1 border-bottom" wire:key="mod-{{ $mod->id }}">
                                <span class="badge badge-pill
                                    {{ $mod->estado === 'aprobado' ? 'badge-success' : ($mod->estado === 'pendiente' ? 'badge-warning' : 'badge-secondary') }}
                                    mr-2" style="min-width:70px;">
                                    {{ ucfirst($mod->estado ?? '—') }}
                                </span>
                                <div class="flex-grow-1">
                                    <span class="small font-weight-bold">{{ $mod->bien?->nombre ?? '(bien eliminado)' }}</span>
                                    <span class="small text-muted ml-1">· campo: {{ $mod->campo }}</span>
                                </div>
                                <small class="text-muted ml-2">{{ $mod->created_at?->diffForHumans() }}</small>
                            </div>
                        @empty
                            <p class="text-muted small py-3 text-center">Sin modificaciones recientes.</p>
                        @endforelse
                    </div>

                    {{-- Ubicaciones --}}
                    <div class="tab-pane fade" id="pane-ubic" role="tabpanel">
                        @forelse($ultimasUbicaciones as $ubic)
                            <div class="d-flex align-items-center py-1 border-bottom" wire:key="ubic-{{ $ubic->id }}">
                                <i class="fas fa-arrow-right text-info mr-2"></i>
                                <div class="flex-grow-1">
                                    <span class="small font-weight-bold">{{ $ubic->bien?->nombre ?? '(bien eliminado)' }}</span>
                                    @if($ubic->ubicacionDestino)
                                        <span class="small text-muted ml-1">→ {{ $ubic->ubicacionDestino->nombre }}</span>
                                    @endif
                                </div>
                                <small class="text-muted ml-2">{{ $ubic->fecha_movimiento?->diffForHumans() }}</small>
                            </div>
                        @empty
                            <p class="text-muted small py-3 text-center">Sin cambios de ubicación recientes.</p>
                        @endforelse
                    </div>

                    {{-- Eliminaciones aprobadas --}}
                    <div class="tab-pane fade" id="pane-elim" role="tabpanel">
                        @forelse($ultimasEliminaciones as $elim)
                            <div class="d-flex align-items-center py-1 border-bottom" wire:key="elim-{{ $elim->id }}">
                                <i class="fas fa-trash-alt text-danger mr-2"></i>
                                <div class="flex-grow-1">
                                    <span class="small font-weight-bold">{{ $elim->bien?->nombre ?? '(registro eliminado)' }}</span>
                                    <span class="badge badge-danger badge-pill ml-1">Aprobada</span>
                                </div>
                                <small class="text-muted ml-2">{{ $elim->created_at?->diffForHumans() }}</small>
                            </div>
                        @empty
                            <p class="text-muted small py-3 text-center">Sin eliminaciones aprobadas recientes.</p>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</div>
