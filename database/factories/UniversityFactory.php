<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UniversityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company() . ' University',
            'short_name' => strtoupper(fake()->lexify('??')),
            'country' => fake()->country(),
            'city' => fake()->city(),
            'website' => fake()->url(),
            'contact_email' => fake()->safeEmail(),
            'description' => fake()->paragraph(),
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
        ];
    }
}
