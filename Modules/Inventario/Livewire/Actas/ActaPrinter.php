<?php

namespace Modules\Inventario\Livewire\Actas;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;

class ActaPrinter
{
    /**
     * Genera el HTML paginado del acta.
     *
     * @param Collection $bienes Lista de bienes del usuario
     * @param \App\Models\User $user Usuario seleccionado
     * @param string $nombreCompleto Nombre completo del usuario
     * @param string $fecha Fecha de emisión
     * @param int $filasPorPagina Cantidad de filas por página
     * @return string HTML generado
     */
    public static function renderActaPaginada($bienes, $user, $nombreCompleto, $fecha, $itemsPorPagina)
    {
        // Aseguramos que sea una colección
        $bienes = $bienes instanceof Collection ? $bienes : collect([]);

        $html = '';
        $paginas = $bienes->chunk($itemsPorPagina);
        $totalPaginas = $paginas->count();

        foreach ($paginas as $i => $bienesPagina) {
            $pagina = $i + 1;

            // Comenzamos el contenedor de una página
            $htmlPagina = '<div class="contenedor-acta bg-white shadow rounded border">
                                <div class="contenido-principal flex-grow-1 flex-column">';

            // Encabezado (siempre)
            $htmlPagina .= View::make('inventario::livewire.actas.encabezado', [
                'nombreCompleto' => $nombreCompleto,
                'pagina' => $pagina,
            ])->render();

            // Introducción (solo en la primera página)
            if ($pagina === 1) {
                $htmlPagina .= View::make('inventario::livewire.actas.texto-inicial', [
                    'user' => $user,
                    'nombreCompleto' => $nombreCompleto,
                    'miFecha' => $fecha,
                ])->render();
            }

            // Tabla de bienes (siempre)
            $htmlPagina .= View::make('inventario::livewire.actas.tabla-bienes', [
                'bienes' => $bienesPagina,
                'pagina' => $pagina,
                'itemsPorPagina' => $itemsPorPagina,
            ])->render();

            // Firmas (solo en la última página)
            if ($pagina === $totalPaginas) {
                $htmlPagina .= View::make('inventario::livewire.actas.firmas', [
                    'user' => $user,
                    'nombreCompleto' => $nombreCompleto,
                ])->render();
            }

            // Footer (siempre)            
            $htmlPagina .= View::make('inventario::livewire.actas.footer')->render();

            // Cerramos el contenedor de la página
            $htmlPagina .= '</div></div>';

            // Acumulamos la página
            $html .= $htmlPagina;
        }

        return $html;
    }
}
