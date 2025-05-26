<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;
use Livewire\WithPagination;

use Modules\Users\Models\User;

use Modules\Inventario\Entities\Bien;
use Modules\Inventario\Entities\Categoria;
use Modules\Inventario\Entities\Dependencia;
use Modules\Inventario\Entities\Ubicacion;
use Modules\Inventario\Entities\Almacenamiento;
use Modules\Inventario\Entities\Estado;
use Modules\Inventario\Entities\Mantenimiento;

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
    public $filtroUsuario;
    public $filtroCategoria;
    public $filtroDependencia;
    public $filtroEstado;

    // --- Campos del bien ---
    public $nombre, $detalle, $serie, $origen, $fechaAdquisicion, $precio, $cantidad;
    public $categoria_id, $dependencia_id, $usuario_id, $almacenamiento_id, $estado_id, $mantenimiento_id, $observaciones;
    public $categorias;
    public $dependencias;
    public $usuarios;
    public $estados;
    public $almacenamientos;
    public $mantenimientos;

    // --- Query string ---
    protected $queryString = [
        'perPage' => ['except' => 25],
        'filtroUsuario' => ['except' => null],
        'filtroCategoria' => ['except' => null],
        'filtroDependencia' => ['except' => null],
        'filtroEstado' => ['except' => null],
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

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
        'nombre', 'cantidad', 'detalle', 'usuario_id', 
        
        'categoria_id', 
        
        'dependencia_id', 
        'ubicacion_id',
        
        'origen', 'fechaAdquisicion', 'precio', 
        
        'estado_id', 'mantenimiento_id', 'almacenamiento_id', 
        
        'observaciones'
    ];

    public function mount()
    {
        if (!auth()->user()->hasPermission('ver-bienes')) {
            abort(403);
        }

        // Si no hay filtros, carga todos los catálogos
        if (!$this->filtroUsuario && !$this->filtroCategoria && !$this->filtroDependencia && !$this->filtroEstado) {
            $this->usuarios = User::all();
            $this->categorias = Categoria::all();
            $this->dependencias = Dependencia::all();
            $this->estados = Estado::all();
        } else {
            $this->actualizarOpcionesFiltros();
        }
    }

    public function updatingPerPage()
    {
        $this->resetPage(); // Reinicia la página al cambiar la cantidad
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
    }

    public function store()
    {
        if (!auth()->user()->hasPermission('crear-bienes')) {
            session()->flash('error', 'No tienes permiso para crear bienes.');
            return;
        }

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

        Bien::create([
            'nombre' => $this->nombre,
            'detalle' => $this->detalle,
            'serie' => $this->serie,
            'origen' => $this->origen,
            'fechaAdquisicion' => $this->fechaAdquisicion,
            'precio' => $this->precio,
            'cantidad' => $this->cantidad,
            'categoria_id' => $this->categoria_id,
            'dependencia_id' => $this->dependencia_id,
            'usuario_id' => $this->usuario_id,
            'almacenamiento_id' => $this->almacenamiento_id,
            'estado_id' => $this->estado_id,
            'mantenimiento_id' => $this->mantenimiento_id,
            'observaciones' => $this->observaciones,
        ]);

        session()->flash('message', 'Bien creado exitosamente.');
        $this->resetInput();
    }

    public function delete($id)
    {
        if (!auth()->user()->hasPermission('eliminar-bienes')) {
            session()->flash('error', 'No tienes permiso para eliminar bienes.');
            return;
        }

        Bien::findOrFail($id)->delete();
        session()->flash('message', 'Bien eliminado exitosamente.');
    }

    public function resetInput()
    {
        $this->nombre = '';
        $this->detalle = '';
        $this->serie = '';
        $this->origen = '';
        $this->fechaAdquisicion = null;
        $this->precio = null;
        $this->cantidad = null;
        $this->categoria_id = null;
        $this->dependencia_id = null;
        $this->usuario_id = null;
        $this->almacenamiento_id = null;
        $this->estado_id = null;
        $this->mantenimiento_id = null;
        $this->observaciones = '';

        $this->verTodos = false;
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
    }

    public function updatedFiltroDependencia($value)
    {
        $this->actualizarOpcionesFiltros();
    }

    public function updatedFiltroCategoria($value)
    {
        $this->actualizarOpcionesFiltros();
    }

    public function updatedFiltroEstado($value)
    {
        $this->actualizarOpcionesFiltros();
    }

    public function updatedFiltroUsuario($value)
    {
        $this->actualizarOpcionesFiltros();
    }

    public function actualizarOpcionesFiltros()
    {
        $query = Bien::query();

        if ($this->filtroDependencia) {
            $query->where('dependencia_id', $this->filtroDependencia);
        }

        if ($this->filtroCategoria) {
            $query->where('categoria_id', $this->filtroCategoria);
        }

        if ($this->filtroEstado) {
            $query->where('estado_id', $this->filtroEstado);
        }

        if ($this->filtroUsuario) {
            $query->where('usuario_id', $this->filtroUsuario);
        }

        $bienesFiltrados = $query->get();

        // Extrae valores únicos para los filtros relacionados
        $this->usuarios = User::whereIn('id', $bienesFiltrados->pluck('usuario_id')->unique())->get();
        $this->categorias = Categoria::whereIn('id', $bienesFiltrados->pluck('categoria_id')->unique())->get();
        $this->dependencias = Dependencia::whereIn('id', $bienesFiltrados->pluck('dependencia_id')->unique())->get();
        $this->estados = Estado::whereIn('id', $bienesFiltrados->pluck('estado_id')->unique())->get();

        if ($this->filtroUsuario && !$this->usuarios->pluck('id')->contains($this->filtroUsuario)) {
            $this->filtroUsuario = null;
        }

        if ($this->filtroCategoria && !$this->categorias->pluck('id')->contains($this->filtroCategoria)) {
            $this->filtroCategoria = null;
        }

        if ($this->filtroDependencia && !$this->dependencias->pluck('id')->contains($this->filtroDependencia)) {
            $this->filtroDependencia = null;
        }

        if ($this->filtroEstado && !$this->estados->pluck('id')->contains($this->filtroEstado)) {
            $this->filtroEstado = null;
        }

    }

    public function render()
    {
        $user = auth()->user();

        $bienesQuery = Bien::with([
            'categoria',
            'dependencia',
            'usuario',
            'almacenamiento',
            'estado',
            'mantenimiento'
        ]);

        // Reglas de visibilidad
        if ($user->hasRole('Administrador') || $user->hasRole('Rector')) {
            // No se aplica ningún filtro, ve todos los bienes
        } elseif ($user->hasRole('Coordinador')) {
            if (!$this->verTodos) {
                $bienesQuery->where('usuario_id', $user->id);
            }
        } else {
            // Cualquier otro usuario ve solo sus bienes
            $bienesQuery->where('usuario_id', $user->id);
        }

        // Filtros combinados
        $bienesQuery
            ->when($this->filtroUsuario, fn($q) => $q->where('usuario_id', $this->filtroUsuario))
            ->when($this->filtroCategoria, fn($q) => $q->where('categoria_id', $this->filtroCategoria))
            ->when($this->filtroDependencia, fn($q) => $q->where('dependencia_id', $this->filtroDependencia))
            ->when($this->filtroEstado, fn($q) => $q->where('estado_id', $this->filtroEstado));

        $bienesQuery->orderBy($this->sortField, $this->sortDirection);

        $bienes = $bienesQuery->paginate($this->perPage);

        // Catálogos
        $almacenamientos = Almacenamiento::all();
        $estados = Estado::all();
        $mantenimientos = Mantenimiento::all();

        $categorias = $this->categorias;
        $dependencias = $this->dependencias;
        $usuarios = $this->usuarios;

        return view('inventario::livewire.bienes.bienes-index', compact(
            'bienes',
            'categorias',
            'dependencias',
            'usuarios',
            'almacenamientos',
            'estados',
            'mantenimientos'
        ))->layout('layouts.app');
    }


}
 