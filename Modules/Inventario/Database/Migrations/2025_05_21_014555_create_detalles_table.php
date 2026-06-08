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
            $table->foreignId('bien_id')->constrained('bienes')->onDelete('cascade'); // clave foránea
            $table->string('car_especial', 255)->nullable();
            $table->string('tamano', 255)->nullable();
            $table->string('material', 255)->nullable();
            $table->string('color', 255)->nullable();
            $table->string('marca', 255)->nullable();
            $table->string('otra', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles');
    }
};
