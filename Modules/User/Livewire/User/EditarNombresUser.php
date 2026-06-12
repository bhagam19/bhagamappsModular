<?php

namespace Modules\User\Livewire\User;

use Livewire\Component;
use Modules\User\Entities\User;
use Modules\User\Traits\ProteccionAdminPrincipal;

class EditarNombresUser extends Component
{
    use ProteccionAdminPrincipal;

    public User $user;
    public $nombres;
    public $editando = false;

    public function mount(User $user)
    {
        $this->user    = $user;
        $this->nombres = $user->nombres;
    }

    public function editar()
    {
        abort_unless(auth()->user()?->hasPermission('editar-usuarios'), 403);
        $this->verificarNoEsAdminPrincipal($this->user, 'intento_editar_admin_principal');
        $this->editando = true;
    }

    public function guardar()
    {
        abort_unless(auth()->user()?->hasPermission('editar-usuarios'), 403);
        $this->verificarNoEsAdminPrincipal($this->user, 'intento_editar_admin_principal');
        $this->user->nombres = $this->nombres;
        $this->user->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('user::livewire.user.editar-nombres-user');
    }
}
