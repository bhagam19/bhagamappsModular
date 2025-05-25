<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;
use Modules\Inventario\Entities\Bien;

class EditarNombreBien extends Component
{
    public Bien $bien;
    public $nombre;
    public $editando = false;

    public function mount(Bien $bien)
    {
        $this->bien = $bien;
        $this->nombre = $bien->nombre;
        $this->editando = false; 
    }

    public function editar()
    {
        if (!auth()->user()?->hasPermission('editar-usuarios')) {
            abort(403);
        }
        $this->editando = true;
    }

    public function guardar()
    {
        // Guardamos el nuevo título y desactivamos el modo edición
        $this->bien->nombre = $this->nombre;
        $this->bien->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('inventario::livewire.bienes.editar-nombre-bien');
    }
}