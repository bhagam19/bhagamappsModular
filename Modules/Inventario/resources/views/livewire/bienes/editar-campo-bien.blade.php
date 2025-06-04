<div class="mb-1">
    @if ($editando)
        @if ($tipo === 'textarea')
            <textarea wire:model.lazy="valor" wire:blur="actualizar" wire:keydown.enter.prevent="actualizar"
                wire:keydown.escape="$set('editando', false)" class="form-control form-control-sm" rows="2" autofocus></textarea>
        @elseif ($tipo === 'select')
            <select wire:model.lazy="valor" wire:blur="actualizar" wire:change="actualizar"
                class="form-control form-control-sm" autofocus>
                <option value="">Seleccione...</option>
                @foreach ($opciones as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        @else
            <input type="{{ $tipo }}" wire:model.lazy="valor" wire:blur="actualizar"
                wire:keydown.enter="actualizar" wire:keydown.escape="$set('editando', false)"
                class="form-control form-control-sm" autofocus>
        @endif
    @else
        <div class="d-flex align-items-center justify-content-between flex-nowrap w-100 py-0 overflow-hidden">
            <div class="text-truncate me-2" style="white-space: nowrap;">

                {{-- Etiqueta estilo badge (solo en móvil) --}}
                <span class="badge badge-light border border-primary text-muted small px-2 py-1 d-sm-none">
                    {{ \Illuminate\Support\Str::headline(str_replace('_id', '', $campo)) }}:
                </span>

                {{-- Texto editable con doble click para escritorio --}}
                <span class="px-2 small text-dark editable-desktop d-none d-sm-inline"
                    ondblclick="@this.set('editando', true)" style="cursor: pointer;">
                    @if ($tipo === 'select' && isset($opciones[$valor]))
                        {{ $opciones[$valor] }}
                    @elseif ($tipo === 'date' && $valor)
                        {{ \Carbon\Carbon::parse($valor)->format('d/m/Y') }}
                    @elseif (is_null($valor) || $valor === '')
                        <span class="text-muted fst-italic"> </span>
                    @elseif ($campo === 'precio')
                        {{ '$' . number_format((float) $valor, 0, ',', '.') }}
                    @else
                        {{ $valor }}
                    @endif
                </span>

                {{-- Texto no editable para móvil --}}
                <span class="px-2 small text-dark d-sm-none">
                    @if ($tipo === 'select' && isset($opciones[$valor]))
                        {{ $opciones[$valor] }}
                    @elseif ($tipo === 'date' && $valor)
                        {{ \Carbon\Carbon::parse($valor)->format('d/m/Y') }}
                    @elseif (is_null($valor) || $valor === '')
                        <span class="text-muted fst-italic"> </span>
                    @elseif ($campo === 'precio')
                        {{ '$' . number_format((float) $valor, 0, ',', '.') }}
                    @else
                        {{ $valor }}
                    @endif
                </span>

                @if ($this->campoTieneCambioPendiente())
                    <i class="fas fa-hourglass-half text-info ml-1"
                        title="Este campo tiene un cambio pendiente de aprobación"></i>
                @endif

            </div>

            {{-- Botón editar visible solo en móvil --}}
            @if (auth()->user()->hasPermission('editar-bienes'))
                <button wire:click="$set('editando', true)" class="btn btn-sm text-primary p-0 ms-2 ">
                    <i class="fas fa-edit"></i>
                </button>
            @endif
        </div>

    @endif
</div>
