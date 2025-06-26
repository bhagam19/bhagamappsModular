<?php

namespace Modules\User\Livewire\User;

use Livewire\Component;
use Modules\User\Entities\User;

class EditarEmailUser extends Component
{
    public User $user;
    public $email;
    public $editando = false;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->email = $user->email;
        $this->editando = false;
    }

    public function editar()
    {
        $this->editando = true;
    }

    public function guardar()
    {
        // Guardamos el nuevo título y desactivamos el modo edición
        $this->user->email = $this->email;
        $this->user->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('user::livewire.user.editar-email-user');
    }
}
