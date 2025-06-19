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
            $table->integer('cantidad')->nullable();
            $table->string('serie', 40)->nullable();
            $table->string('origen', 40)->nullable();
            $table->date('fecha_adquisicion')->nullable(); // snake_case recomendado para fechas
            $table->float('precio', 12, 2)->nullable(); // precisión recomendada para precios
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->foreignId('dependencia_id')->nullable()->constrained('dependencias')->nullOnDelete();
            $table->foreignId('almacenamiento_id')->nullable()->constrained('almacenamientos')->nullOnDelete();
            $table->foreignId('estado_id')->nullable()->constrained('estados')->nullOnDelete();
            $table->foreignId('mantenimiento_id')->nullable()->constrained('mantenimientos')->nullOnDelete();
            $table->string('observaciones', 200)->nullable();
            $table->softDeletes(); // columna deleted_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('bienes');

        Schema::enableForeignKeyConstraints();
    }
};
