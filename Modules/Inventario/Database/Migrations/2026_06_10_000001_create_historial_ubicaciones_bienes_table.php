<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_ubicaciones_bienes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained('bienes')->onDelete('cascade');
            $table->foreignId('ubicacion_origen_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->foreignId('ubicacion_destino_id')->constrained('ubicaciones')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_movimiento')->useCurrent();
            $table->string('observaciones', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_ubicaciones_bienes');
    }
};
