<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EnquiryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'subject' => fake()->sentence(),
            'message' => fake()->paragraph(),
            'type' => fake()->randomElement(['general', 'admission', 'visa', 'course']),
            'is_read' => false,
            'is_replied' => false,
            'status' => 'pending',
        ];
    }
}
