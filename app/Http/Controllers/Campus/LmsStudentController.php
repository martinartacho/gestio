<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusCourse;
use App\Models\CampusStudent;
use App\Models\LmsCourseCertificate;
use App\Models\LmsLesson;
use App\Models\LmsLessonProgress;
use App\Models\LmsLessonResponse;
use App\Services\LmsGradingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LmsStudentController extends Controller
{
    // ─── Índex: llista de lliçons d'un curs ──────────────────────────────────

    public function index(string $slug): View
    {
        $student = auth('student')->user();
        $course  = $this->getCourseForStudent($student, $slug);

        $lessons      = $course->lessons()->published()->get();
        $completedIds = LmsLessonProgress::where('student_id', $student->id)
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->pluck('lesson_id')
            ->toArray();

        $total    = $lessons->count();
        $done     = count($completedIds);
        $progress = $total > 0 ? (int) round($done / $total * 100) : 0;

        $certificate = LmsCourseCertificate::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->first();

        return view('campus.lms.student.index', compact(
            'course', 'lessons', 'completedIds', 'total', 'done', 'progress', 'certificate'
        ));
    }

    // ─── Detall d'una lliçó ───────────────────────────────────────────────────

    public function show(string $slug, LmsLesson $lesson): View
    {
        $student = auth('student')->user();
        $course  = $this->getCourseForStudent($student, $slug);

        abort_if($lesson->course_id !== $course->id || ! $lesson->isPublished(), 404);

        $lessons      = $course->lessons()->published()->ordered()->get();
        $index        = $lessons->search(fn ($l) => $l->id === $lesson->id);
        $prevLesson   = $index > 0 ? $lessons[$index - 1] : null;
        $nextLesson   = isset($lessons[$index + 1]) ? $lessons[$index + 1] : null;
        $totalLessons = $lessons->count();

        $isCompleted = LmsLessonProgress::where('lesson_id', $lesson->id)
            ->where('student_id', $student->id)
            ->exists();

        // Respostes de l'alumne per a aquesta lliçó, indexades per question_index
        $studentResponses = LmsLessonResponse::where('lesson_id', $lesson->id)
            ->where('student_id', $student->id)
            ->get()
            ->keyBy('question_index');

        return view('campus.lms.lesson', compact(
            'course', 'lesson', 'isCompleted',
            'prevLesson', 'nextLesson', 'totalLessons',
            'studentResponses'
        ));
    }

    // ─── Marcar lliçó com a completada ────────────────────────────────────────

    public function complete(LmsLesson $lesson): RedirectResponse
    {
        $student = auth('student')->user();

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
            ['verb' => 'completed', 'completed_at' => now()]
        );

        // Auto-emetre certificat si totes les lliçons publicades estan completes
        $total = LmsLesson::where('course_id', $lesson->course_id)
            ->published()
            ->count();

        $done = LmsLessonProgress::where('student_id', $student->id)
            ->whereHas('lesson', fn ($q) => $q
                ->where('course_id', $lesson->course_id)
                ->published()
            )
            ->count();

        if ($done >= $total && $total > 0) {
            LmsCourseCertificate::firstOrCreate(
                ['course_id' => $lesson->course_id, 'student_id' => $student->id],
                ['verb' => 'passed', 'issued_at' => now()]
            );
        }

        return back()->with('success', '✓ Lliçó marcada com a completada.');
    }

    // ─── Desar resposta a una pregunta ────────────────────────────────────────

    public function saveResponse(
        Request $request,
        LmsLesson $lesson,
        int $questionIndex,
        LmsGradingService $grader
    ): RedirectResponse {
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

        // Obtenir la definició de la pregunta
        $questions = $lesson->questions ?? [];
        $question  = collect($questions)->firstWhere('index', $questionIndex);
        abort_if($question === null, 404);

        $type = $question['type'] ?? 'open_text';

        // Validació adaptada al tipus
        $rules = match ($type) {
            'yes_no'      => ['answer' => 'required|boolean'],
            'choice_one'  => ['answer' => 'required|string|max:500'],
            'choice_many' => ['answer' => 'required|array', 'answer.*' => 'string|max:500'],
            default       => ['answer' => 'nullable|string|max:5000'],
        };

        $validated = $request->validate($rules);
        $raw       = $validated['answer'];

        // Construir camps de resposta
        $responseData = [
            'question_type' => $type,
            'submitted_at'  => now(),
        ];

        match ($type) {
            'yes_no'      => $responseData['response_bool']    = (bool) $raw,
            'choice_one'  => $responseData['response_choices'] = [(string) $raw],
            'choice_many' => $responseData['response_choices'] = (array) $raw,
            default       => $responseData['response_text']    = $raw,
        };

        // Avaluació automàtica
        $grading = $grader->grade($question, $raw);
        $responseData['score']       = $grading['score'];
        $responseData['auto_graded'] = $grading['auto_graded'];

        LmsLessonResponse::updateOrCreate(
            ['lesson_id' => $lesson->id, 'student_id' => $student->id, 'question_index' => $questionIndex],
            $responseData
        );

        $lessonUrl = route('campus.lms.lesson', [
            'slug'   => $lesson->course->slug,
            'lesson' => $lesson->id,
        ]) . '#q-' . $questionIndex;

        return redirect($lessonUrl)->with('success', '✓ Resposta desada.');
    }

    // ─── Certificat d'un curs ─────────────────────────────────────────────────

    public function certificate(string $slug): View
    {
        $student     = auth('student')->user();
        $course      = $this->getCourseForStudent($student, $slug);
        $certificate = LmsCourseCertificate::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        return view('campus.lms.certificate', compact('course', 'student', 'certificate'));
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
