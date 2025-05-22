<?php

namespace Modules\Users\Livewire\Permissions;

use Livewire\Component;
use Modules\Users\Models\Permission;

class EditarDescripcionPermission extends Component
{
    public Permission $permission;
    public $descripcion;
    public $editando = false;

    public function mount(Permission $permission)
    {
        $this->permission = $permission;
        $this->descripcion = $permission->descripcion;
        $this->editando = false; 
    }

    public function editar()
    {
        $this->editando = true;
    }

    public function guardar()
    {
        // Guardamos el nuevo título y desactivamos el modo edición
        $this->permission->descripcion = $this->descripcion;
        $this->permission->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('users::livewire.permissions.editar-descripcion-permission');
    }
}