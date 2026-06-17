<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('actividades')->cascadeOnDelete();
            $table->string('responsable_tipo', 20)->nullable();
            $table->unsignedBigInteger('responsable_id')->nullable();
            $table->string('codigo', 25)->unique();
            $table->string('nombre', 250);
            $table->text('descripcion')->nullable();
            $table->string('estado', 20)->default('Pendiente');
            $table->unsignedTinyInteger('avance')->default(0);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
