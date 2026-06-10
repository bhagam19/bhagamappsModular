<?php

namespace Modules\Inventario\Livewire\Notifications;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Inventario\Entities\{
    Bien,
    HistorialModificacionBien,
    HistorialDependenciaBien,
    Detalle,
};
use Illuminate\Support\Facades\DB;

class NotificacionesDropdown extends Component
{
    use WithPagination;

    public $perPage = 10;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $modificacionesPendientes = HistorialModificacionBien::with(['bien', 'user'])
            ->where('estado', 'pendiente')
            ->latest()
            ->paginate($this->perPage);

        return view('inventario::livewire.hmb.notificaciones-dropdown', [
            'modificacionesPendientes' => $modificacionesPendientes,
        ]);
    }

    public function aprobarCambio($id)
    {
        $cambio = HistorialModificacionBien::find($id);

        if (!$cambio) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'El cambio no fue encontrado.');
            $this->dispatch('cambioActualizado');
            return;
        }

        $bien = Bien::with('dependencia')->find($cambio->bien_id);

        DB::beginTransaction();

        try {
            if ($cambio->tipo_objeto === 'bien') {

                if (!$bien) {
                    throw new \Exception('No se encontró el bien asociado.');
                }

                $campo = $cambio->campo;

                if (!array_key_exists($campo, $bien->getAttributes())) {
                    throw new \Exception("El campo '$campo' no existe en el modelo Bien.");
                }

                $bien->$campo = $cambio->valor_nuevo;
                $bien->save();

                $cambio->estado = 'aprobada';
                $cambio->aprobado_por = auth()->id();
                $cambio->save();

                if ($campo === 'dependencia_id') {
                    HistorialDependenciaBien::create([
                        'bien_id'                => $bien->id,
                        'dependencia_anterior_id' => $cambio->valor_anterior,
                        'dependencia_nueva_id'    => $cambio->valor_nuevo,
                        'aprobado_por'            => auth()->id(),
                    ]);
                }
            }

            if ($cambio->tipo_objeto === 'detalle') {
                $detalle = Detalle::firstOrNew(['bien_id' => $cambio->bien_id]);

                $datos = json_decode($cambio->valor_nuevo, true);

                if (!is_array($datos)) {
                    throw new \Exception('El valor nuevo no es un JSON válido.');
                }

                foreach ($datos as $campo => $valorNuevo) {
                    if (array_key_exists($campo, $detalle->getAttributes())) {
                        $detalle->$campo = $valorNuevo;
                    }
                }

                $detalle->save();

                $cambio->estado = 'aprobada';
                $cambio->aprobado_por = auth()->id();
                $cambio->save();
            }

            DB::commit();

            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Cambio aprobado correctamente.');
            $this->dispatch('cambioActualizado');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Ocurrió un error al aprobar el cambio.');
            $this->dispatch('cambioActualizado');
        }
    }

    public function rechazarCambio($id)
    {
        try {
            $cambio = HistorialModificacionBien::find($id);

            if (!$cambio) {
                $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'El cambio no fue encontrado.');
                $this->dispatch('cambioActualizado');
                return;
            }

            $cambio->estado = 'rechazada';
            $cambio->aprobado_por = auth()->id();
            $cambio->save();

            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Cambio rechazado correctamente.');
            $this->dispatch('cambioActualizado');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Ocurrió un error al rechazar el cambio.');
            $this->dispatch('cambioActualizado');
        }
    }
}
