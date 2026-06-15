<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DependenciasSeeder extends Seeder
{
    public function run(): void
    {
        $path = __DIR__ . '/data/dependencias.csv';

        $handle = fopen($path, 'r');

        // Primera línea: encabezados
        $headers = array_map('trim', fgetcsv($handle));

        DB::transaction(function () use ($handle, $headers) {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < count($headers)) {
                    continue;
                }

                $data = array_combine($headers, $row);

                // updateOrCreate preserva IDs y es idempotente.
                // El id es crítico: bienes.dependencia_id lo referencia.
                DB::table('dependencias')->updateOrInsert(
                    ['id' => (int) $data['id']],
                    [
                        'nombre'       => trim($data['nombre']),
                        'ubicacion_id' => (int) $data['ubicacion_id'],
                        'user_id'      => (int) $data['user_id'],
                        'created_at'   => $data['created_at'],
                        'updated_at'   => $data['updated_at'],
                    ]
                );
            }
        });

        fclose($handle);
    }
}
