<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use SplFileObject;

class HistorialModificacionesBienesSeeder extends Seeder
{
    public function run(): void
    {
        $dataFile = __DIR__ . '/data/historial_modificaciones_bienes.csv';

        if (!file_exists($dataFile)) {
            $this->command->warn('historial_modificaciones_bienes.csv not found in data/ — skipping.');
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

            DB::table('historial_modificaciones_bienes')->insertOrIgnore([
                'id'             => $data['id'],
                'bien_id'        => $data['bien_id'],
                'tipo_objeto'    => $data['tipo_objeto'],
                'campo'          => $data['campo'],
                'valor_anterior' => $data['valor_anterior'] !== '' ? $data['valor_anterior'] : null,
                'valor_nuevo'    => $data['valor_nuevo'] !== '' ? $data['valor_nuevo'] : null,
                'dependencia_id' => $data['dependencia_id'],
                'estado'         => $data['estado'],
                'aprobado_por'   => $data['aprobado_por'] !== '' ? $data['aprobado_por'] : null,
                'created_at'     => $data['created_at'],
                'updated_at'     => $data['updated_at'],
            ]);
        }
    }
}
