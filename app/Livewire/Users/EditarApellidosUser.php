<?php

namespace App\Livewire\Users;

use Livewire\Component;
use App\Models\User;

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
        $this->editando = true;
    }

    public function guardar()
    {
        // Guardamos el nuevo título y desactivamos el modo edición
        $this->user->apellidos = $this->apellidos;
        $this->user->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('livewire.users.editar-apellidos-user');
    }
}