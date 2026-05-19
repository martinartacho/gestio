<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusCourse;
use App\Models\CampusSeason;

class CatalogController extends Controller
{
    public function index()
    {
        $seasons = CampusSeason::orderByDesc('start_date')->get();

        $activeSeason = $seasons->firstWhere('is_active', true) ?? $seasons->first();

        $courses = CampusCourse::with(['category', 'space', 'teachers'])
            ->where('status', 'active')
            ->where('is_public', true)
            ->when($activeSeason, fn ($q) => $q->where('season_id', $activeSeason->id))
            ->orderBy('start_date')
            ->get();

        return view('campus.catalog.index', compact('courses', 'seasons', 'activeSeason'));
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

        return view('campus.catalog.show', compact('course', 'alreadyEnrolled'));
    }
}
