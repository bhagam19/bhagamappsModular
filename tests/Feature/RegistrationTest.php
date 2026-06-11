<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_registration_screen_cannot_be_rendered_if_support_is_disabled(): void
    {
        if (Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    public function test_new_users_can_register(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        // CreateNewUser requiere role_id 5 (Docente) o 6 (Estudiante).
        DB::table('roles')->insert([
            ['id' => 5, 'nombre' => 'Docente',    'descripcion' => null, 'app_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'nombre' => 'Estudiante', 'descripcion' => null, 'app_id' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->post('/register', [
            'nombres'               => 'Juan',
            'apellidos'             => 'Pérez',
            'userID'                => '1234567890',
            'role_id'               => 6,
            'email'                 => 'juan.perez@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'terms'                 => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }
}
