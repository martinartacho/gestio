<?php

namespace Tests\Feature\Campus;

use App\Models\CampusCourse;
use App\Models\CampusSeason;
use App\Models\CampusTeacher;
use App\Models\LmsLesson;
use App\Settings\SettingStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LmsTeacherWizardTest extends TestCase
{
    use RefreshDatabase;

    private CampusTeacher $teacher;
    private CampusSeason $season;

    protected function setUp(): void
    {
        parent::setUp();

        // Activar LMS (feature flag global)
        Cache::flush();
        $this->app->forgetInstance(SettingStore::class);
        app(SettingStore::class)->set('lms_enabled', true);
        $this->app->forgetInstance(SettingStore::class);
        Cache::flush();
        app(SettingStore::class)->set('lms_enabled', true);

        $this->teacher = CampusTeacher::factory()->create();
        $this->season  = CampusSeason::factory()->create(['status' => 'active']);
    }

    // ─── Pas 1 ───────────────────────────────────────────────────────────────

    public function test_teacher_can_access_wizard_step1(): void
    {
        $this->actingAs($this->teacher, 'teacher')
             ->get(route('teacher.lms.wizard.step1'))
             ->assertOk()
             ->assertSee('Dades bàsiques');
    }

    public function test_guest_cannot_access_wizard(): void
    {
        $this->get(route('teacher.lms.wizard.step1'))
             ->assertRedirect();
    }

    public function test_step1_stores_data_in_session(): void
    {
        $this->actingAs($this->teacher, 'teacher')
             ->post(route('teacher.lms.wizard.store1'), [
                 'title'          => 'Curs de prova',
                 'format'         => 'online',
                 'season_id'      => $this->season->id,
                 'sessions_count' => 4,
                 'description'    => 'Descripció de prova.',
                 'objectives'     => null,
             ])
             ->assertRedirect(route('teacher.lms.wizard.step2'))
             ->assertSessionHas('wizard_course.title', 'Curs de prova')
             ->assertSessionHas('wizard_course.sessions_count', 4);
    }

    // ─── Pas 2 ───────────────────────────────────────────────────────────────

    public function test_step2_redirects_if_no_step1_data(): void
    {
        $this->actingAs($this->teacher, 'teacher')
             ->get(route('teacher.lms.wizard.step2'))
             ->assertRedirect(route('teacher.lms.wizard.step1'));
    }

    public function test_step2_accessible_after_step1(): void
    {
        $this->actingAs($this->teacher, 'teacher')
             ->withSession(['wizard_course' => [
                 'title'          => 'Curs de prova',
                 'format'         => 'online',
                 'season_id'      => $this->season->id,
                 'sessions_count' => 3,
             ]])
             ->get(route('teacher.lms.wizard.step2'))
             ->assertOk()
             ->assertSee('Títols de les sessions');
    }

    // ─── Pas 3 ───────────────────────────────────────────────────────────────

    public function test_step3_redirects_if_incomplete_wizard(): void
    {
        // Sense cap dada de sessió
        $this->actingAs($this->teacher, 'teacher')
             ->get(route('teacher.lms.wizard.step3'))
             ->assertRedirect(route('teacher.lms.wizard.step1'));

        // Amb step1 però sense step2
        $this->actingAs($this->teacher, 'teacher')
             ->withSession(['wizard_course' => ['title' => 'X', 'sessions_count' => 2]])
             ->get(route('teacher.lms.wizard.step3'))
             ->assertRedirect(route('teacher.lms.wizard.step1'));
    }

    // ─── Confirm ─────────────────────────────────────────────────────────────

    private function wizardSession(int $sessions = 2): array
    {
        return [
            'wizard_course' => [
                'title'          => 'Curs del wizard',
                'format'         => 'online',
                'season_id'      => $this->season->id,
                'sessions_count' => $sessions,
                'description'    => 'Test',
                'objectives'     => null,
            ],
            'wizard_sessions' => collect(range(1, $sessions))
                ->mapWithKeys(fn ($i) => [$i => ['title' => "Sessió {$i}"]])
                ->all(),
        ];
    }

    public function test_confirm_creates_course_as_draft(): void
    {
        $this->actingAs($this->teacher, 'teacher')
             ->withSession($this->wizardSession())
             ->post(route('teacher.lms.wizard.confirm'))
             ->assertRedirect();

        $this->assertDatabaseHas('campus_courses', [
            'title'  => 'Curs del wizard',
            'format' => 'online',
            'status' => 'draft',
        ]);
    }

    public function test_confirm_creates_lesson_stubs(): void
    {
        $this->actingAs($this->teacher, 'teacher')
             ->withSession($this->wizardSession(3))
             ->post(route('teacher.lms.wizard.confirm'));

        $course = CampusCourse::where('title', 'Curs del wizard')->firstOrFail();

        $this->assertDatabaseCount('lms_lessons', 3);
        $this->assertDatabaseHas('lms_lessons', [
            'course_id' => $course->id,
            'title'     => 'Sessió 1',
            'status'    => 'draft',
        ]);
    }

    public function test_confirm_attaches_teacher_as_main(): void
    {
        $this->actingAs($this->teacher, 'teacher')
             ->withSession($this->wizardSession())
             ->post(route('teacher.lms.wizard.confirm'));

        $course = CampusCourse::where('title', 'Curs del wizard')->firstOrFail();

        $this->assertDatabaseHas('campus_course_teacher', [
            'course_id'  => $course->id,
            'teacher_id' => $this->teacher->id,
            'role'       => 'main',
        ]);
    }

    public function test_confirm_clears_session(): void
    {
        $this->actingAs($this->teacher, 'teacher')
             ->withSession($this->wizardSession())
             ->post(route('teacher.lms.wizard.confirm'))
             ->assertSessionMissing('wizard_course')
             ->assertSessionMissing('wizard_sessions');
    }
}
