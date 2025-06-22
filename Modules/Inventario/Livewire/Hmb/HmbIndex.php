<?php

namespace Modules\Inventario\Livewire\Hmb;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Inventario\Entities\{
    Bien,
    HistorialModificacionBien,
    Detalle,
    HistorialDependenciaBien,
};
use Illuminate\Support\Facades\DB;

class HmbIndex extends Component
{
    use WithPagination;

    // --- Paginación y orden ---
    public $perPage = 10;
    protected $paginationTheme = 'bootstrap';
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    public function mount()
    {
        if (!auth()->user()->hasPermission('gestionar-historial-modificaciones-bienes')) {
            return redirect()->route('inventario.bienes.index');
        }
    }

    protected $listeners = ['modificacionActualizada' => '$refresh'];

    public function render()
    {
        $modificacionesPendientes = HistorialModificacionBien::with([
            'bien',
            'valorAnteriorCategoria',
            'valorNuevoCategoria',
            'valorAnteriorDependencia',
            'valorNuevoDependencia',
            'valorAnteriorEstado',
            'valorNuevoEstado',

        ])
            ->latest()
            ->paginate($this->perPage);

        return view('inventario::livewire.hmb.hmb-index', [
            'modificacionesPendientes' => $modificacionesPendientes,
        ]);
    }

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc')
            : 'asc';

        $this->sortField = $field;
    }

    public function aprobarModificacion($id)
    {
        $modificacion = HistorialModificacionBien::find($id);

        logger()->info('Aprobando modificacion con ID: ' . $id);

        $bien = Bien::with('dependencia')->find($modificacion->bien_id);
        $usuario = $bien->dependencia->usuario_id;

        if (!$modificacion) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'La modificacion no fue encontrada.');
            $this->dispatch('modificacionActualizado');
            return;
        }

        DB::beginTransaction();

        try {
            if ($modificacion->tipo_objeto === 'bien') {

                if (!$bien) {
                    throw new \Exception('No se encontró el bien asociado.');
                }

                $campo = $modificacion->campo;

                if (!array_key_exists($campo, $bien->getAttributes())) {
                    throw new \Exception("El campo '$campo' no existe en el modelo Bien.");
                }

                $bien->$campo = $modificacion->valor_nuevo;
                $bien->save();

                $modificacion->estado = 'aprobada';
                $modificacion->aprobado_por = auth()->id();
                $modificacion->save();

                // Si el campo modificado es dependencia_id, guardar también en historial_dependencias_bienes
                if ($campo === 'dependencia_id') {
                    HistorialDependenciaBien::create([
                        'bien_id' => $bien->id,
                        'dependencia_anterior_id' => $modificacion->valor_anterior,
                        'dependencia_nueva_id' => $modificacion->valor_nuevo,
                        'aprobado_por' => auth()->id(),        // quien aprobó el modificacion                        
                    ]);
                }
            }

            if ($modificacion->tipo_objeto === 'detalle') {
                $detalle = Detalle::firstOrNew(['bien_id' => $modificacion->bien_id]);

                $datos = json_decode($modificacion->valor_nuevo, true);

                if (!is_array($datos)) {
                    throw new \Exception('El valor nuevo no es un JSON válido.');
                }

                foreach ($datos as $campo => $valorNuevo) {
                    if (array_key_exists($campo, $detalle->getAttributes())) {

                        // Actualizar el campo
                        $detalle->$campo = $valorNuevo;
                    }
                }

                $detalle->save();

                $modificacion->estado = 'aprobada';
                $modificacion->aprobado_por = auth()->id();
                $modificacion->save();
            }

            DB::commit();

            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Modificacion aprobada correctamente.');
            $this->dispatch('modificacionActualizada');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Ocurrió un error al aprobar el modificacion.');
            $this->dispatch('modificacionActualizad');
        }
    }

    public function rechazarModificacion($id)
    {
        try {
            $modificacion = HistorialModificacionBien::find($id);

            if (!$modificacion) {
                $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'La modificacion no fue encontrada.');
                $this->dispatch('modificacionActualizada');
                return;
            }

            $modificacion->estado = 'rechazada';
            $modificacion->aprobado_por = auth()->id();
            $modificacion->save();

            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Modificacion rechazada correctamente.');

            $this->dispatch('modificacionActualizada');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Ocurrió un error al rechazar el modificacion.');
            $this->dispatch('modificacionActualizada');
        }
    }
}
