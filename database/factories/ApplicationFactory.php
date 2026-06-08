<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => \App\Models\Student::factory(),
            'university_id' => \App\Models\University::factory(),
            'course_id' => \App\Models\Course::factory(),
            'agent_id' => \App\Models\User::factory(),
            'application_status_id' => \App\Models\ApplicationStatus::factory(),
            'application_number' => strtoupper(fake()->bothify('APP-####-????')),
        ];
    }
}
