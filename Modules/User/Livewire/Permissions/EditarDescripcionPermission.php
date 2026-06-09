<?php

namespace Modules\User\Livewire\Permissions;

use Livewire\Component;
use Modules\User\Entities\Permission;

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
        abort_if(! auth()->user()->hasPermission('editar-permisos'), 403);
        $this->editando = true;
    }

    public function guardar()
    {
        abort_if(! auth()->user()->hasPermission('editar-permisos'), 403);
        $this->permission->descripcion = $this->descripcion;
        $this->permission->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('user::livewire.permissions.editar-descripcion-permission');
    }
}
