<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'university_id' => \App\Models\University::factory(),
            'title' => fake()->jobTitle() . ' ' . fake()->randomElement(['BSc', 'MSc', 'BA', 'MA', 'PhD']),
            'course_code' => strtoupper(fake()->bothify('??-####')),
            'course_type' => fake()->randomElement(['UG', 'PG', 'DIPLOMA']),
            'fee' => fake()->randomFloat(2, 5000, 50000),
            'duration' => fake()->randomElement(['1 year', '2 years', '3 years', '4 years']),
            'intakes' => fake()->randomElement(['January, September', 'September', 'January, May, September']),
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
        ];
    }
}
