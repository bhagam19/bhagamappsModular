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
        Schema::create('bienes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->nullable();
            $table->string('detalle', 400)->nullable();
            $table->string('serie', 40)->nullable();
            $table->string('origen', 40)->nullable();
            $table->date('fechaAdquisicion')->nullable();
            $table->float('precio')->nullable();
            $table->integer('cantidad')->nullable();
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->foreignId('dependencia_id')->constrained('dependencias');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('almacenamiento_id')->constrained('almacenamientos');
            $table->foreignId('estado_id')->constrained('estados');
            $table->foreignId('mantenimiento_id')->constrained('mantenimientos');
            $table->string('observaciones', 200)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bienes');
    }
};
