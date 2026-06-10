<?php

namespace Modules\Inventario\Livewire\Ubicaciones;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Modules\Inventario\Entities\Bien;
use Modules\Inventario\Entities\Ubicacion;
use Modules\Inventario\Entities\HistorialUbicacionBien;
use Modules\Inventario\Entities\Dependencia;

class HistorialUbicacionesBien extends Component
{
    use WithPagination;

    public string $busqueda = '';
    public int $perPage = 25;
    public string $sortField = 'nombre';
    public string $sortDirection = 'asc';
    public ?int $filtroDependencia = null;

    public ?int $cambiandoBienId = null;
    public ?int $historialBienId = null;

    // Formulario de cambio de ubicación
    public ?int $nuevaUbicacionId = null;
    public string $nuevaFechaMovimiento = '';
    public string $nuevasObservaciones = '';

    public array $dependencias = [];
    public array $ubicaciones = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasPermission('ver-historial-ubicaciones-bienes'), 403);
        $this->nuevaFechaMovimiento = now()->toDateString();
        $this->cargarCatalogos();
    }

    private function cargarCatalogos(): void
    {
        $this->dependencias = Dependencia::orderBy('nombre')->pluck('nombre', 'id')->toArray();
        $this->ubicaciones  = Ubicacion::orderBy('nombre')->pluck('nombre', 'id')->toArray();
    }

    public function updatingBusqueda(): void        { $this->resetPage(); }
    public function updatingFiltroDependencia(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function iniciarCambio(int $bienId): void
    {
        abort_unless(auth()->user()?->hasPermission('cambiar-ubicacion-bienes'), 403);
        $this->resetFormulario();
        $this->cambiandoBienId   = $bienId;
        $this->historialBienId   = null;
    }

    public function confirmarCambio(): void
    {
        abort_unless(auth()->user()?->hasPermission('cambiar-ubicacion-bienes'), 403);

        $this->validate([
            'nuevaUbicacionId'      => 'required|exists:ubicaciones,id',
            'nuevaFechaMovimiento'  => 'required|date',
            'nuevasObservaciones'   => 'nullable|string|max:500',
        ]);

        $bien = Bien::with('ubicacionActual.ubicacionDestino')->findOrFail($this->cambiandoBienId);

        // RI-002: la ubicación destino debe existir (validado arriba)
        // RI-003: el origen es la ubicación destino del último historial
        $origenId = $bien->ubicacionActual?->ubicacion_destino_id;

        HistorialUbicacionBien::create([
            'bien_id'              => $this->cambiandoBienId,
            'ubicacion_origen_id'  => $origenId,
            'ubicacion_destino_id' => $this->nuevaUbicacionId,
            'user_id'              => auth()->id(),
            'fecha_movimiento'     => $this->nuevaFechaMovimiento,
            'observaciones'        => $this->nuevasObservaciones ?: null,
        ]);

        $this->resetFormulario();
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Ubicación actualizada correctamente.');
    }

    public function toggleHistorial(int $bienId): void
    {
        $this->historialBienId = ($this->historialBienId === $bienId) ? null : $bienId;
        $this->resetFormulario();
    }

    public function cancelar(): void
    {
        $this->resetFormulario();
    }

    private function resetFormulario(): void
    {
        $this->cambiandoBienId       = null;
        $this->nuevaUbicacionId      = null;
        $this->nuevaFechaMovimiento  = now()->toDateString();
        $this->nuevasObservaciones   = '';
    }

    public function render()
    {
        $columnasSortables = ['nombre', 'serie', 'dependencia_id'];
        $sortField = in_array($this->sortField, $columnasSortables) ? $this->sortField : 'nombre';

        $bienes = Bien::with(['dependencia', 'ubicacionActual.ubicacionDestino'])
            ->when($this->busqueda, fn($q) => $q->where('nombre', 'like', '%' . $this->busqueda . '%'))
            ->when($this->filtroDependencia, fn($q) => $q->where('dependencia_id', $this->filtroDependencia))
            ->when($sortField === 'dependencia_id',
                fn($q) => $q->join('dependencias as dep_sort', 'bienes.dependencia_id', '=', 'dep_sort.id')
                             ->orderBy('dep_sort.nombre', $this->sortDirection)
                             ->select('bienes.*'),
                fn($q) => $q->orderBy($sortField, $this->sortDirection)
            )
            ->paginate($this->perPage);

        $historial = collect();
        if ($this->historialBienId) {
            $historial = HistorialUbicacionBien::with(['ubicacionOrigen', 'ubicacionDestino', 'user'])
                ->where('bien_id', $this->historialBienId)
                ->orderByDesc('fecha_movimiento')
                ->get();
        }

        return view('inventario::livewire.ubicaciones.historial-ubicaciones-bien', [
            'bienes'   => $bienes,
            'historial' => $historial,
        ]);
    }
}
