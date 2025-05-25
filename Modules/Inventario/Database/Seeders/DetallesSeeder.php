<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use SplFileObject;

class DetallesSeeder extends Seeder
{
    public function run(): void
    {

        $path = __DIR__.'/data/bienes.csv';

        if (!file_exists($path) || !is_readable($path)) {
            echo "Archivo CSV no encontrado o no legible: $path\n";
            return;
        }

        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV);

        $header = null;

        foreach ($file as $row) {
            // Saltar filas vacías o mal formateadas
            if ($row === [null] || count($row) < 2) continue;

            if (!$header) {
                $header = array_map('trim', $row);
                continue;
            }

            $item = array_combine($header, array_map('trim', $row));

            if (!empty($item['detalleDelBien'])) {
                $partes = explode(';', $item['detalleDelBien']);

                DB::table('detalles')->insert([
                    'bien_id'      => $item['codBien'],
                    'car_especial' => $partes[0] ?? null,
                    'tamano'       => $partes[1] ?? null,
                    'material'     => $partes[2] ?? null,
                    'color'        => $partes[3] ?? null,
                    'marca'        => $partes[4] ?? null,
                    'otra'         => $partes[5] ?? null,
                ]);
            }
        }

        /*        
        $file = new SplFileObject(__DIR__.'/data/detallesDeBienes.csv');
        $file->setFlags(SplFileObject::READ_CSV);

        $headers = array_map("trim", str_getcsv($file->fgets()));

        foreach ($file as $row) {
            foreach ($file as $row) {

                if (count($row) < 2 || empty($row[0]) || $row[0] === $headers[0]) {
                    continue;
                }
                $data = array_combine($headers, $row);

                DB::table('detalles')->insert([
                    'car_especial' => $data['carEsp'],
                    'tamano' => $data['tamano'],
                    'material' => $data['material'],
                    'color' => $data['color'],
                    'marca' => $data['marca'],
                    'otra' => $data['otra'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        */
    }
}
