<?php

namespace Modules\Inventario\Livewire\Catalogos;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Inventario\Entities\Ubicacion;
use Modules\Inventario\Entities\Dependencia;

class UbicacionesIndex extends Component
{
    use WithPagination;

    public string $busqueda = '';
    public int $perPage = 25;
    public string $sortField = 'nombre';
    public string $sortDirection = 'asc';

    public bool $creando = false;
    public string $nuevoNombre = '';

    public ?int $editandoId = null;
    public string $editNombre = '';

    public ?int $eliminandoId = null;
    public string $eliminandoNombre = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasPermission('ver-ubicaciones'), 403);
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function iniciarCreacion(): void
    {
        abort_unless(auth()->user()?->hasPermission('crear-ubicaciones'), 403);
        $this->cancelarEdicion();
        $this->cancelarEliminacion();
        $this->creando = true;
        $this->nuevoNombre = '';
    }

    public function guardarNuevo(): void
    {
        abort_unless(auth()->user()?->hasPermission('crear-ubicaciones'), 403);
        $this->validate(['nuevoNombre' => 'required|string|max:255|unique:ubicaciones,nombre']);
        Ubicacion::create(['nombre' => $this->nuevoNombre]);
        $this->creando = false;
        $this->nuevoNombre = '';
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Ubicación creada correctamente.');
    }

    public function cancelarCreacion(): void
    {
        $this->creando = false;
        $this->nuevoNombre = '';
    }

    public function editar(int $id): void
    {
        abort_unless(auth()->user()?->hasPermission('editar-ubicaciones'), 403);
        $this->cancelarCreacion();
        $this->cancelarEliminacion();
        $ubicacion = Ubicacion::findOrFail($id);
        $this->editandoId = $id;
        $this->editNombre = $ubicacion->nombre;
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()?->hasPermission('editar-ubicaciones'), 403);
        $this->validate(['editNombre' => 'required|string|max:255|unique:ubicaciones,nombre,' . $this->editandoId]);
        Ubicacion::findOrFail($this->editandoId)->update(['nombre' => $this->editNombre]);
        $this->cancelarEdicion();
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Ubicación actualizada correctamente.');
    }

    public function cancelarEdicion(): void
    {
        $this->editandoId = null;
        $this->editNombre = '';
    }

    public function confirmarEliminacion(int $id): void
    {
        abort_unless(auth()->user()?->hasPermission('eliminar-ubicaciones'), 403);
        $this->cancelarEdicion();
        $this->cancelarCreacion();
        $ubicacion = Ubicacion::findOrFail($id);
        $this->eliminandoId = $id;
        $this->eliminandoNombre = $ubicacion->nombre;
    }

    public function cancelarEliminacion(): void
    {
        $this->eliminandoId = null;
        $this->eliminandoNombre = '';
    }

    public function eliminar(): void
    {
        abort_unless(auth()->user()?->hasPermission('eliminar-ubicaciones'), 403);
        $count = Dependencia::where('ubicacion_id', $this->eliminandoId)->count();
        if ($count > 0) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: "No se puede eliminar: tiene {$count} dependencia(s) asociada(s).");
            $this->cancelarEliminacion();
            return;
        }
        $ubicacion = Ubicacion::findOrFail($this->eliminandoId);
        $nombre = $ubicacion->nombre;
        $ubicacion->delete();
        $this->cancelarEliminacion();
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: "Ubicación \"{$nombre}\" eliminada.");
    }

    public function render()
    {
        $items = Ubicacion::query()
            ->when($this->busqueda, fn($q) => $q->where('nombre', 'like', '%' . $this->busqueda . '%'))
            ->withCount('dependencias')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('inventario::livewire.catalogos.ubicaciones-index', ['items' => $items]);
    }
}
