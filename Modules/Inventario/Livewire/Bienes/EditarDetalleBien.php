<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;
use Modules\Inventario\Entities\Bien;
use Modules\Inventario\Entities\Detalle;
use Illuminate\Support\Arr;

class EditarDetalleBien extends Component
{
    public $bienId;
    public $editandoDetalle = false;
    public $detalle = [];

    public function toggleEdit()
    {
        $this->editandoDetalle = !$this->editandoDetalle;
    }

    protected $rules = [
        'detalle.car_especial' => 'nullable|string|max:255',
        'detalle.marca'        => 'nullable|string|max:255',
        'detalle.color'        => 'nullable|string|max:255',
        'detalle.tamano'       => 'nullable|string|max:255',
        'detalle.material'     => 'nullable|string|max:255',
        'detalle.otra'         => 'nullable|string|max:255',
    ];

    public function mount($bienId)
    {
        $this->bienId = $bienId; // <--- Agrega esta línea
        $bien = Bien::with('detalle')->findOrFail($bienId);
        $this->detalle = Arr::only(optional($bien->detalle)->toArray() ?? [], [
            'car_especial', 'marca', 'color', 'tamano', 'material', 'otra'
        ]);
    }

    public function actualizar()
    {
        $this->validate();

        $detalle = Detalle::firstOrNew(['bien_id' => $this->bienId]);
        $detalle->fill($this->detalle);
        $detalle->save();
        $detalle->refresh();        
        $this->dispatch('bienActualizado', $detalle->id); 
        $this->toggleEdit();       

        session()->flash('mensaje', 'Detalles actualizados.');
    }
    
    public function render()
    {
        return view('inventario::livewire.bienes.editar-detalle-bien');
    }
}