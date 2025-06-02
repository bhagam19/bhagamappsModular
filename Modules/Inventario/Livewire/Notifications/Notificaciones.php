<?php

namespace Modules\Inventario\Livewire\Notifications;

use Livewire\Component;
use Modules\Users\Models\User;
use Modules\Inventario\Entities\BienAprobacionPendiente;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Notificaciones extends Component
{
    public $cambiosPendientes;

    use AuthorizesRequests;

    protected $listeners = [
        'actualizarCambiosPendientes' => 'cargarCambiosPendientes'
    ];

    public function mount()
    {
        $this->cargarCambiosPendientes();
    }

    public function cargarCambiosPendientes()
    {
        $this->cambiosPendientes = BienAprobacionPendiente::with('usuario')
            ->where('estado', 'pendiente')
            ->get();
    }

    public function aprobarCambio($id)
    {
        $this->authorize('aprobar-cambios-bienes');

        $cambio = BienAprobacionPendiente::findOrFail($id);
        
        // Aquí va la lógica para aplicar el cambio a la tabla bienes o detalles
        // Por ejemplo:
        $bien = $cambio->bien;
        if ($bien) {
            $bien->{$cambio->campo} = $cambio->valor_nuevo;
            $bien->save();
        }
        
        $cambio->estado = 'aprobado';
        $cambio->save();

        $this->cargarCambiosPendientes();
        $this->dispatchBrowserEvent('mensaje-exito', ['mensaje' => 'Cambio aprobado correctamente.']);
    }

    public function rechazarCambio($id)
    {
        $this->authorize('rechazar-cambios-bienes');

        $cambio = BienAprobacionPendiente::findOrFail($id);
        $cambio->estado = 'rechazado';
        $cambio->save();

        $this->cargarCambiosPendientes();
        $this->dispatchBrowserEvent('mensaje-exito', ['mensaje' => 'Cambio rechazado correctamente.']);
    }

    public function render()
    {
        return view('inventario::livewire.notifications.notificaciones');
    }
}