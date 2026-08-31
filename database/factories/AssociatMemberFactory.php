<?php

namespace Database\Factories;

use App\Models\AssociatMember;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssociatMemberFactory extends Factory
{
    protected $model = AssociatMember::class;

    public function configure(): static
    {
        // Un soci pot pertànyer a més d'una institució (relació N:M) — per
        // defecte als tests, se l'afegeix a "campus" com abans amb tenant_id.
        return $this->afterCreating(function (AssociatMember $member) {
            $campusId = \App\Models\Tenant::where('slug', 'campus')->value('id');
            if ($campusId && ! $member->tenants()->where('tenants.id', $campusId)->exists()) {
                $member->tenants()->attach($campusId);
            }
        });
    }

    public function definition(): array
    {
        return [
            'member_number' => (string) $this->faker->unique()->numberBetween(1000, 9999),
            'first_name'    => $this->faker->firstName(),
            'last_name'     => $this->faker->lastName(),
            'email'         => $this->faker->unique()->safeEmail(),
            'password'      => 'password',
            'phone'         => $this->faker->phoneNumber(),
            'address'       => $this->faker->streetAddress(),
            'postal_code'   => $this->faker->postcode(),
            'city'          => $this->faker->city(),
            'status'        => 'active',
            'joined_at'     => $this->faker->dateTimeBetween('-5 years', 'now'),
            'data_consent'  => true,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => 'cancelled', 'cancelled_at' => now()]);
    }
}
