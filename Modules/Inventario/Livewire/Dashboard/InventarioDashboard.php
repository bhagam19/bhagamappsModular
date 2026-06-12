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
};

class InventarioDashboard extends Component
{
    // DASH-001: KPIs
    public int $totalBienes              = 0;
    public int $totalDependencias        = 0;
    public int $totalResponsables        = 0;
    public int $totalCategorias          = 0;
    public int $totalBienesActivos       = 0;
    public int $totalBajas               = 0;
    public int $totalMantPendientes      = 0;
    public int $totalMantRealizados      = 0;
    public int $totalBienesEnMant        = 0;

    // DASH-011: Porcentajes KPI
    public int   $pctActivos        = 0;
    public int   $pctBajas          = 0;
    public float $pctMantPendientes = 0.0;

    // DASH-002/003/005: Datos para gráficas
    public array $chartCategorias   = [];
    public array $chartDependencias = [];
    public array $chartOrigenes     = [];

    // DASH-026: Estado ejecutivo del inventario
    public array $estadoEjecutivo   = [];
    // Condición física (Nuevo/Bueno/Regular/Malo)
    public array $chartCondicion    = [];

    // DASH-007: Alertas
    public int $alertMantVencidos          = 0;
    public int $alertSinResponsable        = 0;
    public int $alertSinUbicacion          = 0;
    public int $alertInfoIncompleta        = 0;
    public int $alertSolicitudesPendientes = 0;

    // DASH-009/014: Calidad de datos
    public int $pctConResponsable   = 0;
    public int $pctConUbicacion     = 0;
    public int $pctConCategoria     = 0;
    public int $pctConEstado        = 0;
    public int $pctConOrigen        = 0;
    public int $countConResponsable = 0;
    public int $countConUbicacion   = 0;
    public int $countConCategoria   = 0;
    public int $countConEstado      = 0;
    public int $countConOrigen      = 0;

    // DASH-016/025: Top Responsables con porcentaje
    public array $topResponsables = [];

    // DASH-022/023: Bienes estratégicos
    public array $bienesEstrategicos = [];

    // DASH-028: Grupos institucionales
    public array $gruposInstitucionales = [];

    public function mount(): void
    {
        $this->cargarKpis();
        $this->cargarGraficas();
        $this->cargarAlertas();
        $this->cargarCalidadDatos();
        $this->cargarTopResponsables();
        $this->cargarBienesEstrategicos();
        $this->cargarGruposInstitucionales();
        $this->cargarEstadoEjecutivo();
    }

