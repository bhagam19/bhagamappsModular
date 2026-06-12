<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('es_principal')->default(false)->after('forzar_cambio_password');
        });

        // Marcar como principal al primer usuario con rol Administrador (por ID ascendente)
        $adminRoleId = DB::table('roles')->where('nombre', 'Administrador')->value('id');
        if ($adminRoleId) {
            $primerId = DB::table('users')
                ->where('role_id', $adminRoleId)
                ->orderBy('id')
                ->value('id');
            if ($primerId) {
                DB::table('users')->where('id', $primerId)->update(['es_principal' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('es_principal');
        });
    }
};
