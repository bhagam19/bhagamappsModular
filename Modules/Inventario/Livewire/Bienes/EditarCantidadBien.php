<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;
use Modules\Inventario\Entities\Bien;

class EditarCantidadBien extends Component
{
    public Bien $bien;
    public $cantidad;
    public $editando = false;

    public function mount(Bien $bien)
    {
        $this->bien = $bien;
        $this->cantidad = $bien->cantidad;
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
        $this->bien->cantidad = $this->cantidad;
        $this->bien->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('inventario::livewire.bienes.editar-cantidad-bien');
    }
}