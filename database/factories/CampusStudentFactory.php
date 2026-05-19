<?php

namespace Database\Factories;

use App\Models\CampusStudent;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampusStudentFactory extends Factory
{
    protected $model = CampusStudent::class;

    public function definition(): array
    {
        return [
            'first_name'   => $this->faker->firstName(),
            'last_name'    => $this->faker->lastName(),
            'email'        => $this->faker->unique()->safeEmail(),
            'password'     => 'password',
            'phone'        => $this->faker->phoneNumber(),
            'dni'          => null,
            'address'      => $this->faker->streetAddress(),
            'postal_code'  => $this->faker->postcode(),
            'city'         => $this->faker->city(),
            'data_consent' => true,
        ];
    }
}
