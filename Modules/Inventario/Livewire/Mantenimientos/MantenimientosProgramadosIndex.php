<?php

namespace Modules\Inventario\Livewire\Mantenimientos;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Inventario\Entities\Bien;
use Modules\Inventario\Entities\MantenimientoProgramado;

class MantenimientosProgramadosIndex extends Component
{
    use WithPagination;

    public string $busqueda        = '';
    public string $filtroEstado    = '';
    public string $filtroTipo      = '';
    public int    $perPage         = 25;
    public string $sortField       = 'fecha_programada';
    public string $sortDirection   = 'asc';

    // Panel de creación
    public bool    $creando            = false;
    public ?int    $formBienId         = null;
    public string  $formTipo           = 'preventivo';
    public string  $formTitulo         = '';
    public string  $formDescripcion    = '';
    public string  $formFechaProgramada = '';

    // Panel de edición
    public ?int   $editandoId          = null;
    public string $editTipo            = 'preventivo';
    public string $editTitulo          = '';
    public string $editDescripcion     = '';
    public string $editFechaProgramada = '';

    // Panel de completar (marcar realizado)
    public ?int   $realizandoId        = null;
    public string $realizFechaRealizada = '';

    public array $bienes = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasPermission('ver-mantenimientos-programados'), 403);
        $this->formFechaProgramada  = now()->addDays(7)->toDateString();
        $this->realizFechaRealizada = now()->toDateString();
        $this->bienes = Bien::orderBy('nombre')->pluck('nombre', 'id')->toArray();
    }

    public function updatingBusqueda(): void     { $this->resetPage(); }
    public function updatingFiltroEstado(): void { $this->resetPage(); }
    public function updatingFiltroTipo(): void   { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
    }

    // ─── Creación ───────────────────────────────────────────────────────────

    public function abrirFormulario(): void
    {
        abort_unless(auth()->user()?->hasPermission('crear-mantenimientos-programados'), 403);
        $this->resetFormulario();
        $this->creando = true;
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()?->hasPermission('crear-mantenimientos-programados'), 403);

        $this->validate([
            'formBienId'          => 'required|exists:bienes,id',
            'formTipo'            => 'required|in:preventivo,correctivo',
            'formTitulo'          => 'required|string|max:200',
            'formDescripcion'     => 'nullable|string|max:1000',
            'formFechaProgramada' => 'required|date',
        ]);

        MantenimientoProgramado::create([
            'bien_id'          => $this->formBienId,
            'user_id'          => auth()->id(),
            'tipo'             => $this->formTipo,
            'titulo'           => $this->formTitulo,
            'descripcion'      => $this->formDescripcion ?: null,
            'fecha_programada' => $this->formFechaProgramada,
            'estado'           => 'pendiente',
        ]);

        $this->resetFormulario();
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Mantenimiento programado correctamente.');
    }

    // ─── Edición ────────────────────────────────────────────────────────────

    public function iniciarEdicion(int $id): void
    {
        abort_unless(auth()->user()?->hasPermission('editar-mantenimientos-programados'), 403);
        $reg = MantenimientoProgramado::findOrFail($id);
        abort_unless($reg->esPendiente(), 422);

        $this->resetFormulario();
        $this->editandoId          = $id;
        $this->editTipo            = $reg->tipo;
        $this->editTitulo          = $reg->titulo;
        $this->editDescripcion     = $reg->descripcion ?? '';
        $this->editFechaProgramada = $reg->fecha_programada->toDateString();
    }

    public function guardarEdicion(): void
    {
        abort_unless(auth()->user()?->hasPermission('editar-mantenimientos-programados'), 403);

        $this->validate([
            'editTipo'            => 'required|in:preventivo,correctivo',
            'editTitulo'          => 'required|string|max:200',
            'editDescripcion'     => 'nullable|string|max:1000',
            'editFechaProgramada' => 'required|date',
        ]);

        $reg = MantenimientoProgramado::findOrFail($this->editandoId);
        abort_unless($reg->esPendiente(), 422);

        $reg->update([
            'tipo'             => $this->editTipo,
            'titulo'           => $this->editTitulo,
            'descripcion'      => $this->editDescripcion ?: null,
            'fecha_programada' => $this->editFechaProgramada,
        ]);

        $this->resetFormulario();
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Mantenimiento actualizado.');
    }

    // ─── Completar (realizado) ────────────────────────────────────────────

    public function iniciarRealizado(int $id): void
    {
        abort_unless(auth()->user()?->hasPermission('cancelar-mantenimientos-programados'), 403);
        $this->resetFormulario();
        $this->realizandoId         = $id;
        $this->realizFechaRealizada = now()->toDateString();
    }

    public function confirmarRealizado(): void
    {
        abort_unless(auth()->user()?->hasPermission('cancelar-mantenimientos-programados'), 403);

        $this->validate([
            'realizFechaRealizada' => 'required|date',
        ]);

        $reg = MantenimientoProgramado::findOrFail($this->realizandoId);
        $reg->update([
            'estado'          => 'realizado',
            'fecha_realizada' => $this->realizFechaRealizada,
        ]);

        $this->resetFormulario();
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Mantenimiento marcado como realizado.');
    }

    // ─── Cancelar ────────────────────────────────────────────────────────

    public function cancelarMantenimiento(int $id): void
    {
        abort_unless(auth()->user()?->hasPermission('cancelar-mantenimientos-programados'), 403);
        MantenimientoProgramado::findOrFail($id)->update(['estado' => 'cancelado']);
        $this->dispatch('mostrar-mensaje', tipo: 'warning', mensaje: 'Mantenimiento cancelado.');
    }

    public function cancelar(): void
    {
        $this->resetFormulario();
    }

    private function resetFormulario(): void
    {
        $this->creando              = false;
        $this->formBienId           = null;
        $this->formTipo             = 'preventivo';
        $this->formTitulo           = '';
        $this->formDescripcion      = '';
        $this->formFechaProgramada  = now()->addDays(7)->toDateString();
        $this->editandoId           = null;
        $this->editTipo             = 'preventivo';
        $this->editTitulo           = '';
        $this->editDescripcion      = '';
        $this->editFechaProgramada  = '';
        $this->realizandoId         = null;
        $this->realizFechaRealizada = now()->toDateString();
    }

    public function render()
    {
        $columnasSortables = ['fecha_programada', 'titulo', 'tipo', 'estado'];
        $sortField = in_array($this->sortField, $columnasSortables) ? $this->sortField : 'fecha_programada';

        $registros = MantenimientoProgramado::with(['bien', 'user'])
            ->when($this->busqueda, function ($q) {
                $q->whereHas('bien', fn($b) => $b->where('nombre', 'like', '%' . $this->busqueda . '%'));
            })
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroTipo,   fn($q) => $q->where('tipo',   $this->filtroTipo))
            ->orderBy($sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('inventario::livewire.mantenimientos.mantenimientos-programados-index', [
            'registros' => $registros,
        ]);
    }
}
