<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BienesResponsablesSeeder extends Seeder
{
    public function run(): void
    {
        $path = __DIR__ . '/data/bienes_responsables.csv';

        if (!file_exists($path)) {
            $this->command->warn('  ⚠ bienes_responsables.csv no encontrado en data/. Omitiendo.');
            return;
        }

        $handle = fopen($path, 'r');
        $headers = array_map('trim', fgetcsv($handle));

        DB::transaction(function () use ($handle, $headers) {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < count($headers)) {
                    continue;
                }
                $data = array_combine($headers, $row);

                DB::table('bienes_responsables')->updateOrInsert(
                    ['id' => (int) $data['id']],
                    [
                        'bien_id'          => (int) $data['bien_id'],
                        'user_id'          => (int) $data['user_id'],
                        'observaciones'    => ($data['observaciones'] !== '') ? $data['observaciones'] : null,
                        'fecha_asignacion' => ($data['fecha_asignacion'] !== '') ? $data['fecha_asignacion'] : null,
                        'fecha_retiro'     => ($data['fecha_retiro'] !== '' && $data['fecha_retiro'] !== 'NULL') ? $data['fecha_retiro'] : null,
                        'created_at'       => $data['created_at'],
                        'updated_at'       => $data['updated_at'],
                    ]
                );
            }
        });

        fclose($handle);
    }
}
