<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // H-CRIT-002 (Escenario A): la tabla fue planificada; modelo, permisos y seeder existen.
    public function up(): void
    {
        Schema::create('bienes_responsables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained('bienes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('observaciones', 255)->nullable();
            $table->date('fecha_asignacion')->nullable();
            $table->date('fecha_retiro')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bienes_responsables');
        Schema::enableForeignKeyConstraints();
    }
};
