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
        $days = [1 => 'Dilluns', 2 => 'Dimarts', 3 => 'Dimecres', 4 => 'Dijous', 5 => 'Divendres'];
        $day  = $this->faker->numberBetween(1, 5);

        return [
            'tenant_id'   => fn () => \App\Models\Tenant::where('slug', 'campus')->value('id'),
            'day_of_week' => $day,
            'code'        => strtoupper($this->faker->unique()->lexify('??')),
            'start_time'  => $start,
            'end_time'    => $end,
            // NOT NULL a la BD; el factory el generava buit i mai havia
            // petat perquè els usos existents sempre el sobreescrivien.
            'description' => "{$days[$day]} {$start}-{$end}",
            'is_active'   => true,
        ];
    }
}
