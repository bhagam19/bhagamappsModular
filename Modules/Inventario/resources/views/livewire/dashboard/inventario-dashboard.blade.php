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
     DASH-013: ACCESOS RÁPIDOS (al tope)
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
     DASH-017: RESUMEN EJECUTIVO + DASH-007: ALERTAS
══════════════════════════════════════════════════════════════════ --}}
<div class="row">

    {{-- Resumen Ejecutivo --}}
    <div class="col-12 col-md-8 mb-3">
        <div class="card card-outline card-info shadow-sm h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clipboard-list mr-2 text-info"></i>Resumen Ejecutivo
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
                                        {{ $chartDependencias[0]['nombre'] }}
                                        <span class="text-muted font-weight-normal small">({{ number_format($chartDependencias[0]['total']) }} bienes)</span>
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
                                        <span class="text-muted font-weight-normal small">({{ $topResponsables[0]['total'] }} bienes)</span>
                                    @else
                                        <span class="text-muted">Sin responsable asignado</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 mb-3">
                        <div class="d-flex align-items-start">
                            <span class="badge {{ $alertSolicitudesPendientes > 0 ? 'badge-warning' : 'badge-success' }} badge-pill mr-2 mt-1" style="min-width:22px;">4</span>
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
                            <span class="badge {{ $totalMantPendientes > 0 ? 'badge-warning' : 'badge-success' }} badge-pill mr-2 mt-1" style="min-width:22px;">5</span>
                            <div>
                                <div class="text-muted small">Mantenimientos pendientes</div>
                                <div class="font-weight-bold {{ $totalMantPendientes > 0 ? 'text-warning' : 'text-success' }}">
                                    {{ $totalMantPendientes }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 mb-3">
                        <div class="d-flex align-items-start">
                            <span class="badge badge-secondary badge-pill mr-2 mt-1" style="min-width:22px;">6</span>
                            <div>
                                <div class="text-muted small">Mantenimientos realizados</div>
                                <div class="font-weight-bold">{{ $totalMantRealizados }}</div>
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
     DASH-014: CALIDAD DE DATOS (cerca de KPIs, con conteos y pctConOrigen)
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
     DASH-015/016: TOP 10 DEPENDENCIAS + TOP 10 RESPONSABLES
══════════════════════════════════════════════════════════════════ --}}
<div class="row">

    {{-- DASH-015: Top 10 Dependencias --}}
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
                                <th class="pl-3" style="width:32px;">#</th>
                                <th>Dependencia</th>
                                <th class="text-right">Bienes</th>
                                <th class="text-right pr-3">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($chartDependencias as $di => $dep)
                                <tr wire:key="top-dep-{{ $di }}">
                                    <td class="pl-3 text-muted small">{{ $di + 1 }}</td>
                                    <td class="small">{{ $dep['nombre'] }}</td>
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

    {{-- DASH-016: Top 10 Responsables --}}
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
                                <th class="pl-3" style="width:32px;">#</th>
                                <th>Responsable</th>
                                <th class="text-right pr-3">Bienes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topResponsables as $ri => $resp)
                                <tr wire:key="top-resp-{{ $ri }}">
                                    <td class="pl-3 text-muted small">{{ $ri + 1 }}</td>
                                    <td class="small">{{ $resp['nombre'] }}</td>
                                    <td class="text-right pr-3 small font-weight-bold">{{ number_format($resp['total']) }}</td>
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
     DASH-004/005: GRÁFICAS — FILA 2
══════════════════════════════════════════════════════════════════ --}}
<div class="row">

    {{-- DASH-004: Estado del Inventario --}}
    <div class="col-12 col-md-6 mb-3">
        <div class="card card-outline card-success shadow-sm h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie mr-2 text-success"></i>Estado del Inventario
                </h5>
            </div>
            <div class="card-body">
                @if(count($chartEstados) > 0)
                    <div
                        wire:ignore
                        x-data="{
                            chart: null,
                            init() {
                                const labels = @json(collect($chartEstados)->pluck('nombre'));
                                const data   = @json(collect($chartEstados)->pluck('total'));
                                const total  = data.reduce((a, b) => a + b, 0);
                                const colors = ['#28a745','#ffc107','#dc3545','#17a2b8','#6c757d','#fd7e14','#6610f2'];
                                const ctx    = this.$el.querySelector('#chartEstados').getContext('2d');
                                this.chart = new Chart(ctx, {
                                    type: 'pie',
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
                        <canvas id="chartEstados" style="max-height:280px;"></canvas>
                    </div>
                @else
                    <p class="text-muted text-center py-4"><i class="fas fa-info-circle mr-1"></i>Sin datos de estados disponibles.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- DASH-005: Origen de los Bienes (DASH-012: condición corregida) --}}
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

</div>

{{-- ══════════════════════════════════════════════════════════════════
     DASH-006: ÚLTIMOS MOVIMIENTOS (con wire:key — DASH-020)
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
