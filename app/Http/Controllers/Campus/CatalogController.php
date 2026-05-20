<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusCourse;
use App\Models\CampusSeason;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $seasons = CampusSeason::whereIn('status', ['active', 'closed'])
            ->orderByDesc('start_date')
            ->get();

        $activeSeason = $seasons->firstWhere('status', 'active') ?? $seasons->first();

        $selectedSeason = $request->filled('season')
            ? $seasons->firstWhere('id', $request->integer('season'))
            : $activeSeason;
        $selectedSeason ??= $activeSeason;

        $courses = CampusCourse::with(['category', 'space', 'teachers'])
            ->where('status', 'active')
            ->where('is_public', true)
            ->when($selectedSeason, fn($q) => $q->where('season_id', $selectedSeason->id))
            ->orderBy('start_date')
            ->get();

        return view('campus.catalog.index', compact('courses', 'seasons', 'activeSeason', 'selectedSeason'));
    }

    public function show(string $slug)
    {
        $course = CampusCourse::with(['category', 'space', 'teachers', 'season'])
            ->where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        $student = auth('student')->user();

        $alreadyEnrolled = $student
            ? $student->courses()->where('campus_courses.id', $course->id)->exists()
            : false;

        $season          = $course->season;
        $enrollmentOpen  = $season?->enrollmentIsOpen() && $season?->isActive();
        $seasonIsPast    = $season?->isClosed() || $season?->isPast();
        $seasonIsFuture  = $season?->isDraft() || $season?->isFuture();

        return view('campus.catalog.show', compact(
            'course', 'alreadyEnrolled', 'enrollmentOpen', 'seasonIsPast', 'seasonIsFuture'
        ));
    }
}
