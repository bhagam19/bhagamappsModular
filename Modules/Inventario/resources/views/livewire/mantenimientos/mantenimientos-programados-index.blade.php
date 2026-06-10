<div>
    @php $user = auth()->user(); @endphp

    <style>
        .table-hover tbody tr:hover:not(.table-warning):not(.table-info):not(.table-success) {
            background-color: rgb(225, 240, 235) !important;
            transition: background-color 0.1s ease-in-out;
        }
        .badge-pendiente  { background-color: #ffc107; color: #212529; }
        .badge-realizado  { background-color: #28a745; color: #fff; }
        .badge-cancelado  { background-color: #6c757d; color: #fff; }
        .badge-preventivo { background-color: #17a2b8; color: #fff; }
        .badge-correctivo { background-color: #dc3545; color: #fff; }
    </style>

    {{-- Mensaje flotante --}}
    <div x-data="{ show: false, mensaje: '', tipo: 'success' }" x-show="show" x-transition
        class="position-fixed top-0 start-50 translate-middle-x mt-1"
        style="z-index: 9999; width: auto; max-width: 90%;"
        @mostrar-mensaje.window="
            mensaje = $event.detail.mensaje;
            tipo    = $event.detail.tipo ?? 'success';
            show    = true;
            setTimeout(() => show = false, 7000);
        ">
        <div :class="{
            'alert alert-success alert-dismissible fade show': tipo === 'success',
            'alert alert-danger alert-dismissible fade show':  tipo === 'error',
            'alert alert-warning alert-dismissible fade show': tipo === 'warning'
        }" role="alert">
            <span x-text="mensaje"></span>
            <button type="button" class="btn-close" @click="show = false" aria-label="Cerrar"></button>
        </div>
    </div>

    {{-- Panel de creación --}}
    @if($creando)
    <div class="card card-body bg-light mb-3 border-success">
        <h6 class="font-weight-bold mb-2">
            <i class="fas fa-plus-circle mr-1 text-success"></i>
            Nuevo Mantenimiento Programado
        </h6>
        <div class="row">
            <div class="form-group col-md-5 mb-2">
                <label class="small font-weight-bold">Bien <span class="text-danger">*</span></label>
                <select wire:model.lazy="formBienId"
                    class="form-control form-control-sm @error('formBienId') is-invalid @enderror">
                    <option value="">— Seleccionar bien —</option>
                    @foreach($bienes as $bid => $bnombre)
                        <option value="{{ $bid }}">{{ $bnombre }}</option>
                    @endforeach
                </select>
                @error('formBienId')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group col-md-3 mb-2">
                <label class="small font-weight-bold">Tipo <span class="text-danger">*</span></label>
                <select wire:model.lazy="formTipo"
                    class="form-control form-control-sm @error('formTipo') is-invalid @enderror">
                    <option value="preventivo">Preventivo</option>
                    <option value="correctivo">Correctivo</option>
                </select>
                @error('formTipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group col-md-4 mb-2">
                <label class="small font-weight-bold">Fecha Programada <span class="text-danger">*</span></label>
                <input wire:model.lazy="formFechaProgramada" type="date"
                    class="form-control form-control-sm @error('formFechaProgramada') is-invalid @enderror">
                @error('formFechaProgramada')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-5 mb-2">
                <label class="small font-weight-bold">Título <span class="text-danger">*</span></label>
                <input wire:model.lazy="formTitulo" type="text"
                    class="form-control form-control-sm @error('formTitulo') is-invalid @enderror"
                    placeholder="Ej. Revisión eléctrica anual">
                @error('formTitulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group col-md-7 mb-2">
                <label class="small font-weight-bold">Descripción</label>
                <input wire:model.lazy="formDescripcion" type="text"
                    class="form-control form-control-sm @error('formDescripcion') is-invalid @enderror"
                    placeholder="Opcional">
                @error('formDescripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="guardar" class="btn btn-success btn-sm">
                <i class="fas fa-save mr-1"></i> Programar
            </button>
            <button wire:click="cancelar" class="btn btn-secondary btn-sm">Cancelar</button>
        </div>
    </div>
    @endif

    {{-- Panel de completar (realizado) --}}
    @if($realizandoId)
    <div class="card card-body bg-light mb-3 border-success">
        <h6 class="font-weight-bold mb-2">
            <i class="fas fa-check-circle mr-1 text-success"></i>
            Marcar como Realizado
        </h6>
        <div class="row align-items-end">
            <div class="form-group col-md-4 mb-2">
                <label class="small font-weight-bold">Fecha de Realización <span class="text-danger">*</span></label>
                <input wire:model.lazy="realizFechaRealizada" type="date"
                    class="form-control form-control-sm @error('realizFechaRealizada') is-invalid @enderror">
                @error('realizFechaRealizada')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-2 d-flex gap-2">
                <button wire:click="confirmarRealizado" class="btn btn-success btn-sm">
                    <i class="fas fa-check mr-1"></i> Confirmar
                </button>
                <button wire:click="cancelar" class="btn btn-secondary btn-sm">Cancelar</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Barra superior --}}
    <div class="row align-items-end mb-3">
        <div class="col-md-4 mb-2 mb-md-0">
            <label class="small font-weight-bold d-block">Buscar bien</label>
            <input wire:model.live.debounce.300ms="busqueda" type="text"
                class="form-control form-control-sm" placeholder="Nombre del bien...">
        </div>
        <div class="col-md-2 mb-2 mb-md-0">
            <label class="small font-weight-bold d-block">Estado</label>
            <select wire:model.lazy="filtroEstado" class="form-control form-control-sm">
                <option value="">Todos</option>
                <option value="pendiente">Pendiente</option>
                <option value="realizado">Realizado</option>
                <option value="cancelado">Cancelado</option>
            </select>
        </div>
        <div class="col-md-2 mb-2 mb-md-0">
            <label class="small font-weight-bold d-block">Tipo</label>
            <select wire:model.lazy="filtroTipo" class="form-control form-control-sm">
                <option value="">Todos</option>
                <option value="preventivo">Preventivo</option>
                <option value="correctivo">Correctivo</option>
            </select>
        </div>
        <div class="col-md-2 mb-2 mb-md-0">
            <label class="small font-weight-bold d-block">Por página</label>
            <select wire:model.lazy="perPage" class="form-control form-control-sm">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end justify-content-md-end mb-2 mb-md-0">
            @can('crear-mantenimientos-programados')
            <button wire:click="abrirFormulario" class="btn btn-success btn-sm w-100">
                <i class="fas fa-plus mr-1"></i> Programar
            </button>
            @endcan
        </div>
    </div>

    {{-- Paginación superior --}}
    <div class="mb-2">{{ $registros->links('pagination::bootstrap-4') }}</div>

    {{-- Tabla --}}
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th style="width:50px">ID</th>
                    <th>
                        <a href="#" wire:click.prevent="sortBy('titulo')" class="text-white text-decoration-none">
                            Título
                            @if($sortField === 'titulo')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 text-muted"></i>
                            @endif
                        </a>
                    </th>
                    <th style="width:180px">Bien</th>
                    <th style="width:110px" class="text-center">
                        <a href="#" wire:click.prevent="sortBy('tipo')" class="text-white text-decoration-none">
                            Tipo
                            @if($sortField === 'tipo')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 text-muted"></i>
                            @endif
                        </a>
                    </th>
                    <th style="width:100px" class="text-center">
                        <a href="#" wire:click.prevent="sortBy('estado')" class="text-white text-decoration-none">
                            Estado
                            @if($sortField === 'estado')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 text-muted"></i>
                            @endif
                        </a>
                    </th>
                    <th style="width:110px" class="text-center">
                        <a href="#" wire:click.prevent="sortBy('fecha_programada')" class="text-white text-decoration-none">
                            F. Programada
                            @if($sortField === 'fecha_programada')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 text-muted"></i>
                            @endif
                        </a>
                    </th>
                    <th style="width:110px" class="text-center">F. Realizada</th>
                    <th style="width:130px" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registros as $reg)
                    @php
                        $rowClass = match($reg->estado) {
                            'realizado' => 'table-success',
                            'cancelado' => 'table-secondary',
                            default     => ($reg->editandoId ?? null) === $reg->id ? 'table-warning' : '',
                        };
                    @endphp
                    <tr class="{{ $editandoId === $reg->id ? 'table-warning' : $rowClass }}">
                        <td class="align-middle">{{ $reg->id }}</td>
                        <td class="align-middle">
                            <span class="font-weight-bold">{{ $reg->titulo }}</span>
                            @if($reg->descripcion)
                                <br><small class="text-muted">{{ Str::limit($reg->descripcion, 80) }}</small>
                            @endif
                        </td>
                        <td class="align-middle small">{{ $reg->bien?->nombre ?? '—' }}</td>
                        <td class="text-center align-middle">
                            <span class="badge badge-{{ $reg->tipo }}">
                                {{ ucfirst($reg->tipo) }}
                            </span>
                        </td>
                        <td class="text-center align-middle">
                            <span class="badge badge-{{ $reg->estado }}">
                                {{ ucfirst($reg->estado) }}
                            </span>
                        </td>
                        <td class="text-center align-middle small">
                            {{ $reg->fecha_programada?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="text-center align-middle small">
                            {{ $reg->fecha_realizada?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="text-center align-middle">
                            @if($reg->estado === 'pendiente')
                                @can('editar-mantenimientos-programados')
                                <button wire:click="iniciarEdicion({{ $reg->id }})"
                                    class="btn btn-warning btn-xs mr-1" title="Editar">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                @endcan
                                @can('cancelar-mantenimientos-programados')
                                <button wire:click="iniciarRealizado({{ $reg->id }})"
                                    class="btn btn-success btn-xs mr-1" title="Marcar como realizado">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button wire:click="cancelarMantenimiento({{ $reg->id }})"
                                    class="btn btn-danger btn-xs" title="Cancelar mantenimiento"
                                    onclick="return confirm('¿Cancelar este mantenimiento?')">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endcan
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Fila de edición inline --}}
                    @if($editandoId === $reg->id)
                    <tr>
                        <td colspan="8" class="p-0">
                            <div class="bg-light border-top border-bottom p-3">
                                <h6 class="font-weight-bold mb-2">
                                    <i class="fas fa-pencil-alt mr-1 text-warning"></i>
                                    Editar Mantenimiento — ID #{{ $reg->id }}
                                </h6>
                                <div class="row">
                                    <div class="form-group col-md-3 mb-2">
                                        <label class="small font-weight-bold">Tipo <span class="text-danger">*</span></label>
                                        <select wire:model.lazy="editTipo"
                                            class="form-control form-control-sm @error('editTipo') is-invalid @enderror">
                                            <option value="preventivo">Preventivo</option>
                                            <option value="correctivo">Correctivo</option>
                                        </select>
                                        @error('editTipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group col-md-4 mb-2">
                                        <label class="small font-weight-bold">Título <span class="text-danger">*</span></label>
                                        <input wire:model.lazy="editTitulo" type="text"
                                            class="form-control form-control-sm @error('editTitulo') is-invalid @enderror">
                                        @error('editTitulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group col-md-3 mb-2">
                                        <label class="small font-weight-bold">Fecha Programada <span class="text-danger">*</span></label>
                                        <input wire:model.lazy="editFechaProgramada" type="date"
                                            class="form-control form-control-sm @error('editFechaProgramada') is-invalid @enderror">
                                        @error('editFechaProgramada')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group col-md-6 mb-2">
                                        <label class="small font-weight-bold">Descripción</label>
                                        <input wire:model.lazy="editDescripcion" type="text"
                                            class="form-control form-control-sm @error('editDescripcion') is-invalid @enderror">
                                        @error('editDescripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mt-1">
                                    <button wire:click="guardarEdicion" class="btn btn-warning btn-sm">
                                        <i class="fas fa-save mr-1"></i> Guardar Cambios
                                    </button>
                                    <button wire:click="cancelar" class="btn btn-secondary btn-sm">Cancelar</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endif

                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">
                            No se encontraron mantenimientos programados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación inferior --}}
    <div class="mt-2">{{ $registros->links('pagination::bootstrap-4') }}</div>
</div>
