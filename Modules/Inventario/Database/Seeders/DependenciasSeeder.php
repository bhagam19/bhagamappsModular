<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use SplFileObject;

class DependenciasSeeder extends Seeder
{
    public function run(): void
    {

        $file = new SplFileObject(__DIR__ . '/data/dependencias.csv');
        $file->setFlags(SplFileObject::READ_CSV);

        $headers = array_map("trim", str_getcsv($file->fgets()));

        foreach ($file as $row) {

            if (count($row) < 2 || empty($row[0]) || $row[0] === $headers[0]) {
                continue;
            }
            $data = array_combine($headers, $row);

            DB::table('dependencias')->insert([
                //'id' => $data['id'],
                'nombre' => $data['nombre'],
                'ubicacion_id' => $data['ubicacion_id'],
                'user_id' => $data['user_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
