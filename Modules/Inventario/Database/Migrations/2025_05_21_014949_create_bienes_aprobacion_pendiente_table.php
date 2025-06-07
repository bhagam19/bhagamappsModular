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
        Schema::create('bienes_aprobacion_pendiente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained('bienes')->onDelete('cascade');
            $table->string('tipo_objeto')->default('bien'); // bien o detalle
            $table->string('campo'); // campo que se modificó
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->foreignId('dependencia_id')->constrained('dependencias')->onDelete('cascade');
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bienes_aprobacion_pendiente');
    }
};
