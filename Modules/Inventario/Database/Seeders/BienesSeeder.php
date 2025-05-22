<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use SplFileObject;

class BienesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('bienes')->delete();
        
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
                    'nom_bien' => $data['nomBien'],
                    'detalle_del_bien' => $data['detalleDelBien'], // CORREGIDO
                    'serie_del_bien' => $data['serieDelBien'],     // CORREGIDO
                    'origen_del_bien' => $data['origenDelBien'],   // CORREGIDO
                    'fecha_adquisicion' => ($data['fechaAdquisicion'] === '0000-00-00' || empty($data['fechaAdquisicion'])) ? null : $data['fechaAdquisicion'],
                    'precio' => $data['precio'],
                    'cant_bien' => $data['cantBien'],              // CORREGIDO
                    'cod_categoria' => $data['codCategoria'],      // CORREGIDO
                    'cod_dependencias' => $data['codDependencias'],// CORREGIDO
                    'usuario_id' => $data['usuarioID'],
                    'cod_almacenamiento' => $data['codAlmacenamiento'], // CORREGIDO
                    'cod_estado' => $data['codEstado'],            // CORREGIDO
                    'cod_mantenimiento' => $data['codMantenimiento'], // CORREGIDO
                    'observaciones' => $data['observaciones'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            }
        }
    }
}