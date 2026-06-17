<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_indicador', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meta_id')->constrained('metas')->cascadeOnDelete();
            $table->foreignId('indicador_id')->constrained('indicadores')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['meta_id', 'indicador_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_indicador');
    }
};
