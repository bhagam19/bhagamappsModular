<div>
    <div class="row">
        {{-- ── Encabezado de ficha ──────────────────────────────────────── --}}
        <div class="col-12 mb-3">
            <a href="{{ route('admin.backups.index') }}" class="btn btn-sm btn-outline-secondary mr-2">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
            @can('descargar-backups')
                @if ($existe)
                    <a href="{{ route('admin.backups.descargar', $fecha) }}" class="btn btn-sm btn-success">
                        <i class="fas fa-download mr-1"></i> Descargar ZIP
                    </a>
                @endif
            @endcan
        </div>

        {{-- ── Ficha técnica institucional ──────────────────────────────── --}}
        <div class="col-lg-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-archive mr-2"></i>Ficha del Respaldo
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="text-muted pl-3" style="width:40%">Fecha</th>
                                <td>{{ $meta['fecha'] ?? $fecha }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted pl-3">Generado en</th>
                                <td>{{ $meta['generado_en'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted pl-3">Archivo ZIP</th>
                                <td>
                                    {{ $zipName }}
                                    @if (!$existe)
                                        <span class="badge badge-warning ml-1">No encontrado</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted pl-3">Tamaño ZIP</th>
                                <td>{{ $existe ? $this->formatSize($zipSize) : '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted pl-3">Entorno</th>
                                <td>{{ $meta['entorno'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted pl-3">Base de datos</th>
                                <td>{{ $meta['db_database'] ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Versiones --}}
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-code-branch mr-2"></i>Versiones</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            @foreach ([
                                'IEE'         => 'version_iee',
                                'BhagamApps'  => 'version_bhagamapps',
                                'Inventario'  => 'version_inventario',
                                'User'        => 'version_user',
                                'Apps'        => 'version_apps',
                            ] as $label => $key)
                            <tr>
                                <th class="text-muted pl-3" style="width:40%">{{ $label }}</th>
                                <td>{{ $meta[$key] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── Conteos por tabla ────────────────────────────────────────── --}}
        <div class="col-lg-6">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-2"></i>Conteos por Tabla
                    </h3>
                    @if ($meta)
                        <div class="card-tools">
                            <span class="badge badge-secondary">
                                {{ $meta['tablas_exportadas'] ?? 0 }} tablas —
                                {{ number_format($meta['total_registros'] ?? 0) }} registros totales
                            </span>
                        </div>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if ($meta && isset($meta['conteos']))
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Tabla</th>
                                        <th class="text-right">Registros</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($meta['conteos'] as $tabla => $cantidad)
                                        <tr>
                                            <td><code>{{ $tabla }}</code></td>
                                            <td class="text-right">
                                                <span class="badge badge-{{ $cantidad > 0 ? 'light' : 'secondary' }}">
                                                    {{ number_format($cantidad) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Metadata no disponible para este respaldo.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
