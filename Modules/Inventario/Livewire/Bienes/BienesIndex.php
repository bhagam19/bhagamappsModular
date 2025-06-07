<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

use Modules\Users\Models\User;
use Modules\Inventario\Entities\{
    Bien, Categoria, Dependencia, Almacenamiento, Estado, Mantenimiento,BienAprobacionPendiente
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

    private array $ordenBase = [
        'nombre', 'cantidad', 'detalle',
        'categoria_id', 'dependencia_id', 'usuario_id',
        'origen', 'fechaAdquisicion', 'precio',
        'estado_id', 'mantenimiento_id', 'almacenamiento_id',
        'observaciones'
    ];

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
        'cantidad', 'detalle', 
        'dependencia_id', 'usuario_id',
        'categoria_id',
        'origen', 'fechaAdquisicion', 'precio',
        'estado_id', 'mantenimiento_id', 'almacenamiento_id',
        'observaciones'
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
        abort_unless(auth()->user()->hasPermission('ver-bienes'), 403);

        if ($this->hayFiltrosActivos()) {
            $this->actualizarOpcionesFiltros();
        } else {
            $this->cargarCatalogos();
        }
    }

    // ------------------ Métodos auxiliares ------------------ //

    private function hayFiltrosActivos(): bool
    {
        return $this->filtroUsuario || $this->filtroCategoria || $this->filtroDependencia || $this->filtroEstado || $this->filtroNombre;
    }

    private function cargarCatalogos()
    {
        $user = auth()->user();

        if ($user->hasRole('Administrador') || $user->hasRole('Rector')) {
            $this->usuarios = User::orderBy('nombres')->orderBy('apellidos')->get();
            $this->categorias = Categoria::orderBy('nombre')->get();
            $this->dependencias = Dependencia::orderBy('nombre')->get();
            $this->estados = Estado::all();
            $this->mantenimientos = Mantenimiento::all();
            $this->almacenamientos = Almacenamiento::all();
        } else {
            // Obtener las dependencias que tiene asignado el usuario
            $dependenciasIds = Dependencia::where('usuario_id', $user->id)->pluck('id');

            // Obtener los bienes que pertenecen a esas dependencias
            $bienes = Bien::whereIn('dependencia_id', $dependenciasIds)->get();

            // Usuarios: sólo el usuario actual
            $this->usuarios = User::where('id', $user->id)->orderBy('nombres')->orderBy('apellidos')->get();

            // Categorías, dependencias, estados, mantenimientos y almacenamientos filtrados según bienes encontrados
            $this->categorias = Categoria::whereIn('id', $bienes->pluck('categoria_id')->unique())->orderBy('nombre')->get();
            $this->dependencias = Dependencia::whereIn('id', $dependenciasIds)->orderBy('nombre')->get();
            $this->estados = Estado::whereIn('id', $bienes->pluck('estado_id')->unique())->get();
            $this->mantenimientos = Mantenimiento::whereIn('id', $bienes->pluck('mantenimiento_id')->unique())->get();
            $this->almacenamientos = Almacenamiento::whereIn('id', $bienes->pluck('almacenamiento_id')->unique())->get();
        }
    }


    public function actualizarOpcionesFiltros()
    {
        $query = Bien::query();

        // Filtros directos por campos *_id que sí están en bienes
        foreach (['dependencia', 'categoria', 'estado'] as $campo) {
            $valor = $this->{'filtro' . ucfirst($campo)};
            if ($valor) {
                $query->where("{$campo}_id", $valor);
            }
        }

        // Filtro por usuario: se filtra por usuario_id en la tabla dependencias usando whereHas
        if ($this->filtroUsuario) {
            $query->whereHas('dependencia', function ($q) {
                $q->where('usuario_id', $this->filtroUsuario);
            });
        }

        // Filtro por nombre
        if ($this->filtroNombre) {
            $query->where('nombre', 'like', '%' . $this->filtroNombre . '%');
        }

        $bienes = $query->get();

        // Actualizar listas para filtros dinámicos
        $this->usuarios = User::whereIn('id', 
            Dependencia::whereIn('id', $bienes->pluck('dependencia_id')->unique())
                ->pluck('usuario_id')->unique()
        )->orderBy('nombres')->orderBy('apellidos')->get();

        $this->categorias = Categoria::whereIn('id', $bienes->pluck('categoria_id')->unique())
            ->orderBy('nombre')->get();

        $this->dependencias = Dependencia::whereIn('id', $bienes->pluck('dependencia_id')->unique())
            ->orderBy('nombre')->get();

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

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function toggleColumn($column)
    {
        if (in_array($column, $this->visibleColumns)) {
            $this->visibleColumns = array_values(array_filter(
                $this->visibleColumns,
                fn($col) => $col !== $column
            ));
        } else {
            $this->visibleColumns[] = $column;
        }

        // Solo columnas válidas y en orden base
        $this->visibleColumns = array_values(array_intersect($this->ordenBase, $this->visibleColumns));
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

    public function getCantidadTotalFiltradaProperty()
    {
        $user = auth()->user();

        $query = Bien::query();

        // Visibilidad por rol
        if ($user->hasRole('Administrador') || $user->hasRole('Rector')) {
            // Todos los bienes, sin filtro
        } elseif ($user->hasRole('Coordinador')) {
            if (!$this->verTodos) {
                $query->whereHas('dependencia', function($q) use ($user) {
                    $q->where('usuario_id', $user->id);
                });
            }
        } else {
            $query->whereHas('dependencia', function($q) use ($user) {
                $q->where('usuario_id', $user->id);
            });
        }

        // Filtros aplicados
        $query
            ->when($this->filtroUsuario, function($q) {
                $q->whereHas('dependencia', function($query) {
                    $query->where('usuario_id', $this->filtroUsuario);
                });
            })
            ->when($this->filtroCategoria, fn($q) => $q->where('categoria_id', $this->filtroCategoria))
            ->when($this->filtroDependencia, fn($q) => $q->where('dependencia_id', $this->filtroDependencia))
            ->when($this->filtroEstado, fn($q) => $q->where('estado_id', $this->filtroEstado))
            ->when($this->filtroNombre, fn($q) => $q->where('nombre', 'like', '%' . $this->filtroNombre . '%'));

        return $query->sum('cantidad');
    }



    // ------------------ Render ------------------ //

    protected $listeners = ['bienActualizado' => 'recargarBien']; 

    public function recargarBien()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        $bienesQuery = Bien::with([
            'detalle', 'categoria', 'dependencia.usuario',
            'almacenamiento', 'estado', 'mantenimiento'
        ]);

        if ($user->hasRole('Administrador') || $user->hasRole('Rector')) {
            // Todos los bienes, sin filtro
        } elseif ($user->hasRole('Coordinador')) {
            if (!$this->verTodos) {
                $bienesQuery->whereHas('dependencia', function($query) use ($user) {
                    $query->where('usuario_id', $user->id);
                });
            }
            $bienesQuery->orderBy('dependencia_id')->orderBy('nombre', 'asc');
        } else {
            $bienesQuery->whereHas('dependencia', function($query) use ($user) {
                $query->where('usuario_id', $user->id);
            })
            ->orderBy('dependencia_id')
            ->orderBy('nombre', 'asc');
        }

        // Aplicar filtros
        $bienesQuery
            ->when($this->filtroUsuario, function($q) {
                $q->whereHas('dependencia', function($query) {
                    $query->where('usuario_id', $this->filtroUsuario);
                });
            })
            ->when($this->filtroCategoria, fn($q) => $q->where('categoria_id', $this->filtroCategoria))
            ->when($this->filtroDependencia, fn($q) => $q->where('dependencia_id', $this->filtroDependencia))
            ->when($this->filtroEstado, fn($q) => $q->where('estado_id', $this->filtroEstado))
            ->when($this->filtroNombre, fn($q) => $q->where('nombre', 'like', '%' . $this->filtroNombre . '%'));

        $bienesQuery->orderBy($this->sortField, $this->sortDirection);

        $bienes = $bienesQuery->paginate($this->perPage);

        $cambiosPendientes = BienAprobacionPendiente::all();

        return view('inventario::livewire.bienes.bienes-index', [
            'bienes' => $bienes,
            'cambiosPendientes' => $cambiosPendientes,
        ]);
    }



}
