<?php

namespace Modules\Inventario\Livewire\Actas;

use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Inventario\Entities\Bien;

class ActasPDF extends Component
{
    public Bien $bien; // Asegúrate de pasar esta propiedad desde mount()

    public function generarActaPDF()
    {
        $pdf = Pdf::loadView('inventario::livewire.actas.actaPDF', [
            'bien' => $this->bien,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'acta_entrega_' . $this->bien->id . '.pdf');
    }

    public function render()
    {
        return view('inventario::livewire.actas.acta-entrega-index', [
            'bien' => $this->bien,
        ]);
    }
}
