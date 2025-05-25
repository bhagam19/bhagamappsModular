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
        Schema::create('detalles', function (Blueprint $table) {
            $table->id();
            $table->string('car_especial', 40)->nullable();
            $table->string('tamano', 40)->nullable();
            $table->string('material', 40)->nullable();
            $table->string('color', 40)->nullable();
            $table->string('marca', 40)->nullable();
            $table->string('otra', 40)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_de_bienes');
    }
};
