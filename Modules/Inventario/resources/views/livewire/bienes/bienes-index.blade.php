<div>
    @php $user = auth()->user(); @endphp

    @php
        $ocultarUsuario = !$user->hasRole('Administrador') && !$user->hasRole('Rector');
        $columnasOcultas = ['usuario_id'];

        // Filtra columnas visibles
        $visibleColumns = $ocultarUsuario
            ? array_values(array_diff($visibleColumns, $columnasOcultas))
            : $visibleColumns;

        // Filtra columnas disponibles (para móvil)
        $availableColumns = $ocultarUsuario
            ? collect($availableColumns)->except($columnasOcultas)->all()
            : $availableColumns;

        $availableColumns = array_filter(
            $availableColumns,
            function ($key) {
                return !empty($key);
            },
            ARRAY_FILTER_USE_KEY,
        );
    @endphp

    <style>
        .table-hover tbody tr:hover {
            background-color: rgb(135, 180, 174) !important;
            transition: background-color 0.1s ease-in-out;
        }

        thead th {
            position: sticky;
            top: 0;
            background-color: white;
            /* o el color de fondo de tu encabezado */
            z-index: 10;
            /* asegúrate de que quede por encima del contenido */
        }

        .sticky-col {
            position: sticky;
            background-color: white;
        }

        .col-separator {
            border-right: 1px solid #dee2e6 !important;
        }

        .col-separator:last-child {
            border-right: none !important;
        }

        .badge-custom {
            background-color: rgb(18, 48, 78);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            display: inline-block;
            font-size: 0.875rem;
        }

        .bg-regular {
            background-color: rgb(233, 171, 129) !important;
            color: rgb(0, 0, 0) !important;
        }

        .bg-malo {
            background-color: rgb(223, 131, 131) !important;
            color: rgb(0, 0, 0) !important;
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

    {{-- Mostrar filtros (Movil) --}}
    <div class="d-block d-md-none mb-1">
        <button class="btn btn-outline-secondary btn-sm btn-block" type="button" data-toggle="collapse"
            data-target="#filtrosMobile" aria-expanded="false" aria-controls="filtrosMobile">
            <i class="fas fa-filter"></i> Mostrar filtros y búsqueda
        </button>
    </div>

    {{-- Filtros colapsable (Movil) --}}
    <div class="collapse d-md-none" id="filtrosMobile">
        <div class="card shadow-sm mb-1">
            <div class="card-body">

                {{-- Botón solo para rector --}}
                @if ($user->hasRole('Rector'))
                    <div class="mb-3">
                        <button wire:click="$toggle('verTodos')" class="btn btn-outline-primary">
                            <i class="fas fa-users"></i>
                            {{ $verTodos ? 'Ver solo mis bienes' : 'Ver todos los bienes' }}
                        </button>
                    </div>
                @endif

                {{-- Barra superior: Buscar --}}
                <div class="mb-1">
                    <input type="text" wire:model.lazy="filtroNombre" class="form-control"
                        placeholder="🔍 Buscar por nombre...">
                </div>

                {{-- Filtros adicionales --}}
                <div class="d-flex flex-wrap gap-1 align-items-end mb-1">
                    <div class="flex-fill" style="min-width: 200px;">
                        <select wire:model.lazy="filtroUsuario" class="form-control">
                            <option value="">Filtrar por usuario</option>
                            @foreach ($usuarios as $usuario)
                                <option value="{{ $usuario->id }}">{{ $usuario->nombres }} {{ $usuario->apellidos }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-fill" style="min-width: 200px;">
                        <select wire:model.lazy="filtroCategoria" class="form-control">
                            <option value="">Filtrar por categoría</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-fill" style="min-width: 200px;">
                        <select wire:model.lazy="filtroDependencia" class="form-control">
                            <option value="">Filtrar por dependencia</option>
                            @foreach ($dependencias as $dep)
                                <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-fill" style="min-width: 200px;">
                        <select wire:model.lazy="filtroEstado" class="form-control">
                            <option value="">Filtrar por estado</option>
                            @foreach ($estados as $estado)
                                <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-1">
                        <button wire:click="limpiarFiltros" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-eraser"></i> Limpiar filtros
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Botón para mostrar formulario Agregar Bien (Móvil) --}}
    @if (auth()->user()->hasPermission('crear-bienes'))
        <div class="d-block d-md-none mb-1">
            <button class="btn btn-primary btn-sm btn-block" type="button" data-toggle="collapse"
                data-target="#formCreateBien" aria-expanded="false" aria-controls="formCreateBien">
                Agregar Bien
            </button>
        </div>
    @endif

    {{-- Botón para mostrar formulario Agregar Bien (Escritorio) --}}
    @php
        $totalCambios = \Modules\Inventario\Entities\BienAprobacionPendiente::where('estado', 'pendiente')->count();
        $hayCambios = $totalCambios > 0;
        $btnClass = $hayCambios ? 'btn-danger' : 'btn-success';
        $mensaje = $hayCambios
            ? "Tiene <span class='badge bg-warning text-dark ms-1'>{$totalCambios}</span> modificaciones pendientes"
            : 'No hay modificaciones pendientes';
    @endphp

    @if (auth()->user()->hasPermission('crear-bienes'))
        <div
            class="d-none d-md-flex flex-column flex-md-row justify-content-between align-items-center mb-1 gap-1 flex-wrap">

            {{-- Botón para mostrar formulario Agregar Bien (Escritorio) --}}
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" type="button" data-toggle="collapse"
                data-target="#formCreateBien" aria-expanded="false" aria-controls="formCreateBien">
                <i class="fas fa-plus"></i> Agregar Bien
            </button>

            {{-- Contador registro y bienes (Escritorio) --}}
            <div class="d-flex align-items-center px-1 py-1 rounded bg-light border text-muted small"
                style="gap: 4rem; justify-content: space-between; min-width: 320px;">
                <div class="d-flex align-items-center" style="gap: 1rem;">
                    <span style="margin-left: 1rem;">{{ $bienes->total() }} registros</span>
                </div>
                <div class="d-flex align-items-center" style="gap: 1rem;">
                    <span style="margin-left: 1rem;">{{ $this->cantidadTotalFiltrada }} total bienes</span>
                </div>
            </div>

            {{-- Botón ver-aprobaciones-pendientes-bienes (Escritorio) --}}
            @if (auth()->user()->hasPermission('ver-aprobaciones-pendientes-bienes'))
                <a href="{{ route('inventario.bap') }}"
                    class="btn {{ $btnClass }} btn-sm d-flex align-items-center gap-1" role="button"
                    aria-label="Ver modificaciones pendientes">
                    <i class="fas fa-bell"></i>
                    <span>{!! $mensaje !!}</span>
                </a>
            @endif
        </div>
        <div>
            @if (auth()->user()->hasPermission('ver-aprobaciones-pendientes-bienes'))
                <a href="{{ route('inventario.bap') }}"
                    class="btn {{ $btnClass }} btn-sm d-flex d-sm-none align-items-center justify-content-center w-100 my-2"
                    role="button" aria-label="Ver modificaciones pendientes (móvil)">
                    <i class="fas fa-bell"></i>
                    @if ($hayCambios)
                        <span>{!! $mensaje !!}</span>
                    @endif
                </a>
            @endif
        </div>

    @endif

    {{-- Formulario Agregar bien (Móvil y Escritorio) --}}
    @if (auth()->user()->hasPermission('crear-bienes'))
        <div class="collapse" id="formCreateBien">
            <form wire:submit.prevent="store" class="form-row align-items-end mb-1" novalidate>
                @php
                    $fields = [
                        ['model' => 'nombre', 'placeholder' => 'Nombre del bien', 'type' => 'text'],
                        ['model' => 'detalle', 'placeholder' => 'Detalle', 'type' => 'text'],
                        ['model' => 'cantidad', 'placeholder' => 'Cantidad', 'type' => 'number'],
                        ['model' => 'serie', 'placeholder' => 'Serie', 'type' => 'text'],
                        ['model' => 'origen', 'placeholder' => 'Origen', 'type' => 'text'],
                        ['model' => 'fechaAdquisicion', 'placeholder' => 'Fecha adquisición', 'type' => 'date'],
                        ['model' => 'precio', 'placeholder' => 'Precio', 'type' => 'number'],
                        ['model' => 'observaciones', 'placeholder' => 'Observaciones', 'type' => 'text'],
                    ];

                    $selectFields = [
                        ['model' => 'categoria_id', 'label' => 'Categoría', 'options' => $categorias ?? []],
                        ['model' => 'dependencia_id', 'label' => 'Dependencia', 'options' => $dependencias ?? []],
                        [
                            'model' => 'almacenamiento_id',
                            'label' => 'Almacenamiento',
                            'options' => $almacenamientos ?? [],
                        ],
                        ['model' => 'estado_id', 'label' => 'Estado', 'options' => $estados ?? []],
                        ['model' => 'mantenimiento_id', 'label' => 'Mantenimiento', 'options' => $mantenimientos ?? []],
                    ];
                @endphp

                @foreach ($fields as $field)
                    <div class="form-group col-md-2">
                        <input type="{{ $field['type'] }}" wire:model="{{ $field['model'] }}"
                            placeholder="{{ $field['placeholder'] }}"
                            class="form-control form-control-sm @error($field['model']) is-invalid @enderror">
                        @error($field['model'])
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach

                @foreach ($selectFields as $field)
                    <div class="form-group col-md-2">
                        <select wire:model="{{ $field['model'] }}"
                            class="form-control form-control-sm @error($field['model']) is-invalid @enderror">
                            <option value="">{{ $field['label'] }}</option>
                            @foreach ($field['options'] as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->nombre ?? ($item->descripcion ?? ($item->nombre_completo ?? 'Sin nombre')) }}
                                </option>
                            @endforeach
                        </select>
                        @error($field['model'])
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach

                <div class="form-group col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Paginación, Dependencias y Cantidades --}}
    <div class="mt-1 d-flex justify-content-between flex-wrap">

        {{-- Izquierda: Dependencias y Cantidades --}}
        <div class="d-flex flex-column" style="max-width: 400px; min-width: 320px;">

            {{-- Dependencias --}}
            @if (!$user->hasRole('Administrador') && !$user->hasRole('Rector') && isset($dependencias) && $dependencias->count())
                <div class="bg-light border rounded px-3 py-2 text-left shadow-sm mb-2" style="font-size: 1rem;">
                    <div class="font-weight-bold mb-2">Dependencias a tu cargo:</div>
                    @foreach ($dependencias as $dep)
                        <div class="badge badge-custom d-block mb-1">{{ $dep->nombre }}</div>
                    @endforeach
                </div>

                {{-- Cantidades --}}
                <div class="d-flex align-items-center px-3 py-2 rounded bg-light text-muted small"
                    style="gap: 4rem; justify-content: flex-start;">
                    <div>{{ $bienes->total() }} registros</div>
                    <div>{{ $this->cantidadTotalFiltrada }} total bienes</div>
                </div>
            @endif

        </div>

        {{-- Derecha: Selector y Paginación --}}
        <div class="table-responsive d-flex flex-column align-items-start">

            {{-- Selector --}}
            <div class="d-flex align-items-center mb-2">
                <label class="mr-2 mb-0">Mostrar</label>
                <select wire:model.lazy="perPage" class="form-control form-control-sm w-auto">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="ml-2">registros</span>
            </div>

            {{-- Paginación con scroll horizontal --}}
            <div class="w-100 overflow-auto">
                <div class="d-inline-block">
                    {{ $bienes->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>

    </div>

    {{-- Tabla para escritorio --}}
    <div class="table-responsive d-none d-md-block" style="max-height: 600px; overflow-y: auto;" wire:poll.30s>
        <table class="table table-striped table-sm table-hover w-100 mb-0">
            <thead>
                {{-- Fila de filtros --}}
                <tr>
                    {{-- Celda con badge "Filtros" --}}
                    <th style="position: sticky; top: 0; left: 0; background: white; z-index: 11; ">
                        <div class="d-flex flex-column align-items-stretch gap-2">
                            <span class="badge bg-primary text-center">Filtros</span>

                            <button wire:click="limpiarFiltros" class="btn btn-outline-secondary btn-sm w-100"
                                title="Limpiar filtros">
                                <i class="fas fa-eraser me-1"></i>
                            </button>
                        </div>
                    </th>

                    {{-- Filtro por nombre --}}
                    <th style="position: sticky; top: 0; background: white; min-width: 250px;">
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" wire:model.lazy="filtroNombre"
                                class="form-control form-control-sm flex-grow-1" placeholder="Buscar por nombre" />

                            <button wire:click="filtroNombre" class="btn btn-sm btn-primary" title="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </th>

                    {{-- Resto de filtros basados en columnas --}}
                    @foreach ($visibleColumns as $index => $column)
                        @php
                            $isSticky = $index < 1;
                            $left = 30 + $index * 150;
                            $styles = 'position: sticky; top: 0; background: white;';
                            if ($isSticky) {
                                $styles .= " left: {$left}px; z-index: 11;";
                            }
                        @endphp
                        <th style="{{ $styles }}">
                            @switch($column)
                                @case('usuario_id')
                                    <select wire:model.lazy="filtroUsuario" class="form-control form-control-sm">
                                        <option value="">Todos</option>
                                        @foreach ($usuarios as $usuario)
                                            <option value="{{ $usuario->id }}">
                                                {{ $usuario->nombres }} {{ $usuario->apellidos }}</option>
                                        @endforeach
                                    </select>
                                @break

                                @case('categoria_id')
                                    <select wire:model.lazy="filtroCategoria" class="form-control form-control-sm">
                                        <option value="">Todas</option>
                                        @foreach ($categorias as $categoria)
                                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                        @endforeach
                                    </select>
                                @break

                                @case('dependencia_id')
                                    <select wire:model.lazy="filtroDependencia" class="form-control form-control-sm">
                                        <option value="">Todas</option>
                                        @foreach ($dependencias as $dep)
                                            <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                                        @endforeach
                                    </select>
                                @break

                                @case('estado_id')
                                    <select wire:model.lazy="filtroEstado" class="form-control form-control-sm">
                                        <option value="">Todos</option>
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                                        @endforeach
                                    </select>
                                @break

                                @default
                                    {{-- Celda vacía si no aplica filtro --}}
                                    &nbsp;
                            @endswitch
                        </th>
                    @endforeach

                </tr>

                {{-- Fila de encabezados --}}
                <tr class="thead-dark">
                    <th wire:click="sortBy('id')"
                        style="cursor: pointer; position: sticky; top: 60px; left: 0; background-color: rgb(18, 48, 78); z-index: 11;">
                        ID
                        @if ($sortField === 'id')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th wire:click="sortBy('nombre')"
                        style="cursor: pointer; position: sticky; top: 60px; background-color: rgb(18, 48, 78);">
                        Nombre
                        @if ($sortField === 'nombre')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>
                    @foreach ($visibleColumns as $index => $column)
                        @if ($column === 'nombre')
                            @continue
                        @endif
                        @php
                            $left = 30 + $index * 150;
                            $isSticky = $index < 1;
                            $styles =
                                'cursor: pointer; position: sticky; top: 60px; background-color: rgb(18, 48, 78);';
                            if ($isSticky) {
                                $styles .= " left: {$left}px; z-index: 12;";
                            }
                        @endphp
                        <th wire:click="sortBy('{{ $column }}')" style="{{ $styles }}">
                            {{ ucfirst(str_replace('_', ' ', preg_replace('/_id$/', '', $column))) }}
                            @if ($sortField === $column)
                                {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                            @endif
                        </th>
                    @endforeach

                    <th style="position: sticky; top: 60px; background-color: #343a40; z-index: 9;">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($bienes as $bien)
                    @php
                        $estadoNombre = strtolower($bien->estado->nombre ?? '');
                        $rowClass = '';
                        if ($estadoNombre === 'regular') {
                            $rowClass = 'bg-regular text-dark'; // Naranja
                        } elseif ($estadoNombre === 'malo') {
                            $rowClass = 'bg-malo bg-opacity-25 text-danger'; // Rosado/rojo claro
                        }
                    @endphp

                    <tr class="hover:bg-blue-100 transition-colors {{ $rowClass }}">
                        {{-- Columna 1 sticky --}}
                        <td style="position: sticky; left: 0; background-color: white; z-index: 5;">
                            {{ $bien->id }}
                        </td>

                        {{-- Columna Nombre (fija) --}}
                        <td>
                            @if (auth()->user()?->hasPermission('editar-bienes'))
                                @livewire(
                                    'bienes.editar-campo-bien',
                                    [
                                        'bienId' => $bien->id,
                                        'campo' => 'nombre',
                                    ],
                                    key("bien-{$bien->id}-nombre")
                                )
                            @else
                                {{ $bien->nombre }}
                            @endif
                        </td>

                        {{-- Columnas dinámicas --}}
                        @foreach ($visibleColumns as $index => $column)
                            @if ($column === 'nombre')
                                @continue
                            @endif
                            @php
                                $left = 30 + $index * 150;
                                $isSticky = $index < 1;
                            @endphp
                            <td class="col-separator"
                                style="
                                white-space: nowrap;
                                min-width: 150px;
                                @if ($isSticky) position: sticky;
                                    left: {{ $left }}px;
                                    background-color: white;
                                    z-index: 4; @endif
                            ">
                                @if ($column === 'detalle')
                                    @if (auth()->user()?->hasPermission('editar-bienes'))
                                        @livewire('bienes.editar-detalle-bien', ['bienId' => $bien->id], key('editar-detalle-bien-escritorio-' . $bien->id))
                                    @else
                                        @if ($bien->detalle)
                                            <small>
                                                @foreach (['car_especial', 'marca', 'color', 'tamano', 'material', 'otra'] as $attr)
                                                    @if (!empty($bien->detalle->$attr))
                                                        {{ $bien->detalle->$attr }} |
                                                    @endif
                                                @endforeach
                                            </small>
                                        @else
                                            —
                                        @endif
                                    @endif
                                @else
                                    @if ($column === 'ubicacion_id')
                                        {{ $bien->dependencia->ubicacion->nombre ?? 'Sin ubicación' }}
                                    @elseif ($column === 'usuario_id')
                                        <span class="px-2 small text-muted editable-desktop d-none d-sm-inline">
                                            {{ $bien->dependencia->usuario->nombre_completo ?? 'Sin responsable' }}
                                        </span>
                                    @elseif (auth()->user()?->hasPermission('editar-bienes'))
                                        @livewire(
                                            'bienes.editar-campo-bien',
                                            [
                                                'bienId' => $bien->id,
                                                'campo' => $column,
                                            ],
                                            key("bien-{$bien->id}-{$column}")
                                        )
                                    @else
                                        {{ $bien->getDisplayValue($column) }}
                                    @endif
                                @endif
                            </td>
                        @endforeach

                        <td>
                            @if (auth()->user()?->hasPermission('eliminar-bienes'))
                                <button wire:click="delete({{ $bien->id }})" class="btn btn-sm btn-danger"
                                    onclick="confirm('¿Confirma eliminar?') || event.stopImmediatePropagation()"
                                    aria-label="Eliminar bien {{ $bien->id }}">
                                    Eliminar
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($visibleColumns) + 2 }}" class="text-center text-muted">
                            No hay bienes registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Detalles del Bien para escritorio --}}
    <div class="modal fade" id="modalDetallesBien" tabindex="-1" aria-labelledby="modalDetallesBienLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetallesBienLabel">Editar detalles del bien</h5>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cerrarModalDetalles()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    @livewire('bienes.editar-detalle-bien-modal')
                </div>
            </div>
        </div>
    </div>

    <script>
        function abrirModalDetalles(bienId) {
            // Emitir al componente Livewire para cargar el detalle
            Livewire.dispatch('cargarDetalle', {
                bienId: bienId
            });
        }

        window.addEventListener('detalleBienCargado', () => {
            $('#modalDetallesBien').modal('show');
        });

        function cerrarModalDetalles() {
            $('#modalDetallesBien').modal('hide');
        }

        window.addEventListener('cerrar-modal-detalles', () => {
            $('#modalDetallesBien').modal('hide');
        });
    </script>

    {{-- Vista móvil: acordeón con Alpine.js --}}
    <div class="d-block d-md-none" x-data="{ openId: null }" wire:poll.30s>
        <div id="accordionMobileBienes">
            @forelse($bienes as $bien)
                @php

                    $estadoNombre = strtolower($bien->estado->nombre ?? '');
                    $cardClass = match ($estadoNombre) {
                        'regular' => 'border-warning bg-regular',
                        'malo' => 'border-danger bg-malo',
                        default => '',
                    };

                    $badgeClass = match (true) {
                        $bien->cantidad === 0, $bien->cantidad < 5 => 'badge-primary',
                        default => 'badge-primary',
                    };

                    $isOpen = "openId === {$bien->id}";
                    $toggleOpen = "{$isOpen} ? openId = null : openId = {$bien->id}";
                @endphp

                <div class="card mb-2 {{ $cardClass }}">
                    {{-- Encabezado --}}
                    <div class="card-header d-flex align-items-center justify-content-between p-2 w-100"
                        @click="{{ $toggleOpen }}" @keydown.enter.prevent="{{ $toggleOpen }}"
                        @keydown.space.prevent="{{ $toggleOpen }}" tabindex="0" role="button">

                        {{-- Izquierda: nombre + icono de cambios + icono de estado --}}
                        <div class="d-flex align-items-center flex-grow-1 flex-wrap">
                            <span class="text-truncate">
                                {{ $bien->id }}. {{ $bien->nombre }}

                                @if ($bien->tieneCambiosPendientes())
                                    <i class="fas fa-hourglass-half text-info ms-1"
                                        title="Tienes cambios pendientes en este bien"></i>
                                @endif
                            </span>

                            @if ($estadoNombre === 'malo')
                                <i class="fas fa-exclamation-circle text-warning ms-2" title="Estado: Malo"></i>
                            @elseif($estadoNombre === 'regular')
                                <i class="fas fa-exclamation-triangle text-white ms-2" title="Estado: Regular"></i>
                            @endif
                        </div>

                        {{-- Derecha: cantidad + botón eliminar --}}
                        <div class="d-flex align-items-center flex-shrink-0 ms-auto">
                            <span class="me-2 badge {{ $badgeClass }} badge-pill">
                                <i class="fas fa-cubes me-1"></i> {{ $bien->cantidad }}
                            </span>

                            @if (auth()->user()?->hasPermission('eliminar-bienes'))
                                <button wire:click.stop="$dispatch('confirmDelete', {{ $bien->id }})"
                                    class="btn btn-sm btn-danger" aria-label="Eliminar bien {{ $bien->nombre }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Cuerpo del acordeón --}}
                    <div class="card-body p-2" :class="{ 'd-none': openId !== {{ $bien->id }} }">
                        @foreach ($availableColumns as $key => $label)
                            @continue(!in_array($key, $visibleColumns) || empty($key))

                            <div class="mb-0">
                                @if ($key === 'detalle')
                                    @if (auth()->user()?->hasPermission('editar-bienes'))
                                        @livewire('bienes.editar-detalle-bien', ['bienId' => $bien->id], key('editar-detalle-bien-' . $bien->id))
                                    @else
                                        @if ($bien->detalle)
                                            <small class="d-block mt-1 text-muted">
                                                @foreach (['car_especial', 'marca', 'color', 'tamano', 'material', 'otra'] as $attr)
                                                    @if (!empty($bien->detalle->$attr))
                                                        {{ $bien->detalle->$attr }} |
                                                    @endif
                                                @endforeach
                                            </small>
                                        @else
                                            <span class="text-muted fst-italic">Sin detalles</span>
                                        @endif
                                    @endif
                                @elseif ($key === 'usuario_id')
                                    <div
                                        class="d-flex align-items-center justify-content-between flex-nowrap w-100 py-0 overflow-hidden">
                                        <div class="text-truncate me-2" style="white-space: nowrap;">
                                            <span
                                                class="badge badge-light border border-primary text-muted small px-2 py-1 d-sm-none">
                                                {{ $label }}:
                                            </span>
                                            <small class="mt-1 text-muted">
                                                {{ $bien->dependencia->usuario->nombre_completo ?? 'Sin responsable' }}
                                            </small>
                                        </div>
                                    </div>
                                @else
                                    @if (auth()->user()?->hasPermission('editar-bienes'))
                                        @livewire(
                                            'bienes.editar-campo-bien',
                                            [
                                                'bienId' => $bien->id,
                                                'campo' => $key,
                                                'camposPendientes' => $camposPendientes,
                                            ],
                                            key("mobile-bien-{$bien->id}-{$key}")
                                        )
                                    @else
                                        {{ $bien->getDisplayValue($key) ?? '—' }}
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-center text-muted">No hay bienes registrados.</p>
            @endforelse
        </div>
    </div>


    {{-- Paginación --}}
    <div class="mt-3">
        <div class="d-md-block d-flex overflow-auto">
            <div class="mx-auto">
                {{ $bienes->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
