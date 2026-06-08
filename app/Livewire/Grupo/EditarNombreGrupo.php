<?php

namespace App\Livewire\Grupo;

use Livewire\Component;
use App\Models\Grupo;

class EditarNombreGrupo extends Component
{
    public Grupo $grupo;
    public $nombre;
    public $editando = false;

    public function mount(Grupo $grupo)
    {
        $this->grupo = $grupo;
        $this->nombre = $grupo->nombre;
        $this->editando = false; 
    }

    public function editar()
    {
        $this->editando = true;
    }

    public function guardar()
    {
        // Guardamos el nuevo título y desactivamos el modo edición
        $this->grupo->nombre = $this->nombre;
        $this->grupo->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('livewire.grupo.editar-nombre-grupo');
    }
}