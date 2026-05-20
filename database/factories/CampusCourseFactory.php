<?php

namespace Database\Factories;

use App\Models\CampusCategory;
use App\Models\CampusSeason;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampusCourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'        => 'CRS-' . strtoupper(substr(str_replace('-', '', \Illuminate\Support\Str::uuid()), 0, 6)),
            'title'       => $this->faker->sentence(4),
            'slug'        => null,
            'parent_id'   => null,
            'season_id'   => CampusSeason::factory(),
            'category_id' => CampusCategory::factory(),
            'space_id'    => null,
            'time_slot_id'=> null,
            'start_date'  => null,
            'end_date'    => null,
            'sessions'    => $this->faker->numberBetween(4, 20),
            'hours'       => $this->faker->numberBetween(8, 60),
            'format'      => $this->faker->randomElement(array_keys(\App\Models\CampusCourse::FORMATS)),
            'max_students'=> $this->faker->numberBetween(10, 30),
            'price'       => $this->faker->randomFloat(2, 50, 500),
            'status'      => 'draft',
            'is_public'   => false,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active', 'is_public' => true]);
    }

    public function edition(int $parentId): static
    {
        return $this->state(['parent_id' => $parentId]);
    }

    public function unlimited(): static
    {
        return $this->state(['max_students' => null]);
    }
}
