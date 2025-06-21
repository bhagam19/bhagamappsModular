<?php

namespace Modules\Inventario\Livewire\Heb;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Inventario\Entities\{
    HistorialEliminacionBien,
    Bien
};
use Illuminate\Support\Facades\DB;

class HebIndex extends Component
{
    use WithPagination;

    // --- Paginación y orden ---
    public $perPage = 10;
    protected $paginationTheme = 'bootstrap';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    protected $listeners = ['eliminacionActualizada' => '$refresh'];

    public function mount()
    {
        if (!auth()->user()->hasPermission('gestionar-historial-eliminaciones-bienes')) {
            return redirect()->route('inventario.bienes.index');
        }
    }

    public function render()
    {
        $solicitudes = HistorialEliminacionBien::with(['bien', 'usuario', 'dependencia'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('inventario::livewire.heb.heb-index', [
            'solicitudes' => $solicitudes,
        ]);
    }

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc')
            : 'asc';

        $this->sortField = $field;
    }

    public function aprobarEliminacion($id)
    {
        $solicitud = HistorialEliminacionBien::with('bien')->find($id);

        if (!$solicitud || !$solicitud->bien) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No se encontró la solicitud o el bien.');
            $this->dispatch('eliminacionActualizada');
            return;
        }

        DB::beginTransaction();

        try {
            $solicitud->bien->delete(); // Soft delete

            $solicitud->estado = 'aprobado';
            $solicitud->aprobado_por = auth()->id();
            $solicitud->save();

            DB::commit();

            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Eliminación aprobada correctamente.');
            $this->dispatch('eliminacionActualizada');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Error al aprobar la eliminación.');
            $this->dispatch('eliminacionActualizada');
        }
    }

    public function rechazarEliminacion($id)
    {
        try {
            $solicitud = HistorialEliminacionBien::find($id);

            if (!$solicitud) {
                $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'No se encontró la solicitud.');
                $this->dispatch('eliminacionActualizada');
                return;
            }

            $solicitud->estado = 'rechazado';
            $solicitud->aprobado_por = auth()->id();
            $solicitud->save();

            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Eliminación rechazada.');
            $this->dispatch('eliminacionActualizada');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Error al rechazar la eliminación.');
            $this->dispatch('eliminacionActualizada');
        }
    }
}
