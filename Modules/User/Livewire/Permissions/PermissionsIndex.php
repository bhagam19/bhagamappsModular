<?php

namespace Modules\User\Livewire\Permissions;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\User\Entities\Permission;
use Illuminate\Support\Str;

class PermissionsIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap'; // para que se vea bien con Bootstrap

    // Propiedades para el formulario
    public $nombre, $descripcion, $categoria, $nuevaCategoria;

    // Categorías existentes
    public $categorias = [];

    public function mount()
    {

        if (!auth()->user()->hasPermission('ver-permisos')) {
            return redirect()->route('ppal.index');
        }

        // Cargar categorías únicas
        $this->categorias = Permission::select('categoria')
            ->distinct()
            ->pluck('categoria')
            ->filter()
            ->map(fn($cat) => strtolower($cat))
            ->unique()
            ->values()
            ->toArray();
    }

    public function render()
    {
        // Obtener permisos paginados y ordenados
        $permissions = Permission::orderBy('id', 'desc')->paginate(10);

        return view('user::livewire.permissions.permissions-index', [
            'permissions' => $permissions,
            'categorias' => $this->categorias,
        ])->layout('layouts.app');
    }

    public function store()
    {
        $categoriaFinal = $this->categoria === 'otra' ? $this->nuevaCategoria : $this->categoria;

        $this->validate([
            'nombre' => 'required|unique:permissions,nombre',
            'descripcion' => 'nullable|string',
            'categoria' => 'required|string',
            'nuevaCategoria' => $this->categoria === 'otra' ? 'required|string|max:255' : 'nullable',
        ]);

        Permission::create([
            'nombre' => $this->nombre,
            'slug' => Str::slug($this->nombre),
            'categoria' => ucfirst($categoriaFinal),
            'descripcion' => $this->descripcion,
        ]);

        // Reiniciar campos del formulario
        $this->resetInput();

        // Reiniciar la paginación para que muestre la página 1 con el nuevo permiso
        $this->resetPage();

        // Actualizar las categorías (por si se añadió una nueva)
        $this->categorias = Permission::select('categoria')
            ->distinct()
            ->pluck('categoria')
            ->filter()
            ->map(fn($cat) => strtolower($cat))
            ->unique()
            ->values()
            ->toArray();

        session()->flash('message', 'Permiso creado exitosamente.');
    }

    public function delete($id)
    {
        Permission::findOrFail($id)->delete();

        // Reiniciar paginación para evitar mostrar página vacía
        $this->resetPage();

        session()->flash('message', 'Permiso eliminado.');
    }

    public function resetInput()
    {
        $this->nombre = '';
        $this->descripcion = '';
        $this->categoria = '';
        $this->nuevaCategoria = '';
    }
}
