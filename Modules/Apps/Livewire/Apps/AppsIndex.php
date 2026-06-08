<?php

namespace Modules\Apps\Livewire\Apps;

use Livewire\Component;
use Modules\Apps\Entities\App;
use Modules\User\Entities\Role;

class AppsIndex extends Component
{
    public bool $modalVisible = false;
    public ?int $appEditandoId = null;
    public array $rolesSeleccionados = [];

    public function toggleHabilitada(int $appId): void
    {
        abort_if(! auth()->user()->hasPermission('administrar-apps'), 403);

        $app = App::findOrFail($appId);
        $app->habilitada = ! $app->habilitada;
        $app->save();

        cache()->increment('apps.cache_version');
    }

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

    public function render()
    {
        return view('apps::livewire.apps.apps-index', [
            'apps'  => App::orderBy('orden')->orderBy('nombre')->get(),
            'roles' => Role::orderBy('nombre')->get(),
        ]);
    }
}
