<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusCourse;
use App\Models\CampusStudent;
use App\Models\LmsLesson;
use App\Models\LmsLessonProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LmsStudentController extends Controller
{
    // ─── Índex: llista de lliçons d'un curs ──────────────────────────────────

    public function index(string $slug): View
    {
        $student = auth('student')->user();
        $course  = $this->getCourseForStudent($student, $slug);

        $lessons     = $course->lessons()->published()->get();
        $completedIds = LmsLessonProgress::where('student_id', $student->id)
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->pluck('lesson_id')
            ->toArray();

        $total    = $lessons->count();
        $done     = count($completedIds);
        $progress = $total > 0 ? (int) round($done / $total * 100) : 0;

        return view('campus.lms.student.index', compact(
            'course', 'lessons', 'completedIds', 'total', 'done', 'progress'
        ));
    }

    // ─── Detall d'una lliçó ───────────────────────────────────────────────────

    public function show(string $slug, LmsLesson $lesson): View
    {
        $student = auth('student')->user();
        $course  = $this->getCourseForStudent($student, $slug);

        abort_if($lesson->course_id !== $course->id || ! $lesson->isPublished(), 404);

        $lessons     = $course->lessons()->published()->ordered()->get();
        $index       = $lessons->search(fn ($l) => $l->id === $lesson->id);
        $prevLesson  = $index > 0 ? $lessons[$index - 1] : null;
        $nextLesson  = isset($lessons[$index + 1]) ? $lessons[$index + 1] : null;
        $totalLessons = $lessons->count();

        $isCompleted = LmsLessonProgress::where('lesson_id', $lesson->id)
            ->where('student_id', $student->id)
            ->exists();

        return view('campus.lms.lesson', compact(
            'course', 'lesson', 'isCompleted',
            'prevLesson', 'nextLesson', 'totalLessons'
        ));
    }

    // ─── Marcar lliçó com a completada ────────────────────────────────────────

    public function complete(LmsLesson $lesson): RedirectResponse
    {
        $student = auth('student')->user();

        // Verificar matrícula activa
        abort_unless(
            $student->enrollments()
                ->whereIn('status', ['paid', 'confirmed'])
                ->where('course_id', $lesson->course_id)
                ->exists(),
            403
        );

        abort_if(! $lesson->isPublished(), 404);

        LmsLessonProgress::firstOrCreate(
            ['lesson_id' => $lesson->id, 'student_id' => $student->id],
            ['completed_at' => now()]
        );

        return back()->with('success', '✓ Lliçó marcada com a completada.');
    }

    // ─── Helper privat ────────────────────────────────────────────────────────

    private function getCourseForStudent(CampusStudent $student, string $slug): CampusCourse
    {
        return CampusCourse::where('slug', $slug)
            ->whereHas('enrollments', fn ($q) => $q
                ->where('student_id', $student->id)
                ->whereIn('status', ['paid', 'confirmed'])
            )
            ->firstOrFail();
    }
}
