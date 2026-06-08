<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TestimonialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'location' => fake()->city() . ', ' . fake()->country(),
            'content' => fake()->paragraph(),
            'rating' => fake()->numberBetween(1, 5),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
