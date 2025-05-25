<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;
use Modules\Inventario\Entities\Bien;

class EditarFechaAdquisicionBien extends Component
{
    public Bien $bien;
    public $fechaAdquisicion;
    public $editando = false;

    public function mount(Bien $bien)
    {
        $this->bien = $bien;
        $this->fechaAdquisicion = $bien->fechaAdquisicion;
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
        $this->bien->fechaAdquisicion = $this->fechaAdquisicion;
        $this->bien->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('inventario::livewire.bienes.editar-fechaAdquisicion-bien');
    }
}