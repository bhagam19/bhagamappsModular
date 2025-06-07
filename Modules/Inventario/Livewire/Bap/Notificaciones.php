<?php

namespace Modules\Inventario\Livewire\Notifications;

use Livewire\Component;
use Modules\Users\Models\User;
use Modules\Inventario\Entities\BienAprobacionPendiente;
use Modules\Inventario\Entities\HistorialModificacionBien;

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
        $this->cambiosPendientes = BienAprobacionPendiente::with('user', 'bien')
            ->where('estado', 'pendiente')
            ->get();
    }

    public function aprobarCambio($id)
    {
        $this->authorize('aprobar-cambios-bienes');

        $cambio = BienAprobacionPendiente::findOrFail($id);
        $bien = $cambio->bien;

        if (!$bien) {
            $this->dispatch('mensaje-error', ['mensaje' => 'Bien no encontrado.']);
            return;
        }

        DB::beginTransaction();

        try {
            // Aplica el nuevo valor
            $bien->{$cambio->campo} = $cambio->valor_nuevo;
            $bien->save();

            // Guarda en historial
            HistorialModificacionBien::create([
                'bien_id' => $bien->id,
                'campo_modificado' => $cambio->campo,
                'valor_anterior' => $cambio->valor_anterior,
                'valor_nuevo' => $cambio->valor_nuevo,
                'modificado_por' => auth()->id(),
            ]);

            // Cambia estado a aprobado
            $cambio->estado = 'aprobado';
            $cambio->save();

            DB::commit();

            $this->cargarCambiosPendientes();
            $this->dispatch('mensaje-exito', ['mensaje' => 'Cambio aprobado correctamente.']);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('mensaje-error', ['mensaje' => 'Error al aprobar el cambio.']);
        }
    }

    public function rechazarCambio($id)
    {
        $this->authorize('rechazar-cambios-bienes');

        $cambio = BienAprobacionPendiente::findOrFail($id);
        $cambio->estado = 'rechazado';
        $cambio->save();

        $this->cargarCambiosPendientes();
        $this->dispatch('mensaje-exito', ['mensaje' => 'Cambio rechazado correctamente.']);
    }

    public function render()
    {
        return view('inventario::livewire.notifications.notificaciones');
    }
}