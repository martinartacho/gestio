<?php

namespace App\Http\Controllers;

use App\Models\CampusCourse;
use App\Models\CampusHoliday;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarEventsController extends Controller
{
    private const COLOR_MAP = [
        'red'    => '#ef4444',
        'blue'   => '#3b82f6',
        'purple' => '#a855f7',
        'indigo' => '#6366f1',
        'pink'   => '#ec4899',
        'green'  => '#22c55e',
        'orange' => '#f97316',
        'yellow' => '#eab308',
        'teal'   => '#14b8a6',
        'cyan'   => '#06b6d4',
    ];

    public function index(Request $request): JsonResponse
    {
        abort_unless(Auth::user()?->hasAnyRole(['super-admin', 'admin', 'manager']), 403);

        $seasonId = $request->integer('season');

        $query = CampusCourse::with(['category', 'teachers', 'space', 'timeSlot'])
            ->whereNotNull('start_date')
            ->whereNotNull('end_date');

        if ($seasonId) {
            $query->where('season_id', $seasonId);
        }

        $events = [];

        foreach ($query->get() as $course) {
            $color = $this->resolveColor($course->category?->color);

            // Events de sessió individuals
            $sessions = $this->parseCalendarNotes($course->calendar_notes, $course->start_date);

            if (empty($sessions)) {
                // Si no hi ha calendar_notes, un event al start_date
                $sessions = [$course->start_date->copy()];
            }

            $timeSlot    = $course->timeSlot;
            $teacherCode = $course->teachers
                ->sortByDesc(fn($t) => $t->pivot->role === 'main')
                ->first()?->code;

            foreach ($sessions as $date) {
                $start = $timeSlot
                    ? $date->format('Y-m-d') . 'T' . substr($timeSlot->start_time, 0, 5)
                    : $date->toDateString();
                $end = $timeSlot
                    ? $date->format('Y-m-d') . 'T' . substr($timeSlot->end_time, 0, 5)
                    : $date->toDateString();

                $events[] = [
                    'id'    => 'session-' . $course->id . '-' . $date->format('Ymd'),
                    'title' => implode(' / ', array_filter([
                        $course->code ?: $course->title,
                        $teacherCode,
                    ])),
                    'start' => $start,
                    'end'   => $end,
                    'color' => $color,
                    'extendedProps' => [
                        'courseId' => $course->id,
                        'type'     => 'session',
                        'teachers' => $course->teachers
                            ->map(fn($t) => $t->first_name . ' ' . $t->last_name)
                            ->join(', '),
                        'space'    => $course->space?->name,
                        'format'   => $course->format,
                        'sessions' => $course->sessions,
                        'price'    => $course->price,
                    ],
                ];
            }
        }

        foreach ($this->holidayEvents($request) as $event) {
            $events[] = $event;
        }

        return response()->json($events);
    }

    /**
     * Esdeveniments de fons pels festius/dies no lectius, dins el rang visible
     * del calendari. Els recurrents es repeteixen per a cada any del rang.
     */
    private function holidayEvents(Request $request): array
    {
        $rangeStart = $request->date('start');
        $rangeEnd   = $request->date('end');
        $years      = $rangeStart && $rangeEnd
            ? range($rangeStart->year, $rangeEnd->year)
            : [now()->year];

        $events = [];

        foreach (CampusHoliday::all() as $holiday) {
            $end = $holiday->date_end ?? $holiday->date;

            $ranges = $holiday->recurring_yearly
                ? collect($years)->map(function ($year) use ($holiday, $end) {
                    try {
                        $start = Carbon::createFromDate($year, $holiday->date->month, $holiday->date->day);
                        // Si el rang travessa l'any (ex. 23/12–7/1), el final cau l'any següent.
                        $endYear = $end->format('md') < $holiday->date->format('md') ? $year + 1 : $year;
                        $rangeEnd = Carbon::createFromDate($endYear, $end->month, $end->day);
                        return [$start, $rangeEnd];
                    } catch (\Throwable) {
                        return null;
                    }
                })->filter()
                : collect([[$holiday->date, $end]]);

            foreach ($ranges as [$start, $rangeEnd]) {
                $events[] = [
                    'id'            => 'holiday-' . $holiday->id . '-' . $start->format('Ymd'),
                    'title'         => $holiday->label,
                    'start'         => $start->toDateString(),
                    'end'           => $rangeEnd->copy()->addDay()->toDateString(), // FullCalendar: end exclusiu
                    'display'       => 'background',
                    'color'         => $holiday->type === 'festiu' ? '#B23A3A' : '#B8862B',
                    'extendedProps' => ['type' => 'holiday', 'holidayType' => $holiday->type],
                ];
            }
        }

        return $events;
    }

    /**
     * Parseja "16/2, 23/2, 2/3" → array de Carbon.
     * Gestiona el canvi d'any (p.ex. sessions de gen/feb en un curs que comença set).
     */
    private function parseCalendarNotes(?string $notes, Carbon $referenceDate): array
    {
        if (blank($notes)) return [];

        $dates = [];
        $year  = $referenceDate->year;

        foreach (preg_split('/[\s,;]+/', trim($notes)) as $token) {
            $token = trim($token);
            if (empty($token)) continue;

            if (! preg_match('#^(\d{1,2})/(\d{1,2})(?:/(\d{4}))?$#', $token, $m)) continue;

            $day   = (int) $m[1];
            $month = (int) $m[2];
            $y     = isset($m[3]) ? (int) $m[3] : $year;

            // Si el mes parsejat és molt anterior al mes de referència → any +1
            if (! isset($m[3]) && $month < $referenceDate->month - 6) {
                $y = $year + 1;
            }

            try {
                $dates[] = Carbon::createFromDate($y, $month, $day);
            } catch (\Throwable) {
                // Data invàlida: ignorar silenciosament
            }
        }

        return $dates;
    }

    private function resolveColor(?string $name): string
    {
        return self::COLOR_MAP[$name] ?? '#6b7280';
    }
}
