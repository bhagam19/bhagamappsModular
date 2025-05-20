<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::pluck('id', 'nombre')->toArray(); // IDs con nombre como clave

        // Primero crear el usuario específico Adolfo León
        if (isset($roles['Administrador'])) {
            User::updateOrCreate(
                ['userID' => '71379517'], // condición única
                [
                    'nombres' => 'Adolfo León',
                    'apellidos' => 'Ruiz Hernández',
                    'email' => 'bhagam19@gmail.com', // cambia si quieres
                    'email_verified_at' => now(),
                    'password' => Hash::make('Asdf123*'),
                    'role_id' => $roles['Rector'],
                    'current_team_id' => null,
                    'profile_photo_path' => null,
                    'remember_token' => Str::random(10),
                ]
            );
        } else {
            $this->command->error('No existe el rol Administrador. Crea ese rol primero.');
        }
        if (isset($roles['Administrador'])) {
            User::updateOrCreate(
                ['userID' => '71481707'], // condición única
                [
                    'nombres' => 'Dorian Rodrigo',
                    'apellidos' => 'Ruiz Hernández',
                    'email' => 'dorianrodrigo@gmail.com', // cambia si quieres
                    'email_verified_at' => now(),
                    'password' => Hash::make('Asdf123*'),
                    'role_id' => $roles['Coordinador'],
                    'current_team_id' => null,
                    'profile_photo_path' => null,
                    'remember_token' => Str::random(10),
                ]
            );
        } else {
            $this->command->error('No existe el rol Administrador. Crea ese rol primero.');
        }

        // Luego crear los 10 usuarios aleatorios
        $roleIds = array_values($roles); // solo IDs para randomElement

        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'nombres' => fake()->firstName(),
                'apellidos' => fake()->lastName(),
                'userID' => 'USR' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'email' => fake()->unique()->safeEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role_id' => fake()->randomElement($roleIds),
                'current_team_id' => null,
                'profile_photo_path' => null,
                'remember_token' => Str::random(10),
            ]);
        }
    }
}
