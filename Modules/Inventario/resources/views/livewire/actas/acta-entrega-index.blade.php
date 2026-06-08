<div class="mb-4" style="height: 100vh; display: flex; flex-direction: column;">

    <!-- Barra fija arriba -->
    <div class="d-flex align-items-center mb-1 sticky-top bg-white py-2 shadow" style="z-index: 1020; top: 0;">
        @if ($mostrarSelector)
            <span class="text-muted mr-3">Selecciona un usuario:</span>

            <select wire:model.lazy="userId" id="userId" class="custom-select custom-select-sm mr-3"
                style="width: 180px;">
                <option value="">-- Usurio --</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}">{{ $u->nombres }} {{ $u->apellidos }}</option>
                @endforeach
            </select>
        @endif

        <span class="text-muted mr-2">Ítems por página:</span>
        <input type="number" wire:model.lazy="itemsPorPagina" min="1" max="100"
            class="form-control form-control-sm mr-3" style="width: 80px;">

        @if ($userId)
            <span class="text-muted mr-3">|</span>
            <button title="Imprimir Acta" onclick="imprimir()" class="btn btn-link text-success p-0"
                style="font-size: 1.25rem;">
                <i class="fas fa-print"></i>
                Click para descargar o imprimir el Acta
            </button>
        @endif
    </div>

    <!-- Contenedor scrollable -->
    <div class="contenedor-general overflow-auto">

        @if ($contenidoActa)
            <style>
                .contenedor-acta {
                    page-break-after: always;
                    display: flex;
                    flex-direction: column;
                    margin: 1cm 2cm !important;
                    padding: 1cm 2cm !important;
                    font-family: 'Helvetica', Times, serif;
                    font-size: 11pt;
                    max-width: 900px;
                    min-height: 1200px;
                }

                .contenido-principal {
                    flex: 1 0 auto;
                }

                /* Aquí tus estilos para la página del acta */
                .img-escudo {
                    position: relative;
                    width: 90px;
                    height: 90px;
                }

                /* Separadores de colores */
                .linea-verde {
                    background-color: #013801;
                    height: 0.8mm;
                    border: none;
                    margin: 0;
                }

                .linea-blanca {
                    background-color: white;
                    height: 0.8mm;
                    border: none;
                    margin: 0;
                }

                .linea-azul {
                    background-color: #01018a;
                    height: 0.8mm;
                    border: none;
                    margin: 0;
                }

                @media print {
                    body * {
                        visibility: hidden;
                    }

                    .contenedor-acta {
                        position: absolute;
                        left: 0;
                        top: 0;
                        width: 100%;
                    }

                    .contenedor-acta,
                    .contenedor-acta * {
                        visibility: visible;
                    }

                    .contenido-principal {
                        flex: 1 0 auto;
                    }

                    /* Aquí tus estilos para la página del acta */
                    .img-escudo {
                        position: relative;
                        width: 90px;
                        height: 90px;
                    }

                    /* Separadores de colores */
                    .linea-verde {
                        background-color: #013801;
                        height: 0.8mm;
                        border: none;
                        margin: 0;
                    }

                    .linea-blanca {
                        background-color: white;
                        height: 0.8mm;
                        border: none;
                        margin: 0;
                    }

                    .linea-azul {
                        background-color: #01018a;
                        height: 0.8mm;
                        border: none;
                        margin: 0;
                    }
                }
            </style>

            {!! $contenidoActa !!}
        @elseif ($userId)
            <div class="alert alert-warning">
                No hay bienes para este usuario.
            </div>
        @endif

    </div>
