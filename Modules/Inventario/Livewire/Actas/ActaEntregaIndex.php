<?php

namespace Modules\Inventario\Livewire\Actas;

use Livewire\Component;
use Modules\Users\Models\User;
use Modules\Inventario\Entities\Bien;

class ActaEntregaIndex extends Component
{
    public $userId;
    public $user;
    public $users;
    public $bienes;

    public function mount()
    {
        $this->users = User::orderBy('nombres')->get();
    }

    public function updatedUserId($value)
    {
        $this->bienes = Bien::with(['detalle', 'estado', 'dependencia'])
            ->where('usuario_id', $value)
            ->orderBy('nombre', 'asc')
            ->get();

        $this->user = User::find($value);
    }

    public function render()
    {
        $users = User::orderBy('nombres')->get();

        return view('inventario::livewire.actas.acta-entrega-index', [
            'users' => $users,
            'userId' => $this->userId,
            'user' => $this->user,
            'bienes' => $this->bienes ?? collect(),
        ])->layout('layouts.app');
    }
}
