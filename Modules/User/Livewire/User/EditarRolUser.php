<?php

namespace Modules\User\Livewire\User;

use Livewire\Component;
use Modules\ActivityLog\Services\ActivityLogger;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;
use Modules\User\Traits\ProteccionAdminPrincipal;

class EditarRolUser extends Component
{
    use ProteccionAdminPrincipal;
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
        abort_unless(auth()->user()?->hasPermission('editar-usuarios'), 403);
        $this->verificarNoEsAdminPrincipal($this->user, 'intento_editar_admin_principal');
        $this->editando = true;
    }

    public function guardar()
    {
        abort_unless(auth()->user()?->hasPermission('editar-usuarios'), 403);
        $this->verificarNoEsAdminPrincipal($this->user, 'intento_editar_admin_principal');
        $rolAnterior = $this->user->role?->nombre ?? 'sin rol';
        $this->user->role_id = $this->role_id;
        $this->user->save();
        $rolNuevo = Role::find($this->role_id)?->nombre ?? 'desconocido';

        ActivityLogger::log(
            modulo:      'Users',
            accion:      'asignar-rol',
            descripcion: "Rol cambiado para {$this->user->email}: '{$rolAnterior}' → '{$rolNuevo}'",
            tipoObjeto:  'Usuario',
            objetoId:    $this->user->id,
            datosNuevos: ['rol_id' => $this->role_id, 'rol' => $rolNuevo],
        );
        $this->editando = false;

        // Invalidar caché de apps visibles — el nuevo rol puede tener distinta visibilidad
        cache()->increment('apps.cache_version');
    }

    public function render()
    {
        return view('user::livewire.user.editar-rol-user', [
            'roles' => Role::all(),
        ]);
    }
}
