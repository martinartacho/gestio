<?php

namespace Tests\Feature\Filament;

use App\Models\CampusEnrollment;
use App\Models\CampusCourse;
use App\Models\CampusStudent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithFilamentAdmin;
use Tests\TestCase;

class TresoreriaDashboardTest extends TestCase
{
    use RefreshDatabase, InteractsWithFilamentAdmin;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->createAdmin();
    }

    public function test_admin_can_access_tresoreria_dashboard(): void
    {
        $this->actingAs($this->admin)
             ->get('/admin/tresoreria-dashboard')
             ->assertSuccessful();
    }

    public function test_tresoreria_can_access_dashboard(): void
    {
        $user = $this->createTresoreria();

        $this->actingAs($user)
             ->get('/admin/tresoreria-dashboard')
             ->assertSuccessful();
    }

    public function test_manager_cannot_access_dashboard(): void
    {
        $user = $this->createManager();

        $this->actingAs($user)
             ->get('/admin/tresoreria-dashboard')
             ->assertStatus(403);
    }

    public function test_dashboard_shows_correct_enrollment_totals(): void
    {
        $course  = CampusCourse::factory()->create();
        $student = CampusStudent::factory()->create();

        CampusEnrollment::factory()->create([
            'course_id'  => $course->id,
            'student_id' => $student->id,
            'status'     => 'paid',
            'amount'     => 100,
        ]);

        $response = $this->actingAs($this->admin)
             ->get('/admin/tresoreria-dashboard')
             ->assertSuccessful();

        $response->assertSee('100,00');
    }
}
