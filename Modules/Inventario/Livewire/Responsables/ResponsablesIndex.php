<?php

namespace Modules\Inventario\Livewire\Responsables;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Inventario\Entities\Bien;
use Modules\Inventario\Entities\BienResponsable;
use Modules\Inventario\Entities\Dependencia;
use Modules\User\Entities\User;

class ResponsablesIndex extends Component
{
    use WithPagination;

    // Filtros y paginación
    public string $busqueda = '';
    public int $perPage = 25;
    public string $sortField = 'nombre';
    public string $sortDirection = 'asc';
    public ?int $filtroDependencia = null;
    public ?int $filtroResponsable = null;

    // Estado de operación activa
    public ?int $asignandoBienId = null;
    public ?int $transfiriendoBienId = null;
    public ?int $historialBienId = null;

    // Formulario compartido asignar / transferir
    public ?int $nuevoUserId = null;
    public string $nuevaFechaAsignacion = '';
    public string $nuevasObservaciones = '';

    // Catálogos para dropdowns
    public array $dependencias = [];
    public array $usuarios = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasPermission('ver-responsables-bienes'), 403);
        $this->nuevaFechaAsignacion = now()->toDateString();
        $this->cargarCatalogos();
    }

    private function cargarCatalogos(): void
    {
        $this->dependencias = Dependencia::orderBy('nombre')->pluck('nombre', 'id')->toArray();
        $this->usuarios = User::orderBy('nombres')->orderBy('apellidos')
            ->get(['id', 'nombres', 'apellidos'])
            ->mapWithKeys(fn($u) => [$u->id => $u->nombre_completo])
            ->toArray();
    }

    public function updatingBusqueda(): void       { $this->resetPage(); }
    public function updatingFiltroDependencia(): void { $this->resetPage(); }
    public function updatingFiltroResponsable(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField    = $field;
            $this->sortDirection = 'asc';
        }
    }

    // --- Asignar ---

    public function iniciarAsignacion(int $bienId): void
    {
        abort_unless(auth()->user()?->hasPermission('asignar-responsables-bienes'), 403);
        $this->resetFormulario();
        $this->asignandoBienId = $bienId;
        $this->historialBienId = null;
    }

    public function confirmarAsignacion(): void
    {
        abort_unless(auth()->user()?->hasPermission('asignar-responsables-bienes'), 403);

        $this->validate([
            'nuevoUserId'          => 'required|exists:users,id',
            'nuevaFechaAsignacion' => 'required|date',
            'nuevasObservaciones'  => 'nullable|string|max:500',
        ]);

        // RI-001 / RI-003: cerrar responsable activo anterior si existe
        BienResponsable::where('bien_id', $this->asignandoBienId)
            ->whereNull('fecha_retiro')
            ->update(['fecha_retiro' => $this->nuevaFechaAsignacion]);

        BienResponsable::create([
            'bien_id'          => $this->asignandoBienId,
            'user_id'          => $this->nuevoUserId,
            'fecha_asignacion' => $this->nuevaFechaAsignacion,
            'fecha_retiro'     => null,
            'observaciones'    => $this->nuevasObservaciones ?: null,
        ]);

        $this->resetFormulario();
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Responsable asignado correctamente.');
    }

    // --- Transferir ---

    public function iniciarTransferencia(int $bienId): void
    {
        abort_unless(auth()->user()?->hasPermission('transferir-responsables-bienes'), 403);
        $this->resetFormulario();
        $this->transfiriendoBienId = $bienId;
        $this->historialBienId = null;
    }

    public function confirmarTransferencia(): void
    {
        abort_unless(auth()->user()?->hasPermission('transferir-responsables-bienes'), 403);

        $this->validate([
            'nuevoUserId'          => 'required|exists:users,id',
            'nuevaFechaAsignacion' => 'required|date',
            'nuevasObservaciones'  => 'nullable|string|max:500',
        ]);

        // RI-002: fecha_retiro del anterior = fecha_asignacion del nuevo
        BienResponsable::where('bien_id', $this->transfiriendoBienId)
            ->whereNull('fecha_retiro')
            ->update(['fecha_retiro' => $this->nuevaFechaAsignacion]);

        BienResponsable::create([
            'bien_id'          => $this->transfiriendoBienId,
            'user_id'          => $this->nuevoUserId,
            'fecha_asignacion' => $this->nuevaFechaAsignacion,
            'fecha_retiro'     => null,
            'observaciones'    => $this->nuevasObservaciones ?: null,
        ]);

        $this->resetFormulario();
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Responsable transferido correctamente.');
    }

    // --- Liberar responsable ---

    public function liberarResponsable(int $bienId): void
    {
        abort_unless(auth()->user()?->hasPermission('editar-responsables-bienes'), 403);

        BienResponsable::where('bien_id', $bienId)
            ->whereNull('fecha_retiro')
            ->update(['fecha_retiro' => now()->toDateString()]);

        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Responsable liberado del bien.');
    }

    // --- Historial ---

    public function toggleHistorial(int $bienId): void
    {
        $this->historialBienId = ($this->historialBienId === $bienId) ? null : $bienId;
        $this->resetFormulario();
    }

    // --- Cancelar ---

    public function cancelar(): void
    {
        $this->resetFormulario();
    }

    private function resetFormulario(): void
    {
        $this->asignandoBienId    = null;
        $this->transfiriendoBienId = null;
        $this->nuevoUserId         = null;
        $this->nuevaFechaAsignacion = now()->toDateString();
        $this->nuevasObservaciones  = '';
    }

    public function render()
    {
        $bienes = Bien::query()
            ->with(['dependencia', 'responsableActual.user'])
            ->when($this->busqueda, fn($q) => $q->where('nombre', 'like', '%' . $this->busqueda . '%'))
            ->when($this->filtroDependencia, fn($q) => $q->where('dependencia_id', $this->filtroDependencia))
            ->when($this->filtroResponsable, function ($q) {
                $q->whereHas('responsableActual', fn($sub) => $sub->where('user_id', $this->filtroResponsable));
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $historial = collect();
        if ($this->historialBienId) {
            $historial = BienResponsable::with('user')
                ->where('bien_id', $this->historialBienId)
                ->orderByDesc('fecha_asignacion')
                ->get();
        }

        return view('inventario::livewire.responsables.responsables-index', [
            'bienes'   => $bienes,
            'historial' => $historial,
        ]);
    }
}
