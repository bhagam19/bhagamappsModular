<div>
    <div class="card card-body bg-light border-info">
        <h6 class="font-weight-bold mb-1">
            <i class="fas fa-map-marker-alt mr-1 text-info"></i>
            Cambiar Ubicación — {{ $bien->nombre }}
        </h6>

        @if($bien->ubicacionActual?->ubicacionDestino)
        <p class="mb-2 text-muted small">
            Ubicación actual:
            <strong>{{ $bien->ubicacionActual->ubicacionDestino->nombre }}</strong>
            (desde {{ \Carbon\Carbon::parse($bien->ubicacionActual->fecha_movimiento)->format('d/m/Y') }})
        </p>
        @else
        <p class="mb-2 text-muted small">Sin ubicación registrada aún.</p>
        @endif

        <div class="row">
            <div class="form-group col-md-4 mb-2">
                <label class="small font-weight-bold">Nueva Ubicación <span class="text-danger">*</span></label>
                <select wire:model.lazy="nuevaUbicacionId"
                    class="form-control form-control-sm @error('nuevaUbicacionId') is-invalid @enderror">
                    <option value="">— Seleccionar ubicación —</option>
                    @foreach($ubicaciones as $uid => $unombre)
                        <option value="{{ $uid }}">{{ $unombre }}</option>
                    @endforeach
                </select>
                @error('nuevaUbicacionId')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group col-md-3 mb-2">
                <label class="small font-weight-bold">Fecha de Movimiento <span class="text-danger">*</span></label>
                <input wire:model.lazy="fechaMovimiento" type="date"
                    class="form-control form-control-sm @error('fechaMovimiento') is-invalid @enderror">
                @error('fechaMovimiento')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group col-md-5 mb-2">
                <label class="small font-weight-bold">Observaciones</label>
                <input wire:model.lazy="observaciones" type="text"
                    class="form-control form-control-sm @error('observaciones') is-invalid @enderror"
                    placeholder="Opcional">
                @error('observaciones')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mt-1">
            <button wire:click="guardar" class="btn btn-info btn-sm">
                <i class="fas fa-save mr-1"></i> Guardar Cambio
            </button>
        </div>
    </div>
</div>
