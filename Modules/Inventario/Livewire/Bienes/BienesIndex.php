<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

use Modules\Users\Models\User;
use Modules\Inventario\Entities\{
    Bien, Categoria, Dependencia, Ubicacion, Almacenamiento, Estado, Mantenimiento
};

class BienesIndex extends Component
{
    use WithPagination;

    // --- Estado de vista ---
    public bool $verTodos = false;

    // --- Paginación y orden ---
    public int $perPage = 25;
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    // --- Filtros ---
    public $filtroNombre, $filtroUsuario, $filtroCategoria, $filtroDependencia, $filtroEstado;

    // --- Campos del bien ---
    public $nombre, $detalle, $serie, $origen, $fechaAdquisicion, $precio, $cantidad;
    public $categoria_id, $dependencia_id, $usuario_id, $almacenamiento_id, $estado_id, $mantenimiento_id, $observaciones;

    // --- Catálogos cargados ---
    public $categorias, $dependencias, $usuarios, $estados, $almacenamientos, $mantenimientos;

    // --- Columnas de tabla ---
    public $availableColumns = [
        
        'nombre' => 'Nombre del Bien',
        'cantidad' => 'Cantidad',
        'detalle' => 'Detalle',
        'usuario_id' => 'Usuario',
        
        'categoria_id' => 'Categoría',
        'serie' => 'Serie',

        'dependencia_id' => 'Dependencia',
        'ubicacion_id' => 'Ubicación',        

        'origen' => 'Origen',
        'fechaAdquisicion' => 'Fecha de Adquisición',
        'precio' => 'Precio',

        'estado_id' => 'Estado',
        'mantenimiento_id' => 'Mantenimiento',
        'almacenamiento_id' => 'Almacenamiento',

        'observaciones' => 'Observaciones',        

        'created_at' => 'Fecha de Creación',
        'updated_at' => 'Fecha de Actualización',
    ];

    public $visibleColumns = [
        'nombre', 'cantidad', 'detalle', 
        
        'categoria_id',        
        
        'dependencia_id', 'usuario_id', 
        
        'origen', 'fechaAdquisicion', 'precio', 
        
        'estado_id', 'mantenimiento_id', 'almacenamiento_id', 
        
        'observaciones'
    ];

    // --- Query string para filtros ---
    protected $queryString = [
        'perPage' => ['except' => 25],
        'filtroUsuario' => ['except' => null],
        'filtroCategoria' => ['except' => null],
        'filtroDependencia' => ['except' => null],
        'filtroEstado' => ['except' => null],
    ];

    // ------------------ Ciclo de vida ------------------ //

    public function mount()
    {
        abort_unless(auth()->user()->hasPermission('ver-bienes'), 403);

        $this->hayFiltrosActivos() ? $this->actualizarOpcionesFiltros() : $this->cargarCatalogos();
    }

    // ------------------ Métodos auxiliares ------------------ //

    private function hayFiltrosActivos(): bool
    {
        return $this->filtroUsuario || $this->filtroCategoria || $this->filtroDependencia || $this->filtroEstado;
    }

    private function cargarCatalogos()
    {
        $user = auth()->user();

        if ($user->hasRole('Administrador') || $user->hasRole('Rector')) {
            // Todos los valores para administradores y rector
            $this->usuarios = User::orderBy('nombres')->orderBy('apellidos')->get();
            $this->categorias = Categoria::orderBy('nombre')->get();
            $this->dependencias = Dependencia::orderBy('nombre')->get();
            $this->estados = Estado::all();
            $this->mantenimientos = Mantenimiento::all();
            $this->almacenamientos = Almacenamiento::all();
        } else {
            // Solo valores asociados a los bienes del usuario
            $bienes = Bien::where('usuario_id', $user->id)->get();

            $this->usuarios = User::where('id', $user->id)->orderBy('nombres')->orderBy('apellidos')->get();
            $this->categorias = Categoria::whereIn('id', $bienes->pluck('categoria_id')->unique())->orderBy('nombre')->get();
            $this->dependencias = Dependencia::whereIn('id', $bienes->pluck('dependencia_id')->unique())->orderBy('nombre')->get();
            $this->estados = Estado::whereIn('id', $bienes->pluck('estado_id')->unique())->get();
            $this->mantenimientos = Mantenimiento::whereIn('id', $bienes->pluck('mantenimiento_id')->unique())->get();
            $this->almacenamientos = Almacenamiento::whereIn('id', $bienes->pluck('almacenamiento_id')->unique())->get();
        }
    }

