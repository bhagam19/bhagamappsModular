<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use SplFileObject;

class Permission_RoleSeeder extends Seeder
{
    public function run(): void
    {

        $file = new SplFileObject(__DIR__ . '/data/permission_role.csv');
        $file->setFlags(SplFileObject::READ_CSV);

        $headers = array_map("trim", str_getcsv($file->fgets()));

        foreach ($file as $row) {

            if (count($row) < 2 || empty($row[0]) || $row[0] === $headers[0]) {
                continue;
            }
            $data = array_combine($headers, $row);

            DB::table('permission_role')->insert([
                //'id' => $data['id'],
                'role_id' => $data['role_id'],
                'permission_id' => $data['permission_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
