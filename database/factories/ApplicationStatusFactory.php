<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationStatusFactory extends Factory
{
    public function definition(): array
    {
        $colors = ['#3b82f6', '#22c55e', '#ef4444', '#facc15', '#8b5cf6', '#f97316', '#0ea5e9', '#16a34a', '#b91c1c'];

        return [
            'name' => fake()->unique()->words(2, true),
            'bg_color' => fake()->randomElement($colors),
            'text_color' => '#ffffff',
            'sort_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
