<div>
    <style>
        .table-hover tbody tr:hover:not(.bg-warning):not(.bg-danger) {
            background-color: rgb(135, 180, 174) !important;
            transition: background-color 0.1s ease-in-out;
        }
    </style>

    {{-- Mensaje flotante --}}
    <div x-data="{ show: false, mensaje: '', tipo: 'success' }" x-show="show" x-transition
        class="position-fixed top-0 start-50 translate-middle-x mt-1"
        style="z-index: 9999; width: auto; max-width: 90%;"
        @mostrar-mensaje.window="
            mensaje = $event.detail.mensaje;
            tipo = $event.detail.tipo ?? 'success';
            show = true;
            setTimeout(() => show = false, 7000);
        ">
        <div :class="{
            'alert alert-success alert-dismissible fade show': tipo === 'success',
            'alert alert-danger alert-dismissible fade show': tipo === 'error',
            'alert alert-warning alert-dismissible fade show': tipo === 'warning'
        }" role="alert">
            <span x-text="mensaje"></span>
            <button type="button" class="btn-close" @click="show = false" aria-label="Cerrar"></button>
        </div>
    </div>

    {{-- Barra superior --}}
    <div class="row align-items-center mb-3">
        <div class="col-md-4 mb-2 mb-md-0">
            @can('crear-dependencias')
            <button wire:click="iniciarCreacion" class="btn btn-success btn-sm">
                <i class="fas fa-plus mr-1"></i> Nueva Dependencia
            </button>
            @endcan
        </div>
        <div class="col-md-4 mb-2 mb-md-0">
            <input wire:model.live.debounce.300ms="busqueda" type="text"
                class="form-control form-control-sm" placeholder="Buscar dependencia...">
        </div>
        <div class="col-md-4 d-flex justify-content-md-end align-items-center">
            <label class="mr-2 mb-0 text-nowrap">Mostrar</label>
            <select wire:model.lazy="perPage" class="form-control form-control-sm w-auto">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span class="ml-2 text-nowrap">registros</span>
        </div>
    </div>

    {{-- Formulario de creación --}}
    @if($creando)
    <div class="card card-body bg-light mb-3">
        <h6 class="font-weight-bold mb-3">Nueva Dependencia</h6>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Nombre <span class="text-danger">*</span></label>
                    <input wire:model.lazy="nuevoNombre" type="text"
                        class="form-control form-control-sm @error('nuevoNombre') is-invalid @enderror"
                        placeholder="Nombre de la dependencia" autofocus>
                    @error('nuevoNombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Ubicación</label>
                    <select wire:model="nuevoUbicacionId" class="form-control form-control-sm @error('nuevoUbicacionId') is-invalid @enderror">
                        <option value="">— Sin ubicación —</option>
                        @foreach($ubicaciones as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                    @error('nuevoUbicacionId')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Responsable</label>
                    <select wire:model="nuevoUserId" class="form-control form-control-sm @error('nuevoUserId') is-invalid @enderror">
                        <option value="">— Sin responsable —</option>
                        @foreach($usuarios as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                    @error('nuevoUserId')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="guardarNuevo" class="btn btn-success btn-sm">Guardar</button>
            <button wire:click="cancelarCreacion" class="btn btn-secondary btn-sm">Cancelar</button>
        </div>
    </div>
    @endif

    {{-- Confirmación de eliminación --}}
    @if($eliminandoId)
    <div class="alert alert-danger d-flex justify-content-between align-items-center mb-3">
        <span>¿Eliminar <strong>{{ $eliminandoNombre }}</strong>? Esta acción no se puede deshacer.</span>
        <div class="d-flex gap-2 ml-3">
            <button wire:click="eliminar" class="btn btn-danger btn-sm">Sí, eliminar</button>
            <button wire:click="cancelarEliminacion" class="btn btn-secondary btn-sm">Cancelar</button>
        </div>
    </div>
    @endif

    {{-- Paginación superior --}}
    <div class="mb-2">{{ $items->links('pagination::bootstrap-4') }}</div>

    {{-- Tabla --}}
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th style="width:60px">ID</th>
                    <th>
                        <a href="#" wire:click.prevent="sortBy('nombre')" class="text-white text-decoration-none">
                            Nombre
                            @if($sortField === 'nombre')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 text-muted"></i>
                            @endif
                        </a>
                    </th>
                    <th style="width:20%">Ubicación</th>
                    <th style="width:20%">Responsable</th>
                    <th style="width:80px" class="text-center">Bienes</th>
                    <th style="width:130px" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    @if($editandoId === $item->id)
                    <tr class="bg-warning">
                        <td class="align-middle">{{ $item->id }}</td>
                        <td>
                            <input wire:model.lazy="editNombre" type="text"
                                class="form-control form-control-sm @error('editNombre') is-invalid @enderror">
                            @error('editNombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </td>
                        <td>
                            <select wire:model="editUbicacionId" class="form-control form-control-sm @error('editUbicacionId') is-invalid @enderror">
                                <option value="">— Sin ubicación —</option>
                                @foreach($ubicaciones as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                            @error('editUbicacionId')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </td>
                        <td>
                            <select wire:model="editUserId" class="form-control form-control-sm @error('editUserId') is-invalid @enderror">
                                <option value="">— Sin responsable —</option>
                                @foreach($usuarios as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                            @error('editUserId')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </td>
                        <td class="text-center align-middle">{{ $item->bienes_count }}</td>
                        <td class="text-center align-middle">
                            <button wire:click="guardar" class="btn btn-success btn-xs mr-1">
                                <i class="fas fa-check"></i>
                            </button>
                            <button wire:click="cancelarEdicion" class="btn btn-secondary btn-xs">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                    @else
                    <tr>
                        <td class="align-middle">{{ $item->id }}</td>
                        <td class="align-middle">{{ $item->nombre }}</td>
                        <td class="align-middle text-muted">{{ $item->ubicacion?->nombre ?? '—' }}</td>
                        <td class="align-middle text-muted">
                            @if($item->user_id && isset($usuarios[$item->user_id]))
                                {{ $usuarios[$item->user_id] }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            <span class="badge badge-{{ $item->bienes_count > 0 ? 'info' : 'secondary' }}">
                                {{ $item->bienes_count }}
                            </span>
                        </td>
                        <td class="text-center align-middle">
                            @can('editar-dependencias')
                            <button wire:click="editar({{ $item->id }})" class="btn btn-primary btn-xs mr-1" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            @endcan
                            @can('eliminar-dependencias')
                            <button wire:click="confirmarEliminacion({{ $item->id }})" class="btn btn-danger btn-xs" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">No se encontraron dependencias.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación inferior --}}
    <div class="mt-2">{{ $items->links('pagination::bootstrap-4') }}</div>
</div>
