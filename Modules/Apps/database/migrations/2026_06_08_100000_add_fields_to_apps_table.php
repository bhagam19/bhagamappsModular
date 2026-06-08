<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apps', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('nombre');
            $table->text('descripcion')->nullable()->after('ruta');
            $table->string('icono')->nullable()->after('imagen');
            $table->string('color', 20)->nullable()->after('icono');
            $table->unsignedInteger('orden')->default(99)->after('habilitada');
        });
    }

    public function down(): void
    {
        Schema::table('apps', function (Blueprint $table) {
            $table->dropColumn(['slug', 'descripcion', 'icono', 'color', 'orden']);
        });
    }
};
