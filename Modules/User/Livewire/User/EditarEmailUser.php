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
        if (!auth()->user()?->hasPermission('editar-usuarios')) {
            abort(403);
        }
        $this->editando = true;
    }

    public function guardar()
    {
        abort_unless(auth()->user()?->hasPermission('editar-usuarios'), 403);
        $this->user->email = $this->email;
        $this->user->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('user::livewire.user.editar-email-user');
    }
}
