<?php

use Illuminate\Database\Migrations\Migration;
use Modules\User\Entities\Permission;
use Modules\User\Entities\Role;

return new class extends Migration
{
    public function up(): void
    {
        $nuevos = [
            [
                'slug'        => 'ver-grupos',
                'nombre'      => 'ver grupos',
                'descripcion' => 'Permite acceder a la sección de administración de grupos académicos.',
                'categoria'   => 'grupos',
            ],
            [
                'slug'        => 'ver-evaluacion-docente',
                'nombre'      => 'ver evaluación docente',
                'descripcion' => 'Permite acceder a la sección de evaluación docente.',
                'categoria'   => 'evaluacion-docente',
            ],
            [
                'slug'        => 'ver-biblioteca',
                'nombre'      => 'ver biblioteca',
                'descripcion' => 'Permite acceder a la sección de biblioteca.',
                'categoria'   => 'biblioteca',
            ],
        ];

        foreach ($nuevos as $data) {
            Permission::firstOrCreate(['slug' => $data['slug']], $data);
        }

        $slugsAdmin = ['ver-grupos', 'ver-evaluacion-docente', 'ver-biblioteca', 'ver-usuarios'];
        $permissionIds = Permission::whereIn('slug', $slugsAdmin)->pluck('id');

        Role::whereIn('nombre', ['Administrador', 'Rector', 'Coordinador'])->get()
            ->each(fn (Role $rol) => $rol->permissions()->syncWithoutDetaching($permissionIds));
    }

    public function down(): void
    {
        $slugs = ['ver-grupos', 'ver-evaluacion-docente', 'ver-biblioteca'];
        $ids   = Permission::whereIn('slug', $slugs)->pluck('id');

        Role::all()->each(fn (Role $rol) => $rol->permissions()->detach($ids));

        Permission::whereIn('slug', $slugs)->delete();

        $verUsuarios = Permission::where('slug', 'ver-usuarios')->first();
        if ($verUsuarios) {
            Role::where('nombre', 'Coordinador')->first()
                ?->permissions()->detach($verUsuarios->id);
        }
    }
};
