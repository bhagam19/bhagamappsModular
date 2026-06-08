<?php

namespace Modules\User\Livewire\User;

use Livewire\Component;
use Modules\User\Entities\User;

class EditarApellidosUser extends Component
{
    public User $user;
    public $apellidos;
    public $editando = false;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->apellidos = $user->apellidos;
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
        $this->user->apellidos = $this->apellidos;
        $this->user->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('user::livewire.user.editar-apellidos-user');
    }
}
