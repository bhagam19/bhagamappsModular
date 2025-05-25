<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;
use Modules\Inventario\Entities\Bien;

class BienesIndex extends Component
{
    public $bienes;

    public $nombre, $detalle, $serie, $origen, $fechaAdquisicion, $precio, $cantidad;

    public function render()
    {
        $this->bienes = Bien::with([])->get();

        return view('inventario::livewire.bienes.bienes-index')
            ->layout('layouts.app');
    }

    public function mount()
    {
        if (!auth()->user()->hasPermission('ver-bienes')) {
            abort(403);
        }
    }

    public $availableColumns = [
        'nombre' => 'Nombre del Bien',
        'detalle' => 'Detalle',
        'serie' => 'Serie',
        'origen' => 'Origen',
        'fechaAdquisicion' => 'Fecha de Adquisición',
        'precio' => 'Precio',
        'cantidad' => 'Cantidad',
    ];

    public $visibleColumns = [
        'nombre', 'detalle', 'serie', 'origen', 'fechaAdquisicion', 'precio', 'cantidad'
    ];

    public function toggleColumn($column)
    {
        if (in_array($column, $this->visibleColumns)) {
            $this->visibleColumns = array_filter($this->visibleColumns, fn($col) => $col !== $column);
        } else {
            $this->visibleColumns[] = $column;
        }
    }

    public function store()
    {
        if (!auth()->user()->hasPermission('crear-bienes')) {
            session()->flash('error', 'No tienes permiso para crear bienes.');
            return;
        }

        $this->validate([
            'nombre' => 'required|string|max:100',
            'detalle' => 'nullable|string|max:400',
            'serie' => 'nullable|string|max:40',
            'origen' => 'nullable|string|max:40',
            'fecha_adquisicion' => 'nullable|date',
            'precio' => 'nullable|numeric',
            'cantidad' => 'nullable|integer'
        ]);

        Bien::create([
            'nombre' => $this->nombre,
            'detalle' => $this->detalle,
            'serie' => $this->serie,
            'origen' => $this->origen,
            'fechaAdquisicion' => $this->fechaAdquisicion,
            'precio' => $this->precio,
            'cantidad' => $this->cantidad
        ]);

        session()->flash('message', 'Bien creado exitosamente.');
        $this->resetInput();
    }

    public function delete($id)
    {
        if (!auth()->user()->hasPermission('eliminar-bienes')) {
            session()->flash('error', 'No tienes permiso para eliminar bienes.');
            return;
        }

        Bien::findOrFail($id)->delete();
        session()->flash('message', 'Bien eliminado exitosamente.');
    }

    public function resetInput()
    {
        $this->nombre = '';
        $this->detalle = '';
        $this->serie = '';
        $this->origen = '';
        $this->fechaAdquisicion = null;
        $this->precio = null;
        $this->cantidad = null;
    }
}
