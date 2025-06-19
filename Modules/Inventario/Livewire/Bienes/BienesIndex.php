<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

use Modules\Users\Models\User;
use Modules\Inventario\Entities\{
    Bien,
    Categoria,
    Dependencia,
    Almacenamiento,
    Estado,
    Mantenimiento,
    BienAprobacionPendiente
};

class BienesIndex extends Component
{
    use WithPagination;

    public $bienesQuery;

    // --- Estado de vista ---
    public bool $verTodos = false;

    // --- Paginación y orden ---
    public int $perPage = 25;
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    // --- Filtros ---
    public $listaNombresBienes = [];
    public $filtroNombre, $filtroUsuario, $filtroCategoria, $filtroDependencia, $filtroEstado;

    // --- Campos del bien ---
    public $nombre, $detalle, $serie, $origen, $fecha_adquisicion, $precio, $cantidad;
    public $categoria_id, $dependencia_id, $usuario_id, $almacenamiento_id, $estado_id, $mantenimiento_id, $observaciones;

    // --- Catálogos cargados ---
    public $categorias, $dependencias, $usuarios, $estados, $almacenamientos, $mantenimientos;

    // --- Columnas de tabla ---
    public $availableColumns = [
        'nombre' => 'Nombre del Bien',
        'cantidad' => 'Cantidad',
        'detalle' => 'Detalle',
        'dependencia_id' => 'Dependencia',
        'usuario_id' => 'Usuario',
        'categoria_id' => 'Categoría',
        'serie' => 'Serie',
        'ubicacion_id' => 'Ubicación',
        'origen' => 'Origen',
        'fecha_adquisicion' => 'Fecha de Adquisición',
        'precio' => 'Precio',
        'estado_id' => 'Estado',
        'mantenimiento_id' => 'Mantenimiento',
        'almacenamiento_id' => 'Almacenamiento',
        'observaciones' => 'Observaciones',
        'created_at' => 'Fecha de Creación',
        'updated_at' => 'Fecha de Actualización',
    ];

    public $visibleColumns = [];

    private array $ordenBase = [
        'nombre',
        'cantidad',
        'detalle',
        'categoria_id',
        'dependencia_id',
        'usuario_id',
        'origen',
        'fecha_adquisicion',
        'precio',
        'estado_id',
        'mantenimiento_id',
        'almacenamiento_id',
        'observaciones'
    ];

    protected $listeners = [
        'bienActualizado' => 'recargarBien',
    ];

    // --- Query string para filtros ---
    protected $queryString = [
        'perPage' => ['except' => 25],
        'filtroDependencia' => ['except' => null],
        'filtroUsuario' => ['except' => null],
        'filtroCategoria' => ['except' => null],
        'filtroEstado' => ['except' => null],
        'filtroNombre' => ['except' => null],
    ];

    // ------------------ Ciclo de vida ------------------ //

    public function mount()
    {
        setlocale(LC_COLLATE, 'es_CO.UTF-8');

        abort_unless(auth()->user()->hasPermission('ver-bienes'), 403);

        $this->visibleColumns = $this->ordenBase;
        $this->cargarCatalogos();
        $this->listaNombresBienes = Bien::pluck('nombre')
            ->unique()
            ->sort(fn($a, $b) => strnatcasecmp($this->normalizarTexto($a), $this->normalizarTexto($b)))
            ->values()
            ->toArray();
    }

    private function normalizarTexto($string)
    {
        $normalizeChars = [
            'á' => 'a',
            'Á' => 'A',
            'é' => 'e',
            'É' => 'E',
            'í' => 'i',
            'Í' => 'I',
            'ó' => 'o',
            'Ó' => 'O',
            'ú' => 'u',
            'Ú' => 'U',
            'ñ' => 'n',
            'Ñ' => 'N'
        ];
        return strtr($string, $normalizeChars);
    }

    // ------------------ CRUD ------------------ //

