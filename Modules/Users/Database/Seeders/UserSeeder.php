<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

        $file = new SplFileObject(__DIR__ . '/data/users.csv');
        $file->setFlags(SplFileObject::READ_CSV);

        $headers = array_map("trim", str_getcsv($file->fgets()));

        foreach ($file as $row) {
            if (count($row) < 2 || empty($row[0]) || $row[0] === $headers[0]) {
                continue;
            }
            $data = array_combine($headers, $row);

            // Iniciales de todos los nombres
            $inicialNombres = collect(explode(' ', trim($data['nombres'])))
                ->filter() // elimina posibles espacios vacíos
                ->map(fn($nombre) => Str::substr($nombre, 0, 1))
                ->join('');

            // Iniciales de todos los apellidos
            $inicialApellidos = collect(explode(' ', trim($data['apellidos'])))
                ->filter()
                ->map(fn($apellido) => Str::substr($apellido, 0, 1))
                ->join('');

            // Últimos 4 del documento
            $ultimos4 = substr(preg_replace('/\D/', '', $data['userID']), -4);

            // Construir contraseña: ej. "alrh1234@IEE"
            $clave = strtolower($inicialNombres . $inicialApellidos) . $ultimos4 . '@IEE';

            DB::table('users')->insert([
                'id' => $data['id'],
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'userID' => $data['userID'],
                'email' => $data['email'],
                'email_verified_at' => now(),
                'password' => Hash::make($clave),
                'role_id' => $data['role_id'],
                'current_team_id' => null,
                'profile_photo_path' => null,
                'remember_token' => Str::random(10),
            ]);
        }
    }
}
