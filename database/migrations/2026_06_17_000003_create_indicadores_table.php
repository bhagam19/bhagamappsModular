<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('componente_id')->constrained('componentes')->cascadeOnDelete();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->text('formula')->nullable();
            $table->string('unidad', 50)->nullable();
            $table->string('frecuencia', 20)->nullable();
            $table->string('tipo', 20)->nullable();
            $table->string('fuente_dato', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicadores');
    }
};
