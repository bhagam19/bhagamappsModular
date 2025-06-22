<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use SplFileObject;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {

        $file = new SplFileObject(__DIR__ . '/data/permissions.csv');
        $file->setFlags(SplFileObject::READ_CSV);

        $headers = array_map("trim", str_getcsv($file->fgets()));

        foreach ($file as $row) {

            if (count($row) < 2 || empty($row[0]) || $row[0] === $headers[0]) {
                continue;
            }
            $data = array_combine($headers, $row);

            DB::table('permissions')->insert([
                //'id' => $data['id'],
                'nombre' => $data['nombre'],
                'slug' => $data['slug'],
                'descripcion' => $data['descripcion'],
                'categoria' => $data['categoria'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
