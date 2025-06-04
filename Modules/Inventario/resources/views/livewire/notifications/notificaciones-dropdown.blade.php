<style>
    /* Contenedor flotante fijo a la derecha */
    #accordionFlotante {
        position: fixed;
        top: 60px;
        right: 20px;
        width: 350px;
        max-height: 80vh;
        overflow-y: auto;
        z-index: 1050;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 6px;
        background-color: #fff;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Evitar que clic en botones collapse cierre dropdown
        document.querySelectorAll('#accordionFlotante [data-toggle="collapse"]').forEach(function(btn) {
            btn.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        });

        // Agregar listener para alternar colapsos manualmente (si Bootstrap no lo hace)
        $('#accordionFlotante button[data-toggle="collapse"]').on('click', function(e) {
            e.preventDefault();
            var target = $(this).data('target');
            $(target).collapse('toggle');
        });
    });
</script>

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
                                                    Bien Id: {{ $cambio->bien_id }} | {{ $cambio->bien->nombre }} |
                                                    {{ ucfirst($cambio->campo_nombre) }} | Nuevo valor: <span
                                                        class="text-primary">{{ $cambio->valor_nuevo_nombre }}</span>
                                                </button>
                                                <small class="text-muted d-none d-md-block ml-2">
                                                    {{ $cambio->created_at->format('d/m/Y H:i') }}
                                                </small>
                                            </h6>
                                        </div>

                                        <div id="collapse{{ $index }}" class="collapse"
                                            aria-labelledby="heading{{ $index }}">
                                            <div class="card-body py-2 px-3">
                                                <p><strong>Propuesto por:</strong>
                                                    {{ $cambio->usuario->nombre_completo ?? 'Desconocido' }}</p>
                                                <p><strong>Bien:</strong>
                                                    {{ $cambio->bien->nombre ?? 'N/A' }}</p>
                                                <p><strong>Campo:</strong>
                                                    {{ ucfirst($cambio->campo_nombre) ?? 'N/A' }}</p>
                                                <p><strong>Valor anterior:</strong>
                                                    {{ $cambio->valor_anterior_nombre ?? 'N/A' }}</p>
                                                <p><strong>Valor nuevo:</strong> <span
                                                        class="text-primary">{{ $cambio->valor_nuevo_nombre }}</span>
                                                </p>
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
