<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use SplFileObject;

class OrigenesSeeder extends Seeder
{
    public function run(): void
    {
        $dataFile = __DIR__ . '/data/origenes.csv';

        if (!file_exists($dataFile)) {
            $this->command->warn('origenes.csv not found in data/ — skipping.');
            return;
        }

        $file = new SplFileObject($dataFile);
        $file->setFlags(SplFileObject::READ_CSV);

        $headers = array_map('trim', str_getcsv($file->fgets()));

        foreach ($file as $row) {
            if (count($row) < 2 || empty($row[0]) || $row[0] === $headers[0]) {
                continue;
            }
            $data = array_combine($headers, $row);

            // updateOrInsert porque la migración populate_origenes ya pudo haber insertado estos registros
            DB::table('origenes')->updateOrInsert(
                ['id' => $data['id']],
                [
                    'nombre'      => $data['nombre'],
                    'descripcion' => $data['descripcion'] !== '' ? $data['descripcion'] : null,
                    'activo'      => (int) $data['activo'],
                    'created_at'  => $data['created_at'],
                    'updated_at'  => $data['updated_at'],
                ]
            );
        }
    }
}
