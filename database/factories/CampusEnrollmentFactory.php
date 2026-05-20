<?php

namespace Database\Factories;

use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Models\CampusStudent;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampusEnrollmentFactory extends Factory
{
    protected $model = CampusEnrollment::class;

    public function definition(): array
    {
        return [
            'course_id'       => CampusCourse::factory(),
            'first_name'      => $this->faker->firstName(),
            'last_name'       => $this->faker->lastName(),
            'email'           => $this->faker->unique()->safeEmail(),
            'phone'           => $this->faker->phoneNumber(),
            'dni'             => null,
            'status'          => 'pending',
            'enrollment_date' => now()->toDateString(),
            'rgpd_accepted'   => true,
            'rgpd_accepted_at' => now(),
        ];
    }
}
