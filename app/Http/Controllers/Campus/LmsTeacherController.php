<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusCourse;
use App\Models\CampusTeacher;
use App\Models\LmsLesson;
use Illuminate\View\View;

class LmsTeacherController extends Controller
{
    // ─── Índex: totes les lliçons del curs (draft + published) ───────────────

    public function index(string $slug): View
    {
        $teacher = auth('teacher')->user();
        $course  = $this->getCourseForTeacher($teacher, $slug);
        $lessons = $course->lessons()->ordered()->get();

        return view('campus.lms.teacher.index', compact('course', 'lessons'));
    }

    // ─── Detall d'una lliçó (sense tracking) ──────────────────────────────────

    public function show(string $slug, LmsLesson $lesson): View
    {
        $teacher = auth('teacher')->user();
        $course  = $this->getCourseForTeacher($teacher, $slug);

        abort_if($lesson->course_id !== $course->id, 404);

        $lessons      = $course->lessons()->ordered()->get();
        $index        = $lessons->search(fn ($l) => $l->id === $lesson->id);
        $prevLesson   = $index > 0 ? $lessons[$index - 1] : null;
        $nextLesson   = isset($lessons[$index + 1]) ? $lessons[$index + 1] : null;
        $totalLessons = $lessons->count();
        $isCompleted  = false;
        $isPreview    = true; // el professor sempre veu en mode previsualització

        return view('campus.lms.lesson', compact(
            'course', 'lesson', 'isCompleted', 'isPreview',
            'prevLesson', 'nextLesson', 'totalLessons'
        ));
    }

    // ─── Helper privat ────────────────────────────────────────────────────────

    private function getCourseForTeacher(CampusTeacher $teacher, string $slug): CampusCourse
    {
        return CampusCourse::where('slug', $slug)
            ->whereHas('teachers', fn ($q) => $q->where('campus_teachers.id', $teacher->id))
            ->firstOrFail();
    }
}
