<?php

namespace Modules\Inventario\Livewire\Catalogos;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Inventario\Entities\Origen;

class OrigenesIndex extends Component
{
    use WithPagination;

    public string $busqueda = '';
    public int $perPage = 25;
    public string $sortField = 'nombre';
    public string $sortDirection = 'asc';

    public bool $creando = false;
    public string $nuevoNombre = '';
    public string $nuevaDescripcion = '';

    public ?int $editandoId = null;
    public string $editNombre = '';
    public string $editDescripcion = '';

    public ?int $eliminandoId = null;
    public string $eliminandoNombre = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasPermission('ver-origenes'), 403);
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
        abort_unless(auth()->user()?->hasPermission('crear-origenes'), 403);
        $this->cancelarEdicion();
        $this->cancelarEliminacion();
        $this->creando = true;
        $this->nuevoNombre = '';
        $this->nuevaDescripcion = '';
    }

    public function guardarNuevo(): void
    {
        abort_unless(auth()->user()?->hasPermission('crear-origenes'), 403);
        $this->validate([
            'nuevoNombre'      => 'required|string|max:255|unique:origenes,nombre',
            'nuevaDescripcion' => 'nullable|string|max:500',
        ]);
        Origen::create(['nombre' => $this->nuevoNombre, 'descripcion' => $this->nuevaDescripcion ?: null]);
        $this->creando = false;
        $this->nuevoNombre = '';
        $this->nuevaDescripcion = '';
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Origen creado correctamente.');
    }

    public function cancelarCreacion(): void
    {
        $this->creando = false;
        $this->nuevoNombre = '';
        $this->nuevaDescripcion = '';
    }

    public function editar(int $id): void
    {
        abort_unless(auth()->user()?->hasPermission('editar-origenes'), 403);
        $this->cancelarCreacion();
        $this->cancelarEliminacion();
        $origen = Origen::findOrFail($id);
        $this->editandoId = $id;
        $this->editNombre = $origen->nombre;
        $this->editDescripcion = $origen->descripcion ?? '';
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()?->hasPermission('editar-origenes'), 403);
        $this->validate([
            'editNombre'      => 'required|string|max:255|unique:origenes,nombre,' . $this->editandoId,
            'editDescripcion' => 'nullable|string|max:500',
        ]);
        Origen::findOrFail($this->editandoId)->update([
            'nombre'      => $this->editNombre,
            'descripcion' => $this->editDescripcion ?: null,
        ]);
        $this->cancelarEdicion();
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Origen actualizado correctamente.');
    }

    public function cancelarEdicion(): void
    {
        $this->editandoId = null;
        $this->editNombre = '';
        $this->editDescripcion = '';
    }

    public function confirmarEliminacion(int $id): void
    {
        abort_unless(auth()->user()?->hasPermission('eliminar-origenes'), 403);
        $this->cancelarEdicion();
        $this->cancelarCreacion();
        $origen = Origen::findOrFail($id);
        $this->eliminandoId = $id;
        $this->eliminandoNombre = $origen->nombre;
    }

    public function cancelarEliminacion(): void
    {
        $this->eliminandoId = null;
        $this->eliminandoNombre = '';
    }

    public function eliminar(): void
    {
        abort_unless(auth()->user()?->hasPermission('eliminar-origenes'), 403);
        $origen = Origen::findOrFail($this->eliminandoId);
        $nombre = $origen->nombre;
        $origen->delete();
        $this->cancelarEliminacion();
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: "Origen \"{$nombre}\" eliminado.");
    }

    public function render()
    {
        $items = Origen::query()
            ->when($this->busqueda, fn($q) => $q->where('nombre', 'like', '%' . $this->busqueda . '%')
                ->orWhere('descripcion', 'like', '%' . $this->busqueda . '%'))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('inventario::livewire.catalogos.origenes-index', ['items' => $items]);
    }
}
