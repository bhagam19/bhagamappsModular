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
        Schema::create('mantenimientos_programados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained('bienes')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // quien lo programó
            $table->string('tipo')->default('preventivo'); // preventivo, correctivo, etc.
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->date('fecha_programada');
            $table->date('fecha_realizada')->nullable();
            $table->enum('estado', ['pendiente', 'realizado', 'cancelado'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos_programados');
    }
};
