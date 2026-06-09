<?php

namespace Modules\Apps\Livewire\Apps;

use Livewire\Component;
use Modules\Apps\Entities\App;

class EditarNombreApp extends Component
{
    public App $app;
    public string $nombre = '';
    public $editando = false;

    public function mount(App $app): void
    {
        $this->app = $app;
        $this->nombre = $app->nombre;
    }

    public function editar(): void
    {
        abort_if(! auth()->user()->hasPermission('editar-apps'), 403);
        $this->editando = true;
    }

    public function guardar(): void
    {
        abort_if(! auth()->user()->hasPermission('editar-apps'), 403);
        $this->validate(['nombre' => 'required|string|max:255']);
        $this->app->nombre = $this->nombre;
        $this->app->save();
        $this->editando = false;
        cache()->increment('apps.cache_version');
    }

    public function render()
    {
        return view('apps::livewire.apps.editar-nombre-app');
    }
}
