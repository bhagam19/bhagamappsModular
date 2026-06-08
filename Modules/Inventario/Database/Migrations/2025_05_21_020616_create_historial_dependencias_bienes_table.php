<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('historial_dependencias_bienes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained('bienes')->onDelete('cascade');
            $table->foreignId('dependencia_anterior_id')->nullable()->constrained('dependencias')->nullOnDelete();
            $table->foreignId('dependencia_nueva_id')->constrained('dependencias')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // quien hizo el cambio
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete(); // quien aprobó el cambio
            $table->timestamp('fecha_modificacion')->useCurrent(); // coherente con el código anterior
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_ubicaciones_bienes');
    }
};
