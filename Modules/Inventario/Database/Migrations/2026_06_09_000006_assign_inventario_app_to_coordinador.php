<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // H-ALTO-001: Coordinador tiene permisos sobre bienes pero sin acceso app.access:inventario
    public function up(): void
    {
        $appId  = \DB::table('apps')->where('slug', 'inventario')->value('id');
        $roleId = \DB::table('roles')->where('nombre', 'Coordinador')->value('id');

        if ($appId && $roleId) {
            $exists = \DB::table('app_role')
                ->where('app_id', $appId)
                ->where('role_id', $roleId)
                ->exists();

            if (! $exists) {
                \DB::table('app_role')->insert([
                    'app_id'     => $appId,
                    'role_id'    => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $appId  = \DB::table('apps')->where('slug', 'inventario')->value('id');
        $roleId = \DB::table('roles')->where('nombre', 'Coordinador')->value('id');

        if ($appId && $roleId) {
            \DB::table('app_role')
                ->where('app_id', $appId)
                ->where('role_id', $roleId)
                ->delete();
        }
    }
};
