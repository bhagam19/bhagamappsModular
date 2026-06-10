<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // H-CRIT-001: crear permiso gestionar-historial-eliminaciones-bienes
        $permId = \DB::table('permissions')->insertGetId([
            'nombre'      => 'gestionar historial eliminaciones bienes',
            'slug'        => 'gestionar-historial-eliminaciones-bienes',
            'descripcion' => 'Permite ver y gestionar las solicitudes de eliminación de bienes pendientes de aprobación.',
            'categoria'   => 'aprobaciones pendientes',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Asignar a Administrador y Rector
        $roles = \DB::table('roles')->whereIn('nombre', ['Administrador', 'Rector'])->pluck('id');

        foreach ($roles as $roleId) {
            \DB::table('permission_role')->insert([
                'role_id'       => $roleId,
                'permission_id' => $permId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    public function down(): void
    {
        $permId = \DB::table('permissions')
            ->where('slug', 'gestionar-historial-eliminaciones-bienes')
            ->value('id');

        if ($permId) {
            \DB::table('permission_role')->where('permission_id', $permId)->delete();
            \DB::table('permissions')->where('id', $permId)->delete();
        }
    }
};
