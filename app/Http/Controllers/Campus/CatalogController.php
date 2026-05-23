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
            ->when(! $isPreview, fn($q) => $q->where('status', 'active')->where('is_public', true))
            ->when($selectedSeason, fn($q) => $q->where(function ($inner) use ($selectedSeason) {
                // Mostra cursos de la temporada seleccionada + cursos amb inscripció sempre oberta
                $inner->where('season_id', $selectedSeason->id)
                      ->orWhere('open_enrollment', true);
            }))
            ->orderBy('start_date')
            ->get();

        return view('campus.catalog.index', compact('courses', 'seasons', 'activeSeason', 'selectedSeason', 'isPreview'));
    }

    public function show(Request $request, string $slug)
    {
        $isPreview = $request->boolean('preview') && $this->canPreview();

        $course = CampusCourse::with(['category', 'space', 'teachers', 'season'])
            ->where('slug', $slug)
            ->when(! $isPreview, fn($q) => $q->where('is_public', true))
            ->firstOrFail();

        $student = auth('student')->user();

        $alreadyEnrolled = $student
            ? $student->enrollments()->where('course_id', $course->id)->exists()
            : false;

        $season         = $course->season;
        $openEnrollment = $course->open_enrollment;

        // Cursos amb open_enrollment bypassen totes les restriccions de temporada i dates
        $enrollmentOpen = $openEnrollment
            || ($season?->enrollmentIsOpen() && $season?->isActive());
        $seasonIsPast   = ! $openEnrollment && ($season?->isClosed() || $season?->isPast());
        $seasonIsFuture = ! $openEnrollment && ($season?->isDraft() || $season?->isFuture());

        return view('campus.catalog.show', compact(
            'course', 'alreadyEnrolled', 'enrollmentOpen', 'seasonIsPast', 'seasonIsFuture', 'isPreview'
        ));
    }
}
