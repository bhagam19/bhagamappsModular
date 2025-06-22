<?php

namespace Modules\Inventario\Livewire\Hmb;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Inventario\Entities\{
    Bien,
    HistorialModificacionBien,
    HistorialModificacionBien,
    Detalle,
};
use Illuminate\Support\Facades\DB;

class NotificacionesDropdown extends Component
{
    use WithPagination;

    public $perPage = 10;

    protected $paginationTheme = 'bootstrap'; // Cambia a 'tailwind' si usas Tailwind CSS

    public function render()
    {
        $modificacionesPendientes = HistorialModificacionBien::with(['bien', 'user'])
            ->latest()
            ->paginate($this->perPage);

        return view('inventario::livewire.hmb.notificaciones-dropdown', [
            'modificacionesPendientes' => $modificacionesPendientes,
        ]);
    }

    public function aprobarCambio($id)
    {
        $cambio = HistorialModificacionBien::find($id);

        $bien = Bien::with('dependencia')->find($cambio->bien_id);
        $usuario = $bien->dependencia->usuario_id;

        if (!$cambio) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'El cambio no fue encontrado.');
            $this->dispatch('cambioActualizado');
            return;
        }

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

                $valorAnterior = $bien->$campo;
                $bien->$campo = $cambio->valor_nuevo;
                $bien->save();

                HistorialModificacionBien::create([
                    'bien_id' => $bien->id,
                    'tipo_objeto' => 'bien',
                    'campo_modificado' => $campo,
                    'valor_anterior' => $valorAnterior,
                    'valor_nuevo' => $cambio->valor_nuevo,
                    'usuario_id' => $usuario,               // quien hizo el cambio
                    'aprobado_por' => auth()->id(),        // quien aprobó el cambio
                    'fecha_modificacion' => now(),
                ]);
            }

            if ($cambio->tipo_objeto === 'detalle') {
                $detalle = Detalle::firstOrNew(['bien_id' => $cambio->bien_id]);

                $datos = json_decode($cambio->valor_nuevo, true);

                if (!is_array($datos)) {
                    throw new \Exception('El valor nuevo no es un JSON válido.');
                }

                foreach ($datos as $campo => $valorNuevo) {
                    if (array_key_exists($campo, $detalle->getAttributes())) {
                        $valorAnterior = $detalle->$campo;

                        // Guardar en el historial de modificaciones
                        HistorialModificacionBien::create([
                            'bien_id' => $detalle->bien_id,
                            'tipo_objeto' => 'detalle',
                            'campo_modificado' => $campo,
                            'valor_anterior' => $valorAnterior,
                            'valor_nuevo' => $valorNuevo,
                            'usuario_id' => $usuario,
                            'aprobado_por' => auth()->id(),
                            'fecha_modificacion' => now(),
                        ]);

                        // Actualizar el campo
                        $detalle->$campo = $valorNuevo;
                    }
                }

                $detalle->save();
            }

            $cambio->delete();
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

            $cambio->delete();

            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Cambio rechazado correctamente.');

            $this->dispatch('cambioActualizado');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Ocurrió un error al rechazar el cambio.');
            $this->dispatch('cambioActualizado');
        }
    }
}
