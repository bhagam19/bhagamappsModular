<?php

namespace Modules\Apps\Livewire\Apps;

use Livewire\Component;
use Modules\Apps\Entities\App;

class EditarSlugApp extends Component
{
    public App $app;
    public string $slug = '';
    public $editando = false;

    public function mount(App $app): void
    {
        $this->app = $app;
        $this->slug = $app->slug ?? '';
    }

    public function editar(): void
    {
        abort_if(! auth()->user()->hasPermission('editar-apps'), 403);
        $this->editando = true;
    }

    public function guardar(): void
    {
        abort_if(! auth()->user()->hasPermission('editar-apps'), 403);
        $this->validate([
            'slug' => 'nullable|string|max:255|unique:apps,slug,' . $this->app->id,
        ]);
        $this->app->slug = $this->slug ?: null;
        $this->app->save();
        cache()->increment('apps.cache_version');
        $this->editando = false;
    }

    public function render()
    {
        return view('apps::livewire.apps.editar-slug-app');
    }
}
