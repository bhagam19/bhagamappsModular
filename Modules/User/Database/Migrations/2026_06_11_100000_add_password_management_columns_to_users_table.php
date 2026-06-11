<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('bloqueado')->default(false)->after('profile_photo_path');
            $table->boolean('forzar_cambio_password')->default(false)->after('bloqueado');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bloqueado', 'forzar_cambio_password']);
        });
    }
};
