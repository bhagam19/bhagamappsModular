<?php

namespace Modules\User\Livewire\Roles;

use Livewire\Component;
use Modules\User\Entities\Role;

class EditarNombreRole extends Component
{
    public Role $role;
    public $nombre;
    public $editando = false;

    public function mount(Role $role)
    {
        $this->role = $role;
        $this->nombre = $role->nombre;
        $this->editando = false;
    }

    public function editar()
    {
        abort_if(! auth()->user()->hasPermission('editar-roles'), 403);
        $this->editando = true;
    }

    public function guardar()
    {
        abort_if(! auth()->user()->hasPermission('editar-roles'), 403);
        $this->validate(['nombre' => 'required|string|max:255|unique:roles,nombre,' . $this->role->id]);
        $this->role->nombre = $this->nombre;
        $this->role->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('user::livewire.roles.editar-nombre-role');
    }
}