    public function store()
    {
        abort_unless(auth()->user()->can('crear-bienes'), 403);

        $this->validate([
            'nombre' => 'required|string|max:100',
            'detalle' => 'nullable|string|max:400',
            'serie' => 'nullable|string|max:40',
            'origen' => 'nullable|string|max:40',
            'fecha_adquisicion' => 'nullable|date',
            'precio' => 'nullable|numeric',
            'cantidad' => 'nullable|integer',
            'categoria_id' => 'nullable|exists:categorias,id',
            'dependencia_id' => 'nullable|exists:dependencias,id',
            'usuario_id' => 'nullable|exists:users,id',
            'almacenamiento_id' => 'nullable|exists:almacenamientos,id',
            'estado_id' => 'nullable|exists:estados,id',
            'mantenimiento_id' => 'nullable|exists:mantenimientos,id',
            'observaciones' => 'nullable|string|max:255',
        ]);

        Bien::create($this->only([
            'nombre',
            'detalle',
            'serie',
            'origen',
            'fecha_adquisicion',
            'precio',
            'cantidad',
            'categoria_id',
            'dependencia_id',
            'usuario_id',
            'almacenamiento_id',
            'estado_id',
            'mantenimiento_id',
            'observaciones'
        ]));

        session()->flash('message', 'Bien creado exitosamente.');
        $this->resetInput();
    }

    public function duplicar($id)
    {
        $bien = Bien::with('detalle')->findOrFail($id);

        $nuevoBien = $bien->replicate();
        $nuevoBien->save();

        if ($bien->detalle) {
            $nuevoDetalle = $bien->detalle->replicate();
            $nuevoDetalle->bien_id = $nuevoBien->id;
            $nuevoDetalle->save();
        }

        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: "Bien duplicado correctamente. ID: {$nuevoBien->id}");

