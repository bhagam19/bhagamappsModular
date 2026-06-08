<?php

namespace Modules\User\Livewire\User;

use Livewire\Component;
use Modules\User\Entities\User;

class EditarUserIDUser extends Component
{
    public User $user;
    public $userID;
    public $editando = false;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->userID = $user->userID;
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
        $this->user->userID = $this->userID;
        $this->user->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('user::livewire.user.editar-userID-user');
    }
}
