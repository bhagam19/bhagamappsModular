<?php

namespace Modules\Inventario\Livewire\Notifications;

use Livewire\Component;
use Modules\Inventario\Entities\HistorialModificacionBien;


class NotificacionesIcono extends Component
{
    public $total = 0;

    public function mount()
    {
        $this->total = HistorialModificacionBien::where('estado', 'pendiente')->count();
    }

    public function render()
    {
        return view('inventario::livewire.notifications.notificaciones-icono');
    }
}
