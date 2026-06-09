<?php

namespace Modules\Apps\Livewire\Apps;

use Livewire\Component;
use Modules\Apps\Entities\App;

class EditarIconoApp extends Component
{
    public App $app;
    public string $icono = '';
    public $editando = false;

    public function mount(App $app): void
    {
        $this->app = $app;
        $this->icono = $app->icono ?? '';
    }

    public function editar(): void
    {
        abort_if(! auth()->user()->hasPermission('editar-apps'), 403);
        $this->editando = true;
    }

    public function guardar(): void
    {
        abort_if(! auth()->user()->hasPermission('editar-apps'), 403);
        $this->validate(['icono' => 'nullable|string|max:100']);
        $this->app->icono = $this->icono ?: null;
        $this->app->save();
        $this->editando = false;
        cache()->increment('apps.cache_version');
    }

    public function render()
    {
        return view('apps::livewire.apps.editar-icono-app');
    }
}
