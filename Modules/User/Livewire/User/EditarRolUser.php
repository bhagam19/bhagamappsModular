<?php

namespace Modules\User\Livewire\User;

use Livewire\Component;
use Modules\User\Entities\User;

class EditarRolUser extends Component
{
    public User $user;
    public $role_id;
    public $editando = false;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->role_id = $user->role->nombre ?? 'Sin rol';
        $this->editando = false;
    }

    public function editar()
    {
        $this->editando = true;
    }

    public function guardar()
    {
        // Guardamos el nuevo título y desactivamos el modo edición
        $this->user->role_id = $this->role_id;
        $this->user->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('user::livewire.user.editar-rol-user');
    }
}
