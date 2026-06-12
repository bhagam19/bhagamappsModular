<div>

    @push('css')
        <style>
            .cursor-pointer { cursor: pointer; }
            .dropdown-menu label:hover {
                background-color: #f0f0f0;
                border-radius: 4px;
            }
            input[type="checkbox"] { cursor: pointer; }
            th a { white-space: nowrap; }
        </style>
    @endpush

    {{-- Mensajes de sesión --}}
    @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- ================================================================
         BLOQUE 0: Formulario de creación
    ================================================================ --}}
    @if (auth()->user()->hasPermission('crear-usuarios'))
        {{-- Botón para móviles --}}
        <div class="d-block d-md-none mb-2">
            <button class="btn btn-primary btn-sm btn-block" type="button" data-toggle="collapse"
                data-target="#formCreateUser" aria-expanded="false" aria-controls="formCreateUser">
                Crear Usuario
            </button>
        </div>

        {{-- Formulario de creación --}}
        <div class="collapse d-md-block mb-3" id="formCreateUser">
            <form wire:submit.prevent="store"
                class="d-flex flex-wrap align-items-start bg-light p-3 rounded border gap-2" novalidate>

                @php
                    $fields = [
                        ['model' => 'nombres',  'placeholder' => 'Nombres',   'type' => 'text'],
                        ['model' => 'apellidos','placeholder' => 'Apellidos',  'type' => 'text'],
                        ['model' => 'userID',   'placeholder' => 'UserID',     'type' => 'text'],
                        ['model' => 'role_id',  'placeholder' => 'Seleccione rol', 'type' => 'select'],
                        ['model' => 'email',    'placeholder' => 'Email',      'type' => 'email'],
                        ['model' => 'password', 'placeholder' => 'Password',   'type' => 'password'],
                    ];
                @endphp

                @foreach ($fields as $field)
                    <div class="form-group mb-2 flex-grow-1" style="min-width: 150px;">
                        @if ($field['type'] === 'select')
                            <select wire:model="{{ $field['model'] }}"
                                class="form-control form-control-sm @error($field['model']) is-invalid @enderror"
                                aria-label="{{ $field['placeholder'] }}">
                                <option value="">{{ $field['placeholder'] }}</option>
                                @foreach ($rolesDisponibles as $roleId => $roleName)
                                    <option value="{{ $roleId }}">{{ $roleName }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="{{ $field['type'] }}" wire:model="{{ $field['model'] }}"
                                placeholder="{{ $field['placeholder'] }}"
                                class="form-control form-control-sm @error($field['model']) is-invalid @enderror"
                                autocomplete="off">
                        @endif
                        @error($field['model'])
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach

                <div class="align-self-end">
                    <button type="submit" class="btn btn-success btn-sm">Crear</button>
                </div>
            </form>
        </div>
    @endif

    {{-- ================================================================
         BLOQUE 1: Búsqueda y filtros reactivos (USR-001, USR-002, USR-003)
    ================================================================ --}}
    <div class="row mb-3">
        {{-- Búsqueda por nombre / apellido / email --}}
        <div class="col-12 col-md-4 mb-2 mb-md-0">
            <input wire:model.live.debounce.300ms="busqueda"
                type="text"
                class="form-control form-control-sm"
                placeholder="Buscar por nombre, apellido o email…"
                autocomplete="off">
        </div>

        {{-- Filtro por rol --}}
        <div class="col-6 col-md-4 mb-2 mb-md-0">
            <select wire:model.live="filtroRol" class="form-control form-control-sm">
                <option value="">Todos los roles</option>
                @foreach ($rolesDisponibles as $roleId => $roleName)
                    <option value="{{ $roleId }}">{{ $roleName }}</option>
                @endforeach
            </select>
        </div>

        {{-- Filtro por estado --}}
        <div class="col-6 col-md-4">
            <select wire:model.live="filtroEstado" class="form-control form-control-sm">
                <option value="todos">Todos los estados</option>
                <option value="activos">Activos</option>
                <option value="bloqueados">Bloqueados</option>
            </select>
        </div>
    </div>

    {{-- ================================================================
         BLOQUE 2: Controles de tabla (perPage, paginación, columnas)
    ================================================================ --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2 flex-wrap">

        {{-- Selector cantidad por página --}}
        <div class="form-inline">
            <label for="perPage" class="mr-2 mb-0">Mostrar</label>
            <select wire:model.live="perPage" id="perPage" class="form-control form-control-sm" style="width:auto;">
                <option value="10">10</option>
                <option value="25">25</option>
            </select>
            <span class="ml-2">registros</span>
        </div>

        {{-- Paginación superior --}}
        <div class="d-md-block d-flex overflow-auto justify-content-center">
            {{ $users->links('pagination::bootstrap-4') }}
        </div>

        {{-- Mostrar/Ocultar columnas --}}
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center"
                type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <svg width="16" height="16" fill="currentColor" class="mr-2" viewBox="0 0 16 16">
                    <path d="M3 9h10V7H3v2zm0 4h10v-2H3v2zm0-8h10V3H3v2z" />
                </svg>
                Columnas
            </button>
            <div class="dropdown-menu dropdown-menu-right p-3 shadow-sm" style="min-width: 200px;">
                @foreach ($availableColumns as $key => $label)
                    <label class="d-flex align-items-center mb-2" for="col_{{ $key }}" style="user-select:none;">
                        <input class="mr-2" type="checkbox" style="transform: scale(1.2);"
                            id="col_{{ $key }}" wire:click="toggleColumn('{{ $key }}')"
                            @if (in_array($key, $visibleColumns)) checked @endif>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    @if (auth()->user()->hasPermission('editar-usuarios'))
        <div class="text-muted mb-2">
            <small>Doble click en un campo para editar.</small>
        </div>
    @endif

    {{-- ================================================================
         TABLA ESCRITORIO (USR-004: headers ordenables)
    ================================================================ --}}
    @php
        // Macro inline para icono de ordenamiento
        $sortIcon = function(string $field) use ($sortField, $sortDirection): string {
            if ($sortField === $field) {
                $icon = $sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
                return "<i class=\"fas {$icon} ml-1\"></i>";
            }
            return "<i class=\"fas fa-sort ml-1 text-muted\"></i>";
        };
    @endphp

    <div class="table-responsive d-none d-md-block">
        <table class="table table-striped table-hover table-sm">
            <thead class="thead-dark">
                <tr>
                    {{-- ID siempre visible y ordenable --}}
                    <th>
                        <a href="#" wire:click.prevent="sortBy('id')" class="text-white text-decoration-none">
                            ID {!! $sortIcon('id') !!}
                        </a>
                    </th>

                    @if (in_array('nombres', $visibleColumns))
                        <th>
                            <a href="#" wire:click.prevent="sortBy('nombres')" class="text-white text-decoration-none">
                                Nombres {!! $sortIcon('nombres') !!}
                            </a>
                        </th>
                    @endif

                    @if (in_array('apellidos', $visibleColumns))
                        <th>
                            <a href="#" wire:click.prevent="sortBy('apellidos')" class="text-white text-decoration-none">
                                Apellidos {!! $sortIcon('apellidos') !!}
                            </a>
                        </th>
                    @endif

                    @if (in_array('userID', $visibleColumns))
                        <th>
                            <a href="#" wire:click.prevent="sortBy('userID')" class="text-white text-decoration-none">
                                No. Documento {!! $sortIcon('userID') !!}
                            </a>
                        </th>
                    @endif

                    @if (in_array('rol', $visibleColumns))
                        <th>
                            <a href="#" wire:click.prevent="sortBy('rol')" class="text-white text-decoration-none">
                                Rol {!! $sortIcon('rol') !!}
                            </a>
                        </th>
                    @endif

                    @if (in_array('email', $visibleColumns))
                        <th>
                            <a href="#" wire:click.prevent="sortBy('email')" class="text-white text-decoration-none">
                                Email {!! $sortIcon('email') !!}
                            </a>
                        </th>
                    @endif

                    @if (in_array('estado', $visibleColumns))
                        <th>
                            <a href="#" wire:click.prevent="sortBy('bloqueado')" class="text-white text-decoration-none">
                                Estado {!! $sortIcon('bloqueado') !!}
                            </a>
                        </th>
                    @endif

                    @if (in_array('created_at', $visibleColumns))
                        <th>
                            <a href="#" wire:click.prevent="sortBy('created_at')" class="text-white text-decoration-none">
                                Creación {!! $sortIcon('created_at') !!}
                            </a>
                        </th>
                    @endif

                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="align-middle">{{ $user->id }}</td>

                        @if (in_array('nombres', $visibleColumns))
                            @if (auth()->user()?->hasPermission('editar-usuarios'))
                                <td>@livewire('user.editar-nombres-user', ['user' => $user], key('nombres-' . $user->id))</td>
                            @else
                                <td class="align-middle">{{ $user->nombres }}</td>
                            @endif
                        @endif

                        @if (in_array('apellidos', $visibleColumns))
                            @if (auth()->user()?->hasPermission('editar-usuarios'))
                                <td>@livewire('user.editar-apellidos-user', ['user' => $user], key('apellidos-' . $user->id))</td>
                            @else
                                <td class="align-middle">{{ $user->apellidos }}</td>
                            @endif
                        @endif

                        @if (in_array('userID', $visibleColumns))
                            @if (auth()->user()?->hasPermission('editar-usuarios'))
                                <td>@livewire('user.editar-userID-user', ['user' => $user], key('userID-' . $user->id))</td>
                            @else
                                <td class="align-middle">{{ $user->userID }}</td>
                            @endif
                        @endif

                        @if (in_array('rol', $visibleColumns))
                            @if (auth()->user()?->hasPermission('editar-usuarios'))
                                <td>@livewire('user.editar-rol-user', ['user' => $user], key('role-' . $user->id))</td>
                            @else
                                <td class="align-middle">{{ $user->role->nombre ?? '—' }}</td>
                            @endif
                        @endif

                        @if (in_array('email', $visibleColumns))
                            @if (auth()->user()?->hasPermission('editar-usuarios'))
                                <td>@livewire('user.editar-email-user', ['user' => $user], key('email-' . $user->id))</td>
                            @else
                                <td class="align-middle">{{ $user->email }}</td>
                            @endif
                        @endif

                        @if (in_array('estado', $visibleColumns))
                            <td class="align-middle">
                                @if ($user->bloqueado)
                                    <span class="badge badge-danger">Bloqueado</span>
                                @else
                                    <span class="badge badge-success">Activo</span>
                                @endif
                            </td>
                        @endif

                        @if (in_array('created_at', $visibleColumns))
                            <td class="align-middle text-muted small">
                                {{ $user->created_at?->format('d/m/Y') ?? '—' }}
                            </td>
                        @endif

                        <td class="align-middle">
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                @if (auth()->user()->hasPermission('restablecer-passwords'))
                                    @livewire('user.gestion-password-user', ['user' => $user], key('pass-' . $user->id))
                                @endif
                                @if (auth()->user()->hasPermission('bloquear-usuarios') || auth()->user()->hasPermission('desbloquear-usuarios'))
                                    @livewire('user.gestion-estado-user', ['user' => $user], key('estado-' . $user->id))
                                @endif
                                @if (auth()->user()->hasPermission('eliminar-usuarios'))
                                    <button wire:click="delete({{ $user->id }})" class="btn btn-sm btn-danger"
                                        onclick="confirm('¿Confirma eliminar?') || event.stopImmediatePropagation()"
                                        aria-label="Eliminar usuario {{ $user->nombres }} {{ $user->apellidos }}">
                                        Eliminar
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-3">No se encontraron usuarios.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ================================================================
         VISTA MÓVIL: acordeón (USR-006)
    ================================================================ --}}
    <div class="d-block d-md-none">
        <div id="accordionMobile">
            @forelse ($users as $user)
                <div class="card mb-2">
                    <div class="card-header p-2 d-flex align-items-center"
                        id="heading{{ $user->id }}"
                        data-toggle="collapse" data-target="#collapse{{ $user->id }}"
                        aria-expanded="false" aria-controls="collapse{{ $user->id }}"
                        style="cursor: pointer;" role="button" tabindex="0"
                        onkeydown="if(event.key === 'Enter' || event.key === ' ') { $('#collapse{{ $user->id }}').collapse('toggle'); event.preventDefault(); }">
                        <div>
                            <span class="font-weight-bold">{{ $user->id }}. {{ $user->nombres }} {{ $user->apellidos }}</span>
                            <br>
                            <small class="text-muted">{{ $user->role->nombre ?? '—' }}</small>
                            <span class="ml-2 badge {{ $user->bloqueado ? 'badge-danger' : 'badge-success' }} badge-sm">
                                {{ $user->bloqueado ? 'Bloqueado' : 'Activo' }}
                            </span>
                        </div>
                        <div class="ml-auto d-flex gap-1">
                            @if (auth()->user()->hasPermission('restablecer-passwords'))
                                @livewire('user.gestion-password-user', ['user' => $user], key('mpass-' . $user->id))
                            @endif
                            @if (auth()->user()->hasPermission('bloquear-usuarios') || auth()->user()->hasPermission('desbloquear-usuarios'))
                                @livewire('user.gestion-estado-user', ['user' => $user], key('mestado-' . $user->id))
                            @endif
                            @if (auth()->user()->hasPermission('eliminar-usuarios'))
                                <button wire:click.stop="delete({{ $user->id }})" class="btn btn-sm btn-danger"
                                    onclick="confirm('¿Confirma eliminar?') || event.stopImmediatePropagation()"
                                    aria-label="Eliminar usuario {{ $user->nombres }} {{ $user->apellidos }}">
                                    Eliminar
                                </button>
                            @endif
                        </div>
                    </div>

                    <div id="collapse{{ $user->id }}" class="collapse"
                        aria-labelledby="heading{{ $user->id }}" data-parent="#accordionMobile">
                        <div class="card-body p-2">
                            <div class="mb-2">
                                <strong>Nombres:</strong>
                                <span>{{ $user->nombres }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Apellidos:</strong>
                                <span>{{ $user->apellidos }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Documento:</strong>
                                <span>{{ $user->userID }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Rol:</strong>
                                <span>{{ $user->role->nombre ?? '—' }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Email:</strong>
                                <span>{{ $user->email }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Creado:</strong>
                                <span class="text-muted">{{ $user->created_at?->format('d/m/Y') ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-muted">No se encontraron usuarios.</p>
            @endforelse
        </div>
    </div>

    {{-- Paginación inferior --}}
    <div class="mt-3">
        <div class="d-md-block d-flex overflow-auto">
            <div class="mx-auto">
                {{ $users->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

</div>
