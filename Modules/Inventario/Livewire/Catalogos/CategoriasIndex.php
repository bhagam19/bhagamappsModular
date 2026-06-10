<?php

namespace Modules\Inventario\Livewire\Catalogos;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Inventario\Entities\Categoria;

class CategoriasIndex extends Component
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
        abort_unless(auth()->user()?->hasPermission('ver-categorias'), 403);
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
        abort_unless(auth()->user()?->hasPermission('crear-categorias'), 403);
        $this->cancelarEdicion();
        $this->cancelarEliminacion();
        $this->creando = true;
        $this->nuevoNombre = '';
    }

    public function guardarNuevo(): void
    {
        abort_unless(auth()->user()?->hasPermission('crear-categorias'), 403);
        $this->validate(['nuevoNombre' => 'required|string|max:255|unique:categorias,nombre']);
        Categoria::create(['nombre' => $this->nuevoNombre]);
        $this->creando = false;
        $this->nuevoNombre = '';
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Categoría creada correctamente.');
    }

    public function cancelarCreacion(): void
    {
        $this->creando = false;
        $this->nuevoNombre = '';
    }

    public function editar(int $id): void
    {
        abort_unless(auth()->user()?->hasPermission('editar-categorias'), 403);
        $this->cancelarCreacion();
        $this->cancelarEliminacion();
        $categoria = Categoria::findOrFail($id);
        $this->editandoId = $id;
        $this->editNombre = $categoria->nombre;
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()?->hasPermission('editar-categorias'), 403);
        $this->validate(['editNombre' => 'required|string|max:255|unique:categorias,nombre,' . $this->editandoId]);
        Categoria::findOrFail($this->editandoId)->update(['nombre' => $this->editNombre]);
        $this->cancelarEdicion();
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Categoría actualizada correctamente.');
    }

    public function cancelarEdicion(): void
    {
        $this->editandoId = null;
        $this->editNombre = '';
    }

    public function confirmarEliminacion(int $id): void
    {
        abort_unless(auth()->user()?->hasPermission('eliminar-categorias'), 403);
        $this->cancelarEdicion();
        $this->cancelarCreacion();
        $categoria = Categoria::findOrFail($id);
        $this->eliminandoId = $id;
        $this->eliminandoNombre = $categoria->nombre;
    }

    public function cancelarEliminacion(): void
    {
        $this->eliminandoId = null;
        $this->eliminandoNombre = '';
    }

    public function eliminar(): void
    {
        abort_unless(auth()->user()?->hasPermission('eliminar-categorias'), 403);
        $categoria = Categoria::withCount('bienes')->findOrFail($this->eliminandoId);
        if ($categoria->bienes_count > 0) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: "No se puede eliminar: tiene {$categoria->bienes_count} bien(es) asociado(s).");
            $this->cancelarEliminacion();
            return;
        }
        $nombre = $categoria->nombre;
        $categoria->delete();
        $this->cancelarEliminacion();
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: "Categoría \"{$nombre}\" eliminada.");
    }

    public function render()
    {
        $items = Categoria::query()
            ->when($this->busqueda, fn($q) => $q->where('nombre', 'like', '%' . $this->busqueda . '%'))
            ->withCount('bienes')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('inventario::livewire.catalogos.categorias-index', ['items' => $items]);
    }
}
