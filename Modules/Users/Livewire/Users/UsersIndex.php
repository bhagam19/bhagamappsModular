<?php

namespace Modules\Users\Livewire\Users;

use Livewire\Component;
use Modules\Users\Models\User;

class UsersIndex extends Component
{
    public $users;
    public $nombres, $apellidos, $userID, $role_id, $email, $password;

    public function render()
    {
        $this->users = User::with('role')->get();
        return view('users::livewire.users.users-index')
        ->layout('layouts.app');
    }    

    public function mount()
    {
        if (!auth()->user()->hasPermission('ver-usuarios')) {
            abort(403);
        }
    }

    public $availableColumns = [
        'nombres' => 'Nombres',
        'apellidos' => 'Apellidos',
        'userID' => 'No. Documento',
        'rol' => 'Rol',
        'email' => 'Email',
    ];

    public $visibleColumns = ['id', 'nombres', 'apellidos', 'rol', 'email','userID'];   

    public function toggleColumn($column)
    {
        if (in_array($column, $this->visibleColumns)) {
            $this->visibleColumns = array_filter($this->visibleColumns, fn($col) => $col !== $column);
        } else {
            $this->visibleColumns[] = $column;
        }
    } 

    public function store()
    {

        if (!auth()->user()->hasPermission('crear-usuarios')) {
            session()->flash('error', 'No tienes permiso para crear usuarios.');
            return;
        }

        $this->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'userID' => 'required|numeric|unique:users,userID',
            'role_id' => 'required|exists:roles,id',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).+$/'
            ],
        ], [
            'password.regex' => 'La contraseña debe tener al menos 8 caracteres, incluyendo una letra mayúscula, una letra minúscula, un número y un carácter especial.',
        ]);

        User::create([
            'nombres' => ucwords(strtolower($this->nombres)),
            'apellidos' => ucwords(strtolower($this->apellidos)),
            'userID' => $this->userID,
            'role_id' => $this->role_id,
            'email' => $this->email,
            'password' => bcrypt($this->password),
        ]);

        session()->flash('message', 'Usuario creado exitosamente.');

        $this->resetInput();
    }    

    public function delete($id)
    {
        if (!auth()->user()->hasPermission('eliminar-usuarios')) {
            session()->flash('error', 'No tienes permiso para eliminar usuarios.');
            return;
        }
        
        User::findOrFail($id)->delete();
        session()->flash('message', 'Usuario eliminado exitosamente.');
    }

    public function resetInput()
    {
        $this->nombres = '';
        $this->apellidos = '';
        $this->userID = '';
        $this->role_id = null;
        $this->email = '';
        $this->password = '';
    }
}
