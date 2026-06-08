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

        $file = new SplFileObject(__DIR__ . '/data/detalles.csv');
        $file->setFlags(SplFileObject::READ_CSV);

        $headers = array_map("trim", str_getcsv($file->fgets()));

        foreach ($file as $row) {

            if (count($row) < 2 || empty($row[0]) || $row[0] === $headers[0]) {
                continue;
            }
            $data = array_combine($headers, $row);

            DB::table('detalles')->insert([
                'bien_id' => $data['bien_id'],
                'car_especial' => $this->limpiarValor($data['car_especial']),
                'tamano' => $this->limpiarValor($data['tamano']),
                'material' => $this->limpiarValor($data['material']),
                'color' => $this->limpiarValor($data['color']),
                'marca' => $this->limpiarValor($data['marca']),
                'otra' => $this->limpiarValor($data['otra']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function limpiarValor($valor)
    {
        $valor = trim($valor); // elimina espacios al inicio y final
        $valorNormalizado = strtolower($valor);

        return (in_array($valorNormalizado, ['n/a', 'null', 'na', ''], true)) ? '' : $valor;
    }
}
