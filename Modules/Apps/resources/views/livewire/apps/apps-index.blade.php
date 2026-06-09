<div>
    {{-- Mensajes de sesión --}}
    @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Formulario de creación --}}
    @if (auth()->user()->hasPermission('crear-apps'))
        <div class="mb-3">
            {{-- Botón para móviles --}}
            <div class="d-block d-md-none mb-2">
                <button class="btn btn-primary btn-sm btn-block" type="button"
                        data-toggle="collapse" data-target="#formCreateApp"
                        aria-expanded="false" aria-controls="formCreateApp">
                    <i class="fas fa-plus mr-1"></i> Nueva Aplicación
                </button>
            </div>

            <div class="collapse d-md-block" id="formCreateApp">
                <form wire:submit.prevent="store"
                      class="bg-light p-3 rounded border" novalidate>
                    <div class="d-flex flex-wrap gap-2 align-items-start">

                        <div class="form-group mb-2" style="min-width: 160px; flex: 1;">
                            <input type="text" wire:model="nombre" placeholder="Nombre *"
                                   class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                                   autocomplete="off">
                            @error('nombre') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-2" style="min-width: 130px; flex: 1;">
                            <input type="text" wire:model="slug" placeholder="Slug (único)"
                                   class="form-control form-control-sm @error('slug') is-invalid @enderror"
                                   autocomplete="off">
                            @error('slug') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-2" style="min-width: 160px; flex: 2;">
                            <input type="text" wire:model="ruta" placeholder="Ruta * (ej: /mi-app)"
                                   class="form-control form-control-sm @error('ruta') is-invalid @enderror"
                                   autocomplete="off">
                            @error('ruta') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-2" style="min-width: 160px; flex: 2;">
                            <input type="text" wire:model="descripcion" placeholder="Descripción"
                                   class="form-control form-control-sm @error('descripcion') is-invalid @enderror"
                                   autocomplete="off">
                            @error('descripcion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-2" style="min-width: 130px; flex: 1;">
                            <input type="text" wire:model="icono" placeholder="Icono (fas fa-...)"
                                   class="form-control form-control-sm @error('icono') is-invalid @enderror"
                                   autocomplete="off">
                            @error('icono') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-2 d-flex align-items-center" style="min-width: 90px;">
                            <label class="mb-0 mr-1 small text-muted">Color</label>
                            <input type="color" wire:model="color"
                                   class="form-control form-control-sm p-0 @error('color') is-invalid @enderror"
                                   style="width: 40px; height: 31px; cursor: pointer;">
                            @error('color') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-2" style="width: 70px;">
                            <input type="number" wire:model="orden" placeholder="Orden"
                                   class="form-control form-control-sm @error('orden') is-invalid @enderror"
                                   min="0" max="999">
                            @error('orden') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-2 align-self-end">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-save mr-1"></i> Crear
                            </button>
                        </div>
                    </div>
                    <div class="text-muted mt-1">
                        <small>La aplicación se crea deshabilitada. Habilitarla una vez lista.</small>
                    </div>
                </form>
            </div>
        </div>

    @endif

    @if (auth()->user()->hasPermission('editar-apps'))
        <div class="text-muted mb-2">
            <small>Doble clic en un campo para editar.</small>
        </div>
    @endif

    {{-- Tabla de aplicaciones --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-th-large mr-2"></i>
                Gestión de Aplicaciones
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width:40px">#</th>
                            <th style="min-width:150px">Nombre</th>
                            <th style="min-width:160px">Slug / Ruta</th>
                            <th style="min-width:180px">Descripción</th>
                            <th style="min-width:140px">Icono / Color</th>
                            <th class="text-center" style="width:80px">Orden</th>
                            <th class="text-center" style="width:100px">Habilitada</th>
                            <th style="min-width:120px">Roles</th>
                            <th class="text-center" style="width:130px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($apps as $app)
                            <tr>
                                <td class="align-middle text-muted small">{{ $app->id }}</td>

                                <td class="align-middle">
                                    @if (auth()->user()->hasPermission('editar-apps'))
                                        @livewire('apps.editar-nombre-app', ['app' => $app], key('nombre-' . $app->id))
                                    @else
                                        <strong>{{ $app->nombre }}</strong>
                                    @endif
                                </td>

                                <td class="align-middle small">
                                    @if (auth()->user()->hasPermission('editar-apps'))
                                        @livewire('apps.editar-slug-app', ['app' => $app], key('slug-' . $app->id))
                                        @livewire('apps.editar-ruta-app', ['app' => $app], key('ruta-' . $app->id))
                                    @else
                                        <code>{{ $app->slug }}</code><br>
                                        <span class="text-muted">{{ $app->ruta }}</span>
                                    @endif
                                </td>

                                <td class="align-middle small">
                                    @if (auth()->user()->hasPermission('editar-apps'))
                                        @livewire('apps.editar-descripcion-app', ['app' => $app], key('desc-' . $app->id))
                                    @else
                                        <small class="text-muted">{{ Str::limit($app->descripcion, 60) }}</small>
                                    @endif
                                </td>

                                <td class="align-middle small">
                                    @if (auth()->user()->hasPermission('editar-apps'))
                                        @livewire('apps.editar-icono-app', ['app' => $app], key('icono-' . $app->id))
                                        @livewire('apps.editar-color-app', ['app' => $app], key('color-' . $app->id))
                                    @else
                                        @if ($app->icono)
                                            <i class="{{ $app->icono }}" style="color: {{ $app->color ?? '#666' }}"></i>
                                            <small class="text-muted ml-1">{{ $app->icono }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    @endif
                                </td>

                                <td class="align-middle text-center">
                                    @if (auth()->user()->hasPermission('editar-apps'))
                                        @livewire('apps.editar-orden-app', ['app' => $app], key('orden-' . $app->id))
                                    @else
                                        <span class="badge badge-light border">{{ $app->orden }}</span>
                                    @endif
                                </td>

                                <td class="align-middle text-center">
                                    <button
                                        wire:click="toggleHabilitada({{ $app->id }})"
                                        class="btn btn-sm {{ $app->habilitada ? 'btn-success' : 'btn-secondary' }}"
                                        title="{{ $app->habilitada ? 'Deshabilitar' : 'Habilitar' }}">
                                        <i class="fas {{ $app->habilitada ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ $app->habilitada ? 'Sí' : 'No' }}
                                    </button>
                                </td>

                                <td class="align-middle small">
                                    @forelse ($app->roles as $role)
                                        <span class="badge badge-info mr-1">{{ $role->nombre }}</span>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </td>

                                <td class="align-middle text-center">
                                    <button
                                        wire:click="abrirModalRoles({{ $app->id }})"
                                        class="btn btn-sm btn-outline-primary mb-1"
                                        title="Gestionar roles">
                                        <i class="fas fa-user-tag"></i>
                                    </button>
                                    <button
                                        wire:click="abrirModalUsuarios({{ $app->id }})"
                                        class="btn btn-sm btn-outline-secondary mb-1"
                                        title="Usuarios directos ({{ $app->user->count() }})">
                                        <i class="fas fa-user"></i>
                                        @if($app->user->count() > 0)
                                            <span class="badge badge-secondary ml-1">{{ $app->user->count() }}</span>
                                        @endif
                                    </button>
                                    @if (auth()->user()->hasPermission('eliminar-apps'))
                                        <button
                                            wire:click="delete({{ $app->id }})"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="confirm('¿Confirma eliminar la aplicación «{{ addslashes($app->nombre) }}»?') || event.stopImmediatePropagation()"
                                            title="Eliminar aplicación">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No hay aplicaciones registradas.
                                    <br><small>Ejecuta <code>php artisan apps:sync</code> para importar los módulos instalados.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal gestión de usuarios directos (app_user) --}}
    @if ($modalUsuariosVisible)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-user mr-1"></i>
                            Usuarios con acceso directo a esta aplicación
                        </h5>
                        <button wire:click="cerrarModalUsuarios" type="button" class="close">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                        <p class="text-muted small mb-3">
                            Los usuarios seleccionados verán esta aplicación independientemente de su rol.
                        </p>
                        @foreach ($users as $user)
                            <div class="form-check mb-2">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="user_{{ $user->id }}"
                                    wire:model="usuariosSeleccionados"
                                    value="{{ $user->id }}">
                                <label class="form-check-label" for="user_{{ $user->id }}">
                                    {{ $user->apellidos }}, {{ $user->nombres }}
                                    @if ($user->role)
                                        <small class="text-muted">— {{ $user->role->nombre }}</small>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button wire:click="cerrarModalUsuarios" type="button" class="btn btn-secondary">
                            Cancelar
                        </button>
                        <button wire:click="guardarUsuarios" type="button" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Guardar usuarios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal gestión de roles --}}
    @if ($modalVisible)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-user-tag mr-1"></i>
                            Roles con acceso a esta aplicación
                        </h5>
                        <button wire:click="cerrarModal" type="button" class="close">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @foreach ($roles as $role)
                            <div class="form-check mb-2">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="role_{{ $role->id }}"
                                    wire:model="rolesSeleccionados"
                                    value="{{ $role->id }}">
                                <label class="form-check-label" for="role_{{ $role->id }}">
                                    {{ $role->nombre }}
                                    @if ($role->descripcion)
                                        <small class="text-muted">— {{ $role->descripcion }}</small>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button wire:click="cerrarModal" type="button" class="btn btn-secondary">
                            Cancelar
                        </button>
                        <button wire:click="guardarRoles" type="button" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Guardar roles
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
