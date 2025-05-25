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
            $table->string('nombre', 100)->nullable();
            $table->string('detalle', 400)->nullable();
            $table->string('serie', 40)->nullable();
            $table->string('origen', 40)->nullable();
            $table->date('fechaAdquisicion')->nullable();
            $table->float('precio')->nullable();
            $table->integer('cantidad')->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias');
            $table->foreignId('dependencia_id')->nullable()->constrained('dependencias');
            $table->foreignId('usuario_id')->nullable()->constrained('users');
            $table->foreignId('almacenamiento_id')->nullable()->constrained('almacenamientos');
            $table->foreignId('estado_id')->nullable()->constrained('estados');
            $table->foreignId('mantenimiento_id')->nullable()->constrained('mantenimientos');
            $table->string('observaciones', 200)->nullable();
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
