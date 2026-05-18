<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CampusTimeSlotFactory extends Factory
{
    public function definition(): array
    {
        $start = $this->faker->randomElement(['09:00', '10:00', '16:00', '17:00', '18:00']);
        [$h, $m] = explode(':', $start);
        $end = sprintf('%02d:%s', (int)$h + 2, $m);

        return [
            'day_of_week' => $this->faker->numberBetween(1, 5),
            'code'        => strtoupper($this->faker->unique()->lexify('??')),
            'start_time'  => $start,
            'end_time'    => $end,
            'description' => null,
            'is_active'   => true,
        ];
    }
}
