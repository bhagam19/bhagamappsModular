<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bienes', function (Blueprint $table) {
            $table->foreignId('origen_id')
                ->nullable()
                ->after('origen')
                ->constrained('origenes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bienes', function (Blueprint $table) {
            $table->dropForeign(['origen_id']);
            $table->dropColumn('origen_id');
        });
    }
};
