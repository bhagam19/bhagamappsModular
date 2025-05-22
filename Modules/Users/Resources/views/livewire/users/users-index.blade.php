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
    @if(auth()->user()->hasPermission('crear-usuarios'))
        <div class="d-block d-md-none mb-3">
            <button 
                class="btn btn-primary btn-sm btn-block" 
                type="button" 
                data-toggle="collapse" 
                data-target="#formCreateUser" 
                aria-expanded="false" 
                aria-controls="formCreateUser"
            >
                Crear Usuario
            </button>
        </div>
    @endif

    {{-- Formulario de creación de usuario--}}
    @if(auth()->user()->hasPermission('crear-usuarios'))
        <div class="collapse d-md-block" id="formCreateUser">
            <form wire:submit.prevent="store" class="d-flex flex-column flex-md-row flex-wrap align-items-md-center mb-4" novalidate>
                @php
                    $fields = [
                        ['model' => 'nombres', 'placeholder' => 'Nombres', 'type' => 'text'],
                        ['model' => 'apellidos', 'placeholder' => 'Apellidos', 'type' => 'text'],
                        ['model' => 'userID', 'placeholder' => 'UserID', 'type' => 'text'],
                        ['model' => 'role_id', 'placeholder' => 'Seleccione rol', 'type' => 'select'],
                        ['model' => 'email', 'placeholder' => 'Email', 'type' => 'email'],
                        ['model' => 'password', 'placeholder' => 'Password', 'type' => 'password'],
                    ];
                    $roles = \Modules\Users\Models\Role::all(); // Obtener roles fuera del loop
                @endphp

                @foreach ($fields as $field)
                    <div class="form-group mr-md-2 flex-grow-1" style="min-width: 120px;">
                        @if($field['type'] === 'select')
                            <select 
                                wire:model="{{ $field['model'] }}" 
                                class="form-control form-control-sm @error($field['model']) is-invalid @enderror"
                                aria-label="{{ $field['placeholder'] }}"
                            >
                                <option value="">{{ $field['placeholder'] }}</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->nombre }}</option>
                                @endforeach
                            </select>
                        @else
                            <input 
                                type="{{ $field['type'] }}" 
                                wire:model="{{ $field['model'] }}" 
                                placeholder="{{ $field['placeholder'] }}" 
                                class="form-control form-control-sm @error($field['model']) is-invalid @enderror"
                                aria-label="{{ $field['placeholder'] }}"
                                autocomplete="off"
                            >
                        @endif
                        @error($field['model'])
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach

                <button type="submit" class="btn btn-primary btn-sm mt-2 mt-md-0">Crear</button>
            </form>
        </div>
    @endif

    @if(auth()->user()->hasPermission('editar-usuarios'))
        <p>Para editar, doble click en el campo que desee modificar.</p>
    @endif

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
                    for="col_{{ $key }}"
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
                    <svg 
                        width="16" height="16" fill="currentColor" 
                        style="transition: transform 0.3s;" 
                        class="ml-2 checkmark" 
                        viewBox="0 0 16 16" 
                        aria-hidden="true" 
                        hidden
                    >
                        <path d="M13.485 3.5L6.75 10.24 3.515 7l-1.02 1.02L6.75 12.3l8-8-1.264-1.265z"/>
                    </svg>
                </label>
            @endforeach
        </div>
    </div>    

    @push('css')
    <style>
        .cursor-pointer { cursor: pointer; }
        .dropdown-menu label:hover {
            background-color: #f0f0f0;
            border-radius: 4px;
        }
        input[type="checkbox"] { cursor: pointer; }
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
                    @if(in_array('nombres', $visibleColumns)) <th>Nombres</th> @endif
                    @if(in_array('apellidos', $visibleColumns)) <th>Apellidos</th> @endif
                    @if(in_array('userID', $visibleColumns)) <th>Número Documento</th> @endif
                    @if(in_array('rol', $visibleColumns)) <th>Rol</th> @endif
                    @if(in_array('email', $visibleColumns)) <th>Email</th> @endif
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        @if(in_array('nombres', $visibleColumns))
                            @if(auth()->user()?->hasPermission('editar-usuarios'))
                                <td>@livewire('users.editar-nombres-user', ['user' => $user], key('nombres-'.$user->id))</td>
                            @else
                                <td>{{ $user->nombres }}</td>
                            @endif
                        @endif

                        @if(in_array('apellidos', $visibleColumns))
                            @if(auth()->user()?->hasPermission('editar-usuarios'))
                                <td>@livewire('users.editar-apellidos-user', ['user' => $user], key('apellidos-'.$user->id))</td>
                            @else
                                <td>{{ $user->apellidos }}</td>
                            @endif
                        @endif

                        @if(in_array('userID', $visibleColumns))
                            @if(auth()->user()?->hasPermission('editar-usuarios'))
                                <td>@livewire('users.editar-userID-user', ['user' => $user], key('userID-'.$user->id))</td>
                            @else
                                <td>{{ $user->userID }}</td>
                            @endif
                        @endif

                        @if(in_array('rol', $visibleColumns))
                            @if(auth()->user()?->hasPermission('editar-usuarios'))
                                <td>@livewire('users.editar-rol-user', ['user' => $user], key('role-'.$user->id))</td>
                            @else
                                <td>{{ $user->role->nombre ?? '' }}</td>
                            @endif
                        @endif

                        @if(in_array('email', $visibleColumns))
                            @if(auth()->user()?->hasPermission('editar-usuarios'))
                                <td>@livewire('users.editar-email-user', ['user' => $user], key('email-'.$user->id))</td>
                            @else
                                <td>{{ $user->email }}</td>
                            @endif
                        @endif

                        <td>
                            @if(auth()->user()->hasPermission('eliminar-usuarios'))
                                <button 
                                    wire:click="delete({{ $user->id }})" 
                                    class="btn btn-sm btn-danger"
                                    onclick="confirm('¿Confirma eliminar?') || event.stopImmediatePropagation()"
                                    aria-label="Eliminar usuario {{ $user->nombres }} {{ $user->apellidos }}"
                                >
                                    Eliminar
                            </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No hay usuarios registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Vista móvil: acordeón --}}
    <div class="d-block d-md-none">
        <div id="accordionMobile">
            @foreach($users as $user)
                <div class="card mb-2">
                    <div 
                        class="card-header p-2 d-flex align-items-center"
                        id="heading{{ $user->id }}"
                        data-toggle="collapse"
                        data-target="#collapse{{ $user->id }}"
                        aria-expanded="false"
                        aria-controls="collapse{{ $user->id }}"
                        style="cursor: pointer;"
                        role="button"
                        tabindex="0"
                        onkeydown="if(event.key === 'Enter' || event.key === ' ') { $('#collapse{{ $user->id }}').collapse('toggle'); event.preventDefault(); }"
                    >
                        <span>{{ $user->id }}. {{ $user->nombres }} {{ $user->apellidos }}</span>

                        @if(auth()->user()->hasPermission('eliminar-usuarios'))
                            <button 
                                wire:click.stop="delete({{ $user->id }})" 
                                class="btn btn-sm btn-danger ml-auto"
                                onclick="confirm('¿Confirma eliminar?') || event.stopImmediatePropagation()"
                                aria-label="Eliminar usuario {{ $user->nombres }} {{ $user->apellidos }}"
                            >
                                Eliminar
                            </button>
                        @endif
                    </div>

                    <div id="collapse{{ $user->id }}" class="collapse" aria-labelledby="heading{{ $user->id }}" data-parent="#accordionMobile">
                        <div class="card-body p-2">
                            <div class="mb-2">
                                <strong>Nombres:</strong> 
                                @livewire('users.editar-nombres-user', ['user' => $user], key('mobile-nombres-'.$user->id))
                            </div>
                            <div class="mb-2">
                                <strong>Apellidos:</strong> 
                                @livewire('users.editar-apellidos-user', ['user' => $user], key('mobile-apellidos-'.$user->id))
                            </div>
                            <div class="mb-2">
                                <strong>Documento:</strong> 
                                @livewire('users.editar-userID-user', ['user' => $user], key('mobile-userID-'.$user->id))
                            </div>
                            <div class="mb-2">
                                <strong>Rol:</strong> 
                                @livewire('users.editar-rol-user', ['user' => $user], key('mobile-rol-'.$user->id))
                            </div>
                            <div class="mb-2">
                                <strong>Email:</strong> 
                                @livewire('users.editar-email-user', ['user' => $user], key('mobile-email-'.$user->id))
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            @if($users->isEmpty())
                <p class="text-center text-muted">No hay usuarios registrados.</p>
            @endif
        </div>
    </div>
</div> 
