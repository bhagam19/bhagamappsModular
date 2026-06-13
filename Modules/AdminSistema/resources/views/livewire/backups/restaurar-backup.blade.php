<div>
    {{-- ── Banda de advertencia fija ──────────────────────────────────────── --}}
    <div class="callout callout-warning">
        <h5><i class="fas fa-exclamation-triangle mr-1"></i>Zona de recuperación de desastres</h5>
        <p class="mb-0">
            Esta acción sobreescribe los registros actuales con los del Snapshot seleccionado.
            Solo disponible para el <strong>Administrador Principal</strong>.
            Los datos no incluidos en el Snapshot <strong>no se eliminarán</strong>.
        </p>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- ESTADO: listado                                                        --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @if ($estado === 'listado')

        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>Seleccionar Snapshot para Restaurar
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" wire:click="$refresh">
                        <i class="fas fa-sync-alt" wire:loading.class="fa-spin" wire:target="$refresh"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                @if (count($backups) === 0)
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-database fa-2x mb-2 d-block"></i>
                        No hay respaldos disponibles.
                        <a href="{{ route('admin.backups.index') }}" class="d-block mt-2">
                            Ir al Centro de Backups para generar uno.
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Versión IEE</th>
                                    <th>Usuarios</th>
                                    <th>Bienes</th>
                                    <th>Tamaño</th>
                                    <th>Estado</th>
                                    <th class="text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($backups as $backup)
                                    @php $meta = $backup['meta']; @endphp
                                    <tr>
                                        <td><strong>{{ $backup['fecha'] }}</strong></td>
                                        <td>{{ $meta['version_iee'] ?? '—' }}</td>
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
                                            @if ($meta)
                                                <button
                                                    wire:click="seleccionar('{{ $backup['fecha'] }}')"
                                                    wire:loading.attr="disabled"
                                                    class="btn btn-xs btn-warning"
                                                    title="Restaurar este snapshot"
                                                >
                                                    <i class="fas fa-undo-alt mr-1"
                                                       wire:loading.class="fa-spin fas fa-spinner"
                                                       wire:target="seleccionar('{{ $backup['fecha'] }}')"></i>
                                                    Restaurar
                                                </button>
                                            @else
                                                <span class="text-muted text-sm">Sin metadata</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- ESTADO: vista-previa (RESTORE-WEB-003)                                 --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @elseif ($estado === 'vista-previa')

        <div class="row">
            {{-- Ficha del snapshot --}}
            <div class="col-lg-6">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-archive mr-2"></i>
                            Snapshot: <strong>IEE-{{ $fechaSeleccionada }}.zip</strong>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th class="text-muted pl-3" style="width:40%">Fecha</th>
                                    <td>{{ $metaSeleccionada['fecha'] ?? $fechaSeleccionada }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-3">Generado en</th>
                                    <td>{{ $metaSeleccionada['generado_en'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-3">Tamaño ZIP</th>
                                    <td>{{ $this->formatSize($zipSize) }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-3">Entorno</th>
                                    <td>{{ $metaSeleccionada['entorno'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-3">Base de datos</th>
                                    <td>{{ $metaSeleccionada['db_database'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-3">Versión IEE</th>
                                    <td><strong>v{{ $metaSeleccionada['version_iee'] ?? '—' }}</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-3">Versión BhagamApps</th>
                                    <td>v{{ $metaSeleccionada['version_bhagamapps'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-3">Tablas exportadas</th>
                                    <td>{{ $metaSeleccionada['tablas_exportadas'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-3">Total registros</th>
                                    <td><strong>{{ number_format($metaSeleccionada['total_registros'] ?? 0) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Conteos principales --}}
            <div class="col-lg-6">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table mr-2"></i>Datos que se restaurarán
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        @php
                            $conteos   = $metaSeleccionada['conteos'] ?? [];
                            $destacados = [
                                'users'        => 'Usuarios',
                                'bienes'       => 'Bienes',
                                'dependencias' => 'Dependencias',
                                'categorias'   => 'Categorías',
                                'permissions'  => 'Permisos',
                                'roles'        => 'Roles',
                            ];
                        @endphp
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                @foreach ($destacados as $key => $label)
                                    @if (isset($conteos[$key]))
                                    <tr>
                                        <th class="text-muted pl-3" style="width:50%">{{ $label }}</th>
                                        <td>
                                            <span class="badge badge-{{ $conteos[$key] > 0 ? 'primary' : 'secondary' }} px-2">
                                                {{ number_format($conteos[$key]) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Acciones vista-previa --}}
        <div class="d-flex gap-2 mt-1">
            <button wire:click="cancelar" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-left mr-1"></i> Volver al listado
            </button>
            <button wire:click="irAConfirmar" class="btn btn-warning">
                <i class="fas fa-exclamation-triangle mr-1"></i> Continuar con la restauración
            </button>
        </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- ESTADO: confirmar (RESTORE-WEB-004)                                    --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @elseif ($estado === 'confirmar')

        <div class="card card-outline card-danger">
            <div class="card-header bg-danger">
                <h3 class="card-title text-white">
                    <i class="fas fa-exclamation-circle mr-2"></i>Confirmación de restauración
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-danger">
                    <strong><i class="fas fa-exclamation-triangle mr-1"></i>Esta acción es destructiva y no se puede deshacer.</strong><br>
                    Los registros actuales de la base de datos serán sobreescritos con los del Snapshot:
                    <br><strong class="text-monospace">IEE-{{ $fechaSeleccionada }}.zip</strong>
                    ({{ $metaSeleccionada['total_registros'] ?? '—' }} registros · {{ $this->formatSize($zipSize) }})
                </div>

                <p class="mb-2">Para confirmar, escribe exactamente <code class="text-danger font-weight-bold">RESTAURAR</code> en el campo:</p>

                <div class="form-group mb-4">
                    <input
                        wire:model.live="confirmacion"
                        type="text"
                        class="form-control form-control-lg text-center font-weight-bold {{ $confirmacion === 'RESTAURAR' ? 'is-valid border-success' : ($confirmacion !== '' ? 'is-invalid border-danger' : '') }}"
                        placeholder="RESTAURAR"
                        autocomplete="off"
                        spellcheck="false"
                        style="letter-spacing: 0.2em; font-size: 1.4rem; max-width: 320px;"
                    >
                    @if ($confirmacion !== '' && $confirmacion !== 'RESTAURAR')
                        <div class="invalid-feedback d-block">
                            Debes escribir exactamente: <strong>RESTAURAR</strong>
                        </div>
                    @endif
                    @if ($confirmacion === 'RESTAURAR')
                        <div class="valid-feedback d-block text-success">
                            <i class="fas fa-check-circle mr-1"></i>Confirmación válida.
                        </div>
                    @endif
                </div>

                <div class="d-flex">
                    <button wire:click="cancelar" class="btn btn-secondary mr-3">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>

                    <button
                        wire:click="ejecutarRestauracion"
                        wire:loading.attr="disabled"
                        class="btn btn-danger {{ $confirmacion !== 'RESTAURAR' ? 'disabled' : '' }}"
                        @if ($confirmacion !== 'RESTAURAR') disabled @endif
                    >
                        <span wire:loading.remove wire:target="ejecutarRestauracion">
                            <i class="fas fa-undo-alt mr-1"></i> Ejecutar restauración
                        </span>
                        <span wire:loading wire:target="ejecutarRestauracion">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Restaurando... por favor espera
                        </span>
                    </button>
                </div>
            </div>
        </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- ESTADO: resultado (RESTORE-WEB-008)                                    --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @elseif ($estado === 'resultado')

        @if ($exito)
            <div class="alert alert-success">
                <h5><i class="fas fa-check-circle mr-2"></i>Restauración completada exitosamente</h5>
                <p class="mb-0">
                    El Snapshot <strong>IEE-{{ $fechaSeleccionada }}.zip</strong> fue restaurado correctamente.
                    Se recomienda verificar el acceso al sistema y limpiar caché si es necesario.
                </p>
            </div>
        @else
            <div class="alert alert-danger">
                <h5><i class="fas fa-times-circle mr-2"></i>Restauración fallida</h5>
                <p class="mb-0">
                    Ocurrió un error durante la restauración de
                    <strong>IEE-{{ $fechaSeleccionada }}.zip</strong>.
                    Revisa el detalle a continuación y consulta
                    <code>storage/logs/restore.log</code> para más información.
                </p>
            </div>
        @endif

        @if ($outputComando)
            <div class="card card-outline card-{{ $exito ? 'success' : 'danger' }} mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-terminal mr-2"></i>Salida del proceso de restauración
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $exito ? 'success' : 'danger' }}">
                            {{ $exito ? 'SUCCESS' : 'FAILURE' }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <pre class="m-0 p-3" style="background:#1e1e1e;color:#d4d4d4;font-size:0.8rem;border-radius:0 0 4px 4px;overflow-x:auto;max-height:400px;">{{ $outputComando }}</pre>
                </div>
            </div>
        @endif

        <div class="mt-3">
            <button wire:click="resetear" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver al listado de respaldos
            </button>
        </div>

    @endif
</div>
