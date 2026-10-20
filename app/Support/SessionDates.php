<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\CampusCourse;
use Carbon\Carbon;

/**
 * Dates de les sessions d'un curs ⇄ text de `campus_courses.calendar_notes` («5/10, 12/10, 4/1»).
 * El text és el format que ja llegeix CampusCourse::parseSessionDates(); aquí només es converteix de i cap a una llista de dates.
 */
final class SessionDates
{
    /** @return list<string> dates Y-m-d ordenades i sense repetits */
    public static function parse(?string $notes, ?Carbon $start): array
    {
        if ($notes === null || trim($notes) === '') {
            return [];
        }

        $dates = (new CampusCourse)->parseSessionDates($notes, $start ?? now())
            ->map(fn (Carbon $d): string => $d->format('Y-m-d'))
            ->unique()->sort()->values()->all();

        return $dates;
    }

    /**
     * @param  list<string>  $dates  Y-m-d
     * @return string text compatible amb el parser: «d/m» i només amb any quan el parser no el deduiria sol
     */
    public static function serialize(array $dates, ?Carbon $start): string
    {
        $dates = array_values(array_unique(array_filter($dates)));
        sort($dates);

        if ($dates === []) {
            return '';
        }

        $year = ($start ?? Carbon::parse($dates[0]))->year;
        $prevMonth = 0;
        $parts = [];

        foreach ($dates as $iso) {
            $d = Carbon::parse($iso);

            $expected = $year + ($prevMonth > 0 && $d->month < $prevMonth ? 1 : 0);

            if ($d->year === $expected) {
                $year = $expected;
                $parts[] = $d->day.'/'.$d->month;
            } else {
                $parts[] = $d->day.'/'.$d->month.'/'.$d->year;
            }

            $prevMonth = $d->month;
        }

        return implode(', ', $parts);
    }
}
