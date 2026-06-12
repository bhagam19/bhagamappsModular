<?php

namespace Modules\User\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\User\Entities\User;
use Modules\User\Entities\Role;
use Modules\User\Traits\ProteccionAdminPrincipal;

class UserIndex extends Component
{
    use WithPagination, ProteccionAdminPrincipal;

    // Campos del formulario de creación
    public $nombres, $apellidos, $userID, $role_id, $email, $password;

    // Paginación
    public int $perPage = 25;

    // Búsqueda y filtros (USR-001, USR-002, USR-003)
    public string $busqueda      = '';
    public string $filtroRol     = '';
    public string $filtroEstado  = 'todos';

    // Ordenamiento (USR-004)
    public string $sortField     = 'nombres';
    public string $sortDirection = 'asc';

    // Roles para filtro y formulario de creación
    public array $rolesDisponibles = [];

    // Columnas
    public $availableColumns = [
        'nombres'    => 'Nombres',
        'apellidos'  => 'Apellidos',
        'userID'     => 'No. Documento',
        'rol'        => 'Rol',
        'email'      => 'Email',
        'estado'     => 'Estado',
        'created_at' => 'Creación',
    ];

    public $visibleColumns = ['id', 'nombres', 'apellidos', 'rol', 'email', 'estado'];

    public function mount()
    {
        if (!auth()->user()->hasPermission('ver-usuarios')) {
            return redirect()->route('ppal.index');
        }
        $this->rolesDisponibles = Role::orderBy('nombre')->pluck('nombre', 'id')->toArray();
    }

    // USR-005: resetPage en cada cambio de filtro; limitar perPage a valores seguros (< 16 KB snapshot)
    public function updatedPerPage(): void
    {
        if (!in_array($this->perPage, [10, 25])) {
            $this->perPage = 25;
        }
        $this->resetPage();
    }
    public function updatingBusqueda(): void     { $this->resetPage(); }
    public function updatingFiltroRol(): void    { $this->resetPage(); }
    public function updatingFiltroEstado(): void { $this->resetPage(); }

    // USR-004: ordenamiento por columna con toggle de dirección
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $query = User::query()
            ->select('users.*')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->with('role')
            ->when($this->busqueda, fn($q) => $q->where(
                fn($inner) => $inner
                    ->where('users.nombres',    'like', '%' . $this->busqueda . '%')
                    ->orWhere('users.apellidos', 'like', '%' . $this->busqueda . '%')
                    ->orWhere('users.email',     'like', '%' . $this->busqueda . '%')
            ))
            ->when($this->filtroRol !== '', fn($q) => $q->where('users.role_id', $this->filtroRol))
            ->when($this->filtroEstado === 'activos',    fn($q) => $q->where('users.bloqueado', false))
            ->when($this->filtroEstado === 'bloqueados', fn($q) => $q->where('users.bloqueado', true));

        if ($this->sortField === 'rol') {
            $query->orderBy('roles.nombre', $this->sortDirection);
        } else {
            $query->orderBy('users.' . $this->sortField, $this->sortDirection);
        }

        return view('user::livewire.user.user-index', [
            'users' => $query->paginate($this->perPage),
        ]);
    }

    public function toggleColumn($column): void
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

    public function store(): void
    {
        if (!auth()->user()->hasPermission('crear-usuarios')) {
            session()->flash('error', 'No tienes permiso para crear user.');
            return;
        }

        $this->validate([
            'nombres'   => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'userID'    => 'required|numeric|unique:users,userID',
            'role_id'   => 'required|exists:roles,id',
            'email'     => 'required|email|unique:users,email',
            'password'  => [
                'required', 'string', 'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).+$/',
            ],
        ], [
            'password.regex' => 'La contraseña debe tener al menos 8 caracteres, incluyendo una letra mayúscula, una letra minúscula, un número y un carácter especial.',
        ]);

        User::create([
            'nombres'   => ucwords(strtolower($this->nombres)),
            'apellidos' => ucwords(strtolower($this->apellidos)),
            'userID'    => $this->userID,
            'role_id'   => $this->role_id,
            'email'     => $this->email,
            'password'  => bcrypt($this->password),
        ]);

        session()->flash('message', 'Usuario creado exitosamente.');
        $this->resetInput();
    }

    public function delete($id): void
    {
        if (!auth()->user()->hasPermission('eliminar-usuarios')) {
            session()->flash('error', 'No tienes permiso para eliminar user.');
            return;
        }

        $target = User::findOrFail($id);
        $this->verificarNoEsAdminPrincipal($target, 'intento_eliminar_admin_principal');

        $target->delete();
        session()->flash('message', 'Usuario eliminado exitosamente.');
    }

    public function resetInput(): void
    {
        $this->nombres   = '';
        $this->apellidos = '';
        $this->userID    = '';
        $this->role_id   = null;
        $this->email     = '';
        $this->password  = '';
    }
}
