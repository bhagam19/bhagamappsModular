<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;
use Modules\Inventario\Entities\Bien;
use Modules\Inventario\Entities\Detalle;

class EditarDetalleBienModal extends Component
{
    public $bienId;
    public $nombreBien;
    public $detalle = [];

    protected $rules = [
        'detalle.car_especial' => 'nullable|string|max:255',
        'detalle.marca' => 'nullable|string|max:255',
        'detalle.color' => 'nullable|string|max:255',
        'detalle.tamano' => 'nullable|string|max:255',
        'detalle.material' => 'nullable|string|max:255',
        'detalle.otra' => 'nullable|string|max:255',
    ];

    protected $listeners = ['cargarDetalle'];

    public function cargarDetalle($bienId)
    {
        $this->bienId = $bienId;
        $bien = Bien::with('detalle')->findOrFail($bienId);
        $this->nombreBien = $bien->nombre;
        $this->detalle = $bien->detalle?->toArray() ?? [];

        // Evento a JS indicando que ya cargó todo
        $this->dispatch('detalleBienCargado');
    }

    public function actualizar()
    {
        $this->validate();

        $detalle = Detalle::firstOrNew(['bien_id' => $this->bienId]);
        $detalle->fill($this->detalle);
        $detalle->save();

        Bien::find($this->bienId)?->touch();

        $this->dispatch('cerrar-modal-detalles');
        $this->dispatch('bienActualizado');

        session()->flash('success', 'Detalles actualizados correctamente.');
    }

    public function render()
    {
        return view('inventario::livewire.bienes.editar-detalle-bien-modal');
    }
}
