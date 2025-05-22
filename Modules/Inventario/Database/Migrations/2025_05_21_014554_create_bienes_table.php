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
            $table->string('nom_bien', 100)->nullable();
            $table->string('detalle_del_bien', 400)->nullable();
            $table->string('serie_del_bien', 40)->nullable();
            $table->string('origen_del_bien', 40)->nullable();
            $table->date('fecha_adquisicion')->nullable();
            $table->float('precio')->nullable();
            $table->integer('cant_bien')->nullable();
            $table->foreignId('cod_categoria')->constrained('categorias_de_bienes');
            $table->foreignId('cod_dependencias')->constrained('dependencias');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('cod_almacenamiento')->constrained('almacenamientos');
            $table->foreignId('cod_estado')->constrained('estado_del_bien');
            $table->foreignId('cod_mantenimiento')->constrained('mantenimientos');
            $table->string('observaciones', 200)->nullable();
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