    public function actualizarOpcionesFiltros()
    {
        $query = Bien::query();

        foreach (['dependencia', 'categoria', 'estado', 'usuario'] as $campo) {
            $valor = $this->{'filtro' . ucfirst($campo)};
            if ($valor) {
                $query->where("{$campo}_id", $valor);
            }
        }

        $bienes = $query->get();

        // Actualizar catálogos según los bienes filtrados
        $this->usuarios = User::whereIn('id', $bienes->pluck('usuario_id')->unique())->orderBy('nombres')->orderBy('apellidos')->get();
        $this->categorias = Categoria::whereIn('id', $bienes->pluck('categoria_id')->unique())->orderBy('nombre')->get();
        $this->dependencias = Dependencia::whereIn('id', $bienes->pluck('dependencia_id')->unique())->orderBy('nombre')->get();
        $this->estados = Estado::whereIn('id', $bienes->pluck('estado_id')->unique())->get();

        // Validar filtros activos
        foreach (['usuario', 'categoria', 'dependencia', 'estado'] as $campo) {
            $filtro = "filtro" . ucfirst($campo);
            if ($this->$filtro && !$this->{Str::plural($campo)}->pluck('id')->contains($this->$filtro)) {
                $this->$filtro = null;
            }
        }
    }

    // ------------------ Interacción con tabla ------------------ //

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc')
            : 'asc';

        $this->sortField = $field;
    }

    public function updatingPerPage() { $this->resetPage(); }

    public function toggleColumn($column)
    {
        $this->visibleColumns = in_array($column, $this->visibleColumns)
            ? array_values(array_filter($this->visibleColumns, fn($col) => $col !== $column))
            : [...$this->visibleColumns, $column];
    }

    public function limpiarFiltros()
    {
        $this->reset([
            'filtroUsuario',
            'filtroCategoria',
            'filtroDependencia',
            'filtroEstado',
        ]);
        $this->resetPage();
        $this->cargarCatalogos();
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
            'fechaAdquisicion' => 'nullable|date',
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
            'nombre', 'detalle', 'serie', 'origen', 'fechaAdquisicion', 'precio',
            'cantidad', 'categoria_id', 'dependencia_id', 'usuario_id',
            'almacenamiento_id', 'estado_id', 'mantenimiento_id', 'observaciones'
        ]));

        session()->flash('message', 'Bien creado exitosamente.');
        $this->resetInput();
    }

    public function delete($id)
    {
        abort_unless(auth()->user()->can('eliminar-bienes'), 403);

        Bien::findOrFail($id)->delete();
        session()->flash('message', 'Bien eliminado exitosamente.');
    }

    public function resetInput()
    {
        foreach ([
            'nombre', 'detalle', 'serie', 'origen', 'fechaAdquisicion',
            'precio', 'cantidad', 'categoria_id', 'dependencia_id', 'usuario_id',
            'almacenamiento_id', 'estado_id', 'mantenimiento_id', 'observaciones'
        ] as $campo) {
            $this->$campo = null;
        }

        $this->verTodos = false;
    }

    // ------------------ Reactividad de filtros ------------------ //

    public function updatedFiltroUsuario()     { $this->actualizarOpcionesFiltros(); }
    public function updatedFiltroCategoria()   { $this->actualizarOpcionesFiltros(); }
    public function updatedFiltroDependencia() { $this->actualizarOpcionesFiltros(); }
    public function updatedFiltroEstado()      { $this->actualizarOpcionesFiltros(); }

    // ------------------ Render ------------------ //

    protected $listeners = ['bienActualizado' => 'render'];
    
    public function recargarBien($bienId)
    {
        $this->resetPage(); // útil si estás usando paginación

    }

    public function render()
    {
        $this->cargarCatalogos();

        $user = auth()->user();

        $bienesQuery = Bien::with([
            'detalle', 'categoria', 'dependencia', 'usuario',
            'almacenamiento', 'estado', 'mantenimiento'
        ]);
        
        // Filtros de visibilidad por rol
        if ($user->hasRole('Administrador') || $user->hasRole('Rector')) {
            // Todos los bienes
        } elseif ($user->hasRole('Coordinador')) {
            if (!$this->verTodos) {
                $bienesQuery->where('usuario_id', $user->id)
                ->orderBy('dependencia_id')
                ->orderBy('nombre', 'asc'); // Orden alfabético por nombre
            }
        } else {
            $bienesQuery->where('usuario_id', $user->id)
            ->orderBy('dependencia_id')
            ->orderBy('nombre', 'asc'); // Orden alfabético por nombre
        }        

        // Aplicar filtros
        $bienesQuery
            ->when($this->filtroUsuario, fn($q) => $q->where('usuario_id', $this->filtroUsuario))
            ->when($this->filtroCategoria, fn($q) => $q->where('categoria_id', $this->filtroCategoria))
            ->when($this->filtroDependencia, fn($q) => $q->where('dependencia_id', $this->filtroDependencia))
            ->when($this->filtroEstado, fn($q) => $q->where('estado_id', $this->filtroEstado))
            ->orderBy($this->sortField, $this->sortDirection);
        
        $bienes = $bienesQuery->paginate($this->perPage);
        
        return view('inventario::livewire.bienes.bienes-index', [
            'bienes' => $bienes,
            'categorias' => $this->categorias,
            'dependencias' => $this->dependencias,
            'usuarios' => $this->usuarios,
            'almacenamientos' => $this->almacenamientos,
            'estados' => $this->estados,
            'mantenimientos' => $this->mantenimientos,
        ])->layout('layouts.app');
        
    }
}

 