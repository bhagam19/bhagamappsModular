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
        Schema::create('historial_modificaciones_bienes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained('bienes')->onDelete('cascade');

            $table->string('tipo_objeto')->default('bien'); // 'bien' o 'detalle'
            $table->string('campo_modificado');             // Ejemplo: 'color', 'marca'
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();

            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();   // quien modificó (puede ser null si es automático)
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete(); // quién aprobó el cambio

            $table->timestamp('fecha_modificacion')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_modificaciones_bienes');
    }
};
