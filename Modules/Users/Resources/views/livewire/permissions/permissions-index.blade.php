<div>

    {{-- Mensajes de sesión --}}
    @if(session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Botón para mostrar formulario en móvil --}}
    <div class="d-block d-md-none mb-3">
        <button 
            class="btn btn-primary btn-sm btn-block" 
            type="button" 
            data-toggle="collapse" 
            data-target="#formCreatePermission" 
            aria-expanded="false" 
            aria-controls="formCreatePermission"
        >
            Crear Permiso
        </button>
    </div>

    {{-- Formulario de creación de permiso--}}
    @php
        $fields = [
            ['model' => 'nombre', 'placeholder' => 'Nombre', 'type' => 'text'],
            ['model' => 'descripcion', 'placeholder' => 'Descripción', 'type' => 'text'],
        ];
    @endphp

    <div class="collapse d-md-block" id="formCreatePermission">
        <form wire:submit.prevent="store" class="d-flex flex-column flex-md-row flex-wrap align-items-md-center mb-4" novalidate>
            
            @foreach ($fields as $field)
                <div class="form-group mr-md-2 flex-grow-1" style="min-width: 120px;">
                    <input 
                        type="{{ $field['type'] }}" 
                        wire:model="{{ $field['model'] }}" 
                        placeholder="{{ $field['placeholder'] }}" 
                        class="form-control form-control-sm @error($field['model']) is-invalid @enderror"
                        aria-label="{{ $field['placeholder'] }}"
                        autocomplete="off"
                    >
                    @error($field['model'])
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach

            {{-- Select de categoría --}}
            <div class="form-group mr-md-2 flex-grow-1" style="min-width: 120px;">
                <select 
                    wire:model.lazy="categoria" 
                    class="form-control form-control-sm @error('categoria') is-invalid @enderror"
                    aria-label="Categoría"
                >
                    <option value="">Selecciona una Categoría</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                    @endforeach
                    <option value="otra">Otra</option>
                </select>
            </div>

            {{-- Input para nueva categoría (condicional) --}}
            @if($categoria === 'otra')
                <div class="form-group mr-md-2 flex-grow-1" style="min-width: 120px;">
                    <input 
                        type="text"
                        wire:model.lazy="nuevaCategoria"
                        placeholder="Nueva categoría"
                        class="form-control form-control-sm @error('nuevaCategoria') is-invalid @enderror"
                        aria-label="Nueva categoría"
                        autocomplete="off"
                    >
                    @error('nuevaCategoria')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <button type="submit" class="btn btn-primary btn-sm mt-2 mt-md-0">Crear</button>
        </form>
    </div>

    {{ $permissions->links() }}

    <p>Para editar, doble click en el campo que desee modificar.</p>
    
    {{-- Tabla para escritorio --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Id</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Slug</th>
                    <th>Categoría</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $permission)
                    <tr>
                        <td>{{ $permission->id }}</td>
                        <td>
                            @livewire('permissions.editar-nombre-permission', ['permission' => $permission], key('nombre-'.$permission->id))
                        </td>
                        <td>
                            @livewire('permissions.editar-descripcion-permission', ['permission' => $permission], key('descripcion-'.$permission->id))
                        </td>
                        <td>
                            @livewire('permissions.editar-categoria-permission', ['permission' => $permission], key('categoria-'.$permission->id))
                        </td> 
                        <td class="text-muted small">{{ $permission->slug }}</td>                                               
                        <td>
                            <button 
                                wire:click="delete({{ $permission->id }})" 
                                class="btn btn-sm btn-danger"
                                onclick="confirm('¿Confirma eliminar?') || event.stopImmediatePropagation()"
                                aria-label="Eliminar permiso {{ $permission->nombre }} {{ $permission->descripcion }}"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No hay permisos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>


    {{-- Vista móvil: acordeón --}}
    <div class="d-block d-md-none">
        <div id="accordionMobile">
            @foreach($permissions as $permission)
                <div class="card mb-2">
                    <div 
                        class="card-header p-2 d-flex align-items-center"
                        id="heading{{ $permission->id }}"
                        data-toggle="collapse"
                        data-target="#collapse{{ $permission->id }}"
                        aria-expanded="false"
                        aria-controls="collapse{{ $permission->id }}"
                        style="cursor: pointer;"
                        role="button"
                        tabindex="0"
                        onkeydown="if(event.key === 'Enter' || event.key === ' ') { $('#collapse{{ $permission->id }}').collapse('toggle'); event.preventDefault(); }"
                    >
                        <span>{{ $permission->id }}. {{ $permission->nombre }}</span>

                        <button 
                            wire:click.stop="delete({{ $permission->id }})" 
                            class="btn btn-sm btn-danger ml-auto"
                            onclick="confirm('¿Confirma eliminar?') || event.stopImmediatePropagation()"
                            aria-label="Eliminar permiso {{ $permission->nombre }}"
                        >
                            Eliminar
                        </button>
                    </div>

                    <div id="collapse{{ $permission->id }}" class="collapse" aria-labelledby="heading{{ $permission->id }}" data-parent="#accordionMobile">
                        <div class="card-body p-2">
                            <div class="mb-2">
                                <strong>Nombre:</strong> 
                                @livewire('permissions.editar-nombre-permission', ['permission' => $permission], key('mobile-nombre-'.$permission->id))
                            </div>
                            <div class="mb-2">
                                <strong>Descripción:</strong> 
                                @livewire('permissions.editar-descripcion-permission', ['permission' => $permission], key('mobile-descripcion-'.$permission->id))
                            </div>
                            <div class="mb-2">
                                <strong>Categoría:</strong> 
                                @livewire('permissions.editar-categoria-permission', ['permission' => $permission], key('mobile-categoria-'.$permission->id))
                            </div>
                            <div class="mb-2">
                                <strong>Slug:</strong> {{ $permission->slug }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            @if($permissions->isEmpty())
                <p class="text-center text-muted">No hay permisos registrados.</p>
            @endif
        </div>
    </div>

    {{ $permissions->links() }}


</div>

