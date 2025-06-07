<?php

namespace Modules\Inventario\Livewire\Bap;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Inventario\Entities\BienAprobacionPendiente;
use Modules\Inventario\Entities\HistorialModificacionBien;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotificacionesDropdown extends Component
{
    use WithPagination;

    public $perPage = 10;

    protected $paginationTheme = 'bootstrap'; // Cambia a 'tailwind' si usas Tailwind CSS

    public function render()
    {
        return view('inventario::livewire.bap.notificaciones-dropdown', [
            'cambiosPendientes' => BienAprobacionPendiente::with('user', 'bien')->latest()->paginate($this->perPage),
        ]);
    }

    public function aprobarCambio($id)
    {
        logger('Intentando aprobar ID: ' . $id);

        if (!auth()->user()->hasPermission('aprobar-cambios-bienes')) {
            $this->dispatch('mensaje-error', ['mensaje' => 'No tienes permiso para aprobar cambios.']);
            return;
        }

        $cambio = BienAprobacionPendiente::findOrFail($id);
        $bien = $cambio->bien;

        if (!$bien) {
            $this->dispatch('mensaje-error', ['mensaje' => 'Bien no encontrado.']);
            return;
        }

        if (!Schema::hasColumn('bienes', $cambio->campo)) {
            $this->dispatch('mensaje-error', ['mensaje' => "El campo '{$cambio->campo}' no existe."]);
            return;
        }

        DB::beginTransaction();

        try {
            $bien->{$cambio->campo} = $cambio->valor_nuevo;
            $bien->save();

            HistorialModificacionBien::create([
                'bien_id' => $bien->id,
                'campo_modificado' => $cambio->campo,
                'valor_anterior' => $cambio->valor_anterior,
                'valor_nuevo' => $cambio->valor_nuevo,
                'modificado_por' => auth()->id(),
            ]);

            $cambio->delete();

            DB::commit();

            $this->resetPage(); // Reinicia a la página 1 si es necesario
            $this->dispatch('mensaje-exito', ['mensaje' => 'Cambio aprobado correctamente.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('mensaje-error', ['mensaje' => 'Error al aprobar el cambio.']);
        }
    }

    public function rechazarCambio($id)
    {
        if (!auth()->user()->hasPermission('aprobar-cambios-bienes')) {
            $this->dispatch('mensaje-error', ['mensaje' => 'No tienes permiso para rechazar cambios.']);
            return;
        }

        try {
            $cambio = BienAprobacionPendiente::findOrFail($id);
            $cambio->delete();

            $this->resetPage(); // Reinicia la paginación si quedaste en una página vacía
            $this->dispatch('mensaje-exito', ['mensaje' => 'Cambio rechazado correctamente.']);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('mensaje-error', ['mensaje' => 'Error al rechazar el cambio.']);
        }
    }
}
