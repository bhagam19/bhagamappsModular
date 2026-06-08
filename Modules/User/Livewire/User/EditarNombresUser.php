<?php

namespace Modules\User\Livewire\User;

use Livewire\Component;
use Modules\User\Entities\User;

class EditarNombresUser extends Component
{
    public User $user;
    public $nombres;
    public $editando = false;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->nombres = $user->nombres;
        $this->editando = false;
    }

    public function editar()
    {
        if (!auth()->user()?->hasPermission('editar-usuarios')) {
            abort(403);
        }
        $this->editando = true;
    }

    public function guardar()
    {
        // Guardamos el nuevo título y desactivamos el modo edición
        $this->user->nombres = $this->nombres;
        $this->user->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('user::livewire.user.editar-nombres-user');
    }
}
