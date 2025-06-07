<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;

class TestFiltro extends Component
{
    public $filtroNombreTest = '';

    public function updatedFiltroNombreTest()
    {
        logger('Filtro cambiado: ' . $this->filtroNombreTest);
    }

    public function render()
    {
        return view('inventario::livewire.bienes.test-filtro');
    }
}
