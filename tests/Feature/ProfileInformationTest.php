<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm;
use Livewire\Livewire;
use Modules\User\Entities\User;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_profile_information_is_available(): void
    {
        $this->actingAs($user = User::factory()->create());

        $component = Livewire::test(UpdateProfileInformationForm::class);

        $this->assertEquals($user->nombres, $component->state['nombres']);
        $this->assertEquals($user->apellidos, $component->state['apellidos']);
        $this->assertEquals($user->email, $component->state['email']);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $this->actingAs($user = User::factory()->create());

        Livewire::test(UpdateProfileInformationForm::class)
            ->set('state', [
                'nombres'   => 'Juan',
                'apellidos' => 'Pérez',
                'userID'    => $user->userID,
                'email'     => $user->email,
            ])
            ->call('updateProfileInformation');

        $this->assertEquals('Juan', $user->fresh()->nombres);
        $this->assertEquals('Pérez', $user->fresh()->apellidos);
    }
}
