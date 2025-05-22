<?php

namespace Modules\Users\Livewire\Roles;

use Livewire\Component;
use Modules\Users\Models\Role;

class EditarDescripcionRole extends Component
{
    public Role $role;
    public $descripcion;
    public $editando = false;

    public function mount(Role $role)
    {
        $this->role = $role;
        $this->descripcion = $role->descripcion;
        $this->editando = false; 
    }

    public function editar()
    {
        $this->editando = true;
    }

    public function guardar()
    {
        // Guardamos el nuevo título y desactivamos el modo edición
       $this->role->descripcion = $this->descripcion;
        $this->role->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('users::livewire.roles.editar-descripcion-role');
    }
}