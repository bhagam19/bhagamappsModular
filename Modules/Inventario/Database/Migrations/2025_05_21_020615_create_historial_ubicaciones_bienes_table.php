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
        Schema::create('historial_ubicaciones_bienes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained('bienes')->onDelete('cascade');
            $table->foreignId('ubicacion_anterior_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->foreignId('ubicacion_nueva_id')->constrained('ubicaciones')->onDelete('cascade');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete(); // quien hizo el cambio
            $table->timestamp('fecha_cambio')->useCurrent();
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
