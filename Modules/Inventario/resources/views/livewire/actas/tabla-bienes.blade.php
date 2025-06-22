{{-- Tabla de bienes asignados --}}
<table style="width: 100%; border-collapse: collapse; font-size: 10px;">

    <thead>
        <tr>
            <th style="border: 1px solid #dee2e6; padding: 0.25rem; background-color: #f8f9fa; text-align: left;">Item
            </th>
            <th style="border: 1px solid #dee2e6; padding: 0.25rem; background-color: #f8f9fa; text-align: left;">Bien
            </th>
            <th style="border: 1px solid #dee2e6; padding: 0.25rem; background-color: #f8f9fa; text-align: left;">Detalle
            </th>
            <th style="border: 1px solid #dee2e6; padding: 0.25rem; background-color: #f8f9fa; text-align: center;">Cant
            </th>
            <th style="border: 1px solid #dee2e6; padding: 0.25rem; background-color: #f8f9fa; text-align: left;">Estado
            </th>
            <th style="border: 1px solid #dee2e6; padding: 0.25rem; background-color: #f8f9fa; text-align: left;">
                Dependencia</th>
            <th style="border: 1px solid #dee2e6; padding: 0.25rem; background-color: #f8f9fa; text-align: left;">
                Observaciones</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($bienes as $bien)
            <tr style="background-color: {{ $loop->odd ? '#ffffff' : '#f2f2f2' }};">
                <td style="border: 1px solid #dee2e6; padding: 0.25rem; text-align: center;">
                    {{ ($pagina - 1) * $itemsPorPagina + $loop->iteration }}
                </td>
                <td style="border: 1px solid #dee2e6; padding: 0.25rem;">
                    {{ $bien->nombre }}
                </td>
                <td style="border: 1px solid #dee2e6; padding: 0.25rem;">
                    @if ($bien->detalle)
                        <small style="display: block; margin-top: 0.25rem;">
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
                <td style="border: 1px solid #dee2e6; padding: 0.25rem; text-align: center;">
                    {{ $bien->cantidad }}
                </td>
                <td style="border: 1px solid #dee2e6; padding: 0.25rem;">
                    {{ ucfirst($bien->estado->nombre) }}
                </td>
                <td style="border: 1px solid #dee2e6; padding: 0.25rem;">
                    {{ $bien->dependencia->nombre }}
                </td>
                <td style="border: 1px solid #dee2e6; padding: 0.25rem;">
                    {{ $bien->observaciones }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="border: 1px solid #dee2e6; padding: 0.25rem; text-align: center;">
                    No se encontraron bienes asignados.
                </td>
            </tr>
        @endforelse
    </tbody>

</table>
