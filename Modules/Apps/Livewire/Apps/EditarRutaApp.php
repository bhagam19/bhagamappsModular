<?php

namespace Modules\Apps\Livewire\Apps;

use Livewire\Component;
use Modules\Apps\Entities\App;

class EditarRutaApp extends Component
{
    public App $app;
    public string $ruta = '';
    public $editando = false;

    public function mount(App $app): void
    {
        $this->app = $app;
        $this->ruta = $app->ruta;
    }

    public function editar(): void
    {
        abort_if(! auth()->user()->hasPermission('editar-apps'), 403);
        $this->editando = true;
    }

    public function guardar(): void
    {
        abort_if(! auth()->user()->hasPermission('editar-apps'), 403);
        $this->validate(['ruta' => 'required|string|max:255']);
        $this->app->ruta = $this->ruta;
        $this->app->save();
        $this->editando = false;
    }

    public function render()
    {
        return view('apps::livewire.apps.editar-ruta-app');
    }
}
