<?php

namespace Tests\Feature\Campus;

use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Models\CampusStudent;
use App\Models\LmsLesson;
use App\Models\LmsLessonProgress;
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
}
