<?php

namespace Modules\User\Livewire\User;

use Livewire\Component;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;

class EditarRolUser extends Component
{
    public User $user;
    public $role_id;
    public $editando = false;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->role_id = $user->role_id;
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
        $this->user->role_id = $this->role_id;
        $this->user->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('user::livewire.user.editar-rol-user', [
            'roles' => Role::all(),
        ]);
    }
}
