<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissions = [
            ['nombre' => 'ver administración de contraseñas', 'slug' => 'ver-administracion-passwords',  'descripcion' => 'Permite acceder al panel de administración de contraseñas de usuarios.',       'categoria' => 'administracion-passwords'],
            ['nombre' => 'restablecer contraseñas',           'slug' => 'restablecer-passwords',         'descripcion' => 'Permite restablecer la contraseña de cualquier usuario.',                    'categoria' => 'administracion-passwords'],
            ['nombre' => 'bloquear usuarios',                 'slug' => 'bloquear-usuarios',             'descripcion' => 'Permite bloquear una cuenta de usuario para impedir su autenticación.',     'categoria' => 'administracion-passwords'],
            ['nombre' => 'desbloquear usuarios',              'slug' => 'desbloquear-usuarios',          'descripcion' => 'Permite desbloquear una cuenta de usuario previamente bloqueada.',           'categoria' => 'administracion-passwords'],
        ];

        foreach ($permissions as $perm) {
            $id = DB::table('permissions')->insertGetId(array_merge($perm, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));

            // Administrador (role nombre) y Rector reciben todos estos permisos.
            $roles = DB::table('roles')->whereIn('nombre', ['Administrador', 'Rector'])->pluck('id');
            foreach ($roles as $roleId) {
                DB::table('permission_role')->insert([
                    'role_id'       => $roleId,
                    'permission_id' => $id,
                ]);
            }
        }
    }

    public function down(): void
    {
        $slugs = [
            'ver-administracion-passwords',
            'restablecer-passwords',
            'bloquear-usuarios',
            'desbloquear-usuarios',
        ];

        $ids = DB::table('permissions')->whereIn('slug', $slugs)->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
