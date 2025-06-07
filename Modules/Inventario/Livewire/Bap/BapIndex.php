<?php

namespace Modules\Inventario\Livewire\Bap  ;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Inventario\Entities\BienAprobacionPendiente;
use Modules\Inventario\Entities\HistorialModificacionBien;
use Illuminate\Support\Facades\DB;

class BapIndex extends Component
{
    use WithPagination;
    
     // --- Paginación y orden ---
    public $perPage = 10;
    protected $paginationTheme = 'bootstrap';
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    public function mount()
    {
        if (!auth()->user()->hasPermission('ver-aprobaciones-pendientes')) {
            return redirect()->route('inventario.bienes.index');
        }
    }

    protected $listeners = ['cambioActualizado' => '$refresh'];

    public function render()
    {
        $aprobacionesPendientes = BienAprobacionPendiente::with(['bien', 'user'])
            ->latest()
            ->paginate($this->perPage);

        return view('inventario::livewire.bap.bap-index', [
            'aprobacionesPendientes' => $aprobacionesPendientes,
        ]);
    }

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc')
            : 'asc';

        $this->sortField = $field;
    }    

    public function aprobarCambio($id)
    {
        $cambio = BienAprobacionPendiente::with('bien')->find($id);

        if (!$cambio || !$cambio->bien) {
            session()->flash('error', 'No se pudo encontrar el bien asociado al cambio.');
            return;
        }

        DB::beginTransaction();

        try {
            $bien = $cambio->bien;
            $campo = $cambio->campo;

            if (!array_key_exists($campo, $bien->getAttributes())) {
                session()->flash('error', "El campo '$campo' no existe en el modelo de Bien.");
                return;
            }

            $valorAnterior = $bien->$campo;
            $bien->$campo = $cambio->valor_nuevo;
            $bien->save();

            HistorialModificacionBien::create([
                'bien_id' => $bien->id,
                'campo_modificado' => $campo,
                'valor_anterior' => $valorAnterior,
                'valor_nuevo' => $cambio->valor_nuevo,
                'modificado_por' => auth()->id(),
            ]);

            $cambio->delete();

            DB::commit();

            session()->flash('message', 'Cambio aprobado correctamente.');
            $this->emit('cambioActualizado');

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            session()->flash('error', 'Ocurrió un error al aprobar el cambio.');
        }
    }

    public function rechazarCambio($id)
    {
        try {
            $cambio = BienAprobacionPendiente::find($id);

            if (!$cambio) {
                session()->flash('error', 'El cambio no fue encontrado.');
                return;
            }

            $cambio->delete();

            session()->flash('message', 'Cambio rechazado correctamente.');
            $this->emit('cambioActualizado');

        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Ocurrió un error al rechazar el cambio.');
        }
    }
}
