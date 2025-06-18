<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use SplFileObject;

class BienesSeeder extends Seeder
{
    public function run(): void
    {

        $file = new SplFileObject(__DIR__ . '/data/bienes.csv');
        $file->setFlags(SplFileObject::READ_CSV);

        $headers = array_map("trim", str_getcsv($file->fgets()));

        foreach ($file as $row) {
            foreach ($file as $row) {

                if (count($row) < 2 || empty($row[0]) || $row[0] === $headers[0]) {
                    continue;
                }
                $data = array_combine($headers, $row);

                DB::table('bienes')->insert([
                    'id' => $data['id'],
                    'nombre' => $data['nombre'],
                    'serie' => $data['serie'],
                    'origen' => $data['origen'],
                    'fechaAdquisicion' => (in_array($data['fechaAdquisicion'], ['0000-00-00', '', 'NULL'], true) || is_null($data['fechaAdquisicion']))
                        ? '2003-02-20'
                        : $data['fechaAdquisicion'],
                    'precio' => $data['precio'],
                    'cantidad' => $data['cantidad'],
                    'categoria_id' => $data['categoria_id'],
                    'dependencia_id' => $data['dependencia_id'],
                    'almacenamiento_id' => $data['almacenamiento_id'],
                    'estado_id' => $data['estado_id'],
                    'mantenimiento_id' => $data['mantenimiento_id'],
                    'observaciones' => $data['observaciones'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
