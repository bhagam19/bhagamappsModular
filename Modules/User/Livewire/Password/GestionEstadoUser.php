<?php

namespace Modules\User\Livewire\Password;

use Livewire\Component;
use Modules\User\Entities\AuditoriaPassword;
use Modules\User\Entities\User;

class GestionEstadoUser extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function bloquear(): void
    {
        abort_unless(auth()->user()->hasPermission('bloquear-usuarios'), 403);

        $this->user->update(['bloqueado' => true]);

        AuditoriaPassword::create([
            'usuario_afectado_id' => $this->user->id,
            'administrador_id'    => auth()->id(),
            'accion'              => 'user_blocked',
            'fecha_hora'          => now(),
        ]);

        $this->user->refresh();
        session()->flash('message', "Usuario {$this->user->nombres} bloqueado.");
    }

    public function desbloquear(): void
    {
        abort_unless(auth()->user()->hasPermission('desbloquear-usuarios'), 403);

        $this->user->update(['bloqueado' => false]);

        AuditoriaPassword::create([
            'usuario_afectado_id' => $this->user->id,
            'administrador_id'    => auth()->id(),
            'accion'              => 'user_unblocked',
            'fecha_hora'          => now(),
        ]);

        $this->user->refresh();
        session()->flash('message', "Usuario {$this->user->nombres} desbloqueado.");
    }

    public function render()
    {
        return view('user::livewire.password.gestion-estado-user');
    }
}
