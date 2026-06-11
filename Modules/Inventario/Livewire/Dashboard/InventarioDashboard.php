<?php

namespace Modules\Inventario\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

use Modules\Inventario\Entities\{
    Bien,
    Categoria,
    Dependencia,
    MantenimientoProgramado,
    HistorialModificacionBien,
    HistorialUbicacionBien,
    HistorialEliminacionBien,
    BienResponsable,
};

class InventarioDashboard extends Component
{
    // DASH-001: KPIs
    public int $totalBienes           = 0;
    public int $totalDependencias     = 0;
    public int $totalResponsables     = 0;
    public int $totalCategorias       = 0;
    public int $totalBienesActivos    = 0;
    public int $totalBajas            = 0;
    public int $totalMantPendientes   = 0;
    public int $totalMantRealizados   = 0;

    // DASH-002/003/004/005: Datos para gráficas
    public array $chartCategorias   = [];
    public array $chartDependencias = [];
    public array $chartEstados      = [];
    public array $chartOrigenes     = [];
    public bool  $origenesNormalizados = false;

    // DASH-007: Alertas
    public int $alertMantVencidos          = 0;
    public int $alertSinResponsable        = 0;
    public int $alertSinUbicacion          = 0;
    public int $alertInfoIncompleta        = 0;
    public int $alertSolicitudesPendientes = 0;

    // DASH-009: Calidad de datos
    public int $pctConResponsable = 0;
    public int $pctConUbicacion   = 0;
    public int $pctConCategoria   = 0;
    public int $pctConEstado      = 0;

    public function mount(): void
    {
        $this->cargarKpis();
        $this->cargarGraficas();
        $this->cargarAlertas();
        $this->cargarCalidadDatos();
    }

    private function cargarKpis(): void
    {
        $this->totalBienes         = Bien::count();
        $this->totalDependencias   = Dependencia::count();
        $this->totalResponsables   = Dependencia::whereNotNull('user_id')
            ->distinct('user_id')->count('user_id');
        $this->totalCategorias     = Categoria::count();
        $this->totalBienesActivos  = $this->totalBienes;
        $this->totalBajas          = Bien::onlyTrashed()->count();
        $this->totalMantPendientes = MantenimientoProgramado::where('estado', 'pendiente')->count();
        $this->totalMantRealizados = MantenimientoProgramado::where('estado', 'realizado')->count();
    }

    private function cargarGraficas(): void
    {
        // DASH-002: Bienes por categoría
        $this->chartCategorias = DB::table('bienes')
            ->leftJoin('categorias', 'bienes.categoria_id', '=', 'categorias.id')
            ->selectRaw('COALESCE(categorias.nombre, "Sin categoría") as nombre, COUNT(*) as total')
            ->whereNull('bienes.deleted_at')
            ->groupBy('categorias.id', 'categorias.nombre')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => ['nombre' => $r->nombre, 'total' => (int) $r->total])
            ->toArray();

