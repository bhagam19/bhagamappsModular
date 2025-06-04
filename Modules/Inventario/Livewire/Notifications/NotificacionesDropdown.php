<?php

namespace Modules\Inventario\Livewire\Notifications;

use Livewire\Component;
use Modules\Inventario\Entities\BienAprobacionPendiente;

class NotificacionesDropdown extends Component
{
    public $cambiosPendientes;

    public function mount()
    {
        $this->cambiosPendientes = BienAprobacionPendiente::with('user')->latest()->get();
    }

    public function render()
    {
        return view('inventario::livewire.notifications.notificaciones-dropdown');
    }

    public function aprobarCambio($id)
    {
        // Lógica para aprobar
    }

    public function rechazarCambio($id)
    {
        // Lógica para rechazar
    }
}
