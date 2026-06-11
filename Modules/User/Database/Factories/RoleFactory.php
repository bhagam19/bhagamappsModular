<?php

namespace Modules\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\User\Entities\Role;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'nombre'     => $this->faker->unique()->word(),
            'descripcion' => null,
            'app_id'     => null,
        ];
    }
}
