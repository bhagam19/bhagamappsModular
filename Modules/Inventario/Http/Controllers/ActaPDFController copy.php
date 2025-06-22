<?php

namespace Modules\Inventario\Http\Controllers;

use Modules\Inventario\Entities\Bien;
use Barryvdh\DomPDF\Facade\Pdf;

class ActaPDFController
{
    public function show(Bien $bien)
    {
        // Opcional: cargar relaciones si las necesitas
        $bien->load(['categoria', 'responsable', 'ubicacion']);

        $pdf = Pdf::loadView('inventario::livewire.actas.actaPDF', [
            'bien' => $bien,
        ]);

        return $pdf->download('acta_entrega_' . $bien->id . '.pdf');
    }
}
