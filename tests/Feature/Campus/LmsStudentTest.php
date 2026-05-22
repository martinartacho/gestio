<?php

namespace Tests\Feature\Campus;

use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Models\CampusStudent;
use App\Models\LmsCourseCertificate;
use App\Models\LmsLesson;
use App\Models\LmsLessonProgress;
use App\Models\LmsLessonResponse;
use App\Settings\SettingStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LmsStudentTest extends TestCase
{
    use RefreshDatabase;

    private CampusCourse $course;
    private CampusStudent $student;
    private CampusEnrollment $enrollment;
    private LmsLesson $lesson;

    protected function setUp(): void
    {
        parent::setUp();

        // Activar LMS
        Cache::flush();
        $this->app->forgetInstance(SettingStore::class);
        app(SettingStore::class)->set('lms_enabled', true);
        $this->app->forgetInstance(SettingStore::class);
        Cache::flush();
        app(SettingStore::class)->set('lms_enabled', true);

        // Curs
        $this->course = CampusCourse::factory()->create([
            'status'    => 'active',
            'is_public' => true,
            'slug'      => 'test-lms-course',
        ]);

        // Alumne
        $this->student = CampusStudent::factory()->create();

        // Matrícula pagada
        $this->enrollment = CampusEnrollment::factory()->create([
            'student_id' => $this->student->id,
            'course_id'  => $this->course->id,
            'status'     => 'paid',
        ]);

        // Lliçó publicada
        $this->lesson = LmsLesson::factory()->create([
            'course_id'      => $this->course->id,
            'session_number' => 1,
            'status'         => 'published',
            'sort_order'     => 1,
        ]);
    }

    // ─── Accés a l'índex ─────────────────────────────────────────────────────

    public function test_guest_cannot_access_lms_course_index(): void
    {
        $response = $this->get(route('campus.lms.course', $this->course->slug));
        $response->assertRedirect();
    }

    public function test_student_without_enrollment_cannot_access_lms_course(): void
    {
        $otherStudent = CampusStudent::factory()->create();
        $this->actingAs($otherStudent, 'student');

        $response = $this->get(route('campus.lms.course', $this->course->slug));
        $response->assertStatus(404);
    }

    public function test_enrolled_student_can_see_lesson_index(): void
    {
        $this->actingAs($this->student, 'student');
        $response = $this->get(route('campus.lms.course', $this->course->slug));

        $response->assertOk();
        $response->assertSee($this->course->title);
        $response->assertSee($this->lesson->title);
    }

    // ─── Accés a una lliçó ───────────────────────────────────────────────────

    public function test_enrolled_student_can_view_published_lesson(): void
    {
        $this->actingAs($this->student, 'student');
        $response = $this->get(route('campus.lms.lesson', [
            $this->course->slug, $this->lesson->id,
        ]));

        $response->assertOk();
        $response->assertSee($this->lesson->title);
    }

    public function test_student_cannot_see_draft_lesson(): void
    {
        $draftLesson = LmsLesson::factory()->create([
            'course_id'      => $this->course->id,
            'session_number' => 2,
            'status'         => 'draft',
        ]);

        $this->actingAs($this->student, 'student');
        $response = $this->get(route('campus.lms.lesson', [
            $this->course->slug, $draftLesson->id,
        ]));

        $response->assertStatus(404);
    }

    public function test_student_cannot_view_lesson_of_another_course(): void
    {
        $otherCourse = CampusCourse::factory()->create(['slug' => 'other-course']);
        $otherLesson = LmsLesson::factory()->create([
            'course_id' => $otherCourse->id, 'status' => 'published',
        ]);

        $this->actingAs($this->student, 'student');
        $response = $this->get(route('campus.lms.lesson', [
            $this->course->slug, $otherLesson->id,
        ]));

        $response->assertStatus(404);
    }

    // ─── Marcar com a completada ──────────────────────────────────────────────

    public function test_student_can_complete_lesson(): void
    {
        $this->actingAs($this->student, 'student');

        $response = $this->post(route('campus.lms.lesson.complete', $this->lesson->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('lms_lesson_progress', [
            'lesson_id'  => $this->lesson->id,
            'student_id' => $this->student->id,
        ]);
    }

    public function test_completing_lesson_twice_does_not_create_duplicate(): void
    {
        $this->actingAs($this->student, 'student');

        $this->post(route('campus.lms.lesson.complete', $this->lesson->id));
        $this->post(route('campus.lms.lesson.complete', $this->lesson->id));

        $this->assertDatabaseCount('lms_lesson_progress', 1);
    }

    public function test_student_without_enrollment_cannot_complete_lesson(): void
    {
        $otherStudent = CampusStudent::factory()->create();
        $this->actingAs($otherStudent, 'student');

        $response = $this->post(route('campus.lms.lesson.complete', $this->lesson->id));
        $response->assertStatus(403);
    }

    // ─── Progrés ──────────────────────────────────────────────────────────────

    public function test_progress_percentage_shown_on_index(): void
    {
        // Crear 2a lliçó
        LmsLesson::factory()->create([
            'course_id'      => $this->course->id,
            'session_number' => 2,
            'status'         => 'published',
            'sort_order'     => 2,
        ]);

        // Completar la 1a
        LmsLessonProgress::create([
            'lesson_id'    => $this->lesson->id,
            'student_id'   => $this->student->id,
            'completed_at' => now(),
        ]);

        $this->actingAs($this->student, 'student');
        $response = $this->get(route('campus.lms.course', $this->course->slug));

        $response->assertOk();
        $response->assertSee('1 de 2'); // 1 de 2 lliçons completades
    }

    // ─── Feature flag ─────────────────────────────────────────────────────────

    public function test_lms_disabled_returns_404_on_course_index(): void
    {
        Cache::flush();
        $this->app->forgetInstance(SettingStore::class);
        app(SettingStore::class)->set('lms_enabled', false);
        $this->app->forgetInstance(SettingStore::class);
        Cache::flush();
        app(SettingStore::class)->set('lms_enabled', false);

        $this->actingAs($this->student, 'student');
        $response = $this->get(route('campus.lms.course', $this->course->slug));
        $response->assertStatus(404);
    }

    // ─── Model helpers ────────────────────────────────────────────────────────

    public function test_is_completed_by_returns_false_when_no_progress(): void
    {
        $this->assertFalse($this->lesson->isCompletedBy($this->student));
    }

    public function test_is_completed_by_returns_true_when_progress_exists(): void
    {
        LmsLessonProgress::create([
            'lesson_id'    => $this->lesson->id,
            'student_id'   => $this->student->id,
            'completed_at' => now(),
        ]);

        $this->assertTrue($this->lesson->isCompletedBy($this->student));
    }

    public function test_scope_published_filters_drafts(): void
    {
        LmsLesson::factory()->create([
            'course_id' => $this->course->id, 'session_number' => 99, 'status' => 'draft',
        ]);

        $published = LmsLesson::published()->where('course_id', $this->course->id)->get();

        $this->assertCount(1, $published);
        $this->assertEquals('published', $published->first()->status);
    }

    // ─── Respostes — open_text ────────────────────────────────────────────────

    public function test_student_can_save_open_text_response(): void
    {
        $lesson = LmsLesson::factory()->withQuestions()->create([
            'course_id' => $this->course->id, 'session_number' => 10, 'status' => 'published',
        ]);

        $this->actingAs($this->student, 'student');
        $response = $this->post(route('campus.lms.lesson.response.save', [
            'lesson' => $lesson->id, 'questionIndex' => 0,
        ]), ['answer' => 'La meva resposta oberta aquí.']);

        $response->assertRedirect();
        $this->assertDatabaseHas('lms_lesson_responses', [
            'lesson_id'     => $lesson->id,
            'student_id'    => $this->student->id,
            'question_index'=> 0,
            'question_type' => 'open_text',
            'response_text' => 'La meva resposta oberta aquí.',
        ]);
    }

    public function test_open_text_response_not_auto_graded(): void
    {
        $lesson = LmsLesson::factory()->withQuestions()->create([
            'course_id' => $this->course->id, 'session_number' => 11, 'status' => 'published',
        ]);

        $this->actingAs($this->student, 'student');
        $this->post(route('campus.lms.lesson.response.save', [
            'lesson' => $lesson->id, 'questionIndex' => 0,
        ]), ['answer' => 'Resposta.']);

        $resp = LmsLessonResponse::where('lesson_id', $lesson->id)
            ->where('student_id', $this->student->id)
            ->where('question_index', 0)
            ->first();

        $this->assertNull($resp->score);
        $this->assertFalse((bool) $resp->auto_graded);
    }

    public function test_saving_response_twice_updates_not_duplicates(): void
    {
        $lesson = LmsLesson::factory()->withQuestions()->create([
            'course_id' => $this->course->id, 'session_number' => 12, 'status' => 'published',
        ]);

        $this->actingAs($this->student, 'student');
        $this->post(route('campus.lms.lesson.response.save', ['lesson' => $lesson->id, 'questionIndex' => 0]), ['answer' => 'Primera.']);
        $this->post(route('campus.lms.lesson.response.save', ['lesson' => $lesson->id, 'questionIndex' => 0]), ['answer' => 'Actualitzada.']);

        $this->assertDatabaseCount('lms_lesson_responses', 1);
        $this->assertDatabaseHas('lms_lesson_responses', ['response_text' => 'Actualitzada.']);
    }

    // ─── Respostes — choice_one (avaluable) ──────────────────────────────────

    public function test_student_can_save_choice_one_response(): void
    {
        $lesson = LmsLesson::factory()->withQuestions()->create([
            'course_id' => $this->course->id, 'session_number' => 13, 'status' => 'published',
        ]);

        $this->actingAs($this->student, 'student');
        $this->post(route('campus.lms.lesson.response.save', ['lesson' => $lesson->id, 'questionIndex' => 2]), ['answer' => 'A']);

        $this->assertDatabaseHas('lms_lesson_responses', [
            'lesson_id'      => $lesson->id,
            'student_id'     => $this->student->id,
            'question_index' => 2,
            'question_type'  => 'choice_one',
        ]);
    }

    public function test_correct_choice_gets_full_score(): void
    {
        $lesson = LmsLesson::factory()->withQuestions()->create([
            'course_id' => $this->course->id, 'session_number' => 14, 'status' => 'published',
        ]);

        $this->actingAs($this->student, 'student');
        $this->post(route('campus.lms.lesson.response.save', ['lesson' => $lesson->id, 'questionIndex' => 2]), ['answer' => 'A']); // 'A' és correct_answer, points=2

        $resp = LmsLessonResponse::where('lesson_id', $lesson->id)->where('question_index', 2)->first();
        $this->assertTrue((bool) $resp->auto_graded);
        $this->assertEquals(2.00, (float) $resp->score);
    }

    public function test_incorrect_choice_gets_zero_score(): void
    {
        $lesson = LmsLesson::factory()->withQuestions()->create([
            'course_id' => $this->course->id, 'session_number' => 15, 'status' => 'published',
        ]);

        $this->actingAs($this->student, 'student');
        $this->post(route('campus.lms.lesson.response.save', ['lesson' => $lesson->id, 'questionIndex' => 2]), ['answer' => 'B']); // 'B' és incorrecte

        $resp = LmsLessonResponse::where('lesson_id', $lesson->id)->where('question_index', 2)->first();
        $this->assertTrue((bool) $resp->auto_graded);
        $this->assertEquals(0.00, (float) $resp->score);
    }

    // ─── Respostes — yes_no (avaluable) ──────────────────────────────────────

    public function test_yes_no_correct_answer_scores_points(): void
    {
        $lesson = LmsLesson::factory()->withQuestions()->create([
            'course_id' => $this->course->id, 'session_number' => 16, 'status' => 'published',
        ]);

        $this->actingAs($this->student, 'student');
        $this->post(route('campus.lms.lesson.response.save', ['lesson' => $lesson->id, 'questionIndex' => 1]), ['answer' => '1']); // correct_answer=true, points=1

        $resp = LmsLessonResponse::where('lesson_id', $lesson->id)->where('question_index', 1)->first();
        $this->assertEquals(1.00, (float) $resp->score);
        $this->assertTrue((bool) $resp->auto_graded);
    }

    public function test_yes_no_wrong_answer_scores_zero(): void
    {
        $lesson = LmsLesson::factory()->withQuestions()->create([
            'course_id' => $this->course->id, 'session_number' => 17, 'status' => 'published',
        ]);

        $this->actingAs($this->student, 'student');
        $this->post(route('campus.lms.lesson.response.save', ['lesson' => $lesson->id, 'questionIndex' => 1]), ['answer' => '0']); // correct_answer=true, però enviem false

        $resp = LmsLessonResponse::where('lesson_id', $lesson->id)->where('question_index', 1)->first();
        $this->assertEquals(0.00, (float) $resp->score);
    }

    // ─── Respostes — autorització ─────────────────────────────────────────────

    public function test_unenrolled_student_cannot_save_response(): void
    {
        $lesson = LmsLesson::factory()->withQuestions()->create([
            'course_id' => $this->course->id, 'session_number' => 18, 'status' => 'published',
        ]);
        $otherStudent = CampusStudent::factory()->create();

        $this->actingAs($otherStudent, 'student');
        $response = $this->post(route('campus.lms.lesson.response.save', ['lesson' => $lesson->id, 'questionIndex' => 0]), ['answer' => 'test']);

        $response->assertStatus(403);
    }

    public function test_invalid_question_index_returns_404(): void
    {
        $lesson = LmsLesson::factory()->withQuestions()->create([
            'course_id' => $this->course->id, 'session_number' => 19, 'status' => 'published',
        ]);

        $this->actingAs($this->student, 'student');
        $response = $this->post(route('campus.lms.lesson.response.save', ['lesson' => $lesson->id, 'questionIndex' => 99]), ['answer' => 'test']);

        $response->assertStatus(404);
    }

    // ─── Certificats ─────────────────────────────────────────────────────────

    public function test_certificate_not_issued_before_all_lessons_complete(): void
    {
        // Crear una 2a lliçó publicada (la 1a del setUp ja existeix)
        LmsLesson::factory()->create([
            'course_id' => $this->course->id, 'session_number' => 20, 'status' => 'published',
        ]);

        $this->actingAs($this->student, 'student');
        // Completar NOMÉS la primera de les dues
        $this->post(route('campus.lms.lesson.complete', $this->lesson->id));

        $this->assertDatabaseMissing('lms_course_certificates', [
            'course_id'  => $this->course->id,
            'student_id' => $this->student->id,
        ]);
    }

    public function test_certificate_issued_when_all_lessons_complete(): void
    {
        // Amb una sola lliçó publicada (la del setUp), completar-la emet el cert
        $this->actingAs($this->student, 'student');
        $this->post(route('campus.lms.lesson.complete', $this->lesson->id));

        $this->assertDatabaseHas('lms_course_certificates', [
            'course_id'  => $this->course->id,
            'student_id' => $this->student->id,
        ]);
    }

    public function test_completing_same_lesson_twice_does_not_duplicate_certificate(): void
    {
        $this->actingAs($this->student, 'student');
        $this->post(route('campus.lms.lesson.complete', $this->lesson->id));
        $this->post(route('campus.lms.lesson.complete', $this->lesson->id));

        $this->assertDatabaseCount('lms_course_certificates', 1);
    }

    public function test_student_can_view_certificate_page(): void
    {
        LmsCourseCertificate::create([
            'course_id'  => $this->course->id,
            'student_id' => $this->student->id,
            'issued_at'  => now(),
        ]);

        $this->actingAs($this->student, 'student');
        $response = $this->get(route('campus.lms.course.certificate', $this->course->slug));

        $response->assertOk();
        $response->assertSee($this->student->full_name);
        $response->assertSee($this->course->title);
    }

    public function test_student_without_certificate_gets_404(): void
    {
        $this->actingAs($this->student, 'student');
        $response = $this->get(route('campus.lms.course.certificate', $this->course->slug));

        $response->assertStatus(404);
    }
}
