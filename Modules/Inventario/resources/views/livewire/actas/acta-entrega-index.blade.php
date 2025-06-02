<div id="acta-entrega" class="p-6 bg-white text-black w-3/4 font-arial">

    <style>
        /* Encabezado del Acta */
        .font-arial {
            font-family: Arial, sans-serif !important;
            font-size: 24px !important;
        }

        .img-escudo {
            position: relative;
            width: 150px;
            height: 150px;
        }

        #separadorColores hr:nth-child(1) {
            background-color: #013801;
            height: 1mm;
        }

        #separadorColores hr:nth-child(2) {
            background-color: white;
            height: 1mm;
        }

        #separadorColores hr:nth-child(3) {
            background-color: #01018a;
            height: 1mm;
        }
    </style>

    {{-- Selección de usuario --}}
    <div id="encabezadoGenerarActa" class="d-flex justify-content-end align-items-center mb-3">

        <span class="text-muted mr-3">Selecciona un usuario:</span>

        <select wire:model.lazy="userId" id="userId" class="custom-select custom-select-sm mr-3" style="width: 180px;">
            <option value="">-- Usuarios --</option>
            @foreach ($users as $u)
                <option value="{{ $u->id }}">{{ $u->nombres }} {{ $u->apellidos }}</option>
            @endforeach
        </select>

        @php
            $pgActa = 1; // Número de página del acta
            $miFecha = \Carbon\Carbon::now()->locale('es')->isoFormat('DD [de] MMMM [de] YYYY'); // Fecha actual en formato dd-mm-YYYY
            $nombreCompleto = $user ? mb_strtoupper($user->nombres . ' ' . $user->apellidos, 'UTF-8') : '';
        @endphp

        <span class="text-muted mr-3">|</span>

        <button title="Imprimir Acta" onclick="imprimir()" class="btn btn-link text-success p-0"
            style="font-size: 1.25rem;">
            <i class="fas fa-print"></i>
        </button>

    </div>

    @if ($user)
        <div class="bg-white text-black w-3/4 mx-auto p-6 border border-gray-300 rounded-lg shadow-lg"
            style="padding: 3cm;">

            {{-- Encabezado --}}
            <div id="d-flex align-items-center justify-content-center w-3/4 mx-auto">

                <div class="row align-items-center">
                    <div class="col-2 text-center">
                        <img class="img-escudo img-fluid" src="{{ asset('vendor/adminlte/dist/img/escudo.png') }}"
                            alt="Escudo" />
                    </div>
                    <div class="col-10 font-arial">
                        <h4 class="text-center mb-2 font-weight-bold">INSTITUCIÓN EDUCATIVA ENTRERRÍOS</h4>
                        <h5 class="text-justify mb-2">
                            Constituida y autorizados sus estudios por Resolución Departamental 1490 del 20 de febrero
                            de 2003 y mediante la cual se le concede <span class="font-weight-bold">Reconocimiento de
                                Carácter Oficial</span>; autorizados sus estudios para Educación Formal de Adultos por
                            Resolución
                            12339 del 13 de junio de 2006; y aclaradas sus jornadas y modelos por Resolución
                            Departamental S201500286893 del 1 de julio de 2015.
                        </h5>
                        <h6 class="text-right mb-0 font-weight-bold">DANE 105264000013 - NIT 811044496-0</h6>
                    </div>
                </div>
            </div>

            <div id="separadorColores" class="w-3/4 mx-auto my-2">
                <hr class="my-0" />
                <hr class="my-0" />
                <hr class="my-0" />
            </div>

            {{-- Cuerpo --}}
            <div class="mb-4 px-3">
                <h4 class="text-center font-weight-bold pt-3 pb-1">
                    ACTA DE ENTREGA DE INVENTARIO - {{ $nombreCompleto }} - PÁGINA {{ $pgActa }}
                </h4>

                <p class="text-left font-arial pb-2 pt-2">
                    Entrerríos, {{ $miFecha }}
                </p>

                <p class="text-justify pb-2 pt-2">
                    El Rector de la IE Entrerríos hace entrega del siguiente inventario al docente
                    <span class="font-weight-bold">{{ $nombreCompleto }}</span>, identificado con <span
                        class="font-weight-bold">CC. {{ number_format($user->userID, 0, ',', '.') }}</span>.
                </p>
            </div>

            {{-- Tabla de bienes asignados --}}
            <table class="table table-bordered table-sm table-striped">
                <thead>
                    <tr>
                        <th>Bien</th>
                        <th>Detalle</th>
                        <th>Cant</th>
                        <th>Estado</th>
                        <th>Dependencia</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bienes as $bien)
                        <tr>
                            <td>{{ $bien->nombre }}</td>
                            <td>
                                @if ($bien->detalle)
                                    <small class="d-block mt-1 text-muted">
                                        {{ collect([
                                            $bien->detalle->car_especial,
                                            $bien->detalle->marca,
                                            $bien->detalle->color,
                                            $bien->detalle->tamano,
                                            $bien->detalle->material,
                                            $bien->detalle->otra,
                                        ])->filter()->implode(' | ') }}
                                    </small>
                                @endif
                            </td>
                            <td>{{ $bien->cantidad }}</td>
                            <td>{{ ucfirst($bien->estado->nombre) }}</td>
                            <td>{{ $bien->dependencia->nombre }}</td>
                            <td>{{ $bien->observaciones }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No se encontraron bienes asignados.</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>





            {{-- Pie de página --}}
            <footer id="piedePaginaActa" class="mt-6 text-center text-gray-700 text-sm">
                <div class="mb-2">
                    <hr class="border-t-2 border-green-700" />
                    <hr class="border-t border-white -mt-2" />
                    <hr class="border-t-2 border-blue-900 -mt-3" />
                </div>
                <h4 class="font-semibold mb-1">"MÁS QUE ENSEÑAR ES FORMAR"</h4>
                <p>
                    Institución Educativa Entrerríos || Entrerríos, Antioquia || Dirección: Carrera 14 No. 10 –
                    17<br />
                    Teléfonos: 8670153 - 3135784406<br />
                    Correo: <a href="mailto:ieentrerrios@yahoo.es"
                        class="text-blue-600 underline">ieentrerrios@yahoo.es</a>
                    ||
                    Sitio Web: <a href="https://sites.google.com/view/ieentrerrios" target="_blank"
                        class="text-blue-600 underline">ieentrerrios</a>
                </p>
            </footer>
    @endif

</div>
