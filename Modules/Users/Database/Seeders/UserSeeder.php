<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Users\Models\User;
use Modules\Users\Models\Role;

use SplFileObject;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::pluck('id', 'nombre')->toArray(); // ['Administrador' => 1, 'Rector' => 2, ...]

        // Verificamos que existan los roles necesarios
        if (!isset($roles['Rector']) || !isset($roles['Coordinador'])) {
            $this->command->error('Faltan los roles "Rector" y/o "Coordinador". Ejecuta primero RoleSeeder.');
            return;
        }
        
        $file = new SplFileObject(__DIR__.'/data/users.csv');
        $file->setFlags(SplFileObject::READ_CSV);

        $headers = array_map("trim", str_getcsv($file->fgets()));

        foreach ($file as $row) {
            if (count($row) < 2 || empty($row[0]) || $row[0] === $headers[0]) {
                continue;
            }
            $data = array_combine($headers, $row);

            DB::table('users')->insert([
                'id' => $data['id'],
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],                
                'userID' => $data['userID'],
                'email' => $data['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('inventApp123*'),
                'role_id' => $data['role_id'],
                'current_team_id' => null,
                'profile_photo_path' => null,
                'remember_token' => Str::random(10),
            ]);               
        }

        
        /*
        // Usuario 1: Adolfo León (Rector)
        User::updateOrCreate(
            ['userID' => '71379517'],
            [
                'nombres' => 'Adolfo León',
                'apellidos' => 'Ruiz Hernández',
                'email' => 'bhagam19@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('Asdf123*'),
                'role_id' => $roles['Rector'],
                'current_team_id' => null,
                'profile_photo_path' => null,
                'remember_token' => Str::random(10),
            ]
        );
        */
        // Usuario 2: Dorian Rodrigo (Coordinador)
        User::updateOrCreate(
            ['userID' => '71481707'],
            [
                'nombres' => 'Dorian Rodrigo',
                'apellidos' => 'Ruiz Hernández',
                'email' => 'dorianrodrigo@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('Asdf123*'),
                'role_id' => $roles['Coordinador'],
                'current_team_id' => null,
                'profile_photo_path' => null,
                'remember_token' => Str::random(10),
            ]
        );

        // Crear 10 usuarios aleatorios
        /*
        $roleIds = array_values($roles);

        for ($i = 1; $i <= 150; $i++) {
            User::updateOrCreate(
                ['userID' => 'USR' . str_pad($i, 3, '0', STR_PAD_LEFT)],
                [
                    'nombres' => fake()->firstName(),
                    'apellidos' => fake()->lastName(),
                    'email' => fake()->unique()->safeEmail(),
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role_id' => fake()->randomElement($roleIds),
                    'current_team_id' => null,
                    'profile_photo_path' => null,
                    'remember_token' => Str::random(10),
                ]
            );
        }
        */
    }
}
