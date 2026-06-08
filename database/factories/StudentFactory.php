<?php

namespace Database\Factories;

use App\Models\StudentStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agent_id' => \App\Models\User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->phoneNumber(),
            'gender' => fake()->randomElement(['Male', 'Female']),
            'dob' => fake()->date('Y-m-d', '2005-01-01'),
            'nationality' => fake()->country(),
            'passport_number' => strtoupper(fake()->bothify('??#######')),
            'passport_expiry' => fake()->dateTimeBetween('+1 year', '+10 years'),
            'marital_status' => 'Single',
            'qualification' => fake()->randomElement(['High School', 'Bachelor', 'Master']),
            'passed_year' => fake()->numberBetween(2018, 2024),
            'preferred_country' => fake()->country(),
            'source' => fake()->randomElement(['Facebook', 'Website', 'Referral', 'WhatsApp']),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function ($student) {
            if (!$student->current_stage_id || !StudentStage::where('id', $student->current_stage_id)->exists()) {
                $stage = StudentStage::first() ?? StudentStage::factory()->create();
                $student->current_stage_id = $stage->id;
            }
        });
    }
}
