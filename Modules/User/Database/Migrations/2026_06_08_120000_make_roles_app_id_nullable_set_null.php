<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina el riesgo de pérdida masiva de roles por CASCADE.
 *
 * Estado previo: roles.app_id FK → apps(id) ON DELETE CASCADE
 * Problema: eliminar una App destruye en cascada todos sus roles,
 *           y si esos roles tienen usuarios asignados, el sistema queda en estado inconsistente.
 *
 * Corrección: hacer app_id nullable con ON DELETE SET NULL.
 * Eliminar una App → app_id queda NULL en los roles; los roles NO se destruyen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['app_id']);
            $table->unsignedBigInteger('app_id')->nullable()->change();
            $table->foreign('app_id')->references('id')->on('apps')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['app_id']);
            $table->unsignedBigInteger('app_id')->nullable(false)->change();
            $table->foreign('app_id')->references('id')->on('apps')->onDelete('cascade');
        });
    }
};
