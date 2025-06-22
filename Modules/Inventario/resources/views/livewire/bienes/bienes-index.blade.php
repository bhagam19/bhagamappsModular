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
            z-index: 10;
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

        .table-sm td,
        .table-sm th {
            padding: 0.05rem 0.3rem !important;
        }

        .table-sm input.form-control,
        .table-sm select.form-control {
            padding: 0.05rem 0.3rem;
            font-size: 0.8rem;
            height: calc(1.2em + 0.3rem + 2px);
            line-height: 0.5;
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

    {{-- Mensaje de sesión con AlpineJS --}}
    <div x-data="{ show: false, mensaje: '', tipo: 'success' }" x-show="show" x-transition class="position-fixed top-0 start-50 translate-middle-x mt-1"
        style="z-index: 9999; width: auto; max-width: 90%;"
        @mostrar-mensaje.window="
        mensaje = $event.detail.mensaje; 
        tipo = $event.detail.tipo ?? 'success'; 
        show = true; 
        setTimeout(() => show = false, 10000);
     ">

        <div :class="{
            'alert alert-success alert-dismissible fade show': tipo === 'success',
            'alert alert-danger alert-dismissible fade show': tipo === 'error',
            'alert alert-warning alert-dismissible fade show': tipo === 'warning'
        }"
            role="alert">

            <span x-text="mensaje"></span>

            <button type="button" class="btn-close" @click="show = false" aria-label="Cerrar"></button>
        </div>
    </div>

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

    {{-- Contador modificaciones y eliminaciones pendientes --}}
    @php
        use Modules\Inventario\Entities\HistorialModificacionBien;
        use Modules\Inventario\Entities\HistorialEliminacionBien;

        $totalModificaciones = HistorialModificacionBien::where('estado', 'pendiente')->count();
        $totalEliminaciones = HistorialEliminacionBien::where('estado', 'pendiente')->count();

        $hayModificaciones = $totalModificaciones > 0;
        $hayEliminaciones = $totalEliminaciones > 0;

        $btnClassModificaciones = $hayModificaciones ? 'btn-danger' : 'btn-success';
        $btnClassEliminaciones = $hayEliminaciones ? 'btn-danger' : 'btn-success';

        $mensajeModificaciones = $hayModificaciones
            ? "Modificaciones pendientes: <span class='badge bg-warning text-dark ms-1'>{$totalModificaciones}</span>"
            : 'No hay modificaciones pendientes';

        $mensajeEliminaciones = $hayEliminaciones
            ? "Eliminaciones pendientes: <span class='badge bg-warning text-white ms-1'>{$totalEliminaciones}</span>"
            : 'No hay eliminaciones pendientes';
    @endphp

    {{-- Botón para mostrar formulario Agregar Bien (Escritorio) --}}
    {{-- Contador registro y bienes (Escritorio) --}}
    {{-- Botón gestionar-historial-modificaciones-bienes (Escritorio) --}}
    {{-- Botón gestionar-historial-eliminaciones-bienes (Escritorio) --}}
    @if (auth()->user()->hasPermission('crear-bienes'))
        <div
            class="d-none d-md-flex flex-column flex-md-row justify-content-between align-items-center mb-1 gap-1 flex-wrap">

            {{-- Botón para mostrar formulario Agregar Bien (Escritorio) --}}
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" type="button"
                wire:click="toggleFormulario">
                <i class="fas fa-plus pr-1"></i> {{ $mostrarFormulario ? 'Ocultar' : 'Agregar Bien' }}
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

            {{-- Botón gestionar-historial-modificaciones-bienes (Escritorio) --}}
            @if (auth()->user()->hasPermission('gestionar-historial-modificaciones-bienes'))
                <a href="{{ route('inventario.hmb') }}"
                    class="btn {{ $btnClassModificaciones }} btn-sm d-flex align-items-center gap-1" role="button"
                    aria-label="Ver historial de modificaciones">
                    <i class="fas fa-bell mr-1"></i>
                    <span>{!! $mensajeModificaciones !!}</span>
                </a>
            @endif

            {{-- Botón gestionar-historial-eliminaciones-bienes (Escritorio) --}}
            @if (auth()->user()->hasPermission('gestionar-historial-eliminaciones-bienes'))
                <a href="{{ route('inventario.heb') }}"
                    class="btn {{ $btnClassEliminaciones }} btn-sm d-flex align-items-center gap-1" role="button"
                    aria-label="Ver historial de eliminaciones">
                    <i class="fas fa-bell mr-1"></i>
                    <span>{!! $mensajeEliminaciones !!}</span>
                </a>
            @endif
        </div>

        {{-- Botón gestionar-historial-modificaciones-bienes (Movil) --}}
        <div>
            @if (auth()->user()->hasPermission('gestionar-historial-modificaciones-bienes'))
                <a href="{{ route('inventario.hmb') }}"
                    class="btn {{ $btnClassModificaciones }} btn-sm d-flex d-sm-none align-items-center justify-content-center w-100 my-2"
                    role="button" aria-label="Ver historial de modificaciones (móvil)">
                    <i class="fas fa-bell mr-1"></i>
                    <span>{!! $mensajeModificaciones !!}</span>
                </a>
            @endif
        </div>
        {{-- Botón gestionar-historial-eliminaciones-bienes (Movil) --}}
        <div>
            @if (auth()->user()->hasPermission('gestionar-historial-eliminaciones-bienes'))
                <a href="{{ route('inventario.heb') }}"
                    class="btn {{ $btnClassEliminaciones }} btn-sm d-flex d-sm-none align-items-center justify-content-center w-100 my-2"
                    role="button" aria-label="Ver historial de eliminaciones (móvil)">
                    <i class="fas fa-bell mr-1"></i>
                    <span>{!! $mensajeEliminaciones !!}</span>
                </a>
            @endif
        </div>

    @endif

    {{-- Botón para mostrar formulario Agregar Bien (Móvil) --}}
    @if (auth()->user()->hasPermission('crear-bienes'))
        <div class="d-block d-md-none mb-1">
            <button class="btn btn-primary btn-sm btn-block" type="button" wire:click="toggleFormulario">
                {{ $mostrarFormulario ? 'Ocultar' : 'Agregar Bien' }}
            </button>
        </div>
    @endif

    {{-- Formulario Agregar bien (Móvil y Escritorio) --}}
    @if (auth()->user()->hasPermission('crear-bienes'))
        <div>
            @if ($mostrarFormulario)
                <form wire:submit.prevent="store" class="form-row align-items-end mb-1 rounded"
                    style="background-color: #d7e7ff;" novalidate>
                    @php
                        $fields = [
                            ['model' => 'nombre', 'placeholder' => 'Nombre del bien', 'type' => 'text'],
                            ['model' => 'cantidad', 'placeholder' => 'Cantidad', 'type' => 'number'],
                            ['model' => 'serie', 'placeholder' => 'N/Serial (Si Aplica)', 'type' => 'text'],
                            ['model' => 'origen', 'placeholder' => 'Origen', 'type' => 'text'],
                            ['model' => 'fecha_adquisicion', 'placeholder' => 'Fecha adquisición', 'type' => 'date'],
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
                            [
                                'model' => 'mantenimiento_id',
                                'label' => 'Mantenimiento',
                                'options' => $mantenimientos ?? [],
                            ],
                        ];

                        $detalleFields = [
                            ['model' => 'car_especial', 'placeholder' => 'Características especiales'],
                            ['model' => 'marca', 'placeholder' => 'Marca'],
                            ['model' => 'color', 'placeholder' => 'Color'],
                            ['model' => 'tamano', 'placeholder' => 'Tamaño'],
                            ['model' => 'material', 'placeholder' => 'Material'],
                            ['model' => 'otra', 'placeholder' => 'Otra característica'],
                        ];
                    @endphp

                    {{-- Campo especial: Nombre del bien con Select + “Otro” --}}
                    <div class="form-group col-md-2 mb-1">
                        <label class="small text-muted">Nombre del bien</label>
                        <select wire:model.lazy="nombreSeleccionado"
                            class="form-control form-control-sm @error('nombreSeleccionado') is-invalid @enderror">
                            <option value="">Nombre del Bien</option>
                            <option value="otro">Otro (No está en lista)</option>
                            @foreach ($listaNombresBienes as $nombre)
                                <option value="{{ $nombre }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                        @error('nombreSeleccionado')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Input para nuevo nombre, visible solo si se selecciona “otro” --}}
                    @if ($nombreSeleccionado === 'otro')
                        <div class="form-group col-md-2 mb-1">
                            <label class="small text-muted bg-warning rounded pl-1 pr-1">Nuevo nombre</label>
                            <input type="text" wire:model="nombreNuevo" placeholder="Ingrese nuevo nombre"
                                class="form-control bg-warning form-control-sm @error('nombreNuevo') is-invalid @enderror">
                            @error('nombreNuevo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    {{-- Campo especial: Origen del bien con Select + “Otro” --}}
                    <div class="form-group col-md-2 mb-1">
                        <label class="small text-muted">Origen del bien</label>
                        <select wire:model.lazy="origenSeleccionado"
                            class="form-control form-control-sm @error('origenSeleccionado') is-invalid @enderror">
                            <option value="">Origen del Bien</option>
                            <option value="otro">Otro (No está en lista)</option>
                            @foreach ($listaOrigenesBienes as $origen)
                                <option value="{{ $origen }}">{{ $origen }}</option>
                            @endforeach
                        </select>
                        @error('origenSeleccionado')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Input para nuevo origen, visible solo si se selecciona “otro” --}}
                    @if ($origenSeleccionado === 'otro')
                        <div class="form-group col-md-2 mb-1">
                            <label class="small text-muted bg-warning rounded pl-1 pr-1">Nuevo origen</label>
                            <input type="text" wire:model="origenNuevo" placeholder="Ingrese nuevo origen"
                                class="form-control bg-warning form-control-sm @error('origenNuevo') is-invalid @enderror">
                            @error('origenNuevo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    {{-- Campos individuales --}}
                    @foreach ($fields as $field)
                        @if ($field['model'] === 'nombre' || $field['model'] === 'origen')
                            @continue
                        @endif
                        <div class="form-group col-md-2 mb-1">
                            <input type="{{ $field['type'] }}" wire:model="{{ $field['model'] }}"
                                placeholder="{{ $field['placeholder'] }}"
                                class="form-control form-control-sm @error($field['model']) is-invalid @enderror">
                            @error($field['model'])
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach

                    {{-- Campos select --}}
                    @foreach ($selectFields as $field)
                        <div class="form-group col-md-2 mb-1">
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

                    {{-- Agrupación visual Detalles del Bien --}}
                    <div class="col-12 mb-1">
                        <small class="text-muted font-weight-bold">Detalles del Bien</small>
                        <div class="form-row">
                            @foreach ($detalleFields as $field)
                                <div class="form-group col-md-2 mb-1">
                                    <input type="text" wire:model="detalleBien.{{ $field['model'] }}"
                                        placeholder="{{ $field['placeholder'] }}"
                                        class="form-control form-control-sm @error('detalleBien.' . $field['model']) is-invalid @enderror">
                                    @error('detalleBien.' . $field['model'])
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>


                    <div class="form-group col-md-auto mb-1">
                        <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                    </div>
                </form>

            @endif
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

        {{-- Contenedor con Selector (izquierda) y Destacados (derecha) --}}
        <div class="w-100 d-flex justify-content-between align-items-center flex-wrap mb-2 gap-2">

            {{-- Selector --}}
            <div class="d-flex align-items-center mb-0 gap-2">
                <label class="mb-0">Mostrar</label>
                <select wire:model.lazy="perPage" class="form-control form-control-sm w-auto">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>registros</span>
            </div>

            @if ($user->hasRole('Administrador') || $user->hasRole('Rector'))
                {{-- Bienes destacados (barra horizontal alineada a la derecha) --}}
                <div class="d-flex align-items-center overflow-auto gap-2 mt-1">
                    <span class="fw-bold pr-2">Destacados</span>
                    @foreach ($bienesDestacados as $bien)
                        <div class="d-flex align-items-center px-2 py-1 rounded border bg-white shadow-sm"
                            style="white-space: nowrap;">
                            <span class="text-gray-700 small me-2">{{ $bien->nombre }}:</span>
                            <span class="text-gray-600 fw-bold small pl-2">{{ $bien->cantidad_total }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>

        {{-- Paginación --}}
        <div class="w-100 overflow-auto">
            <div class="d-inline-block">
                {{ $bienes->links('pagination::bootstrap-4') }}
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
                    <th style="position: sticky; top: 0; left: 0; background-color: #ffffff; z-index: 11;">
                        <span class="badge bg-primary text-center p-2">Filtros →</span>
                    </th>

                    {{-- Botón para limpiar filtros --}}
                    <th style="position: sticky; top: 0; left: 73px; background: rgb(255, 255, 255); z-index: 11; ">
                        <div class="d-flex flex-column align-items-stretch gap-2 px-3">
                            <button wire:click="limpiarFiltros" class="btn btn-outline-primary btn-sm w-100"
                                title="Limpiar filtros">
                                <i class="fas fa-eraser me-1"></i>
                            </button>
                        </div>
                    </th>

                    {{-- Filtro por nombre --}}
                    <th
                        style="position: sticky; top: 0; left: 145px; background: white; z-index: 11; min-width: 250px;">
                        <div class="d-flex align-items-center gap-2">
                            <select wire:model.lazy="filtroNombre" class="form-control form-control-sm">
                                <option value="">Filtrar por bien</option>
                                @foreach ($listaNombresBienes as $nombre)
                                    <option value="{{ $nombre }}">{{ $nombre }}</option>
                                @endforeach
                            </select>

                            <button wire:click="$refresh" class="btn btn-sm btn-primary" title="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </th>

                    {{-- Resto de filtros basados en columnas --}}
                    @foreach ($visibleColumns as $index => $column)
                        @if ($column === 'nombre')
                            @continue
                        @endif
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
                    <th style="position: sticky; top: 30px; left: 0; background-color: rgb(18, 48, 78); z-index: 11;">
                        Acciones
                    </th>
                    <th wire:click="sortBy('id')"
                        style="cursor: pointer; position: sticky; top: 30px; left: 73px; background-color: rgb(18, 48, 78); z-index: 11;">
                        ID
                        @if ($sortField === 'id')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>
                    <th wire:click="sortBy('nombre')"
                        style="cursor: pointer; position: sticky; top: 30px; left: 145px; background-color: rgb(18, 48, 78); z-index: 11;">
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
                                'cursor: pointer; position: sticky; top: 30px; background-color: rgb(18, 48, 78);';
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


                </tr>
            </thead>

            <style>
                tr.no-hover:hover {
                    background-color: inherit !important;
                }
            </style>

            <tbody>
                @forelse ($bienes as $bien)
                    @php
                        $estadoNombre = strtolower($bien->estado->nombre ?? '');
                        $rowClass = '';

                        if ($bien->eliminacionPendiente) {
                            $rowClass = 'bg-dark text-muted no-hover'; // Eliminación pendiente → Oscuro + no-hover
                        } elseif ($estadoNombre === 'regular') {
                            $rowClass = 'bg-regular text-dark'; // Estado regular → Naranja
                        } elseif ($estadoNombre === 'malo') {
                            $rowClass = 'bg-malo bg-opacity-25 text-danger'; // Estado malo → Rosado claro
                        }
                    @endphp

                    <tr class="transition-colors hover:bg-blue-100 {{ $rowClass }}">

                        {{-- Columna Acciones (fija) --}}
                        <td @class([
                            'position-sticky z-50',
                            'bg-white bg-opacity-90' => !$bien->eliminacionPendiente,
                            'text-muted bg-dark' => $bien->eliminacionPendiente,
                        ]) style="left: 0;"
                            title="{{ $bien->eliminacionPendiente ? 'Eliminación pendiente de aprobación' : '' }}">

                            @if ($bien->eliminacionPendiente)
                                {{-- 🔒 Versión desactivada (cuando hay solicitud pendiente) --}}
                                <button class="btn btn-outline-secondary btn-sm p-1 me-1" disabled
                                    aria-label="Duplicar bien {{ $bien->id }}" title="Duplicar (deshabilitado)">
                                    <i class="fas fa-copy" style="font-size: 0.8rem;"></i>
                                </button>

                                <button class="btn btn-outline-danger btn-sm p-1 me-1" disabled
                                    aria-label="Eliminar bien {{ $bien->id }}"
                                    title="Eliminación solicitada (deshabilitado)">
                                    <i class="fas fa-trash-alt" style="font-size: 0.8rem;"></i>
                                </button>
                            @else
                                {{-- ✅ Versión funcional (sin solicitud pendiente) --}}
                                <button x-data @click="duplicarBien('{{ $this->getId() }}', {{ $bien->id }})"
                                    class="btn btn-outline-secondary btn-sm p-1 me-1"
                                    aria-label="Duplicar bien {{ $bien->id }}" title="Duplicar">
                                    <i class="fas fa-copy" style="font-size: 0.8rem;"></i>
                                </button>

                                <button wire:click="abrirModalEliminacion({{ $bien->id }})"
                                    class="btn btn-outline-danger btn-sm p-1 me-1"
                                    aria-label="Solicitar eliminación bien {{ $bien->id }}"
                                    title="Solicitar eliminación">
                                    <i class="fas fa-trash-alt" style="font-size: 0.8rem;"></i>
                                </button>
                            @endif
                        </td>

                        {{-- Columna Id (fija) --}}
                        <td @class([
                            'position-sticky z-50',
                            'bg-white bg-opacity-90' => !$bien->eliminacionPendiente,
                            'text-muted bg-dark' => $bien->eliminacionPendiente,
                        ]) style="left: 73px;"
                            title="{{ $bien->eliminacionPendiente ? 'Eliminación pendiente de aprobación' : '' }}">
                            {{ $bien->id }}
                        </td>

                        {{-- Columna Nombre (fija) --}}
                        <td @class([
                            'position-sticky z-50',
                            'bg-white bg-opacity-90' => !$bien->eliminacionPendiente,
                            'text-muted bg-dark' => $bien->eliminacionPendiente,
                        ]) style="left: 145px;"
                            title="{{ $bien->eliminacionPendiente ? 'Eliminación pendiente de aprobación' : '' }}">


                            @if (auth()->user()?->hasPermission('editar-bienes') && !$bien->eliminacionPendiente)
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
                            <td @class([
                                'col-separator',
                                'position-sticky z-40 bg-white' =>
                                    $isSticky && !$bien->eliminacionPendiente,
                                'position-sticky z-40 bg-dark bg-opacity-75 text-light' =>
                                    $isSticky && $bien->eliminacionPendiente,
                            ])
                                style="{{ $isSticky ? 'left: ' . $left . 'px;' : '' }} white-space: nowrap; min-width: 150px;"
                                title="{{ $bien->eliminacionPendiente ? 'Eliminación pendiente de aprobación' : '' }}">

                                @if ($column === 'detalle')
                                    @if (auth()->user()?->hasPermission('editar-bienes') && !$bien->eliminacionPendiente)
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
                                    @elseif (auth()->user()?->hasPermission('editar-bienes') && !$bien->eliminacionPendiente)
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

    {{-- Vista móvil: acordeón con Alpine.js --}}
    <div class="d-block d-md-none" x-data="{ openId: null }" wire:poll.30s>
        <div id="accordionMobileBienes">
            @forelse($bienes as $bien)
                @php
                    $estadoNombre = strtolower($bien->estado->nombre ?? '');
                    $cardClass = '';

                    if ($bien->eliminacionPendiente) {
                        $cardClass = 'border-secondary bg-dark text-muted'; // Eliminación pendiente → gris claro
                    } elseif ($estadoNombre === 'regular') {
                        $cardClass = 'border-warning bg-regular';
                    } elseif ($estadoNombre === 'malo') {
                        $cardClass = 'border-danger bg-malo';
                    }

                    $badgeClass = match (true) {
                        $bien->cantidad === 0, $bien->cantidad < 5 => 'badge-primary',
                        default => 'badge-primary',
                    };

                    $isOpen = "openId === {$bien->id}";
                    $toggleOpen = "{$isOpen} ? openId = null : openId = {$bien->id}";
                @endphp

                <div class="card mb-2 {{ $cardClass }}"
                    @if ($bien->eliminacionPendiente) title="Eliminación pendiente de aprobación" @endif>

                    {{-- Encabezado --}}
                    <div class="card-header d-flex align-items-center justify-content-between p-2 w-100"
                        @if (!$bien->eliminacionPendiente) @click="{{ $toggleOpen }}" 
                            @keydown.enter.prevent="{{ $toggleOpen }}"
                            @keydown.space.prevent="{{ $toggleOpen }}" @endif
                        tabindex="0" role="button" @class([
                            'cursor-pointer' => !$bien->eliminacionPendiente,
                            'opacity-75' => $bien->eliminacionPendiente,
                        ])>

                        {{-- Izquierda: nombre + icono de modificaciones + icono de estado --}}
                        <div class="d-flex align-items-center flex-grow-1 flex-wrap">
                            <span class="text-truncate">
                                {{ $bien->id }}. {{ $bien->nombre }}

                                @if ($bien->eliminacionPendiente)
                                    <i class="fas fa-hourglass-half text-info ms-1 pl-2"></i>
                                    → <i class="fas fa-trash-alt text-danger ms-2 pl-1"
                                        title="Eliminación pendiente de aprobación"></i>
                                @elseif ($bien->tieneModificacionesPendientes())
                                    <i class="fas fa-hourglass-half text-info ms-1 pl-2"
                                        title="Tienes modificaciones pendientes en este bien"></i>
                                @endif
                            </span>

                            @if ($estadoNombre === 'malo')
                                <i class="fas fa-exclamation-circle text-white ml-2" title="Estado: Malo"></i>
                            @elseif($estadoNombre === 'regular')
                                <i class="fas fa-exclamation-triangle text-white ml-2" title="Estado: Regular"></i>
                            @endif
                        </div>

                        {{-- Derecha: cantidad + botón eliminar --}}
                        <div class="d-flex align-items-center flex-shrink-0 ms-auto">
                            <span class="me-2 badge {{ $badgeClass }} badge-pill mr-2">
                                <i class="fas fa-cubes me-1 "></i> {{ $bien->cantidad }}
                            </span>

                            @if (!$bien->eliminacionPendiente)
                                <button x-data @click="duplicarBien('{{ $this->getId() }}', {{ $bien->id }})"
                                    class="btn btn-outline-primary btn-sm p-1 pr-2 pl-2 mr-1 ml-1"
                                    aria-label="Duplicar bien {{ $bien->id }}" title="Duplicar">
                                    <i class="fas fa-copy" style="font-size: 0.8rem;"></i>
                                </button>
                            @endif

                            @if (auth()->user()?->hasPermission('eliminar-bienes') && !$bien->eliminacionPendiente)
                                <button wire:click="abrirModalEliminacion({{ $bien->id }})"
                                    class="btn btn-outline-danger btn-sm p-1 pr-2 pl-2 mr-1 ml-1"
                                    aria-label="Solicitar eliminación bien {{ $bien->id }}"
                                    title="Solicitar eliminación">
                                    <i class="fas fa-trash-alt" style="font-size: 0.8rem;"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Cuerpo del acordeón --}}
                    {{-- ⚠️ SOLO se renderiza el contenido si NO tiene eliminación pendiente --}}
                    @unless ($bien->eliminacionPendiente)
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
                    @endunless
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

    {{-- Modal Eliminar Bien para escritorio - Bootstrap 4 --}}
    <div wire:ignore.self class="modal fade" id="modalEliminarBien" tabindex="-1"
        aria-labelledby="modalEliminarBienLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEliminarBienLabel">Solicitar eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <label for="motivo" class="form-label">Motivo:</label>
                    <select wire:model="motivoSeleccionado" id="motivo" class="form-control">
                        <option value="">Seleccione un motivo</option>
                        @foreach ($motivosBase as $motivo)
                            <option value="{{ (string) $motivo }}">{{ $motivo }}</option>
                        @endforeach
                        <option value="otro">Otro...</option>
                    </select>

                    {{-- Debug temporal --}}
                    <div class="mt-2 text-muted small">Seleccionado: {{ $motivoSeleccionado }}</div>

                    @if ((string) $motivoSeleccionado === 'otro')
                        <input type="text" wire:model.defer="motivoNuevo" class="form-control mt-2"
                            placeholder="Ingrese nuevo motivo" />
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button wire:click="solicitarEliminacion" type="button"
                        class="btn btn-danger btn-sm">Solicitar</button>
                </div>

            </div>
        </div>
    </div>

    {{-- Script para manejar la duplicación de bienes --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function duplicarBien(componentId, id) {
            Swal.fire({
                title: '¿Deseas duplicar este bien?',
                text: 'Escribe "Duplicar" para confirmar:',
                input: 'text',
                inputPlaceholder: 'Duplicar',
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                preConfirm: (value) => {
                    if (value !== 'Duplicar') {
                        Swal.showValidationMessage('Debes escribir exactamente "Duplicar"');
                    }
                    return value;
                }
            }).then((result) => {
                if (result.isConfirmed && result.value === 'Duplicar') {
                    Livewire.find(componentId).call('duplicar', id);
                }
            });
        }
    </script>

    {{-- Script para manejar el modal de eliminación de bienes --}}
    <script>
        document.addEventListener('abrir-modal-eliminar-bien', () => {
            $('#modalEliminarBien').modal({
                backdrop: 'static', // Evita que se cierre al hacer clic afuera
                keyboard: false // Evita cierre con ESC
            });
            $('#modalEliminarBien').modal('show');
        });

        document.addEventListener('cerrar-modal-eliminar-bien', () => {
            $('#modalEliminarBien').modal('hide');
        });
    </script>

    {{-- Scripts para manejar el modal de detalles del bien --}}
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

</div>
