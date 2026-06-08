<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $name = $this->faker->unique()->userName();

        return [
            'business_name' => $this->faker->company(),
            'owner_name'    => $this->faker->name(),
            'name'          => $name,
            'slug'          => Str::slug($name),
            'contact'       => $this->faker->phoneNumber(),
            'address'       => $this->faker->address(),
            'email'         => $this->faker->unique()->safeEmail(),
            'password'      => Hash::make('password'),
            'role'          => 'agent',
            'active'        => true,
            'agreement_status' => 'verified',
            'paid_crm'      => false,
        ];
    }
}
