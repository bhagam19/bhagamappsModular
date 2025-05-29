<div>
    @php $user = auth()->user(); @endphp

    <style>

        .table-hover tbody tr:hover {
            background-color:rgb(135, 180, 174) !important; 
            transition: background-color 0.1s ease-in-out;
        }

        thead th {
            position: sticky;
            top: 0;
            background-color: white; /* o el color de fondo de tu encabezado */
            z-index: 10; /* asegúrate de que quede por encima del contenido */
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
            background-color:rgb(240, 147, 85) !important;
            color:rgb(0, 0, 0) !important;
        }

        .bg-malo {
            background-color:rgb(243, 76, 76) !important;
            color:rgb(0, 0, 0) !important;
        }

    </style>

    {{-- Mensaje de bienvenida --}}

    {{-- Dependencias a cargo del usuario (compacto, alineado a la derecha, parte superior) --}}
    @if(!$user->hasRole('Administrador') && !$user->hasRole('Rector') && isset($dependencias) && $dependencias->count())
        <div class="d-flex justify-content-end mb-2">
            <div class="bg-light border rounded px-3 py-2 text-right shadow-sm" style="font-size: 1rem;">
                <div class="font-weight-bold mb-2">Dependencias a tu cargo:</div>
                @foreach($dependencias as $dep)
                    <div class="badge badge-custom d-block mb-1">{{ $dep->nombre }}</div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Mensaje de sesión --}}
    @if(session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif    

    {{-- Botón para mostrar formulario (móvil) --}}
    @if(auth()->user()->hasPermission('crear-bienes'))
        <div class="d-block d-md-none mb-3">
            <button class="btn btn-primary btn-sm btn-block" type="button" data-toggle="collapse" data-target="#formCreateBien" aria-expanded="false" aria-controls="formCreateBien">
                Agregar Bien
            </button>
        </div>
    @endif

    {{-- Botón para mostrar formulario (escritorio) --}}
    @if(auth()->user()->hasPermission('crear-bienes'))
        <div class="d-none d-md-block mb-3">
            <button class="btn btn-primary btn-sm" type="button" data-toggle="collapse" data-target="#formCreateBien" aria-expanded="false" aria-controls="formCreateBien">
                Agregar Bien
            </button>
        </div>
    @endif

    {{-- Formulario de creación (igual para móvil y escritorio) --}}
    @if(auth()->user()->hasPermission('crear-bienes'))
        <div class="collapse" id="formCreateBien">
            <form wire:submit.prevent="store" class="form-row align-items-end mb-4" novalidate>
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
                     // ['model' => 'usuario_id', 'label' => 'Usuario', 'options' => $usuarios ?? []],
                        ['model' => 'almacenamiento_id', 'label' => 'Almacenamiento', 'options' => $almacenamientos ?? []],
                        ['model' => 'estado_id', 'label' => 'Estado', 'options' => $estados ?? []],
                        ['model' => 'mantenimiento_id', 'label' => 'Mantenimiento', 'options' => $mantenimientos ?? []],
                    ];
                @endphp

                @foreach ($fields as $field)
                    <div class="form-group col-md-3">
                        <input
                            type="{{ $field['type'] }}"
                            wire:model="{{ $field['model'] }}"
                            placeholder="{{ $field['placeholder'] }}"
                            class="form-control form-control-sm @error($field['model']) is-invalid @enderror"
                        >
                        @error($field['model'])
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach

                @foreach ($selectFields as $field)
                    <div class="form-group col-md-3">
                        <select
                            wire:model="{{ $field['model'] }}"
                            class="form-control form-control-sm @error($field['model']) is-invalid @enderror"
                        >
                            <option value="">{{ $field['label'] }}</option>
                            @foreach($field['options'] as $item)
                                <option value="{{ $item->id }}">{{ $item->nombre ?? $item->descripcion ?? $item->nombre_completo ?? 'Sin nombre' }}</option>
                            @endforeach
                        </select>
                        @error($field['model'])
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach

                <div class="form-group col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Crear</button>
                </div>
            </form>
        </div>
    @endif        

    {{-- Botón para mostrar filtros y búsqueda en móvil --}}
    <div class="d-block d-md-none mb-3">
        <button class="btn btn-outline-secondary btn-sm btn-block" type="button" data-toggle="collapse" data-target="#filtrosMobile" aria-expanded="false" aria-controls="filtrosMobile">
            <i class="fas fa-filter"></i> Mostrar filtros y búsqueda
        </button>
    </div>

    {{-- filtros y búsqueda en escritorio --}}
    <div class="collapse d-md-block" id="filtrosMobile">

        <div class="card shadow-sm mb-4">
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

                {{-- Barra superior: Buscar y cantidad por página --}}
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <input type="text" wire:model.lazy="filtroNombre" class="form-control" placeholder="🔍 Buscar por nombre...">
                    </div>

                    <div class="col-md-6 d-flex justify-content-end align-items-center">
                        <label class="mr-2 mb-0">Mostrar</label>
                        <select wire:model.lazy="perPage" class="form-control w-auto">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ml-2">registros</span>
                    </div>
                </div>

                {{-- Filtros adicionales --}}
                <div class="row mb-3">

                    <div class="col-md-3">
                        <select wire:model.lazy="filtroUsuario" class="form-control">
                            <option value="">Todos los usuarios</option>
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->id }}">{{ $usuario->nombres }} {{ $usuario->apellidos }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select wire:model.lazy="filtroCategoria" class="form-control">
                            <option value="">Todas las categorías</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select wire:model.lazy="filtroDependencia" class="form-control">
                            <option value="">Todas las dependencias</option>
                            @foreach($dependencias as $dep)
                                <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select wire:model.lazy="filtroEstado" class="form-control">
                            <option value="">Todos los estados</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Botón limpiar --}}
                <div class="text-right">
                    <button wire:click="limpiarFiltros" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-eraser"></i> Limpiar filtros
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Tabla para escritorio --}}
    <div class="table-responsive d-none d-md-block" style="max-height: 600px; overflow-y: auto;">
        <table class="table table-striped table-sm table-hover w-100 mb-0">
            <thead class="thead-dark">
                <tr>
                    {{-- Columna 1 fija: ID --}}
                    <th wire:click="sortBy('id')" style="cursor: pointer; position: sticky; top: 0; left: 0; background-color:rgb(18, 48, 78); z-index: 11;">
                        ID
                        @if ($sortField === 'id')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    {{-- Columnas dinámicas --}}
                    @foreach ($visibleColumns as $index => $column)
                        @php
                            $left = 30 + ($index * 150); // Ajusta según el ancho de cada columna
                            $isSticky = $index < 1; // Solo las dos primeras columnas visibles también serán sticky (junto con ID = 3 totales)
                        @endphp
                        <th
                            class="col-separator"
                            wire:click="sortBy('{{ $column }}')"
                            style="cursor: pointer;
                                @if ($isSticky)
                                    position: sticky;
                                    top: 0;
                                    left: {{ $left }}px;
                                    z-index: 12;
                                    background-color: rgb(18, 48, 78);
                                @else
                                    position: sticky;
                                    top: 0;
                                    background-color: rgb(18, 48, 78);
                                @endif"
                        >
                            {{ ucfirst(str_replace('_', ' ', preg_replace('/_id$/', '', $column))) }}
                            @if ($sortField === $column)
                                {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                            @endif
                        </th>
                    @endforeach

                    <th style="position: sticky; top: 0; background-color: #343a40; z-index: 9;">Acciones</th>
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

                        {{-- Columnas dinámicas --}}
                        @foreach ($visibleColumns as $index => $column)
                            @php
                                $left = 30 + ($index * 150);
                                $isSticky = $index < 1;
                            @endphp
                            <td class="col-separator" style="
                                white-space: nowrap;
                                min-width: 150px;
                                @if ($isSticky)
                                    position: sticky;
                                    left: {{ $left }}px;
                                    background-color: white;
                                    z-index: 4;
                                @endif
                            ">
                                @if ($column === 'detalle')
                                    @if ($bien->detalle)
                                        <small>
                                            @if ($bien->detalle->car_especial) {{ $bien->detalle->car_especial }} | @endif
                                            @if ($bien->detalle->marca) {{ $bien->detalle->marca }} | @endif
                                            @if ($bien->detalle->color) {{ $bien->detalle->color }} | @endif
                                            @if ($bien->detalle->tamano) {{ $bien->detalle->tamano }} | @endif
                                            @if ($bien->detalle->material) {{ $bien->detalle->material }} | @endif
                                            @if ($bien->detalle->otra) {{ $bien->detalle->otra }} @endif
                                        </small>
                                    @else
                                        —
                                    @endif
                                @else
                                    @if ($column === 'ubicacion_id')
                                        {{ $bien->dependencia->ubicacion->nombre ?? 'Sin ubicación' }}
                                    @elseif (auth()->user()?->hasPermission('editar-bienes'))
                                        @livewire('bienes.editar-campo-bien', [
                                            'bienId' => $bien->id,
                                            'campo' => $column,
                                        ], key("bien-{$column}-{$bien->id}-{$bien->updated_at}"))
                                    @else
                                        {{ $bien->getDisplayValue($column) }}
                                    @endif
                                @endif
                            </td>
                        @endforeach

                        <td>
                            @if(auth()->user()?->hasPermission('eliminar-bienes'))
                                <button 
                                    wire:click="delete({{ $bien->id }})" 
                                    class="btn btn-sm btn-danger"
                                    onclick="confirm('¿Confirma eliminar?') || event.stopImmediatePropagation()"
                                    aria-label="Eliminar bien {{ $bien->id }}"
                                >
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

    {{-- Vista móvil: acordeón con Alpine.js --}}
    <div class="d-block d-md-none" x-data="{ openId: null }">
        <div id="accordionMobileBienes">
            @foreach($bienes as $bien)
                @php
                    $estadoNombre = strtolower($bien->estado->nombre ?? '');
                    $cardClass = '';
                    if ($estadoNombre === 'regular') {
                        $cardClass = 'border-warning bg-regular';
                    } elseif ($estadoNombre === 'malo') {
                        $cardClass = 'border-danger bg-malo';
                    }
                @endphp
                <div class="card mb-2 {{ $cardClass }}">
                    <div
                        class="card-header p-2 d-flex align-items-center"
                        @click="openId === {{ $bien->id }} ? openId = null : openId = {{ $bien->id }}"
                        @keydown.enter.prevent="openId === {{ $bien->id }} ? openId = null : openId = {{ $bien->id }}"
                        @keydown.space.prevent="openId === {{ $bien->id }} ? openId = null : openId = {{ $bien->id }}"
                        tabindex="0"
                        role="button"
                    >
                        <span>{{ $bien->id }}. {{ $bien->nombre }}</span>

                        @php
                            $badgeClass = match(true) {
                                $bien->cantidad === 0 => 'badge-success',
                                $bien->cantidad < 5 => 'badge-success',
                                default => 'badge-success',
                            };
                        @endphp
                        <span class="ml-auto mr-2 badge {{ $badgeClass }} badge-pill">{{ $bien->cantidad }}</span>

                        @if(auth()->user()?->hasPermission('eliminar-bienes'))
                            <button
                                wire:click.stop="$emit('confirmDelete', {{ $bien->id }})"
                                class="btn btn-sm btn-danger ml-auto"
                                aria-label="Eliminar bien {{ $bien->nombre }}"
                            >
                                Eliminar
                            </button>
                        @endif
                    </div>

                    <div x-show="openId === {{ $bien->id }}" x-collapse class="card-body p-2">
                        @foreach($availableColumns as $key => $label)
                            @if(in_array($key, $visibleColumns))
                                <div class="mb-2">
                                    <strong>{{ $label }}:</strong>

                                    @if ($key === 'detalle')
                                        @if ($bien->detalle)
                                            <small class="d-block mt-1">
                                                @foreach (['car_especial', 'marca', 'color', 'tamano', 'material', 'otra'] as $attr)
                                                    @if ($bien->detalle->$attr)
                                                        {{ $bien->detalle->$attr }} |
                                                    @endif
                                                @endforeach
                                            </small>
                                        @else
                                            —
                                        @endif
                                    @else
                                        @if(auth()->user()?->hasPermission('editar-bienes'))
                                            @livewire('bienes.editar-campo-bien', [
                                                'bienId' => $bien->id,
                                                'campo' => $key,
                                            ], key("mobile-bien-{$key}-{$bien->id}-{$bien->updated_at}"))
                                        @else
                                            {{ $bien->getDisplayValue($key) ?? '—' }}
                                        @endif
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if($bienes->isEmpty())
                <p class="text-center text-muted">No hay bienes registrados.</p>
            @endif
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
