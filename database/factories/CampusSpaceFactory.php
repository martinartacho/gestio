<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CampusSpaceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id'   => fn () => \App\Models\Tenant::where('slug', 'campus')->value('id'),
            'name'        => $this->faker->unique()->words(2, true),
            'code'        => strtoupper($this->faker->unique()->lexify('??-###')),
            'capacity'    => $this->faker->numberBetween(10, 100),
            'type'        => $this->faker->randomElement(array_keys(\App\Models\CampusSpace::TYPES)),
            'description' => null,
            'is_active'   => true,
        ];
    }
}
