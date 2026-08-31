<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CampusTeacherFactory extends Factory
{
    public function configure(): static
    {
        // Un professor pot pertànyer a més d'una institució (relació N:M) —
        // per defecte als tests, se l'afegeix a "campus" com abans amb tenant_id.
        return $this->afterCreating(function (\App\Models\CampusTeacher $teacher) {
            $campusId = \App\Models\Tenant::where('slug', 'campus')->value('id');
            if ($campusId && ! $teacher->tenants()->where('tenants.id', $campusId)->exists()) {
                $teacher->tenants()->attach($campusId);
            }
        });
    }

    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName  = $this->faker->lastName();

        return [
            'user_id'              => null,
            'code'                 => strtoupper(
                substr(Str::ascii($firstName), 0, 3) .
                substr(Str::ascii($lastName),  0, 3)
            ),
            'first_name'           => $firstName,
            'last_name'            => $lastName,
            'email'                => $this->faker->unique()->safeEmail(),
            'phone'                => $this->faker->phoneNumber(),
            'city'                 => $this->faker->city(),
            'postal_code'          => $this->faker->postcode(),
            'specialization'       => $this->faker->words(3, true),
            'bio'                  => null,
            'status'               => 'active',
            'needs_payment'        => true,
            'data_consent'         => false,
            'fiscal_responsibility' => false,
            'ceded_confirmation'   => false,
            'payment_status'       => 'pending',
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }
}
