<?php

namespace Modules\Apps\Livewire\Apps;

use Livewire\Component;
use Modules\Apps\Entities\App;

class EditarDescripcionApp extends Component
{
    public App $app;
    public string $descripcion = '';
    public $editando = false;

    public function mount(App $app): void
    {
        $this->app = $app;
        $this->descripcion = $app->descripcion ?? '';
    }

    public function editar(): void
    {
        abort_if(! auth()->user()->hasPermission('editar-apps'), 403);
        $this->editando = true;
    }

    public function guardar(): void
    {
        abort_if(! auth()->user()->hasPermission('editar-apps'), 403);
        $this->validate(['descripcion' => 'nullable|string|max:500']);
        $this->app->descripcion = $this->descripcion ?: null;
        $this->app->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('apps::livewire.apps.editar-descripcion-app');
    }
}
