<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permisos = [
            ['slug' => 'ver-responsables-bienes',        'nombre' => 'Ver Responsables de Bienes',        'categoria' => 'responsables'],
            ['slug' => 'asignar-responsables-bienes',    'nombre' => 'Asignar Custodio a Bien',          'categoria' => 'responsables'],
            ['slug' => 'editar-responsables-bienes',     'nombre' => 'Editar Responsables de Bienes',    'categoria' => 'responsables'],
            ['slug' => 'transferir-responsables-bienes', 'nombre' => 'Transferir Responsables de Bienes','categoria' => 'responsables'],
        ];

        DB::table('permissions')->insertOrIgnore($permisos);

        $adminId = DB::table('roles')->where('nombre', 'Administrador')->value('id');
        $rectorId = DB::table('roles')->where('nombre', 'Rector')->value('id');
        $coordId  = DB::table('roles')->where('nombre', 'Coordinador')->value('id');

        $todosIds = DB::table('permissions')
            ->whereIn('slug', array_column($permisos, 'slug'))
            ->pluck('id');

        $verPermId = DB::table('permissions')
            ->where('slug', 'ver-responsables-bienes')
            ->value('id');

        foreach ($todosIds as $permId) {
            DB::table('permission_role')->insertOrIgnore(['role_id' => $adminId,  'permission_id' => $permId]);
            DB::table('permission_role')->insertOrIgnore(['role_id' => $rectorId, 'permission_id' => $permId]);
        }

        DB::table('permission_role')->insertOrIgnore(['role_id' => $coordId, 'permission_id' => $verPermId]);
    }

    public function down(): void
    {
        $slugs = [
            'ver-responsables-bienes',
            'asignar-responsables-bienes',
            'editar-responsables-bienes',
            'transferir-responsables-bienes',
        ];
        $ids = DB::table('permissions')->whereIn('slug', $slugs)->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('slug', $slugs)->delete();
    }
};
