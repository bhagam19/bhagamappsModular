<?php

namespace Modules\Users\Livewire\Roles;

use Livewire\Component;
use Modules\Users\Models\Role;

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
        $this->editando = true;
    }

    public function guardar()
    {
        // Guardamos el nuevo título y desactivamos el modo edición
        $this->role->nombre = $this->nombre;
        $this->role->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('users::livewire.roles.editar-nombre-role');
    }
}