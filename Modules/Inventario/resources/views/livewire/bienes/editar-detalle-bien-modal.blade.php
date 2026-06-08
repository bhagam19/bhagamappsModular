<div>
    <h5 class="mb-3">Detalles de: <strong>{{ $nombreBien }}</strong></h5>

    <form wire:submit.prevent="actualizar">
        @foreach (['car_especial', 'marca', 'color', 'tamano', 'material', 'otra'] as $campo)
            <div class="mb-2">
                <label class="small text-muted">{{ ucfirst(str_replace('_', ' ', $campo)) }}</label>
                <input type="text" wire:model.defer="detalle.{{ $campo }}" class="form-control form-control-sm"
                    placeholder="{{ ucfirst(str_replace('_', ' ', $campo)) }}">
            </div>
        @endforeach

        <button type="submit" class="btn btn-sm btn-primary mt-2">Guardar cambios</button>
    </form>
</div>
