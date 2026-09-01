<?php

namespace Database\Factories;

use App\Models\CampusStudent;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampusStudentFactory extends Factory
{
    protected $model = CampusStudent::class;

    public function configure(): static
    {
        // Un alumne pot pertànyer a més d'una institució (relació N:M) — per
        // defecte als tests, se l'afegeix a "campus" com abans amb tenant_id.
        return $this->afterCreating(function (CampusStudent $student) {
            $campusId = \App\Models\Tenant::where('slug', 'campus')->value('id');
            if ($campusId && ! $student->tenants()->where('tenants.id', $campusId)->exists()) {
                $student->tenants()->attach($campusId);
            }
        });
    }

    public function definition(): array
    {
        return [
            'first_name'        => $this->faker->firstName(),
            'last_name'         => $this->faker->lastName(),
            'email'             => $this->faker->unique()->safeEmail(),
            'password'          => 'password',
            'phone'             => $this->faker->phoneNumber(),
            'dni'               => null,
            'address'           => $this->faker->streetAddress(),
            'postal_code'       => $this->faker->postcode(),
            'city'              => $this->faker->city(),
            'data_consent'      => true,
            'email_verified_at' => now(), // verificat per defecte als tests
        ];
    }

    /** Alumne sense email verificat. */
    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }

    /** Alumne suspès. */
    public function suspended(string $reason = 'Suspensió de test'): static
    {
        return $this->state([
            'suspended_at'      => now(),
            'suspension_reason' => $reason,
        ]);
    }
}
