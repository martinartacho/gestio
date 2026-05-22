<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusCourse;
use App\Models\CampusTeacher;
use App\Models\LmsLesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    // ─── Detall d'una lliçó (mode previsualització) ───────────────────────────

    public function show(string $slug, LmsLesson $lesson): View
    {
        $teacher = auth('teacher')->user();
        $course  = $this->getCourseForTeacher($teacher, $slug);

        abort_if($lesson->course_id !== $course->id, 404);

        $lessons          = $course->lessons()->ordered()->get();
        $index            = $lessons->search(fn ($l) => $l->id === $lesson->id);
        $prevLesson       = $index > 0 ? $lessons[$index - 1] : null;
        $nextLesson       = isset($lessons[$index + 1]) ? $lessons[$index + 1] : null;
        $totalLessons     = $lessons->count();
        $isCompleted      = false;
        $isPreview        = true;
        $studentResponses = collect();

        return view('campus.lms.lesson', compact(
            'course', 'lesson', 'isCompleted', 'isPreview',
            'prevLesson', 'nextLesson', 'totalLessons', 'studentResponses'
        ));
    }

    // ─── Formulari nova sessió ─────────────────────────────────────────────────

    public function create(string $slug): View
    {
        $teacher = auth('teacher')->user();
        $course  = $this->getCourseForTeacher($teacher, $slug);

        $nextNumber = $course->lessons()->max('session_number') + 1;
        $lesson     = new LmsLesson(['session_number' => $nextNumber, 'sort_order' => $nextNumber]);

        return view('campus.lms.teacher.lesson-form', compact('course', 'lesson'));
    }

    // ─── Desar nova sessió ────────────────────────────────────────────────────

    public function store(Request $request, string $slug): RedirectResponse
    {
        $teacher = auth('teacher')->user();
        $course  = $this->getCourseForTeacher($teacher, $slug);

        $data = $this->validateLesson($request);
        $data['course_id'] = $course->id;

        $lesson = LmsLesson::create($data);

        return redirect()
            ->route('teacher.lms.lesson.edit', [$course->slug, $lesson->id])
            ->with('success', '✓ Sessió creada correctament.');
    }

    // ─── Formulari editar sessió ───────────────────────────────────────────────

    public function edit(string $slug, LmsLesson $lesson): View
    {
        $teacher = auth('teacher')->user();
        $course  = $this->getCourseForTeacher($teacher, $slug);

        abort_if($lesson->course_id !== $course->id, 404);

        return view('campus.lms.teacher.lesson-form', compact('course', 'lesson'));
    }

    // ─── Actualitzar sessió ────────────────────────────────────────────────────

    public function update(Request $request, string $slug, LmsLesson $lesson): RedirectResponse
    {
        $teacher = auth('teacher')->user();
        $course  = $this->getCourseForTeacher($teacher, $slug);

        abort_if($lesson->course_id !== $course->id, 404);

        $lesson->update($this->validateLesson($request));

        return back()->with('success', '✓ Sessió desada correctament.');
    }

    // ─── Eliminar sessió ──────────────────────────────────────────────────────

    public function destroy(string $slug, LmsLesson $lesson): RedirectResponse
    {
        $teacher = auth('teacher')->user();
        $course  = $this->getCourseForTeacher($teacher, $slug);

        abort_if($lesson->course_id !== $course->id, 404);

        $lesson->delete();

        return redirect()
            ->route('teacher.lms.course', $course->slug)
            ->with('success', '✓ Sessió eliminada.');
    }

    // ─── Validació compartida ─────────────────────────────────────────────────

    private function validateLesson(Request $request): array
    {
        $base = $request->validate([
            'session_number' => 'required|integer|min:1',
            'title'          => 'required|string|max:255',
            'subtitle'       => 'nullable|string|max:255',
            'duration'       => 'nullable|string|max:50',
            'status'         => 'required|in:draft,published',
            'sort_order'     => 'nullable|integer',
            'quote_text'     => 'nullable|string|max:1000',
            'quote_author'   => 'nullable|string|max:255',
            'quote_work'     => 'nullable|string|max:255',
            'intro_text'     => 'nullable|string',
            'topic_text'     => 'nullable|string',
        ]);

        // Reflexió: array de preguntes (input: reflection_questions[] de textarea)
        $reflRaw = $request->input('reflection_questions', []);
        $base['reflection_questions'] = array_values(
            array_filter(
                array_map(fn ($q) => is_string($q) && trim($q) !== '' ? ['question' => trim($q)] : null, $reflRaw),
                fn ($q) => $q !== null
            )
        );

        // Exercici
        $base['exercise'] = [
            'title'     => $request->input('exercise_title', ''),
            'duration'  => $request->input('exercise_duration', ''),
            'statement' => $request->input('exercise_statement', ''),
            'examples'  => array_values(array_filter(
                array_map('trim', $request->input('exercise_examples', [])),
                fn ($e) => $e !== ''
            )),
            'tips' => array_values(array_filter(
                array_map('trim', $request->input('exercise_tips', [])),
                fn ($t) => $t !== ''
            )),
            'demo_first_person' => $request->input('exercise_demo_first') ?: null,
            'demo_third_person' => $request->input('exercise_demo_third') ?: null,
        ];

        // Preguntes interactives (JSON enviat com a camp ocult)
        $questionsJson = $request->input('questions_json', '[]');
        $decoded = json_decode($questionsJson, true);
        $base['questions'] = is_array($decoded) ? $decoded : [];

        return $base;
    }

    // ─── Helper privat ────────────────────────────────────────────────────────

    private function getCourseForTeacher(CampusTeacher $teacher, string $slug): CampusCourse
    {
        return CampusCourse::where('slug', $slug)
            ->whereHas('teachers', fn ($q) => $q->where('campus_teachers.id', $teacher->id))
            ->firstOrFail();
    }
}
