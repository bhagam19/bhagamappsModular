<?php

namespace App\Livewire\Users;

use Livewire\Component;
use App\Models\User;

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
        $this->editando = true;
    }

    public function guardar()
    {
        // Guardamos el nuevo título y desactivamos el modo edición
        $this->user->userID = $this->userID;
        $this->user->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('livewire.users.editar-userID-user');
    }
}