<?php

namespace Modules\Apps\Livewire\Apps;

use Livewire\Component;
use Modules\Apps\Entities\App;

class EditarOrdenApp extends Component
{
    public App $app;
    public int $orden = 99;
    public $editando = false;

    public function mount(App $app): void
    {
        $this->app = $app;
        $this->orden = $app->orden ?? 99;
    }

    public function editar(): void
    {
        abort_if(! auth()->user()->hasPermission('editar-apps'), 403);
        $this->editando = true;
    }

    public function guardar(): void
    {
        abort_if(! auth()->user()->hasPermission('editar-apps'), 403);
        $this->validate(['orden' => 'required|integer|min:0|max:999']);
        $this->app->orden = $this->orden;
        $this->app->save();
        $this->editando = false;
        cache()->increment('apps.cache_version');
    }

    public function render()
    {
        return view('apps::livewire.apps.editar-orden-app');
    }
}
