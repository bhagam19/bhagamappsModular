<?php

namespace App\Livewire\Grupo;

use Livewire\Component;
use App\Models\Grupo; // Ajusta según tu modelo

class CrearGrupo extends Component
{
    public $nombres = ['']; // arreglo con un input inicial

    protected $rules = [
        'nombres.*' => 'required|string|max:255',
    ];

    public function agregarInput()
    {
        $this->nombres[] = '';
    }

    public function eliminarInput($index)
    {
        unset($this->nombres[$index]);
        $this->nombres = array_values($this->nombres); // reindexar
    }

    public function guardar()
    {
        $this->validate();

        foreach ($this->nombres as $nombre) {
            Grupo::create(['nombre' => $nombre]);
        }

        // Opcional: limpiar inputs luego de guardar
        $this->nombres = [''];

        session()->flash('mensaje', 'Grupos creados correctamente.');
    }

    public function render()
    {
        return view('livewire.grupo.crear-grupo');
    }
}