<?php

namespace Modules\User\Livewire\Roles;

use Livewire\Component;
use Modules\User\Entities\Role;
use Modules\User\Entities\Permission;

class EditarRolePermissions extends Component
{
    public $role;
    public $groupedPermissions = [];
    public $selectedPermissions = [];

    public function mount(Role $role)
    {
        $this->role = $role;
        $this->selectedPermissions = $role->permissions()->pluck('permissions.id')->toArray();

        // Agrupar permisos por prefijo (categoría)
        $permissions = Permission::all();
        foreach ($permissions as $permission) {
            $group = lcfirst($permission->categoria); // Ej: "User" → "user"
            $this->groupedPermissions[$group][] = $permission;
        }
    }

    public function save()
    {
        $this->validate([
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'exists:permissions,id',
        ]);

        $this->role->permissions()->sync($this->selectedPermissions);
        session()->flash('message', 'Permisos actualizados correctamente.');
        return redirect()->route('user.roles.index');
    }

    public function render()
    {
        return view('user::livewire.roles.editar-role-permissions')
            ->layout('layouts.app');
    }
}
