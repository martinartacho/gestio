<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CampusCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => $this->faker->word() . ' ' . \Illuminate\Support\Str::random(6),
            'slug'        => null,
            'description' => $this->faker->sentence(),
            'color'       => $this->faker->randomElement(array_keys(\App\Models\CampusCategory::COLORS)),
            'is_active'   => true,
            'order'       => $this->faker->numberBetween(1, 20),
        ];
    }
}
