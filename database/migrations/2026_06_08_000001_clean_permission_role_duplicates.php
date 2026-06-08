<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Elimina los registros con id mayor al mínimo para cada par (role_id, permission_id).
        // Conserva siempre el registro más antiguo (id más bajo).
        // Idempotente: si no hay duplicados, no elimina nada.
        DB::statement('
            DELETE pr1
            FROM permission_role pr1
            INNER JOIN permission_role pr2
                ON  pr1.role_id       = pr2.role_id
                AND pr1.permission_id = pr2.permission_id
                AND pr1.id            > pr2.id
        ');
    }

    public function down(): void
    {
        // La restauración completa del estado anterior se realiza desde:
        // docs/impl/backups/permission_role_before_cleanup.sql
        //
        // Comando:
        //   mysql -u <user> -p <database> < docs/impl/backups/permission_role_before_cleanup.sql
    }
};
