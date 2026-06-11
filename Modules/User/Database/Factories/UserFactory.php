<?php

namespace Modules\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'nombres'                    => $this->faker->firstName(),
            'apellidos'                  => $this->faker->lastName(),
            'userID'                     => $this->faker->unique()->numerify('##########'),
            'email'                      => $this->faker->unique()->safeEmail(),
            'email_verified_at'          => now(),
            'password'                   => Hash::make('password'),
            'two_factor_secret'          => null,
            'two_factor_recovery_codes'  => null,
            'remember_token'             => Str::random(10),
            'profile_photo_path'         => null,
            'current_team_id'            => null,
            'role_id'                    => Role::factory(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }

    public function withPersonalTeam(callable $callback = null): static
    {
        // Teams feature is not active; return neutral state for test compatibility.
        return $this->state([]);
    }
}
