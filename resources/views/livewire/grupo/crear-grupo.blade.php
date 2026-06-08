<div>
    @if (session()->has('mensaje'))
        <div class="alert alert-success">{{ session('mensaje') }}</div>
    @endif

    <form wire:submit.prevent="guardar">
        @foreach ($nombres as $index => $nombre)
            <div class="form-group d-flex align-items-center">
                <input type="text" class="form-control @error('nombres.' . $index) is-invalid @enderror"
                       placeholder="Nombre del grupo" wire:model.defer="nombres.{{ $index }}">
                @error('nombres.' . $index)
                    <span class="text-danger ml-2">{{ $message }}</span>
                @enderror
                @if (count($nombres) > 1)
                    <button type="button" class="btn btn-danger btn-sm ml-2" wire:click="eliminarInput({{ $index }})">
                        &times;
                    </button>
                @endif
            </div>
        @endforeach

        <div class="mb-2">
            <button type="button" class="btn btn-outline-primary btn-sm" wire:click="agregarInput">
                <i class="fas fa-plus"></i> Agregar otro
            </button>
        </div>

        <div>
            <button type="submit" class="btn btn-success btn-sm">Guardar Grupo(s)</button>
        </div>
    </form>
</div>
