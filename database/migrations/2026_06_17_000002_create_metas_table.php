<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objetivo_id')->constrained('objetivos')->cascadeOnDelete();
            $table->foreignId('componente_id')->constrained('componentes')->cascadeOnDelete();
            $table->string('codigo', 25)->unique();
            $table->string('nombre', 300);
            $table->text('descripcion')->nullable();
            $table->string('unidad', 50)->nullable();
            $table->decimal('valor_objetivo', 10, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas');
    }
};
