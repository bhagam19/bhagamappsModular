<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;
use Illuminate\Support\Facades\Notification;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use Modules\Users\Models\User;
use Modules\Inventario\Entities\{
    Bien,
    Categoria,
    Dependencia,
    Almacenamiento,
    Estado,
    Mantenimiento,
    HistorialEliminacionBien,
    Detalle
};
use Modules\Inventario\Livewire\Heb\NotificacionHeb;

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

    // --- Bienes destacados ---
    public $bienesDestacados = [];

    // --- Filtros ---
    public $nombreSeleccionado = '', $nombreNuevo = '', $listaNombresBienes = [];
    public $origenSeleccionado = '', $origenNuevo = '', $listaOrigenesBienes = [];
    public $filtroNombre, $filtroUser, $filtroCategoria, $filtroDependencia, $filtroEstado;

    // --- Campos del bien ---
    public $nombre, $detalle, $serie, $origen, $fecha_adquisicion, $precio, $cantidad;
    public $categoria_id, $dependencia_id, $user_id, $almacenamiento_id, $estado_id, $mantenimiento_id, $observaciones;

    // --- Campos de detalle del bien ---
    public $detalleBien = [
        'car_especial' => null,
        'marca' => null,
        'color' => null,
        'tamano' => null,
        'material' => null,
        'otra' => null,
    ];

    // --- Catálogos cargados ---
    public $categorias, $dependencias, $users, $estados, $almacenamientos, $mantenimientos;

    // --- Columnas de tabla ---
    public $availableColumns = [
        'nombre' => 'Nombre del Bien',
        'cantidad' => 'Cantidad',
        'detalle' => 'Detalle',
        'dependencia_id' => 'Dependencia',
        'user_id' => 'user',
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
        'id',
        'nombre',
        'cantidad',
        'detalle',
        'categoria_id',
        'dependencia_id',
        'user_id',
        'origen',
        'fecha_adquisicion',
        'precio',
        'estado_id',
        'mantenimiento_id',
        'almacenamiento_id',
        'observaciones'
    ];
    //--- Eliminación con soft delete ---
    public $bienId, $motivo, $nuevoMotivo;
    public $bienSeleccionadoId;
    public $motivoSeleccionado;
    public $motivoNuevo;
    public $motivosBase = [
        'No existe',
        'No está en mi inventario',
        'Duplicado',
        'Error en el registro original',
        'Extraviado',
        'Lo agregué por error',
    ];

    protected $listeners = [
        'bienActualizado' => 'recargarBien',
        'bienCreado' => '$refresh',
    ];

    // --- Query string para filtros ---
    protected $queryString = [
        'perPage' => ['except' => 25],
        'filtroDependencia' => ['except' => null],
        'filtrouser' => ['except' => null],
        'filtroCategoria' => ['except' => null],
        'filtroEstado' => ['except' => null],
        'filtroNombre' => ['except' => null],
    ];

    // ------------------ Ciclo de vida ------------------ //

    public function mount()
    {
        abort_unless(auth()->user()->hasPermission('ver-bienes'), 403);

        $this->cargarBienesDestacados();

        $this->visibleColumns = $this->ordenBase;
        $this->cargarCatalogos();
        $this->listaNombresBienes = Bien::pluck('nombre')
            ->unique()
            ->sort(fn($a, $b) => strnatcasecmp($this->normalizarTexto($a), $this->normalizarTexto($b)))
            ->values()
            ->toArray();
        $this->listaOrigenesBienes = Bien::pluck('origen')
            ->unique()
            ->sort(fn($a, $b) => strnatcasecmp($this->normalizarTexto($a), $this->normalizarTexto($b)))
            ->values()
            ->toArray();
    }

    public function cargarBienesDestacados()
    {
        $this->bienesDestacados = DB::table('bienes')
            ->select('nombre', DB::raw('SUM(cantidad) as cantidad_total'))
            ->groupBy('nombre')
            ->orderByDesc('cantidad_total')
            ->take(5)
            ->get();
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

    public bool $mostrarFormulario = false;

    public function toggleFormulario()
    {
        $this->mostrarFormulario = !$this->mostrarFormulario;
    }

    public function store()
    {
        abort_unless(auth()->user()->hasPermission('crear-bienes'), 403);

        // Validación del bien
        $this->validate([
            'nombreSeleccionado' => 'required|string|max:100',
            'nombreNuevo' => 'nullable|string|max:100',
            'detalle' => 'nullable|string|max:400',
            'serie' => 'nullable|string|max:40',
            'origenSeleccionado' => 'required|string|max:100',
            'origenNuevo' => 'nullable|string|max:100',
            'fecha_adquisicion' => 'nullable|date',
            'precio' => 'nullable|numeric',
            'cantidad' => 'nullable|integer',
            'categoria_id' => 'nullable|exists:categorias,id',
            'dependencia_id' => 'nullable|exists:dependencias,id',
            'almacenamiento_id' => 'nullable|exists:almacenamientos,id',
            'estado_id' => 'nullable|exists:estados,id',
            'mantenimiento_id' => 'nullable|exists:mantenimientos,id',
            'observaciones' => 'nullable|string|max:255',

            // Validación de los detalles
            'detalleBien.car_especial' => 'nullable|string|max:255',
            'detalleBien.marca' => 'nullable|string|max:100',
            'detalleBien.color' => 'nullable|string|max:50',
            'detalleBien.tamano' => 'nullable|string|max:50',
            'detalleBien.material' => 'nullable|string|max:100',
            'detalleBien.otra' => 'nullable|string|max:255',
        ]);

        // Obtener el nombre final
        $nombreFinal = $this->nombreSeleccionado === 'otro'
            ? trim($this->nombreNuevo)
            : $this->nombreSeleccionado;

        if (empty($nombreFinal)) {
            $this->addError('nombreSeleccionado', 'Debe seleccionar o ingresar un nombre.');
            return;
        }

        // Obtener el origen final
        $origenFinal = $this->origenSeleccionado === 'otro'
            ? trim($this->origenNuevo)
            : $this->origenSeleccionado;

        if (empty($origenFinal)) {
            $this->addError('origenSeleccionado', 'Debe seleccionar o ingresar un origen.');
            return;
        }

        // Crear el bien
        $bien = Bien::create([
            'nombre' => $nombreFinal,
            'serie' => $this->serie,
            'origen' => $this->origen,
            'fecha_adquisicion' => $this->fecha_adquisicion,
            'precio' => $this->precio,
            'cantidad' => $this->cantidad,
            'categoria_id' => $this->categoria_id,
            'dependencia_id' => $this->dependencia_id,
            'almacenamiento_id' => $this->almacenamiento_id,
            'estado_id' => $this->estado_id,
            'mantenimiento_id' => $this->mantenimiento_id,
            'observaciones' => $this->observaciones,
        ]);

        logger("Bien creado con ID: " . $bien->id);

        // Guardar el detalle del bien asociado
        if (!empty(array_filter($this->detalleBien ?? []))) {
            Detalle::create(array_merge(
                ['bien_id' => $bien->id],
                $this->detalleBien
            ));
        }

        session()->flash('message', 'Bien creado exitosamente.');
        $this->resetInput();
        $this->mostrarFormulario = false;
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

    public function abrirModalEliminacion($bienId)
    {
        $this->bienSeleccionadoId = $bienId;
        $this->motivoSeleccionado = null;
        $this->motivoNuevo = '';

        // Motivos desde la base de datos
        $motivosDB = HistorialEliminacionBien::whereNotNull('motivo')
            ->where('motivo', '!=', '')
            ->distinct()
            ->orderBy('motivo')
            ->pluck('motivo')
            ->toArray();

        // 👉 Combina motivos base definidos en la propiedad + base de datos → únicos y sin vacíos
        $this->motivosBase = array_unique(array_merge($this->motivosBase, $motivosDB));

        $this->dispatch('abrir-modal-eliminar-bien');
    }

    public function solicitarEliminacion()
    {
        $motivoFinal = $this->motivoSeleccionado === 'otro' ? trim($this->motivoNuevo) : $this->motivoSeleccionado;

        logger()->info('Motivo de eliminación:', ['motivo' => $motivoFinal]);

        if (empty($motivoFinal)) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: 'Debe ingresar un motivo.');
            return;
        }

        $user = auth()->user();
        $bien = Bien::with('dependencia.user')->findOrFail($this->bienSeleccionadoId);

        // Si tiene rol autorizado → eliminar directamente
        if ($user->hasRole('Administrador') || $user->hasRole('Rector')) {
            // Registrar en el historial como aprobado
            HistorialEliminacionBien::create([
                'bien_id' => $bien->id,
                'dependencia_id' => $bien->dependencia_id,
                'user_id' => $bien->dependencia->user->id,
                'motivo' => $motivoFinal,
                'estado' => 'aprobado',
                'aprobado_por' => $user->id,
                'created_at' => now(),
            ]);

            // Aplicar soft delete al bien
            $bien->delete();

            $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'El bien fue eliminado correctamente.');
            $this->dispatch('cerrar-modal-eliminar-bien');
            return;
        }

        // User sin permiso → guardar solicitud pendiente        

        // Verificar si el usuario pertenece a la dependencia del bien
        if (!$user->dependencias->pluck('id')->contains($bien->dependencia_id)) {
            return redirect()->route('inventario.bienes.index');
        }

        // Verificar si ya existe una solicitud pendiente para este bien
        $yaExiste = HistorialEliminacionBien::where('bien_id', $bien->id)
            ->where('estado', 'pendiente')
            ->exists();

        if ($yaExiste) {
            $this->dispatch('mostrar-mensaje', tipo: 'warning', mensaje: 'Ya existe una solicitud pendiente para este bien.');
            $this->dispatch('cerrar-modal-eliminar-bien');
            return;
        }

        // Crear el registro de solicitud pendiente    
        $solicitud = HistorialEliminacionBien::create([
            'bien_id' => $bien->id,
            'dependencia_id' => $bien->dependencia_id,
            'user_id' => $bien->dependencia->user->id,
            'motivo' => $motivoFinal,
            'estado' => 'pendiente'
        ]);

        // Enviar notificación a administradores y rector
        $usersDestino = User::whereHas('role', function ($query) {
            $query->whereIn('nombre', ['Administrador', 'Rector']);
        })->get();

        Notification::send($usersDestino, new NotificacionHeb($solicitud));

        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Solicitud de eliminación enviada.');
        $this->dispatch('cerrar-modal-eliminar-bien');
    }

    public function resetInput()
    {
        foreach (
            [
                'nombreSeleccionado',
                'nombreNuevo',
                'origenSeleccionado',
                'origenNuevo',
                'serie',
                'fecha_adquisicion',
                'precio',
                'cantidad',
                'categoria_id',
                'dependencia_id',
                'almacenamiento_id',
                'estado_id',
                'mantenimiento_id',
                'observaciones'
            ] as $campo
        ) {
            $this->$campo = null;
        }

        $this->detalleBien = [];
        $this->verTodos = false;
    }

    // ------------------ Métodos auxiliares ------------------ //

    private function cargarCatalogos()
    {
        $user = auth()->user();

        $dependenciasIds = $user->hasRole('Administrador') || $user->hasRole('Rector')
            ? Dependencia::pluck('id')
            : Dependencia::where('user_id', $user->id)->pluck('id');

        $bienes = Bien::whereIn('dependencia_id', $dependenciasIds)->get();

        $this->users = User::whereIn(
            'id',
            Dependencia::whereIn('id', $bienes->pluck('dependencia_id')->unique())
                ->pluck('user_id')->unique()
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
            'dependencia.user',
            'almacenamiento',
            'estado',
            'mantenimiento',
            'modificacionesPendientes'
        ]);

        if ($user->hasRole('Coordinador') && !$this->verTodos) {
            $query->whereHas('dependencia', fn($q) => $q->where('user_id', $user->id));
        } elseif (!$user->hasRole('Administrador') && !$user->hasRole('Rector')) {
            $query->whereHas('dependencia', fn($q) => $q->where('user_id', $user->id));
        }

        return $query
            ->when($this->filtroUser, fn($q) => $q->whereHas('dependencia', fn($sub) => $sub->where('user_id', $this->filtroUser)))
            ->when($this->filtroCategoria, fn($q) => $q->where('categoria_id', $this->filtroCategoria))
            ->when($this->filtroDependencia, fn($q) => $q->where('dependencia_id', $this->filtroDependencia))
            ->when($this->filtroEstado, fn($q) => $q->where('estado_id', $this->filtroEstado))
            ->when($this->filtroNombre, fn($q) => $q->where('nombre', $this->filtroNombre))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function actualizarOpcionesFiltros()
    {
        $bienes = $this->filtrarBienesQuery()->get();

        $this->users = User::whereIn(
            'id',
            Dependencia::whereIn('id', $bienes->pluck('dependencia_id')->unique())
                ->pluck('user_id')->unique()
        )->orderBy('nombres')->orderBy('apellidos')->get();

        $this->categorias = Categoria::whereIn('id', $bienes->pluck('categoria_id')->unique())->orderBy('nombre')->get();
        $this->dependencias = Dependencia::whereIn('id', $bienes->pluck('dependencia_id')->unique())->orderBy('nombre')->get();
        $this->estados = Estado::whereIn('id', $bienes->pluck('estado_id')->unique())->get();

        foreach (['user', 'categoria', 'dependencia', 'estado'] as $campo) {
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
            'filtroUser',
            'filtroCategoria',
            'filtroDependencia',
            'filtroEstado',
            'filtroNombre'
        ]);
        $this->resetPage();
        $this->cargarCatalogos();
    }

    // ------------------ Reactividad de filtros ------------------ //

    public function updatedFiltroUser()
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
