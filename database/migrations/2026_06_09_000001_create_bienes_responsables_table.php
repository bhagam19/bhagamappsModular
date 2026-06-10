<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bienes_responsables')) {
            return;
        }

        Schema::create('bienes_responsables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained('bienes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->date('fecha_asignacion');
            $table->date('fecha_retiro')->nullable();
            $table->foreignId('asignado_por_user_id')->constrained('users');
            $table->timestamps();

            // Solo un responsable activo por bien (fecha_retiro IS NULL)
            $table->index(['bien_id', 'fecha_retiro']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienes_responsables');
    }
};