        $this->resetPage();
    }


    public function delete($id)
    {
        abort_unless(auth()->user()->can('eliminar-bienes'), 403);

        Bien::findOrFail($id)->delete();
        session()->flash('message', 'Bien eliminado exitosamente.');
    }

    public function resetInput()
    {
        foreach (
            [
                'nombre',
                'detalle',
                'serie',
                'origen',
                'fecha_adquisicion',
                'precio',
                'cantidad',
                'categoria_id',
                'dependencia_id',
                'usuario_id',
                'almacenamiento_id',
                'estado_id',
                'mantenimiento_id',
                'observaciones'
            ] as $campo
        ) {
            $this->$campo = null;
        }

        $this->verTodos = false;
    }

    // ------------------ Métodos auxiliares ------------------ //

    private function cargarCatalogos()
    {
        $user = auth()->user();

        $dependenciasIds = $user->hasRole('Administrador') || $user->hasRole('Rector')
            ? Dependencia::pluck('id')
            : Dependencia::where('usuario_id', $user->id)->pluck('id');

        $bienes = Bien::whereIn('dependencia_id', $dependenciasIds)->get();

        $this->usuarios = User::whereIn(
            'id',
            Dependencia::whereIn('id', $bienes->pluck('dependencia_id')->unique())
                ->pluck('usuario_id')->unique()
        )->orderBy('nombres')->orderBy('apellidos')->get();

        $this->categorias = Categoria::whereIn('id', $bienes->pluck('categoria_id')->unique())->orderBy('nombre')->get();
        $this->dependencias = Dependencia::whereIn('id', $bienes->pluck('dependencia_id')->unique())->orderBy('nombre')->get();
        $this->estados = Estado::whereIn('id', $bienes->pluck('estado_id')->unique())->get();
        $this->mantenimientos = Mantenimiento::whereIn('id', $bienes->pluck('mantenimiento_id')->unique())->get();
        $this->almacenamientos = Almacenamiento::whereIn('id', $bienes->pluck('almacenamiento_id')->unique())->get();
    }

    private function filtrarBienesQuery()
    {
        $user = auth()->user();

        $query = Bien::with([
            'detalle',
            'categoria',
            'dependencia.usuario',
            'almacenamiento',
            'estado',
            'mantenimiento',
            'aprobacionesPendientes'
        ]);

        if ($user->hasRole('Coordinador') && !$this->verTodos) {
            $query->whereHas('dependencia', fn($q) => $q->where('usuario_id', $user->id));
        } elseif (!$user->hasRole('Administrador') && !$user->hasRole('Rector')) {
            $query->whereHas('dependencia', fn($q) => $q->where('usuario_id', $user->id));
        }

        return $query
            ->when($this->filtroUsuario, fn($q) => $q->whereHas('dependencia', fn($sub) => $sub->where('usuario_id', $this->filtroUsuario)))
            ->when($this->filtroCategoria, fn($q) => $q->where('categoria_id', $this->filtroCategoria))
            ->when($this->filtroDependencia, fn($q) => $q->where('dependencia_id', $this->filtroDependencia))
            ->when($this->filtroEstado, fn($q) => $q->where('estado_id', $this->filtroEstado))
            ->when($this->filtroNombre, fn($q) => $q->where('nombre', $this->filtroNombre))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function actualizarOpcionesFiltros()
    {
        $bienes = $this->filtrarBienesQuery()->get();

        $this->usuarios = User::whereIn(
            'id',
            Dependencia::whereIn('id', $bienes->pluck('dependencia_id')->unique())
                ->pluck('usuario_id')->unique()
        )->orderBy('nombres')->orderBy('apellidos')->get();

        $this->categorias = Categoria::whereIn('id', $bienes->pluck('categoria_id')->unique())->orderBy('nombre')->get();
        $this->dependencias = Dependencia::whereIn('id', $bienes->pluck('dependencia_id')->unique())->orderBy('nombre')->get();
        $this->estados = Estado::whereIn('id', $bienes->pluck('estado_id')->unique())->get();

        foreach (['usuario', 'categoria', 'dependencia', 'estado'] as $campo) {
            $filtro = "filtro" . ucfirst($campo);
            if ($this->$filtro && !$this->{Str::plural($campo)}->pluck('id')->contains($this->$filtro)) {
                $this->$filtro = null;
            }
        }
    }

    public function getCantidadTotalFiltradaProperty()
    {
        return $this->filtrarBienesQuery()->sum('cantidad');
    }

    // ------------------ Interacción con tabla ------------------ //

    public function toggleColumn($column)
    {
        if (in_array($column, $this->visibleColumns)) {
            $this->visibleColumns = array_values(array_filter($this->visibleColumns, fn($col) => $col !== $column));
        } else {
            $this->visibleColumns[] = $column;
        }

        $this->visibleColumns = array_values(array_intersect($this->ordenBase, $this->visibleColumns));
    }

    public function sortBy($field)
    {
        $this->resetPage();
        $this->gotoPage(1);
        $this->sortDirection = $this->sortField === $field
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc')
            : 'asc';
        $this->sortField = $field;
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset([
            'filtroUsuario',
            'filtroCategoria',
            'filtroDependencia',
            'filtroEstado',
            'filtroNombre'
        ]);
        $this->resetPage();
        $this->cargarCatalogos();
    }

    // ------------------ Reactividad de filtros ------------------ //

    public function updatedFiltroUsuario()
    {
        $this->resetPage();
        $this->actualizarOpcionesFiltros();
    }
    public function updatedFiltroCategoria()
    {
        $this->resetPage();
        $this->actualizarOpcionesFiltros();
    }
    public function updatedFiltroDependencia()
    {
        $this->resetPage();
        $this->actualizarOpcionesFiltros();
    }
    public function updatedFiltroEstado()
    {
        $this->resetPage();
        $this->actualizarOpcionesFiltros();
    }
    public function updatedFiltroNombre()
    {
        $this->resetPage();
    }
    public function buscar()
    {
        $this->resetPage();
    }
    public $forceRender = 0;
    public function recargarBien()
    {
        $this->forceRender++;
        $this->resetPage();
    }

    // ------------------ Render ------------------ //

    public function render()
    {
        $bienes = $this->filtrarBienesQuery()->paginate($this->perPage);

        $camposPendientes = $bienes->mapWithKeys(fn($bien) => [
            $bien->id => $bien->camposPendientes()
        ]);

        return view('inventario::livewire.bienes.bienes-index', [
            'bienes' => $bienes,
            'camposPendientes' => $camposPendientes,
        ]);
    }
}
