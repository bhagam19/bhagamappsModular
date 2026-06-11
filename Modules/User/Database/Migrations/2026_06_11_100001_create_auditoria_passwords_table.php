<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_passwords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_afectado_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('administrador_id')->constrained('users')->onDelete('cascade');
            $table->enum('accion', [
                'password_reset',
                'password_forced',
                'user_blocked',
                'user_unblocked',
            ]);
            $table->timestamp('fecha_hora');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_passwords');
    }
};
