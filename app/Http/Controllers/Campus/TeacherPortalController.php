<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\Password;

class TeacherPortalController extends Controller
{
    public function courses()
    {
        $teacher = auth('teacher')->user();

        $courses = $teacher->courses()
            ->with('season', 'category', 'space', 'timeSlot')
            ->withCount(['enrollments as students_count' => fn($q) => $q->whereIn('status', ['paid', 'pending'])])
            ->orderByDesc('start_date')
            ->get()
            ->each(fn($c) => $c->sessions_past = $this->computeSessionsPast($c));

        return view('campus.teacher.portal.courses', compact('courses'));
    }

    private function computeSessionsPast(CampusCourse $course): ?int
    {
        if (! $course->sessions || ! $course->start_date) {
            return null;
        }

        $today = now()->startOfDay();

        if ($today < $course->start_date) {
            return 0;
        }

        // Use actual session dates from calendar_notes when available
        if ($course->calendar_notes) {
            $dates = $this->parseSessionDates($course->calendar_notes, $course->start_date);
            if ($dates->isNotEmpty()) {
                return $dates->filter(fn($d) => $d->lte($today))->count();
            }
        }

        // Fallback: weekly estimate capped at end_date
        $end = $course->end_date ?? $course->start_date->copy()->addWeeks($course->sessions - 1);

        if ($today >= $end) {
            return $course->sessions;
        }

        $weeksDone = (int) ($course->start_date->diffInDays($today) / 7) + 1;

        return min($weeksDone, $course->sessions);
    }

    private function parseSessionDates(string $notes, Carbon $startDate): Collection
    {
        $year      = $startDate->year;
        $prevMonth = 0;

        return collect(array_filter(array_map('trim', explode(',', $notes))))
            ->map(function (string $part) use (&$year, &$prevMonth) {
                // Accepts "d/m" or "d/m/yyyy" or "d/m/yy"
                if (! preg_match('/^(\d{1,2})\/(\d{1,2})(?:\/(\d{2,4}))?$/', $part, $m)) {
                    return null;
                }

                $day   = (int) $m[1];
                $month = (int) $m[2];
                $y     = isset($m[3]) ? (strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3]) : null;

                if ($y === null) {
                    // If month rolled back (e.g. notes cross a year boundary), advance year
                    if ($prevMonth > 0 && $month < $prevMonth) {
                        $year++;
                    }
                    $y = $year;
                }

                $prevMonth = $month;

                try {
                    return Carbon::createFromDate($y, $month, $day)->startOfDay();
                } catch (\Exception) {
                    return null;
                }
            })
            ->filter();
    }

    public function course(string $slug)
    {
        $teacher = auth('teacher')->user();

        $course = $teacher->courses()
            ->with('season', 'category', 'space', 'timeSlot')
            ->where('campus_courses.slug', $slug)
            ->firstOrFail();

        $enrollments = CampusEnrollment::with('student')
            ->where('course_id', $course->id)
            ->whereIn('status', ['paid', 'pending'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('campus.teacher.portal.course', compact('course', 'enrollments'));
    }

    public function editProfile()
    {
        $teacher = auth('teacher')->user();
        return view('campus.teacher.portal.profile', compact('teacher'));
    }

    public function updateProfile(Request $request)
    {
        $teacher = auth('teacher')->user();

        $data = $request->validate([
            'phone'    => ['nullable', 'string', 'max:20'],
            'bio'      => ['nullable', 'string', 'max:1000'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $teacher->update($data);

        return back()->with('success', 'Perfil actualitzat correctament.');
    }

    public function liquidations()
    {
        $teacher = auth('teacher')->user();

        $payments = $teacher->teacherPayments()
            ->with('course.season')
            ->orderByDesc('created_at')
            ->get();

        return view('campus.teacher.portal.liquidations', compact('payments'));
    }
}
