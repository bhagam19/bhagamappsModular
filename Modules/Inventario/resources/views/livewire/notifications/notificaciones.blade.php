<style>
    /* Contenedor flotante fijo a la derecha */
    #accordionFlotante {
        position: fixed;
        top: 80px;
        /* Ajusta según tu header */
        right: 20px;
        width: 350px;
        /* Ancho fijo o máximo */
        max-height: 80vh;
        /* Máximo alto para scroll interno */
        overflow-y: auto;
        z-index: 1050;
        /* Por encima de otros elementos */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 6px;
        background-color: #fff;
    }
</style>

<div>
    @if ($cambiosPendientes->isEmpty())
        <div class="alert alert-info" style="max-width: 350px; position: fixed; top: 80px; right: 20px; z-index:1050;">
            No hay cambios pendientes por aprobar.
        </div>
    @else
        <div id="accordionFlotante" class="alert alert-warning p-0">
            <div id="accordionGeneral">
                <div class="card mb-0">
                    <div class="card-header p-2" id="headingGeneral">
                        <h5 class="mb-0 d-flex justify-content-between align-items-center">
                            <button class="btn btn-link text-dark" data-toggle="collapse" data-target="#collapseGeneral"
                                aria-expanded="false" aria-controls="collapseGeneral"
                                style="width: 100%; text-align: left;">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Tienes {{ $cambiosPendientes->count() }} cambios pendientes por aprobar
                            </button>
                        </h5>
                    </div>

                    <div id="collapseGeneral" class="collapse" aria-labelledby="headingGeneral"
                        data-parent="#accordionGeneral">
                        <div class="card-body p-0" style="max-height: 60vh; overflow-y: auto;">
                            <div id="accordionIndividual">
                                @foreach ($cambiosPendientes as $index => $cambio)
                                    <div class="card mb-1">
                                        <div class="card-header p-2" id="heading{{ $index }}">
                                            <h6 class="mb-0 d-flex justify-content-between align-items-center">
                                                <button class="btn btn-link btn-sm text-dark" data-toggle="collapse"
                                                    data-target="#collapse{{ $index }}" aria-expanded="false"
                                                    aria-controls="collapse{{ $index }}"
                                                    style="width: 100%; text-align: left;">
                                                    Bien ID: {{ $cambio->bien_id }} - Campo:
                                                    {{ ucfirst($cambio->campo) }} - Nuevo valor: <span
                                                        class="text-primary">{{ $cambio->valor_nuevo }}</span>
                                                </button>
                                                <small class="text-muted d-none d-md-block ml-2">
                                                    {{ $cambio->created_at->format('d/m/Y H:i') }}
                                                </small>
                                            </h6>
                                        </div>

                                        <div id="collapse{{ $index }}" class="collapse"
                                            aria-labelledby="heading{{ $index }}"
                                            data-parent="#accordionIndividual">
                                            <div class="card-body py-2 px-3">
                                                <p><strong>Propuesto por:</strong>
                                                    {{ $cambio->usuario->name ?? 'Desconocido' }}</p>
                                                <p><strong>Valor anterior:</strong>
                                                    {{ $cambio->valor_anterior ?? 'N/A' }}</p>
                                                <p><strong>Valor nuevo:</strong> <span
                                                        class="text-primary">{{ $cambio->valor_nuevo }}</span></p>
                                                <p><strong>Fecha de creación:</strong>
                                                    {{ $cambio->created_at->format('d/m/Y H:i') }}</p>

                                                <div class="d-flex justify-content-end mt-3">
                                                    <button wire:click="aprobarCambio({{ $cambio->id }})"
                                                        class="btn btn-success btn-sm mr-2">
                                                        <i class="fas fa-check"></i> Aprobar
                                                    </button>
                                                    <button wire:click="rechazarCambio({{ $cambio->id }})"
                                                        class="btn btn-danger btn-sm">
                                                        <i class="fas fa-times"></i> Rechazar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div> <!-- Fin accordionIndividual -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
