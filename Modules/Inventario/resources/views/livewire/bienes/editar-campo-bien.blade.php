<div>
    @if ($editando)
        @if ($tipo === 'textarea')
            <textarea 
                wire:model.lazy="valor"
                wire:blur="actualizar"
                wire:keydown.enter.prevent="actualizar"
                wire:keydown.escape="$set('editando', false)"
                class="form-control"
                rows="2"
                autofocus
            ></textarea>
        @elseif ($tipo === 'select')
            <select 
                wire:model.lazy="valor"
                wire:blur="actualizar"
                wire:change="actualizar"
                class="form-control"
                autofocus
            >
                <option value="">Seleccione...</option>
                @foreach ($opciones as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        @else
            <input 
                type="{{ $tipo }}"
                wire:model.lazy="valor"
                wire:blur="actualizar"
                wire:keydown.enter="actualizar"
                wire:keydown.escape="$set('editando', false)"
                class="form-control"
                autofocus
            >
        @endif
    @else
        <div @class([
            'd-flex justify-content-between align-items-center' => !in_array($campo, ['precio', 'cantidad', 'fechaAdquisicion']),
            'd-flex justify-content-end align-items-center' => in_array($campo, ['precio', 'cantidad', 'fechaAdquisicion']),
        ])>

            {{-- Texto editable con doble click para escritorio --}}
            @php
                $esNumerico = in_array($campo, ['precio', 'cantidad', 'fechaAdquisicion']);
            @endphp

            <span 
                @class([
                    'editable-desktop',
                    'pr-md-3' => $esNumerico,
                ])
                @style([
                    'padding-right: 11rem' => $esNumerico,
                    'cursor: pointer'
                ])
                ondblclick="@this.set('editando', true)"
            >
                @if ($tipo === 'select' && isset($opciones[$valor]))
                    <i class="fas fa-edit fa-xs text-primary d-none d-md-inline"></i></i> {{ $opciones[$valor] }}
                @elseif ($tipo === 'date' && $valor)
                    <i class="fas fa-edit fa-xs text-primary d-none d-md-inline"></i></i> {{ \Carbon\Carbon::parse($valor)->format('d/m/Y') }}
                @elseif (is_null($valor) || $valor === '')
                    <i class="fas fa-edit fa-xs text-primary d-none d-md-inline"></i></i> <span class="text-muted fst-italic">Sin valor</span>
                @elseif ($campo === 'precio')
                    {{ '$' . number_format((float) $valor, 0, ',', '.') }} <i class="fas fa-edit fa-xs text-primary d-none d-md-inline"></i>
                @else
                    <i class="fas fa-edit fa-xs text-primary d-none d-md-inline"></i></i> {{ $valor }}
                @endif
            </span>

            {{-- Botón visible solo en móvil (excepto para el campo precio) --}}
            @if(auth()->user()->hasPermission('editar-bienes'))
                <button 
                    wire:click="$set('editando', true)" 
                    class="btn btn-sm btn-outline-primary d-md-none"
                >
                    Editar
                </button>
            @endif
        </div>
    @endif

</div>
