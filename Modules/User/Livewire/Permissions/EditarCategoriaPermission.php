<?php

namespace Modules\User\Livewire\Permissions;

use Livewire\Component;
use Modules\User\Entities\Permission;

class EditarCategoriaPermission extends Component
{
    public Permission $permission;
    public $categoria;
    public $editando = false;

    public function mount(Permission $permission)
    {
        $this->permission = $permission;
        $this->categoria = $permission->categoria;
        $this->editando = false;
    }

    public function editar()
    {
        $this->editando = true;
    }

    public function guardar()
    {
        // Guardamos el nuevo título y desactivamos el modo edición
        $this->permission->categoria = $this->categoria;
        $this->permission->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('user::livewire.permissions.editar-categoria-permission');
    }
}
