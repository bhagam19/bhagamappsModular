<?php

namespace Modules\Users\Livewire\Permissions;

use Livewire\Component;
use Modules\Users\Models\Permission;
use Illuminate\Support\Str;

class PermissionsIndex extends Component
{
    public $permissions;
    public $nombre, $descripcion, $slug, $categoria, $permissionId;

    public function render()
    {
        $this->permissions = Permission::all();
        return view('users::livewire.permissions.permissions-index', [
            'permissions' => Permission::all()
        ])->layout('layouts.app');
    }

    public function store()
    {
        $this->validate([
            'nombre' => 'required|unique:permissions,nombre',
            'descripcion' => 'nullable|string',
            'categoria' => 'required|string',
        ]);

        Permission::create([
            'nombre' => $this->nombre,
            'slug' => Str::slug($this->nombre),
            'categoria' => ucfirst($this->categoria),
            'descripcion' => $this->descripcion,
        ]);

        session()->flash('message', 'Permiso creado exitosamente.');
        $this->resetInput();
    }   

    public function delete($id)
    {
        Permission::findOrFail($id)->delete();
        session()->flash('message', 'Permiso eliminado.');
    }

    public function resetInput()
    {
        $this->nombre = '';
        $this->descripcion = '';
        $this->categoria = '';
        $this->permissionId = null;
    }
}

