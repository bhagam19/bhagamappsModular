<?php

namespace Modules\User\Livewire\Password;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Modules\User\Entities\AuditoriaPassword;
use Modules\User\Entities\User;

class GestionPasswordUser extends Component
{
    public User $user;

    public string $nuevaPassword    = '';
    public bool   $forzarCambio     = false;
    public bool   $mostrarPassword  = false;
    public string $passwordVisible  = '';
    public bool   $confirmado       = false;

    public function mount(User $user): void
    {
        abort_unless(auth()->user()->hasPermission('restablecer-passwords'), 403);

        $this->user = $user;
    }

    public function generarPassword(): void
    {
        $this->nuevaPassword = Str::password(12, true, true, true, false);
        $this->passwordVisible = $this->nuevaPassword;
    }

    public function restablecer(): void
    {
        abort_unless(auth()->user()->hasPermission('restablecer-passwords'), 403);

        $this->validate([
            'nuevaPassword' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).+$/',
            ],
        ], [
            'nuevaPassword.regex' => 'La contraseña debe tener mayúscula, minúscula, número y carácter especial.',
        ]);

        $acciones = ['password_reset'];

        $this->user->update([
            'password'               => Hash::make($this->nuevaPassword),
            'forzar_cambio_password' => $this->forzarCambio,
        ]);

        AuditoriaPassword::create([
            'usuario_afectado_id' => $this->user->id,
            'administrador_id'    => auth()->id(),
            'accion'              => 'password_reset',
            'fecha_hora'          => now(),
        ]);

        if ($this->forzarCambio) {
            AuditoriaPassword::create([
                'usuario_afectado_id' => $this->user->id,
                'administrador_id'    => auth()->id(),
                'accion'              => 'password_forced',
                'fecha_hora'          => now(),
            ]);
        }

        $this->confirmado       = true;
        $this->nuevaPassword    = '';
        $this->passwordVisible  = '';
    }

    public function render()
    {
        return view('user::livewire.password.gestion-password-user');
    }
}
