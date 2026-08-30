<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CampusSeasonFactory extends Factory
{
    public function definition(): array
    {
        $year = $this->faker->unique()->numberBetween(1900, 2900);
        $quad = $this->faker->randomElement([1, 2]);
        $name = ($quad === 1 ? 'Tardor' : 'Primavera') . " {$year}";

        return [
            'tenant_id'    => fn () => \App\Models\Tenant::where('slug', 'campus')->value('id'),
            'name'         => $name,
            'year'         => $year,
            'quadrimester' => $quad,
            'start_date'   => $quad === 1 ? "{$year}-09-01" : "{$year}-02-01",
            'end_date'     => $quad === 1 ? "{$year}-01-31" : "{$year}-06-30",
            'status'       => 'draft',
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function closed(): static
    {
        return $this->state(['status' => 'closed']);
    }

    public function q1(int $year = 2026): static
    {
        return $this->state([
            'year'         => $year,
            'quadrimester' => 1,
            'name'         => "Tardor {$year}",
        ]);
    }

    public function q2(int $year = 2026): static
    {
        return $this->state([
            'year'         => $year,
            'quadrimester' => 2,
            'name'         => "Primavera {$year}",
        ]);
    }
}
