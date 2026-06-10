<?php

namespace Modules\Inventario\Livewire\Notifications;

use Livewire\Component;
use Livewire\Attributes\On;
use Modules\Inventario\Entities\HistorialModificacionBien;

class NotificacionesIcono extends Component
{
    public $total = 0;

    public function mount()
    {
        $this->total = HistorialModificacionBien::where('estado', 'pendiente')->count();
    }

    #[On('cambioActualizado')]
    public function actualizarContador()
    {
        $this->total = HistorialModificacionBien::where('estado', 'pendiente')->count();
    }

    public function render()
    {
        return view('inventario::livewire.hmb.notificaciones-icono');
    }
}
