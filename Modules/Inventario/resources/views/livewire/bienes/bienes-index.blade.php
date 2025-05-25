<div>
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
                Crear Bien
            </button>
        </div>
    @endif

    {{-- Formulario de creación --}}
    @if(auth()->user()->hasPermission('crear-bienes'))
        <div class="collapse d-md-block" id="formCreateBien">
            <form wire:submit.prevent="store" class="d-flex flex-column flex-md-row flex-wrap align-items-md-center mb-4" novalidate>
                @php
                    $fields = [
                        ['model' => 'nom_bien', 'placeholder' => 'Nombre del bien', 'type' => 'text'],
                        ['model' => 'detalle_del_bien', 'placeholder' => 'Detalle', 'type' => 'text'],
                        ['model' => 'serie_del_bien', 'placeholder' => 'Serie', 'type' => 'text'],
                        ['model' => 'origen_del_bien', 'placeholder' => 'Origen', 'type' => 'text'],
                        ['model' => 'fecha_adquisicion', 'placeholder' => 'Fecha adquisición', 'type' => 'date'],
                        ['model' => 'precio', 'placeholder' => 'Precio', 'type' => 'number'],
                        ['model' => 'cant_bien', 'placeholder' => 'Cantidad', 'type' => 'number'],
                        ['model' => 'observaciones', 'placeholder' => 'Observaciones', 'type' => 'text'],
                    ];

                    $selectFields = [
                        ['model' => 'cod_categoria', 'label' => 'Categoría', 'options' => $categorias ?? []],
                        ['model' => 'cod_dependencias', 'label' => 'Dependencia', 'options' => $dependencias ?? []],
                        ['model' => 'usuario_id', 'label' => 'Usuario responsable', 'options' => $usuarios ?? []],
                        ['model' => 'cod_almacenamiento', 'label' => 'Almacenamiento', 'options' => $almacenamientos ?? []],
                        ['model' => 'cod_estado', 'label' => 'Estado', 'options' => $estados ?? []],
                        ['model' => 'cod_mantenimiento', 'label' => 'Mantenimiento', 'options' => $mantenimientos ?? []],
                    ];
                @endphp

                @foreach ($fields as $field)
                    <div class="form-group mr-md-2 flex-grow-1" style="min-width: 160px;">
                        <input
                            type="{{ $field['type'] }}"
                            wire:model="{{ $field['model'] }}"
                            placeholder="{{ $field['placeholder'] }}"
                            class="form-control form-control-sm @error($field['model']) is-invalid @enderror"
                            autocomplete="off"
                        >
                        @error($field['model'])
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach

                @foreach ($selectFields as $field)
                    <div class="form-group mr-md-2 flex-grow-1" style="min-width: 160px;">
                        <select
                            wire:model="{{ $field['model'] }}"
                            class="form-control form-control-sm @error($field['model']) is-invalid @enderror"
                        >
                            <option value="">{{ $field['label'] }}</option>
                            @foreach($field['options'] as $item)
                                <option value="{{ $item->id }}">{{ $item->nombre ?? $item->descripcion ?? $item->name }}</option>
                            @endforeach
                        </select>
                        @error($field['model'])
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach

                <button type="submit" class="btn btn-primary btn-sm mt-2 mt-md-0">Crear</button>
            </form>
        </div>
    @endif

    {{-- Tabla responsive escritorio --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    @foreach($availableColumns as $key => $label)
                        @if(in_array($key, $visibleColumns))
                            <th>{{ $label }}</th>
                        @endif
                    @endforeach
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bienes as $bien)
                    <tr>
                        <td>{{ $bien->id }}</td>
                        @foreach($availableColumns as $key => $label)
                            @if(in_array($key, $visibleColumns))
                                <td>
                                    @if(auth()->user()?->hasPermission('editar-bienes'))
                                        @livewire("bienes.editar-$key-bien", ['bien' => $bien], key("$key-{$bien->id}"))
                                    @else
                                        {{ $bien->$key }}
                                    @endif
                                </td>
                            @endif
                        @endforeach
                        <td>
                            @if(auth()->user()->hasPermission('eliminar-bienes'))
                                <button 
                                    wire:click="delete({{ $bien->id }})" 
                                    class="btn btn-sm btn-danger"
                                    onclick="confirm('¿Confirma eliminar?') || event.stopImmediatePropagation()"
                                    aria-label="Eliminar bien {{ $bien->nom_bien }}"
                                >
                                    Eliminar
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%" class="text-center text-muted">No hay bienes registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Vista móvil --}}
    <div class="d-block d-md-none">
        <div id="accordionMobileBienes">
            @foreach($bienes as $bien)
                <div class="card mb-2">
                    <div 
                        class="card-header p-2 d-flex align-items-center"
                        data-toggle="collapse"
                        data-target="#collapseBien{{ $bien->id }}"
                        aria-expanded="false"
                        aria-controls="collapseBien{{ $bien->id }}"
                        style="cursor: pointer;"
                    >
                        <span>{{ $bien->id }}. {{ $bien->nom_bien }}</span>
                        @if(auth()->user()->hasPermission('eliminar-bienes'))
                            <button 
                                wire:click.stop="delete({{ $bien->id }})" 
                                class="btn btn-sm btn-danger ml-auto"
                                onclick="confirm('¿Confirma eliminar?') || event.stopImmediatePropagation()"
                                aria-label="Eliminar bien {{ $bien->nom_bien }}"
                            >
                                Eliminar
                            </button>
                        @endif
                    </div>

                    <div id="collapseBien{{ $bien->id }}" class="collapse" data-parent="#accordionMobileBienes">
                        <div class="card-body p-2">
                            @foreach($availableColumns as $key => $label)
                                @if(in_array($key, $visibleColumns))
                                    <div class="mb-2">
                                        <strong>{{ $label }}:</strong>
                                        @livewire("bienes.editar-$key-bien", ['bien' => $bien], key("mobile-$key-{$bien->id}"))
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
