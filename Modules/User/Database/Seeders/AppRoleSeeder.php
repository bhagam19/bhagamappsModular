<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use SplFileObject;

class AppRoleSeeder extends Seeder
{
    public function run(): void
    {
        $dataFile = __DIR__ . '/data/app_role.csv';

        if (!file_exists($dataFile)) {
            $this->command->warn('app_role.csv not found in data/ — skipping.');
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

            // Verificar que el app y el rol existan antes de insertar (AdminSistemaSeeder
            // puede no haber corrido aún, por lo que su app_id no existiría todavía)
            $appExists  = DB::table('apps')->where('id', $data['app_id'])->exists();
            $roleExists = DB::table('roles')->where('id', $data['role_id'])->exists();

            if (!$appExists || !$roleExists) {
                continue;
            }

            DB::table('app_role')->insertOrIgnore([
                'id'         => $data['id'],
                'app_id'     => $data['app_id'],
                'role_id'    => $data['role_id'],
                'created_at' => $data['created_at'],
                'updated_at' => $data['updated_at'],
            ]);
        }
    }
}
