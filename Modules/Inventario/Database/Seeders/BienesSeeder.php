<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use SplFileObject;

class BienesSeeder extends Seeder
{
    public function run(): void
    {
                
        $file = new SplFileObject(__DIR__.'/data/bienes.csv');
        $file->setFlags(SplFileObject::READ_CSV);

        $headers = array_map("trim", str_getcsv($file->fgets()));

        foreach ($file as $row) {
            foreach ($file as $row) {

                if (count($row) < 2 || empty($row[0]) || $row[0] === $headers[0]) {
                    continue;
                }
                $data = array_combine($headers, $row);

                DB::table('bienes')->insert([
                    'id' => $data['codBien'],
                    'nombre' => $data['nomBien'],
                    'serie' => $data['serieDelBien'],     // CORREGIDO
                    'origen' => $data['origenDelBien'],   // CORREGIDO
                    'fechaAdquisicion' => ($data['fechaAdquisicion'] === '0000-00-00' || empty($data['fechaAdquisicion'])) ? null : $data['fechaAdquisicion'],
                    'precio' => $data['precio'],
                    'cantidad' => $data['cantBien'],              // CORREGIDO
                    'categoria_id' => $data['codCategoria'],      // CORREGIDO
                    'dependencia_id' => $data['codDependencias'],// CORREGIDO
                    'almacenamiento_id' => $data['codAlmacenamiento'], // CORREGIDO
                    'estado_id' => $data['codEstado'],            // CORREGIDO
                    'mantenimiento_id' => $data['codMantenimiento'], // CORREGIDO
                    'observaciones' => $data['observaciones'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            }
        }
    }
}