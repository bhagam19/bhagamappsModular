<?php

namespace Modules\Apps\Livewire\Apps;

use Livewire\Component;
use Modules\Apps\Entities\App;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;

class AppsIndex extends Component
{
    public bool $modalVisible = false;
    public bool $modalUsuariosVisible = false;
    public ?int $appEditandoId = null;
    public array $rolesSeleccionados = [];
    public array $usuariosSeleccionados = [];

    public function toggleHabilitada(int $appId): void
    {
        abort_if(! auth()->user()->hasPermission('administrar-apps'), 403);

        $app = App::findOrFail($appId);
        $app->habilitada = ! $app->habilitada;
        $app->save();

        cache()->increment('apps.cache_version');
    }

    // --- Gestión de roles ---

    public function abrirModalRoles(int $appId): void
    {
        abort_if(! auth()->user()->hasPermission('administrar-apps'), 403);

        $this->appEditandoId = $appId;
        $app = App::with('roles')->findOrFail($appId);
        $this->rolesSeleccionados = $app->roles->pluck('id')->toArray();
        $this->modalVisible = true;
    }

    public function guardarRoles(): void
    {
        abort_if(! auth()->user()->hasPermission('administrar-apps'), 403);

        $app = App::findOrFail($this->appEditandoId);
        $app->roles()->sync($this->rolesSeleccionados);
        $this->modalVisible = false;
        $this->appEditandoId = null;
        $this->rolesSeleccionados = [];

        cache()->increment('apps.cache_version');
    }

    public function cerrarModal(): void
    {
        $this->modalVisible = false;
        $this->appEditandoId = null;
        $this->rolesSeleccionados = [];
    }

    // --- Gestión de usuarios directos (app_user) ---

    public function abrirModalUsuarios(int $appId): void
    {
        abort_if(! auth()->user()->hasPermission('administrar-apps'), 403);

        $this->appEditandoId = $appId;
        $app = App::with('user')->findOrFail($appId);
        $this->usuariosSeleccionados = $app->user->pluck('id')->toArray();
        $this->modalUsuariosVisible = true;
    }

    public function guardarUsuarios(): void
    {
        abort_if(! auth()->user()->hasPermission('administrar-apps'), 403);

        $app = App::findOrFail($this->appEditandoId);

        $syncData = collect($this->usuariosSeleccionados)
            ->mapWithKeys(fn ($id) => [$id => ['activo' => true]])
            ->toArray();

        $app->user()->sync($syncData);
        $this->modalUsuariosVisible = false;
        $this->appEditandoId = null;
        $this->usuariosSeleccionados = [];

        cache()->increment('apps.cache_version');
    }

    public function cerrarModalUsuarios(): void
    {
        $this->modalUsuariosVisible = false;
        $this->appEditandoId = null;
        $this->usuariosSeleccionados = [];
    }

    public function render()
    {
        return view('apps::livewire.apps.apps-index', [
            'apps'  => App::with(['roles', 'user'])->orderBy('orden')->orderBy('nombre')->get(),
            'roles' => Role::orderBy('nombre')->get(),
            'users' => User::orderBy('apellidos')->orderBy('nombres')->get(),
        ]);
    }
}
