<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'name'    => fake()->company(),
            'email'   => fake()->companyEmail(),
            'phone'   => fake()->phoneNumber(),
            'address' => fake()->address(),
            'tax_id'  => fake()->numerify('###########'),
        ];
    }
}