    private function cargarKpis(): void
    {
        $this->totalBienes         = Bien::count();
        $this->totalDependencias   = Dependencia::count();
        $this->totalResponsables   = DB::table('bienes_responsables')
            ->whereNull('fecha_retiro')
            ->distinct('user_id')
            ->count('user_id');
        $this->totalCategorias     = Categoria::count();
        $this->totalBienesActivos  = $this->totalBienes;
        $this->totalBajas          = Bien::onlyTrashed()->count();
        $this->totalMantPendientes = MantenimientoProgramado::where('estado', 'pendiente')->count();
        $this->totalMantRealizados = MantenimientoProgramado::where('estado', 'realizado')->count();
        $this->totalBienesEnMant   = DB::table('mantenimientos_programados')
            ->where('estado', 'pendiente')
            ->distinct('bien_id')
            ->count('bien_id');

        $totalCiclo              = $this->totalBienesActivos + $this->totalBajas;
        $this->pctActivos        = $totalCiclo > 0 ? (int) round($this->totalBienesActivos / $totalCiclo * 100) : 100;
        $this->pctBajas          = $totalCiclo > 0 ? (int) round($this->totalBajas / $totalCiclo * 100) : 0;
        $this->pctMantPendientes = $this->totalBienes > 0
            ? round($this->totalMantPendientes / $this->totalBienes * 100, 1)
            : 0.0;
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

        // DASH-003/024: Bienes por dependencia (top 10) con responsable
        $this->chartDependencias = DB::table('bienes')
            ->leftJoin('dependencias', 'bienes.dependencia_id', '=', 'dependencias.id')
            ->leftJoin('users', 'dependencias.user_id', '=', 'users.id')
            ->selectRaw("COALESCE(dependencias.nombre, 'Sin dependencia') as nombre, COUNT(*) as total, CONCAT(COALESCE(users.nombres,''), ' ', COALESCE(users.apellidos,'')) as responsable")
            ->whereNull('bienes.deleted_at')
            ->groupBy('dependencias.id', 'dependencias.nombre', 'users.nombres', 'users.apellidos')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'nombre'      => $r->nombre,
                'total'       => (int) $r->total,
                'responsable' => trim($r->responsable) ?: '—',
            ])
            ->toArray();

        // Condición física de bienes (Nuevo/Bueno/Regular/Malo)
        $this->chartCondicion = DB::table('bienes')
            ->leftJoin('estados', 'bienes.estado_id', '=', 'estados.id')
            ->selectRaw('COALESCE(estados.nombre, "Sin estado") as nombre, COUNT(*) as total')
            ->whereNull('bienes.deleted_at')
            ->groupBy('estados.id', 'estados.nombre')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => ['nombre' => $r->nombre, 'total' => (int) $r->total])
            ->toArray();

        // DASH-005: Origen de bienes — normaliza NULL / "" / "-" a "Sin origen"
        $rawOrigenes = DB::table('bienes')
            ->selectRaw('origen, COUNT(*) as total')
            ->whereNull('deleted_at')
            ->groupBy('origen')
            ->orderByDesc('total')
            ->get();

        $this->chartOrigenes = $rawOrigenes
            ->groupBy(fn($r) => (is_null($r->origen) || $r->origen === '' || $r->origen === '-') ? 'Sin origen' : $r->origen)
            ->map(fn($group, $nombre) => (object) ['nombre' => $nombre, 'total' => $group->sum('total')])
            ->sortByDesc('total')
            ->values()
            ->map(fn($r) => ['nombre' => $r->nombre, 'total' => (int) $r->total])
            ->toArray();
    }

    private function cargarAlertas(): void
    {
        $this->alertMantVencidos = MantenimientoProgramado::where('estado', 'pendiente')
            ->where('fecha_programada', '<', now()->toDateString())
            ->count();

        // DASHREL-001/002: sin responsable si carece de dependencia_id y de bienes_responsables activo
        $this->alertSinResponsable = DB::table('bienes')
            ->whereNull('deleted_at')
            ->whereNull('dependencia_id')
            ->whereNotIn('id', function ($q) {
                $q->select('bien_id')->from('bienes_responsables')->whereNull('fecha_retiro');
            })
            ->count();

        // DASHREL-002: sin ubicación si carece de dependencia_id y de historial_ubicaciones_bienes
        $this->alertSinUbicacion = DB::table('bienes')
            ->whereNull('deleted_at')
            ->whereNull('dependencia_id')
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

        // DASHREL-001: con responsable = dependencia asignada OR bienes_responsables activo
        $this->countConResponsable = DB::table('bienes')
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNotNull('dependencia_id')
                  ->orWhereIn('id', function ($sub) {
                      $sub->select('bien_id')->from('bienes_responsables')->whereNull('fecha_retiro');
                  });
            })
            ->count();

        // DASHREL-002: con ubicación = dependencia asignada OR historial_ubicaciones_bienes
        $this->countConUbicacion = DB::table('bienes')
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNotNull('dependencia_id')
                  ->orWhereIn('id', function ($sub) {
                      $sub->select('bien_id')->from('historial_ubicaciones_bienes');
                  });
            })
            ->count();

        $this->countConCategoria = Bien::whereNotNull('categoria_id')->count();
        $this->countConEstado    = Bien::whereNotNull('estado_id')->count();
        $this->countConOrigen    = Bien::whereNotNull('origen')
            ->where('origen', '!=', '')
            ->where('origen', '!=', '-')
            ->count();

        $t = $this->totalBienes;
        $this->pctConResponsable = (int) round($this->countConResponsable / $t * 100);
        $this->pctConUbicacion   = (int) round($this->countConUbicacion / $t * 100);
        $this->pctConCategoria   = (int) round($this->countConCategoria / $t * 100);
        $this->pctConEstado      = (int) round($this->countConEstado / $t * 100);
        $this->pctConOrigen      = (int) round($this->countConOrigen / $t * 100);
    }

    private function cargarTopResponsables(): void
    {
        $total = $this->totalBienes;

        $this->topResponsables = DB::table('bienes_responsables')
            ->join('users', 'bienes_responsables.user_id', '=', 'users.id')
            ->selectRaw("CONCAT(users.nombres, ' ', users.apellidos) as nombre, COUNT(bienes_responsables.bien_id) as total")
            ->whereNull('bienes_responsables.fecha_retiro')
            ->groupBy('bienes_responsables.user_id', 'users.nombres', 'users.apellidos')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'nombre' => $r->nombre,
                'total'  => (int) $r->total,
                'pct'    => $total > 0 ? round((int) $r->total / $total * 100, 1) : 0.0,
            ])
            ->toArray();
    }

    // DASH-022/023: Bienes estratégicos por keyword en nombre
    private function cargarBienesEstrategicos(): void
    {
        if ($this->totalBienes === 0) {
            return;
        }

        $grupos = [
            ['label' => 'Portátiles',    'icon' => 'fa-laptop',        'color' => 'info',      'keywords' => ['portátil', 'laptop', 'notebook']],
            ['label' => 'Video Beam',    'icon' => 'fa-video',         'color' => 'primary',   'keywords' => ['video beam', 'videobeam', 'proyector']],
            ['label' => 'Computadores',  'icon' => 'fa-desktop',       'color' => 'teal',      'keywords' => ['computador']],
            ['label' => 'Cámaras',       'icon' => 'fa-camera',        'color' => 'purple',    'keywords' => ['cámara', 'camara']],
            ['label' => 'Impresoras',    'icon' => 'fa-print',         'color' => 'secondary', 'keywords' => ['impresora', 'multifuncional']],
            ['label' => 'Televisores',   'icon' => 'fa-tv',            'color' => 'dark',      'keywords' => ['televisor', 'television', 'televisión']],
            ['label' => 'Tablets',       'icon' => 'fa-tablet-alt',    'color' => 'cyan',      'keywords' => ['tablet']],
            ['label' => 'Switch / Red',  'icon' => 'fa-network-wired', 'color' => 'orange',    'keywords' => ['switch', 'router']],
            ['label' => 'UPS',           'icon' => 'fa-bolt',          'color' => 'warning',   'keywords' => ['ups']],
            ['label' => 'Servidores',    'icon' => 'fa-server',        'color' => 'maroon',    'keywords' => ['servidor']],
        ];

        $total = $this->totalBienes;

        foreach ($grupos as $grupo) {
            $query = DB::table('bienes')->whereNull('deleted_at');
            $query->where(function ($q) use ($grupo) {
                foreach ($grupo['keywords'] as $kw) {
                    $q->orWhereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($kw) . '%']);
                }
            });
            $count = $query->count();
            $this->bienesEstrategicos[] = [
                'label' => $grupo['label'],
                'icon'  => $grupo['icon'],
                'color' => $grupo['color'],
                'total' => $count,
                'pct'   => $total > 0 ? round($count / $total * 100, 1) : 0.0,
            ];
        }

        usort($this->bienesEstrategicos, fn($a, $b) => $b['total'] <=> $a['total']);
    }

    // DASH-028: Grupos institucionales por categoria_id
    private function cargarGruposInstitucionales(): void
    {
        if ($this->totalBienes === 0) {
            return;
        }

        $total = $this->totalBienes;
        $grupos = [
            ['label' => 'Mobiliario',             'icon' => 'fa-chair',        'color' => 'primary',   'ids' => [1, 20]],
            ['label' => 'Equipos Tecnológicos',   'icon' => 'fa-laptop',       'color' => 'info',      'ids' => [5, 6]],
            ['label' => 'Equipos Audiovisuales',  'icon' => 'fa-film',         'color' => 'warning',   'ids' => [7]],
            ['label' => 'Equipos Administrativos','icon' => 'fa-briefcase',    'color' => 'teal',      'ids' => [9]],
            ['label' => 'Material Didáctico',     'icon' => 'fa-book-open',    'color' => 'success',   'ids' => [3, 12, 13, 19]],
            ['label' => 'Instrumentos Musicales', 'icon' => 'fa-music',        'color' => 'pink',      'ids' => [4]],
            ['label' => 'Herramientas',           'icon' => 'fa-tools',        'color' => 'secondary', 'ids' => [26]],
        ];

        $asignados = 0;
        foreach ($grupos as &$grupo) {
            $count = DB::table('bienes')
                ->whereNull('deleted_at')
                ->whereIn('categoria_id', $grupo['ids'])
                ->count();
            $grupo['total'] = $count;
            $grupo['pct']   = $total > 0 ? round($count / $total * 100, 1) : 0.0;
            $asignados += $count;
        }
        unset($grupo);

        $otros = $total - $asignados;
        $grupos[] = [
            'label' => 'Otros',
            'icon'  => 'fa-archive',
            'color' => 'dark',
            'ids'   => [],
            'total' => max(0, $otros),
            'pct'   => $total > 0 ? round(max(0, $otros) / $total * 100, 1) : 0.0,
        ];

        usort($grupos, fn($a, $b) => $b['total'] <=> $a['total']);
        $this->gruposInstitucionales = $grupos;
    }

    // DASH-026: Tablero ejecutivo de estado del inventario
    private function cargarEstadoEjecutivo(): void
    {
        $total = $this->totalBienes + $this->totalBajas;

        $this->estadoEjecutivo = [
            [
                'label' => 'Bienes Activos',
                'icon'  => 'fa-check-circle',
                'color' => 'success',
                'total' => $this->totalBienes,
                'pct'   => $total > 0 ? round($this->totalBienes / $total * 100, 1) : 100.0,
            ],
            [
                'label' => 'En Mantenimiento',
                'icon'  => 'fa-tools',
                'color' => 'warning',
                'total' => $this->totalBienesEnMant,
                'pct'   => $this->totalBienes > 0 ? round($this->totalBienesEnMant / $this->totalBienes * 100, 1) : 0.0,
            ],
            [
                'label' => 'Dados de Baja',
                'icon'  => 'fa-times-circle',
                'color' => 'danger',
                'total' => $this->totalBajas,
                'pct'   => $total > 0 ? round($this->totalBajas / $total * 100, 1) : 0.0,
            ],
            [
                'label' => 'Solicitudes Pendientes',
                'icon'  => 'fa-clock',
                'color' => 'info',
                'total' => $this->alertSolicitudesPendientes,
                'pct'   => null,
            ],
        ];
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
