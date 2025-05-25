<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;
use Modules\Inventario\Entities\Bien;

class EditarPrecioBien extends Component
{
    public Bien $bien;
    public $precio;
    public $editando = false;

    public function mount(Bien $bien)
    {
        $this->bien = $bien;
        $this->precio = $bien->precio;
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
        $this->bien->precio = $this->precio;
        $this->bien->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('inventario::livewire.bienes.editar-precio-bien');
    }
}