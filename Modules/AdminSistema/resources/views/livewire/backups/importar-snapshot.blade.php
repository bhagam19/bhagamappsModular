<div>
    {{-- ── Banda de contexto DR ──────────────────────────────────────────────── --}}
    <div class="callout callout-info">
        <h5><i class="fas fa-life-ring mr-1"></i>Recuperación desde Snapshot externo</h5>
        <p class="mb-0">
            Carga un Snapshot ZIP descargado desde Google Drive o almacenado localmente.
            El sistema lo validará, mostrará una vista previa y solicitará confirmación antes de
            restaurar. Solo disponible para el <strong>Administrador Principal</strong>.
        </p>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- ESTADO: subir (SNAP-003)                                               --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @if ($estado === 'subir')

        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-upload mr-2"></i>Cargar Snapshot ZIP
                </h3>
            </div>
            <div class="card-body">

                @if ($errorUpload)
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        <strong>Error de validación:</strong> {{ $errorUpload }}
                    </div>
                @endif

                @error('zipFile')
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                    </div>
                @enderror

                <div class="form-group">
                    <label for="snap-zip" class="font-weight-bold">
                        Archivo ZIP del Snapshot Institucional
                    </label>
                    <div class="custom-file">
                        <input
                            type="file"
                            class="custom-file-input {{ $errors->has('zipFile') ? 'is-invalid' : '' }}"
                            id="snap-zip"
                            wire:model="zipFile"
                            accept=".zip,application/zip,application/x-zip-compressed"
                        >
                        <label class="custom-file-label" for="snap-zip">
                            Seleccionar archivo .zip
                        </label>
                    </div>
                    <small class="form-text text-muted mt-1">
                        Solo archivos <code>.zip</code> · Límite del servidor:
                        <strong>{{ ini_get('upload_max_filesize') }}</strong>
                        · Solo Snapshots generados por IEE BhagamApps
                    </small>
                </div>

                {{-- Barra de progreso de upload --}}
                <div wire:loading wire:target="zipFile" class="mt-2">
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                             role="progressbar" style="width:100%"></div>
                    </div>
                    <small class="text-muted">Cargando archivo...</small>
                </div>

                {{-- Nombre del archivo seleccionado --}}
                @if ($zipFile && !$errors->has('zipFile'))
                    <div class="alert alert-light border mt-2 py-2">
                        <i class="fas fa-file-archive text-info mr-1"></i>
                        <strong>Archivo seleccionado:</strong>
                        {{ $zipFile->getClientOriginalName() }}
                        ({{ $this->formatSize($zipFile->getSize()) }})
                    </div>
                @endif

                <div class="mt-3">
                    <button
                        wire:click="cargarYValidar"
                        wire:loading.attr="disabled"
                        class="btn btn-info {{ !$zipFile ? 'disabled' : '' }}"
                        @if (!$zipFile) disabled @endif
                    >
                        <span wire:loading.remove wire:target="cargarYValidar">
                            <i class="fas fa-check-circle mr-1"></i> Cargar y validar
                        </span>
                        <span wire:loading wire:target="cargarYValidar">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Validando estructura...
                        </span>
                    </button>
                    <a href="{{ route('admin.backups.index') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-arrow-left mr-1"></i> Volver a Backups
                    </a>
                </div>

                {{-- Info de flujo --}}
                <div class="mt-4 pt-3 border-top">
                    <h6 class="text-muted mb-2">Flujo de recuperación desde Drive:</h6>
                    <ol class="text-muted" style="font-size:0.9rem;">
                        <li>Descargar el Snapshot ZIP desde Google Drive</li>
                        <li>Seleccionar y cargar el archivo aquí</li>
                        <li>Revisar la vista previa del contenido</li>
                        <li>Escribir <code>RESTAURAR</code> para confirmar</li>
                        <li>La plataforma queda restaurada al estado del Snapshot</li>
                    </ol>
                </div>
            </div>
        </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- ESTADO: vista-previa (SNAP-005)                                        --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @elseif ($estado === 'vista-previa')

        <div class="alert alert-success">
            <i class="fas fa-check-circle mr-1"></i>
            <strong>Snapshot válido.</strong>
            El archivo fue cargado y verificado correctamente.
        </div>

        <div class="row">
            {{-- Ficha del snapshot --}}
            <div class="col-lg-6">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-archive mr-2"></i>
                            Snapshot: <strong>{{ $nombreArchivo }}</strong>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th class="text-muted pl-3" style="width:42%">Fecha del Snapshot</th>
                                    <td>{{ $meta['fecha'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-3">Generado en</th>
                                    <td>{{ $meta['generado_en'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-3">Tamaño ZIP</th>
                                    <td>{{ $this->formatSize($tamanoBytes) }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-3">Entorno origen</th>
                                    <td>{{ $meta['entorno'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-3">Base de datos</th>
                                    <td>{{ $meta['db_database'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-3">Versión IEE</th>
                                    <td><strong>v{{ $meta['version_iee'] ?? '—' }}</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-3">Versión BhagamApps</th>
                                    <td>v{{ $meta['version_bhagamapps'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-3">Tablas exportadas</th>
                                    <td>{{ $meta['tablas_exportadas'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-3">Total registros</th>
                                    <td><strong>{{ number_format($meta['total_registros'] ?? 0) }}</strong></td>
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
                            $conteos    = $meta['conteos'] ?? [];
                            $destacados = [
                                'users'        => 'Usuarios',
                                'bienes'       => 'Bienes',
                                'dependencias' => 'Dependencias',
                                'categorias'   => 'Categorías',
                                'permissions'  => 'Permisos',
                                'roles'        => 'Roles',
                                'apps'         => 'Aplicaciones',
                            ];
                        @endphp
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                @foreach ($destacados as $key => $label)
                                    @if (isset($conteos[$key]))
                                    <tr>
                                        <th class="text-muted pl-3" style="width:55%">{{ $label }}</th>
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

        <div class="d-flex mt-1">
            <button wire:click="cancelar" class="btn btn-secondary mr-2">
                <i class="fas fa-times mr-1"></i> Cancelar
            </button>
            <button wire:click="irAConfirmar" class="btn btn-warning">
                <i class="fas fa-exclamation-triangle mr-1"></i> Continuar con la restauración
            </button>
        </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- ESTADO: confirmar (SNAP-006)                                           --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @elseif ($estado === 'confirmar')

        <div class="card card-outline card-danger">
            <div class="card-header bg-danger">
                <h3 class="card-title text-white">
                    <i class="fas fa-exclamation-circle mr-2"></i>Confirmación de restauración desde Snapshot externo
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-danger">
                    <strong><i class="fas fa-exclamation-triangle mr-1"></i>Esta acción sobreescribirá los datos actuales de la base de datos.</strong><br>
                    Snapshot a restaurar:
                    <strong class="text-monospace">{{ $nombreArchivo }}</strong><br>
                    Contenido: <strong>{{ number_format($meta['total_registros'] ?? 0) }}</strong> registros
                    · {{ $this->formatSize($tamanoBytes) }}
                    · IEE v{{ $meta['version_iee'] ?? '—' }}
                </div>

                <p class="mb-2">
                    Para confirmar, escribe exactamente
                    <code class="text-danger font-weight-bold">RESTAURAR</code>:
                </p>

                <div class="form-group mb-4">
                    <input
                        wire:model.live="confirmacion"
                        type="text"
                        class="form-control form-control-lg text-center font-weight-bold
                            {{ $confirmacion === 'RESTAURAR' ? 'is-valid border-success' : ($confirmacion !== '' ? 'is-invalid border-danger' : '') }}"
                        placeholder="RESTAURAR"
                        autocomplete="off"
                        spellcheck="false"
                        style="letter-spacing:0.2em;font-size:1.4rem;max-width:320px;"
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
    {{-- ESTADO: resultado                                                      --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @elseif ($estado === 'resultado')

        @if ($exito)
            <div class="alert alert-success">
                <h5><i class="fas fa-check-circle mr-2"></i>Restauración completada exitosamente</h5>
                <p class="mb-0">
                    El Snapshot <strong>{{ $nombreArchivo }}</strong> fue restaurado correctamente.
                    La plataforma ha quedado en el estado del Snapshot importado.
                </p>
            </div>
        @else
            <div class="alert alert-danger">
                <h5><i class="fas fa-times-circle mr-2"></i>Restauración fallida</h5>
                <p class="mb-0">
                    Ocurrió un error durante la restauración.
                    Revisa el detalle a continuación y consulta
                    <code>storage/logs/restore.log</code>.
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
                <i class="fas fa-arrow-left mr-1"></i> Importar otro Snapshot
            </button>
            <a href="{{ route('admin.backups.index') }}" class="btn btn-outline-secondary ml-2">
                <i class="fas fa-database mr-1"></i> Ir a Backups
            </a>
        </div>

    @endif
</div>

{{-- Actualiza el label del custom-file-input con el nombre del archivo --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.body.addEventListener('change', function (e) {
            if (e.target && e.target.classList.contains('custom-file-input')) {
                const label = e.target.nextElementSibling;
                if (label && e.target.files.length > 0) {
                    label.textContent = e.target.files[0].name;
                }
            }
        });
    });
</script>
