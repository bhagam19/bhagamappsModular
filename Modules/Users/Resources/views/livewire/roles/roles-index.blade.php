<div>
    
    {{-- Mensajes de sesión --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Botón para mostrar formulario en móvil --}}
    <div class="d-block d-md-none mb-3">
        <button class="btn btn-primary btn-sm btn-block" type="button" data-toggle="collapse" data-target="#formCreateRole" aria-expanded="false" aria-controls="formCreateRole">
            Crear Rol
        </button>
    </div>

    {{-- Formulario de creación de rol --}}
    <div class="collapse d-md-block" id="formCreateRole">
        <form wire:submit.prevent="store" class="d-flex flex-column flex-md-row flex-wrap align-items-md-center mb-4">
            <div class="form-group mr-md-2 flex-grow-1" style="min-width: 120px;">
                <input type="text" wire:model="nombre" placeholder="Nombre del Rol" class="form-control form-control-sm @error('nombre') is-invalid @enderror">
                @error('nombre') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="form-group mr-md-2 flex-grow-1" style="min-width: 120px;">
                <input type="text" wire:model="descripcion" placeholder="Descripcion del Rol" class="form-control form-control-sm @error('descripcion') is-invalid @enderror">
                @error('descripcion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Crear</button>
        </form>
    </div>

    <p>Para editar, doble click en el campo que desee modificar.</p>

    {{-- Dropdown para mostrar/ocultar columnas --}}
    <div class="dropdown mb-3 text-right">
        <button 
            class="btn btn-outline-secondary dropdown-toggle d-none d-md-flex align-items-center ml-auto" 
            type="button" 
            id="dropdownMenuButton" 
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"            
            aria-label="Mostrar opciones de columnas"
        >
            <svg width="20" height="20" fill="currentColor" class="mr-2" viewBox="0 0 16 16" aria-hidden="true">
                <path d="M3 9h10V7H3v2zm0 4h10v-2H3v2zm0-8h10V3H3v2z"/>
            </svg>
             Mostrar/Ocultar Columnas
        </button>
        <div 
            class="dropdown-menu dropdown-menu-right p-3 shadow-sm" 
            aria-labelledby="dropdownMenuButton" 
            style="min-width: 200px;"
        >            
            @foreach($availableColumns as $key => $label)
                <label 
                    class="d-flex align-items-center mb-2 cursor-pointer" 
                    style="user-select:none;"
                >
                    <input 
                        class="mr-2" 
                        type="checkbox"                        
                        style="transform: scale(1.3);" 
                        id="col_{{ $key }}"
                        wire:click="toggleColumn('{{ $key }}')"
                        @if(in_array($key, $visibleColumns)) checked @endif
                    >
                    <span class="flex-grow-1">{{ $label }}</span>
                    <svg width="16" height="16" fill="currentColor" style="transition: transform 0.3s;" class="ml-2 checkmark" viewBox="0 0 16 16" hidden>
                        <path d="M13.485 3.5L6.75 10.24 3.515 7l-1.02 1.02L6.75 12.3l8-8-1.264-1.265z"/>
                    </svg>
                </label>
            @endforeach
        </div>
    </div>

    @push('css')
    <style>
        .cursor-pointer {
            cursor: pointer;
        }
        .dropdown-menu label:hover {
            background-color: #f0f0f0;
            border-radius: 4px;
        }
        input[type="checkbox"] {
            cursor: pointer;
        }
        input[type="checkbox"]:checked + span + svg.checkmark {
            display: inline;
            transform: scale(1.2);
            color: #007bff;
        }
    </style>
    @endpush

    {{-- Tabla para escritorio --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Id</th>                    
                    @if(in_array('nombre', $visibleColumns)) <th>Nombre del Rol</th> @endif
                    @if(in_array('descripcion', $visibleColumns)) <th>Descripción</th> @endif
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $rol)
                    <tr>
                        <td>{{ $rol->id }}</td>
                        @if(in_array('nombre', $visibleColumns))
                            <td>@livewire('roles.editar-nombre-role', ['role' => $rol], key('nombre-'.$rol->id))</td>
                        @endif
                        @if(in_array('descripcion', $visibleColumns))
                            <td>@livewire('roles.editar-descripcion-role', ['role' => $rol], key('descripcion-'.$rol->id))</td>
                        @endif
                        <td>
                            <a href="{{ route('roles.editar-permisos', $rol->id) }}" class="btn btn-sm btn-primary">
                                Gestionar Permisos
                            </a>
                            <button wire:click="delete({{ $rol->id }})" 
                                class="btn btn-sm btn-danger"
                                onclick="confirm('¿Confirma eliminar este rol?') || event.stopImmediatePropagation()">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">No hay roles registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Vista móvil: acordeón --}}
    <div class="d-block d-md-none">
        <div id="accordionMobile">
            @foreach($roles as $rol)
                <div class="card mb-2">
                    <div 
                        class="card-header p-2 d-flex align-items-center"
                        id="heading{{ $rol->id }}"
                        data-toggle="collapse"
                        data-target="#collapse{{ $rol->id }}"
                        aria-expanded="false"
                        aria-controls="collapse{{ $rol->id }}"
                        style="cursor: pointer;"
                        role="button"
                        tabindex="0"
                        onkeydown="if(event.key === 'Enter' || event.key === ' ') { $('#collapse{{ $rol->id }}').collapse('toggle'); event.preventDefault(); }"
                    >
                        <span>{{ $rol->id }}. {{ $rol->nombre }}</span>

                        <button 
                            wire:click.stop="delete({{ $rol->id }})" 
                            class="btn btn-sm btn-danger ml-auto"
                            onclick="confirm('¿Confirma eliminar?') || event.stopImmediatePropagation()"
                            aria-label="Eliminar usuario {{ $rol->nombre }}"
                        >
                            Eliminar
                        </button>
                    </div>

                    <div id="collapse{{ $rol->id }}" class="collapse" aria-labelledby="heading{{ $rol->id }}" data-parent="#accordionMobile">
                        <div class="card-body p-2">
                            <div class="mb-2">
                                <strong>Rol:</strong> 
                                @livewire('roles.editar-nombre-role', ['role' => $rol], key('mobile-nombre-'.$rol->id))
                            </div>
                            <div class="mb-2">
                                <strong>Descripción:</strong> 
                                @livewire('roles.editar-descripcion-role', ['role' => $rol], key('mobile-descripcion-'.$rol->id))
                            </div>
                            <div class="mb-2">
                                <a href="{{ route('roles.editar-permisos', $rol->id) }}" class="btn btn-sm btn-primary">
                                    Gestionar Permisos
                                </a>
                            </div>
                            
                        </div>
                    </div>
                </div>
            @endforeach
            @if($roles->isEmpty())
                <p class="text-center text-muted">No hay roles registrados.</p>
            @endif
        </div>
    </div>

</div>
