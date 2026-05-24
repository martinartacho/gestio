<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusCourse;
use App\Models\CampusSeason;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    private function canPreview(): bool
    {
        $user = auth('web')->user();
        return $user && $user->hasPermissionTo('courses.edit');
    }

    public function index(Request $request)
    {
        $isPreview = $request->boolean('preview') && $this->canPreview();

        $seasons = $isPreview
            ? CampusSeason::orderByDesc('start_date')->get()
            : CampusSeason::whereIn('status', ['active', 'closed'])->orderByDesc('start_date')->get();

        $activeSeason = $seasons->firstWhere('status', 'active') ?? $seasons->first();

        $selectedSeason = $request->filled('season')
            ? $seasons->firstWhere('id', $request->integer('season'))
            : $activeSeason;
        $selectedSeason ??= $activeSeason;

        $courses = CampusCourse::with(['category', 'space', 'teachers'])
            ->withCount(['enrollments as active_enrollments_count' => fn($q) =>
                $q->whereIn('status', CampusCourse::ACTIVE_STATUSES)
            ])
            ->when(! $isPreview, fn($q) => $q->where('status', 'active')->where('is_public', true))
            ->when($selectedSeason, fn($q) => $q->where(function ($inner) use ($selectedSeason) {
                $inner->where('season_id', $selectedSeason->id)
                      ->orWhere('open_enrollment', true);
            }))
            ->orderBy('start_date')
            ->get();

        $enrollmentOpen = $selectedSeason
            ? ($selectedSeason->enrollmentIsOpen() && $selectedSeason->isActive())
            : false;

        // Matrícules de l'alumne autenticat per als cursos mostrats (una sola query)
        $student       = auth('student')->user();
        $myEnrollments = $student
            ? $student->enrollments()
                      ->whereIn('course_id', $courses->pluck('id'))
                      ->whereNotIn('status', ['cancelled', 'refunded'])
                      ->get()
                      ->keyBy('course_id')
            : collect();

        return view('campus.catalog.index', compact(
            'courses', 'seasons', 'activeSeason', 'selectedSeason',
            'isPreview', 'enrollmentOpen', 'myEnrollments'
        ));
    }

    public function show(Request $request, string $slug)
    {
        $isPreview = $request->boolean('preview') && $this->canPreview();

        $course = CampusCourse::with(['category', 'space', 'teachers', 'season'])
            ->withCount(['enrollments as active_enrollments_count' => fn($q) =>
                $q->whereIn('status', CampusCourse::ACTIVE_STATUSES)
            ])
            ->where('slug', $slug)
            ->when(! $isPreview, fn($q) => $q->where('is_public', true))
            ->firstOrFail();

        $student      = auth('student')->user();
        // Cancel·lada/retornada = pot tornar a inscriure's; no compten com a "ja inscrit"
        $myEnrollment = $student
            ? $student->enrollments()
                      ->where('course_id', $course->id)
                      ->whereNotIn('status', ['cancelled', 'refunded'])
                      ->first()
            : null;
        $alreadyEnrolled = $myEnrollment !== null;

        $season         = $course->season;
        $openEnrollment = $course->open_enrollment;

        $enrollmentOpen = $openEnrollment
            || ($season?->enrollmentIsOpen() && $season?->isActive());
        $seasonIsPast   = ! $openEnrollment && ($season?->isClosed() || $season?->isPast());
        $seasonIsFuture = ! $openEnrollment && ($season?->isDraft() || $season?->isFuture());

        return view('campus.catalog.show', compact(
            'course', 'myEnrollment', 'alreadyEnrolled',
            'enrollmentOpen', 'seasonIsPast', 'seasonIsFuture', 'isPreview'
        ));
    }
}
