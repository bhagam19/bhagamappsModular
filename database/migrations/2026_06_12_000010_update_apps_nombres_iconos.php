<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // "Usuarios" → "Gestión de Acceso" con ícono de control de acceso
        DB::table('apps')->where('id', 1)->update([
            'nombre' => 'Gestión de Acceso',
            'icono'  => 'fas fa-user-shield',
        ]);

        // "Aplicaciones" → ícono más representativo de lanzador de apps
        DB::table('apps')->where('id', 3)->update([
            'icono' => 'fas fa-rocket',
        ]);
    }

    public function down(): void
    {
        DB::table('apps')->where('id', 1)->update([
            'nombre' => 'Usuarios',
            'icono'  => 'fas fa-users',
        ]);

        DB::table('apps')->where('id', 3)->update([
            'icono' => 'fas fa-th-large',
        ]);
    }
};
