<?php

namespace Tests\Feature\Filament;

use App\Models\CampusTeacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithFilamentAdmin;
use Tests\TestCase;

class TeacherResourceTest extends TestCase
{
    use RefreshDatabase, InteractsWithFilamentAdmin;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->createAdmin();
    }

    public function test_admin_can_list_teachers(): void
    {
        CampusTeacher::factory()->count(3)->create();

        $this->actingAs($this->admin)
             ->get('/admin/teachers')
             ->assertSuccessful();
    }

    public function test_admin_can_access_create_teacher_form(): void
    {
        $this->actingAs($this->admin)
             ->get('/admin/teachers/create')
             ->assertSuccessful();
    }

    public function test_admin_can_access_edit_teacher_form(): void
    {
        $teacher = CampusTeacher::factory()->create();

        $this->actingAs($this->admin)
             ->get("/admin/teachers/{$teacher->id}/edit")
             ->assertSuccessful();
    }

    public function test_tresoreria_can_list_teachers(): void
    {
        $user = $this->createTresoreria();
        CampusTeacher::factory()->count(2)->create();

        $this->actingAs($user)
             ->get('/admin/teachers')
             ->assertSuccessful();
    }

    public function test_teacher_stored_in_database_with_extended_fields(): void
    {
        $teacher = CampusTeacher::factory()->create([
            'dni'         => '12345678A',
            'city'        => 'Barcelona',
            'postal_code' => '08001',
        ]);

        $this->assertDatabaseHas('campus_teachers', [
            'id'          => $teacher->id,
            'dni'         => '12345678A',
            'city'        => 'Barcelona',
            'postal_code' => '08001',
        ]);
    }

    public function test_inactive_teacher_has_correct_status(): void
    {
        $teacher = CampusTeacher::factory()->inactive()->create();

        $this->assertSame('inactive', $teacher->status);
    }
}
