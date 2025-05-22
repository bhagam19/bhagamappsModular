<?php

namespace Modules\Users\Livewire\Roles;

use Livewire\Component;
use Modules\Users\Models\Role;

class RolesIndex extends Component
{
    public $roles;
    public $nombre, $descripcion;

    public function render()
    {
        return view('users::livewire.roles.roles-index')
            ->layout('layouts.app');
    }

    public $availableColumns = [
        'id' => 'ID',
        'nombre' => 'Nombre del Rol',
        'descripcion' => 'Descripcion del Rol',
    ];

    public $visibleColumns = ['id', 'nombre', 'descripcion'];

    public function toggleColumn($column)
    {
        if (in_array($column, $this->visibleColumns)) {
            $this->visibleColumns = array_filter($this->visibleColumns, fn($col) => $col !== $column);
        } else {
            $this->visibleColumns[] = $column;
        }
    }    

    public function mount()
    {
        $this->roles = Role::all();
    }

    public function store()
    {
        $this->validate([
            'nombre' => 'required|unique:roles,nombre',
            'descripcion' => 'nullable|string',
        ]);

        Role::create([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
        ]);

        session()->flash('message', 'Rol creado exitosamente.');
        $this->resetInput();
    }

    public function delete($id)
    {
        Role::findOrFail($id)->delete();
        session()->flash('message', 'Rol eliminado exitosamente.');
    }

    public function resetInput()
    {
        $this->nombre = '';
        $this->descripcion = '';
    }
}

