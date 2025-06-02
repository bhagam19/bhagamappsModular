{{-- Tabla de bienes asignados --}}
<table class="table table-bordered table-sm table-striped" style="font-size: 10px;">
    <thead>
        <tr>
            <th>Item</th>
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
                <td>{{ ($pagina - 1) * $itemsPorPagina + $loop->iteration }}</td>
                <td>{{ $bien->nombre }}</td>
                <td>
                    @if ($bien->detalle)
                        <small class="d-block mt-1">
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
