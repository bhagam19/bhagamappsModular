<?php

namespace Modules\Inventario\Livewire\Catalogos;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Inventario\Entities\Dependencia;
use Modules\Inventario\Entities\Ubicacion;
use Modules\User\Entities\User;

class DependenciasIndex extends Component
{
    use WithPagination;

    public string $busqueda = '';
    public int $perPage = 25;
    public string $sortField = 'nombre';
    public string $sortDirection = 'asc';

    public bool $creando = false;
    public string $nuevoNombre = '';
    public ?int $nuevoUbicacionId = null;
    public ?int $nuevoUserId = null;

    public ?int $editandoId = null;
    public string $editNombre = '';
    public ?int $editUbicacionId = null;
    public ?int $editUserId = null;

    public ?int $eliminandoId = null;
    public string $eliminandoNombre = '';

    public array $ubicaciones = [];
    public array $usuarios = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasPermission('ver-dependencias'), 403);
        $this->ubicaciones = Ubicacion::orderBy('nombre')->pluck('nombre', 'id')->toArray();
        $this->usuarios    = User::orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos'])
            ->mapWithKeys(fn($u) => [$u->id => trim($u->nombres . ' ' . $u->apellidos)])
            ->toArray();
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
        abort_unless(auth()->user()?->hasPermission('crear-dependencias'), 403);
        $this->cancelarEdicion();
        $this->cancelarEliminacion();
        $this->creando = true;
        $this->nuevoNombre = '';
        $this->nuevoUbicacionId = null;
        $this->nuevoUserId = null;
    }

    public function guardarNuevo(): void
    {
        abort_unless(auth()->user()?->hasPermission('crear-dependencias'), 403);
        $this->validate([
            'nuevoNombre'      => 'required|string|max:255|unique:dependencias,nombre',
            'nuevoUbicacionId' => 'nullable|exists:ubicaciones,id',
            'nuevoUserId'      => 'nullable|exists:users,id',
        ]);
        Dependencia::create([
            'nombre'       => $this->nuevoNombre,
            'ubicacion_id' => $this->nuevoUbicacionId,
            'user_id'      => $this->nuevoUserId,
        ]);
        $this->creando = false;
        $this->nuevoNombre = '';
        $this->nuevoUbicacionId = null;
        $this->nuevoUserId = null;
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Dependencia creada correctamente.');
    }

    public function cancelarCreacion(): void
    {
        $this->creando = false;
        $this->nuevoNombre = '';
        $this->nuevoUbicacionId = null;
        $this->nuevoUserId = null;
    }

    public function editar(int $id): void
    {
        abort_unless(auth()->user()?->hasPermission('editar-dependencias'), 403);
        $this->cancelarCreacion();
        $this->cancelarEliminacion();
        $dep = Dependencia::findOrFail($id);
        $this->editandoId    = $id;
        $this->editNombre    = $dep->nombre;
        $this->editUbicacionId = $dep->ubicacion_id;
        $this->editUserId      = $dep->user_id;
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()?->hasPermission('editar-dependencias'), 403);
        $this->validate([
            'editNombre'      => 'required|string|max:255|unique:dependencias,nombre,' . $this->editandoId,
            'editUbicacionId' => 'nullable|exists:ubicaciones,id',
            'editUserId'      => 'nullable|exists:users,id',
        ]);
        Dependencia::findOrFail($this->editandoId)->update([
            'nombre'       => $this->editNombre,
            'ubicacion_id' => $this->editUbicacionId,
            'user_id'      => $this->editUserId,
        ]);
        $this->cancelarEdicion();
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Dependencia actualizada correctamente.');
    }

    public function cancelarEdicion(): void
    {
        $this->editandoId    = null;
        $this->editNombre    = '';
        $this->editUbicacionId = null;
        $this->editUserId      = null;
    }

    public function confirmarEliminacion(int $id): void
    {
        abort_unless(auth()->user()?->hasPermission('eliminar-dependencias'), 403);
        $this->cancelarEdicion();
        $this->cancelarCreacion();
        $dep = Dependencia::findOrFail($id);
        $this->eliminandoId    = $id;
        $this->eliminandoNombre = $dep->nombre;
    }

    public function cancelarEliminacion(): void
    {
        $this->eliminandoId    = null;
        $this->eliminandoNombre = '';
    }

    public function eliminar(): void
    {
        abort_unless(auth()->user()?->hasPermission('eliminar-dependencias'), 403);
        $dep = Dependencia::withCount('bienes')->findOrFail($this->eliminandoId);
        if ($dep->bienes_count > 0) {
            $this->dispatch('mostrar-mensaje', tipo: 'error', mensaje: "No se puede eliminar: tiene {$dep->bienes_count} bien(es) asociado(s).");
            $this->cancelarEliminacion();
            return;
        }
        $nombre = $dep->nombre;
        $dep->delete();
        $this->cancelarEliminacion();
        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: "Dependencia \"{$nombre}\" eliminada.");
    }

    public function render()
    {
        $items = Dependencia::query()
            ->with(['ubicacion'])
            ->when($this->busqueda, fn($q) => $q->where('nombre', 'like', '%' . $this->busqueda . '%'))
            ->withCount('bienes')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('inventario::livewire.catalogos.dependencias-index', [
            'items'      => $items,
            'ubicaciones' => $this->ubicaciones,
            'usuarios'    => $this->usuarios,
        ]);
    }
}
