<?php

namespace Modules\ActivityLog\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\ActivityLog\Entities\ActivityLog;
use Modules\User\Entities\User;

class ActivityLogIndex extends Component
{
    use WithPagination;

    // Filtros (LOG-010)
    public string $filtroUsuario = '';
    public string $filtroModulo  = '';
    public string $filtroAccion  = '';
    public string $filtroDesde   = '';
    public string $filtroHasta   = '';
    public int    $perPage       = 25;

    // Dashboard rápido (LOG-012)
    public int   $accionesHoy    = 0;
    public int   $accionesSemana = 0;
    public array $ultimosEventos = [];

    // Catálogos para filtros dinámicos
    public array $modulosDisponibles = [];
    public array $accionesDisponibles = [];

    public function mount(): void
    {
        abort_unless(
            auth()->user()->hasPermission('ver-activity-log') && auth()->user()->isAdminPrincipal(),
            403,
            'Esta sección está reservada para el Administrador Principal.'
        );

        $this->cargarDashboard();
        $this->cargarCatalogos();
    }

    private function cargarDashboard(): void
    {
        $this->accionesHoy    = ActivityLog::whereDate('created_at', today())->count();
        $this->accionesSemana = ActivityLog::where('created_at', '>=', now()->startOfWeek())->count();
        $this->ultimosEventos = ActivityLog::with('user')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->toArray();
    }

    private function cargarCatalogos(): void
    {
        $this->modulosDisponibles  = ActivityLog::distinct()->orderBy('modulo')->pluck('modulo')->toArray();
        $this->accionesDisponibles = ActivityLog::distinct()->orderBy('accion')->pluck('accion')->toArray();
    }

    public function updatingFiltroUsuario(): void { $this->resetPage(); }
    public function updatingFiltroModulo(): void  { $this->resetPage(); }
    public function updatingFiltroAccion(): void  { $this->resetPage(); }
    public function updatingFiltroDesde(): void   { $this->resetPage(); }
    public function updatingFiltroHasta(): void   { $this->resetPage(); }

    public function limpiarFiltros(): void
    {
        $this->filtroUsuario = '';
        $this->filtroModulo  = '';
        $this->filtroAccion  = '';
        $this->filtroDesde   = '';
        $this->filtroHasta   = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = ActivityLog::with('user')->latest('created_at');

        // Filtro por usuario: nombre, apellido o email
        if ($this->filtroUsuario !== '') {
            $busqueda = $this->filtroUsuario;
            $query->where(function ($q) use ($busqueda) {
                $q->whereHas('user', function ($u) use ($busqueda) {
                    $u->where('nombres', 'like', "%{$busqueda}%")
                      ->orWhere('apellidos', 'like', "%{$busqueda}%")
                      ->orWhere('email', 'like', "%{$busqueda}%");
                });
            });
        }

        if ($this->filtroModulo !== '') {
            $query->where('modulo', $this->filtroModulo);
        }

        if ($this->filtroAccion !== '') {
            $query->where('accion', $this->filtroAccion);
        }

        if ($this->filtroDesde !== '') {
            $query->whereDate('created_at', '>=', $this->filtroDesde);
        }

        if ($this->filtroHasta !== '') {
            $query->whereDate('created_at', '<=', $this->filtroHasta);
        }

        $registros = $query->paginate($this->perPage);

        return view('activitylog::livewire.activity-log-index', [
            'registros' => $registros,
        ]);
    }
}
