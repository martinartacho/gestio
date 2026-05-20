<?php

namespace Tests\Feature\Filament;

use App\Models\CampusCourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithFilamentAdmin;
use Tests\TestCase;

class CourseResourceTest extends TestCase
{
    use RefreshDatabase, InteractsWithFilamentAdmin;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->createAdmin();
    }

    public function test_can_list_courses(): void
    {
        $this->actingAs($this->admin)
             ->get('/admin/courses')
             ->assertSuccessful();
    }

    public function test_can_access_create_course_form(): void
    {
        $this->actingAs($this->admin)
             ->get('/admin/courses/create')
             ->assertSuccessful();
    }

    public function test_can_access_edit_course_form(): void
    {
        $course = CampusCourse::factory()->create();

        $this->actingAs($this->admin)
             ->get("/admin/courses/{$course->id}/edit")
             ->assertSuccessful();
    }

    public function test_course_slug_is_generated_on_creation(): void
    {
        $course = CampusCourse::factory()->create(['code' => 'TEST-01', 'slug' => null]);

        $this->assertNotNull($course->fresh()->slug);
        $this->assertSame('test-01', $course->fresh()->slug);
    }

    public function test_course_is_template_by_default(): void
    {
        $course = CampusCourse::factory()->create(['parent_id' => null]);
        $this->assertTrue($course->isTemplate());
    }

    public function test_course_edition_has_parent(): void
    {
        $parent  = CampusCourse::factory()->create();
        $edition = CampusCourse::factory()->edition($parent->id)->create();

        $this->assertTrue($edition->isEdition());
        $this->assertEquals($parent->id, $edition->parent_id);
    }

    public function test_admin_can_access_course_students_tab(): void
    {
        $course = CampusCourse::factory()->create();

        $this->actingAs($this->admin)
             ->get("/admin/courses/{$course->id}/students")
             ->assertSuccessful();
    }
}
