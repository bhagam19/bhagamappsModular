<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use SplFileObject;

class CategoriasDeBienesSeeder extends Seeder
{
    public function run(): void
    {
               
        $file = new SplFileObject(__DIR__.'/data/categoriasDeBienes.csv');
        $file->setFlags(SplFileObject::READ_CSV);

        $headers = array_map("trim", str_getcsv($file->fgets()));

        foreach ($file as $row) {
            foreach ($file as $row) {

                if (count($row) < 2 || empty($row[0]) || $row[0] === $headers[0]) {
                    continue;
                }
                $data = array_combine($headers, $row);

                DB::table('categorias_de_bienes')->insert([
                    'id' => $data['codCategoria'],
                    'nom_categoria' => $data['nomCategoria'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }   
        }
    } 
}