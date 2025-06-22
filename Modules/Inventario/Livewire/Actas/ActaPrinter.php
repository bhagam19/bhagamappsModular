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
        $bienes = $bienes instanceof Collection ? $bienes : collect([]);

        $html = '';
        $paginas = $bienes->chunk($itemsPorPagina);
        $totalPaginas = $paginas->count();

        foreach ($paginas as $i => $bienesPagina) {
            $pagina = $i + 1;

            // Primer div: contenedor-acta (página completa)
            $htmlPagina = '<div class="contenedor-acta" style="
                background-color: white;
                border: 1px solid #ddd;
                margin: 1cm 2cm;
                padding: 1cm 2cm;
                font-family: Helvetica, Times, serif;
                font-size: 11pt;
                max-width: 900px;
                min-height: 1150px;
                page-break-after: always;
                page-break-inside: avoid;
                break-after: page;
                display: block;
            ">';

            // Segundo div: contenido-principal
            $htmlPagina .= '<div class="contenido-principal" style="
                display: block;
            ">';
            
            // Tercer div
            $htmlPagina .= '<div style="
                page-break-after: avoid;
                page-break-inside: avoid;
                break-after: page;
                display: block;
                width: 100%;
            ">';

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

            // Cerramos los divs
            $htmlPagina .= '</div></div></div>';

            $html .= $htmlPagina;
        }

        return $html;
    }



}
