<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StudentStageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word() . ' Stage',
            'slug' => fake()->unique()->slug(1),
            'color' => fake()->hexColor(),
            'stage_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
