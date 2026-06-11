<div>
    @if ($confirmado)
        <div class="alert alert-success alert-dismissible fade show py-1 px-2 mb-0" role="alert">
            <small>Contraseña restablecida.</small>
            <button type="button" class="close py-1" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @else
        <button type="button"
            class="btn btn-sm btn-warning"
            data-toggle="modal"
            data-target="#modalPassword{{ $user->id }}">
            <i class="fas fa-key"></i>
        </button>

        <div class="modal fade" id="modalPassword{{ $user->id }}" tabindex="-1" role="dialog"
            aria-labelledby="modalPasswordLabel{{ $user->id }}" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalPasswordLabel{{ $user->id }}">
                            Restablecer contraseña — {{ $user->nombres }} {{ $user->apellidos }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        @if ($errors->has('nuevaPassword'))
                            <div class="alert alert-danger py-1">
                                {{ $errors->first('nuevaPassword') }}
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="nuevaPassword{{ $user->id }}">Nueva contraseña</label>
                            <div class="input-group">
                                <input
                                    type="{{ $mostrarPassword ? 'text' : 'password' }}"
                                    id="nuevaPassword{{ $user->id }}"
                                    wire:model="nuevaPassword"
                                    class="form-control @error('nuevaPassword') is-invalid @enderror"
                                    autocomplete="new-password"
                                    placeholder="Mínimo 8 caracteres">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary"
                                        wire:click="$toggle('mostrarPassword')">
                                        <i class="fas {{ $mostrarPassword ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info"
                                        wire:click="generarPassword"
                                        title="Generar contraseña aleatoria">
                                        <i class="fas fa-random"></i>
                                    </button>
                                </div>
                            </div>
                            @if ($passwordVisible)
                                <small class="text-muted">
                                    Contraseña generada: <strong>{{ $passwordVisible }}</strong>
                                </small>
                            @endif
                        </div>

                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input"
                                id="forzarCambio{{ $user->id }}"
                                wire:model="forzarCambio">
                            <label class="form-check-label" for="forzarCambio{{ $user->id }}">
                                Forzar cambio al siguiente ingreso
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-warning"
                            wire:click="restablecer"
                            onclick="confirm('¿Confirma restablecer la contraseña?') || event.stopImmediatePropagation()">
                            Restablecer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
