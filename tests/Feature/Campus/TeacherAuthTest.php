<?php

namespace Tests\Feature\Campus;

use App\Models\CampusCourse;
use App\Models\CampusSeason;
use App\Models\CampusTeacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $this->get('/campus/professorat/login')->assertSuccessful();
    }

    public function test_teacher_can_login(): void
    {
        $teacher = CampusTeacher::factory()->create([
            'email'    => 'prof@test.cat',
            'password' => bcrypt('password123'),
        ]);

        $this->post('/campus/professorat/login', [
            'email'    => 'prof@test.cat',
            'password' => 'password123',
        ])->assertRedirect('/campus/professorat/portal');
    }

    public function test_wrong_password_is_rejected(): void
    {
        CampusTeacher::factory()->create([
            'email'    => 'prof@test.cat',
            'password' => bcrypt('password123'),
        ]);

        $this->post('/campus/professorat/login', [
            'email'    => 'prof@test.cat',
            'password' => 'wrong',
        ])->assertSessionHasErrors('email');
    }

    public function test_portal_redirects_unauthenticated_teacher(): void
    {
        $this->get('/campus/professorat/portal')->assertRedirect('/campus/professorat/login');
    }

    public function test_authenticated_teacher_can_access_portal(): void
    {
        $teacher = CampusTeacher::factory()->create();

        $this->actingAs($teacher, 'teacher')
             ->get('/campus/professorat/portal')
             ->assertSuccessful();
    }

    public function test_teacher_can_view_own_course(): void
    {
        $teacher = CampusTeacher::factory()->create();
        $season  = CampusSeason::factory()->create(['status' => 'active']);
        $course  = CampusCourse::factory()->create(['season_id' => $season->id, 'status' => 'active']);

        $course->teachers()->attach($teacher->id, ['role' => 'main', 'sessions_assigned' => null]);

        $this->actingAs($teacher, 'teacher')
             ->get('/campus/professorat/portal/curs/' . $course->slug)
             ->assertSuccessful()
             ->assertSee($course->title);
    }

    public function test_teacher_cannot_view_unassigned_course(): void
    {
        $teacher = CampusTeacher::factory()->create();
        $season  = CampusSeason::factory()->create(['status' => 'active']);
        $course  = CampusCourse::factory()->create(['season_id' => $season->id]);

        $this->actingAs($teacher, 'teacher')
             ->get('/campus/professorat/portal/curs/' . $course->slug)
             ->assertNotFound();
    }

    public function test_teacher_can_access_liquidations(): void
    {
        $teacher = CampusTeacher::factory()->create();

        $this->actingAs($teacher, 'teacher')
             ->get('/campus/professorat/portal/liquidacions')
             ->assertSuccessful();
    }

    public function test_teacher_can_access_profile(): void
    {
        $teacher = CampusTeacher::factory()->create();

        $this->actingAs($teacher, 'teacher')
             ->get('/campus/professorat/portal/perfil')
             ->assertSuccessful();
    }

    public function test_teacher_can_update_profile(): void
    {
        $teacher = CampusTeacher::factory()->create();

        $this->actingAs($teacher, 'teacher')
             ->post('/campus/professorat/portal/perfil', [
                 'phone' => '699 111 222',
                 'bio'   => 'Sóc professor de proves.',
             ])->assertRedirect();

        $this->assertDatabaseHas('campus_teachers', [
            'id'    => $teacher->id,
            'phone' => '699 111 222',
        ]);
    }
}
