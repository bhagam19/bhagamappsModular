<?php

namespace Modules\Inventario\Livewire\Notifications;

use Livewire\Component;
use Modules\Inventario\Entities\BienAprobacionPendiente;


class NotificacionesIcono extends Component
{
    public $total = 0;

    public function mount()
    {
        $this->total = BienAprobacionPendiente::where('estado', 'pendiente')->count();
    }

    public function render()
    {
        return view('inventario::livewire.notifications.notificaciones-icono');
    }
}