        // DASH-003: Bienes por dependencia (top 10)
        $this->chartDependencias = DB::table('bienes')
            ->leftJoin('dependencias', 'bienes.dependencia_id', '=', 'dependencias.id')
            ->selectRaw('COALESCE(dependencias.nombre, "Sin dependencia") as nombre, COUNT(*) as total')
            ->whereNull('bienes.deleted_at')
            ->groupBy('dependencias.id', 'dependencias.nombre')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($r) => ['nombre' => $r->nombre, 'total' => (int) $r->total])
            ->toArray();

        // DASH-004: Estado del inventario
        $this->chartEstados = DB::table('bienes')
            ->leftJoin('estados', 'bienes.estado_id', '=', 'estados.id')
            ->selectRaw('COALESCE(estados.nombre, "Sin estado") as nombre, COUNT(*) as total')
            ->whereNull('bienes.deleted_at')
            ->groupBy('estados.id', 'estados.nombre')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => ['nombre' => $r->nombre, 'total' => (int) $r->total])
            ->toArray();

        // DASH-005: Origen de bienes
        // GROUP BY sobre la columna cruda (válido en ONLY_FULL_GROUP_BY);
        // la normalización de NULL/"" a "Sin origen" se hace en PHP.
        $rawOrigenes = DB::table('bienes')
            ->selectRaw('origen, COUNT(*) as total')
            ->whereNull('deleted_at')
            ->groupBy('origen')
            ->orderByDesc('total')
            ->get();

        $origenes = $rawOrigenes
            ->groupBy(fn($r) => (is_null($r->origen) || $r->origen === '') ? 'Sin origen' : $r->origen)
            ->map(fn($group, $nombre) => (object) ['nombre' => $nombre, 'total' => $group->sum('total')])
            ->sortByDesc('total')
            ->values();

        $this->chartOrigenes = $origenes
            ->map(fn($r) => ['nombre' => $r->nombre, 'total' => (int) $r->total])
            ->toArray();

        $this->origenesNormalizados = $origenes
            ->filter(fn($o) => $o->nombre !== 'Sin origen')
            ->count() >= 2;
    }

    private function cargarAlertas(): void
    {
        $this->alertMantVencidos = MantenimientoProgramado::where('estado', 'pendiente')
            ->where('fecha_programada', '<', now()->toDateString())
            ->count();

        $this->alertSinResponsable = DB::table('bienes')
            ->whereNull('deleted_at')
            ->whereNotIn('id', function ($q) {
                $q->select('bien_id')->from('bienes_responsables')->whereNull('fecha_retiro');
            })
            ->count();

        $this->alertSinUbicacion = DB::table('bienes')
            ->whereNull('deleted_at')
            ->whereNotIn('id', function ($q) {
                $q->select('bien_id')->from('historial_ubicaciones_bienes');
            })
            ->count();

        $this->alertInfoIncompleta = DB::table('bienes')
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('categoria_id')
                    ->orWhereNull('dependencia_id')
                    ->orWhereNull('estado_id');
            })
            ->count();

        $modPendientes  = HistorialModificacionBien::where('estado', 'pendiente')->count();
        $elimPendientes = HistorialEliminacionBien::where('estado', 'pendiente')->count();
        $this->alertSolicitudesPendientes = $modPendientes + $elimPendientes;
    }

    private function cargarCalidadDatos(): void
    {
        if ($this->totalBienes === 0) {
            return;
        }

        $conResponsable = DB::table('bienes')
            ->whereNull('deleted_at')
            ->whereIn('id', function ($q) {
                $q->select('bien_id')->from('bienes_responsables')->whereNull('fecha_retiro');
            })
            ->count();

        $conUbicacion = DB::table('bienes')
            ->whereNull('deleted_at')
            ->whereIn('id', function ($q) {
                $q->select('bien_id')->from('historial_ubicaciones_bienes');
            })
            ->count();

        $conCategoria = Bien::whereNotNull('categoria_id')->count();
        $conEstado    = Bien::whereNotNull('estado_id')->count();

        $this->pctConResponsable = (int) round($conResponsable / $this->totalBienes * 100);
        $this->pctConUbicacion   = (int) round($conUbicacion / $this->totalBienes * 100);
        $this->pctConCategoria   = (int) round($conCategoria / $this->totalBienes * 100);
        $this->pctConEstado      = (int) round($conEstado / $this->totalBienes * 100);
    }

    public function render(): View
    {
        $ultimasModificaciones = HistorialModificacionBien::with(['bien:id,nombre'])
            ->latest()
            ->limit(10)
            ->get(['id', 'bien_id', 'campo', 'estado', 'created_at']);

        $ultimasUbicaciones = HistorialUbicacionBien::with([
            'bien:id,nombre',
            'ubicacionDestino:id,nombre',
        ])
            ->latest('fecha_movimiento')
            ->limit(10)
            ->get(['id', 'bien_id', 'ubicacion_destino_id', 'fecha_movimiento']);

        $ultimasEliminaciones = HistorialEliminacionBien::with(['bien:id,nombre'])
            ->where('estado', 'aprobado')
            ->latest()
            ->limit(10)
            ->get(['id', 'bien_id', 'created_at']);

        return view('inventario::livewire.dashboard.inventario-dashboard', compact(
            'ultimasModificaciones',
            'ultimasUbicaciones',
            'ultimasEliminaciones',
        ));
    }
}
