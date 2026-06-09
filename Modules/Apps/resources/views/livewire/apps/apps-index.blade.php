<div>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-th-large mr-2"></i>
                Gestión de Aplicaciones
            </h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Aplicación</th>
                        <th>Slug / Ruta</th>
                        <th class="text-center" style="width:110px">Habilitada</th>
                        <th class="text-center" style="width:90px">Orden</th>
                        <th style="width:120px">Roles asignados</th>
                        <th class="text-center" style="width:80px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($apps as $app)
                        <tr>
                            <td class="align-middle text-muted small">{{ $app->id }}</td>
                            <td class="align-middle">
                                @if ($app->icono)
                                    <i class="{{ $app->icono }} mr-1" style="color: {{ $app->color ?? '#666' }}"></i>
                                @endif
                                <strong>{{ $app->nombre }}</strong>
                                @if ($app->descripcion)
                                    <br><small class="text-muted">{{ Str::limit($app->descripcion, 50) }}</small>
                                @endif
                            </td>
                            <td class="align-middle small">
                                <code>{{ $app->slug }}</code><br>
                                <span class="text-muted">{{ $app->ruta }}</span>
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
                            <td class="align-middle text-center">
                                <span class="badge badge-light border">{{ $app->orden }}</span>
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
                                    class="btn btn-sm btn-outline-secondary"
                                    title="Usuarios directos ({{ $app->user->count() }})">
                                    <i class="fas fa-user"></i>
                                    @if($app->user->count() > 0)
                                        <span class="badge badge-secondary ml-1">{{ $app->user->count() }}</span>
                                    @endif
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No hay aplicaciones registradas.
                                <br><small>Ejecuta <code>php artisan apps:sync</code> para importar los módulos instalados.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
