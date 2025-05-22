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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); 
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('userID', 191)->unique(); // probablemente un código interno de la institución
            $table->string('email', 191)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // Relaciones
            $table->foreignId('role_id')->constrained()->onDelete('restrict'); // Asocia al rol
            $table->foreignId('current_team_id')->nullable(); // Jetstream
            $table->string('profile_photo_path', 2048)->nullable(); // Jetstream

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
