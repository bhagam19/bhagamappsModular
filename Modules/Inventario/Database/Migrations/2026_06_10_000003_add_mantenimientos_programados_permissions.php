<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permisos = [
            ['slug' => 'ver-mantenimientos-programados',     'nombre' => 'Ver Mantenimientos Programados de Bienes',     'categoria' => 'mantenimientos'],
            ['slug' => 'crear-mantenimientos-programados',   'nombre' => 'Crear/Programar Mantenimientos de Bienes',     'categoria' => 'mantenimientos'],
            ['slug' => 'editar-mantenimientos-programados',  'nombre' => 'Editar Mantenimientos Programados de Bienes',  'categoria' => 'mantenimientos'],
            ['slug' => 'cancelar-mantenimientos-programados','nombre' => 'Completar/Cancelar Mantenimientos de Bienes',  'categoria' => 'mantenimientos'],
        ];

        DB::table('permissions')->insertOrIgnore($permisos);

        $adminId  = DB::table('roles')->where('nombre', 'Administrador')->value('id');
        $rectorId = DB::table('roles')->where('nombre', 'Rector')->value('id');
        $coordId  = DB::table('roles')->where('nombre', 'Coordinador')->value('id');

        $todosIds = DB::table('permissions')
            ->whereIn('slug', array_column($permisos, 'slug'))
            ->pluck('id');

        // Admin y Rector: todos los permisos
        foreach ($todosIds as $permId) {
            if ($adminId)  DB::table('permission_role')->insertOrIgnore(['role_id' => $adminId,  'permission_id' => $permId]);
            if ($rectorId) DB::table('permission_role')->insertOrIgnore(['role_id' => $rectorId, 'permission_id' => $permId]);
        }

        // Coordinador: solo ver y crear
        foreach (['ver-mantenimientos-programados', 'crear-mantenimientos-programados'] as $slug) {
            $permId = DB::table('permissions')->where('slug', $slug)->value('id');
            if ($permId && $coordId) {
                DB::table('permission_role')->insertOrIgnore(['role_id' => $coordId, 'permission_id' => $permId]);
            }
        }
    }

    public function down(): void
    {
        $slugs = [
            'ver-mantenimientos-programados',
            'crear-mantenimientos-programados',
            'editar-mantenimientos-programados',
            'cancelar-mantenimientos-programados',
        ];
        $ids = DB::table('permissions')->whereIn('slug', $slugs)->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('slug', $slugs)->delete();
    }
};
