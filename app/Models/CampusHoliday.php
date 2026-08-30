<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class CampusHoliday extends Model
{
    use BelongsToTenant;

    protected $table = 'campus_holidays';

    protected $fillable = [
        'tenant_id',
        'date', 'date_end', 'label', 'type', 'recurring_yearly',
    ];

    protected $casts = [
        'date'             => 'date',
        'date_end'         => 'date',
        'recurring_yearly' => 'boolean',
    ];

    public const TYPES = [
        'festiu'    => 'Festiu',
        'no_lectiu' => 'No lectiu',
    ];

    /**
     * Comprova si una data cau dins d'algun festiu/període no lectiu,
     * tenint en compte rangs de dies i recurrència anual (amb suport
     * per a rangs que travessen l'any, com les vacances de Nadal).
     */
    public static function fallsOn(CarbonInterface $date): bool
    {
        return static::all()->contains(fn(self $holiday) => $holiday->coversDate($date));
    }

    public function coversDate(CarbonInterface $date): bool
    {
        $start = $this->date;
        $end   = $this->date_end ?? $this->date;

        if (! $this->recurring_yearly) {
            return $date->toDateString() >= $start->toDateString()
                && $date->toDateString() <= $end->toDateString();
        }

        $target = $date->format('md');
        $s      = $start->format('md');
        $e      = $end->format('md');

        return $s <= $e
            ? ($target >= $s && $target <= $e)
            : ($target >= $s || $target <= $e); // el rang travessa l'any (ex. 23/12–7/1)
    }
}
