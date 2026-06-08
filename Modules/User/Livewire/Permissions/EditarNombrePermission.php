<?php

namespace Modules\User\Livewire\Permissions;

use Livewire\Component;
use Modules\User\Entities\Permission;

class EditarNombrePermission extends Component
{
    public Permission $permission;
    public $nombre;
    public $editando = false;

    public function mount(Permission $permission)
    {
        $this->permission = $permission;
        $this->nombre = $permission->nombre;
        $this->editando = false;
    }

    public function editar()
    {
        $this->editando = true;
    }

    public function guardar()
    {
        // Guardamos el nuevo título y desactivamos el modo edición
        $this->permission->nombre = $this->nombre;
        $this->permission->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('user::livewire.permissions.editar-nombre-permission');
    }
}
