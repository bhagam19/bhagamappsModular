<?php

namespace Modules\Apps\Livewire\Apps;

use Livewire\Component;
use Modules\Apps\Entities\App;

class EditarColorApp extends Component
{
    public App $app;
    public string $color = '';
    public $editando = false;

    public function mount(App $app): void
    {
        $this->app = $app;
        $this->color = $app->color ?? '#666666';
    }

    public function editar(): void
    {
        abort_if(! auth()->user()->hasPermission('editar-apps'), 403);
        $this->editando = true;
    }

    public function guardar(): void
    {
        abort_if(! auth()->user()->hasPermission('editar-apps'), 403);
        $this->validate(['color' => 'nullable|string|max:20']);
        $this->app->color = $this->color ?: null;
        $this->app->save();
        $this->editando = false;
        cache()->increment('apps.cache_version');
    }

    public function render()
    {
        return view('apps::livewire.apps.editar-color-app');
    }
}
