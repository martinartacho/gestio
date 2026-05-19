<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CampusTeacherFactory extends Factory
{
    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName  = $this->faker->lastName();

        return [
            'user_id'        => null,
            'code'           => strtoupper(
                substr(Str::ascii($firstName), 0, 3) .
                substr(Str::ascii($lastName),  0, 3)
            ),
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'email'          => $this->faker->unique()->safeEmail(),
            'phone'          => $this->faker->phoneNumber(),
            'specialization' => $this->faker->words(3, true),
            'bio'            => null,
            'status'         => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }
}
