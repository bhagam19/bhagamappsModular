<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MantenimientosProgramadosSeeder extends Seeder
{
    public function run(): void
    {
        $path = __DIR__ . '/data/mantenimientos_programados.csv';

        if (!file_exists($path)) {
            $this->command->warn('  ⚠ mantenimientos_programados.csv no encontrado en data/. Omitiendo.');
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

                DB::table('mantenimientos_programados')->updateOrInsert(
                    ['id' => (int) $data['id']],
                    [
                        'bien_id'          => (int) $data['bien_id'],
                        'user_id'          => ($data['user_id'] !== '' && $data['user_id'] !== 'NULL') ? (int) $data['user_id'] : null,
                        'tipo'             => $data['tipo'],
                        'titulo'           => $data['titulo'],
                        'descripcion'      => ($data['descripcion'] !== '') ? $data['descripcion'] : null,
                        'fecha_programada' => $data['fecha_programada'],
                        'fecha_realizada'  => ($data['fecha_realizada'] !== '' && $data['fecha_realizada'] !== 'NULL') ? $data['fecha_realizada'] : null,
                        'estado'           => $data['estado'],
                        'created_at'       => $data['created_at'],
                        'updated_at'       => $data['updated_at'],
                    ]
                );
            }
        });

        fclose($handle);
    }
}
